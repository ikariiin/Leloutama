<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 2/7/16
 * Time: 8:33 PM
 */

namespace Leloutama\lib\Core\Interfaces;
interface Endpoint {
    /**
     * Every endpoint instance must implement this method, which must
     * return the raw response payload which is to be sent to the client.
     *
     * Any resource allocated by the Endpoint instance should be freed 
     * just before return-ing.
     * @return mixed
     */
    public function serve();
}