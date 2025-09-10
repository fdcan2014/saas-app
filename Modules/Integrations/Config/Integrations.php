<?php
namespace Modules\Integrations\Config;
use CodeIgniter\Config\BaseConfig;

class Integrations extends BaseConfig
{
    public int $workerBatch = 10;
    public int $httpTimeout = 10; // seconds
    public int $maxAttempts = 8;
    public int $backoffBase = 15; // seconds
    public int $backoffCap  = 3600; // seconds
    public string $userAgent = 'SaaS-Integrations-Worker/1.0';
}
