<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/count/list/history', function (Request $request, Response $response) {
            $member_id = (json_decode($request->getBody()))->member_id;

            $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id'";
            $run = new GetAll($sql, $response);
            $run->evaluate();

            $response->getBody()->write(json_encode($run->getterCount()));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);

        });
};
