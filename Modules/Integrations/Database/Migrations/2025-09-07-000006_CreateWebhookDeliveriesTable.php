<?php
namespace Modules\Integrations\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateWebhookDeliveriesTable extends Migration {
  public function up() {
    $this->forge->addField([
      'id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
      'tenant_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
      'webhook_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
      'outbox_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
      'status'=>['type'=>'VARCHAR','constraint'=>16],   // success|failed
      'http_status'=>['type'=>'INT','constraint'=>11,'null'=>true],
      'duration_ms'=>['type'=>'INT','constraint'=>11,'null'=>true],
      'response_snippet'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],
      'error'=>['type'=>'TEXT','null'=>true],
      'attempt'=>['type'=>'INT','constraint'=>11,'default'=>1],
      'delivered_at'=>['type'=>'DATETIME','null'=>true],
      'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey(['tenant_id','outbox_id']);
    $this->forge->createTable('webhook_deliveries', true);
  }
  public function down(){ $this->forge->dropTable('webhook_deliveries', true); }
}
