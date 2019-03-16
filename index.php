<?php
/**
 * Created by HAlex on 06/10/2017 16:22
 */
///////////
$path = realpath(ltrim($_SERVER["REQUEST_URI"], "/"));
if ($path && is_file($path)) {
    if (strtolower(substr($path, -4)) == '.php') {
        // disallowed file
        header("HTTP/1.1 404 Not Found");
        echo "404 Not Found";
    } else {
        // asset file, serve from filesystem
        return false;
    }
}
///////////
header("Access-Control-Allow-Origin: *");
include_once "Kamebase/Loader.php";
