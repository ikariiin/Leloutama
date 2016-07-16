<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 14/5/16
 * Time: 1:26 AM
 */

namespace Leloutama\lib\Core\Server;
use Leloutama\lib\Core\Modules\Responses\HttpResponse;

class CgiInterface {
    private $http;
    private $peerInfo;
    private $fileName;
    private $port;

    /**
     * CgiInterface constructor.
     * @param Http $http
     * @param int $port
     * @param string $peerInfo
     * @param HttpResponse $response
     */
    public function __construct(Http $http, int $port, string $peerInfo, HttpResponse $response) {
        $this->http = $http;
        $this->port = $port;
        $this->peerInfo = $peerInfo;

        $envVars = $this->createEnvVars();
        $messageBody = $this->giveMessageBody();
    }

    private static function analyseResponse(HttpResponse $response) {
        //
    }

    /**
     * Creates the ENV vars for the cgi process, and returns an array consisting of them.
     * @internal Http $http
     * @return array
     */
    private function createEnvVars(): array {
        /* Initialize the env array. */
        $env = [];

        /* Firstly, we need to parse the required details from the Http object */
        // See: http://www.faqs.org/rfcs/rfc3875.html for details about the env vars.

        // First the query string.
        $env["QUERY_STRING"] = $this->http->getQueryString();
        // Then requested URI.
        $env["REQUESTED_URI"] = $this->http->getRequestedResource();
        // Then request method.
        $env["REQUEST_METHOD"] = $this->http->getMethod();
        // Need to check if there is any request body
        if(strlen($this->http->getRequestBody()) !== 0) {
            // Set the content length
            $env["CONTENT_LENGTH"] = $this->http->getHeaderParam("Content-Length");
            // Set the content type
            $env["CONTENT_TYPE"] = $this->http->getHeaderParam("Content-Type");
        }
        // Set the server protocol.
        $env["SERVER_PROTOCOL"] = "HTTP/1.1";
        // Set the server software.
        $env["SERVER_SOFTWARE"] = "Leloutama v1.2";
        // Set server port.
        $env["SERVER_PORT"] = $this->port;
        // Set remote address.
        $env["REMOTE_ADDR"] = $this->peerInfo;
        // Set redirect status.
        $env["REDIRECT_STATUS"] = 200;
        // Set server name.
        $env["SERVER_NAME"] = $this->http->getHeaderParam("Host");
        // Set script name.
        $env["SCRIPT_NAME"] = $this->http->getRequestedResource();
        // Set script file name.
        $env["SCRIPT_FILENAME"] = $this->fileName;

        /*
         * Done all the hardcoded things, now need to set the HTTP_... things.
         */
        // Get the parsed headers.
        $headers = $this->http->getParsedHeaders();
        // Unset the 'route' key.
        unset($headers["route"]);
        // Iterate over the headers.
        foreach ($headers as $header => $value) {
            // Normalize the $header.
            $header = str_replace("-", "_", $header);
            $header = mb_strtoupper($header);
            // Set the env.
            $env[sprintf("HTTP_%s", $header)] = $value;
        }
        // Merge current $_ENV and $env
        $finalEnv = array_merge($_ENV, $env);

        // Finally RETURN the fuckin' environment variable.
        return $finalEnv;
    }

    private function giveMessageBody(): string {
        return $this->http->getRequestBody();
    }
}