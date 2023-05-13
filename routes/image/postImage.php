<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->post('/image/default/add', function (Request $request, Response $response){
        $data = $request->getParsedBody();
        $email = $data['email'];

        $sql = "SELECT * FROM member WHERE M_email = '$email'";
        $run = new Get($sql, $response);
        $run->evaluate();
        $result = $run->getterResult();

        //create a new folder for the image member
        $directoryPath = __DIR__ . '/album/' . $result->M_id;
        mkdir($directoryPath, 0777, true);

        //use default image settings
        $default_image = __DIR__ . '/album/default_image.png';

        $sql = "INSERT INTO memberimage (MI_member_id, MI_image_name, MI_image) 
                VALUES ('$result->M_id', 'default_image.png', '$default_image')";
        $run = new Update($sql, $response);
        $run->evaluate();
        return $run->return();
    });
};