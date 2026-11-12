<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

session_start([
    'use_strict_mode' => 1,
    'use_only_cookies' => 1,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off',
]);
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

$hasValidToken = static function (ServerRequestInterface $request): bool {
    $data = $request->getParsedBody();
    $token = is_array($data) ? ($data['csrf_token'] ?? null) : null;

    return is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
};

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(false, true, true);

$renderer = new PhpRenderer(__DIR__ . '/../templates');
$renderer->setLayout('layout.php');

require_once __DIR__ . '/../src/ArticleRepository.php';

$pdo = require __DIR__ . '/../database/connection.php';
$articleRepository = new ArticleRepository($pdo);
require_once __DIR__ . '/../src/validate-article.php';

$requireLogin = static function (
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
) use ($app, $hasValidToken): ResponseInterface {
    if (!isset($_SESSION['user_id'])) {
        return $app->getResponseFactory()->createResponse(303)
            ->withHeader('Location', '/iniciar-sesion');
    }

    if ($request->getMethod() === 'POST' && !$hasValidToken($request)) {
        return $app->getResponseFactory()->createResponse(403);
    }

    return $handler->handle($request);
};

$app->get('/', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use ($renderer): ResponseInterface {
    $response = $response->withHeader(
        'Content-Type', 'text/html; charset=UTF-8'
    );
    return $renderer->render($response, 'home.php');
});

$app->get('/articulos', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use ($articleRepository, $renderer): ResponseInterface {
    $perPage = 2;
    $totalPages = max(1, (int) ceil(
        $articleRepository->countPublished() / $perPage
    ));
    $queryParams = $request->getQueryParams();
    $page = filter_var($queryParams['pagina'] ?? '1', FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]) ?: 1;
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    return $renderer->render(
        $response->withHeader('Content-Type', 'text/html; charset=UTF-8'),
        'article-list.php', [
            'pageArticles' => $articleRepository->listPage($perPage, $offset),
            'page' => $page,
            'totalPages' => $totalPages,
        ]
    );
});

$app->get('/articulos/{slug}', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
) use ($articleRepository, $renderer): ResponseInterface {
    $article = $articleRepository->findBySlug($args['slug']);
    $response = $response->withHeader(
        'Content-Type', 'text/html; charset=UTF-8'
    );

    if ($article === null) {
        return $renderer->render(
            $response->withStatus(404),
            'article-not-found.php'
        );
    }

    return $renderer->render($response, 'article.php', [
        'article' => $article,
    ]);
});

$app->get('/iniciar-sesion', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use ($renderer): ResponseInterface {
    return $renderer->render(
        $response->withHeader('Content-Type', 'text/html; charset=UTF-8'),
        'login.php',
        ['error' => null]
    );
});

