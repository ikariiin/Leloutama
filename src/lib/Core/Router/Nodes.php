<?php
/**
 * @author Gourab Nag
 * File Name: Router.php
 * Time: Sunday, April 24, 14:41
 */

namespace Leloutama\lib\Core\Router;

class Nodes {
    protected $expose;
    protected $response;
    protected $method;

    /**
     * Constructor for providing an instance of self.
     */
    public function __construct() {
        return $this;
    }

    /**
     * Sets the exposure request route of the route.
     * @param string $expose
     * @return self
     */
    public function setExposure(string $expose): self {
        $this->expose = $expose;
        return $this;
    }

    public function setResponse($response): self {
        $this->response = $response;
        return $this;
    }

    public function getExposedRoute(): string {
        return $this->expose;
    }

    public function getResponse() {
        return $this->response;
    }
}