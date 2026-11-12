<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(false, true, true);

$renderer = new PhpRenderer(__DIR__ . '/../templates');
$renderer->setLayout('layout.php');

require_once __DIR__ . '/../src/ArticleRepository.php';

$pdo = require __DIR__ . '/../database/connection.php';
$articleRepository = new ArticleRepository($pdo);

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
});

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
});

$app->run();
