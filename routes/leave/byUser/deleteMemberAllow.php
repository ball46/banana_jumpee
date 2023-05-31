<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app){
    $app->delete('/leave/delete/member/allow', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $SM_id = $data->member_allow_id;

        $sql = "DELETE FROM role WHERE SM_id = '$SM_id'";
        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};
