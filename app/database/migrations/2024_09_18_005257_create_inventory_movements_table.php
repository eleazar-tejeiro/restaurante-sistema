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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id('movement_id');
            $table->unsignedBigInteger('inventory_id');
            $table->foreign('inventory_id')->references('id')->on('inventories');
            $table->enum('movement_type', ['input', 'output', 'adjustment']);
            $table->integer('quantity');
            $table->dateTime('movement_date')->useCurrent();
            $table->unsignedBigInteger('reference_id');
            $table->string('reference_type',50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};