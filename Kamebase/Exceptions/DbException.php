<?php
/**
 * Created by HAlexTM on 09/03/2018 17:55
 */


namespace Kamebase\Exceptions;


class DbException extends \Exception {

    public function __construct(string $message) {
        parent::__construct($message);
    }
}