<?php
/**
 * Created by HAlex on 17/10/2017 19:43
 */

namespace kamebase\database;


use mysqli;

class DB {

    /**
     * @var mysqli
     */
    protected static $connection;

    public static function setConnection($host, $username, $password, $database) {
        self::$connection = new mysqli($host, $username, $password, $database);
        self::$connection->set_charset("utf8");
    }

    public static function query($query) {
        if (is_null(self::$connection)) return false;
        return self::$connection->query($query);
    }

    public static function escape($string) {
        if (is_null($string)) return "";
        if (is_null(self::$connection)) return false;
        return self::$connection->escape_string($string);
    }
}