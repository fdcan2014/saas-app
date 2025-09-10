<?php
namespace Modules\Integrations\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateApiKeysTable extends Migration {
  public function up() {
    $this->forge->addField([
      'id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
      'tenant_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
      'name'=>['type'=>'VARCHAR','constraint'=>120],
      'token_id'=>['type'=>'VARCHAR','constraint'=>32],
      'token_hash'=>['type'=>'VARCHAR','constraint'=>64],
      'last4'=>['type'=>'VARCHAR','constraint'=>8],
      'scopes'=>['type'=>'TEXT','null'=>true],
      'revoked_at'=>['type'=>'DATETIME','null'=>true],
      'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
      'updated_at'=>['type'=>'DATETIME','null'=>true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey(['tenant_id','token_id']);
    $this->forge->createTable('api_keys', true);
  }
  public function down(){ $this->forge->dropTable('api_keys', true); }
}