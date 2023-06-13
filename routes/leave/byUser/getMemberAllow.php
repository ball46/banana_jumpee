<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->get('/leave/get/member/allow', function (Request $request, Response $response) {
        $member_id = (json_decode($request->getBody()))->member_id;

        $sql = "SELECT * FROM memberallow WHERE SM_member_applicant_id = '$member_id'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};
