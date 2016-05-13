<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 11/5/16
 * Time: 12:36 AM
 */

namespace Leloutama\lib\Ext\ServerExt;

use Leloutama\lib\Core\Router\Router;
use Leloutama\lib\Core\Server\Http;
use Leloutama\lib\Core\Utility\Request;

interface ServerExtension {
    public function __construct(array $configuration, string $docRoot);

    public function beforeConstruct(Router $router, string $stringHeaders);

    public function afterRequestBuild(Request $request, Http $http);

    public function beforeHeaderCreationCall(string $content, string $mime, int $status);

    public function afterHeaderCreation(array $headers, string $content, string $mime, int $status);

    public function beforeFinalServe(array $headAndBody);
}