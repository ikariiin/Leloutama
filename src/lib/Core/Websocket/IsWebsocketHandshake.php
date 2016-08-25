<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 21/8/16
 * Time: 6:40 PM
 */

namespace Leloutama\lib\Core\Websocket;
use Leloutama\lib\Core\Modules\Http\Request;

class IsWebsocketHandshake {
    private $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function is(): bool {
        return $this->request->getHeader("Upgrade") !== null
            && $this->request->getHeader("Upgrade") === "websocket";
    }
}