<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/leave/admin/allow', function (Request $request, Response $response){
        $data = json_decode($request->getBody());
        $vacation_id = $data->vacation_id;
        $admin = $data->admin;

        if($admin) {
            $sql = "SELECT * FROM vacation WHERE V_id = '$vacation_id'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_vacation = $run->getterResult();
            $member_id = $data_vacation->V_member_id;
            $start_date = $data_vacation->V_start_date;
            $end_date = $data_vacation->V_end_date;
            if($data_vacation->V_allow > 1) {
                $sql = "UPDATE vacation SET V_allow = V_allow - '1' WHERE V_id = '$vacation_id'";
                $run = new Update($sql, $response);
                $run->evaluate();
                return $run->return();
            }else if($data_vacation->V_allow == 1){
                $sql = "UPDATE vacation SET V_allow = '0' WHERE V_id = '$vacation_id'";
                $run = new Update($sql, $response);
                $run->evaluate();

                $sql = "UPDATE member SET M_leave = '1' WHERE M_id = '$member_id'";
                $run = new Update($sql, $response);
                $run->evaluate();

                date_default_timezone_set('Asia/Bangkok');
                $current_timestamp = time();
                $now_date = date("Y-m-d", $current_timestamp);
                $now_time = date("H:i:s", $current_timestamp);
                $now_timestamp = date("Y-m-d H:i:s", $current_timestamp);
                if($now_date >= $start_date && $now_date <= $end_date) {
                    $sql = "SELECT * FROM faceid 
                            WHERE F_member_id = '$member_id' AND F_date IN ('$start_date','$end_date')";
                    $run = new GetAll($sql, $response);
                    $run->evaluate();
                    $result = $run->getterResult();

                    if($data_vacation->V_time_period != "all day"){
                        foreach ($result as $data){
                            $id = $data->id;
                            $temperature = $data->F_temperature;
                            $in_out = $data->F_in_out;
                            $device_ip = $data->F_device_ip;
                            $device_key = $data->F_device_key;
                            $date_name = $data->F_date_name;
                            $timestamp_by_device = $data->F_timestamp_by_device;

                            if($in_out && ($data->work == "normal" || $data->work == "late")){
                                $work = "normal_leave";
                                $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                    $date_name, $now_date, $now_time, $now_timestamp, $timestamp_by_device, $work, $id);
                                $cal->scan_again();
                                $sql = $cal->getterSQL();
                                $run = new Update($sql, $response);
                                $run->evaluate();
                            }else if(!$in_out && ($data->work == "saot" || $data->work == "normal")){
                                $work = "OT";
                                $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                    $date_name, $now_date, $now_time, $now_timestamp, $timestamp_by_device, $work, $id);
                                $cal->scan_again();
                                $sql = $cal->getterSQL();
                                $run = new Update($sql, $response);
                                $run->evaluate();
                            }else{
                                $sql = "DELETE FROM faceid WHERE F_id = '$id'";
                                $run = new Update($sql, $response);
                                $run->evaluate();
                            }
                        }
                    }else{
                        foreach ($result as $data){
                            $id = $data->id;
                            $temperature = $data->F_temperature;
                            $in_out = $data->F_in_out;
                            $device_ip = $data->F_device_ip;
                            $device_key = $data->F_device_key;
                            $date_name = $data->F_date_name;
                            $timestamp_by_device = $data->F_timestamp_by_device;
                            $work = $data->F_work != "absent" ? "OT" : "leave";

                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                    $date_name, $now_date, $now_time, $now_timestamp, $timestamp_by_device, $work, $id);
                            $cal->scan_again();
                            $sql = $cal->getterSQL();
                            $run = new Update($sql, $response);
                            $run->evaluate();
                        }
                    }
                }

                return $run->return();
            }else{
                $response->getBody()->write(json_encode("The number of admins has been reached as required."));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(304);
            }
        }else{
            $response->getBody()->write(json_encode("You are not admin"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(403);
        }
    });
};