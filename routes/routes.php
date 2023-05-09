<?php

use Slim\App;

return function (App $app) {
    //call api users
    $routes = require __DIR__ . '/users/users.php';
    $routes($app);

    //call api roles
    $routes = require __DIR__ . '/roles/roles.php';
    $routes($app);

    //call api faceID
    $routes = require __DIR__ . '/roles/roles.php';
    $routes($app);
};