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

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end->modify('+1 day'); // Include the end date
        $interval = new DateInterval('P1D'); // 1 day interval
        $period = new DatePeriod($start, $interval, $end);
        $day = 0;
        foreach ($period as $date) {
            if ($date->format('N') != 7) { // Exclude Sundays (1 for Monday, 7 for Sunday)
                $day++;
            }
        }

        $num = $time_period != "all day" ? 0.5 : 1.0;
        $day *= $num;
        $can_leave = 1;

        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $now_year = date("Y", $current_timestamp);

        $sql = "SELECT * FROM countleave WHERE C_member_id = '$member_id' AND C_year = '$now_year'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $sql_leave = "";
        $sql_status = "";
        $change = 0;

        if ($run->getterCount()) {
            $result = $run->getterResult();
            $id = $result->C_id;
            $business = $result->C_business_leave;
            $sick = $result->C_sick_leave;
            $special = $result->C_special_leave;

            if ($special_leave) {
                if ($special >= $day) {
                    $special -= $day;
                    $sql_leave = "UPDATE countleave SET C_special_leave = '$special' WHERE C_id = '$id'";
                } else {
                    $can_leave = 0;
                }
            } else if ($sick_leave) {
                if ($sick >= $day) {
                    $sick -= $day;
                    $sql_leave = "UPDATE countleave SET C_sick_leave = '$sick' WHERE C_id = '$id'";
                } else {
                    $can_leave = 0;
                }
            } else {
                if ($business >= $day) {
                    $business -= $day;
                    $sql_leave = "UPDATE countleave SET C_business_leave = '$business' WHERE C_id = '$id'";
                } else {
                    $can_leave = 0;
                }
            }
        }
        else {
            $sql = "SELECT * FROM countleave WHERE C_member_id = '$member_id' AND C_status = '1'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $old_special_leave = 0.0;
            if ($run->getterCount()) {
                $data_leave = $run->getterResult();
                $old_special_leave = $data_leave->C_special_leave;
                $sql_status = "UPDATE countleave SET C_status = '0' WHERE C_id = '$data_leave->C_id'";
                $change = 1;
            }

            $sql = "SELECT * FROM member WHERE M_id = '$member_id'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_member = $run->getterResult();

            $sql = "SELECT * FROM maxleave WHERE ML_id = '$data_member->M_max_leave_id'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $result = $run->getterResult();
            $max_business = $result->ML_business_leave;
            $max_sick = $result->ML_sick_leave;
            $max_special = $result->ML_special_leave;

            if ($special_leave) {
                if ($day <= $max_special + $old_special_leave) {
                    $special = ($max_special + $old_special_leave) - $day;
                    $sql_leave = "INSERT INTO countleave (C_member_id, C_business_leave, C_sick_leave, C_special_leave,
                                    C_year) 
                                    VALUES ('$member_id', '$max_business', '$max_sick', '$special', '$now_year')";
                } else {
                    $can_leave = 0;
                }
            } else if ($sick_leave) {
                if ($day <= $max_special) {
                    $sick = $max_sick - $day;
                    $sql_leave = "INSERT INTO countleave (C_member_id, C_business_leave, C_sick_leave, C_special_leave,
                                    C_year) 
                                    VALUES ('$member_id', '$max_business', '$sick', '$max_special', '$now_year')";
                } else {
                    $can_leave = 0;
                }
            } else {
                if ($day <= $max_business) {
                    $business = $max_business - $day;
                    $sql_leave = "INSERT INTO countleave (C_member_id, C_business_leave, C_sick_leave, C_special_leave,
                                    C_year) 
                                    VALUES ('$member_id', '$business', '$max_sick', '$max_special', '$now_year')";
                } else {
                    $can_leave = 0;
                }
            }
        }

        if ($can_leave) {
            $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id' AND V_status = '1'";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            if ($run->getterCount() != 0) {
                $result = $run->getterResult();
                foreach ($result as $row) {
                    $last_date = $row->V_start_date;//this is the last date of all old profiling
                    if ($last_date >= $start_date) {
                        $response->getBody()->write(json_encode("your old vacation is overlap new vacation"));
                        return $response
                            ->withHeader('content-type', 'application/json')
                            ->withStatus(403);
                    }
                }
            }

            if($change) {
                $run = new Update($sql_status, $response);
                $run->evaluate();
            }

            $run = new Update($sql_leave, $response);
            $run->evaluate();

            $sql = "SELECT * FROM allowcount";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_allow = $run->getterResult();
            if($special_leave){
                $allow_count = $data_allow->A_special;
            }else if($sick_leave){
                $allow_count = $data_allow->A_sick;
            }else{
                $allow_count = $data_allow->A_business;
            }

            $sql = "INSERT INTO vacation (V_member_id, V_title, V_detail, V_location, V_GPS, V_time_period, 
                    V_start_date, V_end_date, V_start_time, V_end_time, V_sick_leave, V_sick_file, V_special_leave, 
                    V_allow) 
                    VALUES ('$member_id', '$title', '$detail', '$location', '$GPS', '$time_period', '$start_date', 
                    '$end_date', '$start_time', '$end_time', '$sick_leave', '$sick_file', '$special_leave', 
                    '$allow_count')";

            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        } else {
            $response->getBody()->write(json_encode("Your vacation was over limit"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        }
    });
};