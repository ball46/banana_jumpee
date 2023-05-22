<?php

use Slim\App;

return function (App $app) {
    //call api users
    $routes = require __DIR__ . '/users/users.php';
    $routes($app);

    //call api roles
    $routes = require __DIR__ . '/roles/roles.php';
    $routes($app);

    //call api faceID
    $routes = require __DIR__ . '/faceid/face.php';
    $routes($app);

    //call api date of work
    $routes = require __DIR__ . '/dow/dow.php';
    $routes($app);

    //call api image member
    $routes = require __DIR__ . '/image/image.php';
    $routes($app);

    //call api leave
    $routes = require __DIR__ . '/leave/leave.php';
    $routes($app);
};