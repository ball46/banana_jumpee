<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->delete('/leave/delete/member/allow', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $MA_id = $data->member_allow_id;

        $sql = "SELECT * FROM memberallow WHERE MA_id = '$MA_id'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $data_member_allow = $run->getterResult();
        $member_id = $data_member_allow->MA_member_id;
        $type = $data_member_allow->MA_type_leave;
        if ($type != "special") {
            $sql = "SELECT * FROM memberallow WHERE MA_type_leave = '$type'";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            $count_member_allow_type = $run->getterCount();

            $sql = "SELECT * FROM allowcount";
            $run = new Get($sql, $response);
            $run->evaluate();
            $result = $run->getterResult();
            $max_allow = $type == "business" ? $result->A_business : $result->A_sick;

            if ($count_member_allow_type == $max_allow) {
                $response->getBody()->write(json_encode("Your member allow in " . $type .
                    " type must more than " . $max_allow . " members"));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(304);
            }

            $sick_leave = $type == "sick" ? 1 : 0;
            $sql = "SELECT * FROM vacation WHERE V_status = 1 AND V_sick_leave = $sick_leave AND V_count_allow > 0
                    AND FIND_IN_SET('$member_id', REPLACE(V_allow, ' ', ',')) > 0";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $array_data = $run->getterResult();
                foreach ($array_data as $data) {
                    $member_allow = $data->V_allow;
                    $member_allow = explode(" ", $member_allow);
                    array_pop($member_allow);
                    $location = array_search($member_id, $member_allow);
                    array_splice($member_allow, $location, 1);
                    $member = "";
                    foreach ($member_allow as $data_allow) {
                        $member = $member . $data_allow . " ";
                    }
                    $sql = "UPDATE vacation SET V_allow = '$member', V_count_allow = V_count_allow + 1 
                            WHERE V_id = $data->V_id";
                    $run = new Update($sql, $response);
                    $run->evaluate();
                }
            }

            $sql = "SELECT * FROM vacation WHERE V_status = 1 AND V_sick_leave = $sick_leave AND V_count_allow > 0
                    AND FIND_IN_SET('$member_id', REPLACE(V_wait, ' ', ',')) > 0";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $array_data = $run->getterResult();
                foreach ($array_data as $data) {
                    $member_wait = $data->V_wait;
                    $member_wait = explode(" ", $member_wait);
                    array_pop($member_wait);
                    $location = array_search($member_id, $member_wait);
                    array_splice($member_wait, $location, 1);
                    $member = "";
                    foreach ($member_wait as $data_wait) {
                        $member = $member . $data_wait . " ";
                    }
                    $sql = "UPDATE vacation SET V_wait = '$member' WHERE V_id = $data->V_id";
                    $run = new Update($sql, $response);
                    $run->evaluate();
                }
            }
        }

        $sql = "DELETE FROM memberallow WHERE MA_id = '$MA_id'";
        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};
