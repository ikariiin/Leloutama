<?php
$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute("GET", "/", function ($request, $vars) {
        $response = (new \Leloutama\lib\Core\Utility\Response($request));
        $response
            ->loadFromFile("/index.html")
            ->setMime("text/html")
            ->setStatus(200);

        return $response;
    });

    $r->addRoute("GET", "/greet/{name}", function ($request, $vars) {
        $response = (new \Leloutama\lib\Core\Utility\Response($request))
            ->setContent("<h1>Hi There, " . $vars["name"] . "</h1>")
            ->setMime("text/html")
            ->setStatus(200);

        return $response;
    });

    $r->addRoute("GET", "/template/show/{name}", function ($request, $vars) {
        $response = (new \Leloutama\lib\Core\Utility\Response($request))
            ->useTwigTemplate("/template.twig", $vars)
            ->setMime("text/html")
            ->setStatus(200);

        return $response;
    });
});

return $dispatcher;