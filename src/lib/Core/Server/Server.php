<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 27/4/16
 * Time: 2:27 PM
 */

namespace Leloutama\lib\Core\Server;
require __DIR__ . "/Client.php";
require __DIR__ . "/ThreadDispatcher.php";
use Leloutama\lib\Core\Router\Router;

class Server {
    private $router;
    private $args;

    public function __construct(Router $router, array $exts = [], string $ipAddress = "127.0.0.1", int $port = 2406) {
        $this->args = [$router, $exts ,$ipAddress, $port];

        $this->router = $router;
    }

    public function startServer() {
        $webserverCallable = $this->dispatchMainThread();

        $webserverStart = new ThreadDispatcher($webserverCallable, $this->args);

        /**
         * After implementing the basic things, we need to make a call to a function on a separate thread, which would
         * create another webserver instance, and serve some specific files, for a dashboard, which would give
         * information about the incoming and outgoing requests.
         */

        $webserverStart->start();
    }

    private function dispatchMainThread(): callable {
        $webserverCallable = function(array $args) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if(!socket_bind($socket, $args[2], $args[3])) {
                exit("Socket connection could not be initialized due to the error: " . socket_strerror(socket_last_error($socket)) . "\n");
            }
            if(!socket_listen($socket)) {
                exit("Socket connection could not be initialized due to the error: " . socket_strerror(socket_last_error($socket)) . "\n");
            }

            printf("Server started successfully.\nListening on ip: %s at port: %d\n",
                $args[2],
                $args[3]
            );
            while(($client = socket_accept($socket))) {
                $http = new Http();
                $stringHeaders = $http->parseRawSocketRequest($client);

                if(strlen($stringHeaders) > 0) {
                    $ClientThread = new ThreadDispatcher(function(array $arguments, &$_this){
                        $random = rand();
                        $uid = hash("gost", $random);
                        $client[$uid] = new Client($arguments[0], $arguments[1] , $arguments[2]);

                        $serveOP = $client[$uid]->serve();
                        if(!empty($serveOP)) {
                            $_this->response = $serveOP;

                            $client[$uid] = null;
                            unset($client[$uid]);
                        }

                        return false;
                    }, [$args[0], $args[1] ,$stringHeaders]);

                    $ClientThread->run();

                    if(isset($ClientThread->response)) {
                        socket_write($client, $ClientThread->response);
                        socket_close($client);
                    }
                }
            }
        };

        return $webserverCallable;
    }
}
