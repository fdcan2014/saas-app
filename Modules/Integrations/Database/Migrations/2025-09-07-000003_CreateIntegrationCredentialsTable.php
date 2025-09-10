<?php
namespace Modules\Integrations\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateIntegrationCredentialsTable extends Migration {
  public function up() {
    $this->forge->addField([
      'id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
      'tenant_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
      'provider'=>['type'=>'VARCHAR','constraint'=>80],
      'credentials_enc'=>['type'=>'TEXT'],
      'meta'=>['type'=>'TEXT','null'=>true],
      'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
      'updated_at'=>['type'=>'DATETIME','null'=>true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey(['tenant_id','provider']);
    $this->forge->createTable('integration_credentials', true);
  }
  public function down(){ $this->forge->dropTable('integration_credentials', true); }
}