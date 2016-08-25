<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 1/7/16
 * Time: 10:18 PM
 */

namespace Leloutama\lib\Core\Server;

class RequestTypeAnalyser {
    public static function type(string $rawRequest) {
        /* 
         * Currently since the server only promises to implement Websocket and Http Protocol, this only analyses the
         * request for these two protocol only. 
         */

        if(strpos($rawRequest, "\r\n")) {
            return "http";
        } else {
            return "websocket";
        }
    }
}