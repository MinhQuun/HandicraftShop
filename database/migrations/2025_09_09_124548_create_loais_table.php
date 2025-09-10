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
        Schema::create('LOAI', function (Blueprint $table) {
            $table->string('MALOAI', 10)->primary();
            $table->string('TENLOAI', 50);
            $table->integer('MADANHMUC')->nullable();

            $table->foreign('MADANHMUC')
                ->references('MADANHMUC')->on('DANHMUCSANPHAM')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('LOAI');
    }
};
