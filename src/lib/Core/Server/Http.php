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


    public function parseRawSocketRequest($client): string {
        $head = [];
        $header = 0;
        while(($chars = socket_read($client, 4096, PHP_NORMAL_READ))) {
            $head[$header] = trim($chars);
            if($header > 0) {
                if(!$head[$header] && !$head[$header - 1])
                    break;
            }
            $header++;
        }

        $headers = [];
        foreach($head as $header) {
            if ($header) {
                $headers[]=$header;
            }
        }

        return implode("\r\n", $headers);
    }

    public function getInfo(string $requestedRoute, Router $router) {
        $routes = $router->getRoutes();
        $routesCount = count($routes);
        for($i = 0; $i < $routesCount; $i++) {
            if($routes[$i]->getExposedRoute() == $requestedRoute) {
                return $routes[$i]->getResponse();
            } else {
                continue;
            }
        }
        return false;
    }

    public function getMIMEType(string $fileName): string {
        return mime_content_type($fileName);
    }

    /**
     * Header information getting methods
     */
    public function getRequestedResource(): string {
        return $this->parsedHeaders["route"]["uri"];
    }

    public function getMethod(): string {
        return $this->parsedHeaders["route"]["method"];
    }

    public function getHttpVersion(): string {
        return $this->parsedHeaders["route"]["http"];
    }

    public function getHeaderParam(string $param): string {
        return $this->parsedHeaders[$param];
    }

    public function getAcceptedEncoding(): array {
        return explode(", ", $this->parsedHeaders["Accept-Encoding"]);
    }

    public function getCookies(): array {
        return explode("; ", $this->parsedHeaders["Cookie"]);
    }
}