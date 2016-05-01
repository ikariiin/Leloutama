<?php
/**
 * @author Gourab Nag
 * File Name: Router.php
 * Time: Sunday, April 24, 14:41
 */

namespace Leloutama\lib\Core\Router;
include "Nodes.php";

class Router {
    protected $Routes = [];

    /**
     * Router, function for setting a route.
     * @param string $ExposureNode
     * @param string $Content
     */
    public function setRoute(string $ExposureNode = "/*", string $Content = "<h1>It works.</h1>") {
        $this->Routes[] = (new Nodes())->setExposure($ExposureNode)->setContent($Content);
    }

    public function getRoutes(){
        return $this->Routes;
    }
}