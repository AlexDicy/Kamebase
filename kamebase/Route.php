<?php
/**
 * Created by HAlex on 08/10/2017 10:58
 */

namespace kamebase;

class Route {

    /**
     * If this route has a name, it will be stored here
     *
     * ("as" => "name")
     * @var string
     */
    public $name = null;

    /**
     * The path that this route will match
     *
     * "page/user/{id}"
     * @var string
     */
    public $path;

    /**
     * If this route has some variables, they will be stored here with their value
     *
     * ("id" => 7, "name" => "post-name") from "/user/{id}/post/{name}"
     * @var array
     */
    public $variables = [];

    public $parameterNames;

    /**
     * If a variable isn't filled but is required we can use the defaults array to get a value
     *
     * ("id" => 0, "page" => 1)
     * @var array
     */
    public $defaults = [];

    /**
     * Different requirement for a key
     *
     * ("id" => "[0-9]")
     * @var array
     */
    public $expressions = [];

    /**
     * Methods that can be used with this route (GET, POST...)
     * @var array
     */
    public $methods = [];

    /**
     * The settings will contain:
     *  - the action that will be taken if a route is executed, if set
     *  - the name of the route, if set
     *  - the domain used with the route, if set
     *  - default variables
     *  - etc...
     *
     * ("action" => "ControllerName@method")
     * ("action" => function ($firstVariable, $secondVariable...) {})
     *
     * @var array
     */
    public $settings = [];

    /**
     * Stores the compiled pattern
     * @var array
     */
    public $pattern;

    /**
     * Contains the Controller class if we have one
     * @var string|null
     */
    public $controller;

    public function __construct($methods, $path, $settings) {
        $this->setMethods($methods);
        $this->setPath($path);
        $this->setSettings($settings, $path);
    }

    public function setMethods($methods) {
        $methods = (array)$methods;
        if (in_array("GET", $methods) && !in_array("HEAD", $methods)) {
            $methods[] = "HEAD";
        }
        $this->methods[] = $methods;
    }

    public function setSettings($settings, $path) {
        if (is_null($settings)) throw new \UnexpectedValueException("This route [$path] has no settings");

        // if the seconds argument is a function, store it as the action
        if (is_callable($settings) || is_string($settings)) {
            $this->settings["action"] = $settings;
            return;
        }

        if (isset($settings["action"]) && is_string($settings["action"]) && !strpos($settings["action"], "@")) {
            $action = $settings["action"];
            if (method_exists($action, "__invoke")) {
                $settings["action"] .= "@__invoke";
            } else {
                throw new \UnexpectedValueException("Route [$path]: action $action doesn't exist");
            }
        }

        if (is_array($settings)) {
            if (isset($settings["name"])) $this->name = $settings["name"];
            if (isset($settings["defaults"])) $this->defaults = $settings["defaults"];
        }

        $this->settings = $settings;
    }

    private function fetchVariableNames() {
        preg_match_all('/\{(.*?)\}/', $this->getHost() . $this->getPath(), $matches);

        return array_map(function ($m) {
            return trim($m, "?");
        }, $matches[1]);
    }

    /**
     * @return string
     */
    public function getHost() {
        return isset($this->settings["host"]) ? str_replace(["http://", "https://"], "", $this->settings["host"]) : "";
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        // A pattern must start with a slash and must not have multiple slashes at the beginning because the
        // generated path for this route would be confused with a network path, e.g. '//domain.com/path'.
        $this->path = "/" . ltrim(trim($path), "/");
        $this->pattern = null;

        return $this;
    }

    public function setHost($host) {
        $this->settings["host"] = $host;

        return $this;
    }

    public function where($key, $expression = null) {
        if (is_array($key)) $this->expressions = $key;
        else $this->expressions[$key] = $expression;
    }

    public function getWhere($key) {
        return isset($this->expressions[$key]) ? $this->expressions[$key] : null;
    }

    public function matches(Request $request) {
        $this->getPattern();
        $path = $request->getPath(); // /page/4 -- requested path

        if (preg_match($this->pattern["regex"], rawurldecode($path))
            && (is_null($this->pattern["hostRegex"]) || preg_match($this->pattern["hostRegex"], $request->getHost()))) {
            return true;
        }
        return false;
    }

    public function execute(Request $request) {
        $this->getPattern();
        $this->variables = $this->parseVariables($request);
        $callable = $this->settings["action"];

        if (is_callable($callable)) {
            return $callable(...array_values($this->variables));
        }

        if (is_string($callable)) {
            // TODO: run Controller@method
            //$controller->{$method}(...array_values($parameters));
            echo $callable;
        }
        return null;
    }

    public function parseVariables($request) {
        $parameters = $this->bindPathParameters($request);

        // If the route has a regular expression for the host part of the URI, we will
        // compile that and get the parameter matches for this domain. We will then
        // merge them into this parameters array so that this array is completed.
        if (!is_null($this->pattern["hostRegex"])) {
            $parameters = $this->bindHostParameters(
                $request, $parameters
            );
        }

        return $this->replaceDefaults($parameters);
    }

    protected function replaceDefaults(array $parameters) {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $value ?? $this->defaults[$key];
        }

        foreach ($this->defaults as $key => $value) {
            if (!isset($parameters[$key])) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    public function getController() {
        if (!$this->controller) {
            $this->controller = explode("@", $this->settings["action"], 2)[0];
        }
        return $this->controller;
    }

    protected function bindPathParameters(Request $request) {
        $path = rawurldecode($request->getPath());

        preg_match($this->pattern["regex"], $path, $matches);

        return $this->matchToKeys(array_slice($matches, 1));
    }

    protected function bindHostParameters(Request $request, $parameters) {
        preg_match($this->pattern["hostRegex"], $request->getHost(), $matches);
        return array_merge($this->matchToKeys(array_slice($matches, 1)), $parameters);
    }

    protected function matchToKeys(array $matches) {
        if (empty($parameterNames = $this->getParameterNames())) {
            return [];
        }

        $parameters = array_intersect_key($matches, array_flip($parameterNames));

        return array_filter($parameters, function ($value) {
            return is_string($value) && strlen($value) > 0;
        });
    }


    public function getParameterNames() {
        if (isset($this->parameterNames))
            return $this->parameterNames;
        return $this->parameterNames = $this->fetchVariableNames();
    }

    public function getPattern() {
        return isset($this->pattern) ? $this->pattern : $this->pattern = Router::setup($this);
    }

    public function getVariable($name, $default = null) {
        return isset($this->variables[$name]) ? $this->variables[$name] : $default;
    }

    public function hasDefault($name) {
        return isset($this->defaults[$name]);
    }

    /**
     * Access dynamically to stored variables
     *
     * $this->id() == $variables["id"] or null
     * @param $name
     * @return mixed|null
     */
    public function __get($name) {
        return $this->getVariable($name);
    }
}