<?php declare(strict_types = 1);

namespace Leloutama\lib\Core\IPC\Loop;

class ClientSocket extends BaseSocket
{
    private $onDataCallback;
    private $onCloseCallback;
    private $onWriteCallback;
    private $onWriteBufferDrainCallback;

    private $writeBuffer;

    public function setDataCallback(callable $callback)
    {
        $this->onDataCallback = $callback;
    }

    public function setCloseCallback(callable $callback)
    {
        $this->onCloseCallback = $callback;
    }

    public function setWriteBufferDrainCallback(callable $callback)
    {
        $this->onWriteBufferDrainCallback = $callback;
    }

    public function setWriteCallback(callable $callback)
    {
        $this->onWriteCallback = $callback;
    }

    private function sendWriteBuffer(): bool
    {
        $sent = fwrite($this->stream, $this->writeBuffer);
        $this->writeBuffer = (string)substr($this->writeBuffer, $sent);

        call_user_func($this->onWriteCallback, $sent, $this);

        if ($this->writeBuffer === '') {
            call_user_func($this->onWriteBufferDrainCallback, $this);
            return true;
        }

        return false;
    }

    public function write(string $data): bool
    {
        $this->writeBuffer .= $data;
        return $this->sendWriteBuffer();
    }

    public function onReadable()
    {
        $data = fread($this->stream, 1024);

        if ($data === '') {
            call_user_func($this->onCloseCallback, $this);
        } else {
            call_user_func($this->onDataCallback, $data, $this);
        }
    }

    public function onWritable()
    {
        $this->sendWriteBuffer();
    }
}
