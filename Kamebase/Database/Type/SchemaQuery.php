<?php
/**
 * Created by HAlexTM on 11/03/2018 17:39
 */

namespace Kamebase\Database\Type;


use Kamebase\Database\DB;
use Kamebase\Database\QueryResponse;
use Kamebase\Exceptions\BadQueryException;
use Kamebase\Exceptions\NoDbException;

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