<?php
/**
 * Created by HAlexTM on 09/03/2018 16:07
 */


namespace Kamebase\Database\Type;


use Kamebase\Exceptions\BadQueryException;

abstract class QueryType {
    protected $table;

    public function __construct(string $table) {
        $this->table = $table;
    }

    /**
     * @param array $sections
     * @return string the compiled query
     * @throws BadQueryException
     */
    public function compile(array $sections) {
        throw new BadQueryException("QueryType::compile() is not implemented, data: " . json_encode($sections));
    }

    public function compileWhere(array $data) {
        $or = [];
        foreach ($data as $where) {
            $and = [];
            foreach ($where as $column => $var) {
                $value = is_null($var) ? "IS NULL" : " = " . $this->getVar($var);
                $and[] = $this->getColumn($column) . $value;
            }
            $or[] = implode(" AND ", $and);
        }
        return "WHERE (" . implode(") OR (", $or) . ")";
    }

    public function compileValues(array $sections) {
        $values = $this->getValues($sections["values"]);
        if (isset($sections["columns"])) {
            $columns = $this->getColumns($sections["columns"]);
            return "(" . $columns . ") " . $values;
        }
        return $values;
    }

    public function getValues(array $data) {
        $values = [];
        foreach ($data as $value) {
            $values[] = $this->getVar($value);
        }
        return "VALUES (" . implode(", ", $values) . ")";
    }

    public function getSet(array $sections) {
        $columns = $sections["columns"];
        $values = $sections["values"];
        $set = [];
        for ($i = 0; $i < count($columns); $i++) {
            $set[] = $this->getColumn($columns[$i]) . " = " . $this->getVar($values[$i]);
        }
        return implode(", ", $set);
    }

    public function getLimit(array $sections) {
        $limit = $sections["limit"] ?? -1;
        if ($limit > 0) {
            return " LIMIT " . $limit;
        }
        return "";
    }

    public function getColumns(array $columns) {
        $array = [];
        foreach ($columns as $column) {
            $array[] = $this->getColumn($column);
        }
        return implode(", ", $array);
    }

    public function getColumn($column) {
        if (is_numeric(strpos(".", $column))) {
            return "`" . $column . "`";
        }
        return $this->getTable() . ".`" . $column . "`";
    }

    public function getTable() {
        return "`" . $this->table . "`";
    }

    public function getVar($var = null) {
        if (is_null($var)) return "NULL";

        $replacements = array(
            "\x00"=>'\x00',
            "\n"=>'\n',
            "\r"=>'\r',
            "\\"=>'\\\\',
            "'"=>"\'",
            '"'=>'\"',
            "\x1a"=>'\x1a'
        );
        return "'" . strtr($var, $replacements) . "'";
    }
}