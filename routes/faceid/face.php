<?php

use Slim\App;

return function (App $app) {
    //call api in folder bySystem
    $routes = require __DIR__ . '/byDevice/byDevice.php';
    $routes($app);

    //call api in folder bySystem
    $routes = require __DIR__ . '/bySystem/bySystem.php';
    $routes($app);
};