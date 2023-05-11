<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->delete('/user/delete', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $email = $data->email;
        $admin = $data->admin;

        if($admin) {
            $sql = "DELETE FROM member WHERE M_email = '$email'";

            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        }else {
            $response->getBody()->write(json_encode("You are not admin"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(403);
        }
    });
};