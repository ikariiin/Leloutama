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
use Leloutama\lib\Core\Utility\Response;

class Creator {
    public function __construct(Http $http, array $config) {
        $this->http = $http;
        $this->config = $config;
        return $this;
    }

    public function create(Response $response): Response {
        $response->setHttpAndStatus(sprintf("HTTP/1.1 %d %s", $response->getStatus(), Http::HTTP_REASON[$response->getStatus()]));

        $encodeOP = (new Encode())
            ->encodeBody($this->http, $response->getContent());

        $response->setHeader("Content-Type", $response->getMime());

        if(!empty($encodeOP)) {
            $content = $encodeOP["content"];
            $response->setHeader("Content-Encoding", $encodeOP["algorithm"]);
        } else {
            $content = $response->getContent();
        }

        $response->setContent($content);

        $response->setHeader("Content-Length", strlen($content));

        $response = (new CacheGenerator($this->config))
            ->createCacheHeaders($response);

        $response->setHeader("X-Powered-By", Client::SERVER_NAME);

        return $response;
    }

    public function afterFirstPhase(Response $response) {
        if($this->http->getHeaderParam("If-None-Match") === sprintf('"%d"', ETag::getEtag($response->getContent()))) {
            $response->setStatus(304);
        }

        (new Logger($this->http, $this->config))
            ->logResponse(sprintf("%d %s", $response->getStatus(), Http::HTTP_REASON[$response->getStatus()]));

        if($response->getStatus() === 304 || $this->http->getMethod() === "HEAD") {
            $response->setContent("");
        }

        return $response;
    }
}