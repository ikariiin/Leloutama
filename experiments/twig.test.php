<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/6/16
 * Time: 10:27 PM
 */

require_once __DIR__ . "/../vendor/autoload.php";

require_once __DIR__ . "/../src/lib/Core/Modules/Twig/ServerTwig.php";

require_once "/home/lelouch/Leloutama/vendor/twig/twig/lib/Twig/Autoloader.php";

$twig = new \Leloutama\lib\Core\Modules\ServerTwig\ServerTwig("/home/lelouch/Leloutama/cli/../ServerPages/twig/");
$twig->load("template.twig");
echo $twig->getRenderedTemplate(["name" => "Gourab"]);