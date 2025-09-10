<?php
namespace Modules\Integrations\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateApiKeyRateLimitsTable extends Migration {
  public function up() {
    $this->forge->addField([
      'id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
      'rate_key'=>['type'=>'VARCHAR','constraint'=>64], // api:<id> | user:<id> | ip:<ip>
      'bucket'=>['type'=>'INT','constraint'=>11],       // floor(time/window)
      'count'=>['type'=>'INT','constraint'=>11,'default'=>0],
      'updated_at'=>['type'=>'DATETIME','null'=>true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey(['rate_key','bucket'], false, true);
    $this->forge->createTable('api_key_rate_limits', true);
  }
  public function down(){ $this->forge->dropTable('api_key_rate_limits', true); }
}
