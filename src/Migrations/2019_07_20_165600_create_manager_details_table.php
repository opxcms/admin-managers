<?php

use Core\Foundation\Database\OpxBlueprint;
use Core\Foundation\Database\OpxMigration;
use Illuminate\Support\Facades\Schema;

class CreateManagerDetailsTable extends OpxMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->schema->create('manager_details', static function (OpxBlueprint $table) {
            $table->increments('id');
            $table->integer('manager_id');
            $table->string('display_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->softDeletes();

            $table->index(['id', 'manager_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('manager_details');
    }
}
