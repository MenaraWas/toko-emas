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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
             $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('movement_type', ['in', 'out', 'adjust']);
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('reference_type')->nullable();
            $table->bigInteger('reference_id')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
