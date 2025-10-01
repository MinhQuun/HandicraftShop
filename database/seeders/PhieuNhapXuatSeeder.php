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
             * 1) PHIẾU NHẬP
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
                $mapn = DB::table('PHIEUNHAP')
                    ->where('GHICHU', $plan['ghichu'])
                    ->value('MAPN');

                if (!$mapn) {
                    $mapn = DB::table('PHIEUNHAP')->insertGetId([
                        'MANHACUNGCAP' => $plan['ncc'],
                        'NHANVIEN_ID'  => $nvId,
                        'TRANGTHAI'    => 'DA_XAC_NHAN',
                        'GHICHU'       => $plan['ghichu'],
                        'NGAYNHAP'     => now(),
                    ]);
                }

                foreach ($plan['details'] as [$masp, $sl]) {
                    $p = DB::table('SANPHAM')->where('MASANPHAM', $masp)->first();
                    if (!$p) continue;

                    if ((int)$p->MANHACUNGCAP !== (int)$plan['ncc']) {
                        continue;
                    }

                    DB::table('CT_PHIEUNHAP')->updateOrInsert(
                        ['MAPN' => $mapn, 'MASANPHAM' => $masp],
                        [
                            'SOLUONG' => $sl,
                            'DONGIA'  => $p->GIANHAP,
                        ]
                    );
                }
            }

            /* =========================================================
             * 2) PHIẾU XUẤT
             * ========================================================= */
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

                // Đảm bảo có địa chỉ giao hàng
                $addr = DB::table('DIACHI_GIAOHANG')
                    ->where('MAKHACHHANG', $kh->MAKHACHHANG)
                    ->first();

                if (!$addr) {
                    $defaultAddresses = [
                        'nguyenthitutrinh120504@gmail.com' => '33 Phan Huy Ích, P. 15, Q. Tân Bình, TP.HCM',
                        'hakachi303@gmail.com' => 'Ấp 5, xã Tân Tây, Huyện Gò Công Đông, Tỉnh Tiền Giang',
                        'nptduyc920@gmail.com' => '16 Núi Cấm 1, xã Vĩnh Thái, Thành Phố Nha Trang, Tỉnh Khánh Hòa',
                    ];

                    $addrId = DB::table('DIACHI_GIAOHANG')->insertGetId([
                        'MAKHACHHANG' => $kh->MAKHACHHANG,
                        'DIACHI'      => $defaultAddresses[$plan['email']] ?? 'Địa chỉ mặc định (Seeder)',
                    ]);
                } else {
                    $addrId = $addr->MADIACHI;
                }

                $payload = [
                    'MAKHACHHANG' => $kh->MAKHACHHANG,
                    'NHANVIEN_ID' => $nvId,
                    'TRANGTHAI'   => 'DA_XAC_NHAN',
                    'GHICHU'      => $plan['ghichu'],
                    'NGAYXUAT'    => now(),
                    'MADIACHI'    => $addrId, // ✅ luôn có
                ];

                $mapx = DB::table('PHIEUXUAT')
                    ->where('GHICHU', $plan['ghichu'])
                    ->value('MAPX');

                
                if (!$mapx) {
                    $mapx = DB::table('PHIEUXUAT')->insertGetId($payload);

                    // Debug nếu không insert được
                    if (!$mapx) {
                        $this->command->error("❌ Không thể tạo phiếu xuất: " . json_encode($payload));
                        continue; // bỏ qua kế hoạch này để không bị lỗi null
                    }
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
