<?php

namespace Routier\Routier;

/**
 * Route the user request by the routes that were registered.
 * 
 * Regexes can be used for the matching of the request path.
 */
class Router
{

    /**
     * Contains all the routes that where registered on this router.
     * @access private
     * @var array
     */
    private $routes = [];

    /**
     * @access private
     * @var array
     */
    private $routers = [];

    /**
     * @param string $path 
     */
    public static function redirect($path)
    {
        http_response_code(302);
        header("Location: $path");
        exit();
    }

    public static function json($value) {
        header("Content-type: applicaton/json");
        json_encode($value);
        exit();
    }

    /**
     * @param string $method        The HTTP method (GET, POST, ...)
     * @param string $path          The path that will trigger the callback. 
     *                              ex: /users/:id/profile/
     * @param callable $callback    The callback that will be called if the 
     *                              path is matched
     * 
     */
    private function register(string $method, string $path, callable $callback)
    {
        $this->routes[] = new Route($method, $path, $callback);
    }

    /**
     * Register a router under the specified `path`. The router consumes the 
     * registered path and passes the remaining path to the subrouter.
     * 
     * @param string $path
     * @param Router $router
     * 
     */
    public function use(string $path, Router $router)
    {
        $this->routers[$path] = $router;
    }

    /**
     * @param string $path
     * @param callable $callback
     * 
     */
    public function get(string $path, callable $callback)
    {
        $this->register('GET', $path, $callback);
    }

    /**
     * @param string $path
     * @param callable $callback
     * 
     */
    public function post(string $path, callable $callback)
    {
        $this->register('POST', $path, $callback);
    }

    /**
     * Try to match the registered routes against the requested uri and call the
     * callback accordingly.
     * 
     * NOTE: No more that 1 route will be matched (in the order in which they 
     * where registered).
     */
    public function execute($request_uri = null, $request_method = null)
    {
        if (!isset($request_uri)) {
            $request_uri = $this->remove_get_parameters($_SERVER['REQUEST_URI']);
        }

        if (!isset($request_method)) {
            $request_method = $_SERVER['REQUEST_METHOD'];
        }

        foreach ($this->routes as $route) {
            preg_match_all('#^' . $route->path . '$#', $request_uri, $matches);

            foreach ($matches as $match) {
                if ($match && $request_method == $route->method) {
                    // NOTE: The $request_uri has no GET parameters, this means we cannot 
                    // use the ":name" syntax in GET parameters.
                    $params = $this->extract_params($route, $request_uri);

                    call_user_func($route->callback, $params);
                    // We don't want to match on more than one route.
                    break;
                }
            }
        }

        // Execute the sub-routers
        foreach ($this->routers as $registered_path => $router) {
            $router->execute(substr($request_uri, strlen($registered_path)), $request_method);
        }
    }

    private function remove_get_parameters($request_uri): string
    {
        $get_params_offset = stripos($request_uri, '?');

        if ($get_params_offset) {
            return substr($request_uri, 0, $get_params_offset);
        } else {
            return $request_uri;
        }
    }

    /**
     * @param Route $route          The route on which we want to extract 
     *                              parameters
     * @param string $request_uri   The server REQUEST_URI without GET 
     *                              parameters
     * 
     * @return array    The route parameters ex: ["id" => 123]
     * 
     * NOTE: Float values are cast into int
     */
    private function extract_params($route, $request_uri): array
    {
        $parameters = [];
        preg_match_all('#' . $route->path . '#', $request_uri, $matches);

        // Map the route parameters name with the value we retrieved.
        // Skip the first index as it contains the full match.
        $length = count($matches);
        for ($i = 1; $i < $length; $i++) {
            $value = $matches[$i][0];

            if (is_numeric($value)) {
                $value = (int) $value;
            }

            $parameters[$route->parameters[$i - 1]] = $value;
        }

        return $parameters;
    }
}
