<?php

use App\Models\Brand;
use App\Models\Music;
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
        Schema::create('brand_music', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Brand::class);
            $table->foreignIdFor(Music::class);
            $table->timestamps();
        });
    }

};
