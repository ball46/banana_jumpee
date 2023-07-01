<?php

use Slim\App;

return function (App $app) {
    //call get count list
    $routes = require __DIR__ . '/countHistoryLeave.php';
    $routes($app);

    //call get data leave
    $routes = require __DIR__ . '/dataLeave.php';
    $routes($app);

    //call get list leave is not success approved
    $routes = require __DIR__ . '/listAskToLeave.php';
    $routes($app);

    //call get list history leave
    $routes = require __DIR__ . '/listLeaveHistory.php';
    $routes($app);

    //call get list who want to leave by this member id
    $routes = require __DIR__ . '/listRequestLeave.php';
    $routes($app);

    //call to get leave day all types to show on the dashboard
    $routes = require __DIR__ . '/showLeaveDay.php';
    $routes($app);

    //call check status leave
    $routes = require __DIR__ . '/statusLeave.php';
    $routes($app);
};