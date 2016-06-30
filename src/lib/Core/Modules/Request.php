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
    private $postData = [];
    private $queryParams = [];

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

    public function setIfNoneMatch(string $ifNoneMatch): self {
        $this->ifNoneMatch = $ifNoneMatch;
        return $this;
    }

    public function getIfNoneMatch(): string {
        return $this->ifNoneMatch;
    }

    public function setPostData(array $postData): self {
        $this->postData = $postData;
        return $this;
    }

    public function getPostData(): array {
        return $this->postData;
    }

    public function setQueryParams(array $queryParams): self {
        $this->queryParams = $queryParams;
        return $this;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }
}