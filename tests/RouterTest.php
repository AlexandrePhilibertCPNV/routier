<?php

use PHPUnit\Framework\TestCase;
use Routier\Routier\Router;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertTrue;

class RouterTest extends TestCase
{


    public function testRouteCallbackCalled()
    {

        $was_called = false;
        $router = new Router();

        $router->get('/users', function () use (&$was_called) {
            $was_called = true;
        });

        $router->execute('/users', 'GET');

        assertTrue($was_called, 'Callback should be called');
    }

    public function testRouteCallbackNotCalled()
    {
        $was_called = false;
        $router = new Router();

        $router->get('/someroute', function () use (&$was_called) {
            $was_called = true;
        });

        $router->execute('/someotherroute', 'GET');

        assertFalse($was_called, 'Callback should not be called');
    }

    public function testRouteParameters()
    {
        $route_params = [];
        $router = new Router();

        $router->get('/products/:product_id', function ($params) use (&$route_params) {
            $route_params = $params;
        });

        $router->execute('/products/12', 'GET');

        assertCount(1, $route_params, 'Route parameters should contain 1 element');
        assertIsArray($route_params, 'Route parameters should be an array');
        assertArrayHasKey('product_id', $route_params, '`product_id` key should exist');
        assertEquals(12, $route_params['product_id'], '`product_id` should equal 12');
    }

    public function testGetPostSameURI()
    {
        $was_get_called = false;
        $was_post_called = false;
        $router = new Router();

        $router->get('/products/:id', function () use (&$was_get_called) {
            $was_get_called = true;
        });

        $router->post('/products/:id', function () use (&$was_post_called) {
            $was_post_called = true;
        });

        $router->execute('/products/12', 'POST');

        assertFalse($was_get_called, 'GET route should not be called');
        assertTrue($was_post_called, 'POST route should be called');
    }
}
