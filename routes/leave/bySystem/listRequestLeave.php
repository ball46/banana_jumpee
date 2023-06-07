<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->get('/leave/list/request/leave/{member_id}',
        function (Request $request, Response $response, array $args) {
        $member_id = $args['member_id'];

        $send = [];

        $sql = "SELECT * FROM memberallow WHERE SM_member_approve_id = '$member_id'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if($run->getterCount()){
            $data_allow = $run->getterResult();
            foreach($data_allow as $data){
                $member_applicant_id = $data->SM_member_applicant_id;
                $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_applicant_id' AND V_status = '1'
                        AND FIND_IN_SET('$member_id', V_wait) > 0";
                $run = new GetAll($sql, $response);
                $run->evaluate();
                if($run->getterCount()){
                    $data_vacation = $run->getterResult();
                    foreach ($data_vacation as $vacation){
                        $member_wait = $vacation->V_wait;
                        $member_wait = explode(" ", $member_wait);
                        array_pop($member_wait);
                        $allow = [];

                        $member_allow = $vacation->V_allow;
                        $member_allow = explode(" ", $member_allow);
                        array_pop($member_wait);
                        $wait = [];

                        foreach ($member_wait as $member){
                            $sql = "SELECT * FROM member WHERE M_id = '$member'";
                            $run = new Get($sql, $response);
                            $run->evaluate();
                            $allow[] = $run->getterResult();
                        }

                        foreach ($member_allow as $member){
                            $sql = "SELECT * FROM member WHERE M_id = '$member'";
                            $run = new Get($sql, $response);
                            $run->evaluate();
                            $wait[] = $run->getterResult();
                        }

                        $send[] = array(
                            'vacation' => $vacation,
                            'allow' => $allow,
                            'wait' => $wait
                        );
                    }
                }
            }
            $response->getBody()->write(json_encode($send));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }else{
            $response->getBody()->write(json_encode("You not have requested allow for leave"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    });
};
