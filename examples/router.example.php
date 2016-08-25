<?php
class MyAwesomeWS implements \Leloutama\lib\Core\Websocket\WebsocketHandlerInterface {

    public function onHandshake(\Leloutama\lib\Core\Modules\Http\Request $request, \Leloutama\lib\Core\Modules\Responses\HttpResponse $response) {
        echo "Handshake!!!\n";
        return $response;
    }

    public function onStart($endpoint) {
        // TODO: Implement onStart() method.
    }

    public function onOpen(int $clientId, $handshakeData) {
        // TODO: Implement onOpen() method.
    }

    public function onData(int $clientId, $msg) {
        // TODO: Implement onData() method.
    }

    public function onClose(int $clientId, int $code, string $reason) {
        // TODO: Implement onClose() method.
    }
}
$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute("GET", "/", function ($request, $vars) {
        $response = (new \Leloutama\lib\Core\Modules\Responses\HttpResponse($request));
        $response
            ->setContent("ohai")
            ->setMime("text/html")
            ->setStatus(200);

        return $response;
    });

    $r->addRoute("GET", "/greet/{name}", function ($request, $vars) {
        $response = (new \Leloutama\lib\Core\Modules\Responses\HttpResponse($request))
            ->setContent("<h1>Hi There, " . $vars["name"] . "</h1>")
            ->setMime("text/html")
            ->setStatus(200);

        return $response;
    });

    $r->addRoute("GET", "/template/show/{name}", function ($request, $vars) {
        $response = (new \Leloutama\lib\Core\Modules\Responses\HttpResponse($request))
            ->useTwigTemplate("/template.twig", $vars)
            ->setMime("text/html")
            ->setStatus(200);

        return $response;
    });

    $r->addRoute("GET", "/ws", new \MyAwesomeWS());
});

return $dispatcher;