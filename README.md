# Kamebase
PHP Framework, Kamebase includes Routes and... well for now just routes

I mean, yes, you could say "Hey dude, you just copied everything from Laravel, Symfony...!" and I would reply "Well, yes, I did"

## Routes
`/routes.php`
```php
<?php
use kamebase\Router;

Router::get("/", "Index"); // First param: Route, Second param: function or text

Router::post("/", "Index POST");
Router::put("/", "Index PUT");
Router::delete("/", "Index DELETE");
Router::patch("/", "Index PATCH");
Router::options("/", "Index OPTIONS");

Router::all("/page", "Page"); // This route will work on every method

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
