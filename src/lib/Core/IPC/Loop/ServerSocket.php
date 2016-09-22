<?php declare(strict_types = 1);

namespace Leloutama\lib\Core\IPC\Loop;

class ServerSocket extends BaseSocket
{
    /**
     * @var callable
     */
    private $onNewClientCallback;

    public function setNewClientCallback(callable $callback)
    {
        $this->onNewClientCallback = $callback;
    }

    public function onReadable()
    {
        $clientStream = stream_socket_accept($this->stream);
        stream_set_blocking($clientStream, false); // set the stream to non-blocking as soon as it's created

        call_user_func($this->onNewClientCallback, new ClientSocket($clientStream), $this);
    }

    public function onWritable() { }
}
