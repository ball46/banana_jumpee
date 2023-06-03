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
        $work = "";
        $in_out = 1;

        $sql = "SELECT * FROM holiday 
                WHERE H_start_date <= '$scan_date' AND H_end_date >= '$scan_date' AND H_status = '1'";
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
                $role_id = $member->M_role_id;
                $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' AND F_date = '$scan_date' 
                    ORDER BY F_id DESC LIMIT 2";
                $run = new GetAll($sql, $response);
                $run->evaluate();
                if ($run->getterCount() == 2) {
                    continue;
                } else if ($run->getterCount() == 1) {
                    if ($member->M_leave) {
                        $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id' AND V_status = '1'";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $data_leave = $run->getterResult();
                        $start_date = $data_leave->V_start_date;
                        $end_date = $data_leave->V_end_date;
                        if ($scan_date >= $start_date && $scan_date <= $end_date) {
                            continue;
                        } else {
                            $in_out = 0;
                            $work = "absent";
                        }
                    } else {
                        $in_out = 0;
                        $work = "absent";
                    }
                } else {
                    if ($member->M_leave) {
                        $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id' AND V_status = '1'";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $data_leave = $run->getterResult();
                        $start_date = $data_leave->V_start_date;
                        $end_date = $data_leave->V_end_date;
                        if ($scan_date >= $start_date && $scan_date <= $end_date) {
                            $work = $data_leave->V_time_period == "all day" ? "leave" : "absent";
                        } else {
                            if ($member->M_profiling) {
                                $sql = "SELECT * FROM datework WHERE D_member_id = '$member_id' AND D_status = '1'";
                                $run = new Get($sql, $response);
                                $run->evaluate();
                                $data_date_work = $run->getterResult();
                                $start_date = $data_date_work->D_start_date_work;
                                $end_date = $data_date_work->D_end_date_work;
                                if ($scan_date >= $start_date && $scan_date <= $end_date) {
                                    if ($data_date_work->D_choose_date_name) {
                                        $days = explode(" ", $data_date_work->D_date_name);
                                        $date = $day_name;
                                    } else {
                                        $days = explode(" ", $data_date_work->D_date_num);
                                        $date = date("d", $current_timestamp);
                                    }
                                    foreach ($days as $day) {
                                        if ($day == $date) {
                                            $work = "absent";
                                            break;
                                        } else {
                                            $work = "profiling";
                                        }
                                    }
                                } else {
                                    $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
                                    $run = new Get($sql, $response);
                                    $run->evaluate();
                                    $data_role = $run->getterResult();
                                    $work = $data_role->R_rest_day == $day_name ? "rest day" : "absent";
                                }
                            } else {
                                $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
                                $run = new Get($sql, $response);
                                $run->evaluate();
                                $data_role = $run->getterResult();
                                $work = $data_role->R_rest_day == $day_name ? "rest day" : "absent";
                            }
                        }
                    } else if ($member->M_profiling) {
                        $sql = "SELECT * FROM datework WHERE D_member_id = '$member_id' AND D_status = '1'";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $data_date_work = $run->getterResult();
                        $start_date = $data_date_work->D_start_date_work;
                        $end_date = $data_date_work->D_end_date_work;
                        if ($scan_date >= $start_date && $scan_date <= $end_date) {
                            if ($data_date_work->D_choose_date_name) {
                                $days = explode(" ", $data_date_work->D_date_name);
                                $date = $day_name;
                            } else {
                                $days = explode(" ", $data_date_work->D_date_num);
                                $date = date("d", $current_timestamp);
                            }
                            foreach ($days as $day) {
                                if ($day == $date) {
                                    $work = "absent";
                                    break;
                                } else {
                                    $work = "profiling";
                                }
                            }
                        } else {
                            $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
                            $run = new Get($sql, $response);
                            $run->evaluate();
                            $data_role = $run->getterResult();
                            $work = $data_role->R_rest_day == $day_name ? "rest day" : "absent";
                        }
                    } else {
                        $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $data_role = $run->getterResult();
                        $work = $data_role->R_rest_day == $day_name ? "rest day" : "absent";
                    }
                    $cal = new Work($member_id, 0, $in_out, "by api", "by api",
                        $day_name, $scan_date, $scan_time, $scan_timestamp, $scan_timestamp, $work);
                    $cal->first_scan();
                    $sql = $cal->getterSQL();
                    $run = new Update($sql, $response);
                    $run->evaluate();
                }
            }
            $response->getBody()->write(json_encode(true));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }
    });
};