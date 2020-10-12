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
    private $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    /**
     * @param string $path 
     */
    public static function redirect($path)
    {
        http_response_code(302);
        header("Location: $path");
        exit();
    }

    /**
     * @param string $method        The HTTP method (GET, POST, ...)
     * @param string $path          The path that will trigger the callback. 
     *                              ex: /users/:id/profile/
     * @param callable $callback    The callback that will be called if the 
     *                              path is matched
     * 
     * TODO(alexandre): The route path to regex transformation should be done 
     * inside the route class
     */
    private function register(string $method, string $path, callable $callback)
    {
        // Replace the route parameters ":name" with regexes to match against
        // incoming requests.
        $regexed_path = preg_replace('/:[a-zA-Z_]*/', '([a-zA-Z0-9]+)*', $path);
        // Escape the regex in order to not match the literal "/" character
        $regexed_path = str_replace("/", "\/", $regexed_path);

        // Match the regex on the given path to extract the parameters name.
        // This allows us to retrieve the values later as the path registered on
        // the route was changed to contain regexes where the parameters are.
        preg_match_all('/:([a-zA-Z_]*)/', $path, $matches);

        $route = new Route($method, $regexed_path, $callback);
        $route->parameters = $matches[1];
        $this->routes[] = $route;
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
    public function post(string $path, callable $callback) {
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
            $get_params_offset = stripos($_SERVER['REQUEST_URI'], '?');

            // Remove GET parameters from request uri
            if ($get_params_offset) {
                $request_uri = substr($_SERVER['REQUEST_URI'], 0, $get_params_offset);
            } else {
                $request_uri = $_SERVER['REQUEST_URI'];
            }
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
    private function extract_params($route, $request_uri) : array {
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

            $parameters[$route->parameters[$i-1]] = $value;
        }

        return $parameters;
    }
}
