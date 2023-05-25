<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/leave/new/post', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;
        $title = $data->title;
        $detail = $data->detail;
        $location = $data->location;
        $GPS = $data->GPS;
        $time_period = $data->time_period;
        $start_date = $data->start_date;
        $end_date = $data->end_date;
        $start_time = $time_period != "all day" ? $data->start_time : "00.00.00";
        $end_time = $time_period != "all day" ? $data->end_time : "00.00.00";
        $sick_leave = $data->sick_leave;
        $sick_file = $data->sick_file ?? NULL;
        $special_leave = $data->special_leave;

        $num = $time_period != "all day" ? 0.5 : 1.0;
        $can_leave = 1;

        $sql = "SELECT * FROM maxleave";
        $run = new Get($sql, $response);
        $run->evaluate();
        $result = $run->getterResult();
        $max_business = $result->M_business_leave;
        $max_sick = $result->M_sick_leave;
        $max_special = $result->M_special_leave;

        $sql = "SELECT * FROM countleave WHERE C_member_id = '$member_id'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $result = $run->getterResult();
        $id = $result->C_id;
        $business = $result->C_business_leave;
        $sick = $result->C_sick_leave;
        $special = $result->C_specia_leave;
        $sql_leave = "";

        if($sick_leave){
            if($sick + $num <= $max_sick){
                $sick += $num;
                $sql_leave = "UPDATE countleave SET C_sick_leave = '$sick' WHERE C_id = '$id'";
            }else{
                $can_leave = 0;
            }
        }else if($special_leave){
            if($special + $num <= $max_special){
                $special += $num;
                $sql_leave = "UPDATE countleave SET C_special_leave = '$special' WHERE C_id = '$id'";
            }else{
                $can_leave = 0;
            }
        }else{
            if($business + $num <= $max_business){
                $business += $num;
                $sql_leave = "UPDATE countleave SET C_business_leave = '$business' WHERE C_id = '$id'";
            }else{
                $can_leave = 0;
            }
        }

        if($can_leave) {
            $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id' AND V_status = '1'";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            if ($run->getterCount() != 0) {
                $result = $run->getterResult();
                foreach ($result as $row) {
                    $last_date = $row->V_start_date;//this is the last date of all old profiling
                    if ($last_date > $start_date) {
                        $response->getBody()->write(json_encode("your old vacation is overlap new vacation"));
                        return $response
                            ->withHeader('content-type', 'application/json')
                            ->withStatus(403);
                    }
                }
            }

            $run = new Update($sql_leave, $response);
            $run->evaluate();

            $sql = "UPDATE member SET M_leave = '1' WHERE M_id = '$member_id'";
            $run = new Update($sql, $response);
            $run->evaluate();

            $sql = "INSERT INTO vacation (V_member_id, V_title, V_detail, V_location, V_GPS, V_time_period, V_start_date, 
                V_end_date, V_start_time, V_end_time, V_sick_leave, V_sick_file, V_special_leave) 
                VALUES ('$member_id', '$title', '$detail', '$location', '$GPS', '$time_period', '$start_date', 
                '$end_date', '$start_time', '$end_time', '$sick_leave', '$sick_file', '$special_leave')";

            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        }else{
            $response->getBody()->write(json_encode("Your vacation was maximum for now"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        }
    });
};