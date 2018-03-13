<?php
/**
 * Created by HAlexTM on 10/03/2018 11:04
 */


namespace Kamebase\Database\Schema;


use Kamebase\Database\Type\Create;

class Schema {
    public static function create(string $table) {
        return new Create($table);
    }
}