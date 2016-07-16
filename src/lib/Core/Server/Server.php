<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 27/4/16
 * Time: 2:27 PM
 */

namespace Leloutama\lib\Core\Server;
use FastRoute\Dispatcher;
use Leloutama\lib\Core\Http\Http;
use Leloutama\lib\Core\Http\HttpEndpoint;
use Leloutama\lib\Core\Modules\Generic\Logger;

class Server {
    private $router;
    private $args;

    public function __construct(Dispatcher $router, array $exts = [], string $ipAddress = "127.0.0.1", int $port = 2406) {
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
            try {
                /*
                 * Delete twig cache!
                 */
                TwigCacheDeletor::delete();
                $stream = stream_socket_server(sprintf("tcp://%s:%d",
                    $args[2],
                    $args[3]
                ), $errno, $errmsg);

                if(!$stream) {
                    Logger::logServerStartUpError($errmsg);
                    exit;
                }

                stream_set_blocking($stream, 1);

                printf("Server started successfully.\nListening on ip: %s at port: %d\n",
                    $args[2],
                    $args[3]
                );
                while(true) {
                    $client = stream_socket_accept($stream);

                    if($client) {

                        $peerName = stream_socket_get_name($client, true);
                        $stringHeaders = trim(fread($client, 4096));
                        $parsedPacket = Http::parsePacket($stringHeaders);

                        if(strlen($stringHeaders) > 0) {
                            $ClientThread = new ThreadDispatcher(function(array $arguments, &$_this){
                                $random = rand();
                                $uid = hash("gost", $random);

                                $requestType = RequestTypeAnalyser::type($arguments[4]);
                                
                                if ($requestType === "http") {
                                    $client[$uid] = new HttpEndpoint($arguments[0], $arguments[1] , $arguments[2], $arguments[3]);
                                }

                                $serveOP = $client[$uid]->serve();
                                if(!empty($serveOP)) {
                                    $_this->response = $serveOP;
                                    $client[$uid] = null;
                                    unset($client[$uid]);
                                }

                                return false;
                            }, [$args[0], $args[1], $parsedPacket, $peerName, $stringHeaders]);

                            $ClientThread->run() && $ClientThread->join();;

                            if(isset($ClientThread->response)) {
                                fwrite($client, $ClientThread->response);
                                fclose($client);
                            }
                        }
                    }
                }
            } catch (\Throwable $ex) {
                (new Logger((new Http()), file_get_contents(__DIR__ . "/../../../config/config.json")))
                    ->logError($ex);
            }
        };

        return $webserverCallable;
    }
}
