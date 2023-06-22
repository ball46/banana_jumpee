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
                    'start_work' => $data->F_start_work,
                    'start_status' => $data->F_start_status,
                    'start_temperature' => $data->F_start_temperature,
                    'end_work' => $data->F_end_work,
                    'end_status' => $data->F_end_status,
                    'end_temperature' => $data->F_end_temperature,
                );
            }
            $response->getBody()->write(json_encode($send));
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
