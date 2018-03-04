<?php
/**
 * Created by HAlexTM on 03/03/2018 16:01
 */

namespace kamebase\layout;


class Parser {
    const REGEX_COMMENTS = "/([^\"']|^)((\/\*[\s\S]+?\*\/)|((\/\/|#).+))/";
    const REGEX_INCLUDE = "/{[\s]*include (\S*)[\s]*}/";
    const REGEX_FUNC = "/{[\s]*(\S*)\(\)[\s]*}/";
    const REGEX_VAR = "/({)(.+?)(})/";

    private $name;
    private $data;

    /**
     * @param $name string template name eg. home.partials.header
     */
    public function __construct($name) {
        $this->data = file_get_contents("templates/" . $name);
        $this->name = preg_replace("/[\/\\\]/", ".", substr($name, 0, -strlen(strrchr($name, "."))));
    }

    public function removeComments() {
        $this->data = preg_replace(self::REGEX_COMMENTS, "", $this->data);
    }

    public function replaceData() {
        $this->data = preg_replace(self::REGEX_INCLUDE, "<?php require \"$1.php\" ?>", $this->data);
        $this->data = preg_replace(self::REGEX_FUNC, "<?= $1() ?>", $this->data);
        $this->data = preg_replace(self::REGEX_VAR, "<?= \$$2 ?>", $this->data);
    }

    public function writeLayout($folder) {
        $layout = $folder . "/" . $this->name . ".php";
        file_put_contents($layout, $this->data);
    }
}