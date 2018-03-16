<?php
/**
 * Created by HAlex on 17/10/2017 19:43
 */

namespace Kamebase\Database;


use Kamebase\Exceptions\NoDbException;
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

    /**
     * @return mysqli
     * @throws NoDbException
     */
    public static function connection() {
        if (is_null(self::$connection)) {
            throw new NoDbException();
        }
        return self::$connection;
    }

    public static function connected() {
        return is_object(self::$connection) && self::$connection->ping();
    }

    /**
     * @param $query
     * @return bool|\mysqli_result
     * @throws NoDbException
     */
    public static function query($query) {
        if (is_null(self::$connection)) {
            throw new NoDbException();
        }
        return self::$connection->query($query);
    }

    public static function escape($string) {
        if (is_null($string)) return "";
        if (is_null(self::$connection)) return false;
        return self::$connection->escape_string($string);
    }
}