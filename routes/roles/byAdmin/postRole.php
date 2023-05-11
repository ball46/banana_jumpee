<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/role/new', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $name = $data->role_name;
        $start_time = $data->start_time;
        $get_off_time = $data->get_off_time;
        $username = $data->username;
        $admin = $data->admin;

        if($admin){
            $sql = "INSERT INTO role (R_name, R_start_work, R_get_off_work, R_upd_by) 
                    VALUES ('$name', '$start_time', '$get_off_time', '$username')";

            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        }else{
            $response->getBody()->write(json_encode("You are not admin"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(403);
        }
    });
};