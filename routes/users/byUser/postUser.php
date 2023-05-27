<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/user/register', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $email = $data->email;
        $password = $data->password;
        $password = password_hash($password, PASSWORD_BCRYPT);
        $username = $data->username ?? "";
        $displayName = $data->displayname ?? $username;
        $firstName = $data->firstname ?? "";
        $lastName = $data->lastname ?? "";
        $updateBy = $data->updateby;
        $roleId = $data->roleid;
        $max_leave_id = $data->max_leave_id;
        $admin = $data->admin ?? false;

        if (!$email || !$password) {
            $error = array(
                "Message" => "Email and password are required fields."
            );
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        $sql = "INSERT INTO member (M_email, M_password, M_username, M_display_name, M_first_name, M_last_name, 
                    M_upd_by, M_role_id, M_max_leave_id, M_admin) 
                VALUES ('$email', '$password', '$username', '$displayName', '$firstName', '$lastName', 
                        '$updateBy', '$roleId', '$max_leave_id', '$admin')";

        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};