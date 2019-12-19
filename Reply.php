<?php
/**
 * Created by HAlexTM on 23/12/2018 15:10
 */

class Reply {
    private $success = false;
    private $errors = [];
    private $message = null;
    private $content = null;
    private $statusCode = 200;

    public function __construct($content = null, $success = true, $message = null, $errors = [], int $statusCode = 200) {
        $this->content = $content;
        $this->message = $message;
        $this->success = $success;
        $this->errors = $errors;
        $this->statusCode = $statusCode;
    }

    public function compile() {
        $array = ["success" => $this->success];
        if ($this->message !== null) $array["message"] = $this->message;
        if ($this->content !== null) $array["content"] = $this->content;
        if ($this->errors) $array["errors"] = $this->errors;
        $array["statusCode"] = $this->statusCode;
        return $array;
    }


    public static function success($content = "ok", $message = null, int $statusCode = 200) {
        return static::ok($content, $message, $statusCode);
    }

    public static function ok($content = "ok", $message = null, int $statusCode = 200) {
        return new Reply($content, true, $message, null, $statusCode);
    }

    public static function error(string $error, int $statusCode = 200) {
        return new Reply("error", false, $error, [$error], $statusCode);
    }

    public static function errors(array $errors, int $statusCode = 200) {
        return new Reply("error", false, null, $errors, $statusCode);
    }
}
