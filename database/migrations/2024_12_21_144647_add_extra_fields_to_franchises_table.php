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
        Schema::table('franchises', function (Blueprint $table) {
            $table->integer('year_founded')->default(0);
            $table->integer('units')->default(0);
            $table->integer('min_investment')->default(0);
            $table->integer('max_investment')->default(0);
            $table->integer('franchise_fee')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('franchises', function (Blueprint $table) {
            $table->dropColumn('year_founded');
            $table->dropColumn('units');
            $table->dropColumn('min_investment');
            $table->dropColumn('max_investment');
            $table->dropColumn('franchise_fee');
        });
    }
};
