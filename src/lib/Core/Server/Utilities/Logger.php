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
        $request = sprintf("Request Received\n\tTime: %s\n\tRequested Resource: %s \n\tMethod: %s\n",
            date("M d Y-H:i:s "),
            $this->http->getRequestedResource(),
            $this->http->getMethod()
        );
        echo "\nLOG LEVEL: NORMAL" . $request;
        $previousContent = file_get_contents(__DIR__ . "/../../../../logs/Leloutama.log");
        file_put_contents(__DIR__ . "/../../../../logs/Leloutama.log", $previousContent . "\nLOG LEVEL: NORMAL\n" . $request);
    }

    public function logResponse(string $status) {
        $response = sprintf("Response Sent\n \t For Resource: %s\n \t Status: %s\n",
            $this->http->getRequestedResource(),
            $status
        );
        echo $response;
        $previousContent = file_get_contents(__DIR__ . "/../../../../logs/Leloutama.log");
        file_put_contents(__DIR__ . "/../../../../logs/Leloutama.log", $previousContent . "\nLOG LEVEL: NORMAL\n" . $response);
    }

    public function logError(\Throwable $ex) {
        printf("There was an error in the server, description: %s\nIn file: %s\nIn line: %s\n",
            $ex->getMessage(),
            $ex->getFile(),
            $ex->getLine()
        );
        $error = "\nLOG LEVEL: ERROR";
        $error .= "TIME: " . date("M d Y-H:i:s ") . "\n";
        $error .= "MESSAGE: " . $ex->getMessage() . "\n";
        $error .= "STACK TRACE: " . $ex->getTraceAsString() . "\n";
        $previousContent = file_get_contents(__DIR__ . "/../../../../logs/Leloutama.log");
        file_put_contents(__DIR__ . "/../../../../logs/Leloutama.log", $previousContent . $error);
    }
}