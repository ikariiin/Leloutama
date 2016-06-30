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
    private $config;

    static $logsFile;

    public function __construct(Http $http, array $config) {
        $this->http = $http;
        $this->config = $config;

        self::$logsFile = $config["Logs"];

        return $this;
    }

    public function logRequest() {
        $request = sprintf("Request Received\n\tTime: %s\n\tRequested Resource: %s \n\tMethod: %s\n",
            date("M d Y-H:i:s "),
            $this->http->getRequestedResource(),
            $this->http->getMethod()
        );
        echo "\nLOG LEVEL: NORMAL\n" . $request;

        self::store("\nLOG LEVEL: NORMAL\n" . $request);
    }

    public function logResponse(string $status) {
        $response = sprintf("Response Sent\n \t For Resource: %s\n \t Status: %s\n",
            $this->http->getRequestedResource(),
            $status
        );
        echo "\nLOG LEVEL: NORMAL\n" . $response;

        self::store("\nLOG LEVEL: NORMAL\n" . $response);
    }

    public function logError(\Throwable $ex) {
        printf("There was an error in the server, description: %s\nIn file: %s\nIn line: %s\n",
            $ex->getMessage(),
            $ex->getFile(),
            $ex->getLine()
        );
        $error = "\nLOG LEVEL: ERROR\n";
        $error .= "TIME: " . date("M d Y-H:i:s ") . "\n";
        $error .= "MESSAGE: " . $ex->getMessage() . "\n";
        $error .= "STACK TRACE: " . $ex->getTraceAsString() . "\n";

        self::store($error);
    }

    public static function logServerStartUpError(string $errstr) {
        $error =  sprintf("Server Startup Error: %s\n", $errstr);
        $error = "\nLOG LEVEL: ERROR\n" . $error;

        echo $error;
        self::store($error);
    }

    private static function store(string $payload) {
        if(isset(self::$logsFile)) {
            $logsFile = self::$logsFile;
        } else {
            $logsFile = json_decode(__DIR__ . "/../../../config/config.json", true)["Logs"];
        }
        $handle = fopen($logsFile, "a");
        fwrite($handle, $payload);
        fclose($handle);
    }
}