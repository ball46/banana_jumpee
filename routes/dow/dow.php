<?php

use Slim\App;

return function (App $app) {
    //date of work version 1 (version choose the date of week)
    $routes = require __DIR__ . '/postV1.php';
    $routes($app);

    //date of work version 2 (version choose date of month)
    $routes = require __DIR__ . '/postV2.php';
    $routes($app);
};