<?php

use kamebase\Boot;
use kamebase\database\DB;
use kamebase\layout\Layout;
use kamebase\Request;

/**
 * Created by HAlex on 08/10/2017 21:01
 */

class Loader {

    public static function handle() {
        $request = new Request(true);
        $response = Boot::matchRoutes($request);
        $response->send();
    }

    /**
     * Include a class
     *
     * "kamebase/Route"
     * @param $className
     * @param $required bool
     */
    public static function load($className, $required = true) {
        if ($required) {
            requireFileOnce($className);
        } else {
            includeFileOnce($className);
        }
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


Loader::load("routes");

$config = kamebase\Config::getConfig();
if ($config->requireInstallation()) {
    // manually handle installation, routers, database etc.
    $config->install();

} else {
    Loader::loadWithPrefix("kamebase", "Boot", "Request");
    Loader::loadWithPrefix("kamebase/session", "Session");

    Session::start();

    Loader::load("kamebase/functions");

    if ($config->hasDbData()) {
        $data = $config->getDbData();
        DB::setConnection($data["host"], $data["user"], $data["password"], $data["database"]);
        Session::reload();
    }

    Layout::cacheTemplates();
    Loader::handle();

    Session::save();
}





function includeFileOnce($className) {
    include_once $className . ".php";
}

function requireFileOnce($className) {
    $className = str_replace("\\", "/", $className);
    require_once $className . ".php";
}