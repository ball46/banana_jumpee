<?php

use Slim\App;

return function (App $app) {
    //call add special days
    $routes = require __DIR__ . '/addSpecialLeave.php';
    $routes($app);

    //call add new position max leave
    $routes = require __DIR__ . '/postMaxLeave.php';
    $routes($app);

    //call to update count leave
    $routes = require __DIR__ . '/updateMaxLeave.php';
    $routes($app);
};