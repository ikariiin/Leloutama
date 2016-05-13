<?php

class Response extends \Leloutama\lib\Core\Utility\AbstractResponse {
    public function onReady($fileName) {
        $this->initializeExtensionManager();

        try {
            $fileCheckr = $this->extensionManager->load("FileCheckr");
            $fileCheckr->load($this->getConfig("docRoot") . $fileName);
            $data = $fileCheckr->getData();

            $this->set($data);
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
        }
        return $this;
    }
}

$router = new \Leloutama\lib\Core\Router\Router();

$indexResponse = new Response();
$indexResponse->setOnReadyMethodArgs("/index.html");

$postResponse = new Response();

$router->bind("/", $indexResponse);

return $router;