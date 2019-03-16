<?php
/**
 * Created by HAlex on 12/10/2018 18:18
 */

namespace Kamebase;


use Kamebase\Session\Session;

class Checker {
    private $failed = false;
    private $errors = [];

    private $countFailed = 0;
    private $countSucceed = 0;

    public function check(bool $condition, $message = false) {
        if ($condition) {
            $this->countSucceed++;
            return true;
        } else {
            $this->failed = true;
            $this->countFailed++;
            if ($message) {
                $this->errors[] = $message;
            }
        }
        return false;
    }

    public function success() {
        return !$this->failed;
    }

    public function failed() {
        return $this->failed;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function flashErrors() {
        Session::error($this->errors, false);
    }
}