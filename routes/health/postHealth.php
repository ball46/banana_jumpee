<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/health/post', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());

        $sql = $data->memberId == "-" ? "INSERT INTO healthlog " : "INSERT INTO health ";

        $sql .= "(H_member_id, H_session, H_sex, H_height, H_weight, H_ideal_weight, H_weight_control, H_BMI, 
                            H_blood_oxygen, H_blood_pressure_high, H_blood_pressure_low, H_heart_rate, H_temperature, 
                            H_fat_rate, H_fat_mass, H_basal_metabolism, H_moisture, H_moisture_score, H_skeletal_muscle,
                            H_skeletal_muscle_score, H_visceral_fat, H_visceral_fat_score, H_bone_mineral, 
                            H_bone_mineral_score, H_extracellular_fluid, H_all_moisture, H_intracellular_fluid, H_protein,
                            H_inorganic_salt, H_physical_age, H_age, H_overall_rating, H_device_key, H_cr_by,
                            H_timestamp_by_device, H_upd_by) 
                    VALUES ('$data->memberId', 'guest', '$data->sex', '$data->height', '$data->weight',
                        '$data->idealWeight', '$data->weightControl', '$data->bmi', '$data->bloodOxygen',
                        '$data->bpHigh', '$data->bpLow', '$data->heartRate',
                        '$data->temperature', '$data->fatRate', '$data->fatMass', '$data->basalMetabolism',
                        '$data->moisture', '$data->moistureScore', '$data->muscle',
                        '$data->muscleScore', '$data->visceralFat', '$data->visceralFatScore',
                        '$data->boneMineral', '$data->boneMineralScore', '$data->extracellularFluid',
                        '$data->allMoisture', '$data->intracellularFluid', '$data->protein', '$data->inorganicSalt',
                        '$data->physicalAge', '$data->age', '$data->overallRating', '$data->uuid', '',
                        '$data->sortedKey' ,'api')";

        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};