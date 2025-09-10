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
        Schema::create('SANPHAM', function (Blueprint $table) {
            $table->string('MASANPHAM', 10)->primary();
            $table->string('TENSANPHAM', 255);
            $table->string('HINHANH', 255)->nullable();
            $table->decimal('GIABAN', 18, 0)->nullable();
            $table->integer('SOLUONGTON')->nullable();
            $table->string('MOTA', 1000)->nullable();
            $table->string('MALOAI', 10)->nullable();
            $table->string('MAKHUYENMAI', 10)->nullable();
            $table->integer('MANHACUNGCAP')->nullable();

            $table->foreign('MALOAI')->references('MALOAI')->on('LOAI')->cascadeOnDelete();
            // 2 FK dưới để đúng cấu trúc; nếu bạn chưa migrate 2 bảng kia thì có thể tạm bỏ:
            // $table->foreign('MAKHUYENMAI')->references('MAKHUYENMAI')->on('KHUYENMAI')->nullOnDelete();
            // $table->foreign('MANHACUNGCAP')->references('MANHACUNGCAP')->on('NHACUNGCAP')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('SANPHAM');
    }
};
