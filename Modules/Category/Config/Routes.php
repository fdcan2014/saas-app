<?php
namespace Modules\Category\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Rotas para o módulo de categorias.
 */
/** @var RouteCollection $routes */

$routes->group('api/categories', ['namespace' => 'Modules\Category\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->get('', 'CategoryController::index');
    $routes->post('', 'CategoryController::create');
    $routes->get('(:num)', 'CategoryController::show/$1');
    $routes->put('(:num)', 'CategoryController::update/$1');
    $routes->delete('(:num)', 'CategoryController::delete/$1');
});