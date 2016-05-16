<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 27/4/16
 * Time: 10:28 PM
 */

namespace Leloutama\lib\Core\Server;
require __DIR__ . "/Http.php";
require __DIR__ . "/Body.php";
require __DIR__ . "/../Utility/Request.php";
require  __DIR__ . "/../Utility/ServerExtensionManager.php";
use Leloutama\lib\Core\Router\Router;
use Leloutama\lib\Core\Utility\Request;
use Leloutama\lib\Core\Utility\ServerExtensionManager;

class Client {
    /* Protected Vars */
    protected $router;
    protected $http;
    protected $stringHeaders;
    protected $request;
    protected $rawRequestBody;
    protected $body;
    protected $peerName;

    /* Private Vars */
    private $config;
    private $exts;
    private $extManager;
    private $extInstances;

    /* Constants */
    const SERVER_NAME = "Leloutama";

    /**
     * Client constructor.
     * Constructs the Client instance.
     * @param Router $router
     * @param array $exts
     * @param array $packet
     * @param string $peerName
     */
    public function __construct(Router $router, array $exts, array $packet, string $peerName) {
        $this->config = json_decode(file_get_contents(__DIR__ . "/../../../config/Core/config.json"), true);

        $this->peerName = $peerName;
        $this->exts = $exts;
        $this->extManager = new ServerExtensionManager($this->config);

        $this->extInstances = $extInstances = $this->loadExt($exts);

        $stringHeaders = $packet["headers"];
        $rawRequestBody = $packet["body"];

        foreach($this->extInstances as $ext) {
            $beforeConstruct = $ext->beforeConstruct($router, $stringHeaders, $rawRequestBody);

            if($beforeConstruct !== null) {
                $router = $beforeConstruct["rotuer"];

                $stringHeaders = $beforeConstruct["stringHeaders"];

                $rawRequestBody = $beforeConstruct["rawRequestBody"];
            }
        }

        $this->router = $router;

        $this->stringHeaders = $stringHeaders;

        $this->rawRequestBody = $rawRequestBody;

        $this->http = (new Http($stringHeaders))
            ->setRequestBody($rawRequestBody);

        $this->body = new Body();
    }

    /**
     * The public method to call to initialize the whole serving process.
     * Returns the response as a string, which can be just socket_write'en.
     * @return string
     */
    public function serve(): string {
        $this->http->Headerize();
        $this->http->parseHeaders();

        $this->body->load($this->rawRequestBody);

        $this->body->parse();

        $this->buildRequest();

        printf("Request Received\n\tTime: %s\n\tRequested Resource: %s \n\tMethod: %s\n",
            date("M d Y-H:i:s "),
            $this->http->getRequestedResource(),
            $this->http->getMethod()
        );

        $response = $this->process();

        foreach ($this->extInstances as $ext) {
            $extFinalServeOp = $ext->beforeFinalServe($response);

            if($extFinalServeOp !== null) {
                $response = $extFinalServeOp;
            }
        }

        $responseHeaders = $response[0];
        $responseBody = $response[1];


        $finalPacket = $responseHeaders . "\r\n\r\n" . $responseBody;

        return $finalPacket;
    }

    protected function buildRequest() {
        $cookies = $this->http->getCookies();
        $requestedResource = $this->http->getRequestedResource();

        $this->request = (new Request)
            ->setCookies($cookies)
            ->setRequestedResource($requestedResource)
            ->setIfNoneMatch($this->http->getHeaderParam("If-None-Match"));

        if($this->http->getMethod() === "POST") {
            $this->request->setPostData([
                "raw" => $this->body->getRawBody(),
                "parsed" => $this->body->getParsedBody()
            ]);
        } elseif($this->http->getMethod() === "GET") {
            $queryString = $this->http->getQueryString();
            $parsed = $this->body->parse($queryString, "&");
            $this->request->setQueryParams([
                "raw" => $queryString,
                "parsed" => $parsed
            ]);
        }

        foreach ($this->extInstances as $ext) {
            $extAfterRequestBuild = $ext->afterRequestBuild($this->request, $this->http);

            if($extAfterRequestBuild !== null) {
                $this->request = $extAfterRequestBuild;
            }
        }
    }

