<?php
/**
 * Created by HAlex on 09/10/2017 16:05
 */

namespace kamebase;

class Request {

    public static $mainRequest = null;

    protected $get;
    protected $post;
    protected $attributes;
    protected $cookies;
    protected $files;
    protected $server;
    protected $headers;

    protected $content;
    protected $host;
    protected $path;
    protected $method;
    protected $currentRoute;

    public function __construct($createFromGlobals = true) {
        if ($createFromGlobals) {
            // With the php's bug #66606, the php's built-in web server
            // stores the Content-Type and Content-Length header values in
            // HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH fields.
            $server = $_SERVER;
            if (PHP_SAPI === "cli-server") {
                if (array_key_exists("HTTP_CONTENT_LENGTH", $_SERVER)) {
                    $server["CONTENT_LENGTH"] = $_SERVER["HTTP_CONTENT_LENGTH"];
                }
                if (array_key_exists("HTTP_CONTENT_TYPE", $_SERVER)) {
                    $server["CONTENT_TYPE"] = $_SERVER["HTTP_CONTENT_TYPE"];
                }
            }
            $this->createRequestFromFactory($_GET, $_POST, array(), $_COOKIE, $_FILES, $server);

            if ($this->hasFormData()) {
                parse_str($this->getContent(), $data);
                $this->post = $data;
            }
        }
    }

    protected function hasFormData() {
        return strpos(static::getOrDefault($this->headers, "CONTENT_TYPE"), "application/x-www-form-urlencoded") === 0
            && in_array(strtoupper(static::getOrDefault($this->server, "REQUEST_METHOD", "GET")), array("PUT", "DELETE", "PATCH"));
    }

    private function createRequestFromFactory(array $get = array(), array $post = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        $this->get = $get;
        $this->post = $post;
        $this->attributes = $attributes;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
        $this->headers = $this->parseHeaders($this->server);

        $this->content = $content;
        $this->host = null;
        $this->path = null;
        $this->method = null;
        $this->currentRoute = null;
    }

    public static function getMainRequest() {
        return self::$mainRequest;
    }

    public static function setMainRequest(Request $request) {
        self::$mainRequest = $request;
    }

    public function getPath() {
        if ($this->path === null) {
            $this->path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
            //$this->path = trim($parsedUrl, "/");
        }

        return $this->path == "" ? "/" : $this->path;
    }

    public function getHost() {
        if ($this->host === null) {
            $this->host = $_SERVER['HTTP_HOST'];
        }
        return $this->host;
    }

    public function getMethod() {
        if ($this->method === null) {
            $this->method = strtoupper(static::getOrDefault($this->server, "REQUEST_METHOD", "GET"));

            if ($this->method === "POST") {
                if ($method = static::getOrDefault($this->headers, "X-HTTP-METHOD-OVERRIDE")) {
                    $this->method = strtoupper($method);
                } else {
                    $this->method = strtoupper(static::getOrDefault($this->post, "_method", static::getOrDefault($this->get, "_method", "POST")));
                }
            }
        }

        return $this->method;
    }

    public function setRoute($route) {
        $this->currentRoute = $route;
    }

    public static function getOrDefault(array $array, $key, $default = null) {
        if (array_key_exists($key, $array) && $array[$key] !== null) return $array[$key];
        return $default;
    }

    private function parseHeaders($server) {
        $headers = array();
        $contentHeaders = array("CONTENT_LENGTH" => true, "CONTENT_MD5" => true, "CONTENT_TYPE" => true);
        foreach ($server as $key => $value) {
            if (strpos($key, "HTTP_") === 0) {
                $headers[substr($key, 5)] = $value;
            } else if (isset($contentHeaders[$key])) {
                $headers[$key] = $value;
            }
        }

        if (isset($server["PHP_AUTH_USER"])) {
            $headers["PHP_AUTH_USER"] = $server["PHP_AUTH_USER"];
            $headers["PHP_AUTH_PW"] = isset($server["PHP_AUTH_PW"]) ? $server["PHP_AUTH_PW"] : "";
        } else {
            /*
             * php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
             * For this workaround to work, add these lines to your .htaccess file:
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             *
             * A sample .htaccess file:
             * RewriteEngine On
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             * RewriteCond %{REQUEST_FILENAME} !-f
             * RewriteRule ^(.*)$ app.php [QSA,L]
             */

            $authorizationHeader = null;
            if (isset($server["HTTP_AUTHORIZATION"])) {
                $authorizationHeader = $server["HTTP_AUTHORIZATION"];
            } else if (isset($server["REDIRECT_HTTP_AUTHORIZATION"])) {
                $authorizationHeader = $server["REDIRECT_HTTP_AUTHORIZATION"];
            }

            if ($authorizationHeader !== null) {
                if (stripos($authorizationHeader, "basic ") === 0) {
                    $exploded = explode(":", base64_decode(substr($authorizationHeader, 6)), 2);
                    if (count($exploded) == 2) {
                        list($headers["PHP_AUTH_USER"], $headers["PHP_AUTH_PW"]) = $exploded;
                    }
                } else if (empty($server["PHP_AUTH_DIGEST"]) && (stripos($authorizationHeader, "digest ") === 0)) {
                    $headers["PHP_AUTH_DIGEST"] = $authorizationHeader;
                    $server["PHP_AUTH_DIGEST"] = $authorizationHeader;
                } else if (stripos($authorizationHeader, "bearer ") === 0) {
                    $headers["AUTHORIZATION"] = $authorizationHeader;
                }
            }
        }

        if (isset($headers["AUTHORIZATION"])) {
            return $headers;
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers["PHP_AUTH_USER"])) {
            $headers["AUTHORIZATION"] = "Basic " . base64_encode($headers["PHP_AUTH_USER"] . ":" . $headers["PHP_AUTH_PW"]);
        } else if (isset($headers["PHP_AUTH_DIGEST"])) {
            $headers["AUTHORIZATION"] = $headers["PHP_AUTH_DIGEST"];
        }

        return $headers;
    }

    protected function getContent($asResource = false) {
        $currentContentIsResource = is_resource($this->content);

        if ($asResource === true) {
            if ($currentContentIsResource) {
                rewind($this->content);

                return $this->content;
            }

            // Content passed in parameter (test)
            if (is_string($this->content)) {
                $resource = fopen("php://temp", "r+");
                fwrite($resource, $this->content);
                rewind($resource);

                return $resource;
            }

            $this->content = false;

            return fopen("php://input", "rb");
        }

        if ($currentContentIsResource) {
            rewind($this->content);

            return stream_get_contents($this->content);
        }

        if ($this->content === null || $this->content === false) {
            $this->content = file_get_contents("php://input");
        }

        return $this->content;
    }

    public function getServer() {
        return $this->server;
    }

    public function getPost() {
        return $this->post;
    }

    public function getGet() {
        return $this->get;
    }
}