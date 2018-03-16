<?php
/**
 * Created by HAlexTM on 16/03/2018 17:06
 */


namespace Kamebase\Exceptions;


use Throwable;

class SessionHandlerException extends \Exception {

    public function __construct(string $message, int $code = 0, Throwable $previous = null) {
        parent::__construct("Session Error: " . $message, $code, $previous);
    }
}