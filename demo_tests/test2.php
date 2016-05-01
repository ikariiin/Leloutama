<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 28/4/16
 * Time: 6:29 PM
 */

require "../src/lib/Core/Router/Router.php";
require "../src/lib/Core/Server/Server.php";

$router = new Leloutama\lib\Core\Router\Router();
$router->setRoute("/", "hmm");
$server = new Leloutama\lib\Core\Server\Server($router);
$server->startServer();