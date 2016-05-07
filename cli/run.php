<?php
require __DIR__ . "/../src/lib/Core/Router/Router.php";
require __DIR__ . "/../src/lib/Core/Server/Server.php";

$ip = "127.0.0.1";

$port = 0;

$router = new \Leloutama\lib\Core\Router\Router();

$shortOptions = "h::";
$longOptions = [
    "host:",
    "port:",
    "router:"
];

$options = getopt($shortOptions, $longOptions);

if(isset($options["host"]) && !$options["host"]) {}