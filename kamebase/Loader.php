<?php

use kamebase\Boot;
use kamebase\Request;

/**
 * Created by HAlex on 08/10/2017 21:01
 */

class Loader {

    public static function handle() {
        try {
            $request = new Request(true);
            $response = Boot::matchRoutes($request);
            $response->send();
        } catch (Exception $e) {
            echo $e;
        } catch (Throwable $e) {
            echo $e;
        }
    }

    /**
     * Include a class
     *
     * "kamebase/Route"
     * @param $className
     */
    public static function load($className) {
        includeFileOnce($className);
    }

    public static function loadWithPrefix($prefix, ...$classes) {
        foreach ($classes as $class) {
            self::load($prefix . "/" . $class);
        }
    }
}

spl_autoload_register(function ($className) {
    requireFileOnce($className);
});




Loader::loadWithPrefix("kamebase", "Boot", "Request");
Loader::load("routes");
Loader::handle();





function includeFileOnce($className) {
    include_once $className . ".php";
}

function requireFileOnce($className) {
    require_once $className . ".php";
}

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
