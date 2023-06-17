<?php

use Slim\App;

return function (App $app) {
    //call to update count leave
    $routes = require __DIR__ . '/byAdmin/byAdmin.php';
    $routes($app);

    //call api in folder bySystem
    $routes = require __DIR__ . '/bySystem/bySystem.php';
    $routes($app);

    //call api in folder byUser
    $routes = require __DIR__ . '/byUser/byUser.php';
    $routes($app);
};
