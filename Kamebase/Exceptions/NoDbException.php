<?php
/**
 * Created by HAlexTM on 09/03/2018 17:55
 */


namespace Kamebase\Exceptions;


use Throwable;

class NoDbException extends \Exception {

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct("The database is null, connection error or not set", $code, $previous);
    }
}