<?php
/**
 * Created by HAlexTM on 08/03/2018 17:33
 */


namespace kamebase\cacher;


use kamebase\layout\Layout;

class Cacher {
    const CACHE_DIR = "kamebase/cacher/cache/";
    private $lastCached = [];

    public function getDir($type) {
        $dir = self::CACHE_DIR . $type . "/";
        if (!is_dir($dir)) mkdir($dir, 755, true);
        return $dir;
    }

    public function getLastCached($type) {
        if (isset($this->lastCached[$type])) {
            return $this->lastCached[$type];
        } else {
            if (file_exists($this->getDir($type) . "lastmod")) {
                $file = fopen($this->getDir($type) . "lastmod", "r");
                $lastMod = fgets($file);
                fclose($file);
                $this->lastCached[$type] = $lastMod;
                return $lastMod;
            }
            return -1;
        }
    }

    public function elapsed($type) {
        return time() - $this->getLastCached($type);
    }

    public function setLastModified($type) {
        file_put_contents(self::CACHE_DIR . "$type.last", time());
    }

    public function cacheTemplates($force = false) {
        Layout::cacheTemplates($force);
    }
}