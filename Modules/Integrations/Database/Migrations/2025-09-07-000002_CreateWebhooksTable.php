<?php
namespace Modules\Integrations\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateWebhooksTable extends Migration {
  public function up() {
    $this->forge->addField([
      'id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
      'tenant_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
      'event'=>['type'=>'VARCHAR','constraint'=>80],
      'url'=>['type'=>'VARCHAR','constraint'=>255],
      'secret_enc'=>['type'=>'TEXT','null'=>true],
      'active'=>['type'=>'TINYINT','constraint'=>1,'default'=>1],
      'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
      'updated_at'=>['type'=>'DATETIME','null'=>true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey(['tenant_id','event']);
    $this->forge->createTable('webhooks', true);
  }
  public function down(){ $this->forge->dropTable('webhooks', true); }
}