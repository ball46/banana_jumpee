<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/history/time/temp/{token}', function (Request $request, Response $response, array $args) {
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

        $data = json_decode($request->getBody());
        $start_date = $data->start_date;
        $end_date = $data->end_date;

        $sql = "SELECT * FROM faceid WHERE F_member_id = $member_id AND 
                DATE(F_date) BETWEEN '$start_date' AND '$end_date'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        $array_data_face_id = $run->getterResult();

        $send = [];

        foreach ($array_data_face_id as $data_face_id) {
            $date = $data_face_id->F_date;
            $date = explode("-", $date);

            $year = $date[0];
            $month = ltrim($date[1], '0');
            $day = ltrim($date[2], '0');

            $data_date = array(
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'time' => $data_face_id->F_time,
            );

            $send[] = array(
                'date' => $data_date,
                'time' => $data_face_id->F_time,
                'temperature' => $data_face_id->F_temperature,
            );
        }

        $response->getBody()->write(json_encode($send));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};