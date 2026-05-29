<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('qiyes_grade', 8, 2)->change();
            $table->decimal('SAAT_grade', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
};
