<?php

use Kamebase\Boot;
use Kamebase\Database\DB;
use Kamebase\Layout\Layout;
use Kamebase\Request;

/**
 * Created by HAlex on 08/10/2017 21:01
 */

class Loader {

    public static function handle(Request $request) {
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

$config = Kamebase\Config::getConfig();
if ($config->requireInstallation()) {
    // manually handle installation, routers, database etc.
    $config->install();

} else {
    Loader::loadWithPrefix("kamebase", "Boot", "Request");
    Loader::loadWithPrefix("kamebase/session", "Session");
    Loader::load("kamebase/functions");

    if ($config->hasDbData()) {
        $data = $config->getDbData();
        DB::setConnection($data["host"], $data["user"], $data["password"], $data["database"]);
    }
    $request = new Request(true);
    $request::setMainRequest($request);
    Session::setHandler($config->getSessionHandler());

    Layout::cacheTemplates();
    Loader::handle($request);

    Session::shutdown();
}





function includeFileOnce($className) {
    include_once $className . ".php";
}

function requireFileOnce($className) {
    $className = str_replace("\\", "/", $className);
    require_once $className . ".php";
}