<?php

/**
 * Simply used to include each route, this way they don't need to be required in each file separate.
 */

$routes_path = 'routes/';

require $routes_path . 'homepage.php';
require $routes_path . 'register.php';
require $routes_path . 'landingpage.php';
require $routes_path . 'sendmessagepage.php';
require $routes_path . 'readmessagepage.php';
require $routes_path . 'downloadmessagepage.php';
require $routes_path . 'showdownloadedpage.php';
require $routes_path . 'adminsettings.php';
