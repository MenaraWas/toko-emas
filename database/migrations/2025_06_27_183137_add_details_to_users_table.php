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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->foreignId('role_id')->after('password')->constrained();
            $table->foreignId('branch_id')->after('role_id')->nullable()->constrained();
            $table->enum('status', ['active', 'inactive'])->after('branch_id')->default('active');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //

            // Hapus dalam urutan terbalik dari method up()
            $table->dropSoftDeletes();
            $table->dropColumn('status');

            // dropConstrainedForeignId akan menghapus foreign key & kolom sekaligus
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
