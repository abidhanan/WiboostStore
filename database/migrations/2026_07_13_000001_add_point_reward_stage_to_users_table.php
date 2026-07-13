<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'point_reward_stage')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('point_reward_stage')->default(0)->after('points');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'point_reward_stage')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('point_reward_stage');
            });
        }
    }
};
