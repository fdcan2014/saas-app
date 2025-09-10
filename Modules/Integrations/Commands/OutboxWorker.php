<?php
namespace Modules\Integrations\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Integrations\Services\DeliveryService;
use Modules\Integrations\Config\Integrations;

class OutboxWorker extends BaseCommand
{
    protected $group = 'Integrations';
    protected $name = 'integrations:outbox-worker';
    protected $description = 'Consume and deliver webhook outbox messages with retries.';

    public function run(array $params)
    {
        $once = in_array('--once', $params, true);
        $sleep = (int) (getenv('WORKER_SLEEP') ?: 5);
        $service = service('autoloader'); // ensure services loaded
        $delivery = service('Modules\Integrations\Services\DeliveryService');
        $cfg = new Integrations();

        if ($once) {
            $processed = $delivery->runBatch($cfg->workerBatch);
            CLI::write("Processed: {$processed}", 'green');
            return;
        }

        CLI::write('Starting outbox worker... (Ctrl+C to stop)', 'yellow');
        while (true) {
            $processed = $delivery->runBatch($cfg->workerBatch);
            if ($processed == 0) {
                sleep($sleep);
            }
        }
    }
}
