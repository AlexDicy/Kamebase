<?php
/**
 * Created by HAlexTM on 11/03/2018 17:39
 */

namespace kamebase\database\type;


use kamebase\database\DB;
use kamebase\database\QueryResponse;
use kamebase\exceptions\BadQueryException;
use kamebase\exceptions\NoDbException;

class SchemaQuery {
    private $table;

    public function __construct(string $table) {
        $this->table = $table;
    }

    /**
     * @return QueryResponse
     * @throws BadQueryException
     * @throws NoDbException
     */
    public function execute() {
        $sql = $this->compile();
        return new QueryResponse(DB::query($sql));
    }

    /**
     * @return string
     * @throws BadQueryException
     */
    public function compile() {
        throw new BadQueryException("SchemaQuery::compile() is not implemented");
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

    public function table(string $table) {
        $this->table = $table;
        return $this;
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