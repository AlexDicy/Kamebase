<?php
/**
 * Created by HAlexTM on 09/03/2018 16:10
 */


namespace Kamebase\Exceptions;


class BadQueryException extends DbException {
    public function __construct($message = "") {
        parent::__construct("There was an error with the query, " . $message);
    }
}