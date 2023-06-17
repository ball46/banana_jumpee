<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/count/list/history/{token}', function (Request $request, Response $response, array $args) {
        $token = jwt::decode($args['token'], new Key("my_secret_key", 'HS256'));
        $member_id = $token->id;

        $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id'";
        $run = new GetAll($sql, $response);
        $run->evaluate();

        $response->getBody()->write(json_encode($run->getterCount()));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);

    });
};
