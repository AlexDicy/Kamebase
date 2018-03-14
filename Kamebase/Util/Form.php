<?php
/**
 * Created by HAlexTM on 14/03/2018 17:02
 */

namespace Kamebase\Util;


class Form {
    public static function textarea($name, $value = null, $attributes = []) {
        $defaults = [
            "name" => $name,
            "cols" => 50,
            "rows" => 10
        ];

        $attributes = static::attributes($attributes + $defaults);

        return "<textarea" . $attributes . ">" . htmlentities($value, ENT_QUOTES, "UTF-8") . "</textarea>";
    }

    /**
     * Get html attributes, the string will start with a space if not empty
     * @param array $attributes
     * @return string
     */
    public static function attributes(array $attributes = []) {
        if (count($attributes) > 0) {
            $attr = [];
            foreach ($attributes as $name => $value) {
                if (is_numeric($name)) $name = $value;
                $attr[] = $name . "=" . htmlentities($value);
            }
            return " " . implode(" ", $attr);
        } return "";
    }
}