<?php
include "classes/Router.php";

// var_dump($_SERVER);
// $parsedUrl = parse_url($url);
// $fragment = isset($parsedUrl['fragment']) ? $parsedUrl['fragment'] : '';

// cors and headers
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    // cache for one day
    header('Access-Control-Max-Age: 86400');
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

// inital entry point of the application, 
// without any extra processing we are just initalising our routes here
$router = Router::getRouter();
$route = (isset($_GET['route']) ? str_replace("?route=", "", $_GET['route']) : "/"); 

// define routes
$router->get("/", "controllers\PropertyIndexController@propertyIndex");
// // define api routes 
$router->get("/api/property-list", "controllers\api\PropertyController@getPropertyList");
$router->get("/api/amend-coordinates", "controllers\api\PropertyController@getAmendCoorindates");

$router->route($_SERVER['REQUEST_METHOD'], $route);

?>