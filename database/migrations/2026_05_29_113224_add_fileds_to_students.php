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
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();
            $table->string('address')->nullable();

            $table->decimal('qiyes_grade')->nullable();
            $table->decimal('SAAT_grade')->nullable();

            $table->renameColumn('image', 'cert_image');
            $table->string('SAAT_cert_image')->nullable();
            $table->string('qiyes_cert_image')->nullable();
            $table->string('other_cert_image')->nullable();
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
