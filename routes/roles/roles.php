<?php

use Slim\App;

return function (App $app) {
    //Add new role
    $routes = require __DIR__ . '/byAdmin/postRole.php';
    $routes($app);

    //get role data
    $routes = require __DIR__ . '/byUser/getRole.php';
    $routes($app);

    //get all role data
    $routes = require __DIR__ . '/byAdmin/getAllRole.php';
    $routes($app);

    //update role data
    $routes = require __DIR__ . '/byAdmin/putRole.php';
    $routes($app);

    //fake delete role data
    $routes = require __DIR__ . '/byAdmin/statusRole.php';
    $routes($app);

    //real delete role data
    $routes = require __DIR__ . '/byAdmin/deleteRole.php';
    $routes($app);
};