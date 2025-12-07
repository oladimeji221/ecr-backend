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
        Schema::table('blogs', function (Blueprint $table) {
            $table->longText('content')->change();
            $table->string('status')->default('draft')->after('user_id');
            $table->string('meta_robot')->nullable()->after('meta_keywords');
            $table->string('canonical_url')->nullable()->after('meta_robot');
            $table->string('custom_url')->nullable()->after('canonical_url');
            $table->text('json_ld')->nullable()->after('custom_url');
            $table->string('og_title')->nullable()->after('json_ld');
            $table->string('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->text('content')->change();
            $table->dropColumn([
                'status',
                'meta_robot',
                'canonical_url',
                'custom_url',
                'json_ld',
                'og_title',
                'og_description',
                'og_image',
            ]);
        });
    }
};