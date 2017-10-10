<?php
/**
 * Created by HAlex on 06/10/2017 17:07
 */

use kamebase\Router;

Router::get("/", "Index");

Router::post("/", "Index POST");
Router::put("/", "Index PUT");
Router::delete("/", "Index DELETE");
Router::patch("/", "Index PATCH");
Router::options("/", "Index OPTIONS");



Router::all("/page", "Page");


Router::all("/page/{id}/section/{section?}", function ($id, $sec) {
    return ["id" => $id, "section" => $sec, "message" => "ID della pagina: " . $id . ", sezione: " . $sec];
})->where(
    ["id" => "[0-9]+", "section" => "[a-zA-Z-]+"]
)->defaults(
    "section", "not-set"
);