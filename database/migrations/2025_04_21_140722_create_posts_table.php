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
            $table->string('author',255)->nullable();
            $table->string('image',100)->nullable();
            $table->string('font_color',10)->default('#000000');
            $table->string('font_size',10)->default('66');
            $table->string('font_style',10)->default('1');
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
