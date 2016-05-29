<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 27/5/16
 * Time: 10:49 PM
 */

namespace Leloutama\lib\Core\Utility;
class Response {
    private $request;
    private $content;
    private $mime;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Content setter.
     * @param string $content
     * @return Response $this
     */
    public function setContent(string $content): self {
        // Set the content in this object.
        $this->content = $content;

        // Call the mime type setter.
        $this->setMime($content);

        // Return this object.
        return $this;
    }

    /**
     * MIME Type setter.
     * @param string $content
     * @return Response
     */
    public function setMime(string $content): self {
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