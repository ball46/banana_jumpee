<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/list/ask/to/leave/{token}', function (Request $request, Response $response, array $args) {
        try {
            $token = jwt::decode($args['token'], new Key("my_secret_key", 'HS256'));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array(
                "error_message" => "Invalid token",
                "message" => $e->getMessage()
            )));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        }
        $member_id = $token->id;

        $send_data = [];

        $sql = "SELECT * FROM vacation WHERE V_status = 1 AND V_member_id = $member_id AND V_count_allow > 0";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $data_vacation = $run->getterResult();

            $sql = "SELECT * FROM allowcount";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_count = $run->getterResult();

            foreach ($data_vacation as $vacation) {
                $use_special_leave = (bool)$vacation->V_special_leave;
                if($vacation->V_sick_leave){
                    $type = "sick";
                    $max_count = $data_count->A_sick;
                }else{
                    $type = "business";
                    $max_count = $data_count->A_business;
                }
                $send_data[] = array(
                    'vid' => $vacation->V_id,
                    'vacation' => $vacation->V_title,
                    'type' => $type,
                    'use_special_or_not' => $use_special_leave,
                    'special_day' => $use_special_leave ? $vacation->V_count_day : 0,
                    'start_date' => $vacation->V_start_date,
                    'end_date' => $vacation->V_end_date,
                    'allow' => $max_count - $vacation->V_count_allow,
                    'max_count' => $max_count
                );
            }
            $send = array(
                'num' => count($send_data),
                'data' => $send_data
            );
            $response->getBody()->write(json_encode($send));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } else {
            $response->getBody()->write(json_encode("You not have requested allow for leave"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    });
};
