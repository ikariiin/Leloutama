<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 8/5/16
 * Time: 6:04 PM
 */

namespace Leloutama\lib\Ext;
use Leloutama\lib\Core\Utility\Request;

interface ClientExtension {
    public function __construct(Request $request, array $config);
}