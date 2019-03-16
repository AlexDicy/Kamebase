# Kamebase
PHP Framework, Kamebase includes Routes, Templates, Session manager...

## Routes
`/routes.php`
```php
<?php
use kamebase\layout\Layout;
use kamebase\router\Router;

Router::get("/", "Controller@index"); // First param: Route, Second param: function or Controller@method

Router::post("/", "Controller@post");
Router::put("/", "Controller@put");
Router::delete("/", "Controller@delete");
Router::patch("/", "Controller@patch");
Router::options("/", function () {
    return Layout::load("index.page");
});

Router::all("/page", "Page@show"); // This route will work on every method

// You can use variables in the route, for a post slug you can use for example /blog/{post}
//
// You can even use an optional variable, just add a "?" after the name, like /user/{id?}
// If the variable "id" has got a default value it will be used
Router::all("/page/{id}/section/{section?}", function ($id, $sec) {

    // If you "return" an array it will be automatically converted to JSON
    $array = array("id" => $id, "section" => $sec);
    
    // Return arrays (served as JSON), numbers or text (such as HTML)
    return $array;
    
})->where(

    // You can specify a regex for the variables with a where($name, $expression);
    ["id" => "[0-9]+", "section" => "[a-zA-Z-]+"]
    
)->defaults(

    // Specify a default value for optional variables
    "section", "not-set"
);
```

## Config
Create a file named `/Config.php` in the root directory,
the config will allow you to connect to a database.
```php
<?php
class Config extends \Kamebase\Config {
    protected $dbData = [
        "host" => "localhost",
        "user" => "username",
        "password" => "password",
        "database" => "database"
    ];

    public function isCloudFlareEnabled() {
        return true;
    }
}
```

This Project doesn't use Composer, sorry.
