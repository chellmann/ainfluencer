<?php

use App\Models\Brand;
use App\Models\Imagemodel;
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
        Schema::create('imagemodels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('company', 100);
            $table->timestamps();
        });

        $openai = Imagemodel::create([
            'company' => 'OpenAI',
            'name' => 'gpt-image-1',
        ]);

        Imagemodel::create([
            'company' => 'minimax',
            'name' => 'image-01',
        ]);

        Schema::table('brands', function (Blueprint $table) use ($openai) {
            $table->foreignIdFor(\App\Models\Imagemodel::class)->default($openai->id)->after('prompt_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagemodels');
    }
};
