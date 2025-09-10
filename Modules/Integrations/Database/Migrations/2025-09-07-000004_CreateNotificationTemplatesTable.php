<?php
namespace Modules\Integrations\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateNotificationTemplatesTable extends Migration {
  public function up() {
    $this->forge->addField([
      'id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
      'tenant_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
      'channel'=>['type'=>'VARCHAR','constraint'=>32],
      'name'=>['type'=>'VARCHAR','constraint'=>120],
      'subject'=>['type'=>'VARCHAR','constraint'=>200,'null'=>true],
      'body'=>['type'=>'TEXT'],
      'locale'=>['type'=>'VARCHAR','constraint'=>16,'default'=>'pt-BR'],
      'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
      'updated_at'=>['type'=>'DATETIME','null'=>true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey(['tenant_id','channel','name']);
    $this->forge->createTable('notification_templates', true);
  }
  public function down(){ $this->forge->dropTable('notification_templates', true); }
}