<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 27/4/16
 * Time: 10:28 PM
 */

namespace Leloutama\lib\Core\Server;
require __DIR__ . "/Http.php";
require __DIR__ . "/../Utility/Request.php";
use Leloutama\lib\Core\Router\Router;
use Leloutama\lib\Core\Utility\Request;

class Client {
    /* Protected Vars */
    protected $router;
    protected $http;
    protected $stringHeaders;
    protected $request;

    /* Private Vars */
    private $config;

    /**
     * Client constructor.
     * Constructs the Client instance.
     * Needs the user defined router as the first argument, and the raw string headers separated by \r\n, as the second.
     * @param Router $router
     * @param $stringHeaders
     */
    public function __construct(Router $router, $stringHeaders) {
        $this->router = $router;

        $this->stringHeaders = $stringHeaders;

        $this->http = new Http($stringHeaders);

        $this->config = json_decode(file_get_contents(__DIR__ . "/../../../config/Core/config.json"), true);
    }

    /**
     * The public method to call to initialize the whole serving process.
     * Returns the response as a string, which can be just socket_write'en.
     * @return string
     */
    public function serve(): string {
        $this->http->Headerize();
        $this->http->parseHeaders();

        $this->buildRequest();

        printf("Request Recieved \n \t Time: %s \n \t Requested Resource: %s \n \t Method: %s \n",
            date("M d Y-H:i:s "),
            $this->http->getRequestedResource(),
            $this->http->getMethod()
        );

        $response = $this->process();

        $responseHeaders = $response[0];
        $responseBody = $response[1];


        $finalPacket = $responseHeaders . "\r\n\r\n" . $responseBody;

        return $finalPacket;
    }

    protected function buildRequest() {
        $cookies = $this->http->getCookies();
        $requestedResource = $this->http->getRequestedResource();

        $this->request = (new Request())
            ->setCookies($cookies)
            ->setRequestedResource($requestedResource);
    }

    /**
     * Processes the router, gets the requested resource, creates the headers, and returns an array.
     * If there is some error, it calls the methods to get the content, and also returns an array with the structure of
     * [0] => headers, [1] => body
     * @return array
     */
    private function process(): array {
        $response = $this->http->getInfo($this->http->getRequestedResource(), $this->router);

        if(!$response) {
            $toServeContent = $this->get404();

            return $this->createHeaders($toServeContent, "text/html", "", 404);
        }

        $response->setRequest($this->request)->loadConfig($this->config);

        if($this->http->getMethod() !== "GET") {
            $toServeContent = $this->get405();

            return $this->createHeaders($toServeContent, "text/html", "", 405);
        } else {
            $responseChangeState = $response->onReady();
            $toServeContent = $responseChangeState->getBody();
            $responseStatus = $responseChangeState->getStatus();
        }

        return $this->createHeaders($toServeContent, $response->getMime(), "", $responseStatus);
    }

    protected function formatBody(string $body): string {
        return implode("\r\n", explode("\n", $body));
    }

    protected function get404(): string {
        $html404 = file_get_contents(__DIR__ . "/../Resources/400Errors.html");

        $vars = array(
            "%error_code%" => "404",
            "%error_code_meaning%" => "Not Found",
            "%error_description%" => "The router was not configured to handle this route at all... So..."
        );

        $html404 = $this->replaceVarsInErrorPage($vars, $html404);

        return $html404;
    }

    protected function get405(): string {
        $html405 = file_get_contents(__DIR__ . "/../Resources/400Errors.html");

        $vars = array(
            "%error_code%" => "405",
            "%error_code_meaning%" => "Method Not Supported",
            "%error_description%" => "The method requested is not supported by the server."
        );

        $html405 = $this->replaceVarsInErrorPage($vars, $html405);

        return $html405;
    }

    protected function replaceVarsInErrorPage(array $vars, string $content): string {
        foreach($vars as $varName => $value) {
            $content = str_replace($varName, $value, $content);
        }
        return $content;
    }

    private function createHeaders(string $content, string $mimeType, string $fileName = "", int $status = 200): array {
        $headers = [];
        switch($status) {
            case 200:
                $headers[] = "HTTP/1.1 200 OK";
                break;
            case 404:
                $headers[] = "HTTP/1.1 404 Not Found";
                break;
            case 405:
                $headers[] = "HTTP/1.1 405 Method Not Supported";
                break;
        }

        $encodeOP = $this->encodeBody($content);

        $headers[] = sprintf("Content-Type: %s", $mimeType);

        if(!empty($encodeOP)) {
            $content = $encodeOP[0];
            $headers[] = sprintf("Content-Encoding: %s", $encodeOP[1]);
        }

        $headers[] = sprintf("Content-Length: %d", strlen($content));

        $headers = implode("\r\n", $headers);
        return [$headers, $content];
    }

    protected function encodeBody(string $body): array {
        $toReturn = [];
        if(in_array("gzip", $this->http->getAcceptedEncoding())) {
            $body = gzencode($body);
            $toReturn = [$body, "gzip"];
        } elseif(in_array("deflate", $this->http->getAcceptedEncoding())) {
            $body = gzcompress($body);
            $toReturn = [$body, "deflate"];
        }

        return $toReturn;
    }
}