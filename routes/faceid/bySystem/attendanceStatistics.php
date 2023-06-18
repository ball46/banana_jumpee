<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/attendance/statistic/{token}', function (Request $request, Response $response, array $args) {
        try {
            $token = jwt::decode($args['token'], new Key("my_secret_key", 'HS256'));
        }catch (Exception $e){
            $response->getBody()->write(json_encode(array(
                "error_message" => "Invalid token",
                "message" => $e->getMessage()
            )));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        }
        $member_id = $token->id;

        date_default_timezone_set('Asia/Bangkok');
        $current_timestamp = time();
        $now_month = date("m", $current_timestamp);
        $now_year = date("Y", $current_timestamp);

        $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' 
                AND MONTH(F_date) = '$now_month' AND YEAR(F_date) = '$now_year'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if($run->getterCount()) {
            $date_of_entry = $run->getterCount();
            $absent = 0;
            $late = 0;
            $normal = 0;

            $array_data_face = $run->getterResult();
            foreach ($array_data_face as $data_face){
                $work = $data_face->F_work;
                if($work == "absent"){
                    $absent++;
                }else if($work == "late"){
                    $late++;
                }else{
                    $normal++;
                }
            }

            $send = array(
                'num_of_month' => $now_month,
                'date_of_entry' => $date_of_entry,
                'normal' => $normal,
                'late' => $late,
                'absent' => $absent
            );
            $response->getBody()->write(json_encode($send));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }else{
            $response->getBody()->write(json_encode("You not have data attendant in this month."));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    });
};