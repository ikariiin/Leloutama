<?php declare(strict_types = 1);

namespace Leloutama\lib\Core\IPC\Loop;

class Loop
{
    private $readSockets = [];
    private $writeSockets = [];

    /**
     * @var Socket[]
     */
    private $sockets = [];

    private $running = false;

    public function watchForReads(Socket $socket)
    {
        $this->readSockets[$socket->getId()] = $socket->getStream();

        if (!isset($this->sockets[$socket->getId()])) {
            $this->sockets[$socket->getId()] = $socket;
        }
    }

    public function stopWatchingForReads(Socket $socket)
    {
        unset($this->readSockets[$socket->getId()]);

        if (!isset($this->writeSockets[$socket->getId()])) {
            unset($this->sockets[$socket->getId()]);
        }
    }

    public function watchForWrites(Socket $socket)
    {
        $this->writeSockets[$socket->getId()] = $socket->getStream();
        $this->sockets[$socket->getId()] = $socket;
    }

    public function stopWatchingForWrites(Socket $socket)
    {
        unset($this->writeSockets[$socket->getId()]);

        if (!isset($this->readSockets[$socket->getId()])) {
            unset($this->sockets[$socket->getId()]);
        }
    }

    private function tick()
    {
        echo "Loop tick\n";

        $r = $this->readSockets;
        $w = $this->writeSockets;
        $e = null;

        $timeoutSecs = 1;
        $timeoutUSecs = 0;

        $selectResult = stream_select($r, $w, $e, $timeoutSecs, $timeoutUSecs);

        if ($selectResult === false) {
            throw new \Exception("select() returned an error\n");
        }

        if ($selectResult > 0) {
            echo count($r) . " sockets have readable activity\n";
            foreach ($r as $socket) {
                $this->sockets[(int)$socket]->onReadable();
            }

            echo count($w) . " sockets are writable\n";
            foreach ($w as $socket) {
                $this->sockets[(int)$socket]->onWritable();
            }
        }
    }

    public function run()
    {
        try {
            $this->running = true;

            while ($this->running) {
                $this->tick();
            }
        } finally {
            $this->running = false;
        }
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}
