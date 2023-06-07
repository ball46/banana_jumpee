<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/leave/member/allow', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_allow_id = $data->member_allow_id;
        $vacation_id = $data->vacation_id;

        $sql_log = "INSERT INTO allowlog (AL_member_allow_id, AL_vacation_id) 
                    VALUES ('$member_allow_id', '$vacation_id')";
        $run = new Update($sql_log, $response);
        $run->evaluate();

        $sql = "SELECT * FROM vacation WHERE V_id = '$vacation_id'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $data_vacation = $run->getterResult();
        $member_id = $data_vacation->V_member_id;
        $start_date = $data_vacation->V_start_date;
        $end_date = $data_vacation->V_end_date;

        $member_wait = $data_vacation->V_wait;
        $member_wait = explode(" ", $member_wait);
        array_pop($member_wait);
        $location = array_search($member_allow_id, $member_wait);
        array_splice($member_wait, $location, 1);

        $member = "";
        foreach ($member_wait as $data){
            $member = $member . $data . " ";
        }
        $member_allow = $data_vacation->V_allow;
        $member_allow = $member_allow == "" ? $member_allow_id . " " : $member_allow . $member_allow_id . " ";

        if ($data_vacation->V_allow > 1) {
            $sql = "UPDATE vacation SET V_allow = '$member_allow', V_wait = '$member', 
                    V_count_allow = V_count_allow - '1' WHERE V_id = '$vacation_id'";
            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        } else if ($data_vacation->V_allow == 1) {
            $sql = "UPDATE vacation SET V_allow = '$member_allow', V_wait = '$member',
                    V_count_allow = '0' WHERE V_id = '$vacation_id'";
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
            if ($now_date >= $start_date && $now_date <= $end_date) {
                $sql = "SELECT * FROM faceid 
                        WHERE F_member_id = '$member_id' AND F_date BETWEEN '$start_date' AND '$end_date'";
                $run = new GetAll($sql, $response);
                $run->evaluate();
                $result = $run->getterResult();

                if ($data_vacation->V_time_period != "all day") {
                    foreach ($result as $data) {
                        $id = $data->F_id;
                        $temperature = $data->F_temperature;
                        $in_out = $data->F_in_out;
                        $device_ip = $data->F_device_ip;
                        $device_key = $data->F_device_key;
                        $date_name = $data->F_date_name;
                        $timestamp_by_device = $data->F_timestamp_by_device;

                        if ($in_out && ($data->work == "normal" || $data->work == "late")) {
                            $work = "normal_leave";
                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                $date_name, $now_date, $now_time, $now_timestamp, $timestamp_by_device, $work, $id);
                            $cal->scan_again();
                            $sql = $cal->getterSQL();
                            $run = new Update($sql, $response);
                            $run->evaluate();
                        } else if (!$in_out && ($data->work == "saot" || $data->work == "normal")) {
                            $work = "OT";
                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                $date_name, $now_date, $now_time, $now_timestamp, $timestamp_by_device, $work, $id);
                            $cal->scan_again();
                            $sql = $cal->getterSQL();
                            $run = new Update($sql, $response);
                            $run->evaluate();
                        } else {
                            $sql = "DELETE FROM faceid WHERE F_id = '$id'";
                            $run = new Update($sql, $response);
                            $run->evaluate();
                        }
                    }
                } else {
                    foreach ($result as $data) {
                        $id = $data->F_id;
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
        } else {
            $sql = "UPDATE vacation SET V_allow = '$member_allow', V_wait = '$member' WHERE V_id = '$vacation_id'";
            $run = new Update($sql, $response);
            $run->evaluate();

            $response->getBody()->write(json_encode("The number of admins has been reached as required."));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(304);
        }
    });
};