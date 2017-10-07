<?php
/**
 * Created by HAlex on 06/10/2017 17:08
 */

class Router {

    public static $methods = ["GET", "POST", "HEAD", "PUT", "PATCH", "DELETE", "OPTIONS"];

    public static function setRoute($methods, $route, $action) {
        if (static::isController($action)) {
            $action = static::toController($action);
        }

        $route = new Route($methods, trim($route, "/") ?: "/", $action);
        return $route;
    }

    public static function all($route, $action = null) {
        return static::setRoute(static::$methods, $route, $action);
    }

    public static function get($route, $action = null) {
        return static::setRoute(["GET", "HEAD"], $route, $action);
    }

    public static function post($route, $action = null) {
        return static::setRoute("POST", $route, $action);
    }

    public static function put($route, $action = null) {
        return static::setRoute("PUT", $route, $action);
    }

    public static function patch($route, $action = null) {
        return static::setRoute("PATCH", $route, $action);
    }

    public static function delete($route, $action = null) {
        return static::setRoute("DELETE", $route, $action);
    }

    public static function options($route, $action = null) {
        return static::setRoute("OPTIONS", $route, $action);
    }

    protected static function isController($action) {
        if (!$action instanceof Closure) {
            return is_string($action) || (isset($action["uses"]) && is_string($action["uses"]));
        }
        return false;
    }

    protected static function toController($action) {
        if (is_string($action)) {
            $action = ["uses" => $action];
        }
        return $action;
    }

    public static function compiler(Route $route) {
        $hostVariables = array();
        $variables = array();
        $hostRegex = null;
        $hostTokens = array();

        if ("" !== $host = $route->getHost()) {
            $result = self::compilePattern($route, $host, true);

            $hostVariables = $result["variables"];
            $variables = $hostVariables;

            $hostTokens = $result["tokens"];
            $hostRegex = $result["regex"];
        }

        $path = $route->getPath();

        $result = self::compilePattern($route, $path, false);

        $staticPrefix = $result["staticPrefix"];

        $pathVariables = $result["variables"];

        foreach ($pathVariables as $pathParam) {
            if ("_fragment" === $pathParam) {
                throw new InvalidArgumentException(sprintf("Route pattern \"%s\" cannot contain \"_fragment\" as a path parameter.", $route->getPath()));
            }
        }

        $variables = array_merge($variables, $pathVariables);

        $tokens = $result["tokens"];
        $regex = $result["regex"];

        return new CompiledRoute(
            $staticPrefix,
            $regex,
            $tokens,
            $pathVariables,
            $hostRegex,
            $hostTokens,
            $hostVariables,
            array_unique($variables)
        );
    }

    const REGEX_DELIMITER = '#';
    const SEPARATORS = '/,;.:-_~+*=@|';
    const VARIABLE_MAXIMUM_LENGTH = 32;

