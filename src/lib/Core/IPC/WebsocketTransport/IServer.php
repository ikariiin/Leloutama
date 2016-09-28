<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 22/9/16
 * Time: 11:01 PM
 */

namespace Leloutama\lib\Core\IPC\WebsocketTransport;


use Leloutama\lib\Core\IPC\Loop\ClientSocket;
use Leloutama\lib\Core\IPC\Loop\Loop;
use Leloutama\lib\Core\IPC\Loop\ServerSocket;

class IServer {
    private $clients;

    private $loop;
    private $socket;

    public function __construct(Loop $loop, ServerSocket $socket) {
        $this->loop = $loop;
        $this->socket = $socket;

        $socket->setNewClientCallback([$this, "newConnection"]);
    }
    
    public function newConnection(ClientSocket $clientSocket) {
        echo "New IPC comm initiated #{$clientSocket->getId()}\n";

        try {
            $this->clients[$clientSocket->getId()] = $clientSocket;

            $clientSocket->setDataCallback([$this, "onData"]);
            $clientSocket->setWriteCallback([$this,  "onWrite"]);
            $clientSocket->setWriteBufferDrainCallback([$this, "onWriteComplete"]);
            $clientSocket->setCloseCallback([$this, "onClose"]);

            $this->loop->watchForReads($clientSocket);
        } catch (\Throwable $ex) {
            echo $ex->getMessage();
        }
    }

    public function onData(string $data, ClientSocket $client) {
        echo "$data\n";
    }

    public function onWrite(int $sent, ClientSocket $client) {
        echo "New Write\n";
    }

    public function onWriteComplete(ClientSocket $client) {
        echo "Write Complete\n";
    }

    public function onCLose(ClientSocket $client) {
        echo "Conn closed {$client->getId()}\n";
    }

    public function start() {
        echo "IPC Comm started.";
        $this->loop->watchForReads($this->socket);
    }
}