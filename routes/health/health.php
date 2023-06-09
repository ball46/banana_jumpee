<?php

use Slim\App;

return function (App $app) {
    //call post health
    $routes = require __DIR__ . '/postHealth.php';
    $routes($app);
};