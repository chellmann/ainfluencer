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
        Schema::create('music', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('author', 100);
            $table->string('description', 255)->nullable()->default('');
            $table->string('file', 100);
            $table->integer('start_time')->default(0);
            $table->timestamps();
        });
    }

};
