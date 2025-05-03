<?php

use App\Models\Account;
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
        Schema::table('platform_posts', function (Blueprint $table) {
            $table->foreignIdFor(Account::class)->after('post_id');
            $table->datetime('published_at')->nullable()->after('foreign_id');
            $table->datetime('planned_time')->nullable()->after('foreign_id');
        });
    }

};
