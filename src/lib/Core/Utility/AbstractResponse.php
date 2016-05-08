<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 6/5/16
 * Time: 10:31 PM
 */

namespace Leloutama\lib\Core\Utility;
abstract class AbstractResponse {
    protected $body = "";
    protected $mime = "text/html";
    protected $fileName = "";
    protected $onReadyMethod;
    protected $onReadyMethodArgs;
    protected $status = 200;

    private $request;
    private $config;

    public function __construct() {
        return $this;
    }

    public function setRequest(Request $request) {
        $this->request = $request;
        return $this;
    }

    abstract public function setBody(string $body);
    abstract public function setMime(string $mime);
    abstract public function setFileName(string $fileName);
    abstract public function setStatus(int $code);

    public function getBody(): string {
        return $this->body;
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function getMime(): string {
        return $this->mime;
    }

    public function getStatus(): int {
        return $this->status;
    }

    public function getRequest() {
        return $this->request;
    }

    public function setOnReadyMethod(string $method) {
        $this->onReadyMethod = $method;
        return $this;
    }

    public function setOnReadyMethodArgs($arguments) {
        $this->onReadyMethodArgs = $arguments;
        return $this;
    }

    public function onReady() {
        $this->{$this->onReadyMethod}($this->onReadyMethodArgs);
        return $this;
    }

    public function loadConfig(array $config) {
        $this->config = $config;
        return $this;
    }

    public function getConfig(string $key) {
        return $this->config[$key];
    }
}