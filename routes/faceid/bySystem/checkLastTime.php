<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/face/check/last', function (Request $request, Response $response) {
        //create time by php
        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $scan_date = date("Y-m-d", $current_timestamp);
        $scan_time = date("H:i:s", $current_timestamp);
        $scan_timestamp = date("Y-m-d H:i:s", $current_timestamp);
        $date_now = new DateTime($scan_date);
        $day_name = $date_now->format('D');

        $sql = "SELECT * FROM member";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        $members = $run->getterResult();

        //to check today is holiday or not
        $sql = "SELECT * FROM holiday WHERE '$scan_date' BETWEEN H_start_date AND H_end_date";
        $run = new Get($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $response->getBody()->write(json_encode("Today is holiday"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(304);
        } else {
            foreach ($members as $member) {
                $member_id = $member->M_id;

                $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' AND F_date = '$scan_date'";
                $run = new GetAll($sql, $response);
                $run->evaluate();
                $have_data_face_or_not = $run->getterCount();
                $data_face = $have_data_face_or_not ? $run->getterResult() : "";
                if ($have_data_face_or_not && $data_face->F_time_out != null) {
                    continue;
                }

                //normal case
                $sql = "SELECT * FROM role WHERE R_id = '$member->M_role_id'";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_role = $run->getterResult();
                $rest_days = explode(" ", $data_role->R_rest_day);
                $rest_or_absent = 0;
                foreach ($rest_days as $rest_day) {
                    if ($day_name == $rest_day) {
                        $rest_or_absent = 1;
                        break;
                    }
                }
                $work = $have_data_face_or_not ? ($rest_or_absent ? "OT" : "absent") :
                    ($rest_or_absent ? "rest day" : "absent");

                //profiling case
                if ($member->M_profiling) {
                    $profiling_day_work = 0;
                    $sql = "SELECT * FROM datework WHERE D_member_id = '$member_id' 
                            AND '$scan_date' BETWEEN D_start_date_work AND D_end_date_work";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    if ($run->getterCount()) {
                        $data_date_work = $run->getterResult();
                        if ($data_date_work->D_choose_date_name) {
                            $days = explode(" ", $data_date_work->D_date_name);
                            $date = $day_name;
                        } else {
                            $days = explode(" ", $data_date_work->D_date_num);
                            $date = date("d", $current_timestamp);
                        }
                        foreach ($days as $day) {
                            if ($day == $date) {
                                $profiling_day_work = 1;
                                break;
                            }
                        }
                        $work = $have_data_face_or_not ? ($profiling_day_work ? "absent" : "OT") :
                            ($profiling_day_work ? "absent" : "profiling");
                    }
                }

                //leave case
                if ($member->M_leave) {
                    $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id' 
                            AND '$scan_date' BETWEEN V_start_date AND V_end_date";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    if ($run->getterCount()) {
                        $data_leave = $run->getterResult();
                        $work = $have_data_face_or_not ?
                            ($data_leave->V_time_period == "all day" ? "OT" : "normal_leave") :
                            ($data_leave->V_time_period == "all day" ? "leave" : "absent");
                    }
                }

                $cal = new Work($member_id, 0, "by api", "by api",
                    $day_name, $scan_date, $scan_time, $scan_timestamp, $scan_timestamp, $work);
                if ($have_data_face_or_not) {
                    $cal->end_work_scan();
                } else {
                    $cal->start_work_scan();
                }
                $sql = $cal->getterSQL();
                $run = new Update($sql, $response);
                $run->evaluate();
            }
            $response->getBody()->write(json_encode(true));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }
    });
};