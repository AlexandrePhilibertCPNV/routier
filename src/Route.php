<?php

namespace Routier\Routier;

/**
 * Store a registered route attributes.
 */
class Route
{

    /**
     * The HTTP method (GET, POST, etc...)
     * @var string
     */
    public $method;

    /**
     * The path that will trigger the callback. Can contain a regex
     * @var string
     */
    public $path;

    /**
     * The callback called when the path is matched
     * @var callable
     */
    public $callback;

    /**
     * Contains the 
     * @var array
     */
    public $parameters;

    /**
     * @param string $method        The HTTP method (GET, POST, etc...)
     * @param string $path          The path that will trigger the callback. 
     *                              Can contain a regex
     * @param callable $callback    The callback called when the path is matched
     */
    public function __construct(string $method, string $path, callable $callback)
    {
        $this->method = $method;
        $this->callback = $callback;
        $this->path = $this->convert_to_regex($path);
        $this->extract_route_parameters($path);
    }

    /**
     * Converts the given path to a regex that will match against incoming 
     * router requests.
     * 
     * @param string $path  The path as it was registered in the router
     * 
     * @return string  The path transformed to a regex string
     */
    private function convert_to_regex(string $path)
    {
        // Replace the route parameters ":name" with regexes to match against
        // incoming requests.
        $regexed_path = preg_replace('/:[a-zA-Z_]*/', '([a-zA-Z0-9]+)*', $path);
        // Escape the regex in order to not match the literal "/" character
        $regexed_path = str_replace("/", "\/", $regexed_path);

        return $regexed_path;
    }

    private function extract_route_parameters(string $path)
    {
        $matches = null;

        // Match the regex on the given path to extract the parameters name.
        // This allows us to retrieve the values later as the path registered on
        // the route was changed to contain regexes where the parameters are.
        preg_match_all('/:([a-zA-Z_]*)/', $path, $matches);

        $this->parameters = $matches[1];
    }
}
