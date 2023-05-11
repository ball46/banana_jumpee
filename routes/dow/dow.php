<?php

use Slim\App;

return function (App $app) {
    //date of work version 1 (version choose the date of week)
    $routes = require __DIR__ . '/postDateWork.php';
    $routes($app);
};