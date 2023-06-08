<?php

use Slim\App;

return function (App $app) {
    //call get list history leave
    $routes = require __DIR__ . '/bySystem/listLeaveHistory.php';
    $routes($app);

    //call get list who want to leave by this member id
    $routes = require __DIR__ . '/bySystem/listRequestLeave.php';
    $routes($app);

    //call to get leave day all types to show on the dashboard
    $routes = require __DIR__ . '/bySystem/showLeaveDay.php';
    $routes($app);

    //call check status leave
    $routes = require __DIR__ . '/bySystem/statusLeave.php';
    $routes($app);

    //call to update count leave
    $routes = require __DIR__ . '/byAdmin/updateMaxLeave.php';
    $routes($app);

    //call add special days
    $routes = require __DIR__ . '/byAdmin/addSpecialLeave.php';
    $routes($app);

    //call add new position max leave
    $routes = require __DIR__ . '/byAdmin/postMaxLeave.php';
    $routes($app);

    //call to delete member allow
    $routes = require __DIR__ . '/byUser/deleteMemberAllow.php';
    $routes($app);

    //call to get member allow
    $routes = require __DIR__ . '/byUser/getMemberAllow.php';
    $routes($app);

    //call add new position max leave
    $routes = require __DIR__ . '/byUser/memberAllow.php';
    $routes($app);

    //call leave work
    $routes = require __DIR__ . '/byUser/postLeave.php';
    $routes($app);

    //call to post member allow
    $routes = require __DIR__ . '/byUser/postMemberAllow.php';
    $routes($app);
};
