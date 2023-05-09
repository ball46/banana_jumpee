<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

$app = AppFactory::create();
$app->setBasePath('/banana_jumpee');
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello world!");
    return $response;
});

//use routes
$routes = require __DIR__ . '/../routes/routes.php';
$routes($app);

$app->run();