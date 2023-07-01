<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/face/list/history/{token}', function (Request $request, Response $response, array $args) {
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
        $limit = $data->limit;
        $offset = $data->offset;

        $send = [];

        $sql = "SELECT * FROM faceid WHERE F_member_id = $member_id";
        $run = new Get($sql, $response);
        $run->evaluate();
        $first_day_to_work = ($run->getterResult())->F_date;

        $sql = "SELECT * FROM faceid WHERE F_member_id = '$member_id' ORDER BY F_id DESC 
                    LIMIT $limit OFFSET $offset";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $data_face_id = $run->getterResult();
            foreach ($data_face_id as $data) {
                $date = $data->F_date;
                $date = explode("-", $date);
                $date_face_scan = array(
                    'year' => $date[0],
                    'month' => ltrim($date[1], '0'),
                    'day' => ltrim($date[2], '0'),
                );

                $send[] = array(
                    'date_face_scan' => $date_face_scan,
                    'time_in' => $data->F_time_in,
                    'status_in' => $data->F_status_in,
                    'temperature_in' => $data->F_temperature_in,
                    'time_out' => $data->F_time_out,
                    'status_out' => $data->F_status_out,
                    'temperature_out' => $data->F_temperature_out,
                );
            }
            $send_data = array(
                'first_day_to_work' =>$first_day_to_work,
                'data' => $send
            );
            $response->getBody()->write(json_encode($send_data));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } else {
            $response->getBody()->write(json_encode("You not have leave list"));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    });
};
