<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/list/request/leave/{token}', function (Request $request, Response $response, array $args) {
        $token = jwt::decode($args['token'], new Key("my_secret_key", 'HS256'));
        $member_id = $token->id;

        $send = [];

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
                        $member_wait = $vacation->V_wait;
                        $member_wait = explode(" ", $member_wait);
                        array_pop($member_wait);
                        $wait = [];

                        $member_allow = $vacation->V_allow;
                        $member_allow = explode(" ", $member_allow);
                        array_pop($member_allow);
                        $allow = [];

                        foreach ($member_wait as $member) {
                            $sql = "SELECT * FROM member WHERE M_id = '$member'";
                            $run = new Get($sql, $response);
                            $run->evaluate();
                            $wait[] = $run->getterResult();
                        }

                        foreach ($member_allow as $member) {
                            $sql = "SELECT * FROM member WHERE M_id = '$member'";
                            $run = new Get($sql, $response);
                            $run->evaluate();
                            $allow[] = $run->getterResult();
                        }

                        $send[] = array(
                            'vacation' => $vacation,
                            'type' => $type,
                            'allow' => $allow,
                            'wait' => $wait,
                            'max_count' => $max_count
                        );
                    }
                }
            }
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
