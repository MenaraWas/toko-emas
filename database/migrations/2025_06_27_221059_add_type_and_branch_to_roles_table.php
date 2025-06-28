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
        Schema::table('roles', function (Blueprint $table) {
            //
            $table->string('type')->default('management'); // management atau branch
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            //
            $table->dropColumn('type');
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
