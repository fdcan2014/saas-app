<?php
namespace Modules\Tenant\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Definições de rotas para o módulo Tenant.
 *
 * Esse arquivo é incluído automaticamente pelo autoload de módulos do
 * CodeIgniter 4 se configurado em Config\Modules.php.  Aqui definimos
 * endpoints RESTful para gestão de tenants.
 */
/** @var RouteCollection $routes */

$routes->group('api/tenants', ['namespace' => 'Modules\Tenant\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->get('', 'TenantController::index');
    $routes->post('', 'TenantController::create');
    $routes->get('(:num)', 'TenantController::show/$1');
    $routes->put('(:num)', 'TenantController::update/$1');
    $routes->delete('(:num)', 'TenantController::delete/$1');
});