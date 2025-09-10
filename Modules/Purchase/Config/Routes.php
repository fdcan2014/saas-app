<?php
namespace Modules\Purchase\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Define as rotas para o módulo de compras.
 */
class Routes
{
    public static function routes(RouteCollection $routes): void
    {
        $routes->group('api/purchases', ['namespace' => 'Modules\Purchase\Controllers\Api'], function ($routes) {
            $routes->get('/', 'PurchaseController::index');
            $routes->post('/', 'PurchaseController::create');
            $routes->get('(:num)', 'PurchaseController::show/$1');
            $routes->patch('(:num)', 'PurchaseController::update/$1');
            $routes->delete('(:num)', 'PurchaseController::delete/$1');
        });
    }
}