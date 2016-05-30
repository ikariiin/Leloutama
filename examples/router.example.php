<?php
$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute("GET", "/", function ($request, $vars) {
        $response = (new \Leloutama\lib\Core\Utility\Response($request));

        $fileChecker = $response->extManager->load("FileCheckr");

        $content = $fileChecker->load(__DIR__ . "/../ServerPages/index.html")->getContent();
        $response
            ->setContent($content)
            ->setMime("text/html");

        return $response;
    });

    $r->addRoute("GET", "/greet/{name}", function ($request, $vars) {
        $response = (new \Leloutama\lib\Core\Utility\Response($request))
            ->setContent("<h1>Hi There, " . $vars["name"] . "</h1>")
            ->setMime("text/html");

        return $response;
    });
});

return $dispatcher;