<?php

use Slim\App;

return function (App $app) {
    //call leave work
    $routes = require __DIR__ . '/byUser/postLeave.php';
    $routes($app);

    //call check status leave
    $routes = require __DIR__ . '/bySystem/statusLeave.php';
    $routes($app);

    //call to update count leave
    $routes = require __DIR__ . '/byAdmin/updateMaxLeave.php';
    $routes($app);

    //call add special days
    $routes = require __DIR__ . '/byAdmin/addSpecialLeave.php';
    $routes($app);
};