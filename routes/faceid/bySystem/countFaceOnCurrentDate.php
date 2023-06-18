<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/count/face/current/date', function (Request $request, Response $response) {
        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $now_date = date("Y-m-d", $current_timestamp);

        $sql = "SELECT * FROM faceid WHERE F_date = '$now_date' AND F_in_out = '1'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        $all = $run->getterCount();
        $normal = 0;
        $late = 0;
        $absent = 0;
        if($all){
            $data_current_date = $run->getterResult();
            foreach ($data_current_date as $data){
                if($data->F_work == "normal" || $data->F_work == "normal_leave" || $data->F_work == "OT"){
                    $normal++;
                }else if($data->F_work == "late"){
                    $late++;
                }else{
                    $absent++;
                }
            }
        }
        $data_current_date = array(
            'all' => $all,
            'normal' => $normal,
            'late' => $late,
            'absent' => $absent,
        );
        $response->getBody()->write(json_encode($data_current_date));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};