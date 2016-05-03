<?php
/**
 * @author Gourab Nag
 * File Name: Router.php
 * Time: Sunday, April 24, 14:41
 */

namespace Leloutama\lib\Core\Router;

class Nodes {
    protected $expose;
    protected $content;
    protected $mime;

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

    /**
     * Sets the pointer, which identifies which file, to point to.
     * @param string $content
     * @return Nodes
     */
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    public function setMime(string $mime) {
        $this->mime = $mime;
        return $this;
    }

    public function getMime() {
        return $this->mime;
    }

    public function getExposedRoute(): string {
        return $this->expose;
    }

    public function getContent(): string {
        return $this->content;
    }
}