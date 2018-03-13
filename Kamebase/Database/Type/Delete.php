<?php
/**
 * Created by HAlexTM on 10/03/2018 10:12
 */


namespace Kamebase\Database\Type;


class Delete extends QueryType {

    public function compile(array $sections) {
        $sql = "DELETE FROM " . $this->getTable();

        if (isset($sections["where"])) {
            $sql .= " " . $this->compileWhere($sections["where"]);
        }

        return $sql;
    }
}