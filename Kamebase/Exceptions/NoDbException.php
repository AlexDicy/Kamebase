<?php
/**
 * Created by HAlexTM on 09/03/2018 17:55
 */


namespace Kamebase\Exceptions;


use Throwable;

class NoDbException extends DbException {

    public function __construct() {
        parent::__construct("The database is null, connection error or not set");
    }
}