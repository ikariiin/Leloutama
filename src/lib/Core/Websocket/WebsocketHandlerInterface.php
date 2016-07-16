<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 1/7/16
 * Time: 9:37 PM
 */

namespace Leloutama\lib\Core\Websocket;
use Leloutama\lib\Core\Modules\Http\Request;
use Leloutama\lib\Core\Modules\Responses\HttpResponse;

interface WebsocketHandlerInterface {
    public function onHandshake(Request $request, HttpResponse $response);

    public function onStart($endpoint);

    public function onOpen(int $clientId, $handshakeData);

    public function onData(int $clientId, $msg);

    public function onClose(int $clientId, int $code, string $reason);
}