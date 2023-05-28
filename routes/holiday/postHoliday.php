<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/holiday/new/day', function (Request $request, Response $response){
        $data = json_decode($request->getBody());
        $name = $data->name;
        $start_date = $data->start_date;
        $end_date = $data->end_date;
        $upd_by = $data->upd_by;
        $admin = $data->admin;

        if($admin) {
            $sql = "INSERT INTO holiday (H_name, H_start_date, H_end_date, H_upd_by) 
                    VALUES ('$name', '$start_date', '$end_date', '$upd_by')";
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