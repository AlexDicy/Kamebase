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
    private static $extended = [];
    private static $sections = [];
    private static $currentFile = "";

    public static function load($name, $data = array()) {
        $data["templateName"] = $name;
        ob_start();
        extract($data, EXTR_SKIP);

        if (self::require($name)) {
            return ltrim(ob_get_clean());
        }
        return null;
    }

    // Websites with many files will struggle some second on the first load
    public static function cacheTemplates($force = false) {
        $files = [];
        $folder = "templates";
        $folderLen = strlen($folder) + 1;
        $outputFolder = "kamebase/layout/cache";
        if (!is_dir($folder)) return;
        if (file_exists($outputFolder . "/lastmod")) {
            $file = fopen($outputFolder . "/lastmod", "r");
            $lastMod = fgets($file);
            fclose($file);
            if (!$force && $lastMod == filemtime($folder)) return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $name = $file->getPathname();
                $files[] = substr($name, $folderLen, strlen($name));
            }
        }

        self::deleteFolder($outputFolder);
        mkdir($outputFolder);

        foreach ($files as $file) {
            $parser = new Parser($file);
            //$parser->removeComments(); it has to be fixed
            $parser->replaceData();
            $parser->writeLayout($outputFolder);
        }
        file_put_contents($outputFolder . "/lastmod", filemtime($folder));
    }

    private static function deleteFolder($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        self::deleteFolder($dir . "/" . $object);
                    else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
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

    public static function extend($template) {
        ob_start();
    }

    public static function stopExtend($template) {
        self::$extended[$template] = ob_get_clean();
        self::require($template);
    }

    public static function require($template) {
        $name = self::getFilename($template);
        if (file_exists($name)) {
            self::$currentFile = $template;
            /** @noinspection PhpIncludeInspection */
            return require $name;
        }
        return false;
    }

    public static function section($name) {
        if ($name === "content") {
            return self::$extended[self::$currentFile];
        }
        return self::$sections[$name];
    }

    public static function getFilename($name) {
        return "kamebase/layout/cache/" . self::$prefix . $name . ".php";
    }

    /**
     * Can be used to change between multiple themes
     * @param string $prefix
     */
    public static function setPrefix(string $prefix) {
        self::$prefix = $prefix;
    }
}