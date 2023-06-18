<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/time/in/out/{token}', function (Request $request, Response $response, array $args) {
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
        $member_id = $token->id;

        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $now_date = date("Y-m-d", $current_timestamp);

        $time = array(
            'timeIn' => "none",
            'timeOut' => "none"
        );

        $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' AND F_date = '$now_date'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if($run->getterCount()) {
            $data_history = $run->getterResult();
            foreach ($data_history as $data){
                if($data->F_in_out){
                    $time['timeIn'] = $data->F_time;
                }else{
                    $time['timeOut'] = $data->F_time;
                }
            }
        }

        $response->getBody()->write(json_encode($time));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};