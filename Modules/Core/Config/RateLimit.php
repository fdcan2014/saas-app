<?php
namespace Modules\Core\Config;
use CodeIgniter\Config\BaseConfig;

class RateLimit extends BaseConfig
{
    public int $limit  = 100;   // requests
    public int $window = 60;    // seconds
    public string $headerLimit     = 'X-RateLimit-Limit';
    public string $headerRemaining = 'X-RateLimit-Remaining';
    public string $headerReset     = 'X-RateLimit-Reset';
}