$app->post('/iniciar-sesion', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use ($pdo, $renderer, $hasValidToken): ResponseInterface {
    if (!$hasValidToken($request)) {
        return $response->withStatus(403);
    }

    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $email = is_string($email) ? strtolower(trim($email)) : '';
    $password = is_string($password) ? $password : '';

    $statement = $pdo->prepare(
        'SELECT id, password_hash FROM users WHERE email = :email'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if ($user === false || !password_verify($password, $user['password_hash'])) {
        $response = $response->withStatus(422)->withHeader(
            'Content-Type', 'text/html; charset=UTF-8'
        );
        return $renderer->render($response, 'login.php', [
            'error' => 'Revisa tus datos de acceso.',
        ]);
    }

    if (!session_regenerate_id(true)) {
        return $response->withStatus(500);
    }
    $_SESSION = [
        'user_id' => (int) $user['id'],
        'csrf_token' => bin2hex(random_bytes(32)),
    ];

    return $response->withHeader('Location', '/panel/articulos/nuevo')
        ->withStatus(303);
});

$app->group('/panel', function (RouteCollectorProxy $group) use (
    $articleRepository, $renderer
): void {
    $group->get('/articulos/nuevo', function (
        ServerRequestInterface $request,
        ResponseInterface $response
    ) use ($renderer): ResponseInterface {
        $response = $response->withHeader(
            'Content-Type', 'text/html; charset=UTF-8'
        );
        return $renderer->render($response, 'article-form.php', [
            'values' => ['title' => '', 'slug' => '', 'body' => ''],
            'errors' => [],
        ]);
    });
    $group->post('/articulos/nuevo', function (
        ServerRequestInterface $request,
        ResponseInterface $response
    ) use ($articleRepository, $renderer): ResponseInterface {
        ['values' => $values, 'errors' => $errors] = validateArticle(
            $request->getParsedBody()
        );
        if ($errors === []) {
            $saved = $articleRepository->create(
                $values['slug'], $values['title'], $values['body']
            );
            if ($saved) {
            return $response->withHeader('Location', '/panel/articulos')
                ->withStatus(303);
        }

            $errors['slug'] = 'Ese slug ya existe. Escribe otro.';
        }

        $response = $response->withStatus(422)->withHeader(
            'Content-Type', 'text/html; charset=UTF-8'
        );
        return $renderer->render($response, 'article-form.php', [
            'values' => $values,
            'errors' => $errors,
        ]);
    });
    $group->get('/articulos', function (
        ServerRequestInterface $request,
        ResponseInterface $response
    ) use ($articleRepository, $renderer): ResponseInterface {
        return $renderer->render(
            $response->withHeader(
                'Content-Type', 'text/html; charset=UTF-8'
            ),
            'admin-article-list.php',
            ['articles' => $articleRepository->listAll()]
        );
    });

    $group->get('/articulos/{id:[1-9][0-9]*}/editar', function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) use ($articleRepository, $renderer): ResponseInterface {
        $id = (int) $args['id'];
        $article = $articleRepository->findById($id);
        $response = $response->withHeader(
            'Content-Type', 'text/html; charset=UTF-8'
        );
        if ($article === null) {
            return $renderer->render(
                $response->withStatus(404), 'article-not-found.php'
            );
        }

        return $renderer->render($response, 'article-form.php', [
            'values' => $article,
            'errors' => [],
            'formTitle' => 'Editar artículo',
            'formAction' => '/panel/articulos/' . $id . '/editar',
            'submitLabel' => 'Guardar cambios',
        ]);
    });

    $group->post('/articulos/{id:[1-9][0-9]*}/editar', function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) use ($articleRepository, $renderer): ResponseInterface {
        $id = (int) $args['id'];
        $response = $response->withHeader(
            'Content-Type', 'text/html; charset=UTF-8'
        );
        if ($articleRepository->findById($id) === null) {
            return $renderer->render(
                $response->withStatus(404), 'article-not-found.php'
            );
        }

        ['values' => $values, 'errors' => $errors] = validateArticle(
            $request->getParsedBody()
        );

        if ($errors === []) {
            $saved = $articleRepository->update(
                $id, $values['slug'], $values['title'], $values['body']
            );
            if ($saved) {
                $url = '/panel/articulos/' . $id . '/editar';
                return $response->withHeader('Location', $url)
                    ->withStatus(303);
            }
            if ($articleRepository->findById($id) === null) {
                return $renderer->render(
                    $response->withStatus(404), 'article-not-found.php'
                );
            }
            $errors['slug'] = 'Ese slug ya existe. Escribe otro.';
        }

        return $renderer->render(
            $response->withStatus(422), 'article-form.php', [
                'values' => $values,
                'errors' => $errors,
                'formTitle' => 'Editar artículo',
                'formAction' => '/panel/articulos/' . $id . '/editar',
                'submitLabel' => 'Guardar cambios',
            ]
        );
    });

    $group->get('/articulos/{id:[1-9][0-9]*}/eliminar', function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) use ($articleRepository, $renderer): ResponseInterface {
        $article = $articleRepository->findById((int) $args['id']);
        $response = $response->withHeader(
            'Content-Type', 'text/html; charset=UTF-8'
        );
        if ($article === null) {
            return $renderer->render(
                $response->withStatus(404), 'article-not-found.php'
            );
        }

        return $renderer->render($response, 'article-delete.php', [
            'article' => $article,
        ]);
    });

    $group->post('/articulos/{id:[1-9][0-9]*}/eliminar', function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) use ($articleRepository, $renderer): ResponseInterface {
        $deleted = $articleRepository->delete((int) $args['id']);
        if (!$deleted) {
            return $renderer->render(
                $response->withStatus(404)->withHeader(
                    'Content-Type', 'text/html; charset=UTF-8'
                ),
                'article-not-found.php'
            );
        }

        return $response->withHeader('Location', '/panel/articulos')
            ->withStatus(303);
    });
    $group->get('/articulos/{id:[1-9][0-9]*}/vista-previa', function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) use ($articleRepository, $renderer): ResponseInterface {
        $article = $articleRepository->findById((int) $args['id']);
        $response = $response->withHeader(
            'Content-Type', 'text/html; charset=UTF-8'
        )->withHeader('Cache-Control', 'no-store');
        if ($article === null) {
            return $renderer->render(
                $response->withStatus(404), 'article-not-found.php'
            );
        }

        return $renderer->render($response, 'article-preview.php', [
            'article' => $article,
        ]);
    });
    $group->post('/articulos/{id:[1-9][0-9]*}/publicar', function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) use ($articleRepository, $renderer): ResponseInterface {
        $id = (int) $args['id'];
        $article = $articleRepository->findById($id);
        $response = $response->withHeader(
            'Content-Type', 'text/html; charset=UTF-8'
        );
        if ($article === null) {
            return $renderer->render(
                $response->withStatus(404), 'article-not-found.php'
            );
        }

        ['values' => $values, 'errors' => $errors] = validateArticle(
            $article
        );
        if ($errors !== []) {
            return $renderer->render(
                $response->withStatus(422), 'article-form.php', [
                    'values' => $values,
                    'errors' => $errors,
                    'formTitle' => 'Revisa el artículo antes de publicar',
                    'formAction' => '/panel/articulos/' . $id . '/editar',
                    'submitLabel' => 'Guardar cambios',
                ]
            );
        }
        if (!$articleRepository->publish($id)) {
            if ($articleRepository->findById($id) === null) {
                return $renderer->render(
                    $response->withStatus(404), 'article-not-found.php'
                );
            }
            $response->getBody()->write(
                '<h1>No se pudo publicar. Vuelve al editor.</h1>'
            );
            return $response->withStatus(422);
        }

        return $response->withHeader('Location', '/panel/articulos')
            ->withStatus(303);
    });
})->add($requireLogin);

$app->post('/cerrar-sesion', function (
    ServerRequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
    $_SESSION = [];
    if (!session_regenerate_id(true)) {
        return $response->withStatus(500);
    }

    return $response->withHeader('Location', '/')->withStatus(303);
})->add($requireLogin);

$app->run();
