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
        Schema::create('column_data', function (Blueprint $table) {
            $table->id();
            $table->string('table_token');
            $table->string('column_token');
            $table->string('data')->nullable();
            $table->string('type');
            $table->integer('s_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('column_data');
    }
};
