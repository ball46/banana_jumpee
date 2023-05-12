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
        $timestamp = $data->timestamp;//this is timestamp for the device

        //create time by php
        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $scan_date = date("Y-m-d", $current_timestamp);
        $scan_time = date("H:i:s", $current_timestamp);
        $scan_timestamp = date("Y-m-d H:i:s", $current_timestamp);

        try {
            //this fetch last data to compare now data
            $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' ORDER BY F_id DESC LIMIT 1";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_history = $run->getterResult();
            $have_or_not = $run->getterCount();

            //get date time for face id history
            $last_date = date("Y-m-d", $data_history->F_date);

            //this fetch to check profiling and bring role id by member table
            $sql = "SELECT * FROM member WHERE M_id = '$member_id'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_member = $run->getterResult();

            $date_now = new DateTime($scan_date);
            $day_name = $date_now->format('D');//Mon, Tue, Wed, Thu, Fri, Sat, Sun
            $scan_time_ver_check = strtotime($scan_time);

            //this to get data about role id
            $role_id = $data_member->M_role_id;
            $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_role = $run->getterResult();
            //these are time to check work time
            $start_work_role = strtotime($data_role->R_start_work);
            $get_off_work_role = strtotime($data_role->R_get_off_work);

            $work = "";
            $in_out = 1;
            $data_date_work = "";
            $start_work_profiling = "";
            $end_work_profiling = "";

            if($data_member->M_profiling){
                //to get profiling information
                $sql = "SELECT * FROM datework WHERE D_member_id = '$member_id' AND D_status = '1'";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_date_work = $run->getterResult();
                $start_work_profiling = strtotime($data_date_work->D_start_time_work);
                $end_work_profiling = strtotime($data_date_work->D_end_time_work);
            }

            if ($have_or_not) {
                if ($data_member->M_profiling) {
                    $count = 0;
                    if ($data_date_work->D_choose_date_name) {
                        $days = explode(" ", $data_date_work->D_date_name);
                        $date = $day_name;
                    } else {
                        $days = explode(" ", $data_date_work->D_date_num);
                        $date = date("d", $current_timestamp);
                    }
                    $date_have = count($days);
                    foreach ($days as $day) {
                        if ($day == $date) {
                            if ($scan_date > $last_date) {
                                $work = $scan_time_ver_check <= $start_work_profiling ? "normal" :
                                    ($scan_time_ver_check <= $end_work_profiling ? "late" : "absent");

                                $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                    $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                                $cal->first_scan();
                            } else {
                                $in_out = 0;
                                $work = $scan_time_ver_check >= $end_work_profiling ? "normal" : "saot";

                                if ($data_history->F_in_out) {
                                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                        $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                                    $cal->first_scan();
                                } else {
                                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                        $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp, $work,
                                        $data_history->F_id);
                                    $cal->scan_again();
                                }
                            }
                            $sql = $cal->getterSQL();
                        } else {
                            $count++;
                        }
                    }
                    //go to role version
                    if ($count == $date_have) {
                        if ($scan_date > $last_date) {
                            $work = $scan_time_ver_check <= $start_work_role ? "normal" :
                                ($scan_time_ver_check <= $get_off_work_role ? "late" : "absent");
                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                            $cal->first_scan();
                        } else {
                            $in_out = 0;
                            $work = $scan_time_ver_check >= $get_off_work_role ? "normal" : "saot";

                            if ($data_history->F_in_out) {
                                $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                    $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                                $cal->first_scan();
                            } else {
                                $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                    $scan_date, $scan_time, $scan_timestamp, $timestamp, $work, $data_history->F_id);
                                $cal->scan_again();
                            }
                        }
                        $sql = $cal->getterSQL();
                    }
                } else {
                    if ($scan_date > $last_date) {
                        //in this case most likely go to office to work
                        $work = $scan_time_ver_check <= $start_work_role ? "normal" :
                            ($scan_time_ver_check <= $get_off_work_role ? "late" : "absent");
                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                            $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                        $cal->first_scan();
                    } else {
                        //in this case have 2 possible ine way is scan after checkin and another way is scan again
                        $in_out = 0;
                        $work = $scan_time_ver_check >= $get_off_work_role ? "normal" : "saot";

                        if ($data_history->F_in_out) {
                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                            $cal->first_scan();
                        } else {
                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                $scan_date, $scan_time, $scan_timestamp, $timestamp, $work, $data_history->F_id);
                            $cal->scan_again();
                        }
                    }
                    $sql = $cal->getterSQL();
                }
            } else {
                if ($data_member->M_profiling) {
                    $count = 0;
                    if ($data_date_work->D_choose_date_name) {
                        $days = explode(" ", $data_date_work->D_date_name);
                        $date = $day_name;
                    } else {
                        $days = explode(" ", $data_date_work->D_date_num);
                        $date = date("d", $current_timestamp);
                    }
                    $date_have = count($days);
                    foreach ($days as $day) {
                        if ($day == $date) {
                            $work = $scan_time_ver_check <= $start_work_profiling ? "normal" :
                                ($scan_time_ver_check <= $end_work_profiling ? "late" : "absent");

                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                            $cal->first_scan();

                            $sql = $cal->getterSQL();
                        } else {
                            $count++;
                        }
                    }
                    //go to role version
                    if ($count == $date_have) {
                        $work = $scan_time_ver_check <= $start_work_role ? "normal" :
                            ($scan_time_ver_check <= $get_off_work_role ? "late" : "absent");
                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                            $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                        $cal->first_scan();
                        $sql = $cal->getterSQL();
                    }
                } else {
                    $work = $scan_time_ver_check <= $start_work_role ? "normal" :
                        ($scan_time_ver_check <= $get_off_work_role ? "late" : "absent");

                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                        $scan_date, $scan_time, $scan_timestamp, $timestamp, $work);
                    $cal->first_scan();
                    $sql = $cal->getterSQL();
                }
            }
            $sql_log = "INSERT INTO faceidlog (F_member_id, F_temperature, F_in_out, F_device_ip,F_device_key, 
                        F_date_name, F_date, F_time, F_cr_date, F_timestamp_by_device, F_work)
                        VALUES ('$member_id', '$temperature', '$in_out', '$device_ip', '$device_key','$day_name',
                        '$scan_date', '$scan_time', '$scan_timestamp', '$timestamp', '$work')";
            $run = new Update($sql_log, $response);
            $run->evaluate();

            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        } catch (PDOException $e) {
            $error = array(
                "Message" => $e->getMessage()
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });
};