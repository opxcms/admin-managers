<?php

use Core\Foundation\Database\OpxBlueprint;
use Core\Foundation\Database\OpxMigration;
use Illuminate\Support\Facades\Schema;

class CreateManagersTable extends OpxMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->schema->create('managers', static function (OpxBlueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('blocked')->default(0);
            $table->timestamp('last_password_change')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id', 'email', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('managers');
    }
}
