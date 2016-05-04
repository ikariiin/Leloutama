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
$router->setRoute("/", "This Actually works! buhahahahahah!", "text/plain");
$router->setRoute("/hello", "<h1>Hello</h1>", "text/html");
$server = new Leloutama\lib\Core\Server\Server($router);
$server->startServer();