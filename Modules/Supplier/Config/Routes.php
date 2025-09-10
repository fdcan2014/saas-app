<?php
namespace Modules\Supplier\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Rotas para o módulo de fornecedores.
 */
/** @var RouteCollection $routes */

$routes->group('api/suppliers', ['namespace' => 'Modules\Supplier\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->get('', 'SupplierController::index');
    $routes->post('', 'SupplierController::create');
    $routes->get('(:num)', 'SupplierController::show/$1');
    $routes->put('(:num)', 'SupplierController::update/$1');
    $routes->delete('(:num)', 'SupplierController::delete/$1');
});