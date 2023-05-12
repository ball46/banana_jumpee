<?php

use Slim\App;

return function (App $app) {
    //date of work
    $routes = require __DIR__ . '/postDateWork.php';
    $routes($app);
};