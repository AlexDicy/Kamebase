<?php
/**
 * Created by HAlex on 06/10/2017 17:07
 */

use kamebase\Router;

Router::get("/", "Hello");

Router::post("/", "Hello");
Router::put("/", "Hello");
Router::delete("/", "Hello");
Router::patch("/", "Hello");
Router::options("/", "Hello");
Router::all("/{id}", "Hello")->where("id", "[0-9]");