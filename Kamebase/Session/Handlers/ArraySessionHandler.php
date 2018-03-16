<?php
/**
 * Created by HAlexTM on 16/03/2018 17:43
 */


namespace Kamebase\Session\Handlers;


class ArraySessionHandler implements Handler {
    private $array = [];

    /**
     * Save a value with this key in the session data
     *
     * @param string|array $key
     * @param mixed $value
     */
    public function set($key, $value = true) {
        if (is_array($key)) {
            array_merge($this->array, $key);
        } else {
            $this->array[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @param mixed $default value used if nothing was found
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->array[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool true if this key has a value (!= null)
     */
    public function has($key) {
        return isset($this->array[$key]);
    }

    /**
     * @param string $key the data to be deleted
     */
    public function unset($key) {
        unset($this->array[$key]);
    }

    /**
     * Method is run when the handler is set
     */
    public function setup() {
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
    }
}