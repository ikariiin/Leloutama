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

$shortOptions = "h";
$longOptions = [
    "host:",
    "port:",
    "router:"
];

$help = <<<HELP
 _        ___  _       ___   __ __  ______   ____  ___ ___   ____
| T      /  _]| T     /   \ |  T  T|      T /    T|   T   T /    T
| |     /  [_ | |    Y     Y|  |  ||      |Y  o  || _   _ |Y  o  |
| l___ Y    _]| l___ |  O  ||  |  |l_j  l_j|     ||  \_/  ||     |
|     T|   [_ |     T|     ||  :  |  |  |  |  _  ||   |   ||  _  |
|     ||     T|     |l     !l     |  |  |  |  |  ||   |   ||  |  |
l_____jl_____jl_____j \___/  \__,_j  l__j  l__j__jl___j___jl__j__j

A multithreaded webserver completely written in PHP.

Options:

--host                  Specify the host from which the server has to listen.
--port                  Specify the port of the host, which the server will listen from.
--router                Specify the absolute/relative path of the router file.
-h                      Display this help text.


HELP;

$options = getopt($shortOptions, $longOptions);

if(isset($options["h"])) {
    echo $help;
}

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