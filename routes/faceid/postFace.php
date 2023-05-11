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
        $timestamp = $data->timestamp;

        //create time by php
        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $scan_date = date("Y-m-d", $current_timestamp);
        $scan_time = date("H:i:s", $current_timestamp);
        $scan_timestamp = date("Y-m-d H:i:s", $current_timestamp);

        $ver1 = "";
        $ver2 = "";
        $role_id = "";

        try {
            $db = new DB();
            $conn = $db->connect();

            //this fetch last data to compare now data
            $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' ORDER BY F_id DESC LIMIT 1";
            $statement = $conn->query($sql);
            $result = $statement->fetch(PDO::FETCH_OBJ);

            $scan_date = DateTime::createFromFormat('Y-m-d', $scan_date);
            $scan_time = strtotime($scan_time);
            $date_now = new DateTime($scan_time);

            //this fetch to bring role id by member table
            $sql = "SELECT * FROM member WHERE M_id = '$member_id'";
            $statement = $conn->query($sql);
            $data_member = $statement->fetch(PDO::FETCH_OBJ);

            if($data_member->M_profiling_v1){
                echo "V1";
                $sql = "SELECT * FROM memberdateworkv1 WHERE MD_member_id = '$member_id'";
            }else if($data_member->M_profiling_v2){
                echo "V2";
            }else{
                $role_id = $data_member->M_role_id;
            }

            //it has data in table or not
            if ($result !== false) {
                //role path
                $last_date = DateTime::createFromFormat('Y-m-d', $result->F_date);
                $last_time = strtotime($result->F_time);

                $date_last = new DateTime($last_date);
                $interval = $date_last->diff($date_now);
                $period = $interval->days;

                if ($scan_date > $last_date) {
                    $in_out = 1;

                    //this fetch data about role id
                    $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
                    $statement = $conn->query($sql);
                    $data_role = $statement->fetch(PDO::FETCH_OBJ);
                    $start_work = strtotime($data_role->R_start_work);
                    $get_off_work = strtotime($data_role->R_get_off_work);

                    //delete
                    if (!($period <= 1 || ($result->F_date_name == "Sat" && date('D') == "Mon"))) {
                        $datesBetween = array();
                        $currentDate = clone $date_last;
                        while ($currentDate < $date_now) {
                            $currentDate->modify('+1 day');
                            $datesBetween[] = $currentDate->format('Y-m-d');
                        }
                        array_pop($datesBetween);
                        foreach ($datesBetween as $date) {
                            $dayOfWeek = $date->format('D');
                            if ($dayOfWeek == "Sun") continue;
                            $work = "absent";
                            $sql = "INSERT INTO faceid (F_member_id, F_temperature, F_in_out, F_device_ip,
                                    F_device_key, F_date_name, F_date, F_time, F_cr_date, F_timestamp_by_device, F_work) 
                                    VALUES ('$member_id', '$temperature', '$in_out', '$device_ip', '$device_key',
                                    '$dayOfWeek','$date', '$scan_time', '$scan_timestamp', '$timestamp', '$work')";
                            $statement_absent = $conn->prepare($sql);
                            $statement_absent->execute();
                        }
                    }
                    //
                    $work = $scan_time <= $start_work ? "normal" :
                        ($scan_time <= $get_off_work ? "late" : "absent");
                    $dayOfWeek = $date_now->format('D');
                    $sql = "INSERT INTO faceid (F_member_id, F_temperature, F_in_out, F_device_ip,
                            F_device_key, F_date_name, F_date, F_time, F_cr_date, F_timestamp_by_device, F_work) 
                            VALUES ('$member_id', '$temperature', '$in_out', '$device_ip', '$device_key',
                            '$dayOfWeek','$scan_date', '$scan_time', '$scan_timestamp', '$timestamp', '$work')";
                } else {
                    if ($scan_time >= $last_time) {
                        //it has 2 case get off work or scan again
                        $in_out = 0;

                        //this fetch data about role id
                        $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
                        $statement = $conn->query($sql);
                        $data_role = $statement->fetch(PDO::FETCH_OBJ);
                        $get_off_work = strtotime($data_role->R_get_off_work);

                        $work = $scan_time >= $get_off_work ? "normal" : "saot";

                        if ($result->F_in_out == 1) {
                            $dayOfWeek = $date_now->format('D');
                            $sql = "INSERT INTO faceid (F_member_id, F_temperature, F_in_out, F_device_ip,
                                    F_device_key, F_date_name, F_date, F_time, F_cr_date, F_timestamp_by_device, F_work) 
                                    VALUES ('$member_id', '$temperature', '$in_out', '$device_ip', '$device_key',
                                    '$dayOfWeek','$scan_date', '$scan_time', '$scan_timestamp', '$timestamp', '$work')";
                        } else {
                            $sql = "UPDATE faceid SET F_temperature = '$temperature', F_in_out = '$in_out', 
                                    F_device_ip = '$device_ip', F_device_key = '$device_key', F_time = '$scan_time',
                                    F_cr_date = '$scan_timestamp', F_timestamp_by_device = '$timestamp', 
                                    F_work = '$work' WHERE F_id = '$result->F_id'";
                        }

                    }
                }
            } else {
                $in_out = 1;

                //this fetch data about role id
                $sql = "SELECT * FROM role WHERE R_id = '$role_id'";
                $statement = $conn->query($sql);
                $data_role = $statement->fetch(PDO::FETCH_OBJ);
                $start_work = strtotime($data_role->R_start_work);
                $get_off_work = strtotime($data_role->R_get_off_work);

                $work = $scan_time <= $start_work ? "normal" :
                    ($scan_time <= $get_off_work ? "late" : "absent");
                $dayOfWeek = $date_now->format('D');
                $sql = "INSERT INTO faceid (F_member_id, F_temperature, F_in_out, F_device_ip,
                            F_device_key, F_date_name, F_date, F_time, F_cr_date, F_timestamp_by_device, F_work) 
                            VALUES ('$member_id', '$temperature', '$in_out', '$device_ip', '$device_key',
                            '$dayOfWeek','$scan_date', '$scan_time', '$scan_timestamp', '$timestamp', '$work')";
            }
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