    /**
     * Processes the router, gets the requested resource, creates the headers, and returns an array.
     * If there is some error, it calls the methods to get the content, and also returns an array with the structure of
     * [0] => headers, [1] => body
     * @return array
     */
    private function process(): array {
        $routeInfo =  $this->http->getInfo($this->http->getRequestedResource(), $this->router);
        if(!$routeInfo) {
            $toServeContent = $this->get404();
            $mime = "text/html";
            $status = 404;
        } else {
            if($this->http->getMethod() !== "GET" && $this->http->getMethod() !== "POST") {
                $toServeContent = $this->get405();
                $mime = "text/html";
                $status = 405;
            } else {
                $response = $routeInfo["response"];
                $response->setRequest($this->request)->loadConfig($this->config);

                $response->onReady($response->getOnReadyMethodArgs());

                if(sprintf('"%d"', $this->http->getEtag($this->encodeBody($response->getBody())[0])) == $this->http->getHeaderParam("If-None-Match")){
                    $toServeContent = $response->getBody();
                    $status = 304;
                    $mime = $response->getMime();
                } else {
                    $toServeContent = $response->getBody();
                    $status = $response->getStatus();
                    $mime = $response->getMime();
                }
            }
        }

        foreach($this->extInstances as $ext) {
            $extBeforeHeaderCreationCall = $ext->beforeHeaderCreationCall($toServeContent, $mime, $status);

            if($extBeforeHeaderCreationCall !== null) {
                $toServeContent = $extBeforeHeaderCreationCall["content"];
                $mime = $extBeforeHeaderCreationCall["mime"];
                $status = $extBeforeHeaderCreationCall["status"];
            }
        }

        return $this->createHeaders($toServeContent, $mime, $status);
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

    private function createHeaders(string $content, string $mimeType, int $status = 200): array {
        $headers = [];

        $headers[] = sprintf("HTTP/1.1 %d %s", $status, Http::HTTP_REASON[$status]);

        $encodeOP = $this->encodeBody($content);

        $headers[] = sprintf("Content-Type: %s", $mimeType);

        if(!empty($encodeOP)) {
            $content = $encodeOP[0];
            $headers[] = sprintf("Content-Encoding: %s", $encodeOP[1]);
        }

        $headers[] = sprintf("Content-Length: %d", strlen($content));

        $this->createCacheHeaders($this->http->getEtag($content), $headers);

        $headers[] = sprintf("X-Powered-By: %s", self::SERVER_NAME);

        foreach($this->extInstances as $ext) {
            $extAfterHeaderCreation = $ext->afterHeaderCreation($headers, $content, $mimeType, $status);

            if($extAfterHeaderCreation !== null) {
                $headers = $extAfterHeaderCreation;
            }
        }

        $this->logResponse(sprintf("%d %s", $status, Http::HTTP_REASON[$status]));

        if($status === 304) {
            $content = "";
        }

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

    private function loadExt(array $exts) {
        $extCount = count($exts);
        $extensionsBundle = [];
        for($i = 0; $i < $extCount; $i++) {
            $extName = $exts[$i];
            try {
                $extensionsBundle[$extName] = $this->extManager->load($extName);
            } catch(\Exception $ex) {
                printf("Couldn't load the extension %s, because of the reason - %s.\nContinuing without loading it.\n",
                    $extName,
                    $ex->getMessage()
                );
            }
        }
        return $extensionsBundle;
    }

    private function createCacheHeaders(string $etag, array &$headers) {
        $scope = (isset($this->config["Cache-Config"]["scope"])) ? $this->config["Cache-Config"]["scope"] : "public";
        $maxAge = (isset($this->config["Cache-Config"]["max-age"])) ? $this->config["Cache-Config"]["max-age"] : 120;

        $headers[] = sprintf('Cache-Control: %s, max-age=%d',
            $scope,
            $maxAge
        );
        $headers[] = sprintf('Etag: "%s"', $etag);
    }

    protected function logResponse(string $status) {
        printf("Response Sent\n \t For Resource: %s\n \t Status: %s\n",
            $this->http->getRequestedResource(),
            $status
        );
    }
}