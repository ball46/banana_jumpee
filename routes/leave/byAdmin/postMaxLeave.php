<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/leave/post/new/max/count', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $position = $data->position;
        $business = $data->business_leave;
        $sick = $data->sick_leave;
        $special = $data->special_leave;
        $username = $data->username;
        $admin = $data->admin;

        if($admin){
            $sql = "INSERT INTO maxleave (ML_position, ML_business_leave, ML_sick_leave, ML_special_leave, ML_upd_by) 
                    VALUES ('$position', '$business', '$sick', '$special', '$username')";
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