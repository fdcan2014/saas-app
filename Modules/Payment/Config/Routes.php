<?php
namespace Modules\Payment\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Define as rotas para o módulo de pagamentos.
 */
class Routes
{
    public static function routes(RouteCollection $routes): void
    {
        $routes->group('api/payments', ['namespace' => 'Modules\Payment\Controllers\Api'], function ($routes) {
            $routes->get('/', 'PaymentController::index');
            $routes->post('/', 'PaymentController::create');
            $routes->patch('(:num)', 'PaymentController::update/$1');
        });
    }
}