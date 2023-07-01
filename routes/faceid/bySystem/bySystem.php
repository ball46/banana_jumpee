<?php

use Slim\App;

return function (App $app) {
    //to get data attendants in current month
    $routes = require __DIR__ . '/attendanceStatistics.php';
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

    //check line data in table face id
    $routes = require __DIR__ . '/countHistoryFaceID.php';
    $routes($app);

    //get history data time and temperature
    $routes = require __DIR__ . '/historyTimeTemp.php';
    $routes($app);

    //get all history data about member id
    $routes = require __DIR__ . '/listFaceIDHistory.php';
    $routes($app);

    //get temperature in out work in current date
    $routes = require __DIR__ . '/temperatureInOutWork.php';
    $routes($app);

    //get time in out work in current date
    $routes = require __DIR__ . '/timeInOutWork.php';
    $routes($app);
};