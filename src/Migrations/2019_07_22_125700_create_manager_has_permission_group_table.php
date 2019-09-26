<?php

use Core\Foundation\Database\OpxBlueprint;
use Core\Foundation\Database\OpxMigration;
use Illuminate\Support\Facades\Schema;

class CreateManagerHasPermissionGroupTable extends OpxMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->schema->create('manager_has_permission_group', static function (OpxBlueprint $table) {
            $table->integer('manager_id');
            $table->integer('group_id');

            $table->index(['manager_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('manager_has_permission_group');
    }
}
