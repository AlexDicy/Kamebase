<?php
/**
 * Created by HAlex on 26/10/2017 17:01
 */

use kamebase\layout\Layout;
use kamebase\Router;

if (!function_exists("getUrl")) {
    function getUrl($routeName, $parameters = null, $default = "/") {
        return Router::getUrl($routeName, $parameters, $default);
    }
}

if (!function_exists("requireStyle")) {
    function requireStyle($cssFile) {
        Layout::requireStyle($cssFile);
    }
}

if (!function_exists("getStyle")) {
    function getStyle() {
        return Layout::getStyle();
    }
}

if (!function_exists("extend")) {
    function extend($template) {
        Layout::extend($template);
    }
}

if (!function_exists("stopExtend")) {
    function stopExtend($template) {
        Layout::stopExtend($template);
    }
}

if (!function_exists("section")) {
    function section($name) {
        return Layout::section($name);
    }
}