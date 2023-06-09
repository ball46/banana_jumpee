<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/health/get/{persona_id}', function (Request $request, Response $response, array $args) {
        $persona_id = $args['persona_id'];
        $sql = "SELECT * FROM role WHERE H_member_id = '$persona_id'";

        $run = new GetAll($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};