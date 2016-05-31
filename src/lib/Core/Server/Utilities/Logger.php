<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/5/16
 * Time: 5:03 PM
 */

namespace Leloutama\lib\Core\Server\Utilities;
use Leloutama\lib\Core\Server\Http;

class Logger {
    private $http;

    public function __construct(Http $http) {
        $this->http = $http;
        return $this;
    }

    public function logRequest() {
        printf("Request Received\n\tTime: %s\n\tRequested Resource: %s \n\tMethod: %s\n",
            date("M d Y-H:i:s "),
            $this->http->getRequestedResource(),
            $this->http->getMethod()
        );
    }

    public function logResponse(string $status) {
        printf("Response Sent\n \t For Resource: %s\n \t Status: %s\n",
            $this->http->getRequestedResource(),
            $status
        );
    }

    public function logError(\Throwable $ex) {
        printf("There was an error in the server, description: %s\nIn file: %s\nIn line: %s\n",
            $ex->getMessage(),
            $ex->getFile(),
            $ex->getLine()
        );
    }
}