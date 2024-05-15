<?php

class Router {

    private static $router;

    private function __construct(private array $routes = []) {}

    /**
     * get/make router instance
     */
    public static function getRouter(): self {
        // if no router instance, make one
        if(!isset(self::$router)) {
            self::$router = new self();
        }

        return self::$router;
    }

    /**
     * register get route
     * 
     * @param string $uri
     * @param string $action
     */
    public function get(string $uri, string $action): void {
        $this->register($uri, $action, "GET");
    }
    
    /**
     * register post route
     * 
     * @param string $uri
     * @param string $action
     */
    public function post(string $uri, string $action): void {
        $this->register($uri, $action, "POST");
    }

    /**
     * route
     * 
     * @param string $method
     * @param string $uri
     */
    public function route(string $method, string $uri): bool {
        try {
            // get registered route details or throw 404
            $routeDetails = $this->routes[$method][$uri];
            if (!$routeDetails) {
                throw new Exception("Route not found");
            }

            $controller = str_replace("controllers\\", "", (strpos($routeDetails['controller'], "api\\") ? str_replace("api\\", "", $routeDetails['controller']) : $routeDetails['controller']));
            $function = $routeDetails['method'];

            include "controllers/PropertyIndexController.php";
            include "controllers/api/PropertyController.php";

            $controllerInstance = new $controller();
            $controllerInstance->$function();

        } catch (Exception $e) {
            abort("Route not found", 404);
            exit;
        }
        
        return true;
    }

    /**
     * register
     * 
     * @param string $uri
     * @param string $action
     * @param string $method
     */
    protected function register(string $uri, string $action, string $method): void {
        if(!isset($this->routes[$method])) $this->routes[$method] = [];

        list($controller, $function) = $this->extractAction($action);

        $this->routes[$method][$uri] = [
            'controller' => $controller,
            'method' => $function
        ];
    }
    
    protected function extractAction(string $action, string $seperator = '@'): array {
        $sepIdx = strpos($action, $seperator);
        
        $controller = substr($action, 0, $sepIdx);
        $function = substr($action, $sepIdx + 1, strlen($action));
        
        return [$controller, $function];
    }
}
?>