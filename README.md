# Kamebase
PHP Framework, Kamebase includes Routes, Templates Engine, Session manager...

## Routes
`/routes.php`
```php
<?php
use kamebase\layout\Layout;
use kamebase\Router;

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
## Templates
`/templates/*`
Files in this folder will be converted to PHP

    .
    ├── ...
    ├── home.html               # The "home" container
    │   ├── header.html         # The "header" that will be included in the main file
    │   └── content.html        # Our "content" file, that will extend "home", atuomatically including other files
    └── ...

`/templates/home.html` `"home"`
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to my new website!</title>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    { css /assets/css/home.css }
    { getStyle() }
</head>
<body>
{ include home.header }
{ section content }
</body>
</html>

```

`/templates/home/container.html` `"home.container"`
```html
{ extends home }
<p>This is my new awesome website!</p>
```

#### Templates Syntax
`{ variable }` refers to `$variable`

`{ method() }` calls a global function like `getStyle()`

`{ css https://url }` require a css that can be added to the page using `getStyle()`

`{ extend template.name }` this will load the requested template and add the file content to the section `content`

`{ section content }` write the section `content` (this should be inside the extended template)

`{ include template.name }` include the requested template (can be used for headers, footers...)

This Project doesn't use Composer, sorry.
