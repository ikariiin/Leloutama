<?php
include "../src/lib/Core/Router/Router.php";

use \Leloutama\lib\Core\Router\Router;

$router = new Router();
$router->setRoute("/yolo", "<h1>o/</h1>");
$router->setRoute("/*", "/%requested_path%");

var_dump($router->getRoutes());