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
        Schema::create('wasted', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id');
            $table->foreign('inventory_id')->references('id')->on('users');
            $table->integer('quantity');
            $table->date('waste_date');
            $table->enum('reason', ['expired', 'damaged', 'overproduction','quality_issues','other']);
            $table->string('description',255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wasted');
    }
};