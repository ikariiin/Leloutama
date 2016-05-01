<?php
/**
 * @author Gourab Nag
 * File Name: Router.php
 * Time: Sunday, April 24, 14:41
 */

namespace Leloutama\lib\Core\Exceptions;
class Exceptions extends \Exception {
    private $_file;
    public function __construct(string $message, int $code, string $file) {
        parent::__construct($message, $code);
        $this->_file = $file;
    }
}