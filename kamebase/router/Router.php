<?php
/**
 * Created by HAlex on 08/10/2017 12:10
 */

namespace kamebase\router;

use kamebase\Request;

class Router {

    /**
     * Supported methods
     * @var array
     */
    public static $methods = ["GET", "POST", "HEAD", "PUT", "PATCH", "DELETE", "OPTIONS"];

    /**
     * Contains all the routes
     * @var array
     */
    public static $routes = [];

    /**
     * Stores routes divided by method and uri, may contain duplicates
     * @var array
     */
    public static $routesByMethod = [];

    /**
     * All the routes with a name will be stored here for a quick access
     * @var array
     */
    public static $routesByName = [];

    public static function addRoute($methods, $path, $settings) {
        $methods = (array) $methods;
        $route = new Route($methods, $path, $settings);
        $url = $route->getHost() . $route->getPath();

        static::$routes[] = $route;

        foreach ($methods as $method) {
            static::$routesByMethod[$method][$url] = $route;
        }

        if (is_array($settings) && isset($settings["name"])) {
            static::$routesByName[$settings["name"]] = $route;
        }
        return $route;
    }

    public static function all($path, $settings = null) {
        return static::addRoute(static::$methods, $path, $settings);
    }

    public static function get($path, $settings = null) {
        return static::addRoute(["GET", "HEAD"], $path, $settings);
    }

    public static function post($path, $settings = null) {
        return static::addRoute("POST", $path, $settings);
    }

    public static function put($path, $settings = null) {
        return static::addRoute("PUT", $path, $settings);
    }

    public static function patch($path, $settings = null) {
        return static::addRoute("PATCH", $path, $settings);
    }

    public static function delete($path, $settings = null) {
        return static::addRoute("DELETE", $path, $settings);
    }

    public static function options($path, $settings = null) {
        return static::addRoute("OPTIONS", $path, $settings);
    }

    public static function match(Request $request) {
        $routes = is_null($request->getMethod()) ? [] : self::getOrDefault(static::$routesByMethod, $request->getMethod(), []);

        $route = null;
        foreach ($routes as $r) {
            if ($r->matches($request)) {
                $route = $r;
                break;
            }
        }

        if (!is_null($route)) {
            $request->setRoute($route);
            return $route->execute($request);
        }

        /*
        // If no route was found we will now check if a matching route is specified by
        // another HTTP verb. If it is we will need to throw a MethodNotAllowed and
        // inform the user agent of which HTTP verb it should use for this route.
        $others = $this->checkForAlternateVerbs($request);

        if (count($others) > 0) {
            return $this->getRouteForMethods($request, $others);
        }

        throw new NotFoundHttpException;
        */
        return null;
    }

    public static function getUrl($routeName, $parameters = null, $default = "/") {
        if (isset(static::$routesByName[$routeName])) {
            $url = static::$routesByName[$routeName]->getPath();
            if (is_array($parameters)) {
                $path = preg_replace_callback("/\\{.*?\\}/", function ($match) use (&$parameters) {
                    return (empty($parameters) && !(substr($match[0], -2) === "?}")) ? $match[0] : array_shift($parameters);
                }, $url);

                $url = trim(preg_replace("/\\{.*?\\?\\}/", "", $path), "/");
            }
            return strtr(rawurlencode($url), Response::$notToEncode);
        }
        return $default;
    }

    public static function redirect($url = "/") {
        return new Response(Request::getMainRequest(), "", 302, ["Location" => $url]);
    }

    public static function toRoute($routeName, $parameters = null) {
        $url = self::getUrl($routeName);
        if (is_array($parameters)) {
            $path = preg_replace_callback("/\\{.*?\\}/", function ($match) use (&$parameters) {
                return (empty($parameters) && !(substr($match[0], -2) === "?}")) ? $match[0] : array_shift($parameters);
            }, $url);

            $url = trim(preg_replace("/\\{.*?\\?\\}/", "", $path), "/");
        }
        return self::redirect($url);
    }

    public static function addRouteByName($name, Route $route) {
        static::$routesByName[$name] = $route;
    }

    public static function getOrDefault(array $array, $key, $default = null) {
        if (array_key_exists($key, $array) && $array[$key] !== null) return $array[$key];
        return $default;
    }


