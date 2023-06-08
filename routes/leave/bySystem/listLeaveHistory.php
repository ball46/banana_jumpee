<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/list/history/{member_id}',
        function (Request $request, Response $response, array $args) {
            $member_id = $args['member_id'];

            $send = [];

            $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id'";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $data_leave = $run->getterResult();
                foreach ($data_leave as $data) {
                    $member_wait = $data->V_wait;

                    $sql = "SELECT * FROM allowcount";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    $data_count = $run->getterResult();

                    if($data->V_special_leave) {
                        $type = "special";
                        $max_count = $data_count->A_business;
                    }else if($data->V_sick_leave){
                        $type = "sick";
                        $max_count = $data_count->A_sick;
                    }else{
                        $type = "business";
                        $max_count = $data_count->A_special;
                    }

                    $member_wait = explode(" ", $member_wait);
                    array_pop($member_wait);
                    $allow = [];

                    $member_allow = $data->V_allow;
                    $member_allow = explode(" ", $member_allow);
                    array_pop($member_wait);
                    $wait = [];

                    foreach ($member_wait as $member) {
                        $sql = "SELECT * FROM member WHERE M_id = '$member'";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $allow[] = $run->getterResult();
                    }

                    foreach ($member_allow as $member) {
                        $sql = "SELECT * FROM member WHERE M_id = '$member'";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $wait[] = $run->getterResult();
                    }

                    $send[] = array(
                        'vacation' => $data,
                        'type' => $type,
                        'allow' => $allow,
                        'wait' => $wait,
                        'max_count' => $max_count
                    );
                }
                $response->getBody()->write(json_encode($send));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response->getBody()->write(json_encode("You not have leave list"));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(404);
            }
        });
};
