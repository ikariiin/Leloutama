<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/5/16
 * Time: 4:55 PM
 */

namespace Leloutama\lib\Core\Server\Utilities;
use Leloutama\lib\Core\Server\Http;

class Encode {
    public function __construct() {
        return $this;
    }

    public function encodeBody(Http $http, string $body): array {
        $toReturn = [];
        if(function_exists("gzencode") && in_array("gzip", $http->getAcceptedEncoding())) {
            $body = gzencode($body);
            $toReturn = [
                "content" => $body,
                "algorithm" => "gzip"
            ];
        } elseif(function_exists("gzcompress") && in_array("deflate", $http->getAcceptedEncoding())) {
            $body = gzcompress($body);
            $toReturn = [
                "content" => $body,
                "algorithm" => "deflate"
            ];
        }

        return $toReturn;
    }
}