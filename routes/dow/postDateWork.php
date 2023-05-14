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
        $date_name = $data->date_name;
        $choose_date_name = $data->choose_date_name;
        $date_num = $data->date_choose;
        $update_by = $data->update_by;

        $sql = "SELECT * FROM datework WHERE D_member_id = '$member_id' AND D_status = '1'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount() != 0) {
            $result = $run->getterResult();
            foreach ($result as $row) {
                $last_date = $row->D_end_date_work;//this is the last date of all old profiling
                if ($last_date > $start_date) {
                    $response->getBody()->write(json_encode("new profiling is overlap old profiling"));
                    return $response
                        ->withHeader('content-type', 'application/json')
                        ->withStatus(403);
                }
            }
        }

        $sql = "UPDATE member SET M_profiling = '1' WHERE M_id = '$member_id'";
        $run = new Update($sql, $response);
        $run->evaluate();

        $sql = "INSERT INTO datework (D_member_id, D_start_date_work, D_end_date_work, D_start_time_work, 
                    D_end_time_work, D_date_name, D_choose_date_name, D_date_num, D_upd_by) 
                    VALUES ('$member_id', '$start_date', '$end_date', '$start_time', '$end_time', '$date_name', 
                            '$choose_date_name', '$date_num','$update_by')";

        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};