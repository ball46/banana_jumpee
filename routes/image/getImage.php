<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/image/user/{member_id}', function (Request $request, Response $response, array $args) {
        $member_id = $args['member_id'];
        $sql = "SELECT * FROM memberimage WHERE MI_member_id = '$member_id'";

        $run = new Get($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};