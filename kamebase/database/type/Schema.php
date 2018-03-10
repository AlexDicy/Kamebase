<?php
/**
 * Created by HAlexTM on 10/03/2018 11:04
 */


namespace kamebase\database\type;


use kamebase\database\Query;

class Schema extends QueryType {

    private $query;
    private $sql;

    private $engine = "InnoDB";
    private $charset = "utf8mb4";
    private $collation = "utf8mb4_general_ci";
    private $comment;
    private $columns = [];
    private $indexes = [];

    public function __construct(Query $query) {
        parent::__construct("");
        $this->query = $query;
    }

    public function compile(array $sections) {
        $sql = $this->sql;
        $options = [];

        foreach ($this->columns AS $column) {
            $options[] = $column->get();
        }
        foreach ($this->indexes AS $index) {
            $options[$index->getName()] = $index->get();
        }

        $sql .= implode(",\n    ", $options);
        $sql .= "\n)";
        $sql .= " ENGINE = " . $this->engine . " CHARACTER SET " . $this->charset . " COLLATE " . $this->collation;

        if ($this->comment) {
            $sql .= " COMMENT = " . $this->getVar($this->comment);
        }

        return $sql;
    }

    /**
     * @return \kamebase\database\QueryResponse
     * @throws \kamebase\exceptions\BadQueryException
     * @throws \kamebase\exceptions\NoDbException
     */
    public function execute() {
        return $this->query->execute();
    }

    public function create(string $table, bool $checkExists = true) {
        $this->table = $table;
        $sql = "CREATE TABLE ";

        if ($checkExists) {
            // TODO throw an error if the table already exists and/or rename it
        } else {
            $sql .= "IF NOT EXISTS";
        }

        $sql .= "`$table` (\n    ";
        $this->sql = $sql;
    }

    public function engine(string $engine) {
        $this->engine = $engine;
    }

    public function collation(string $collation) {
        $this->collation = $collation;
    }

    public function comment(string $comment) {
        $this->comment = $comment;
    }

    public function column($name, $type = null, $length = null) {
        $column = [
            "name" => $name,
            "type" => $type,
            "length" => $length
        ];
        $this->columns[] = $column;
        return $column;
    }

    public function index(string $name, $type = null, array $columns = []) {
        $index = [
            "name" => $name,
            "type" => $type,
            "columns" => $columns
        ];
        $this->indexes[] = $index;
        return $index;
    }

    public function primary(string $column) {
        return $this->index("primary", "primary", [$column]);
    }

    public function unique(string $name, array $columns) {
        return $this->index($name, "unique", $columns);
    }

    public function key(string $name, string $column) {
        return $this->index($name, "key", [$column]);
    }

    public function fullTextKey(string $name, string $column) {
        return $this->index($name, "fulltext", [$column]);
    }
}