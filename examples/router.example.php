<?php

class Response extends \Leloutama\lib\Core\Utility\AbstractResponse {
    public function setBody(string $content): self {
        $this->body = $content;
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

    public function setStatus(int $code): self {
        $this->status = $code;
        return $this;
    }

    public function makeUp(string $fileName) {
        $this->setFileName($fileName);

        $this->setMime(mime_content_type($this->getConfig("docRoot") . $fileName));

        $this->setStatus(200);

        $body = file_get_contents($this->getConfig("docRoot") . $fileName);

        $this->setBody($body);
    }
}

$router = new \Leloutama\lib\Core\Router\Router();

$indexResponse = new Response();
$indexResponse->setOnReadyMethod("makeUp");
$indexResponse->setOnReadyMethodArgs("index.html");

$router->bind("/", $indexResponse);

return $router;