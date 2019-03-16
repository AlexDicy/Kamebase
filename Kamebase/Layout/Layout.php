<?php
/**
 * Created by HAlex on 17/10/2017 10:02
 */

namespace Kamebase\Layout;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Layout {
    private static $prefix = "";

    private static $styles = [];
    private static $scripts = [];
    private static $variables = [];
    private static $extended = "";
    private static $content = "";
    private static $currentFile = "";

    public static function load($name, $data = array()) {
        $template = self::getFilename($name);
        if (file_exists($template)) {
            self::$currentFile = $template;
            ob_start();
            extract($data, EXTR_SKIP);
            unset($data, $name);
            /** @noinspection PhpIncludeInspection */
            require $template;

            if (!empty(self::$extended)) {
                self::$content = ob_get_clean();
                if (file_exists(self::getFilename(self::$extended))) {
                    /** @noinspection PhpIncludeInspection */
                    require self::getFilename(self::$extended);
                    return ltrim(ob_get_clean());
                }
                return null;
            }

            return ltrim(ob_get_clean());
        }
        return null;
    }

    public static function requireStyle($cssFile) {
        if (!array_key_exists($cssFile, self::$styles)) {
            self::$styles[] = $cssFile;
        }
    }

    public static function getStyle() {
        $links = "";
        // TODO: escape the string
        foreach (self::$styles as $style) {
            $links .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$style\">\n";
        }
        return $links;
    }

    public static function getStyles() {
        return self::getStyle();
    }

    public static function requireScript($jsFile) {
        if (!array_key_exists($jsFile, self::$scripts)) {
            self::$scripts[] = $jsFile;
        }
    }

    public static function getScript() {
        $links = "";
        // TODO: escape the string
        foreach (self::$scripts as $script) {
            $links .= "<script type=\"text/javascript\" src=\"$script\"></script>\n";
        }
        return $links;
    }

    public static function getScripts() {
        return self::getScript();
    }

    public static function extend($extended) {
        ob_start();
        self::$extended = $extended;
    }

    public static function content() {
        return self::$content;
    }

    public static function setContent(string $content) {
        self::$content = $content;
    }

    public static function getFilename($name) {
        return "templates/" . self::$prefix . $name . ".php";
    }

    /**
     * Can be used to change between multiple themes
     * @param string $prefix
     */
    public static function setPrefix(string $prefix) {
        self::$prefix = $prefix;
    }

    public static function set(string $key, $value = true) {
        self::$variables[$key] = $value;
    }

    public static function get(string $key, $default = false) {
        return self::$variables[$key] ?? $default;
    }
}
