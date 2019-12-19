<?php
/**
 * Created by HAlex on 17/10/2017 19:43
 */

namespace Kamebase\Database;


use Kamebase\Exceptions\DbException;
use Kamebase\Exceptions\NoDbException;
use mysqli;

class DB {

    /**
     * @var mysqli
     */
    protected static $connection;

    /**
     * @param $host
     * @param $username
     * @param $password
     * @param $database
     * @throws DbException
     */
    public static function setConnection($host, $username, $password, $database) {
        try {
            mysqli_report(MYSQLI_REPORT_STRICT);
            self::$connection = new mysqli($host, $username, $password, $database);
            self::$connection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
            self::$connection->set_charset("utf8mb4");
            self::query("SET time_zone = '+0:00'");
        } catch (\Exception $e) {
            throw new DbException("Fatal error while connecting...");
        }
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
