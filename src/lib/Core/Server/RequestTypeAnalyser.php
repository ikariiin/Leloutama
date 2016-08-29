<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 1/7/16
 * Time: 10:18 PM
 */

namespace Leloutama\lib\Core\Server;

use Leloutama\lib\Core\Http\Http;
use Leloutama\lib\Core\Modules\Http\Request;
use Leloutama\lib\Core\Websocket\IsWebsocketHandshake;

class RequestTypeAnalyser {
    public static function type(string $rawRequest): array {
        /* 
         * Currently since the server only promises to implement Websocket and Http Protocol, this only analyses the
         * request for these two protocol only. 
         */

        if(strpos($rawRequest, "\r\n")) {
            $http = (new Http($rawRequest));
            $http->Headerize();
            $http->parseHeaders();
            $request = (new Request())
                ->setHeader_Mass($http->getParsedHeaders());
            if((new IsWebsocketHandshake($request))->is()) {
                return ["websocket-handshake", $http];
            }
            return ["http"];
        } else {
            return "websocket";
        }
    }
}