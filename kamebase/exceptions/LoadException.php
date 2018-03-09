<?php
/**
 * Created by HAlexTM on 09/03/2018 10:47
 */

namespace kamebase\exceptions;


class LoadException extends \Exception {
    public function __construct($message = "", $code = 0, \Throwable $previous = null) {
        parent::__construct("Could not load " . $message, $code, $previous);
    }
}