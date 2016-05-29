<?php

namespace FastRoute;

use SuperClosure\Serializer;

class RouteCollector {
    private $routeParser;
    private $dataGenerator;

    /**
     * Constructs a route collector.
     *
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator) {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed  $handler
     */
    public function addRoute($httpMethod, $route, $handler) {
        /*
         * My custom patch...
         * Instantiate an SuperClosure instance.
         * Serialize the handler before inserting it.
         */
        $serializer = new Serializer();

        // Serialize the handler.
        $serializedHandler = $serializer->serialize($handler);
        $routeDatas = $this->routeParser->parse($route);
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                // Use the serialized handler, rather than the original handler.
                $this->dataGenerator->addRoute($method, $routeData, $serializedHandler);
            }
        }
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData() {
        return $this->dataGenerator->getData();
    }
}
