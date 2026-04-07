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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('country');
            $table->string('password');
            $table->string('referral_id')->unique()->nullable();

            // Professional Profile
            $table->string('expertise_category');
            $table->string('role_specialization');

            // Contribution Type
            $table->json('contribution_type');

            // Additional Information
            $table->text('short_bio');
            $table->text('skills_or_tools');
            $table->string('linkedin_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('medium_blog_link')->nullable();
            $table->string('github_link')->nullable();
            $table->string('sample_work_path')->nullable(); // To store the path of the uploaded file

            // Agreements
            $table->boolean('agreed_to_terms');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};