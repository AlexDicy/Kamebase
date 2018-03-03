<?php
/**
 * Created by HAlex on 26/10/2017 17:01
 */

use kamebase\Router;

if (!function_exists("getUrl")) {
    function getUrl($routeName, $parameters = null, $default = "/") {
        return Router::getUrl($routeName, $parameters, $default);
    }
}