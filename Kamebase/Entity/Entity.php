<?php
/**
 * Created by HAlexTM on 13/03/2018 17:56
 */


namespace Kamebase\Entity;


use Kamebase\Database\Query;
use Kamebase\Util\Str;
use ReflectionClass;

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
    private $data = [];

    public function __construct($key = null) {
        if (!isset($this->table)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $className = (new ReflectionClass($this))->getShortName(); // Actual faster way to get class short name
            $this->table = Str::snake(Str::plural($className));
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

    public function one($entity, $key = "") {
        if (empty($key)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $key = Str::snake(Str::lower((new ReflectionClass($entity))->getShortName())) . "_id";
        }
        return new $entity($this->data[$key]);
    }

    public function many($entity, $key = "") {
        if (empty($key)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $key = Str::snake((new ReflectionClass($this))->getShortName()) . "_id";
        }
        /* @var $entity Entity */
        return $entity::where($key, $this->data[$this->key]);
    }

    // Static

    public static function where(...$where) {
        $entity = new static();
        $result = Query::table($entity->table)->select()->where(...$where)->limit(self::LIMIT)->execute();
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
     * @return static[]
     */
    public static function all() {
        $entity = new static();
        $query = Query::table($entity->table)->select()->limit(self::LIMIT);
        if ($entity->timestamps) {
            $query->desc(self::CREATED_AT);
        }
        $result = $query->execute();
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
            if ($result->success()) {
                $id = Query::table($this->table)
                    ->select($this->key)
                    ->desc($this->key)->limit()
                    ->execute()->get();
                $this->data[$this->key] = $id;
                if ($this->timestamps) {
                    $now = date("Y-m-d H:i:s");
                    $this->data[self::CREATED_AT] = $now;
                    $this->data[self::UPDATED_AT] = $now;
                }
                return true;
            }
            return false;
        }
    }

    public function delete() {
        $values = $this->data;
        if ($this->exists) {
            if (isset($values[$this->key])) {
                $key = $values[$this->key];

                $result = Query::table($this->table)
                    ->delete()
                    ->where($this->key, $key)
                    ->execute();
                if ($result->success()) {
                    $this->exists = false;
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * @return Query
     */
    public static function query() {
        return Query::table((new static())->table);
    }

    // Others

    public function jsonSerialize() {
        return $this->data;
    }
}
