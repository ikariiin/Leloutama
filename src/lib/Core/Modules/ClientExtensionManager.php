<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 8/5/16
 * Time: 5:57 PM
 */

namespace Leloutama\lib\Core\Utility;

class ClientExtensionManager {
    private $config;
    private $request;
    private $exts;

    public function __construct(Request $request, array $config) {
        $this->request = $request;
        $this->config = $config;

        $dirScan = scandir(__DIR__ . "/../../Ext/ClientExt", 1);
        $dirScan = array_diff($dirScan, array('..', '.'));

        $dirCount = count($dirScan);
        for($i = 0; $i < $dirCount; $i++) {
            if(is_dir(__DIR__ . "/../../Ext/ClientExt/" . $dirScan[$i])) {
                $this->exts[] = $dirScan[$i];
            }
        }
    }


    public function load(string $extName) {
        if(!in_array($extName, $this->exts)) {
            throw new \Exception("The Extension which was tried to load was not found.");
        }

        $extConfig = $this->getExtSpecificConfig($extName);

        $this->includeExt($extName);

        $extClassPath = "Leloutama\\lib\\Ext\\ClientExt\\$extName\\$extName";

        $extInstance = new $extClassPath($this->request, $extConfig);

        return $extInstance;
    }

    private function getExtSpecificConfig(string $extName): array {
        if(isset($this->config["Extensions"]["Client"][$extName])) {
            return $this->config["Extensions"]["Client"][$extName];
        } else {
            return [];
        }
    }

    private function includeExt(string $extName) {
        require_once __DIR__ . "/../../Ext/ClientExt/" . $extName . "/$extName.php";
    }
}