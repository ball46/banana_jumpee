<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/list/request/leave/{token}', function (Request $request, Response $response, array $args) {
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

        $send_data = [];

        $sql = "SELECT * FROM memberallow WHERE SM_member_approve_id = '$member_id'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $data_allow = $run->getterResult();
            foreach ($data_allow as $data) {
                $member_applicant_id = $data->SM_member_applicant_id;
                $type = $data->SM_type_leave;

                $sql = "SELECT * FROM allowcount";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_count = $run->getterResult();

                if ($type == "business") {
                    $max_count = $data_count->A_business;
                } else if ($type == "sick") {
                    $max_count = $data_count->A_sick;
                } else {
                    $max_count = $data_count->A_special;
                }

                $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_applicant_id' AND V_status = '1'
                        AND FIND_IN_SET('$member_id', REPLACE(V_wait, ' ', ',')) > 0";
                $run = new GetAll($sql, $response);
                $run->evaluate();
                if ($run->getterCount()) {
                    $data_vacation = $run->getterResult();
                    foreach ($data_vacation as $vacation) {
                        $type_leave = $vacation->V_sick_leave ? "sick" : "business";
                        $use_special_leave = (bool)$vacation->V_special_leave;
                        $send_data[] = array(
                            'vid' => $vacation->V_id,
                            'vacation' => $vacation->V_title,
                            'type' => $type_leave,
                            'use_special_or_not' => $use_special_leave,
                            'special_day' => $use_special_leave ? $data->V_count_day : 0,
                            'start_date' => $vacation->V_start_date,
                            'end_date' => $vacation->V_end_date,
                            'allow' => $max_count - $vacation->V_count_allow,
                            'max_count' => $max_count
                        );
                    }
                }
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
