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

    $router->group(['prefix' => '/location', 'namespace' => 'UserLocation'], function () use ($router) {
        $router->post('/distrito/get-all', ['as' => 'location.distrito', 'uses' => 'Distrito\DistritoController@list']);
        $router->post('/distrito/dump', ['as' => 'location.distrito', 'uses' => 'Distrito\DistritoController@dump']);

        $router->post('/concelho/get-all', ['as' => 'location.concelho', 'uses' => 'Concelho\ConcelhoController@list']);
        $router->post('/freguesia/get-all', ['as' => 'location.freguesia', 'uses' => 'Freguesia\FreguesiaController@list']);
    });

    $router->group(['prefix' => '/users', 'namespace' => 'Administration\CRM'], function () use ($router) {
        $router->post('/check-mail', ['as' => 'users.mail-check', 'uses' => 'UserController@emailCheck']);
        $router->post('/registar', ['as' => 'users.register', 'uses' => 'UserController@register']);
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
