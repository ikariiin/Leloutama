<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/5/16
 * Time: 2:30 PM
 */

namespace Leloutama\lib\Core\Server\Utilities;
class ServerContentGetter {
    public function __construct() {
        return $this;
    }

    public function get404(): string {
        $html404 = file_get_contents(__DIR__ . "/../Resources/Errors.html");

        $vars = array(
            "%error_code%" => "404",
            "%error_code_meaning%" => "Not Found",
            "%error_description%" => "But the requested resource was not found."
        );

        $html404 = (new VariableWriter())
            ->replaceVarsInErrorPage($vars, $html404);

        return $html404;
    }

    public function get405(): string {
        $html405 = file_get_contents(__DIR__ . "/../Resources/Errors.html");

        $vars = array(
            "%error_code%" => "405",
            "%error_code_meaning%" => "Method Not Supported",
            "%error_description%" => "The method requested is not supported by the server."
        );

        $html405 = (new VariableWriter())
            ->replaceVarsInErrorPage($vars, $html405);

        return $html405;
    }

    public function get500(): string {
        $html500 = file_get_contents(__DIR__ . "/../Resources/Errors.html");

        $vars = array(
            "%error_code%" => "500",
            "%error_code_meaning%" => "Internal Server Error",
            "%error_description%" => "Some unpleasant things happened during resolving the request... but... "
        );

        $html500 = (new VariableWriter())
            ->replaceVarsInErrorPage($vars, $html500);

        return $html500;
    }
}