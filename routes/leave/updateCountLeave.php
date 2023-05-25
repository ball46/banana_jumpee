<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/leave/update/max/count', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $business = $data->business_leave;
        $sick = $data->sick_leave;
        $special = $data->special_leave;
        $admin = $data->admin;
        $username = $data->username;

        if($admin){
            $sql = "UPDATE maxleave SET M_business_leave = '$business', M_sick_leave = '$sick', 
                    M_special_leave = '$special', M_upd_by = '$username'";

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