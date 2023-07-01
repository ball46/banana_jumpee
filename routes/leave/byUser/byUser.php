<?php

use Slim\App;

return function (App $app) {
    //call add new position max leave
    $routes = require __DIR__ . '/memberAllow.php';
    $routes($app);

    //call leave work
    $routes = require __DIR__ . '/postLeave.php';
    $routes($app);

};