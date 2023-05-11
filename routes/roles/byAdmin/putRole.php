<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/role/update', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $id = $data->id;
        $name = $data->role_name;
        $start_time = $data->start_time;
        $get_off_time = $data->get_off_time;
        $username = $data->username;
        $admin = $data->admin;

        if($admin){
            $sql = "UPDATE role SET R_name = '$name', R_start_work = '$start_time', R_get_off_work = '$get_off_time', 
                   R_upd_by = '$username' WHERE R_id = '$id'";

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