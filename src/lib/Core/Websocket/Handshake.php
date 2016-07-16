<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 30/6/16
 * Time: 10:11 PM
 */

namespace Leloutama\lib\Core\Websocket;
use Leloutama\lib\Core\Modules\Http\Request;
use Leloutama\lib\Core\Modules\Responses\HttpResponse;

class Handshake {
    private $websocketKey;
    private $clientWebsocketVersion;
    private $request;

    const HANDSHAKE_MAGIC_STRING = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";

    public function __construct(Request $request) {
        $this->request = $request;
        $this->websocketKey = $request->getHeader("Sec-WebSocket-Key");
        $this->clientWebsocketVersion = $request->getHeader("Sec-WebSocket-Version");
    }

    public function getResponse(): HttpResponse {
        $response = (new HttpResponse($this->request))
            ->setStatus(101)
            ->setHeader("Upgrade", "websocket")
            ->setHeader("Connection", "upgrade")
            ->setHeader("Sec-WebSocket-Accept", $this->getAcceptString());

        return $response;
    }

    private function getAcceptString(): string {
        $appended = $this->websocketKey . self::HANDSHAKE_MAGIC_STRING;
        $hashed = sha1($appended);
        $encoded = base64_encode($hashed);

        return $encoded;
    }
}