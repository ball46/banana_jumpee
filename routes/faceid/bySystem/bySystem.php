<?php

use Slim\App;

return function (App $app) {
    //check last time
    $routes = require __DIR__ . '/checkLastTime.php';
    $routes($app);

    //check start work for every day
    $routes = require __DIR__ . '/countFaceOnCurrentDate.php';
    $routes($app);

    //check start work foreach members
    $routes = require __DIR__ . '/countFaceOnMonth.php';
    $routes($app);

    //get temperature in out work in current date
    $routes = require __DIR__ . '/temperatureInOutWork.php';
    $routes($app);

    //get time in out work in current date
    $routes = require __DIR__ . '/timeInOutWork.php';
    $routes($app);
};