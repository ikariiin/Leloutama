<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 4/5/16
 * Time: 9:43 PM
 */

namespace Leloutama\lib\Core\Utility;


class Request {
    private $requestedResource;
    private $cookies;
    private $ifNoneMatch;

    public function __construct() {
        return $this;
    }

    public function setCookies(array $getVars): self {
        $this->cookies = $getVars;
        return $this;
    }

    public function setRequestedResource(string $requestedResource): self {
        $this->requestedResource = $requestedResource;
        return $this;
    }

    public function getCookies(): array {
        return $this->cookies;
    }

    public function getRequestedResource(): string {
        return $this->requestedResource;
    }

    public function setIfNoneMatch(string $ifNoneMatch) {
        $this->ifNoneMatch = $ifNoneMatch;
    }
}