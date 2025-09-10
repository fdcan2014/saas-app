<?php
namespace Modules\Product\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Rotas para o módulo de produtos.
 */
/** @var RouteCollection $routes */

$routes->group('api/products', ['namespace' => 'Modules\Product\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->get('', 'ProductController::index');
    $routes->post('', 'ProductController::create');
    $routes->get('(:num)', 'ProductController::show/$1');
    $routes->put('(:num)', 'ProductController::update/$1');
    $routes->delete('(:num)', 'ProductController::delete/$1');
});