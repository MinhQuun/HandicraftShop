<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\KhachHang;

class KhachHangSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy id quyền "khachhang"
        $roleId = DB::table('QUYEN')
            ->whereRaw('LOWER(TENQUYEN) = ?', ['khachhang'])
            ->value('MAQUYEN');

        if (!$roleId) {
            $this->command->warn('⚠️ Bảng QUYEN chưa có quyền "khachhang". Chạy QuyenSeeder trước.');
            return;
        }

        // Danh sách khách mẫu
        $customers = [
            [
                'name'  => 'Nguyễn Thị Tú Trinh',
                'email' => 'nttt@gmail.com',
                'phone' => '0901234567',
            ],
            [
                'name'  => 'Trần Minh Luân',
                'email' => 'tml@gmail.com',
                'phone' => '0902345678',
            ],
            [
                'name'  => 'Nguyễn Phạm Trường Duy',
                'email' => 'nptd@gmail.com',
                'phone' => '0903456789',
            ],
        ];

        foreach ($customers as $c) {
            // 1. Tạo user nếu chưa có
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'name'     => $c['name'],
                    'phone'    => $c['phone'],
                    'password' => Hash::make('123456'), // mật khẩu mặc định
                ]
            );

            // 2. Gán quyền khachhang
            $user->assignRole($roleId);

            // 3. Tạo hoặc cập nhật hồ sơ KH
            $kh = KhachHang::firstOrNew(['user_id' => $user->id]);
            $kh->HOTEN        = $user->name;
            $kh->EMAIL        = $user->email;
            $kh->SODIENTHOAI  = $user->phone;
            $kh->save();

            // 4. Thêm địa chỉ giao hàng mẫu (cho dễ test view sau này)
            $address = 'Số ' . rand(10,99) . ' Đường ABC, Quận ' . rand(1,12) . ', TP.HCM';
            DB::table('DIACHI_GIAOHANG')->insertOrIgnore([
                'MAKHACHHANG' => $kh->MAKHACHHANG,
                'DIACHI'      => $address,
            ]);
        }
    }
}