    private static function compilePattern(Route $route, $pattern, $isHost) {
        $tokens = array();
        $variables = array();
        $matches = array();
        $pos = 0;
        $defaultSeparator = $isHost ? "." : "/";
        $useUtf8 = preg_match("//u", $pattern);
        $needsUtf8 = $route->getOption("utf8");

        if (!$needsUtf8 && $useUtf8 && preg_match("/[\x80-\xFF]/", $pattern)) {
            $needsUtf8 = true;
            @trigger_error(sprintf("Using UTF-8 route patterns without setting the \"utf8\" option is deprecated since Symfony 3.2 and will throw a LogicException in 4.0. Turn on the \"utf8\" route option for pattern \"%s\".", $pattern), E_USER_DEPRECATED);
        }
        if (!$useUtf8 && $needsUtf8) {
            throw new \LogicException(sprintf("Cannot mix UTF-8 requirements with non-UTF-8 pattern \"%s\".", $pattern));
        }

        // Match all variables enclosed in "{}" and iterate over them. But we only want to match the innermost variable
        // in case of nested "{}", e.g. {foo{bar}}. This in ensured because \w does not match "{" or "}" itself.
        preg_match_all("#\{\w+\}#", $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($matches as $match) {
            $varName = substr($match[0][0], 1, -1);
            // get all static text preceding the current variable
            $precedingText = substr($pattern, $pos, $match[0][1] - $pos);
            $pos = $match[0][1] + strlen($match[0][0]);

            if (!strlen($precedingText)) {
                $precedingChar = "";
            } elseif ($useUtf8) {
                preg_match("/.$/u", $precedingText, $precedingChar);
                $precedingChar = $precedingChar[0];
            } else {
                $precedingChar = substr($precedingText, -1);
            }
            $isSeparator = "" !== $precedingChar && false !== strpos(static::SEPARATORS, $precedingChar);

            // A PCRE subpattern name must start with a non-digit. Also a PHP variable cannot start with a digit so the
            // variable would not be usable as a Controller action argument.
            if (preg_match("/^\d/", $varName)) {
                throw new \DomainException(sprintf("Variable name \"%s\" cannot start with a digit in route pattern \"%s\". Please use a different name.", $varName, $pattern));
            }
            if (in_array($varName, $variables)) {
                throw new \LogicException(sprintf("Route pattern \"%s\" cannot reference variable name \"%s\" more than once.", $pattern, $varName));
            }

            if (strlen($varName) > self::VARIABLE_MAXIMUM_LENGTH) {
                throw new \DomainException(sprintf("Variable name \"%s\" cannot be longer than %s characters in route pattern \"%s\". Please use a shorter name.", $varName, self::VARIABLE_MAXIMUM_LENGTH, $pattern));
            }

            if ($isSeparator && $precedingText !== $precedingChar) {
                $tokens[] = array("text", substr($precedingText, 0, -strlen($precedingChar)));
            } elseif (!$isSeparator && strlen($precedingText) > 0) {
                $tokens[] = array("text", $precedingText);
            }

            $regexp = $route->getRequirement($varName);
            if (null === $regexp) {
                $followingPattern = (string)substr($pattern, $pos);
                // Find the next static character after the variable that functions as a separator. By default, this separator and "/"
                // are disallowed for the variable. This default requirement makes sure that optional variables can be matched at all
                // and that the generating-matching-combination of URLs unambiguous, i.e. the params used for generating the URL are
                // the same that will be matched. Example: new Route("/{page}.{_format}", array("_format" => "html"))
                // If {page} would also match the separating dot, {_format} would never match as {page} will eagerly consume everything.
                // Also even if {_format} was not optional the requirement prevents that {page} matches something that was originally
                // part of {_format} when generating the URL, e.g. _format = "mobile.html".
                $nextSeparator = self::findNextSeparator($followingPattern, $useUtf8);
                $regexp = sprintf(
                    "[^%s%s]+",
                    preg_quote($defaultSeparator, self::REGEX_DELIMITER),
                    $defaultSeparator !== $nextSeparator && "" !== $nextSeparator ? preg_quote($nextSeparator, self::REGEX_DELIMITER) : ""
                );
                if (("" !== $nextSeparator && !preg_match("#^\{\w+\}#", $followingPattern)) || "" === $followingPattern) {
                    // When we have a separator, which is disallowed for the variable, we can optimize the regex with a possessive
                    // quantifier. This prevents useless backtracking of PCRE and improves performance by 20% for matching those patterns.
                    // Given the above example, there is no point in backtracking into {page} (that forbids the dot) when a dot must follow
                    // after it. This optimization cannot be applied when the next char is no real separator or when the next variable is
                    // directly adjacent, e.g. "/{x}{y}".
                    $regexp .= "+";
                }
            } else {
                if (!preg_match("//u", $regexp)) {
                    $useUtf8 = false;
                } elseif (!$needsUtf8 && preg_match("/[\x80-\xFF]|(?<!\\\\)\\\\(?:\\\\\\\\)*+(?-i:X|[pP][\{CLMNPSZ]|x\{[A-Fa-f0-9]{3})/", $regexp)) {
                    $needsUtf8 = true;
                    @trigger_error(sprintf("Using UTF-8 route requirements without setting the \"utf8\" option is deprecated since Symfony 3.2 and will throw a LogicException in 4.0. Turn on the \"utf8\" route option for variable \"%s\" in pattern \"%s\".", $varName, $pattern), E_USER_DEPRECATED);
                }
                if (!$useUtf8 && $needsUtf8) {
                    throw new \LogicException(sprintf("Cannot mix UTF-8 requirement with non-UTF-8 charset for variable \"%s\" in pattern \"%s\".", $varName, $pattern));
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
                if ("variable" === $token[0] && $route->hasDefault($token[3])) {
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
        $regexp = self::REGEX_DELIMITER . "^" . $regexp . "$" . self::REGEX_DELIMITER . "s" . ($isHost ? "i" : "");

        // enable Utf8 matching if really required
        if ($needsUtf8) {
            $regexp .= "u";
            for ($i = 0, $nbToken = count($tokens); $i < $nbToken; ++$i) {
                if ("variable" === $tokens[$i][0]) {
                    $tokens[$i][] = true;
                }
            }
        }

        return array(
            "staticPrefix" => self::determineStaticPrefix($route, $tokens),
            "regex" => $regexp,
            "tokens" => array_reverse($tokens),
            "variables" => $variables,
        );
    }
}