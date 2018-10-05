<?php
/**
 * Created by HAlexTM on 16/03/2018 16:54
 */


namespace Kamebase\Session\Handlers;


class PhpSessionHandler implements Handler {

    /**
     * Save a value with this key in the session data
     *
     * @param string|array $key
     * @param mixed $value
     */
    public function set($key, $value = true) {
        if (is_array($key)) {
            array_merge($_SESSION, $key);
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @param mixed $default value used if nothing was found
     * @return mixed
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool true if this key has a value (!= null)
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * @param string $key the data to be deleted
     */
    public function unset($key) {
        unset($_SESSION[$key]);
    }

    /**
     * Method is run when the handler is set
     */
    public function setup() {
        session_name("KamebaseID");
        session_start();
    }

    /**
     * Method is run when the script shutdowns
     */
    public function shutdown() {
    }

    /**
     * Destroy current session
     */
    public function destroy() {
        session_destroy();
    }
}