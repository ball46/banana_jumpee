<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/leave/update/max/count', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $id = $data->id;
        $business = $data->business_leave;
        $sick = $data->sick_leave;
        $special = $data->special_leave;
        $username = $data->username;
        $admin = $data->admin;

        if($admin){
            $sql = "UPDATE maxleave SET ML_business_leave = '$business', ML_sick_leave = '$sick', 
                    ML_special_leave = '$special', ML_upd_by = '$username' WHERE ML_id = '$id'";
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