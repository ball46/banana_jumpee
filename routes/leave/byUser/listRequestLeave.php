<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->get('/leave/list/request/leave', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;

        $send = [];

        $sql = "SELECT * FROM memberallow WHERE SM_member_approve_id = '$member_id'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if($run->getterCount()){
            $data_allow = $run->getterResult();
            foreach($data_allow as $data){
                $member_applicant_id = $data->SM_member_applicant_id;
                $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_applicant_id' AND V_status = '1'";
                $run = new GetAll($sql, $response);
                $run->evaluate();
                if($run->getterCount()){
                    $data_vacation = $run->getterResult();
                    foreach ($data_vacation as $vacation){
                        $vacation_id = $vacation->V_id;
                        $sql = "SELECT * FROM allowlog 
                                WHERE AL_vacation_id = '$vacation_id' AND AL_member_allow_id = '$member_id'";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        if($run->getterCount() == 0){
                            $send[] = $vacation;
                        }
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
