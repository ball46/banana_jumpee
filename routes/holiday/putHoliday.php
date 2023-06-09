<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/holiday/update/holiday', function (Request $request, Response $response){
        $data = json_decode($request->getBody());
        $holiday_id = $data->id;
        $name = $data->name;
        $start_date = $data->start_date;
        $end_date = $data->end_date;
        $upd_by = $data->upd_by;
        $admin = $data->admin;

        if($admin) {
            $sql = "UPDATE memberimage SET H_name = '$name', H_start_date = '$start_date', H_end_date = '$end_date',
                    H_upd_by = '$upd_by' WHERE H_id = '$holiday_id'";
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