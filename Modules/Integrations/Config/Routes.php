<?php
use CodeIgniter\Router\RouteCollection;
/** @var RouteCollection $routes */
$routes->group('api/integrations', ['namespace'=>'Modules\\Integrations\\Controllers\\Api','filter'=>'rate-limit'], static function(RouteCollection $routes) {
    $routes->get('api-keys', 'ApiKeyController::index');
    $routes->post('api-keys', 'ApiKeyController::create');
    $routes->delete('api-keys/(:num)', 'ApiKeyController::delete/$1');

    $routes->get('webhooks', 'WebhookController::index');
    $routes->post('webhooks', 'WebhookController::create');
    $routes->patch('webhooks/(:num)', 'WebhookController::update/$1');
    $routes->delete('webhooks/(:num)', 'WebhookController::delete/$1');

    $routes->get('credentials', 'IntegrationCredentialController::index');
    $routes->post('credentials', 'IntegrationCredentialController::create');
    $routes->delete('credentials/(:num)', 'IntegrationCredentialController::delete/$1');

    $routes->get('templates', 'NotificationTemplateController::index');
    $routes->post('templates', 'NotificationTemplateController::create');
    $routes->patch('templates/(:num)', 'NotificationTemplateController::update/$1');
    $routes->delete('templates/(:num)', 'NotificationTemplateController::delete/$1');

    $routes->get('outbox', 'OutboxController::index');
});