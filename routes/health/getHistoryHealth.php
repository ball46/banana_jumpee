<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/health/get/list/history/{token}', function (Request $request, Response $response, array $args) {
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

        $data = json_decode($request->getBody());
        $start_date = $data->start_date;
        $end_date = $data->end_date;

        $sql = "SELECT * FROM health WHERE H_member_id = '$persona_id' AND 
                DATE(H_cr_date) BETWEEN '$start_date' AND '$end_date'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        $array_data_health = $run->getterResult();

        $send = [];

        foreach ($array_data_health as $data_health) {
            $date_time = $data_health->H_cr_date;
            $date_time = explode(" ", $date_time);
            $date = $date_time[0];
            $date = explode("-", $date);

            $year = $date[0];
            $month = ltrim($date[1], '0');
            $day = ltrim($date[2], '0');
            $time = $date_time[1];

            $data_date = array(
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'time' => $time,
            );

            $data_weight = array(
                'weight' => $data_health->H_weight,
                'ideal_weight' => $data_health->H_ideal_weight,
                'weight_control' => $data_health->H_weight_control
            );

            $data_fat = array(
                'fat_mass' => $data_health->H_fat_mass,
                'fat_rate' => $data_health->H_fat_rate,
                'visceral_fat' => $data_health->H_visceral_fat
            );

            $data_moisture = array(
                'all_moisture' => $data_health->H_all_moisture,
                'moisture' => $data_health->H_moisture
            );

            $data_protein = array(
                'protein' => $data_health->H_protein,
                'extracellular-fluid' => $data_health->H_extracellular_fluid,
                'intracellular_fluid' => $data_health->H_intracellular_fluid
            );

            $send[] = array(
                'date' => $data_date,
                'weight' => $data_weight,
                'temperature' => $data_health->H_temperature,
                'fat' => $data_fat,
                'basal_metabolism' => $data_health->H_basal_metabolism,
                'water' => $data_moisture,
                'skeletal_muscle' => $data_health->H_skeletal_muscle,
                'bone mineral' => $data_health->H_bone_mineral,
                'protein' => $data_protein,
                'inorganic_salt' => $data_health->H_inorganic_salt
            );
        }

        $response->getBody()->write(json_encode($send));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};