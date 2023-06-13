<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/show/leave/day', function (Request $request, Response $response) {
            $member_id = (json_decode($request->getBody()))->member_id;

            date_default_timezone_set('Asia/Bangkok');
            $current_timestamp = time();
            $now_year = date("Y", $current_timestamp);

            $absent = 0;


            $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' AND F_work = 'absent'
                        AND F_in_out = '1' AND YEAR(F_date) = '$now_year'";
            $run = new GetAll($sql, $response);
            if ($run->getterCount()) {
                $run->evaluate();
                $absent = $run->getterCount();
            }

            $sql = "SELECT * FROM member WHERE M_id = '$member_id'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $data_member = $run->getterResult();

            $sql = "SELECT * FROM maxleave WHERE ML_id = '$data_member->M_max_leave_id'";
            $run = new Get($sql, $response);
            $run->evaluate();
            $result = $run->getterResult();
            $max_business = $result->ML_business_leave;
            $max_sick = $result->ML_sick_leave;
            $max_special = $result->ML_special_leave;

            $sql = "SELECT * FROM countleave WHERE C_member_id = '$member_id' AND C_year = '$now_year'";
            $run = new Get($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $data_count_leave = $run->getterResult();
                $business = (int)($max_business - $data_count_leave->C_business_leave) . "/" . (int)$max_business;
                $sick = (int)($max_sick - $data_count_leave->C_sick_leave) . "/" . (int)$max_sick;
                $special = (int)($data_count_leave->C_max_special_leave - $data_count_leave->C_special_leave) . "/";
                $send = array(
                    'business' => $business,
                    'sick' => $sick,
                    'special' => $special . (int)$data_count_leave->C_max_special_leave,
                    'absent' => $absent
                );
            } else {
                $send = array(
                    'business' => "0/" . (int)$max_business,
                    'sick' => "0/" . (int)$max_sick,
                    'special' => "0/" . (int)$max_special,
                    'absent' => $absent
                );
            }
            $response->getBody()->write(json_encode($send));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        });
};
