<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 27/8/16
 * Time: 11:08 PM
 */

namespace Leloutama\lib\Core\Server;
class Socket extends \Threaded {
    private $stream;
    public function __construct($stream) {
        $this->stream = $stream;
    }

    /**
     * @return resource
     */
    public function getStream() {
        return $this->stream;
    }
}