<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/count/month/{member_id}/{month}/{year}',
        function (Request $request, Response $response, array $args) {
            $member_id = $args['member_id'];
            $month = $args['month'];
            $year = $args['year'];

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

                $sql = "SELECT V_title FROM vacation WHERE '$dateYMD' BETWEEN V_start_date AND V_end_date";
                $run = new Get($sql, $response);
                $run->evaluate();
                $vacation_name = $run->getterCount() ? $run->getterResult()->V_title : "";

                $data_date[] = array(
                    'date' => $dateYMD,
                    'status' => $status,
                    'holiday' => $holiday_name,
                    'leave' => $vacation_name,
                );
            }

            $response->getBody()->write(json_encode($data_date));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        });
};