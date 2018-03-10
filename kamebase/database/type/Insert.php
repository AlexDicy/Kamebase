<?php
/**
 * Created by HAlexTM on 09/03/2018 18:32
 */


namespace kamebase\database\type;


class Insert extends QueryType {

    public function compile(array $sections) {
        $sql = "INSERT INTO " . $this->getTable();
        $sql .= " " . $this->compileValues($sections);
        return $sql;
    }
}