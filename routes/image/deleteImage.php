<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/image/delete', function (Request $request, Response $response){
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;

        $sql = "SELECT * FROM memberimage WHERE MI_member_id = '$member_id'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $result = $run->getterResult();

        $sql = "UPDATE memberimage SET MI_image_name = 'default_image.png' WHERE MI_id = '$result->MI_id'";
        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};