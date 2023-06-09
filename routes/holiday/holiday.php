<?php

use Slim\App;

return function (App $app) {
    //call to post holidays
    $routes = require __DIR__ . '/postHoliday.php';
    $routes($app);

    //call to put holiday
    $routes = require __DIR__ . '/putHoliday.php';
    $routes($app);
};