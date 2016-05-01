<?php
namespace Leloutama\lib\Core\Server;
use Leloutama\lib\Core\Router\Router;


class Http {
    protected $unparsedHeader;
    protected $stringHeaders;
    private $parsedHeaders;

    public function __construct(string $stringHeaders = "") {
        $this->stringHeaders = $stringHeaders;
    }

    public function Headerize(string $delimiter = "\r\n"): array {
        $this->unparsedHeader = explode($delimiter, $this->stringHeaders);
        return $this->unparsedHeader;
    }

    public function parseHeaders(): array {
        $headers = $this->unparsedHeader;
        $headersCount = count($headers);

        $parsedHeaders = [];

        for($i = 0; $i < $headersCount; $i++) {
            if($i == 0) {
                $uriHeader = explode(" ", $headers[$i]);

                $parsedHeaders["route"]["method"] = $uriHeader[0];
                $parsedHeaders["route"]["uri"] = $uriHeader[1];
                $parsedHeaders["route"]["http"] = $uriHeader[2];

                continue;
            }

            $kvPair = explode(":", $headers[$i]);
            $parsedHeaders[$kvPair[0]] = trim($kvPair[1]);
        }

        $this->parsedHeaders = $parsedHeaders;
        return $parsedHeaders;
    }

    public function getRequestedResource(): string {
        return $this->parsedHeaders["route"]["uri"];
    }

    public function parseRawSocketRequest($client) {
        $buffer = "";
        while(($chars = socket_read($client, 4096, PHP_NORMAL_READ))) {
            $buffer .= $chars;
        }
        return trim($buffer);
    }

    public function matchRoute(string $requestedRoute, Router $router) {
        $routes = $router->getRoutes();
        $routesCount = $routes;
        for($i = 0; $i < $routesCount; $i++) {
            if($routes[$i]->getExposedRoute() == $requestedRoute) {
                return $routes[$i]->getContent();
            } else {
                return false;
            }
        }
        return false;
    }

    public function getMIMEType(string $fileName): string {
        return mime_content_type($fileName);
    }
}