    public static function setup(Route $route) {
        $hostVariables = array();
        $variables = array();
        $hostRegex = null;
        $hostTokens = array();
        if ($host = $route->getHost() !== "") {
            $result = self::setupPattern($route, $host, true);
            $hostVariables = $result["variables"];
            $variables = $hostVariables;
            $hostTokens = $result["tokens"];
            $hostRegex = $result["regex"];
        }
        $path = preg_replace('/\{(\w+?)\?\}/', '{$1}', $route->getPath());
        $result = self::setupPattern($route, $path, false);
        $staticPrefix = $result["staticPrefix"];
        $pathVariables = $result["variables"];
        foreach ($pathVariables as $pathParam) {
            if ("_fragment" === $pathParam) {
                throw new \InvalidArgumentException("Route pattern \"{$route->getPath()}\" cannot contain \"_fragment\" as a path parameter.");
            }
        }
        $variables = array_merge($variables, $pathVariables);
        $tokens = $result["tokens"];
        $regex = $result["regex"];
        return ["staticPrefix" => $staticPrefix,
            "regex" => $regex,
            "tokens" => $tokens,
            "pathVariables" => $pathVariables,
            "hostRegex" => $hostRegex,
            "hostsTokens" => $hostTokens,
            "hostVariables" => $hostVariables,
            "variables" => array_unique($variables)
        ];
    }

