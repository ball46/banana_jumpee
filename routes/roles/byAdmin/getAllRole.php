<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/role/show/all/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];

        $sql = "SELECT * FROM member WHERE M_id = '$id'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $result = $run->getterResult();
        
        if ($result->M_admin) {
            $sql = "SELECT * FROM role";

            $run = new GetAll($sql, $response);
            $run->evaluate();
            return $run->return();
        } else {
            $response->getBody()->write(json_encode("You are not admin"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(403);
        }
    });
};