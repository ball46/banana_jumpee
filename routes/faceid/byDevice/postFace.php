<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/face/scan', function (Request $request, Response $response) {
        //data obtained from the face scanner
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;
        $temperature = $data->temperature;
        $device_ip = $data->device_ip;
        $device_key = $data->device_key;
        $timestamp_by_device = $data->timestamp;//this is timestamp for the device
//        $timestamp_by_device = date("Y-m-d H:i:s", strtotime($timestamp / 1000));
        $device = explode(" ", $timestamp_by_device);
        $device_YMD = $device[0];
        $device_YMD_data = explode("-", $timestamp_by_device);
        $device_day = $device_YMD_data[2];
        $device_hour = $device[1];
        $device_date = new DateTime($device_YMD);
        $device_date_name = $device_date->format('D');

        //create time by php
        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $scan_date = date("Y-m-d", $current_timestamp);
        $scan_timestamp = date("Y-m-d H:i:s", $current_timestamp);

        //this to get row data in now day if it has it mean this member is start_work_scan
        $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' AND F_date = '$device_YMD'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $have_face_data_or_not = $run->getterCount();
        $data_face = $have_face_data_or_not ? $run->getterResult() : "";

        //this to get data member by member id to check this member it has profiling or leave or not
        $sql = "SELECT * FROM member WHERE M_id = '$member_id'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $data_member = $run->getterResult();

        //this creates day name
        $date_now = new DateTime($scan_date);
        $day_name = $date_now->format('D');//Mon, Tue, Wed, Thu, Fri, Sat, Sun

        //this data to use in normal case
        $role_id = $data_member->M_role_id;
        $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $data_role = $run->getterResult();
        //these are time to check work time
        $start_work_role = $data_role->R_start_work;
        $get_off_work_role = $data_role->R_get_off_work;
        $rest_days = explode(" ", $data_role->R_rest_day);
        //this role rest day pth to check today is rest day or not
        $OT_or_not = 0;
        foreach ($rest_days as $rest_day) {
            if ($day_name == $rest_day) {
                $OT_or_not = 1;
                break;
            }
        }

        //this is variable to use in this code
        $have_holiday_or_not = 0;

        //normal case
        $work = $OT_or_not ? "OT" :
            ($have_face_data_or_not ? ($device_hour >= $get_off_work_role ? "normal" : "saot") :
                ($device_hour <= $start_work_role ? "normal" :
                    ($device_hour <= $get_off_work_role ? "late" : "absent")));

        //profiling case
        if ($data_member->M_profiling) {
            $sql = "SELECT * FROM datework WHERE D_member_id = '$member_id'
                    AND '$device_YMD' BETWEEN D_start_date_work AND D_end_date_work";
            $run = new Get($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $data_date_work = $run->getterResult();
                $start_work_profiling = $data_date_work->D_start_time_work;
                $end_work_profiling = $data_date_work->D_end_time_work;
                if ($data_date_work->D_choose_date_name) {
                    $days = explode(" ", $data_date_work->D_date_name);
                    $date = $device_date_name;
                } else {
                    $days = explode(" ", $data_date_work->D_date_num);
                    $date = $device_day;
                }
                $work = "OT";
                foreach ($days as $day) {
                    if ($day == $date) {
                        $work = $have_face_data_or_not ? ($device_hour >= $end_work_profiling ? "normal" : "saot") :
                            ($device_hour <= $start_work_profiling ? "normal" :
                                ($device_hour <= $end_work_profiling ? "late" : "absent"));
                        break;
                    }
                }
            }
        }

        //leave day
        if ($data_member->M_leave) {
            $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id'
                    AND '$device_YMD' BETWEEN V_start_date AND V_end_date";
            $run = new Get($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $data_leave = $run->getterResult();
                $work = $data_leave->V_time_period != "all day" ? "normal_leave" : "OT";
            }
        }

        //holiday case
        $sql = "SELECT * FROM holiday WHERE '$device_YMD' BETWEEN H_start_date AND H_end_date";
        $run = new Get($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $have_holiday_or_not = 1;
            $work = "OT";
        }

        $cal = new Work($member_id, $temperature, $device_ip, $device_key, $device_date_name,
            $device_YMD, $device_hour, $scan_timestamp, $timestamp_by_device, $work, $have_face_data_or_not ?
                $data_face->F_id : 0);
        if ($device_YMD == $scan_date) {
            if ($have_face_data_or_not) {
                $cal->end_work_scan();
            } else {
                $cal->start_work_scan();
            }
        } else {
            //this part is to check in case face scan device is not connected network
            if ($have_face_data_or_not) {
                if ($data_face->F_device_ip_in == "by api") {
                    $cal->fix_start_work_scan();
                } else {
                    $cal->end_work_scan();
                }
            } else if ($have_holiday_or_not) {
                $cal->start_work_scan();
            }
        }
        $sql = $cal->getterSQL();

        $sql_log = "INSERT INTO faceidlog (F_member_id, F_temperature, F_device_ip, F_device_key, F_timestamp_by_device)
                    VALUES ('$member_id', '$temperature', '$device_ip', '$device_key', '$timestamp_by_device')";
        $run = new Update($sql_log, $response);
        $run->evaluate();
        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};