<?php
namespace Modules\Integrations\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateOutboxMessagesTable extends Migration {
  public function up() {
    $this->forge->addField([
      'id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
      'tenant_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
      'event'=>['type'=>'VARCHAR','constraint'=>120],
      'payload_json'=>['type'=>'LONGTEXT'],
      'status'=>['type'=>'VARCHAR','constraint'=>16,'default'=>'pending'],
      'attempts'=>['type'=>'INT','constraint'=>11,'default'=>0],
      'next_attempt_at'=>['type'=>'DATETIME','null'=>true],
      'last_error'=>['type'=>'TEXT','null'=>true],
      'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
      'updated_at'=>['type'=>'DATETIME','null'=>true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey(['tenant_id','status']);
    $this->forge->createTable('outbox_messages', true);
  }
  public function down(){ $this->forge->dropTable('outbox_messages', true); }
}