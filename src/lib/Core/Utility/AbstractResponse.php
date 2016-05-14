<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 6/5/16
 * Time: 10:31 PM
 */

namespace Leloutama\lib\Core\Utility;

include "ClientExtensionManager.php";

abstract class AbstractResponse {
    protected $body = "";
    protected $mime = "text/html";
    protected $onReadyMethod;
    protected $onReadyMethodArgs;
    protected $status = 200;
    protected $extensionManager;

    private $request;
    private $config;

    abstract public function onReady($arguments);

    public function initializeExtensionManager() {
        $this->extensionManager = new ClientExtensionManager($this->request, $this->config);
    }

    public function setRequest(Request $request) {
        $this->request = $request;
        return $this;
    }

    public function setBody(string $body){
        $this->body = $body;
    }

    public function setMime(string $mime) {
        $this->mime = $mime;
    }

    public function setStatus(int $code) {
        $this->status = $code;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function getMime(): string {
        return $this->mime;
    }

    public function getStatus(): int {
        return $this->status;
    }

    public function getRequest(): Request {
        return $this->request;
    }

    public function __construct($arguments) {
        $this->onReadyMethodArgs = $arguments;
        return $this;
    }

    public function getOnReadyMethodArgs() {
        return $this->onReadyMethodArgs;
    }

    public function loadConfig(array $config) {
        $this->config = $config;
        return $this;
    }

    public function getConfig(string $key) {
        return $this->config[$key];
    }

    public function set(array $data) {
        $body = $data["body"];
        $status = $data["status"];
        $mime = $data["mime"];

        $this->setBody($body);
        $this->setStatus($status);
        $this->setMime($mime);

        return true;
    }
}