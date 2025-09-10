<?php
namespace Modules\PurchaseReturn\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Define rotas para o módulo de devoluções de compras.
 */
class Routes
{
    public static function routes(RouteCollection $routes): void
    {
        $routes->group('api/purchase-returns', ['namespace' => 'Modules\PurchaseReturn\Controllers\Api'], function ($routes) {
            $routes->get('/', 'PurchaseReturnController::index');
            $routes->post('/', 'PurchaseReturnController::create');
            $routes->get('(:num)', 'PurchaseReturnController::show/$1');
        });
    }
}