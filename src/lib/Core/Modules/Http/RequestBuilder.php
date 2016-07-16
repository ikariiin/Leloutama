<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/5/16
 * Time: 4:10 PM
 */

namespace Leloutama\lib\Core\Modules\Http;
use Leloutama\lib\Core\Http\Body;
use Leloutama\lib\Core\Http\Http;

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
            ->setIfNoneMatch($http->getHeaderParam("If-None-Match"))
            ->setHeader_Mass($http->getParsedHeaders());

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