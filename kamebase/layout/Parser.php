<?php
/**
 * Created by HAlexTM on 03/03/2018 16:01
 */

namespace kamebase\layout;


class Parser {
    const REGEX_COMMENTS = "/([^\"']|^)((\/\*[\s\S]+?\*\/)|((\/\/|#).+))/";
    const REGEX_INCLUDE = "/{[\s]*include (\S*)[\s]*}/";
    const REGEX_VAR = "/({)(.+?)(})/";

    private $name;
    private $data;

    /**
     * @param $name string template name eg. home.partials.header
     */
    public function __construct($name) {
        $this->name = $name;
        $this->data = file_get_contents("templates/" . str_replace(".", "/", $this->name));
    }

    public function removeComments() {
        $this->data = preg_replace(self::REGEX_COMMENTS, "", $this->data);
    }

    public function replaceData() {
        $this->data = preg_replace(self::REGEX_INCLUDE, "<?php require $2.php ?>", $this->data);
        $this->data = preg_replace(self::REGEX_VAR, "<?= \$$2 ?>", $this->data);
    }

    public function writeLayout() {
        $layout = "kamebase/layout/cache/" . $this->name . ".php";
    }
}