<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 27/4/16
 * Time: 10:28 PM
 */

namespace Leloutama\lib\Core\Server;
require "Http.php";
use Leloutama\lib\Core\Router\Router;

class Client {
    protected $router;
    protected $http;
    protected $stringHeaders;

    public function __construct(Router $router, $stringHeaders) {
        $this->router = $router;
        $this->stringHeaders = $stringHeaders;
        $this->http = new Http($stringHeaders);
    }

    public function serve(): bool {
        $this->http->Headerize();
        $this->http->parseHeaders();

        $toServeContent = "";

        $getContent = $this->http->matchRoute($this->http->getRequestedResource(), $this->router);
        if(!$getContent) {
            // TODO: make a serve 404 function
            $this->serve404();
            return true;
        } else {
        }

        return true;
    }
}