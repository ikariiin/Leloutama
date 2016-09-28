<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 27/4/16
 * Time: 2:27 PM
 */

namespace Leloutama\lib\Core\Server;
use FastRoute\Dispatcher;
use Leloutama\lib\Core\Http\Body;
use Leloutama\lib\Core\Http\Http;
use Leloutama\lib\Core\Http\HttpEndpoint;
use Leloutama\lib\Core\IPC\Loop\Loop;
use Leloutama\lib\Core\IPC\Loop\ServerSocket;
use Leloutama\lib\Core\IPC\WebsocketTransport\IServer;
use Leloutama\lib\Core\Modules\Generic\Logger;
use Leloutama\lib\Core\Modules\Http\Request;
use Leloutama\lib\Core\Modules\Http\RequestBuilder;
use Leloutama\lib\Core\Websocket\Handshake;

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
        $webserverCallable = function(&$_this, Dispatcher $dispatcher, array $exts, string $ipAddress, int $port) {
            try {
                /*
                 * Delete twig cache!
                 */
                TwigCacheDeletor::delete();
                $stream = stream_socket_server(sprintf("tcp://%s:%d",
                    $ipAddress,
                    $port
                ), $errno, $errmsg);

                if(!$stream) {
                    Logger::logServerStartUpError($errmsg);
                    exit;
                }

                stream_set_blocking($stream, 1);

                printf("Server started successfully.\nListening on ip: %s at port: %d\n",
                    $ipAddress,
                    $port
                );

                (new ThreadDispatcher([Server::class, "commInit"], []))
                    ->start();

                var_dump("?");
                
                while(true) {
                    $client = stream_socket_accept($stream);

                    if($client) {
                        $peerName = stream_socket_get_name($client, true);
                        $sentString = trim(fread($client, 4096));
                        $requestType = RequestTypeAnalyser::type($sentString);

                        if ($requestType[0] === "http" && strlen($sentString) > 0) {
                            $parsedPacket = Http::parsePacket($sentString);

                            $ClientThread = new ThreadDispatcher(function (&$_this, Dispatcher $dispatcher, array $exts, array $packet, string $peername) {
                                $random = mt_rand();
                                $uid = hash("gost", $random);
                                $client[$uid] = new HttpEndpoint($dispatcher, $exts, $packet, $peername);
                                $serveOP = $client[$uid]->serve();
                                if (!empty($serveOP)) {
                                    $_this->response = $serveOP;
                                    $client[$uid] = null;
                                    unset($client[$uid]);
                                }

                                return false;
                            }, [$dispatcher, $exts, $parsedPacket, $peerName, $sentString]);

                            $ClientThread->run() && $ClientThread->join();;

                            if (isset($ClientThread->response)) {
                                fwrite($client, $ClientThread->response);
                                fclose($client);
                            }
                        } elseif ($requestType[0] === "websocket-handshake") {
                            $body = new Body();
                            $body->load($sentString);
                            $http = $requestType[1];
                            $request = (new RequestBuilder())
                                ->buildRequest($http, $body);

                            $socket = new Socket($client);
                            $wsHandleTh = new ThreadDispatcher([Server::class, "newWebsocketHandler"], [$request, $http, $socket]);
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

    public static function newWebsocketHandler(Request $request, Http $http, Socket $socket) {
        var_dump($request, $http, $socket);
    }

    public static function commInit(&$_this) {
        $loop = new Loop;
        $IPCServer = new IServer(
            $loop,
            (new ServerSocket(
                stream_socket_server("tcp://127.0.0.1:47806"))
            )
        );
        $IPCServer->start();
        $loop->run();
    }
}
