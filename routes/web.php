<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/*
$router->get('/', function () use ($router) {
    return $router->app->version();
});
*/
$router->get('/api', 'ExampleController@index');

$router->group(['prefix' => '/api'], function () use ($router) {

    $router->group(['prefix' => '/auth'], function () use ($router) {
        $router->post('/login', ['as' => 'auth.login', 'uses' => 'JWTAuthController@login']);
        $router->post('/me', ['as' => 'auth.logout', 'uses' => 'JWTAuthController@me']);
        $router->post('/refresh', ['as' => 'auth.refresh', 'uses' => 'JWTAuthController@refresh']);
        $router->post('/register', ['as' => 'auth.register', 'uses' => 'JWTAuthController@register']);
        $router->post('/logout', ['as' => 'auth.logout', 'uses' => 'JWTAuthController@logout']);
    });

    $router->group(['prefix' => '/users', 'namespace' => 'Administration\CRM', 'middleware' => 'auth'], function () use ($router) {
        $router->post('/get-all', ['as' => 'users.list', 'uses' => 'UserController@list']);
        $router->post('/get', ['as' => 'users.get', 'uses' => 'UserController@get']);
        $router->group(['prefix' => '/roles', 'namespace' => 'Role', 'middleware' => 'auth'], function () use ($router) {
            $router->post('/get-all', ['as' => 'roles.list', 'uses' => 'UserRoleController@list']);
        });
        $router->group(['prefix' => '/status', 'namespace' => 'Status', 'middleware' => 'auth'], function () use ($router) {
            $router->post('/get-all', ['as' => 'status.list', 'uses' => 'UserStatusController@list']);
        });
    });

});
