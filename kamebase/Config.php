<?php
/**
 * Created by HAlex on 17/10/2017 19:46
 */

namespace kamebase;



class Config {

    /**
     * @var Config
     */
    public static $config = null;

    protected $dbData = [];

    protected $usersColumns = [
        "username" => "username",
        "name" => "name",
        "lastname" => "lastname",
        "password" => "password",
        "email" => "email"
    ];

    protected $tables = [
        "users" => "users"
    ];

    protected $text = [
        "error" => "Error",
        "username.notValid" => "Username can only contain alphanumeric characters and underscore/dot",
        "username.lengthNotValid" => "Username must be at least 4 characters and maximum 40 characters",
        "password.lengthNotValid" => "Password is too short or too long, at least 8 characters, maximum 600 characters",
        "password.tooShort" => "Password is too short, at least 8 characters",
        "password.noMatch" => "Passwords don't match, you must repeat the same password",
        "email.notValid" => "This is not a valid email",
        "usernameOrEmail.tooShort" => "Username or email must be at least 4 characters",
        "account.loggedIn" => "Logged in",
        "account.registered" => "Account registered",
        "account.error.cannotCreate" => "An error occurred while trying to create an account, try again with another username or email",
        "account.error.notFound" => "Account not found, email/username or password might be wrong"
    ];


    public function hasDbData() {
        return !empty($this->dbData);
    }

    /**
     * @return array
     */
    public function getDbData() {
        return $this->dbData;
    }

    /**
     * @return array
     */
    public function getUsersColumns() {
        return $this->usersColumns;
    }

    /**
     * @return array
     */
    public function getTables() {
        return $this->tables;
    }

    public static function loadConfiguration(Config $config) {
        self::$config = $config;
    }

    public static function getConfig() {
        if (is_null(self::$config)) self::$config = new self();
        return self::$config;
    }

    public static function text($key) {
        return self::$config->text[$key];
    }
}