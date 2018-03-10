<?php
/**
 * Created by HAlexTM on 10/03/2018 10:14
 */


namespace kamebase\database\type;


class Update extends QueryType {

    public function compile(array $sections) {
        $sql = "UPDATE " . $this->getTable();
        $sql .= " SET " . $this->getSet($sections);

        if (isset($sections["where"])) {
            $sql .= " " . $this->compileWhere($sections["where"]);
        }

        return $sql;
    }
}