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
use Leloutama\lib\Core\Utility\Response;
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
        try {
            $this->config = json_decode(file_get_contents(__DIR__ . "/../../../config/config.json"), true);

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
        } catch (\Throwable $ex) {
            (new Logger($this->http))
                ->logError($ex);
        }
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

            $responseHeaders = $response->getHeadersAsString();
            $responseBody = $response->getContent();


            $finalPacket = $responseHeaders . "\r\n\r\n" . $responseBody;

            return $finalPacket;
        }
        catch (\Throwable $ex) {
            (new Logger($this->http))
                ->logError($ex);
            $response = (new Response($this->request))
                ->setContent((new ServerContentGetter())
                    ->get500())
                ->setMime("text/html")
                ->setStatus(500);

            $creator = (new Creator($this->http, $this->config));
            $response = $creator->create($response);
            $response = $creator->afterFirstPhase($response);
            $response = $response->getHeadersAsString() . "\r\n\r\n" . $response->getContent();

            return $response;
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
     * @return Response
     */
    private function process(): Response {
        $response = (new Response($this->request))
            ->setContent((new ServerContentGetter())
                ->get500())
            ->setMime("text/html")
            ->setStatus(500);
        $fastRouter = $this->router;

        $routeInfo = $fastRouter->dispatch($this->http->getMethod(), $this->http->getRequestedResource());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response = (new Response($this->request))
                    ->setContent((new ServerContentGetter())
                        ->get404())
                    ->setStatus(404)
                    ->setMime("text/html");
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = (new Response($this->request))
                    ->setContent((new ServerContentGetter())
                        ->get405())
                    ->setMime("text/html")
                    ->setStatus(405);
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
                break;
        }

        return $this->create($response);
    }

    protected function formatBody(string $body): string {
        return implode("\r\n", explode("\n", $body));
    }

    private function create(Response $response): Response {
        $creator = new Creator($this->http, $this->config);
        $response = $creator->create($response);

        foreach($this->extInstances as $ext) {
            $extAfterHeaderCreation = $ext->afterCreation($response);

            if($extAfterHeaderCreation !== null) {
                $response = $extAfterHeaderCreation;
            }
        }
        return $creator->afterFirstPhase($response);
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