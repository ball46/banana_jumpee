<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/health/get/list/history', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());
        $persona_id = $data->persona_id;
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
                'ideal weight' => $data_health->H_ideal_weight,
                'weight control' => $data_health->H_weight_control
            );

            $data_fat = array(
                'fat mass' => $data_health->H_fat_mass,
                'fat rate' => $data_health->H_fat_rate,
                'visceral fat' => $data_health->H_visceral_fat
            );

            $data_moisture = array(
                'all moisture' => $data_health->H_all_moisture,
                'moisture' => $data_health->H_moisture
            );

            $data_protein = array(
                'protein' => $data_health->H_protein,
                'extracellular fluid' => $data_health->H_extracellular_fluid,
                'intracellular fluid' => $data_health->H_intracellular_fluid
            );

            $send[] = array(
                'date' => $data_date,
                'weight' => $data_weight,
                'temperature' => $data_health->H_temperature,
                'fat' => $data_fat,
                'basal metabolism' => $data_health->H_basal_metabolism,
                'water' => $data_moisture,
                'skeletal muscle' => $data_health->H_skeletal_muscle,
                'bone mineral' => $data_health->H_bone_mineral,
                'protein' => $data_protein,
                'inorganic salt' => $data_health->H_inorganic_salt
            );
        }

        $response->getBody()->write(json_encode($send));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    });
};