<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/8/16
 * Time: 10:00 PM
 */
namespace Leloutama\lib\Core\Server;
use FastRoute\Dispatcher;
use Leloutama\lib\Core\Http\Body;
use Leloutama\lib\Core\Http\Http;
use Leloutama\lib\Core\Modules\Http\Request;
use Leloutama\lib\Core\Modules\Http\RequestBuilder;
use Leloutama\lib\Core\Websocket\Handshake;

class WebsocketHandler {
    public function __construct(Dispatcher $dispatcher, string $rawString, Http $http) {
        $body = new Body();
        $body->load($rawString);

        $request = (new RequestBuilder())
            ->buildRequest($http, $body);

        $routeInfo = $dispatcher->dispatch("GET", $request->getRequestedResource());
    }

    public static function _commAndInitiatorThread(Socket $commSocket, Socket $responsibilitySocket, Request $request, array $routeInfo) {
    }
}