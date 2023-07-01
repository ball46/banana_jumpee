<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/leave/post/member/allow', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $member_id = $data->member_id;
        $type_leave = $data->type_leave;

        $sql = "SELECT * FROM memberallow 
                WHERE MA_member_id = '$member_id' AND MA_type_leave = '$type_leave'";
        $run = new Get($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $response->getBody()->write(json_encode("You choose the same member for the same type of leave"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        } else {
            $sick_leave = $type_leave == "sick" ? 1 : 0;
            $sql = "SELECT * FROM vacation WHERE V_status = 1 AND V_sick_leave = $sick_leave AND V_count_allow > 0";
            $run = new GetAll($sql, $response);
            $run->evaluate();
            if ($run->getterCount()) {
                $data_vacation = $run->getterResult();
                foreach ($data_vacation as $data) {
                    $member_wait = $data->V_wait;
                    $member_wait = $member_wait . $member_id . " ";
                    $sql = "UPDATE vacation SET V_wait = '$member_wait' WHERE V_id = '$data->V_id'";
                    $run = new Update($sql, $response);
                    $run->evaluate();
                }
            }

            $sql = "INSERT INTO memberallow (MA_member_id, MA_type_leave) VALUES ('$member_id', '$type_leave')";
            $run = new Update($sql, $response);
            $run->evaluate();
            return $run->return();
        }
    });
};
