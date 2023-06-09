<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/health/post', function (Request $request, Response $response) {
        $data = json_decode($request->getBody());

        $sql = "INSERT INTO 
                health (H_member_id, H_session, H_sex, H_height, H_weight, H_ideal_weight, H_weight_control, H_BMI, 
                        H_blood_oxygen, H_blood_pressure_high, H_blood_pressure_low, H_heart_rate, H_temperature, 
                        H_fat_rate, H_fat_mass, H_basal_metabolism, H_moisture, H_moisture_score, H_skeletal_muscle,
                        H_skeletal_muscle_score, H_visceral_fat, H_visceral_fat_score, H_bone_mineral, 
                        H_bone_mineral_score, H_extracellular_fluid, H_all_moisture, H_intracellular_fluid, H_protein,
                        H_inoganic_salt, H_physical_age, H_age, H_overall_rating, H_device_id, H_cr_by, H_upd_by) 
                VALUES ('$data->member_id', '$data->session', '$data->sex', '$data->height', '$data->weight', 
                        '$data->ideal_weight', '$data->weight_control', '$data->bmi', '$data->blood_oxygen', 
                        '$data->blood_pressure_high', '$data->blood_pressure_low', '$data->heart_rate', 
                        '$data->temperature', '$data->fat_rate', '$data->fat_mass', '$data->basal_metabolism', 
                        '$data->moisture', '$data->moisture_score', '$data->skeletal_muscle', 
                        '$data->skeletal_muscle_score', '$data->visceral_fat', '$data->visceral_fat_score',
                        '$data->bone_mineral', '$data->bone_mineral_score', '$data->extracellular_fluid',
                        '$data->all_moisture', '$data->intracellular_fluid', '$data->protein', '$data->inoganic_salt',
                        '$data->physical_age', '$data->age', '$data->overall_rating', '$data->device_id', '$data->cr_by',
                        '$data->upd_by')";

        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};