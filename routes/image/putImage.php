<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->put('/image/change/to/new', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;
        $imageName = $data->imageName;

        $sql = "UPDATE memberimage SET MI_image_name = '$imageName' WHERE MI_member_id = '$member_id'";
        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};