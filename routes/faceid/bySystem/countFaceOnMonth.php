<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/count/month/{token}', function (Request $request, Response $response, array $args) {
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
        $member_id = $token->id;

        $data = json_decode($request->getBody());
        $month = $data->month;
        $year = $data->year;

        date_default_timezone_set('Asia/Bangkok');

        $data_date = [];
        $dateString = $year . "-" . $month;
        $start_date = $dateString . '-01';
        $end_date = date("Y-m-t", strtotime($dateString));

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end->modify('+1 day'); // Include the end date
        $interval = new DateInterval('P1D'); // 1 day interval
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $date) {
            $dateYMD = $date->format('Y-m-d');
            $dateY = $date->format('Y');
            $dateM = $date->format('m');
            $dateM = ltrim($dateM, '0');
            $dateD = $date->format('d');
            $dateD = ltrim($dateD, '0');
            $status = "none";

            //to get late data user in select day
            $data_late = [];
            $sql = "SELECT * FROM faceid WHERE F_date = '$dateYMD' AND F_status_in = 'late'";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            if($run->getterCount()){
                $data_face = $run->getterResult();
                foreach ($data_face as $data){
                    $sql = "SELECT * FROM member WHERE M_id = $data->F_member_id";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    $data_member = $run->getterResult();

                    $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $data_member->M_id";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    $image_name = ($run->getterResult())->MI_image_name;

                    $sql = "SELECT * FROM datework WHERE D_member_id = $data->F_member_id 
                        AND '$dateYMD' BETWEEN D_start_date_work AND D_end_date_work";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    if($run->getterCount()){
                        $data_profiling = $run->getterResult();
                        $time_in_system = DateTime::createFromFormat('H:i:s', $data_profiling->D_start_time_work);
                    }else {
                        $sql = "SELECT * FROM role WHERE R_id = $data_member->M_role_id";
                        $run = new Get($sql, $response);
                        $run->evaluate();
                        $data_role = $run->getterResult();

                        $time_in_system = DateTime::createFromFormat('H:i:s', $data_role->R_start_work);
                    }
                    $time_in_user = DateTime::createFromFormat('H:i:s', $data->F_time_in);
                    $interval = $time_in_user->diff($time_in_system);
                    $different_time = $interval->format('%H:%i:%s');

                    $data_late[] = array(
                        "image_name" => $image_name,
                        "display_name" => $data_member->M_display_name,
                        "first_name" => $data_member->M_first_name,
                        "time_in_member" => $data->F_time_in,
                        "different_time" => $different_time
                    );
                }
            }

            $sql = "SELECT * FROM faceid WHERE F_date = '$dateYMD' AND F_member_id = '$member_id'";
            $run = new Get($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $work = ($run->getterResult())->F_status_in;
                $status = $work == "absent" ? "absent" : ($work == "late" ? "late" : "normal");
            }

            $sql = "SELECT H_name FROM holiday WHERE '$dateYMD' BETWEEN H_start_date AND H_end_date";
            $run = new Get($sql, $response);
            $run->evaluate();
            $holiday_name = $run->getterCount() ? ($run->getterResult())->H_name : "";

            //to get leave data user in select day
            $data_leave = [];
            $sql = "SELECT * FROM vacation WHERE '$dateYMD' BETWEEN V_start_date AND V_end_date";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $data_vacation = $run->getterResult();
                foreach ($data_vacation as $data){
                    $type = $data->V_sick_leave ? 'sick' : 'business';
                    $special = (bool)$data->V_special_leave;
                    $sql = "SELECT * FROM member WHERE M_id = $data->V_member_id";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    $data_member = $run->getterResult();

                    $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $data_member->M_id";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    $image_name = ($run->getterResult())->MI_image_name;

                    $data_leave[] = array(
                        "image_name" => $image_name,
                        "display_name" => $data_member->M_display_name,
                        "first_name" => $data_member->M_first_name,
                        "type" => $type,
                        "use_special" => $special,
                        "title" => $data->V_title,
                        "time_period" => $data->V_time_period
                    );
                }
            }

            $data_date[] = array(
                'year' => $dateY,
                'month' => $dateM,
                'day' => $dateD,
                'status' => $status,
                'holiday' => $holiday_name,
                'member_late' => $data_late,
                'member_leave' => $data_leave,
            );
        }

        $response->getBody()->write(json_encode($data_date));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};