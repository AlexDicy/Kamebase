<?php
/**
 * Created by HAlexTM on 16/03/2018 17:45
 */


namespace Kamebase\Session\Handlers;


use Kamebase\Database\QueryResponse;
use Kamebase\Exceptions\SessionHandlerException;
use Kamebase\Request;
use mysqli;

class DatabaseSessionHandler extends Handler {
    private $connection;
    private $minutes;
    private $exists = false;
    /**
     * @var string session ID stored in cookies
     */
    private $session;
    /**
     * @var array Data that will be saved in the database
     */
    private $data = [];

    /**
     * DatabaseSessionHandler constructor, accepts a connection @see DB::connection()
     * @param mysqli $connection
     * @param int $minutes how many minutes the cookie for the session should be valid
     */
    public function __construct(mysqli $connection, int $minutes) {
        $this->connection = $connection;
        $this->minutes = $minutes;
    }

    /**
     * Save a value with this key in the session data
     *
     * @param string|array $key
     * @param mixed $value
     */
    public function set($key, $value = true) {
        if (is_array($key)) {
            array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @param mixed $default value used if nothing was found
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool true if this key has a value (!= null)
     */
    public function has($key) {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key the data to be deleted
     */
    public function unset($key) {
        unset($this->data[$key]);
    }

    /**
     * Method is run when the handler is set
     */
    public function setup() {
        $this->session = $this->loadSessionId();
        if ($this->exists) $this->loadData();
        setcookie("KamebaseID", $this->session, time() + $this->minutes * 60);
    }

    /**
     * Method is run when the script shutdowns
     */
    public function shutdown() {
        $this->saveData();
    }

    /**
     * Destroy current session
     */
    public function destroy() {
        setcookie("KamebaseID", "deleted", time() - 3600);
        $this->connection->query("DELETE FROM `sessions` WHERE `id` = '" . $this->connection->escape_string($this->session) . "'");
    }

    public function loadSessionId() {
        if (isset($_COOKIE["KamebaseID"])) {
            $this->exists = true;
            return $_COOKIE["KamebaseID"];
        }
        return self::newSessionId();
    }

    public function newSessionId() {
        $string = "";
        $c = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!#$%&'*+-.^_`|~";
        $max = strlen($c);

        for ($i = 0; $i < 64; $i++) {
            $log = log($max, 2);
            $bytes = (int)($log / 8) + 1;
            $bits = (int)$log + 1;
            $filter = (int)(1 << $bits) - 1;
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter;
            } while ($rnd >= $max);
            $string .= $c[$rnd];
        }

        $this->session = $string;
        return $string;
    }

    public function loadData() {
        $session = $this->connection->escape_string($this->session);
        $result = new QueryResponse($this->connection->query("SELECT `data` FROM `sessions` WHERE `id` = '$session'"));
        if ($result->success()) {
            $this->data = unserialize(base64_decode($result->get()));
            if ($this->keepSession()) {
                $this->minutes = 60 * 24 * 365; // 1 year
            }
        }
    }

    public function saveData() {
        $session = $this->connection->escape_string($this->session);
        $ip = $this->connection->escape_string(Request::getMainRequest()->getIp());
        $lastActivity = $this->connection->escape_string(date("Y-m-d H:i:s"));
        $userAgent = $this->connection->escape_string(Request::getMainRequest()->getUserAgent());
        $data = base64_encode(serialize($this->data));
        $result = new QueryResponse($this->connection->query("INSERT INTO `sessions` (`id`, `ip`, `last_activity`, `user_agent`, `data`)
                  VALUES ('$session', '$ip', '$lastActivity', '$userAgent', '$data')
                  ON DUPLICATE KEY UPDATE `ip` = VALUES(`ip`), `last_activity` = VALUES(`last_activity`), `user_agent` = VALUES(`user_agent`), `data` = VALUES(`data`)"));
        if (!$result->success()) throw new SessionHandlerException("Database query failed");
    }
}