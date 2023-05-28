<?php

use Slim\App;

return function (App $app) {
    //call to post holidays
    $routes = require __DIR__ . '/postHoliday.php';
    $routes($app);
};