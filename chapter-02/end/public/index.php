<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(false, true, true);

$articles = [
    'mi-primer-post' => [
        'title' => 'Mi primer post',
        'body' => 'Esta es la primera publicación del blog.',
    ],
    'entender-las-rutas' => [
        'title' => 'Entender las rutas',
        'body' => 'Una ruta conecta una petición con una acción.',
    ],
    'probar-el-blog' => [
        'title' => 'Probar el blog',
        'body' => 'Cambia la dirección y observa la respuesta.',
    ],
];

$app->get('/', function (
    ServerRequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
    $response->getBody()->write('<h1>Bienvenido al blog</h1>');

    return $response->withHeader(
        'Content-Type',
        'text/html; charset=UTF-8'
    );
});

$app->get('/articulos', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use ($articles): ResponseInterface {
    $queryParams = $request->getQueryParams();
    $page = ($queryParams['pagina'] ?? '1') === '2' ? 2 : 1;
    $perPage = 2;
    $offset = ($page - 1) * $perPage;
    $pageArticles = array_slice($articles, $offset, $perPage, true);

    $html = '<h1>Artículos</h1><ul>';
    foreach ($pageArticles as $slug => $article) {
        $url = '/articulos/' . rawurlencode($slug);
        $title = htmlspecialchars(
            $article['title'], ENT_QUOTES, 'UTF-8'
        );
        $html .= '<li><a href="' . $url . '">' . $title . '</a></li>';
    }
    $html .= '</ul>';
    $html .= '<a href="/articulos?pagina=1">Página 1</a> | ';
    $html .= '<a href="/articulos?pagina=2">Página 2</a>';

    $response->getBody()->write($html);
    return $response->withHeader(
        'Content-Type', 'text/html; charset=UTF-8'
    );
});

$app->get('/articulos/{slug}', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
) use ($articles): ResponseInterface {
    $slug = $args['slug'];
    $article = $articles[$slug] ?? null;
    $response = $response->withHeader(
        'Content-Type', 'text/html; charset=UTF-8'
    );

    if ($article === null) {
        $response->getBody()->write('<h1>Artículo no encontrado</h1>');
        return $response->withStatus(404);
    }

    $title = htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8');
    $body = htmlspecialchars(
        $article['body'], ENT_QUOTES, 'UTF-8'
    );
    $response->getBody()->write(
        '<h1>' . $title . '</h1><p>' . $body . '</p>'
    );
    return $response;
});

$app->run();
