<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'emote')) {
                $table->string('emote', 20)->nullable()->after('slug');
            }
        });
        Schema::table('promos', function (Blueprint $table) {
            if (!Schema::hasColumn('promos', 'image')) {
                $table->string('image')->nullable()->after('theme');
            }
        });
    }
    public function down() {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('emote');
        });
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};