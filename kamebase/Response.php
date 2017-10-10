<?php
/**
 * Created by HAlex on 10/10/2017 17:54
 */

namespace kamebase;

class Response {

    public $headers;

    public $statusCode;

    public $statusText;

    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2016-03-01).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static $statusTexts = array(
        100 => "Continue",
        101 => "Switching Protocols",
        102 => "Processing",            // RFC2518
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",          // RFC4918
        208 => "Already Reported",      // RFC5842
        226 => "IM Used",               // RFC3229
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",
        308 => "Permanent Redirect",    // RFC7238
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Payload Too Large",
        414 => "URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I\"m a teapot",                                               // RFC2324
        421 => "Misdirected Request",                                         // RFC7540
        422 => "Unprocessable Entity",                                        // RFC4918
        423 => "Locked",                                                      // RFC4918
        424 => "Failed Dependency",                                           // RFC4918
        425 => "Reserved for WebDAV advanced collections expired proposal",   // RFC2817
        426 => "Upgrade Required",                                            // RFC2817
        428 => "Precondition Required",                                       // RFC6585
        429 => "Too Many Requests",                                           // RFC6585
        431 => "Request Header Fields Too Large",                             // RFC6585
        451 => "Unavailable For Legal Reasons",                               // RFC7725
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates",                                     // RFC2295
        507 => "Insufficient Storage",                                        // RFC4918
        508 => "Loop Detected",                                               // RFC5842
        510 => "Not Extended",                                                // RFC2774
        511 => "Network Authentication Required",                             // RFC6585
    );

    public $protocolVersion = "1.0";

    public $content;

    public $contentType = "text/html";

    public function __construct(Request $request, $content = "", $status = 200, $headers = array()) {
        $this->headers = $headers;
        $this->setContent($content);
        $this->setStatusCode($status);

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!array_key_exists("Date", $this->headers)) {
            $date = \DateTime::createFromFormat("U", time());
            $date->setTimezone(new \DateTimeZone("UTC"));
            $this->headers["Date"] = $date->format("D, d M Y H:i:s") . " GMT";
        }

        $this->load($request);
    }

    public function send() {
        $this->sendHeaders();
        $this->sendContent();

        if (function_exists("fastcgi_finish_request")) {
            fastcgi_finish_request();
        } else if (PHP_SAPI !== "cli") {
            $status = ob_get_status(true);
            $level = count($status);
            // PHP_OUTPUT_HANDLER_* are not defined on HHVM 3.3
            $flags = defined("PHP_OUTPUT_HANDLER_REMOVABLE") ? PHP_OUTPUT_HANDLER_REMOVABLE | PHP_OUTPUT_HANDLER_CLEANABLE : -1;

            while ($level-- > 0 && ($s = $status[$level]) && (!isset($s["del"]) ? !isset($s["flags"]) || $flags === ($s["flags"] & $flags) : $s["del"])) {
                ob_end_flush();
            }
        }
    }

    public function load(Request $request) {
        if ($request->getServer()["SERVER_PROTOCOL"] != "HTTP/1.0") {
            $this->protocolVersion = "1.1";
        }
    }

    public function setContent($content) {
        // Output as JSON
        if ($content instanceof \ArrayObject || $content instanceof \JsonSerializable || is_array($content)) {
            $this->contentType = "application/json";
            $content = json_encode($content);
        }

        if ($content !== null && !is_string($content) && !is_numeric($content) && !is_callable(array($content, "__toString"))) {
            throw new \UnexpectedValueException("The Response content must be a string or object implementing __toString(), \"" . gettype($content) . "\" given.");
        }

        $this->content = (string) $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    public function sendContent() {
        echo $this->getContent();
    }

    public function sendHeaders() {
        if (!headers_sent()) {
            header("HTTP/" . $this->protocolVersion . " " . $this->statusCode . " " . $this->statusText, true, $this->statusCode);
            header("Content-Type: " . $this->contentType . "; charset=UTF-8", true);
        }
    }

    public function setStatusCode($code, $text = null) {
        $this->statusCode = $code = (int)$code;
        if ($this->statusCode < 100 || $this->statusCode >= 600) {
            throw new \InvalidArgumentException("The HTTP status code \"$code\" is not valid.");
        }

        if ($text === null) {
            $this->statusText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : "Unknown status";
            return;
        }

        if ($text === false) {
            $this->statusText = "";
            return;
        }

        $this->statusText = $text;
    }
}