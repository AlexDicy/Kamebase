<?php
/**
 * Created by HAlexTM on 11/03/2018 16:21
 */

namespace Kamebase\Database\Schema;


class Column {
    /* @var string */
    private $name;
    /* @var string */
    private $type;
    /* @var null|int */
    private $length;
    /* @var null|string */
    private $default;
    /* @var null|array */
    private $values;
    /* @var null|string */
    private $after;
    private $notNull = false;
    private $unsigned = false;
    private $primaryKey = false;
    private $unique = false;
    private $autoIncrement = false;
    /* @var string */
    private $comment;

    private $add = false;
    private $drop = false;
    private $alter = false;
    private $rename = false;
    private $newName;

    public function __construct(string $name, $type = "int", $length = null, $default = null) {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->default = $default;
    }

    public function length(int $length) {
        $this->length = $length;
    }

    public function default(string $default) {
        $this->default = $default;
        return $this;
    }

    public function after(string $after) {
        $this->after = $after;
        return $this;
    }

    public function notNull() {
        $this->notNull = true;
        return $this;
    }

    public function unsigned() {
        $this->unsigned = true;
        return $this;
    }

    public function primaryKey() {
        $this->primaryKey = true;
        return $this;
    }

    public function unique() {
        $this->unique = true;
        return $this;
    }

    public function autoIncrement() {
        $this->autoIncrement = true;
        return $this;
    }

    public function comment(string $comment) {
        $this->comment = $comment;
        return $this;
    }

    public function values(...$values) {
        if (is_array($values[0])) $values = $values[0];
        $this->values = $values;
        return $this;
    }

    public function value($value) {
        $this->values = [$value];
        return $this;
    }

    public function getDefinition() {
        $sql = "";
        if ($this->add) {
            $sql .= "ADD ";
        } else if ($this->alter) {
            $sql .= "MODIFY COLUMN ";
        } else if ($this->rename && $this->newName) {
            $sql .= "CHANGE COLUMN `" . $this->name . "` ";
        } else if ($this->drop) {
            return "DROP `" . $this->name . "`";
        }

        $sql .= "`" . ($this->rename ? $this->newName : $this->name) . "` " . $this->type;


        $isInt = false;
        if ($this->length) {
            switch (strtolower($this->type)) {
                case "tinyint":
                case "smallint":
                case "mediumint":
                case "int":
                case "integer":
                case "bigint":
                    $isInt = true;
            }
        }
        if ($this->length && !$isInt) {
            $sql .= "(" . $this->length . ")";
        } else if ($this->values) {
            $sql .= $this->getValues();
        }

        if ($this->unsigned) {
            $sql .= " UNSIGNED";
        }

        if ($this->primaryKey) {
            $sql .= " PRIMARY KEY";
        }

        if ($this->notNull) {
            $sql .= " NOT NULL";
        }

        if ($this->autoIncrement) {
            $sql .= " AUTO_INCREMENT";
        } else if (!$this->primaryKey) {
            if ($this->default === null && !$this->notNull) {
                $sql .= " DEFAULT NULL";
            } else if ($this->default !== null) {
                $sql .= " DEFAULT " . $this->getVar($this->default);
            }
        }

        if ($this->comment) {
            $sql .= " COMMENT " . $this->getVar($this->comment);
        }

        if ($this->alter && $this->after) {
            $sql .= " AFTER `" . $this->after . "`";
        }
        return $sql;
    }


    public function getValues() {
        $values = [];
        foreach ($this->values as $value) {
            $values[] = $this->getVar($value);
        }
        return "(" . implode(", ", $values) . ")";
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