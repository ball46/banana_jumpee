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

            $sql = "SELECT * FROM vacation WHERE '$dateYMD' BETWEEN V_start_date AND V_end_date";
            $run = new Get($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $data_vacation = $run->getterResult();
                $vacation_name = $data_vacation->V_title;
            } else {
                $data_vacation = "";
                $vacation_name = "";
            }

            $data_date[] = array(
                'year' => $dateY,
                'month' => $dateM,
                'day' => $dateD,
                'status' => $status,
                'holiday' => $holiday_name,
                'leave' => $vacation_name,
                'leave_data' => $data_vacation
            );
        }

        $response->getBody()->write(json_encode($data_date));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};