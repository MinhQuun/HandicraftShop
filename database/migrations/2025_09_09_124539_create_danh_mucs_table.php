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
        Schema::create('DANHMUCSANPHAM', function (Blueprint $table) {
            $table->integer('MADANHMUC', true);               // AUTO_INCREMENT
            $table->string('TENDANHMUC', 100);
            // Không tạo timestamps vì DB gốc không có
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('DANHMUCSANPHAM');
    }
};
