<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 28/5/16
 * Time: 8:03 AM
 */

namespace Leloutama\lib\Core\Server\Utilities;
use Leloutama\lib\Core\Utility\Response;

class CacheGenerator {
    private $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function createCacheHeaders(Response $response) {
        $scope = (isset($this->config["Cache-Config"]["scope"])) ? "public" : $this->config["Cache-Config"]["scope"];
        $maxAge = (isset($this->config["Cache-Config"]["max-age"])) ? 120 : $this->config["Cache-Config"]["max-age"];

        $response->setHeader("Cache-Control", sprintf("%s, max-age=%d",
            $scope,
            $maxAge
        ));

        $response->setHeader("Etag", '"' . ETag::getEtag($response->getContent()) . '"');

        return $response;
    }
}