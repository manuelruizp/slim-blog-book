<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Psr\Http\Server\RequestHandlerInterface;

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
    $queryParams = $request->getQueryParams();
    $page = ($queryParams['pagina'] ?? '1') === '2' ? 2 : 1;
    $perPage = 2;
    $offset = ($page - 1) * $perPage;
    $pageArticles = $articleRepository->listPage($perPage, $offset);

    $response = $response->withHeader(
        'Content-Type', 'text/html; charset=UTF-8'
    );
    return $renderer->render($response, 'article-list.php', [
        'pageArticles' => $pageArticles,
    ]);
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

$app->get('/panel/articulos/nuevo', function (
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
})->add($requireLogin);

$app->post('/panel/articulos/nuevo', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use ($articleRepository, $renderer): ResponseInterface {
    $data = $request->getParsedBody();
    $data = is_array($data) ? $data : [];
    $values = [];
    foreach (['title', 'slug', 'body'] as $field) {
        $value = $data[$field] ?? '';
        $values[$field] = is_string($value) ? trim($value) : '';
    }

    $errors = [];
    if ($values['title'] === '') {
        $errors['title'] = 'Escribe el título.';
    }
    if (!preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $values['slug'])) {
        $errors['slug'] = 'Usa minúsculas sin acentos, números y guiones simples.';
    }
    if ($values['body'] === '') {
        $errors['body'] = 'Escribe el contenido del artículo.';
    }
    if ($errors === []) {
        $saved = $articleRepository->create(
            $values['slug'], $values['title'], $values['body']
        );
        if ($saved) {
            $url = '/articulos/' . rawurlencode($values['slug']);
            return $response->withHeader('Location', $url)->withStatus(303);
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
