<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/data/request/leave/{vid}', function (Request $request, Response $response, array $args) {
        $vacation_id = $args['vid'];

        $sql = "SELECT * FROM vacation WHERE V_id = '$vacation_id'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $data_vacation = $run->getterResult();

        $type = $data_vacation->V_special ? 'special' : ($data_vacation->V_sick ? 'sick' : 'business');

        $member_wait = $data_vacation->V_wait;
        $member_wait = explode(" ", $member_wait);
        array_pop($member_wait);

        $member_allow = $data_vacation->V_allow;
        $member_allow = explode(" ", $member_allow);
        array_pop($member_allow);

        $mergedArray = array_merge($member_wait, $member_allow);

        sort($mergedArray);

        $data_send = [];

        foreach ($mergedArray as $member) {
            echo $member;
            $approve = in_array($member, $member_allow);

            $sql = "SELECT * FROM member WHERE M_id = '$member'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_member = $run->getterResult();

            $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $data_member->M_id";
            $run = new Get($sql, $response);
            $run->evaluate();
            $image_name = ($run->getterResult())->MI_image_name;

            $data_send[] = array(
                "image_name" => $image_name,
                "first_name" => $data_member->M_first_name,
                "approve" => $approve
            );
        }

        $send_data = array(
            'vacation' => $data_vacation,
            'type' => $type,
            'member' => $data_send,
        );

        $response->getBody()->write(json_encode($send_data));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};
