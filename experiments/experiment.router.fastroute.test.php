<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 27/5/16
 * Time: 4:50 PM
 */
require_once __DIR__ . "/../vendor/autoload.php";

$serializer = new SuperClosure\Serializer();

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) use ($serializer) {
    $r->addRoute("GET", "/", function ($request) {
        return $request;
    });
});
var_dump($dispatcher);

$routeInfo = $dispatcher->dispatch("GET", "/");
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        echo $handler("LOLOLOLOL");
        break;
}