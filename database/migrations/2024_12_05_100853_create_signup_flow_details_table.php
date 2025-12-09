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
        Schema::create('signup_flow_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('role_id')->default(0);
            $table->integer('franchise_id')->nullable();
            $table->string('name')->nullable();
            $table->string('location')->nullable();
            $table->string('url')->nullable();
            $table->integer('industry')->default(0);
            $table->integer('investment_range')->default(0);
            $table->integer('support_type')->default(0);
            $table->integer('timeline_type')->default(0);
            $table->string('hashtags')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->string('type_of_franchises')->nullable();
            $table->integer('start_your_franchise')->default(0);
            $table->tinyInteger('expert_advice')->default(0);
            $table->integer('services')->default(0);
            $table->integer('experience')->default(0);
            $table->tinyInteger('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signup_flow_details');
    }
};
