<?php
/**
 * Created by HAlexTM on 23/12/2018 16:43
 */

namespace Kamebase\Exceptions;


class ResponseException extends \Exception {

    public function __construct(string $message) {
        parent::__construct($message);
    }
}