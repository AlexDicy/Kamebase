<?php
/**
 * Created by HAlexTM on 10/03/2018 10:20
 */


namespace Kamebase\Database;


use Kamebase\Exceptions\NoDbException;

class QueryResponse {

    /**
     * @var \mysqli_result
     */
    private $response;
    private $success = false;
    private $rows = 0;
    private $affectedRows = 0;
    private $fields = [];

    /**
     * QueryResponse constructor.
     * @param $response bool|\mysqli_result
     */
    public function __construct($response) {
        if ($response instanceof \mysqli_result) {
            $this->response = $response;
            $this->success = true;
            $this->rows = $response->num_rows;
            try {
                $this->affectedRows = DB::connection()->affected_rows;
            } catch (NoDbException $e) {
            }
            $this->fields = $response->fetch_fields() ?: [];
        } else if ($response) {
            $this->success = true;
        }
    }

    public function values($associativeOnly = false) {
        if (!$this->response) {
            return null;
        }
        $values = $this->response->fetch_array($associativeOnly ? MYSQLI_ASSOC : MYSQLI_BOTH);

        if (is_array($values)) {
            return $values;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function success() {
        return $this->success;
    }

    /**
     * @return int
     */
    public function getRows() {
        return $this->rows;
    }

    /**
     * @return int
     */
    public function getAffectedRows() {
        return $this->affectedRows;
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    public function get($index = 0) {
        $values = $this->values();
        if (is_array($values)) {
            return $values[$index];
        }
        return null;
    }
}