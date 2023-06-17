<?php

use Slim\App;

return function (App $app) {
    //call to delete member allow
    $routes = require __DIR__ . '/deleteMemberAllow.php';
    $routes($app);

    //call to get member allow
    $routes = require __DIR__ . '/getMemberAllow.php';
    $routes($app);

    //call add new position max leave
    $routes = require __DIR__ . '/memberAllow.php';
    $routes($app);

    //call leave work
    $routes = require __DIR__ . '/postLeave.php';
    $routes($app);

    //call to post member allow
    $routes = require __DIR__ . '/postMemberAllow.php';
    $routes($app);

    //call to get members is not choose in select type
    $routes = require __DIR__ . '/searchBarMemberAllow.php';
    $routes($app);
};