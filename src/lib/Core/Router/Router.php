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
     * @param \Response $response
     */
    public function bind(string $exposureNode, $response) {
        $this->Routes[] = (new Nodes())
            ->setExposure($exposureNode)
            ->setResponse($response);
    }

    public function getRoutes(){
        return $this->Routes;
    }
}