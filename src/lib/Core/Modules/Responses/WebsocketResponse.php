<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 1/7/16
 * Time: 8:49 PM
 */

namespace Leloutama\lib\Core\Modules\Responses;

use Leloutama\lib\Core\Websocket\WebsocketHandlerInterface;

class WebsocketResponse implements Response {
    private $handler;

    public function __construct() {
        return $this;
    }

    public function registerWsHandler(WebsocketHandlerInterface $websocketHandler): self {
        $this->handler = $websocketHandler;

        return $this;
    }
}