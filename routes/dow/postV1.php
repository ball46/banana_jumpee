<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/dow/date/of/week', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $start_date = $data->start_date;
        $end_date = $data->end_date;
        $start_time = $data->start_time;
        $end_time = $data->end_time;
        $monday = $data->monday;
        $tuesday = $data->tuesday;
        $wednesday = $data->wednesday;
        $thursday = $data->thursday;
        $friday = $data->friday;
        $saturday = $data->saturday;
        $update_by = $data->update_by;

        $sql = "INSERT INTO dateworkv1 (D_start_date_work, D_end_date_work, D_start_time_work, D_end_time_work, 
                       D_monday, D_tuesday, D_wednesday, D_thursday, D_friday, D_saturday, D_upd_by) 
                VALUES ('$start_date', '$end_date', '$start_time', '$end_time', 
                        '$monday', '$tuesday', '$wednesday', '$thursday', '$friday', '$saturday', '$update_by')";

        $run = new Update($sql, $response);
        return $run->evaluate();
    });
};