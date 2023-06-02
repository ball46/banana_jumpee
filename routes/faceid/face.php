<?php

use Slim\App;

return function (App $app) {
    //check in by scan face id
    $routes = require __DIR__ . '/postFace.php';
    $routes($app);

    //check last time
    $routes = require __DIR__ . '/checkLastTime.php';
    $routes($app);

    //check start work for every day
    $routes = require __DIR__ . '/countFaceOnCurrentDate.php';
    $routes($app);

    //check start work foreach members
    $routes = require __DIR__ . '/countFaceOnMonth.php';
    $routes($app);
};