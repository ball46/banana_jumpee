<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/dow/date/work/{token}', function (Request $request, Response $response, array $args) {
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
        $start_time = $data->start_time;
        $end_time = $data->end_time;
        $date_name = $data->date_name;
        $choose_date_name = $data->choose_date_name;
        $date_num = $data->date_num;
        $update_by = $data->update_by;

        if(!$choose_date_name){
            $start_month = explode("-", $start_date);
            $end_month = explode("-", $end_date);
            if($start_month[1] != $end_month[1]){
                $response->getBody()->write(json_encode("choose date number must be in the same month."));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(400);
            }
        }

        $sql = "SELECT * FROM datework WHERE D_member_id = '$member_id' AND D_status = '1'";
        $run = new GetAll($sql, $response);
        $run->evaluate();
        if ($run->getterCount() != 0) {
            $result = $run->getterResult();
            foreach ($result as $row) {
                $last_date = $row->D_end_date_work;//this is the last date of all old profiling
                if ($last_date >= $start_date) {
                    $response->getBody()->write(json_encode("new profiling is overlap old profiling"));
                    return $response
                        ->withHeader('content-type', 'application/json')
                        ->withStatus(403);
                }
            }
        }

        $sql = "UPDATE member SET M_profiling = '1' WHERE M_id = '$member_id'";
        $run = new Update($sql, $response);
        $run->evaluate();

        $sql = "INSERT INTO datework (D_member_id, D_start_date_work, D_end_date_work, D_start_time_work, 
                    D_end_time_work, D_date_name, D_choose_date_name, D_date_num, D_upd_by) 
                    VALUES ('$member_id', '$start_date', '$end_date', '$start_time', '$end_time', '$date_name', 
                            '$choose_date_name', '$date_num','$update_by')";

        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};