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
        $this->path = $path;
        $this->callback = $callback;
        $this->parameters = [];
    }
}