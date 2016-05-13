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
     * Needs the user defined router as the first argument, and the raw string headers separated by \r\n, as the second.
     * @param Router $router
     * @param array $exts
     * @param $stringHeaders
     */
    public function __construct(Router $router, array $exts, string $stringHeaders) {
        $this->config = json_decode(file_get_contents(__DIR__ . "/../../../config/Core/config.json"), true);

        $this->exts = $exts;
        $this->extManager = new ServerExtensionManager($this->config);

        $this->extInstances = $extInstances = $this->loadExt($exts);

        foreach($this->extInstances as $ext) {
            $beforeConstruct = $ext->beforeConstruct($router, $stringHeaders);

            if($beforeConstruct !== null) {
                $router = $beforeConstruct["rotuer"];

                $stringHeaders = $beforeConstruct["stringHeaders"];
            }
        }

        $this->router = $router;

        $this->stringHeaders = $stringHeaders;

        $this->http = new Http($stringHeaders);
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

        $this->request = (new Request())
            ->setCookies($cookies)
            ->setRequestedResource($requestedResource);

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
        $response = $this->http->getInfo($this->http->getRequestedResource(), $this->router);
        if(!$response) {
            $toServeContent = $this->get404();
            $mime = "text/html";
            $status = 404;
        } else {
            if($this->http->getMethod() !== "GET") {
                $toServeContent = $this->get405();
                $mime = "text/html";
                if(sprintf('"%d"', $this->http->getEtag($this->encodeBody($this->get405())[0])) == $this->http->getHeaderParam("If-None-Match")){
                    $status = 304;
                } else {
                    $status = 405;
                }
            } else {
                $response->setRequest($this->request)->loadConfig($this->config);

                $response->onReady($response->getOnReadyMethodArgs());

                if(sprintf('"%d"', $this->http->getEtag($this->encodeBody($response->getBody())[0])) == $this->http->getHeaderParam("If-None-Match")){
                    $toServeContent = $response->getBody();
                    $status = 304;
                    $mime = $response->getMime();
                } else {
                    var_dump($response);
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

        var_dump($toServeContent);

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

        $headers[] = sprintf("HTTP/1.1 %d %s", $status, $this->http->HTTP_REASON[$status]);

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

        $this->logResponse(sprintf("%d %s", $status, $this->http->HTTP_REASON[$status]));

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
        $headers[] = sprintf('Cache-Control: %s, max-age=%d',
            $this->config["Cache-Config"]["scope"],
            $this->config["Cache-Config"]["max-age"]
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