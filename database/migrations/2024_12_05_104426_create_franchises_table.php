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
        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('category_id');
            $table->string('name')->unique();
            $table->longText('description')->nullable();
            $table->string('imagesrc')->nullable();
            $table->integer('mincash')->default(0);
            $table->tinyInteger('other_franchise')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('verified')->default(0);
            $table->tinyInteger('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('franchises');
    }
};
