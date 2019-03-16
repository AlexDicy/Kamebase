<?php
/**
 * Created by HAlexTM on 23/12/2018 16:36
 */

namespace Kamebase\Exceptions;


class MissingParameterException extends ResponseException {

    public function __construct(string $parameter) {
        parent::__construct("Required parameter '$parameter' is missing");
    }
}