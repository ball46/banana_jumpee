<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/health/get', function (Request $request, Response $response) {
        $persona_id = (json_decode($request->getBody()))->persona_id;
        $sql = "SELECT * FROM health WHERE H_member_id = '$persona_id'";

        $run = new GetAll($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};