<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 27/5/16
 * Time: 10:49 PM
 */

namespace Leloutama\lib\Core\Utility;

require_once __DIR__ . "/ClientExtensionManager.php";

class Response {
    private $content;
    private $mime;
    public $extManager;

    public function __construct(Request $request) {
        $this->extManager = new ClientExtensionManager($request, json_decode(file_get_contents(__DIR__ . "/../../../config/Core/config.json"), true));
        return $this;
    }

    /**
     * Content setter.
     * @param string $content
     * @return Response $this
     */
    public function setContent(string $content): self {
        // Set the content in this object.
        $this->content = $content;

        // Need to set up the mime type now.
        $finfo = new \finfo(FILEINFO_MIME);

        // Create the buffer. And retrieve the mime type.
        $mimeType = $finfo->buffer($content);

        // Set the mime type in the object.
        $this->mime = $mimeType;

        // Return this object.
        return $this;
    }

    /**
     * MIME Type setter.
     * @param string $mime
     * @return Response
     */
    public function setMime(string $mime): self {
        $this->mime = $mime;

        // Return this object.
        return $this;
    }

    /**
     * Content getter.
     * @return string
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getMime() {
        return $this->mime;
    }
}