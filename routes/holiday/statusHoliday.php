<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/holiday/check/status', function (Request $request, Response $response) {
        //create time by php
        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $now_date = date("Y-m-d", $current_timestamp);

        $sql = "UPDATE holiday SET H_status = '0' WHERE H_status = '1' AND H_end_date < '$now_date'";
        $run = new Update($sql, $response);
        $run->evaluate();
        $run->return();
    });
};