    public static function setupPattern(Route $route, $pattern, $isHost) {
        $tokens = array();
        $variables = array();
        $matches = array();
        $pos = 0;
        $defaultSeparator = $isHost ? "." : "/";

        $regexDelimiter = "#";
        $separators = "/,;.:-_~+*=@|";
        $variableMaximumLength = 32;

        preg_match_all("#\{\w+\}#", $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($matches as $match) {
            $varName = substr($match[0][0], 1, -1);
            $precedingText = substr($pattern, $pos, $match[0][1] - $pos);
            $pos = $match[0][1] + strlen($match[0][0]);

            if (!strlen($precedingText)) {
                $precedingChar = "";
            } else {
                preg_match("/.$/u", $precedingText, $precedingChar);
                $precedingChar = $precedingChar[0];
            }
            $isSeparator = "" !== $precedingChar && false !== strpos($separators, $precedingChar);

            // A PCRE subpattern name must start with a non-digit. Also a PHP variable cannot start with a digit so the
            // variable would not be usable as a Controller action argument.
            if (preg_match("/^\d/", $varName))
                throw new \InvalidArgumentException("Variable name \"$varName\" cannot start with a digit in route pattern \"$pattern\". Please use a different name.");
            if (in_array($varName, $variables))
                throw new \InvalidArgumentException("Route pattern \"$pattern\" cannot reference variable name \"$varName\" more than once.");
            if (strlen($varName) > $variableMaximumLength)
                throw new \InvalidArgumentException("Variable name \"$varName\" cannot be longer than $variableMaximumLength characters in route pattern \"$pattern\". Please use a shorter name.");

            if ($isSeparator && $precedingText !== $precedingChar) {
                $tokens[] = array("text", substr($precedingText, 0, -strlen($precedingChar)));
            } elseif (!$isSeparator && strlen($precedingText) > 0) {
                $tokens[] = array("text", $precedingText);
            }

            $regexp = $route->getWhere($varName);
            if (null === $regexp) {
                $followingPattern = (string)substr($pattern, $pos);
                // Find the next static character after the variable that functions as a separator. By default, this separator and "/"
                // are disallowed for the variable. This default requirement makes sure that optional variables can be matched at all
                // and that the generating-matching-combination of URLs unambiguous, i.e. the params used for generating the URL are
                // the same that will be matched. Example: new Route("/{page}.{_format}", array("_format" => "html"))
                // If {page} would also match the separating dot, {_format} would never match as {page} will eagerly consume everything.
                // Also even if {_format} was not optional the requirement prevents that {page} matches something that was originally
                // part of {_format} when generating the URL, e.g. _format = "mobile.html".
                $nextSeparator = self::findNextSeparator($followingPattern, $separators);
                $regexp = sprintf("[^%s%s]+", preg_quote($defaultSeparator, $regexDelimiter), $defaultSeparator !== $nextSeparator && "" !== $nextSeparator ? preg_quote($nextSeparator, $regexDelimiter) : "");
                if (("" !== $nextSeparator && !preg_match("#^\{\w+\}#", $followingPattern)) || "" === $followingPattern) {
                    // When we have a separator, which is disallowed for the variable, we can optimize the regex with a possessive
                    // quantifier. This prevents useless backtracking of PCRE and improves performance by 20% for matching those patterns.
                    // Given the above example, there is no point in backtracking into {page} (that forbids the dot) when a dot must follow
                    // after it. This optimization cannot be applied when the next char is no real separator or when the next variable is
                    // directly adjacent, e.g. "/{x}{y}".
                    $regexp .= "+";
                }
            }

            $tokens[] = array("variable", $isSeparator ? $precedingChar : "", $regexp, $varName);
            $variables[] = $varName;
        }

        if ($pos < strlen($pattern)) {
            $tokens[] = array("text", substr($pattern, $pos));
        }

        // find the first optional token
        $firstOptional = PHP_INT_MAX;
        if (!$isHost) {
            for ($i = count($tokens) - 1; $i >= 0; --$i) {
                $token = $tokens[$i];
                if ($token[0] === "variable" && $route->hasOptional($token[3])) {
                    $firstOptional = $i;
                } else {
                    break;
                }
            }
        }

        // compute the matching regexp
        $regexp = "";
        for ($i = 0, $nbToken = count($tokens); $i < $nbToken; ++$i) {
            $regexp .= self::computeRegexp($tokens, $i, $firstOptional);
        }
        $regexp = "#^" . $regexp . "$#s" . ($isHost ? "i" : "");

        return array(
            "staticPrefix" => self::determineStaticPrefix($route, $tokens),
            "regex" => $regexp,
            "tokens" => array_reverse($tokens),
            "variables" => $variables,
        );
    }

    private static function findNextSeparator($pattern, $separators) {
        if ($pattern == "") {
            // return empty string if pattern is empty or false (false which can be returned by substr)
            return "";
        }
        // first remove all placeholders from the pattern so we can find the next real static character
        if ($pattern = preg_replace('#\{\w+\}#', "", $pattern) === "") {
            return "";
        }
        preg_match("/^./u", $pattern, $pattern);
        return false !== strpos($separators, $pattern[0]) ? $pattern[0] : "";
    }

    /**
     * Computes the regexp used to match a specific token. It can be static text or a subpattern.
     *
     * @param array $tokens The route tokens
     * @param int $index The index of the current token
     * @param int $firstOptional The index of the first optional token
     *
     * @return string The regexp pattern for a single token
     */
    private static function computeRegexp(array $tokens, $index, $firstOptional) {
        $token = $tokens[$index];
        if ($token[0] === "text") {
            // Text tokens
            return preg_quote($token[1], "#");
        } else {
            // Variable tokens
            if (0 === $index && 0 === $firstOptional) {
                // When the only token is an optional variable token, the separator is required
                return preg_quote($token[1], "#") . "(?P<{$token[3]}>{$token[2]})?";
            } else {
                $regexp = preg_quote($token[1], "#") . "(?P<{$token[3]}>{$token[2]})";
                if ($index >= $firstOptional) {
                    // Enclose each optional token in a subpattern to make it optional.
                    // "?:" means it is non-capturing, i.e. the portion of the subject string that
                    // matched the optional subpattern is not passed back.
                    $regexp = "(?:$regexp";
                    $nbTokens = count($tokens);
                    if ($nbTokens - 1 == $index) {
                        // Close the optional subpatterns
                        $regexp .= str_repeat(")?", $nbTokens - $firstOptional - (0 === $firstOptional ? 1 : 0));
                    }
                }
                return $regexp;
            }
        }
    }

    private static function determineStaticPrefix(Route $route, array $tokens) {
        if ($tokens[0][0] !== "text") {
            return ($route->hasOptional($tokens[0][3]) || "/" === $tokens[0][1]) ? "" : $tokens[0][1];
        }
        $prefix = $tokens[0][1];
        if (isset($tokens[1][1]) && $tokens[1][1] !== "/" && $route->hasOptional($tokens[1][3]) === false) {
            $prefix .= $tokens[1][1];
        }
        return $prefix;
    }
}