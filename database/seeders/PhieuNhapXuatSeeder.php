<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PhieuNhapXuatSeeder extends Seeder
{
    public function run(): void
    {
        // Nhân viên thực hiện phiếu: lấy user đầu tiên, nếu chưa có thì tạo tạm
        $nvId = DB::table('users')->value('id');
        if (!$nvId) {
            $nvId = DB::table('users')->insertGetId([
                'name' => 'Seeder Staff',
                'email' => 'staff@example.com',
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::beginTransaction();
        try {
            /* =========================================================
             * 1) PHIẾU NHẬP — cố định NCC và sản phẩm theo dữ liệu của bạn
             *    - PN#1: NCC 1 -> SP001, SP004, SP007
             *    - PN#2: NCC 12 -> SP025, SP028, SP030
             *    - PN#3: NCC 5 -> SP018, SP020, SP033
             *  DONGIA nhập: lấy đúng GIANHAP từ SANPHAM
             * ========================================================= */
            $pnPlans = [
                [
                    'ncc'     => 1,
                    'ghichu'  => 'SEED PN 1',
                    'details' => [
                        ['SP001', 10],
                        ['SP004',  6],
                        ['SP007',  8],
                    ],
                ],
                [
                    'ncc'     => 12,
                    'ghichu'  => 'SEED PN 2',
                    'details' => [
                        ['SP025',  5],
                        ['SP028',  7],
                        ['SP030',  4],
                    ],
                ],
                [
                    'ncc'     => 5,
                    'ghichu'  => 'SEED PN 3',
                    'details' => [
                        ['SP018',  3],
                        ['SP020',  3],
                        ['SP033',  5],
                    ],
                ],
            ];

            foreach ($pnPlans as $plan) {
                // Nếu đã seed trước đó thì dùng lại (dựa trên GHICHU)
                $mapn = DB::table('PHIEUNHAP')
                    ->where('GHICHU', $plan['ghichu'])
                    ->value('MAPN');

                if (!$mapn) {
                    $mapn = DB::table('PHIEUNHAP')->insertGetId([
                        'MANHACUNGCAP' => $plan['ncc'],
                        'NHANVIEN_ID'  => $nvId,
                        'TRANGTHAI'    => 'DA_XAC_NHAN', // để trigger cộng tồn
                        'GHICHU'       => $plan['ghichu'],
                        'NGAYNHAP'     => now(),
                    ]);
                }

                foreach ($plan['details'] as [$masp, $sl]) {
                    $p = DB::table('SANPHAM')->where('MASANPHAM', $masp)->first();
                    if (!$p) continue; // an toàn: bỏ qua nếu dữ liệu chưa có

                    // Bảo đảm sản phẩm thuộc đúng NCC của phiếu
                    if ((int)$p->MANHACUNGCAP !== (int)$plan['ncc']) {
                        // nếu không khớp NCC thì bỏ qua để không sai nghiệp vụ
                        continue;
                    }

                    DB::table('CT_PHIEUNHAP')->updateOrInsert(
                        ['MAPN' => $mapn, 'MASANPHAM' => $masp],
                        [
                            'SOLUONG' => $sl,
                            'DONGIA'  => $p->GIANHAP, // dùng GIANHAP làm giá cho seeding
                        ]
                    );
                }
            }

            /* =========================================================
             * 2) PHIẾU XUẤT — bán cho 3 khách đã seed (theo email)
             *    - PX#1: nttt@gmail.com  -> SP001 x2, SP006 x1
             *    - PX#2: tml@gmail.com   -> SP033 x1, SP036 x2
             *    - PX#3: nptd@gmail.com  -> SP041 x1, SP045 x1
             *  DONGIA bán: lấy đúng GIABAN từ SANPHAM
             *  Có MADIACHI thì gán địa chỉ đầu tiên của KH từ DiaChiGiaoHangSeeder.
             * ========================================================= */
            $hasAddressOnPX = Schema::hasColumn('PHIEUXUAT', 'MADIACHI');

            $pxPlans = [
                [
                    'email'   => 'nguyenthitutrinh120504@gmail.com',
                    'ghichu'  => 'SEED PX 1',
                    'details' => [
                        ['SP001', 2],
                        ['SP006', 1],
                    ],
                ],
                [
                    'email'   => 'hakachi303@gmail.com',
                    'ghichu'  => 'SEED PX 2',
                    'details' => [
                        ['SP033', 1],
                        ['SP036', 2],
                    ],
                ],
                [
                    'email'   => 'nptduyc920@gmail.com',
                    'ghichu'  => 'SEED PX 3',
                    'details' => [
                        ['SP041', 1],
                        ['SP045', 1],
                    ],
                ],
            ];

            foreach ($pxPlans as $plan) {
                $kh = DB::table('KHACHHANG')->where('EMAIL', $plan['email'])->first();
                if (!$kh) continue;

                $payload = [
                    'MAKHACHHANG' => $kh->MAKHACHHANG,
                    'NHANVIEN_ID' => $nvId,
                    'TRANGTHAI'   => 'DA_XAC_NHAN', // để trigger trừ tồn
                    'GHICHU'      => $plan['ghichu'],
                    'NGAYXUAT'    => now(),
                ];

                if ($hasAddressOnPX) {
                    $addr = DB::table('DIACHI_GIAOHANG')
                        ->where('MAKHACHHANG', $kh->MAKHACHHANG)
                        ->first();

                    if (!$addr) {
                        // Nếu không tìm thấy địa chỉ, lấy từ dữ liệu trong DiaChiGiaoHangSeeder
                        $defaultAddresses = [
                            'nguyenthitutrinh120504@gmail.com' => '33 Phan Huy Ích, P. 15, Q. Tân Bình, TP.HCM',
                            'hakachi303@gmail.com' => 'Ấp 5, xã Tân Tây, Huyện Gò Công Đông, Tỉnh Tiền Giang',
                            'nptduyc920@gmail.com' => '16 Núi Cấm 1, xã Vĩnh Thái, Thành Phố Nha Trang, Tỉnh Khánh Hòa',
                        ];

                        $addrId = DB::table('DIACHI_GIAOHANG')->insertGetId([
                            'MAKHACHHANG' => $kh->MAKHACHHANG,
                            'DIACHI'      => $defaultAddresses[$plan['email']] ?? 'Địa chỉ mặc định (seeder)',
                        ]);
                    } else {
                        $addrId = $addr->MADIACHI;
                    }
                    $payload['MADIACHI'] = $addrId;
                }

                // Nếu đã seed trước đó thì dùng lại (dựa trên GHICHU)
                $mapx = DB::table('PHIEUXUAT')
                    ->where('GHICHU', $plan['ghichu'])
                    ->value('MAPX');

                if (!$mapx) {
                    $mapx = DB::table('PHIEUXUAT')->insertGetId($payload);
                }

                foreach ($plan['details'] as [$masp, $sl]) {
                    $p = DB::table('SANPHAM')->where('MASANPHAM', $masp)->first();
                    if (!$p) continue;

                    DB::table('CT_PHIEUXUAT')->updateOrInsert(
                        ['MAPX' => $mapx, 'MASANPHAM' => $masp],
                        [
                            'SOLUONG' => $sl,
                            'DONGIA'  => $p->GIABAN,
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('Seeder PhieuNhapXuatSeeder lỗi: ' . $e->getMessage());
            throw $e;
        }
    }
}