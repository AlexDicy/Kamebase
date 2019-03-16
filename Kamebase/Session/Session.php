<?php
/**
 * Created by HAlex on 17/10/2017 16:46
 */

namespace Kamebase\Session;


use Kamebase\Config;
use Kamebase\Database\DB;
use Kamebase\Exceptions\SessionHandlerException;
use Kamebase\Router\Router;
use Kamebase\Session\Handlers\Handler;

class Session {
    /* @var Handler */
    public static $handler = null;

    public static function set($key, $value = true) {
        if (is_array($value) && empty($value)) {
            self::$handler->unset($key);
        } else {
            self::$handler->set($key, $value);
        }
    }

    public static function get($key, $default = null) {
        return self::$handler->get($key, $default);
    }

    public static function has($key) {
        return self::$handler->has($key);
    }

    public static function unset($key) {
        self::$handler->unset($key);
    }

    public static function push($key, $value) {
        $array = self::get($key, []);
        $array[] = $value;

        self::set($key, $array);
    }

    public static function flash($key, $value = true, $push = false) {
        if ($push) {
            self::push($key, $value);
        } else {
            self::set($key, $value);
        }
        self::push("_flash.new", $key);
    }

    public static function info($value, $push = true) {
        self::flash("_message.info", $value, $push);
    }

    public static function error($value, $push = true) {
        self::flash("_message.errors", $value, $push);
    }

    public static function keepSession() {
        return self::$handler->keepSession();
    }

    public static function setKeepSession(bool $keep) {
        self::$handler->setKeepSession($keep);
    }

    public static function setHandler(Handler $handler) {
        if (is_null(self::$handler)) {
            self::$handler = $handler;
            $handler->setup();
            if (DB::connected()) self::reload();
        } else {
            throw new SessionHandlerException("Handler was already set.");
        }
    }

    public static function shutdown() {
        if (is_object(self::$handler)) {
            $keys = self::get("_flash.old", []);

            foreach ($keys as $key) {
                self::unset($key);
            }

            self::set("_flash.old", self::get("_flash.new", []));
            self::set("_flash.new", []);
            self::$handler->shutdown();
            //if (r)
            // TODO clear old sessions
        }
    }


    public static function setup() {
        if (is_object(self::$handler)) self::$handler->setup();
    }

    public static function destroy() {
        if (is_object(self::$handler)) self::$handler->destroy();
    }


    public static function logged() {
        return self::has("id");
    }

    public static function getLevel() {
        return self::get("user", [])["level"] ?? 0;
    }

    public static function changePassword($password, $repeatPassword, $routeName = "home", $table = "users") {
        $errors = [];
        if ($password !== $repeatPassword) $errors[] = Config::text("password.noMatch");
        if (strlen($password) < 8 || strlen($password) > 600) $errors[] = Config::text("password.lengthNotValid");
        if (!self::logged()) $errors[] = "You need to login to change the password";

        if (empty($errors)) {
            $userId = self::get("id");
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE `$table` SET `password` = '$hash' WHERE `id` = '$userId'";

            if (DB::query($query)) {
                Session::flash("_message.info", "Password cambiata");
                return Router::toRoute($routeName);
            }
            $errors[] = "An error occurred, please try again.";
        }
        return self::errorAndRedirect($errors, $routeName);
    }

    public static function errorAndRedirect($errors, $route) {
        Session::flash("_errors", $errors);
        return Router::toRoute($route);
    }

    public static function reload() {
        if (!self::logged()) return;

        $id = self::get("id");
        $table = Config::getConfig()->getTables()["users"];

        $query = "SELECT * FROM `$table` WHERE id = '$id' LIMIT 1";
        $result = DB::query($query);

        if ($result->num_rows == 1) {
            $data = $result->fetch_assoc();
            if ($data) {
                Session::set("user", $data);
            }
        }
    }
}