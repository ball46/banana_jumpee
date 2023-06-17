<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/search/bar', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_allow_id = $data->member_allow_id;
        $member_allow_id = implode(',', array_fill(0, count($member_allow_id), '?'));

        $data_send = [];

        $sql = "SELECT * FROM member WHERE M_id NOT IN ($member_allow_id)";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $array_member = $run->getterResult();
            foreach ($array_member as $member) {
                $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $member->M_id";
                $run = new Get($sql, $response);
                $run->evaluate();
                $image_name = ($run->getterResult())->MI_image_name;

                $data_send[] = array(
                    "image_name" => $image_name,
                    "username" => $member->M_username,
                    "display_name" => $member->M_display_name,
                );
            }

            $response->getBody()->write(json_encode($data_send));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } else {
            $response->getBody()->write(json_encode("You can not choose another in this leave type."));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    });
};
