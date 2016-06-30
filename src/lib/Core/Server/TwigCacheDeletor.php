<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 30/6/16
 * Time: 6:20 AM
 */

namespace Leloutama\lib\Core\Server;
class TwigCacheDeletor {
    public static function delete($dir = __DIR__ . "/../Modules/Twig/twig.cache") {
        if(file_exists($dir)) {
            $it = new \RecursiveDirectoryIterator($dir);
            $files = new \RecursiveIteratorIterator($it,
                \RecursiveIteratorIterator::CHILD_FIRST);

            foreach($files as $file) {
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        }
    }
}