<?php
/**
 * Created by HAlex on 06/10/2017 18:14
 */

class Route {

    protected $methods = [];
    protected $route;
    protected $action;
    protected $expressions = [];
    protected $variables = [];

    public function __construct($methods, $route, $action) {
        $this->methods = $methods;
        $this->route = $route;
        $this->action = $this->loadAction($route, $action);
    }


    public function execute() {
        $callable = $this->action["uses"];

        if (is_string($callable)) {
            return $this->controllerDispatcher()->dispatch(
                $this, $this->getController(), $this->getControllerMethod()
            );
        }

        return $callable(...array_values($this->variables));
    }

    public function where($key, $exp = null) {
        if (is_array($key)) $this->expressions = $key;
        else $this->expressions[$key] = $exp;
    }

    public function setParameter($name, $value) {
        $this->variables[$name] = $value;
    }

    private function loadAction($route, $action) {
        if (is_null($action)) {
            return ["uses" => function () use ($route) {
                throw new LogicException("$route doesn't have an action.");
            }];
        }

        if (is_callable($action)) {
            return ["uses" => $action];
        } else if (!isset($action["uses"])) {
            $action["uses"] = static::getFirstCallable($action);
        }

        if (is_string($action["uses"]) && mb_strpos($action["uses"], "@") == false) {
            $action["uses"] = static::makeInvokable($action["uses"]);
        }

        return $action;
    }

    private static function getFirstCallable($array) {
        foreach ($array as $key => $value) {
            if (is_callable($value) && is_numeric($key)) {
                return $value;
            }
        }
        return null;
    }

    private static function makeInvokable($action) {
        if (method_exists($action, '__invoke')) {
            return $action . "@__invoke";
        }
        throw new UnexpectedValueException("Action $action is invalid.");
    }

    public function getHost() {
        return isset($this->action["domain"]) ? str_replace(["http://", "https://"], "", $this->action["domain"]) : null;
    }

    public function getPath() {
        return $this->route;
    }
}