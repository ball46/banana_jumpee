<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/image/user', function (Request $request, Response $response) {
        $member_id = (json_decode($request->getBody()))->member_id;
        $sql = "SELECT * FROM memberimage WHERE MI_member_id = '$member_id'";

        $run = new Get($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};