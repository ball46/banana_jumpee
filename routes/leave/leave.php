<?php

use Slim\App;

return function (App $app) {
    //call leave work
    $routes = require __DIR__ . '/postLeave.php';
    $routes($app);

    //call check status leave
    $routes = require __DIR__ . '/statusLeave.php';
    $routes($app);

    //call to update count leave
    $routes = require __DIR__ . '/updateMaxLeave.php';
    $routes($app);

    //call add special days
    $routes = require __DIR__ . '/addSpecialLeave.php';
    $routes($app);
};