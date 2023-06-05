<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->post('/leave/add/special/leave', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;
        $title = $data->title;
        $detail = $data->detail;
        $start_date = $data->start_date;
        $end_date = $data->end_date;
        $start_time = $data->start_time;
        $end_time = $data->end_time;
        $day_special = $data->day_special;
        $admin = $data->admin;

        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $now_year = date("Y", $current_timestamp);

        if($admin) {
            $sql = "UPDATE countleave SET C_special_leave = C_special_leave + '$day_special', 
                    C_max_special_leave = C_max_special_leave + '$day_special'
                    WHERE C_member_id = '$member_id' AND C_year = '$now_year'";
            $run = new Update($sql, $response);
            $run->evaluate();

            $sql = "INSERT INTO specialleave (S_member_id, S_title, S_detail, S_start_date, S_end_date, S_start_time, 
                S_end_time, S_day_special) 
                VALUES ('$member_id', '$title', '$detail', '$start_date', '$end_date', '$start_time', '$end_time', 
                '$day_special')";
            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        }else{
            $response->getBody()->write(json_encode("You are not admin"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(403);
        }
    });
};