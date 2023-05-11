<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/role/status/change', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $id = $data->id;
        $status = $data->status;
        $admin = $data->admin;

        if($admin == 1){
            $sql = "UPDATE role SET R_status = '$status' WHERE R_id = '$id'";

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