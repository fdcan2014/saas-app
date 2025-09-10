<?php
namespace Modules\Order\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Rotas para o módulo de pedidos (vendas).
 */
/** @var RouteCollection $routes */

$routes->group('api/orders', ['namespace' => 'Modules\Order\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->get('', 'OrderController::index');
    $routes->post('', 'OrderController::create');
    $routes->get('(:num)', 'OrderController::show/$1');
    $routes->patch('(:num)', 'OrderController::update/$1');
    $routes->delete('(:num)', 'OrderController::delete/$1');
});