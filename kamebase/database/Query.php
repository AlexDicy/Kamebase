<?php
/**
 * Created by HAlexTM on 09/03/2018 16:04
 */


namespace kamebase\database;


use kamebase\database\type\Delete;
use kamebase\database\type\Insert;
use kamebase\database\type\QueryType;
use kamebase\database\type\Schema;
use kamebase\database\type\Select;
use kamebase\database\type\Update;
use kamebase\exceptions\BadQueryException;
use kamebase\exceptions\NoDbException;

class Query {
    /**
     * @var string the table name where we are going to execute the query
     */
    private $table;

    /**
     * @var QueryType
     */
    private $type;

    /*
     * Query sections (data, conditions...)
     */
    private $sections = [];

    public function __construct(string $table, $type = null) {
        $this->table = $table;
        $this->type = $type;
    }

    public function select($columns = null) {
        $this->type = new Select($this->table);
        return $this->columns($columns);
    }

    public function update($columns = null) {
        $this->type = new Update($this->table);
        return $this->columns($columns);
    }

    public function insert($columns = null) {
        $this->type = new Insert($this->table);
        return $this->columns($columns);
    }

    public function delete() {
        $this->type = new Delete($this->table);
        return $this;
    }

    public function schema() {
        $this->type = new Schema($this);
        return $this->type;
    }


    public function columns($columns) {
        if (!is_null($columns)) {
            if (!is_array($columns)) $columns = array_map("trim", explode(",", $columns));
            $this->sections["columns"] = $columns;
        }
        return $this;
    }

    public function values(...$values) {
        if (!is_null($values)) {
            if (!is_array($values)) $values = array_map("trim", explode(",", $values));
            $this->sections["values"] = $values;
        }
        return $this;
    }

    public function where(...$where) {
        if (!is_array($where[0])) {
            if (isset($where[1])) {
                $where = [[$where[0] => $where[1]]];
            }
        }
        $this->sections["where"] = $where;
        return $this;
    }

    /**
     * @throws BadQueryException
     * @throws NoDbException
     */
    public function execute() {
        if (is_null($this->type) || !($this->type instanceof QueryType)) {
            throw new BadQueryException("Query type is not set");
        }
        $sql = $this->type->compile($this->sections);
        return new QueryResponse(DB::query($sql));
    }
}