<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/user/password/change', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $email = $data->email;
        $password = $data->password;
        $password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "UPDATE member SET M_password = '$password' WHERE M_email = '$email'";

        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};