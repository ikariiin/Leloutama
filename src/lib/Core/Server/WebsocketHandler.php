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
    public function __construct(Dispatcher $dispatcher, string $rawString, Http $http, Socket $socket, Socket $commSocket) {
        $body = new Body();
        $body->load($rawString);

        $request = (new RequestBuilder())
            ->buildRequest($http, $body);

        $routeInfo = $dispatcher->dispatch("GET", $request->getRequestedResource());

        $COMMAND_INITIATOR_THREAD = new ThreadDispatcher(function (&$_this, Socket $commSocket, Socket $responsibilitySocket, Request $request, array $routeInfo) {
            WebsocketHandler::_commAndInitiatorThread($commSocket, $responsibilitySocket, $request, $routeInfo);
        }, [$commSocket, $socket, $request, $routeInfo]);

        $COMMAND_INITIATOR_THREAD->start();
    }

    public static function _commAndInitiatorThread(Socket $commSocket, Socket $responsibilitySocket, Request $request, array $routeInfo) {
        $client = $responsibilitySocket->getStream();
        $handshake = new Handshake($request, $routeInfo);

        fwrite($client, $handshake->getRawResponse());
        // Handshake response sent
        $SOCKET_WATCHER_THREAD = new ThreadDispatcher(function (Socket $commSocket, Socket $responsibilitySocket) {
            while(($responsibility = fread($responsibilitySocket->getStream(), 8096 * 2))){
                // Parse $responsibility
                $parsedResponsibility = $responsibility;
            }
        }, [$commSocket, $responsibilitySocket]);
        while(($comm = fread($commSocket->getStream(), 8096 * 2))) {
            var_dump($comm);
        }
    }
}