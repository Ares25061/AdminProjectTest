<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bans', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')
                ->constrained(table: 'users', indexName: 'bans_user_id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('reason');
            $table->timestamp('expiration') ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bans');
    }
};
