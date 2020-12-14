# Routier

Routier is an Express.js like router that aims to be easy to use.

## Register a route

```php
$router = new Router();

$router->get('/about', function () {
    echo 'Look! I am in the /about route!';
});
```

## Use URL parameters

```php
$router = new Router();

$router->get('/user/:user_id', function ($params) {
    $user_id = $params['user_id'];
    echo "The id of the user is $user_id"; 
});
```

## Available Methods

The HTTP methods currently available are :

- get
- post
