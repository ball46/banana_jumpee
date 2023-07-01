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

        $sql = "SELECT * FROM memberallow WHERE MA_member_id = '$member_id'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $data_allow = $run->getterResult();
            foreach ($data_allow as $data) {
                $type = $data->MA_type_leave;

                $sql = "SELECT * FROM allowcount";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_count = $run->getterResult();

                if ($type == "business") {
                    $max_count = $data_count->A_business;
                    $sick_leave = 0;
                } else if ($type == "sick") {
                    $max_count = $data_count->A_sick;
                    $sick_leave = 1;
                } else {
                    continue;
                }

                $sql = "SELECT * FROM vacation WHERE V_sick_leave = $sick_leave AND V_status = 1
                        AND FIND_IN_SET('$member_id', REPLACE(V_wait, ' ', ',')) > 0";
                $run = new GetAll($sql, $response);
                $run->evaluate();
                if ($run->getterCount()) {
                    $data_vacation = $run->getterResult();
                    foreach ($data_vacation as $vacation) {
                        $sql = "SELECT * FROM member WHERE M_id = $vacation->V_member_id";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $data_member = $run->getterResult();
                        $use_special_leave = (bool)$vacation->V_special_leave;

                        $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $data_member->M_id";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $image_name = ($run->getterResult())->MI_image_name;

                        $send_data[] = array(
                            'vid' => $vacation->V_id,
                            'vacation' => $vacation->V_title,
                            'type' => $type,
                            'use_special_or_not' => $use_special_leave,
                            'start_date' => $vacation->V_start_date,
                            'end_date' => $vacation->V_end_date,
                            "image_name" => $image_name,
                            "display_name" => $data_member->M_display_name,
                            "first_name" => $data_member->M_first_name,
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
