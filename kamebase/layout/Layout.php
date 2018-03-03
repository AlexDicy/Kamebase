<?php
/**
 * Created by HAlex on 17/10/2017 10:02
 */

namespace kamebase\layout;


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
}