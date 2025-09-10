<?php
namespace Modules\Shipping\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Define rotas para o módulo de envios.
 */
class Routes
{
    public static function routes(RouteCollection $routes): void
    {
        $routes->group('api/shippings', ['namespace' => 'Modules\Shipping\Controllers\Api'], function ($routes) {
            $routes->get('/', 'ShippingController::index');
            $routes->post('/', 'ShippingController::create');
            $routes->get('(:num)', 'ShippingController::show/$1');
            $routes->patch('(:num)', 'ShippingController::update/$1');
        });
    }
}