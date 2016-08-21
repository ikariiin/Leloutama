<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 21/8/16
 * Time: 5:23 PM
 */

namespace Leloutama\lib\Core\Modules\Generic;
class MimeDiscoverer {
    private $extension;
    private $mimeJson;
    
    public function __construct(string $extension) {
        $this->extension = $extension;
        $this->mimeJson = json_decode(file_get_contents(__DIR__ . "/mime.json"), true);
    }

    public function getMimeType() {
        return $this->mimeJson[$this->extension];
    }
}