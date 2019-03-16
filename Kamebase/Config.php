<?php
/**
 * Created by HAlex on 17/10/2017 19:46
 */

namespace Kamebase;



use Kamebase\Session\Handlers\PhpSessionHandler;

class Config {

    /**
     * @var Config
     */
    public static $config = null;

    /**
     * Installer instance, if not null the website requires an installation
     * @var Object
     */
    protected $installer = null;

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
        "info" => "Info",
        "error" => "Error",
        "username.notValid" => "Username can only contain alphanumeric characters and underscore",
        "username.lengthNotValid" => "Username must be at least 4 characters and maximum 16 characters",
        "password.lengthNotValid" => "Password is too short or too long, at least 8 characters, maximum 600 characters",
        "password.tooShort" => "Password is too short, at least 8 characters",
        "password.noMatch" => "Passwords don't match, you must repeat the same password",
        "email.notValid" => "This is not a valid email",
        "usernameOrEmail.tooShort" => "Username or email must be at least 4 characters",
        "account.loggedIn" => "Logged in",
        "account.registered" => "Your new account is ready, welcome",
        "account.error.cannotCreate" => "An error occurred while trying to create an account, try again with another username or email",
        "account.error.notFound" => "Account not found, email/username or password might be wrong"
    ];

    /**
     * returns true if the website has an installer,
     * in this case the Loader will skip Session, Caches, Router and Database initialization
     * @return bool
     */
    public function requireInstallation() {
        return is_object($this->installer);
    }

    /**
     * The installer must have a "install" method,
     * this should check whether the website is installer or not.
     * this should also load the database info as it will not happen automatically
     *
     * In case it isn't or there was an error this should return false
     * otherwise, if the website is installed and there is no error return true
     */
    public function install() {
        return $this->installer->install();
    }

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

    public function isCloudFlareEnabled() {
        return false;
    }

    public function getSessionHandler() {
        return new PhpSessionHandler();
    }

    public function preBoot() {
        date_default_timezone_set("UTC");
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