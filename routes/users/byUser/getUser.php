<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Firebase\JWT\JWT;

return function (App $app) {
    $app->get('/user/login', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $email = $data->email;
        $password = $data->password;

        $sql = "SELECT * FROM member WHERE M_email = '$email'";
        $run = new Get($sql, $response);
        $run->evaluate();
        if ($run->getterCount() == 0) {
            $response->getBody()->write(json_encode("SQL not found"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
        $result = $run->getterResult();

        if ($result->M_status == 1) {

            $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $result->M_id";
            $run = new Get($sql, $response);
            $run->evaluate();
            $image_name = ($run->getterResult())->MI_image_name;
            //create token
            $payload = array(
                "id" => $result->M_id,
                "admin" => $result->M_admin,
                "role_id" => $result->M_role_id,
                "persona_id" => $result->M_persona_id
            );

            $data_send = array(
                "image_name" => $image_name,
                "admin" => $result->M_admin == 1,
                "username" => $result->M_username,
                "display_name" => $result->M_display_name,
            );

            date_default_timezone_set('Asia/Bangkok');
            $current_timestamp = time();
            $date_string = date("Y-m-d H:i:s", $current_timestamp);

            $path = $_SERVER['HTTP_USER_AGENT'];

            $sql = "INSERT INTO loginlog (L_email_member, L_time_login, L_path) 
                    VALUES ('$result->M_email', '$date_string', '$path')";

            $run = new Update($sql, $response);
            $run->evaluate();

            $jwt = JWT::encode($payload, "my_secret_key", 'HS256');

            if (password_verify($password, $result->M_password)) {
                $send = array(
                    'token' => $jwt,
                    'data' => $data_send
                );
                $response->getBody()->write(json_encode($send));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response->getBody()->write(json_encode("password is not correct"));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(401);
            }
        } else {
            $response->getBody()->write(json_encode("This account is not authorized"));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(403);
        }
    });
};