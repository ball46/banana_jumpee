<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/leave/new/post', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;
        $title = $data->title;
        $detail = $data->detail;
        $location = $data->location;
        $GPS = $data->GPS;
        $time_period = $data->time_period;
        $start_date = $data->start_date;
        $end_date = $data->end_date;
        $sick_leave = $data->sick_leave;
        $sick_file = $data->sick_file ?? NULL;

        $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id' AND V_status = '1'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount() != 0) {
            $result = $run->getterResult();
            foreach ($result as $row) {
                $last_date = $row->V_start_date;//this is the last date of all old profiling
                if ($last_date > $start_date) {
                    $response->getBody()->write(json_encode("your old vacation is overlap new vacation"));
                    return $response
                        ->withHeader('content-type', 'application/json')
                        ->withStatus(403);
                }
            }
        }

        $sql = "UPDATE member SET M_profiling = '1' WHERE M_id = '$member_id'";
        $run = new Update($sql, $response);
        $run->evaluate();

        $sql = "INSERT INTO vacation (V_member_id, V_title, V_detail, V_location, V_GPS, V_time_period, V_start_date, 
                V_end_date, V_sick_leave, V_sick_file) 
                VALUES ('$member_id', '$title', '$detail', '$location', '$GPS', '$time_period', '$start_date', 
                '$end_date','$sick_leave', '$sick_file')";

        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};