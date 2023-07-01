<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/detail/in/date', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $dateYMD = $data->YMD;
        $data_send = [];

        $sql = "SELECT * FROM faceid WHERE F_date = '$dateYMD' AND F_status_in = 'late'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if($run->getterCount()){
            $data_face = $run->getterResult();
            foreach ($data_face as $data){
                $sql = "SELECT * FROM member WHERE M_id = $data->F_member_id";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_member = $run->getterResult();

                $sql = "SELECT * FROM memberimage WHERE  MI_member_id = $data_member->M_id";
                $run = new Get($sql, $response);
                $run->evaluate();
                $image_name = ($run->getterResult())->MI_image_name;


                $sql = "SELECT * FROM datework WHERE D_member_id = $data->F_member_id 
                        AND '$dateYMD' BETWEEN D_start_date_work AND D_end_date_work";
                $run = new Get($sql, $response);
                $run->evaluate();
                if($run->getterCount()){
                    $data_profiling = $run->getterResult();
                    $time_in_system = DateTime::createFromFormat('H:i:s', $data_profiling->D_start_time_work);
                }else {
                    $sql = "SELECT * FROM role WHERE R_id = $data_member->M_role_id";
                    $run = new Get($sql, $response);
                    $run->evaluate();
                    $data_role = $run->getterResult();

                    $time_in_system = DateTime::createFromFormat('H:i:s', $data_role->R_start_work);
                }
                $time_in_user = DateTime::createFromFormat('H:i:s', $data->F_time_in);
                $interval = $time_in_user->diff($time_in_system);
                $different_time = $interval->format('%H:%i:%s');

                $data_send[] = array(
                    "image_name" => $image_name,
                    "first_name" => $data_member->M_first_name,
                    "time_in_member" => $data->F_time_in,
                    "different_time" => $different_time
                );
            }
        }

        $response->getBody()->write(json_encode($data_send));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};