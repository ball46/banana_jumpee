<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/leave/member/allow/{token}', function (Request $request, Response $response, array $args) {
        try {
            $token = jwt::decode($args['token'], new Key("my_secret_key", 'HS256'));
        }catch (Exception $e){
            $response->getBody()->write(json_encode(array(
                "error_message" => "Invalid token",
                "message" => $e->getMessage()
            )));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        }
        $member_allow_id = $token->id;

        $data = json_decode($request->getBody());
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

        if ($data_vacation->V_count_allow > 1) {
            $sql = "UPDATE vacation SET V_allow = '$member_allow', V_wait = '$member', 
                    V_count_allow = V_count_allow - '1' WHERE V_id = '$vacation_id'";
            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        }
        else if ($data_vacation->V_count_allow == 1) {
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
                $data_face_scan = $run->getterResult();

                if ($data_vacation->V_time_period != "all day") {
                    foreach ($data_face_scan as $data) {
                        $in = ($data->F_status_in == "normal" || $data->F_status_in == "late") ? "normal_leave" :
                            ($data->F_status_in == "absent" ? "absent" : "OT");
                        $cal = new Work($member_id, $data->F_temperature_in, $data->F_device_ip_in,
                            $data->F_device_key_in, $data->F_date_name, $now_date, $now_time, $now_timestamp,
                            $data->F_timestamp_by_device_in, $in, $data->F_id);
                        $cal->fix_start_work_scan();
                        $sql = $cal->getterSQL();
                        $run = new Update($sql, $response);
                        $run->evaluate();

                        $out = $data->F_status_out == "absent" ? null :
                            ($data->F_status_out == "normal" ? "OT" : "normal_leave");
                        $cal = new Work($member_id, $data->F_temperature_out, $data->F_device_ip_out,
                            $data->F_device_key_out, $data->F_date_name, $now_date, $now_time, $now_timestamp,
                            $data->F_timestamp_by_device_out, $out, $data->F_id);
                        $cal->end_work_scan();
                        $sql = $cal->getterSQL();
                        $run = new Update($sql, $response);
                        $run->evaluate();
                    }
                } else {
                    foreach ($data_face_scan as $data) {
                        $in = $data->F_status_in != "absent" ? "OT" : "leave";
                        $cal = new Work($member_id, $data->F_temperature_in, $data->F_device_ip_in,
                            $data->F_device_key_in, $data->F_date_name, $now_date, $now_time, $now_timestamp,
                            $data->F_timestamp_by_device_in, $in, $data->F_id);
                        $cal->fix_start_work_scan();
                        $sql = $cal->getterSQL();
                        $run = new Update($sql, $response);
                        $run->evaluate();

                        if($in != "leave") {
                            $out = $data->F_status_out != "absent" ? "OT" : "leave";
                            $cal = new Work($member_id, $data->F_temperature_out, $data->F_device_ip_out,
                                $data->F_device_key_out, $data->F_date_name, $now_date, $now_time, $now_timestamp,
                                $data->F_timestamp_by_device_out, $out, $data->F_id);
                            $cal->end_work_scan();
                            $sql = $cal->getterSQL();
                            $run = new Update($sql, $response);
                            $run->evaluate();
                        }
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