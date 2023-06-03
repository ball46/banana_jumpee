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
        $device_hour = $device[1];

        //create time by php
        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $scan_date = date("Y-m-d", $current_timestamp);
        $scan_time = date("H:i:s", $current_timestamp);
        $scan_timestamp = date("Y-m-d H:i:s", $current_timestamp);

        //this fetch last data to compare now data
        $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' ORDER BY F_id DESC LIMIT 1";
        $run = new Get($sql, $response);
        $run->evaluate();
        $data_history = $run->getterResult();
        $have_or_not = $run->getterCount();

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
        $rest_days = explode(" ", $data_role->R_rest_day);

        $work = "";
        $in_out = 1;
        $data_date_work = "";
        $start_work_profiling = "";
        $end_work_profiling = "";
        $start_date_profiling = "";
        $end_date_profiling = "";
        $have_leave = 0;

        if ($device_YMD == $scan_date) {
            if ($data_member->M_profiling) {
                //to get profiling information
                $sql = "SELECT * FROM datework WHERE D_member_id = '$member_id' AND D_status = '1'";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_date_work = $run->getterResult();
                $start_work_profiling = strtotime($data_date_work->D_start_time_work);
                $end_work_profiling = strtotime($data_date_work->D_end_time_work);
                $start_date_profiling = $data_date_work->D_start_date_work;
                $end_date_profiling = $data_date_work->D_end_date_work;
            }

            if ($data_member->M_leave) {
                $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id' AND V_status = '1'";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_leave = $run->getterResult();
                if ($scan_date >= $data_leave->V_start_date && $scan_date <= $data_leave->V_end_date) {
                    $have_leave = 1;
                    $work = $data_leave->V_time_period != "all day" ? "normal_leave" : "OT";
                }
            }

            $sql = "SELECT * FROM holiday 
                    WHERE H_status = '1' AND '$scan_date' BETWEEN H_start_date AND H_end_date";
            $run = new Get($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $work = "OT";
                if ($data_history->F_in_out && $data_history->F_date == $scan_date) {
                    $in_out = 0;
                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                        $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                    $cal->first_scan();
                } else if (!$data_history->F_in_out && $data_history->F_date == $scan_date) {
                    $in_out = 0;
                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                        $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work, $data_history->F_id);
                    $cal->scan_again();
                } else {
                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                        $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                    $cal->first_scan();
                }
                $sql = $cal->getterSQL();
            } else {
                if ($have_leave) {
                    if ($data_history->F_in_out && $data_history->F_date == $scan_date) {
                        $in_out = 0;
                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                            $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                        $cal->first_scan();
                    } else if (!$data_history->F_in_out && $data_history->F_date == $scan_date) {
                        $in_out = 0;
                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                            $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work, $data_history->F_id);
                        $cal->scan_again();
                    } else {
                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                            $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                        $cal->first_scan();
                    }
                    $sql = $cal->getterSQL();
                } else {
                    if ($have_or_not) {
                        //get date time for face id history
                        $last_date = date("Y-m-d", strtotime($data_history->F_date));

                        if ($data_member->M_profiling &&
                            $scan_date >= $start_date_profiling && $scan_date <= $end_date_profiling) {
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

                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $day_name, $scan_date, $scan_time, $scan_timestamp,
                                            $timestamp_by_device, $work);
                                        $cal->first_scan();
                                    } else {
                                        $in_out = 0;
                                        $work = $scan_time_ver_check >= $end_work_profiling ? "normal" : "saot";

                                        if ($data_history->F_in_out) {
                                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                                $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device,
                                                $work);
                                            $cal->first_scan();
                                        } else {
                                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                                $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device,
                                                $work, $data_history->F_id);
                                            $cal->scan_again();
                                        }
                                    }
                                    $sql = $cal->getterSQL();
                                    break;
                                } else {
                                    $count++;
                                }
                            }
                            //this is OT version
                            if ($count == $date_have) {
                                $work = "OT";
                                if ($scan_date > $last_date) {
                                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                        $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                                    $cal->first_scan();
                                } else {
                                    $in_out = 0;
                                    if ($data_history->F_in_out) {
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device,
                                            $work);
                                        $cal->first_scan();
                                    } else {
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device,
                                            $work, $data_history->F_id);
                                        $cal->scan_again();
                                    }
                                }
                                $sql = $cal->getterSQL();
                            }
                        } else {
                            $OT_or_not = 0;
                            foreach ($rest_days as $rest_day) {
                                if($day_name == $rest_day){
                                    $OT_or_not = 1;
                                    break;
                                }
                            }
                            if ($OT_or_not) {
                                $work = "OT";
                                if ($scan_date > $last_date) {
                                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                        $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                                    $cal->first_scan();
                                } else {
                                    $in_out = 0;
                                    if ($data_history->F_in_out) {
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device,
                                            $work);
                                        $cal->first_scan();
                                    } else {
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device,
                                            $work, $data_history->F_id);
                                        $cal->scan_again();
                                    }
                                }
                            } else {
                                if ($scan_date > $last_date) {
                                    $work = $scan_time_ver_check <= $start_work_role ? "normal" :
                                        ($scan_time_ver_check <= $get_off_work_role ? "late" : "absent");
                                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                        $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                                    $cal->first_scan();
                                } else {
                                    $in_out = 0;
                                    $work = $scan_time_ver_check >= $get_off_work_role ? "normal" : "saot";

                                    if ($data_history->F_in_out) {
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device,
                                            $work);
                                        $cal->first_scan();
                                    } else {
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $day_name, $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device,
                                            $work, $data_history->F_id);
                                        $cal->scan_again();
                                    }
                                }
                            }
                            $sql = $cal->getterSQL();
                        }
                    } else {
                        if ($data_member->M_profiling &&
                            $scan_date >= $start_date_profiling && $scan_date <= $end_date_profiling) {
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
                                        $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                                    $cal->first_scan();

                                    $sql = $cal->getterSQL();
                                } else {
                                    $count++;
                                }
                            }
                            //this is OT version
                            if ($count == $date_have) {
                                $work = "OT";
                                $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                    $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                                $cal->first_scan();
                                $sql = $cal->getterSQL();
                            }
                        } else {
                            $OT_or_not = 0;
                            foreach ($rest_days as $rest_day) {
                                if($day_name == $rest_day){
                                    $OT_or_not = 1;
                                    break;
                                }
                            }
                            $work = $OT_or_not ? "OT" :
                                ($scan_time_ver_check <= $start_work_role ? "normal" :
                                    ($scan_time_ver_check <= $get_off_work_role ? "late" : "absent"));

                            $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $day_name,
                                $scan_date, $scan_time, $scan_timestamp, $timestamp_by_device, $work);
                            $cal->first_scan();
                            $sql = $cal->getterSQL();
                        }
                    }
                }
            }
            $sql_log = "INSERT INTO faceidlog (F_member_id, F_temperature, F_in_out, F_device_ip, F_device_key, 
                        F_date_name, F_date, F_time, F_cr_date, F_timestamp_by_device, F_work)
                        VALUES ('$member_id', '$temperature', '$in_out', '$device_ip', '$device_key','$day_name',
                        '$scan_date', '$scan_time', '$scan_timestamp', '$timestamp_by_device', '$work')";
        } else {
            $device_date = new DateTime($device_YMD);
            $device_date_name = $device_date->format('D');
            $sql = "SELECT * FROM holiday 
                    WHERE H_status = '1' AND '$device_YMD' BETWEEN H_start_date AND H_end_date";
            $run = new Get($sql, $response);
            $run->evaluate();
            //holiday case
            if ($run->getterCount()) {
                $work = "OT";
                $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' AND F_date = '$device_YMD'";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_history = $run->getterResult();

                if ($data_history->F_in_out && $data_history->F_date == $device_YMD) {
                    $in_out = 0;
                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $device_date_name,
                        $device_YMD, $device_hour, $scan_timestamp, $timestamp_by_device, $work, $data_history->F_id);
                    $cal->first_scan();
                } else if (!$data_history->F_in_out && $data_history->F_date == $device_YMD) {
                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $device_date_name,
                        $device_YMD, $device_hour, $scan_timestamp, $timestamp_by_device, $work, $data_history->F_id);
                    $cal->scan_again();
                } else {
                    $in_out = 0;
                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $device_date_name,
                        $device_YMD, $device_hour, $scan_timestamp, $timestamp_by_device, $work, $data_history->F_id);
                    $cal->first_scan();
                }
                $sql = $cal->getterSQL();
            } else {
                $sql = "SELECT * FROM vacation WHERE '$device_YMD' BETWEEN V_start_date AND V_end_date";
                $run = new Get($sql, $response);
                $run->evaluate();
                //leave case
                if ($run->getterCount()) {
                    $data_leave = $run->getterResult();
                    $work = $data_leave->V_time_period != "all day" ? "normal_leave" : "OT";

                    $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' AND F_date = '$device_YMD'";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    $data_history = $run->getterResult();

                    if ($data_history->F_in_out && $data_history->F_date == $device_YMD) {
                        $in_out = 0;
                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $device_date_name,
                            $device_YMD, $device_hour, $scan_timestamp, $timestamp_by_device, $work, $data_history->F_id);
                        $cal->first_scan();
                    } else if (!$data_history->F_in_out && $data_history->F_date == $device_YMD) {
                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $device_date_name,
                            $device_YMD, $device_hour, $scan_timestamp, $timestamp_by_device, $work, $data_history->F_id);
                        $cal->scan_again();
                    } else {
                        $in_out = 0;
                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key, $device_date_name,
                            $device_YMD, $device_hour, $scan_timestamp, $timestamp_by_device, $work, $data_history->F_id);
                        $cal->first_scan();
                    }
                    $sql = $cal->getterSQL();
                } else {
                    $sql = "SELECT * FROM datework WHERE D_start_date_work <= '$device_YMD' 
                         AND D_end_date_work >= '$device_YMD'";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    $data_date_work = $run->getterResult();
                    //profiling case
                    if ($run->getterCount()) {
                        $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' AND F_date = '$device_YMD'";
                        $run = new GetAll($sql, $response);
                        $run->evaluate();
                        $data_history = $run->getterResult();
                        $count = 0;
                        if ($data_date_work->D_choose_date_name) {
                            $days = explode(" ", $data_date_work->D_date_name);
                            $date = $device_date_name;
                        } else {
                            $days = explode(" ", $data_date_work->D_date_num);
                            $data_date = explode("-", $device_YMD);
                            $date = $data_date[2];
                        }
                        $date_have = count($days);

                        if ($run->getterCount() == 2) {
                            foreach ($data_history as $data) {
                                if ($data->F_in_out) {
                                    continue;
                                } else {
                                    $in_out = 0;
                                    foreach ($days as $day) {
                                        if ($day == $date) {
                                            $work = $device_hour >= $data_date_work->D_end_time_work ? "normal" : "saot";
                                            break;
                                        } else {
                                            $count++;
                                        }
                                    }
                                    $work = $count == $date_have ? "OT" : $work;
                                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                        $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                        $timestamp_by_device, $work, $data->F_id);
                                    $cal->scan_again();
                                    $sql = $cal->getterSQL();
                                }
                            }
                        } else {
                            $data = $data_history[0];
                            foreach ($days as $day) {
                                if ($day == $date) {
                                    if ($device_hour <= $data_date_work->D_start_time_work) {
                                        $work = "normal";
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                            $timestamp_by_device, $work, $data->F_id);
                                        $cal->scan_again();
                                        $sql = $cal->getterSQL();
                                    } else {
                                        if ($data->F_in_out) {
                                            if ($data->F_work == "normal" || $data->F_work == "late") {
                                                $in_out = 0;
                                                $work = $device_hour >= $data_date_work->D_end_time_work ?
                                                    "normal" : "saot";
                                                $cal = new Work($member_id, $temperature, $in_out, $device_ip,
                                                    $device_key, $device_date_name, $device_YMD, $device_hour,
                                                    $scan_timestamp, $timestamp_by_device, $work);
                                                $cal->first_scan();
                                                $sql = $cal->getterSQL();
                                            } else if ($data->F_work == "absent") {
                                                $work = $device_hour >= $data_date_work->D_end_time_work ?
                                                    "absent" : "late";
                                                $cal = new Work($member_id, $temperature, $in_out, $device_ip,
                                                    $device_key, $device_date_name, $device_YMD, $device_hour,
                                                    $scan_timestamp, $timestamp_by_device, $work, $data->F_id);
                                                $cal->scan_again();
                                                $sql = $cal->getterSQL();
                                            }
                                        }
                                    }
                                    break;
                                } else {
                                    $count++;
                                }
                            }
                            //OT case version
                            if ($count == $date_have) {
                                if ($data->F_in_out) {
                                    $work = "OT";
                                    if ($data->F_work == "OT") {
                                        $in_out = 0;
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                            $timestamp_by_device, $work);
                                        $cal->first_scan();
                                    } else {
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                            $timestamp_by_device, $work, $data->F_id);
                                        $cal->scan_again();
                                    }
                                    $sql = $cal->getterSQL();
                                }
                            }
                        }
                    } else {
                        //role case
                        $data = $data_history[0];
                        //OT case version
                        $OT_or_not = 0;
                        foreach ($rest_days as $rest_day) {
                            if($device_date_name == $rest_day){
                                $OT_or_not = 1;
                                break;
                            }
                        }
                        if ($OT_or_not) {
                            $work = "OT";
                            if ($data_history->F_in_out) {
                                if ($data_history->F_work == "OT") {
                                    $in_out = 0;
                                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                        $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                        $timestamp_by_device, $work);
                                    $cal->first_scan();
                                    $sql = $cal->getterSQL();
                                } else if ($data_history->F_work == "rest day") {
                                    $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                        $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                        $timestamp_by_device, $work, $data->F_id);
                                    $cal->scan_again();
                                    $sql = $cal->getterSQL();
                                }
                            }
                        } else {
                            //normal case version
                            if ($device_hour <= $start_work_role) {
                                $work = "normal";
                                $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                    $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                    $timestamp_by_device, $work, $data->F_id);
                                $cal->scan_again();
                                $sql = $cal->getterSQL();
                            } else {
                                if ($data_history->F_in_out) {
                                    if ($data_history->F_work == "normal" || $data_history->F_work == "late") {
                                        $in_out = 0;
                                        $work = $device_hour >= $get_off_work_role ? "normal" : "saot";
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                            $timestamp_by_device, $work);
                                        $cal->first_scan();
                                        $sql = $cal->getterSQL();
                                    } else if ($data_history->F_work == "absent") {
                                        $work = $device_hour >= $get_off_work_role ? "absent" : "late";
                                        $cal = new Work($member_id, $temperature, $in_out, $device_ip, $device_key,
                                            $device_date_name, $device_YMD, $device_hour, $scan_timestamp,
                                            $timestamp_by_device, $work, $data->F_id);
                                        $cal->scan_again();
                                        $sql = $cal->getterSQL();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $sql_log = "INSERT INTO faceidlog (F_member_id, F_temperature, F_in_out, F_device_ip, F_device_key, 
                        F_date_name, F_date, F_time, F_cr_date, F_timestamp_by_device, F_work)
                        VALUES ('$member_id', '$temperature', '$in_out', '$device_ip', '$device_key','$device_date_name',
                        '$device_YMD', '$device_hour', '$scan_timestamp', '$timestamp_by_device', '$work')";
        }
        $run = new Update($sql_log, $response);
        $run->evaluate();
        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};