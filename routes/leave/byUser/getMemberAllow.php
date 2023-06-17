<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->get('/leave/get/member/allow/{token}', function (Request $request, Response $response, array $args) {
        $token = jwt::decode($args['token'], new Key("my_secret_key", 'HS256'));
        $member_id = $token->id;

        $sql = "SELECT * FROM memberallow WHERE SM_member_applicant_id = '$member_id'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if($run->getterCount()){
            $array_member_allow = $run->getterResult();
            foreach ($array_member_allow as $member_allow){
                $sql = "SELECT * FROM member WHERE M_id = '$member_allow->SM_member_approve_id'";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_member_allow = $run->getterResult();

                $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $data_member_allow->M_id";
                $run = new Get($sql, $response);
                $run->evaluate();
                $image_name = ($run->getterResult())->MI_image_name;

                $data_send = array(
                    "image_name" => $image_name,
                    "username" => $data_member_allow->M_username,
                    "display_name" => $data_member_allow->M_display_name,
                );
            }
        }else{
            $response->getBody()->write(json_encode("You not have member allow to leave all types."));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    });
};
