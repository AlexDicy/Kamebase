<?php
/**
 * Created by HAlex on 06/10/2017 16:27
 */


/**
 * @param $className
 */
spl_autoload_register(function ($className) {
    $dir = array (
        "kamebase/",
        "controllers/"
    );

    foreach ($dir as $directory) {
        if (file_exists($directory . $className . ".php")) {
            require_once $directory . $className . ".php";
        }
    }
});