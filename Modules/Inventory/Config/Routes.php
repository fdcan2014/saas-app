<?php
namespace Modules\Inventory\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Define as rotas para o módulo de movimentações de estoque.
 */
class Routes
{
    public static function routes(RouteCollection $routes): void
    {
        $routes->group('api/stock-movements', ['namespace' => 'Modules\Inventory\Controllers\Api'], function ($routes) {
            $routes->get('/', 'StockMovementController::index');
            $routes->post('/', 'StockMovementController::create');
        });
    }
}