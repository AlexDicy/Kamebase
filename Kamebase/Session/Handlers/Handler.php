<?php
/**
 * Created by HAlexTM on 16/03/2018 16:40
 */


namespace Kamebase\Session\Handlers;


interface Handler {
    /**
     * Save a value with this key in the session data
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value = true);

    /**
     * @param string $key
     * @param mixed $default value used if nothing was found
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param string $key
     * @return bool true if this key has a value (!= null)
     */
    public function has($key);

    /**
     * @param string $key the data to be deleted
     */
    public function unset($key);

    /**
     * Method is run when the handler is set
     */
    public function setup();

    /**
     * Method is run when the script shutdowns
     */
    public function shutdown();

    /**
     * Destroy current session
     */
    public function destroy();
}