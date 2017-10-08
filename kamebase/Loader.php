<?php
/**
 * Created by HAlex on 08/10/2017 21:01
 */

class Loader {

    /**
     * Include a class
     *
     * "kamebase/Route"
     * @param $className
     */
    public static function load($className) {
        includeFileOnce($className);
    }
}

spl_autoload_register(function ($className) {
    requireFileOnce($className);
});

function includeFileOnce($className) {
    include_once $className . ".php";
}

function requireFileOnce($className) {
    require_once $className . ".php";
}

Loader::load("kamebase/Boot");
Boot::matchRoutes($request);

/*spl_autoload_register(function ($className) {
    $dir = array (
        "kamebase/",
        "controllers/"
    );

    foreach ($dir as $directory) {
        if (file_exists($directory . $className . ".php")) {
            require_once $directory . $className . ".php";
        }
    }
});*/
