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
        Schema::table('posts', function (Blueprint $table) {
            $table->text('image_prompt')->nullable()->after('caption');
            $table->boolean('unblock_image')->nullable()->default(false)->after('caption');
            $table->boolean('unblock_video')->nullable()->default(false)->after('caption');
            $table->boolean('unblock_post')->nullable()->default(false)->after('caption');
        });
    }

};
