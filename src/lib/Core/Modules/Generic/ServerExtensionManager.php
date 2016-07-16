<?php
/**
 * Created by PhpStorm.
 * User: gourab
 * Date: 11/5/16
 * Time: 12:30 PM
 */

namespace Leloutama\lib\Core\Modules\Generic;
class ServerExtensionManager {
    private $config;
    private $exts;

    public function __construct(array $config) {
        $this->config = $config;

        $dirScan = scandir(__DIR__ . "/../../Ext/ServerExt", 1);
        $dirScan = array_diff($dirScan, array('..', '.'));

        $dirCount = count($dirScan);
        for($i = 0; $i < $dirCount; $i++) {
            if(is_dir(__DIR__ . "/../../Ext/ServerExt/" . $dirScan[$i])) {
                $this->exts[] = $dirScan[$i];
            }
        }
    }

    public function load(string $extName) {
        if(!in_array($extName, $this->exts)) {
            throw new \Exception("The Extension was not found");
        }

        $extConfig = $this->getExtSpecificConfig($extName);

        $this->includeExt($extName);

        $extClassPath = "Leloutama\\lib\\Ext\\ServerExt\\$extName\\$extName";

        $extInstance = new $extClassPath($extConfig, $this->config["docRoot"]);

        return $extInstance;
    }

    private function getExtSpecificConfig(string $extName): array {
        if(isset($this->config["Extensions"]["Server"][$extName])) {
            return $this->config["Extensions"]["Server"][$extName];
        } else {
            return [];
        }
    }

    private function includeExt(string $extName) {
        require_once __DIR__ . "/../../Ext/ServerExt/" . $extName . "/ServerExtensionManager.php";
    }
}