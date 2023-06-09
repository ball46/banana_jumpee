<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->get('/leave/get/member/allow/{token}', function (Request $request, Response $response, array $args) {
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
        if(!$token->admin) {
            $response->getBody()->write(json_encode("You are not admin"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(403);
        }

        $data_business = [];
        $data_sick = [];
        $data_special = [];

        $business_allow_id = [];
        $sick_allow_id = [];
        $special_allow_id = [];

        $sql = "SELECT * FROM memberallow";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if($run->getterCount()){
            $array_member_allow = $run->getterResult();
            foreach ($array_member_allow as $member_allow){
                $sql = "SELECT * FROM member WHERE M_id = '$member_allow->MA_member_id'";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_member_allow = $run->getterResult();

                $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $data_member_allow->M_id";
                $run = new Get($sql, $response);
                $run->evaluate();
                $image_name = ($run->getterResult())->MI_image_name;

                $data_send = array(
                    "member_allow_id" => $member_allow->MA_id,
                    "image_name" => $image_name,
                    "username" => $data_member_allow->M_username,
                    "display_name" => $data_member_allow->M_display_name,
                );

                if($member_allow->MA_type_leave == "business"){
                    $data_business[] = $data_send;
                    $business_allow_id[] = $member_allow->MA_id;
                }else if($member_allow->MA_type_leave == "sick"){
                    $data_sick[] = $data_send;
                    $sick_allow_id[] = $member_allow->MA_id;
                }else{
                    $data_special[] = $data_send;
                    $special_allow_id[] = $member_allow->MA_id;
                }
            }

            $send = array(
                'business' => array(
                    'member_allow_id' => $business_allow_id,
                    'data' => $data_business
                ),
                'sick' => array(
                    'member_allow_id' => $sick_allow_id,
                    'data' => $data_sick
                ),
                'special' => array(
                    'member_allow_id' => $special_allow_id,
                    'data' => $data_special
                )
            );

            $response->getBody()->write(json_encode($send));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }else{
            $response->getBody()->write(json_encode("You not have member allow to leave all types."));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    });
};
