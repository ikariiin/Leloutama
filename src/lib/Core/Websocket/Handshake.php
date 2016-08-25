<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 30/6/16
 * Time: 10:11 PM
 */

namespace Leloutama\lib\Core\Websocket;
use FastRoute\Dispatcher;
use Leloutama\lib\Core\Modules\Http\Request;
use Leloutama\lib\Core\Modules\Http\ServerContentGetter;
use Leloutama\lib\Core\Modules\Responses\HttpResponse;
use SuperClosure\Serializer;

class Handshake {
    private $websocketKey;
    private $clientWebsocketVersion;
    private $request;
    private $routeInfo;

    const HANDSHAKE_MAGIC_STRING = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";

    public function __construct(Request $request, array $routeInfo) {
        $this->routeInfo = $routeInfo;
        $this->request = $request;
        $this->websocketKey = $request->getHeader("Sec-WebSocket-Key");
        $this->clientWebsocketVersion = $request->getHeader("Sec-WebSocket-Version");
    }

    public function getRawResponse(): string {
        $response = $this->handleRoute();
        $rawHeaders = $response->getHeadersAsString() . "\r\n\r\n";
        return $rawHeaders;
    }

    private function getAcceptString(): string {
        $appended = $this->websocketKey . self::HANDSHAKE_MAGIC_STRING;
        $hashed = sha1($appended, true);
        $encoded = base64_encode($hashed);

        return $encoded;
    }

    private function handleRoute(): HttpResponse {
        $routeInfo = $this->routeInfo;

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response = (new HttpResponse($this->request))
                    ->setContent((new ServerContentGetter())
                        ->get404())
                    ->setMime("text/html")
                    ->setStatus(404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = (new HttpResponse($this->request))
                    ->setContent((new ServerContentGetter())
                        ->get405())
                    ->setMime("text/html")
                    ->setStatus(405);
                break;
            default:
                /** @var WebsocketHandlerInterface $handler */
                $handler = unserialize($routeInfo[1]);

                $response = (new HttpResponse($this->request))
                    ->setHttpAndStatus("HTTP/1.1 101 Switching Protocols")
                    ->setHeader("Upgrade", "websocket")
                    ->setHeader("Connection", "upgrade")
                    ->setHeader("Sec-WebSocket-Accept", $this->getAcceptString());

                $response = $handler->onHandshake($this->request, $response);
                break;
        }

        return $response;
    }

    private function checkIfDefaultRouteIsPresent() {}
}