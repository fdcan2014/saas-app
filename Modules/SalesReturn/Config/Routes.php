<?php
namespace Modules\SalesReturn\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Define rotas para o módulo de devoluções de vendas.
 */
class Routes
{
    public static function routes(RouteCollection $routes): void
    {
        $routes->group('api/sales-returns', ['namespace' => 'Modules\SalesReturn\Controllers\Api'], function ($routes) {
            $routes->get('/', 'SalesReturnController::index');
            $routes->post('/', 'SalesReturnController::create');
            $routes->get('(:num)', 'SalesReturnController::show/$1');
        });
    }
}