<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Firebase\JWT\JWT;

return function (App $app) {
    $app->get('/user/login/{email}/{password}', function (Request $request, Response $response, array $args) {
        $email = $args['email'];
        $password = $args['password'];
        $sql = "SELECT * FROM member WHERE M_email = '$email'";

        try {
            $db = new DB();
            $conn = $db->connect();

            $statement = $conn->query($sql);
            $result = $statement->fetch(PDO::FETCH_OBJ);

            if ($result->M_status == 1) {
                //create token
                $payload = array(
                    "admin" => $result->M_admin,
                    "email" => $result->M_email,
                    "username" => $result->M_username,
                    "display_name" => $result->M_display_name,
                    "first_name" => $result->M_first_name,
                    "last_name" => $result->M_last_name,
                    "role_id" => $result->M_role_id,
                );

                date_default_timezone_set('Asia/Bangkok');
                $current_timestamp = time();
                $date_string = date("Y-m-d H:i:s", $current_timestamp);

                $path = $_SERVER['HTTP_USER_AGENT'];

                $sql = "INSERT INTO loginlog (L_email_member, L_time_login, L_path) 
                    VALUES ('$result->M_email', '$date_string', '$path')";

                $statement = $conn->prepare($sql);
                $statement->execute();

                $jwt = JWT::encode($payload, "my_secret_key", 'HS256');

                $db = null;
                $response->getBody()->write(json_encode($jwt));

                if (password_verify($password, $result->M_password)) {
                    return $response
                        ->withHeader('content-type', 'application/json')
                        ->withStatus(200);
                } else {
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
        } catch (PDOException $e) {
            $error = array(
                "Message" => $e->getMessage()
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });
};