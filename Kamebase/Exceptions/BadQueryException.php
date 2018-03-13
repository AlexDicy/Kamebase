<?php
/**
 * Created by HAlexTM on 09/03/2018 16:10
 */


namespace Kamebase\Exceptions;


class BadQueryException extends \Exception {
    public function __construct($message = "", $code = 0, \Throwable $previous = null) {
        parent::__construct("There was an error with the query, " . $message, $code, $previous);
    }
}