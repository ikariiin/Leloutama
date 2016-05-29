<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 27/4/16
 * Time: 10:28 PM
 */

namespace Leloutama\lib\Core\Server;

require_once __DIR__ . "/autoloads.php";

use FastRoute\Dispatcher;
use Leloutama\lib\Core\Server\Utilities\Creator;
use Leloutama\lib\Core\Server\Utilities\Logger;
use Leloutama\lib\Core\Server\Utilities\RequestBuilder;
use SuperClosure\Serializer;
use Leloutama\lib\Core\Server\Utilities\ServerContentGetter;
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
     * @param  $router
     * @param array $exts
     * @param array $packet
     * @param string $peerName
     */
    public function __construct(Dispatcher $router, array $exts, array $packet, string $peerName) {
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
        try {
            $this->http->Headerize();
            $this->http->parseHeaders();

            $this->body->load($this->rawRequestBody);

            $this->body->parse();

            $this->buildRequest();

            (new Logger($this->http))
                ->logRequest();

            $response = $this->process();

            foreach ($this->extInstances as $ext) {
                $extFinalServeOp = $ext->beforeFinalServe($response);

                if($extFinalServeOp !== null) {
                    $response = $extFinalServeOp;
                }
            }

            $responseHeaders = $response["headers"];
            $responseBody = $response["content"];


            $finalPacket = $responseHeaders . "\r\n\r\n" . $responseBody;

            return $finalPacket;
        }
        catch (\Throwable $ex) {
            exit(sprintf(
                "An Catchable Fatal Error Was Faced in the Server. The server would no longer continue to work until, a restart is done for the server, or an solution is made for the problem.\nError description: %s\n",
                $ex->getMessage()
            ));
        }
    }

    protected function buildRequest() {
        $this->request = (new RequestBuilder())
            ->buildRequest($this->http, $this->body);

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
        $toServeContent = (new ServerContentGetter())
            ->get500();
        $mime = "text/html";
        $status = 500;
        try {
            $fastRouter = $this->router;

            $routeInfo = $fastRouter->dispatch($this->http->getMethod(), $this->http->getRequestedResource());
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    $toServeContent = (new ServerContentGetter())
                        ->get404();

                    $mime = "text/html";

                    $status = 404;
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $toServeContent = (new ServerContentGetter())
                        ->get405();

                    $mime = "text/html";

                    $status = 405;
                    break;
                case Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    // Deserialize the handler if it isn't already

                    if(!($handler instanceof \Closure)) {
                        $serializer = new Serializer();
                        // Deserialize the handler
                        $handler = $serializer->unserialize($handler);
                    }
                    $vars = $routeInfo[2];

                    // Invoke the handler
                    $response = $handler($this->request, $vars);

                    $toServeContent = $response->getContent();
                    $mime = $response->getMime();

                    $status = 200;
                    break;
            }
        } catch (\Throwable $ex) {
            printf("There was an error in the server, description: %s\nIn file: %s\nIn line: %s\n",
                $ex->getMessage(),
                $ex->getFile(),
                $ex->getLine()
            );
        }

        return $this->create($toServeContent, $mime, $status);
    }

    protected function formatBody(string $body): string {
        return implode("\r\n", explode("\n", $body));
    }

    private function create(string $content, string $mimeType, int $status): array {
        $creator = new Creator($this->http, $this->config);
        $headers = $creator->create($content, $mimeType, $status);

        foreach($this->extInstances as $ext) {
            $extAfterHeaderCreation = $ext->afterHeaderCreation($headers, $content, $mimeType, $status);

            if($extAfterHeaderCreation !== null) {
                $headers = $extAfterHeaderCreation;
            }
        }
        return $creator->afterFirstPhase($headers, $content, $status);
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
}