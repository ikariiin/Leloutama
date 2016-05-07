<?php
require "../src/lib/Core/Router/Router.php";
require "../src/lib/Core/Server/Server.php";
require "../src/lib/Core/Utility/AbstractResponse.php";

class Response extends \Leloutama\lib\Core\Utility\AbstractResponse {
    public function setBody(array $fileName): self {
        $body = file_get_contents($fileName[0]);

        $this->setFileName($fileName[0]);

        $this->setMime(mime_content_type($fileName[0]));

        $this->body = $body . "\n \n " . $this->getRequest()->getRequestedResource();
        return $this;
    }

    public function setFileName(string $fileName): self {
        $this->fileName = $fileName;
        return $this;
    }

    public function setMime(string $mime): self {
        $this->mime = $mime;
        return $this;
    }
}

// Initialize the Router
$router = new Leloutama\lib\Core\Router\Router();

$indexResponse = new Response();
$indexResponse->setOnReadyMethod("setBody");
$indexResponse->setOnReadyMethodArgs(["/home/gourab/Jeeves/README.md"]);

// Set a route
$router->setRoute("/some-php-thing", $indexResponse);

// Initialize the server
$server = new \Leloutama\lib\Core\Server\Server($router, "127.0.0.1", 8000);

// Start the server
$server->startServer();