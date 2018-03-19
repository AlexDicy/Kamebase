<?php
/**
 * Created by HAlexTM on 13/03/2018 17:56
 */


namespace Kamebase\Entity;


use Kamebase\Database\Query;
use Kamebase\Util\Str;

abstract class Entity implements \JsonSerializable {
    const CREATED_AT = "created_at";
    const UPDATED_AT = "updated_at";
    const LIMIT = 1000;

    protected $table;
    protected $key = "id";
    protected $keyType = "int";
    protected $keyIncrement = true;
    protected $timestamps = false;

    protected $limit = 20;

    public $exists = false;
    public $data = [];

    public function __construct($key = null) {
        if (!isset($this->table)) {
            $this->table = Str::snake(Str::plural(static::class));
        }
        if (is_null($key)) {
            $this->defaults();
        } else {
            $this->get($key);
        }
    }

    /**
     * Setup default values for the new Entity
     * @return void
     */
    public function defaults() {

    }

    public function __get($name) {
        return $this->data[$name];
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * @param $data array|null
     */
    public function loadData($data) {
        if ($data) {
            $this->data = $data;
            $this->exists = true;
        }
    }

    /**
     * Get the entity using the primary key
     * @param $key mixed primary key used to get the entity (id column)
     */
    public function get($key) {
        $result = Query::table($this->table)->select()->where($this->key, $key)->limit()->execute();

        if ($result->success()) {
            $this->loadData($result->values());
            return $this;
        }

        return null;
    }

    public static function where(...$where) {
        $entity = new static();
        $result = Query::table($entity->table)->select()->where($where)->limit(self::LIMIT)->execute();
        $found = [];

        if ($result->success()) {
            while ($values = $result->values()) {
                $e = new static();
                $e->loadData($values);
                $found[] = $e;
            }
        }

        return $found;
    }

    public static function all() {
        $entity = new static();
        $result = Query::table($entity->table)->select()->limit(self::LIMIT)->execute();
        $found = [];

        if ($result->success()) {
            while ($values = $result->values()) {
                $e = new static();
                $e->loadData($values);
                $found[] = $e;
            }
        }

        return $found;
    }

    /**
     * Update the entity in the database if it already exists, otherwise insert it.
     * @return bool if the entity was saved successfully
     */
    public function save() {
        $values = $this->data;
        if ($this->exists) {
            if (isset($values[$this->key])) {
                $key = $values[$this->key];
                // remove primary key from the data
                unset($values[$this->key]);
                $result = Query::table($this->table)
                    ->update(array_keys($values))
                    ->values($values)
                    ->where($this->key, $key)
                    ->execute();
                return $result->success();
            }
            return false;
        } else {
            $result = Query::table($this->table)
                ->insert(array_keys($values))
                ->values($values)
                ->execute();
            return $result->success();
        }
    }

    public function jsonSerialize() {
        return $this->data;
    }
}