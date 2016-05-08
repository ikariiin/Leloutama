<?php
if (function_exists("cli_set_process_title")) {
    @cli_set_process_title("aerys");
}
if(!extension_loaded("pthreads")) {
    exit("You need the pthreads extension for running the extension.\n");
}
include __DIR__ . "/../autoloads/router.autoloads.php";
require __DIR__ . "/../src/lib/Core/Server/Server.php";

$host = "127.0.0.1";

$port = 9000;

$router = new \Leloutama\lib\Core\Router\Router();

$shortOptions = "h::";
$longOptions = [
    "host:",
    "port:",
    "router:"
];

$options = getopt($shortOptions, $longOptions);

if(isset($options["host"]) && $options["host"]) {
    $host = $options["host"];
}

if(isset($options["port"]) && $options["port"]) {
    $port = $options["port"];
}

if(isset($options["router"]) && $options["router"]) {
    $router = require($options["router"]);
}

$server = new \Leloutama\lib\Core\Server\Server($router, $host, $port);

$server->startServer();