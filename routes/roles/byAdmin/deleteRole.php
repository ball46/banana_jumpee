<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->delete('/role/delete', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $id = $data->id;

        $sql = "DELETE FROM member WHERE R_id = '$id'";

        $run = new Update($sql, $response);
        return $run->evaluate();
    });
};