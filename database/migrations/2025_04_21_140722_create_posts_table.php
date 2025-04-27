<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Brand;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Brand::class);
            $table->text('text');
            $table->text('author')->nullable();
            $table->text('image');
            $table->text('font_color')->default('#000000');
            $table->text('font_size')->default('66');
            $table->text('font_style')->default('1');
            $table->text('caption')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('rendered_at')->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
