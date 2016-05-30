<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/5/16
 * Time: 5:22 PM
 */

namespace Leloutama\lib\Core\Server\Utilities;
use Leloutama\lib\Core\Server\Client;
use Leloutama\lib\Core\Server\Http;

class Creator {
    public function __construct(Http $http, array $config) {
        $this->http = $http;
        $this->config = $config;
        return $this;
    }

    public function create(string $content, string $mimeType, int $status): array {
        $headers = [];

        $headers[] = sprintf("HTTP/1.1 %d %s", $status, Http::HTTP_REASON[$status]);

        $encodeOP = (new Encode())
            ->encodeBody($this->http, $content);

        $headers[] = sprintf("Content-Type: %s", $mimeType);

        if(!empty($encodeOP)) {
            $content = $encodeOP[0];
            $headers[] = sprintf("Content-Encoding: %s", $encodeOP[1]);
        }

        $headers[] = sprintf("Content-Length: %d", strlen($content));

        (new CacheGenerator($this->config))
            ->createCacheHeaders(ETag::getEtag($content), $headers);

        $headers[] = sprintf("X-Powered-By: %s", Client::SERVER_NAME);

        return $headers;
    }

    public function afterFirstPhase(array $headers, string $content, int $status) {
        (new Logger($this->http))
            ->logResponse(sprintf("%d %s", $status, Http::HTTP_REASON[$status]));

        if($status === 304 || $this->http->getMethod() === "HEAD") {
            $content = "";
        }

        $headers = implode("\r\n", $headers);
        return [
            "headers" => $headers,
            "content" => $content
        ];
    }
}