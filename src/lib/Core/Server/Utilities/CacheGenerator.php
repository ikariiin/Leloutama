<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 28/5/16
 * Time: 8:03 AM
 */

namespace Leloutama\lib\Core\Server\Utilities;
class CacheGenerator {
    private $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function createCacheHeaders(string $etag, array &$headers) {
        $scope = (isset($this->config["Cache-Config"]["scope"])) ? $this->config["Cache-Config"]["scope"] : "public";
        $maxAge = (isset($this->config["Cache-Config"]["max-age"])) ? $this->config["Cache-Config"]["max-age"] : 120;

        $headers[] = sprintf('Cache-Control: %s, max-age=%d',
            $scope,
            $maxAge
        );
        $headers[] = sprintf('Etag: "%s"', $etag);
    }
}