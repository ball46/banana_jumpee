<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
//    $app->post('/dow/choose/date', function (Request $request, Response $response) {
//        $data = json_decode($request->getBody());
//        $email = $data->email;
//
//        date_default_timezone_set('Asia/Bangkok');
//        $current_timestamp = time();
//        $date_string = date("Y-m-d H:i:s", $current_timestamp);
//
//        $path = $_SERVER['HTTP_USER_AGENT'];
//
//        $sql = "INSERT INTO logoutlog (L_email_member, L_time_logout, L_path)
//                VALUES ('$email', '$date_string', '$path')";
//
//        $run = new Update($sql, $response);
//        return $run->evaluate();
//    });
};