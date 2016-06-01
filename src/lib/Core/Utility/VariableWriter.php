<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 29/5/16
 * Time: 2:36 PM
 */

namespace Leloutama\lib\Core\Server\Utilities;
class VariableWriter {
    public function __construct() {
        return $this;
    }

    public function replaceVarsInErrorPage(array $vars, string $content) {
        foreach($vars as $varName => $value) {
            $content = str_replace($varName, $value, $content);
        }
        return $content;
    }
}