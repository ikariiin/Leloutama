<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 12/6/16
 * Time: 10:24 PM
 */

namespace Leloutama\lib\Core\Modules\ServerTwig;

require_once __DIR__ . "/../../../../../vendor/twig/twig/lib/Twig/Autoloader.php";

class ServerTwig {
    private $twig;
    private $template;

    public function __construct(string $docRoot = ""){
        \Twig_Autoloader::register();

        $loader = new \Twig_Loader_Filesystem($docRoot);
        $twig = new \Twig_Environment($loader, [
            "cache" => __DIR__ . "/twig.cache"
        ]);

        $this->twig = $twig;
    }

    public function load(string $template): self {
        $template = $this->twig->loadTemplate($template);

        $this->template = $template;

        return $this;
    }

    public function getRenderedTemplate(array $vars = []) {
        return $this->template->render($vars);
    }
}