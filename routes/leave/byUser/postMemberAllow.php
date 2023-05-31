<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->post('/leave/post/member/allow', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_applicant_id = $data->member_applicant_id;
        $member_approve_id = $data->member_approve_id;
        $type_leave = $data->type_leave;

        $sql = "SELECT * FROM memberallow 
                WHERE SM_member_applicant_id = '$member_applicant_id' AND SM_member_approve_id = '$member_approve_id' 
                AND SM_type_leave = '$type_leave'";
        $run = new Get($sql, $response);
        $run->evaluate();
        if($run->getterCount()){
            $response->getBody()->write(json_encode("You choose the same member for the same type of leave"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        }else{
            $sql = "INSERT INTO memberallow (SM_member_applicant_id, SM_member_approve_id, SM_type_leave) 
                    VALUES ('$member_applicant_id', '$member_approve_id', '$type_leave')";
            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        }
    });
};
