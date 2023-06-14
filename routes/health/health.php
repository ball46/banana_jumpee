<?php

use Slim\App;

return function (App $app) {
    //call post health
    $routes = require __DIR__ . '/postHealth.php';
    $routes($app);

    //call get health
    $routes = require __DIR__ . '/getCurrentHealth.php';
    $routes($app);

    //call get list history health
    $routes = require __DIR__ . '/getHistoryHealth.php';
    $routes($app);
};