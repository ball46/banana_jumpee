<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/leave/update/max/count/{token}', function (Request $request, Response $response, array $args) {
        try {
            $token = jwt::decode($args['token'], new Key("my_secret_key", 'HS256'));
        }catch (Exception $e){
            $response->getBody()->write(json_encode(array(
                "error_message" => "Invalid token",
                "message" => $e->getMessage()
            )));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        }
        $admin = $token->admin;

        $data = json_decode($request->getBody());
        $id = $data->id;
        $position = $data->position;
        $business = $data->business_leave;
        $sick = $data->sick_leave;
        $special = $data->special_leave;
        $username = $data->username;

        if($admin){
            $sql = "UPDATE maxleave SET ML_position = '$position', ML_business_leave = '$business', 
                    ML_sick_leave = '$sick', ML_special_leave = '$special', ML_upd_by = '$username' WHERE ML_id = '$id'";
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