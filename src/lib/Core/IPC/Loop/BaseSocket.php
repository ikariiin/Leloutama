<?php declare(strict_types = 1);

namespace Leloutama\lib\Core\IPC\Loop;

abstract class BaseSocket implements Socket
{
    protected $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function getStream()
    {
        return $this->stream;
    }

    function getId()
    {
        return (int)$this->stream;
    }
}
