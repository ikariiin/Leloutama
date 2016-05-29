<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/5/16
 * Time: 4:10 PM
 */

namespace Leloutama\lib\Core\Server\Utilities;
use Leloutama\lib\Core\Server\Body;
use Leloutama\lib\Core\Server\Http;
use Leloutama\lib\Core\Utility\Request;

class RequestBuilder {
    public function __construct() {
        return $this;
    }

    public function buildRequest(Http $http, Body $body) {
        $cookies = $http->getCookies();
        $requestedResource = $http->getRequestedResource();

        $request = (new Request)
            ->setCookies($cookies)
            ->setRequestedResource($requestedResource)
            ->setIfNoneMatch($http->getHeaderParam("If-None-Match"));

        if($http->getMethod() === "POST") {
            $request->setPostData([
                "raw" => $body->getRawBody(),
                "parsed" => $body->getParsedBody()
            ]);
        } elseif($http->getMethod() === "GET" || $http->getMethod() === "HEAD") {
            $queryString = $http->getQueryString();
            $parsed = $body->parse($queryString, "&");
            $request->setQueryParams([
                "raw" => $queryString,
                "parsed" => $parsed
            ]);
        }

        return $request;
    }
}