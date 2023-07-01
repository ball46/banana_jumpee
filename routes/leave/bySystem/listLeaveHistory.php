<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/leave/list/history/{token}', function (Request $request, Response $response, array $args) {
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

        $sql = "SELECT * FROM vacation WHERE V_member_id = '$member_id' ORDER BY V_id DESC 
                    LIMIT $limit OFFSET $offset";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount()) {
            $data_leave = $run->getterResult();
            foreach ($data_leave as $data) {
                $date = $data->V_date_ask_to_leave;
                $use_special_leave = (bool)$data->V_special_leave;
                $date = explode("-", $date);
                $date_ask_to_leave = array(
                    'year' => $date[0],
                    'month' => ltrim($date[1], '0'),
                    'day' => ltrim($date[2], '0'),
                );

                $sql = "SELECT * FROM allowcount";
                $run = new Get($sql, $response);
                $run->evaluate();
                $data_count = $run->getterResult();

                if ($data->V_sick_leave) {
                    $type = "sick";
                    $max_count = $data_count->A_sick;
                } else {
                    $type = "business";
                    $max_count = $data_count->A_business;
                }

                $send[] = array(
                    'vid' => $data->V_id,
                    'date_ask_to_leave' => $date_ask_to_leave,
                    'vacation' => $data->V_title,
                    'type' => $type,
                    'use_special_or_not' => $use_special_leave,
                    'start_date' => $data->V_start_date,
                    'end_date' => $data->V_end_date,
                    'period_time' => $data->V_count_day,
                    'allow' => $max_count - $data->V_count_allow,
                    'max_count' => $max_count,
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
