<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/user/status/change', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $email = $data->email;
        $status = $data->status;

        $sql = "UPDATE member SET M_status = '$status' WHERE M_email = '$email'";

        $run = new Update($sql, $response);
        return $run->evaluate();
    });
};