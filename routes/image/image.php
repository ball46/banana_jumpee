<?php

use Slim\App;

return function (App $app) {
    //add default image to member when member is register
    $routes = require __DIR__ . '/postImage.php';
    $routes($app);

    //change image member
    $routes = require __DIR__ . '/putImage.php';
    $routes($app);

    //change image member
    $routes = require __DIR__ . '/getImage.php';
    $routes($app);

    //delete now image member (mean change image member to default image member)
    $routes = require __DIR__ . '/deleteImage.php';
    $routes($app);
};