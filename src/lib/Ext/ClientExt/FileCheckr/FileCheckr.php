<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 8/5/16
 * Time: 6:12 PM
 */

namespace Leloutama\lib\Ext\ClientExt\FileCheckr;

include_once __DIR__ . "/../ClientExtension.php";

use Leloutama\lib\Core\Utility\Request;
use Leloutama\lib\Ext\ClientExtension;

class FileCheckr implements ClientExtension {
    protected $request;
    protected $config;

    private $content;
    private $status;
    private $mime;
    private $fileName;

    public function __construct(Request $request, array $config){
        $this->request = $request;
        $this->config = $config;
    }

    public function load(string $fileName): bool {
        $this->fileName = $fileName;
        $content = file_get_contents($fileName);
        if($content != false) {
            $this->content = $content;
            $this->status = 200;
            $this->mime = mime_content_type($fileName);
            return true;
        } else {
            $this->content = file_get_contents(__DIR__ . "/Html/FileChecker.html");
            $this->status = 500;
            $this->mime = "text/html";
            return false;
        }
    }

    public function getData(): array {
        return array(
            "body" => $this->content,
            "status" => $this->status,
            "mime" => $this->mime,
            "fileName" => $this->fileName
        );
    }
}