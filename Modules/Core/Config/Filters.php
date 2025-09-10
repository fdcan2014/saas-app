<?php
namespace Modules\Core\Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'api-key'    => \Modules\Core\Filters\ApiKeyAuthFilter::class,
        'scopes'     => \Modules\Core\Filters\ScopeFilter::class,
        'rate-limit' => \Modules\Core\Filters\RateLimitFilter::class,
        'tenant'     => \Modules\Core\Filters\TenantContextFilter::class,
    ];

    public array $globals = [
        'before' => [],
        'after'  => [],
    ];

    public array $methods = [];

    public array $filters = [
        // Example of using filters on routes can be added in app/Config/Routes.php or module routes
    ];
}
