<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/role/update', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $name = $data->role_name;
        $start_time = $data->start_time;
        $get_off_time = $data->get_off_time;
        $username = $data->username;
        $admin = $data->admin;

        if($admin == 1){
            $sql = "UPDATE role SET R_name = '$name', R_start_work = '$start_time', R_get_off_work = '$get_off_time', 
                   R_upd_by = '$username'";

            $run = new Update($sql, $response);
            return $run->evaluate();
        }else{
            $response->getBody()->write(json_encode("You are not admin"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(403);
        }
    });
};