<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/dow/date/work', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;
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
        $choose_date_name = $data->choose_date_name;
        $date_num = $data->date_choose;
        $update_by = $data->update_by;

        try {
            $sql = "UPDATE member SET M_profiling = '1' WHERE M_id = '$member_id'";

            $run = new Update($sql, $response);
            $run->evaluate();

            $sql = "INSERT INTO datework (D_member_id, D_start_date_work, D_end_date_work, D_start_time_work, 
                    D_end_time_work, D_monday, D_tuesday, D_wednesday, D_thursday, D_friday, D_saturday, 
                    D_choose_date_name, D_date_num, D_upd_by) 
                    VALUES ('$member_id', '$start_date', '$end_date', '$start_time', '$end_time', '$monday', '$tuesday', 
                    '$wednesday', '$thursday', '$friday', '$saturday', '$choose_date_name', '$date_num','$update_by')";

            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        } catch (PDOException $e) {
            $error = array(
                "Message" => $e->getMessage()
            );

            $this->response->getBody()->write(json_encode($error));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });
};