<?php
/**
 * Created by HAlexTM on 11/03/2018 17:29
 */

namespace Kamebase\Database\Type;


use Kamebase\Database\Schema\Column;

class Create extends SchemaQuery {
    private $checkExists = true;

    private $engine = "InnoDB";
    private $charset = "utf8mb4";
    private $collation = "utf8mb4_general_ci";
    private $comment;
    private $columns = [];
    private $indexes = [];

    public function compile() {
        $sql = "CREATE TABLE ";

        if ($this->checkExists) {
            // TODO throw an error if the table already exists and/or rename it
        } else {
            $sql .= "IF NOT EXISTS ";
        }

        $sql .= $this->getTable() . " (\n    ";

        $options = [];

        foreach ($this->columns AS $column) {
            $options[] = $column->getDefinition();
        }
        foreach ($this->indexes AS $index) {
            $definition = "";
            if ($index["type"] == "PRIMARY") {
                $definition .= "PRIMARY KEY (" . $this->getColumns($index["columns"]) . ")";
            } else {
                $definition .= $index["type"] . "KEY `" . $index["name"] . "` (" . $this->getColumns($index["columns"]) . ")";
            }
            $options[$index["name"]] = $definition;
        }

        $sql .= implode(",\n    ", $options);
        $sql .= "\n)";
        $sql .= " ENGINE = " . $this->engine . " CHARACTER SET " . $this->charset . " COLLATE " . $this->collation;

        if ($this->comment) {
            $sql .= " COMMENT = " . $this->getVar($this->comment);
        }

        return $sql;
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

    public function column(string $name, $type = "int", $length = null, $default = null) {
        $column = new Column($name, $type, $length, $default);
        $this->columns[] = $column;
        return $column;
    }

    public function index(string $name, $type = null, array $columns = []) {
        $index = [
            "name" => $name,
            "type" => strtoupper($type),
            "columns" => $columns
        ];
        $this->indexes[] = $index;
        return $index;
    }

    public function primary(...$columns) {
        if (is_array($columns[0])) {
            $columns = $columns[0];
        }
        return $this->index("primary", "primary", $columns);
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

    public function checkExists(bool $checkExists = true) {
        $this->checkExists = $checkExists;
        return $this;
    }
}