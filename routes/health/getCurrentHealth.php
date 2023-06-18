<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/health/get/current/{token}', function (Request $request, Response $response, array $args) {
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
        $persona_id = $token->persona_id;

        $sql = "SELECT * FROM health WHERE H_member_id = '$persona_id' ORDER BY H_id DESC LIMIT 1";
        $run = new Get($sql, $response);
        $run->evaluate();
        $data_health = $run->getterResult();

        $date_time = $data_health->H_cr_date;
        $date_time = explode(" ", $date_time);
        $date = $date_time[0];
        $date = explode("-", $date);

        $year = $date[0];
        $month = ltrim($date[1], '0');
        $day = ltrim($date[2], '0');
        $time = $date_time[1];

        $send = array(
            'point_health' => $data_health->H_overall_rating,
            'bmi' => $data_health->H_BMI,
            'age' => $data_health->H_age,
            'height' => $data_health->H_height,
            'weight' => $data_health->H_weight,
            'heart_rate' => $data_health->H_heart_rate,
            'blood_pressure' => $data_health->H_blood_pressure_high . '/' . $data_health->H_blood_pressure_low,
            'temperature' => $data_health->H_temperature,
            'blood_oxygen' => $data_health->H_blood_oxygen,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'time' => $time,
        );

        $response->getBody()->write(json_encode($send));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};