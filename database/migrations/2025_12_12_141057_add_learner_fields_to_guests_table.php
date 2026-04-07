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
        Schema::table('guests', function (Blueprint $table) {
            // Add category column
            $table->string('category')->after('password'); // 'learner' or 'partner'

            // Make partner-specific fields nullable
            $table->string('expertise_category')->nullable()->change();
            $table->string('role_specialization')->nullable()->change();
            $table->json('contribution_type')->nullable()->change();
            $table->text('short_bio')->nullable()->change();
            $table->text('skills_or_tools')->nullable()->change();

            // Add learner-specific fields
            $table->string('primary_interest_area')->nullable()->after('category');
            $table->string('learning_goal')->nullable()->after('primary_interest_area');
            $table->string('guarantor_full_name')->nullable()->after('learning_goal');
            $table->string('guarantor_phone_number')->nullable()->after('guarantor_full_name');
            $table->string('relationship_to_learner')->nullable()->after('guarantor_phone_number');
            $table->string('how_did_you_hear')->nullable()->after('relationship_to_learner');
            $table->string('coupon_code')->nullable()->after('how_did_you_hear');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('expertise_category')->nullable(false)->change();
            $table->string('role_specialization')->nullable(false)->change();
            $table->json('contribution_type')->nullable(false)->change();
            $table->text('short_bio')->nullable(false)->change();
            $table->text('skills_or_tools')->nullable(false)->change();
            
            $table->dropColumn([
                'category',
                'primary_interest_area',
                'learning_goal',
                'guarantor_full_name',
                'guarantor_phone_number',
                'relationship_to_learner',
                'how_did_you_hear',
                'coupon_code',
            ]);
        });
    }
};