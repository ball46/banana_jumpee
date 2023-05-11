<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/role/user/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $sql = "SELECT * FROM role WHERE R_id = '$id'";

        $run = new Get($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};