<?php
/**
 * Created by HAlex on 17/10/2017 10:02
 */

namespace kamebase\layout;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Layout {

    public static function load($name, $data = array()) {
        try {
            $layoutName = str_replace(".", "/", $name);
            $data["layoutName"] = $layoutName;
            ob_start();
            extract($data, EXTR_SKIP);
            include "layout/base.php";
            return ltrim(ob_get_clean());
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function cacheTemplates() {
        $files = [];
        $folder = "templates";
        $folderLen = strlen($folder) + 1;
        if (!file_exists($folder)) return;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $name = $file->getPathname();
                $files[] = substr($name, $folderLen, strlen($name));
            }
        }

        $outputFolder = "kamebase/layout/cache";
        self::deleteFolder($outputFolder);
        mkdir($outputFolder);

        foreach ($files as $file) {
            $parser = new Parser($file);
            //$parser->removeComments(); it has to be fixed
            $parser->replaceData();
            $parser->writeLayout($outputFolder);
        }
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
}