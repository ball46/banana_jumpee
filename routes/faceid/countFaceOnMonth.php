<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/count/month', function (Request $request, Response $response) {
            $data = json_decode($request->getBody());
            $member_id = $data->member_id;
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

                $sql = "SELECT F_work FROM faceid WHERE F_date = '$dateYMD' AND F_in_out = '1' 
                        AND F_member_id = '$member_id'";
                $run = new Get($sql, $response);
                $run->evaluate();
                if($run->getterCount()) {
                    $work = ($run->getterResult())->F_work;
                    $status = $work == "absent" ? "absent" : ($work == "late" ? "late" : "normal");
                }

                $sql = "SELECT H_name FROM holiday WHERE '$dateYMD' BETWEEN H_start_date AND H_end_date";
                $run = new Get($sql, $response);
                $run->evaluate();
                $holiday_name = $run->getterCount() ? ($run->getterResult())->H_name : "";

                $sql = "SELECT * FROM vacation WHERE '$dateYMD' BETWEEN V_start_date AND V_end_date";
                $run = new Get($sql, $response);
                $run->evaluate();
                if($run->getterCount()){
                    $data_vacation = $run->getterResult();
                    $vacation_name = $data_vacation->V_title;
                }else{
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
                    'leave data' => $data_vacation
                );
            }

            $response->getBody()->write(json_encode($data_date));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        });
};