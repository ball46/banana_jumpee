<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/user/update/data/{old_email}', function (Request $request, Response $response, array $args) {
        $old_email = $args['old_email'];
        $data = json_decode($request->getBody());
        $email = $data->email;
        $username = $data->username;
        $displayName = $data->displayname;
        $firstName = $data->firstname;
        $lastName = $data->lastname;
        $updateBy = $data->updateby;
        $updateDate = $data->updatedate;
        $roleId = $data->roleid;
        $admin = $data->admin;

        $sql = "UPDATE member SET M_email = '$email', M_username = '$username', M_display_name = '$displayName', 
                   M_first_name = '$firstName', M_last_name = '$lastName', M_upd_by = '$updateBy',
                   M_upd_date = '$updateDate', M_role_id = '$roleId', M_admin = '$admin' WHERE M_email = '$old_email'";

        $run = new Update($sql, $response);
        return $run->evaluate();
    });
};