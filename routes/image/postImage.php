<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/image/default/add', function (Request $request, Response $response){
        $data = json_decode($request->getBody());
        $email = $data->email;

        $sql = "SELECT * FROM member WHERE M_email = '$email'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $result = $run->getterResult();

        $sql = "INSERT INTO memberimage (MI_member_id, MI_image_name) 
                VALUES ('$result->M_id', 'default_image.png')";
        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};