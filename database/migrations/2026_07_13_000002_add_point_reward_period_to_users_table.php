<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'point_reward_period')) {
            $afterColumn = Schema::hasColumn('users', 'point_reward_stage') ? 'point_reward_stage' : 'points';

            Schema::table('users', function (Blueprint $table) use ($afterColumn) {
                $column = $table->string('point_reward_period', 7)->nullable();

                if (Schema::hasColumn('users', $afterColumn)) {
                    $column->after($afterColumn);
                }
            });

            DB::table('users')
                ->whereNull('point_reward_period')
                ->update(['point_reward_period' => now()->format('Y-m')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'point_reward_period')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('point_reward_period');
            });
        }
    }
};
