<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
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

$app->run();
