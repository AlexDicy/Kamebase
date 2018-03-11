<?php
/**
 * Created by HAlexTM on 10/03/2018 11:04
 */


namespace kamebase\database\schema;


use kamebase\database\type\Create;

class Schema {
    public static function create(string $table) {
        return new Create($table);
    }
}