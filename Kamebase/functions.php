<?php
/**
 * Created by HAlex on 26/10/2017 17:01
 */

use Kamebase\Layout\Layout;
use Kamebase\Router\Router;

/**
 * Returns a route url using the route's name
 *
 * Url parameters can be passed,
 * $default will be used if there is no route with this name
 */
if (!function_exists("getUrl")) {
    function getUrl($routeName, $parameters = null, $default = "/") {
        return Router::getUrl($routeName, $parameters, $default);
    }
}

/**
 * Dump and die (exit)
 *
 * can be used to test variables content,
 * this will stop the execution
 */
if (!function_exists("dd")) {
    function dd(...$vars) {
        foreach ($vars as $var) {
            var_dump($var);
        }
        exit();
    }

    if (!function_exists("de")) {
        function de(...$vars) {
            dd(...$vars);
        }
    }
}

/**
 * Used in Templates files, Adds a css link to the list
 */
if (!function_exists("requireStyle")) {
    function requireStyle($cssFile) {
        Layout::requireStyle($cssFile);
    }
}

/**
 * Used in Templates files, Outputs css links that were added to the list
 * @see requireStyle()
 */
if (!function_exists("getStyle")) {
    function getStyle() {
        return Layout::getStyle();
    }

    if (!function_exists("getStyles")) {
        function getStyles() {
            return Layout::getStyles();
        }
    }
}

/**
 * Used in Templates files, Adds a js link to the list
 */
if (!function_exists("requireScript")) {
    function requireScript($jsFile) {
        Layout::requireScript($jsFile);
    }
}

/**
 * Used in Templates files, Outputs js links that were added to the list
 * @see requireScript()
 */
if (!function_exists("getScript")) {
    function getScript() {
        return Layout::getScript();
    }

    if (!function_exists("getScripts")) {
        function getScripts() {
            return Layout::getScripts();
        }
    }
}

/**
 * Used in Templates files,
 * indicates that the file extends another file that has "{section content}"
 */
if (!function_exists("extend")) {
    function extend($template) {
        Layout::extend($template);
    }
}

/**
 * Used in Templates files, usually auto-generated
 * indicates that the file ends and it is ready to be included
 */
if (!function_exists("stopExtend")) {
    function stopExtend($template) {
        Layout::stopExtend($template);
    }
}

/**
 * Used in Templates files, requires a section
 *
 * The section "content" will be the file that extended the current file
 * @see extend();
 */
if (!function_exists("section")) {
    function section($name) {
        return Layout::section($name);
    }
}