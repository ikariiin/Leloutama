<?php
namespace Leloutama\lib\Core\Http;


class Http {
    protected $unparsedHeader;
    protected $stringHeaders;
    private $parsedHeaders;
    private $requestBody;

    const HTTP_REASON = [
        100 => "Continue",
        101 => "Switching Protocols",
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I'm A Teapot",
        426 => "Upgrade Required",
        428 => "Precondition Required",
        429 => "Too Many Requests",
        431 => "Request Header Fields Too Large",
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        511 => "Network Authentication Required",
    ];

    public function __construct(string $stringHeaders = "") {
        $this->stringHeaders = $stringHeaders;
        return $this;
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
                if(strpos($uriHeader[1], "?")) {
                    $queryString = strstr($uriHeader[1], "?");
                    $uri = strstr($uriHeader[1], "?", true);

                    $parsedHeaders["route"]["uri"] = $uri;

                    $queryString = substr($queryString, 1, strlen($queryString) - 1);
                    $parsedHeaders["route"]["queryString"] = $queryString;
                } else {
                    $parsedHeaders["route"]["uri"] = $uriHeader[1];
                    $parsedHeaders["route"]["queryString"] = "";
                }
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

    public function getMIMEType(string $fileName): string {
        return mime_content_type($fileName);
    }

    /**
     * Header information getting methods
     */
    public function getRequestedResource(): string {
        return rawurldecode($this->parsedHeaders["route"]["uri"]);
    }

    public function getMethod(): string {
        return $this->parsedHeaders["route"]["method"];
    }

    public function getQueryString(): string {
        return $this->parsedHeaders["route"]["queryString"];
    }

    public function getHttpVersion(): string {
        return $this->parsedHeaders["route"]["http"];
    }

    public function getHeaderParam(string $param): string {
        if(isset($this->parsedHeaders[$param])) {
            return $this->parsedHeaders[$param];
        } else {
            return "";
        }
    }

    public function getAcceptedEncoding(): array {
        return explode(", ", $this->parsedHeaders["Accept-Encoding"]);
    }

    public function getCookies(): array {
        return explode("; ", $this->parsedHeaders["Cookie"]);
    }

    public function setRequestBody($requestBody): self {
        $this->requestBody = $requestBody;
        return $this;
    }

    public function getRequestBody(): string {
        return $this->requestBody;
    }

    public function getParsedHeaders(): array {
        return $this->parsedHeaders;
    }

    public static function parsePacket(string $packet) {
        $explode  = explode("\r\n\r\n", $packet);
        $headers = $explode[0];
        $body = (isset($explode[1])) ? $explode[1] : "";

        return [
            "headers" => $headers,
            "body" => $body
        ];
    }
}