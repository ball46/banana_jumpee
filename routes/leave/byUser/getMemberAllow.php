<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->get('/leave/get/member/allow/{member_id}', function (Request $request, Response $response, array $args) {
        $member_id = $args['member_id'];

        $sql = "SELECT * FROM memberallow WHERE SM_member_applicant_id = '$member_id'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};
