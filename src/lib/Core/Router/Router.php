<?php
/**
 * @author Gourab Nag
 * File Name: Router.php
 * Time: Sunday, April 24, 14:41
 */

namespace Leloutama\lib\Core\Router;
include  __DIR__ . "/Nodes.php";

class Router {
    protected $Routes = [];

    /**
     * Router, function for setting a route.
     * @param string $exposureNode
     * @param string $content
     * @param string $mime
     */
    public function setRoute(string $exposureNode = "/*", string $content = "<h1>It works.</h1>", string $mime = "text/html") {
        $this->Routes[] = (new Nodes())
            ->setExposure($exposureNode)
            ->setContent($content)
            ->setMime($mime);
    }

    public function getRoutes(){
        return $this->Routes;
    }
}