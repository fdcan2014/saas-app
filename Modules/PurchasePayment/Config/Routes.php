<?php
namespace Modules\PurchasePayment\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Define as rotas do módulo de pagamentos de compras.
 */
class Routes
{
    public static function routes(RouteCollection $routes): void
    {
        $routes->group('api/purchase-payments', ['namespace' => 'Modules\PurchasePayment\Controllers\Api'], function ($routes) {
            $routes->get('/', 'PurchasePaymentController::index');
            $routes->post('/', 'PurchasePaymentController::create');
            $routes->patch('(:num)', 'PurchasePaymentController::update/$1');
        });
    }
}