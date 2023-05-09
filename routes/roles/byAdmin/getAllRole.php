<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/role/show/all', function (Request $request, Response $response) {
        $sql = "SELECT * FROM role";

        $run = new GetAll($sql, $response);
        return $run->evaluate();
    });
};