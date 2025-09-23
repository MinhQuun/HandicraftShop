<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) KHACHHANG.user_id -> users(id) ON DELETE SET NULL + UNIQUE nullable (1-1)
        // -- Đổi cột sang nullable
        Schema::table('KHACHHANG', function (Blueprint $table) {
            // nếu chưa phải unsignedBigInteger, bạn có thể change() tương ứng
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // -- Gỡ FK cũ nếu có, bằng cách dò tên constraint theo INFORMATION_SCHEMA
        $this->dropFkIfExists('KHACHHANG', 'user_id');

        // -- Thêm FK mới SET NULL
        DB::statement('ALTER TABLE KHACHHANG ADD CONSTRAINT KH_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');

        // -- UNIQUE 1-1 (nullable unique index)
        // (Nếu đã có unique, bắt lỗi sẽ bỏ qua)
        try {
            Schema::table('KHACHHANG', function (Blueprint $table) {
                $table->unique('user_id', 'khachhang_user_unique');
            });
        } catch (\Throwable $e) {
            // ignore if exists
        }

        // 2) DIACHI_GIAOHANG.MAKHACHHANG -> ON DELETE CASCADE (xóa KH => xóa địa chỉ)
        $this->dropFkIfExists('DIACHI_GIAOHANG', 'MAKHACHHANG');
        DB::statement('ALTER TABLE DIACHI_GIAOHANG ADD CONSTRAINT DC_KH_fk FOREIGN KEY (MAKHACHHANG) REFERENCES KHACHHANG(MAKHACHHANG) ON DELETE CASCADE');

        // 3) DONHANG.MAKHACHHANG -> RESTRICT/NO ACTION (giữ lịch sử)
        // Nếu hiện đang là CASCADE thì thay lại:
        // $this->dropFkIfExists('DONHANG', 'MAKHACHHANG');
        // DB::statement('ALTER TABLE DONHANG ADD CONSTRAINT DH_KH_fk FOREIGN KEY (MAKHACHHANG) REFERENCES KHACHHANG(MAKHACHHANG) ON DELETE RESTRICT');
    }

    public function down(): void
    {
        // rollback tối thiểu: bỏ unique và FK mới, có thể thêm lại FK cũ tùy bạn
        try {
            Schema::table('KHACHHANG', function (Blueprint $table) {
                $table->dropUnique('khachhang_user_unique');
            });
        } catch (\Throwable $e) {}

        $this->dropFkIfExists('KHACHHANG', 'user_id');
        DB::statement('ALTER TABLE KHACHHANG ADD CONSTRAINT KHACHHANG_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');

        $this->dropFkIfExists('DIACHI_GIAOHANG', 'MAKHACHHANG');
        DB::statement('ALTER TABLE DIACHI_GIAOHANG ADD CONSTRAINT DIACHI_GIAOHANG_MAKHACHHANG_foreign FOREIGN KEY (MAKHACHHANG) REFERENCES KHACHHANG(MAKHACHHANG) ON DELETE RESTRICT');
    }

    // Helper: drop FK theo cột (dò tên constraint động, tránh lệ thuộc tên)
    private function dropFkIfExists(string $table, string $column): void
    {
        $dbName = DB::getDatabaseName();
        $sql = "
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ";
        $rows = DB::select($sql, [$dbName, $table, $column]);
        if (!empty($rows)) {
            $name = $rows[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$name}");
        }
    }
};
