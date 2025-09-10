<?php
/**
 * Definição de rotas específicas do módulo Auth.
 *
 * Este arquivo será carregado pelo core da aplicação para registrar as rotas
 * do módulo.  As rotas são agrupadas por namespace para evitar colisão.
 */

$routes->group('auth', ['namespace' => 'Modules\Auth\Controllers'], function ($routes) {
    $routes->get('login', 'LoginController::index');
    $routes->post('login', 'LoginController::login');
});

$routes->group('api/auth', ['namespace' => 'Modules\Auth\Controllers\Api'], function ($routes) {
    $routes->post('login', 'AuthController::login');
});

// Rotas de gestão de usuários (perfís) por tenant
$routes->group('api/users', ['namespace' => 'Modules\Auth\Controllers\Api'], static function ($routes) {
    $routes->get('', 'UserController::index');
    $routes->post('', 'UserController::create');
    $routes->get('(:num)', 'UserController::show/$1');
    $routes->put('(:num)', 'UserController::update/$1');
    $routes->delete('(:num)', 'UserController::delete/$1');
});