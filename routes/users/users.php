<?php

use Slim\App;

return function (App $app) {
    //register user
    $routes = require __DIR__ . '/byUser/postUser.php';
    $routes($app);

    //login user
    $routes = require __DIR__ . '/byUser/getUser.php';
    $routes($app);

    //logout user
    $routes = require __DIR__ . '/byUser/userLogout.php';
    $routes($app);

    //update data user
    $routes = require __DIR__ . '/byUser/putDataUser.php';
    $routes($app);

    //change password user
    $routes = require __DIR__ . '/byUser/newPasswordUser.php';
    $routes($app);

    //change status user
    $routes = require __DIR__ . '/byUser/statusUser.php';
    $routes($app);

    //delete user
    $routes = require __DIR__ . '/byAdmin/deleteUser.php';
    $routes($app);
};