<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 27/5/16
 * Time: 10:49 PM
 */

namespace Leloutama\lib\Core\Utility;

use Leloutama\lib\Core\Modules\ServerTwig\ServerTwig;
use Leloutama\lib\Core\Server\Utilities\ServerContentGetter;

require_once __DIR__ . "/ClientExtensionManager.php";
require_once __DIR__ . "/Utilities.php";

class Response {
    private $content;
    private $mime;
    private $headers;
    private $status;
    private $request;
    private $config;
    private $contentFileName;
    private $dynamicContent;

    public $extManager;
    public $twig;

    public function __construct(Request $request) {
        $this->request = $request;
        $this->config = json_decode(file_get_contents(__DIR__ . "/../../../config/config.json"), true);
        $this->extManager = new ClientExtensionManager($request, $this->config);
        $this->twig = new ServerTwig($this->config["Twig"]["DocRoot"]);
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
     * Mime type getter.
     * @return string
     */
    public function getMime(): string {
        return $this->mime;
    }

    /**
     * Status setter.
     * @param int $status
     * @return Response
     */
    public function setStatus(int $status): self {
        $this->status = $status;

        return $this;
    }

    /**
     * Status getter.
     * @return int
     */
    public function getStatus(): int {
        return $this->status;
    }

    /**
     * Method to set the HTTP version and the status of the response.
     * @param string $httpVersionAndStatus
     * @return Response
     */
    public function setHttpAndStatus(string $httpVersionAndStatus): self {
        $this->headers["top"] = $httpVersionAndStatus;

        return $this;
    }

    /**
     * Normal headers setter.
     * @param string $key
     * @param string $value
     * @return Response
     */
    public function setHeader(string $key, string $value): self {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Headers getter as string.
     * @return string
     */
    public function getHeadersAsString(): string {
        $stringHeaders = "";
        if(isset($this->headers["top"])) {
            $stringHeaders .= $this->headers["top"];
        }
        unset($this->headers["top"]);
        foreach ($this->headers as $key => $value) {
            $stringHeaders .= sprintf("\r\n%s: %s", $key, $value);
        }
        return $stringHeaders;
    }

    /**
     * Headers getter as array.
     * @return array
     */
    public function getHeadersAsArray(): array {
        return $this->headers;
    }

    /**
     * Maps the incoming request to the requested file, in the document root!
     * @return Response
     */
    public function map() {
        $request = $this->request;
        $requestedURI = $request->getRequestedResource();

        $requestedURI = Utilities::removeDotPathSegments($requestedURI);
        $docRoot = $this->config["DocRoot"];

        $fileName = $docRoot . $requestedURI;
        if(file_exists($fileName)) {
            $response = (new Response($this->request))
                ->setContent(file_get_contents($fileName))
                ->setMime("text/html")
                ->setStatus(200);
        } else {
            $response = (new Response($this->request))
                ->setContent((new ServerContentGetter())
                    ->get404()
                )
                ->setMime("text/html")
                ->setStatus(404);
        }

        return $response;
    }

    /**
     * Content file name setter.
     * @param string $fileName
     * @return Response
     */
    public function setContentFile(string $fileName): self {
        $this->contentFileName = $fileName;

        return $this;
    }

    /**
     * Checks if the content file name is set.
     * @return bool
     */
    public function issetContentFile(): bool {
        return (isset($this->contentFileName));
    }

    /**
     * Content file name getter.
     * @return string
     */
    public function getContentFileName(): string {
        return $this->contentFileName;
    }

    /**
     * Dynamic Content Setter.
     * @return Response
     */
    public function setDynamicContent(): self {
        $this->dynamicContent = true;

        return $this;
    }

    public function isDynamicContent(): bool {
        return (isset($this->dynamicContent)) ? true : false;
    }

    public function loadFromFile(string $fileName, bool $prependDocRoot = true): self {
        if($prependDocRoot) {
            $this->setContent(file_get_contents($this->config["DocRoot"] . $fileName));
        } else {
            $this->setContent(file_get_contents($fileName));
        }

        return $this;
    }

    public function useTwigTemplate(string $name, array $vars = []): self {
        $this->twig->load($name);

        $this->setContent($this->twig->getRenderedTemplate($vars));

        return $this;
    }
}