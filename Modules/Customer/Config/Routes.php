<?php
namespace Modules\Customer\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Rotas para o módulo de clientes.
 */
/** @var RouteCollection $routes */

$routes->group('api/customers', ['namespace' => 'Modules\Customer\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->get('', 'CustomerController::index');
    $routes->post('', 'CustomerController::create');
    $routes->get('(:num)', 'CustomerController::show/$1');
    $routes->put('(:num)', 'CustomerController::update/$1');
    $routes->delete('(:num)', 'CustomerController::delete/$1');
});