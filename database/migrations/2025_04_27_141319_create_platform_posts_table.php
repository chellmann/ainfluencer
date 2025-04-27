<?php

use App\Models\Post;
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
        Schema::create('platform_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Post::class);
            $table->string('platform', 100);
            $table->string('foreign_id', 100)->nullable();
            $table->timestamps();
        });
    }

};
