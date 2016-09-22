<?php declare(strict_types = 1);

namespace Leloutama\lib\Core\IPC\Loop;

interface Socket
{
    function onReadable();
    function onWritable();
    function getStream();
    function getId();
}
