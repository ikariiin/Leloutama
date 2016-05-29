<?php
$dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
    $r->addRoute("GET", "/{name}[/{greeting}]", function ($request, array $vars) {
        $response = new \Leloutama\lib\Core\Utility\Response($request);
        
        $response->setContent("#yolo " . $vars["name"] . " " . ((isset($vars["greeting"])) ? $vars["greeting"] : "Greetings upon Thee, Your Majesty"));
        
        return $response;
    });
});

return $dispatcher;