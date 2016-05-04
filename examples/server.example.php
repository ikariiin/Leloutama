<?php
require "../src/lib/Core/Router/Router.php";
require "../src/lib/Core/Server/Server.php";

// Initialize the Router
$router = new Leloutama\lib\Core\Router\Router();

// Set a route
$router->setRoute("/", "\\o/ You successfully started using Leloutama.\n In case you are wondering why is this thing having such a wierd name,\n This name was derived in Room 11 by @Ekn.", "text/plain");

// Set another route
$router->setRoute("/welcome", "<h1>Welcome, Alien.</h1>", "text/html");

// Initialize the server
$server = new Leloutama\lib\Core\Server\Server($router);

// Start the server
$server->startServer();