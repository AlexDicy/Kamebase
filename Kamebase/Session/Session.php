<?php

use Kamebase\Config;
use Kamebase\Database\DB;
use Kamebase\Router\Router;

/**
 * Created by HAlex on 17/10/2017 16:46
 */

class Session {

    public static function set($key, $value = true) {
        if (is_array($key)) {
            array_merge($_SESSION, $key);
        } else {
            $_SESSION[$key] = $value;
        }
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function unset($key) {
        unset($_SESSION[$key]);
    }

    public static function push($key, $value) {
        $array = self::get($key, []);
        $array[] = $value;

        self::set($key, $array);
    }

    public static function flash($key, $value = true) {
        self::set($key, $value);
        self::push("_flash.new", $key);
    }





    public static function logged() {
        return self::has("id");
    }

    public static function getLevel() {
        return self::get("user", [])["level"] ?? 0;
    }

    public static function login($username, $password, $successRoute = "home", $errorRoute = "login", $table = "users") {
        $errors = [];
        if (strlen($username) < 4) $errors[] = Config::text("usernameOrEmail.tooShort");
        if (strlen($password) < 8) $errors[] = Config::text("password.tooShort");
        if (!preg_match("/^[a-z0-9_.@]+\$/i", $username)) $errors[] = Config::text("username.notValid");

        if (empty($errors)) {
            $data = self::tryLogin($username, $password, $table);
            if ($data) {
                Session::set("user", $data);
                Session::set("id", $data["id"]);
                Session::flash("_message.info", Config::text("account.loggedIn"));
                return Router::toRoute($successRoute);
            }
            $errors[] = Config::text("account.error.notFound");
            self::errorAndRedirect($errors, $errorRoute);
        }
        return self::errorAndRedirect($errors, $errorRoute);
    }

    public static function tryLogin($user, $pass, $table) {
        $username = DB::escape($user);

        $query = "SELECT * FROM `$table` WHERE (`username` = '$username' OR `email` = '$username') LIMIT 1";
        $result = DB::query($query);

        if ($result->num_rows == 1) {
            $data = $result->fetch_assoc();
            if (password_verify($pass, $data["password"])) {
                return $data;
            }
        }
        return false;
    }

    public static function register($username, $password, $repeatPassword, $name, $lastname, $email, $successRoute = "home", $errorRoute = "login", $table = "users") {
        $errors = [];
        if (strlen($username) < 4 || strlen($username) > 40) $errors[] = Config::text("username.lengthNotValid");
        if (strlen($password) < 8 || strlen($password) > 600) $errors[] = Config::text("password.lengthNotValid");
        if (!preg_match("/^[a-z0-9_.]+\$/i", $username)) $errors[] = Config::text("username.notValid");
        if (strlen($email) > 255 || !preg_match("(^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$)", $email)) $errors[] = Config::text("email.notValid");
        if ($password !== $repeatPassword) $errors[] = Config::text("password.noMatch");

        if (empty($errors)) {
            $result = self::tryRegister($username, $password, $name, $lastname, $email, $table);
            if ($result && $data = self::tryLogin($username, $password, $table)) {
                Session::set("user", $data);
                Session::set("id", $data["id"]);
                Session::flash("_message.info", Config::text("account.registered"));
                return Router::toRoute($successRoute);
            }
            $errors[] = Config::text("account.error.cannotCreate");
            self::errorAndRedirect($errors, $errorRoute);
        }
        return self::errorAndRedirect($errors, $errorRoute);
    }


    public static function tryRegister($user, $pass, $name, $lastname, $email, $table) {
        $username = DB::escape($user);
        $name = DB::escape($name);
        $lastname = DB::escape($lastname);
        $email = DB::escape($email);
        $password = password_hash($pass, PASSWORD_DEFAULT);

        $columns = Config::getConfig()->getUsersColumns();
        $query = "INSERT INTO `$table` (`{$columns["username"]}`, `{$columns["name"]}`, `{$columns["lastname"]}`, `{$columns["email"]}`, `{$columns["password"]}`)
                  VALUES ('$username', '$name', '$lastname', '$email', '$password')";

        return DB::query($query);
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

    public static function start() {
        session_name("KamebaseID");
        session_start();
    }

    public static function save() {
        $keys = self::get("_flash.old", []);

        foreach ($keys as $key) {
            self::unset($key);
        }

        self::set("_flash.old", self::get("_flash.new", []));
        self::set("_flash.new", []);
    }

    public static function destroy() {
        session_destroy();
    }
}