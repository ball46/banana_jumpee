<?php

use Slim\App;

return function (App $app) {
    //check in by scan face id
    $routes = require __DIR__ . '/postFace.php';
    $routes($app);
};