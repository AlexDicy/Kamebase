<?php
/**
 * Created by HAlex on 26/10/2017 17:01
 */

use Kamebase\Layout\Layout;
use Kamebase\Request;
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
if (!function_exists("route")) {
    function route($routeName, $parameters = null, $default = "/") {
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
 * Used in Templates files, writes the extension
 * @see extend();
 */
if (!function_exists("content")) {
    function content() {
        return Layout::content();
    }
}

/**
 * Used in Templates files,
 * stores a variable that can be used with extend() and get()
 */
if (!function_exists("set")) {
    function set($key, $value = true) {
        Layout::set($key, $value);
    }
}

/**
 * Used in Templates files
 * returns a variable stored with set()
 */
if (!function_exists("get")) {
    function get($key, $default = false) {
        return Layout::get($key, $default);
    }
}

/**
 * Returns old user input escaped with htmlspecialchars
 */
if (!function_exists("old")) {
    function old($key, $default = "") {
        $req = Request::getMainRequest();
        return htmlspecialchars($req->getPost()[$key] ?? $req->getGet()[$key] ?? $default);
    }
}

/**
 * Returns an escaped string
 */
if (!function_exists("e")) {
    function e($value) {
        return htmlspecialchars($value, ENT_QUOTES);
    }
}


/**
 * Requires a file and passes data
 */
if (!function_exists("load")) {
    function load($name, $data = []) {
        extract($data, EXTR_SKIP);
        /** @noinspection PhpIncludeInspection */
        require $name;
    }
}


/**
 * Just like load but instead of outputting the content returns it as string
 */
if (!function_exists("getFile")) {
    function getFile($name, $data = []) {
        ob_start();
        load($name, $data);
        return ob_get_clean();
    }
}