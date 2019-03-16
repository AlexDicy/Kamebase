<?php
/**
 * Created by HAlexTM on 16/03/2018 16:40
 */


namespace Kamebase\Session\Handlers;


abstract class Handler {
    /**
     * Save a value with this key in the session data
     *
     * @param string $key
     * @param mixed $value
     */
    public abstract function set($key, $value = true);

    /**
     * @param string $key
     * @param mixed $default value used if nothing was found
     * @return mixed
     */
    public abstract function get($key, $default = null);

    /**
     * @param string $key
     * @return bool true if this key has a value (!= null)
     */
    public abstract function has($key);

    /**
     * @param string $key the data to be deleted
     */
    public abstract function unset($key);

    /**
     * Method is run when the handler is set
     */
    public abstract function setup();

    /**
     * Method is run when the script shutdowns
     */
    public abstract function shutdown();

    /**
     * Destroy current session
     */
    public abstract function destroy();

    /**
     * @return true if the user requested a long-time session ("remind me" on login)
     */
    public function keepSession() {
        return $this->get("_keepSession", false);
    }

    /**
     * @param bool $keep set to true if you want to keep the session for a long time
     */
    public function setKeepSession(bool $keep) {
        $this->set("_keepSession", $keep);
    }
}