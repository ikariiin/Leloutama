<?php

class Response extends \Leloutama\lib\Core\Utility\AbstractResponse {
    public function onReady($fileName) {
        $this->initializeExtensionManager();

        try {
            $fileCheckr = $this->extensionManager->load("FileCheckr");
            $fileCheckr->load($this->getConfig("docRoot") . $fileName);
            $data = $fileCheckr->getData();

            if(!empty($this->getRequest()->getPostData())) {
                $data["body"] .= "<center><h2>Your Name: " . $this->getRequest()->getPostData()["parsed"]["name"] . "</h2></center>";
            }

            $this->set($data);
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
        }
    }
}

$router = new \Leloutama\lib\Core\Router\Router();

$router->bind("/", (new Response("/index.html")));
$router->bind("/post", (new Response("/post.html")));

return $router;