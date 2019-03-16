<?php

namespace Kamebase;

use Exception;
use Kamebase\Database\DB;
use Kamebase\Session\Session;

/**
 * Created by HAlex on 08/10/2017 21:01
 */

class Loader {
    private static $runOnShutdown = [];

    public static function handle(Request $request) {
        $response = Boot::matchRoutes($request);
        $response->send();

        $maxRun = 3;
        $run = 0;
        while (count(self::$runOnShutdown) > 0 && $run++ < $maxRun) {
            $toRun = self::$runOnShutdown;
            self::$runOnShutdown = [];
            foreach ($toRun as $callable) {
                try {
                    $callable();
                } catch (Exception $ignored) {
                }
            }
        }
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

    public static function runOnShutdown($callable) {
        self::$runOnShutdown[] = $callable;
    }
}

spl_autoload_register(function ($className) {
    requireFileOnce($className);
});


Loader::load("routes");

$config = Config::getConfig();
$config->preBoot();
if ($config->requireInstallation()) {
    // manually handle installation, routers, database etc.
    $config->install();

} else {
    try {
        Loader::loadWithPrefix("Kamebase", "Boot", "Request");
        Loader::loadWithPrefix("Kamebase/Session", "Session");
        Loader::load("Kamebase/functions");

        if ($config->hasDbData()) {
            $data = $config->getDbData();
            DB::setConnection($data["host"], $data["user"], $data["password"], $data["database"]);
        }
        $request = new Request(true);
        $request::setMainRequest($request);
        Session::setHandler($config->getSessionHandler());

        Loader::handle($request);

        Session::shutdown();
    } catch (Exception $e) {
        exit("<h1 style='font-family: sans-serif'>" . htmlentities($e->getMessage()) . "</h1>");
    }
}





function includeFileOnce($className) {
    include_once $className . ".php";
}

function requireFileOnce($className) {
    $className = str_replace("\\", "/", $className);
    require_once $className . ".php";
}
