<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/5/16
 * Time: 10:19 PM
 */

namespace Leloutama\lib\Core\Modules\Http;
class ETag {
    static public function getEtag(string $content) {
        $etag = crc32($content);
        return $etag;
    }
}