<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/dow/check/status/date/work', function (Request $request, Response $response) {
        //create time by php
        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $now_date = date("Y-m-d", $current_timestamp);

        $sql = "SELECT * FROM datework WHERE D_status = '1'";
        $run = new GetAll($sql, $response);
        $run->evaluate();

        if ($run->getterCount() != 0) {
            $count = 0;
            $result = $run->getterResult();
            foreach ($result as $row) {
                $last_date = $row->D_end_date_work;//this is the last date of all old profiling
                if ($last_date < $now_date) {
                    //this get to check how many total rows for this member id
                    $sql = "SELECT * FROM datework WHERE D_member_id = '$row->D_member_id' AND D_status = '1'";
                    $run = new GetAll($sql, $response);
                    $run->evaluate();
                    $status = $run->getterCount() == 1 ? 0 : 1;
                    //this to update M_profiling
                    $sql = "UPDATE member SET M_profiling = '$status' WHERE M_id = '$row->D_member_id'";
                    $run = new Update($sql, $response);
                    $run->evaluate();
                    //this to update status date work if $last_date < $now_date
                    $sql = "UPDATE datework SET D_status = '0' WHERE D_id = '$row->D_id'";
                    $run = new Update($sql, $response);
                    $run->evaluate();
                } else {
                    $count++;
                }
            }
            if ($count == $run->getterCount()) {
                $response->getBody()->write(json_encode("Not change status of profiling"));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(304);
            }
            $response->getBody()->write(json_encode(true));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } else {
            $response->getBody()->write(json_encode("Not have profiling to change status"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    });
};