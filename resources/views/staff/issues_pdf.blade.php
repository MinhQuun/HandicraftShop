<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; }
        h2, h3 { text-align: center; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
        .info-table td { border: none; padding: 4px 0; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>CỬA HÀNG MỸ NGHỆ</h2>
    <h3>PHIẾU XUẤT HÀNG</h3>
    <hr>

    <table class="info-table">
        <tr>
            <td><strong>Mã phiếu:</strong> {{ $header->MAPX }}</td>
            <td><strong>Ngày xuất:</strong> {{ \Carbon\Carbon::parse($header->NGAYXUAT)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Nhân viên:</strong> {{ $header->NHANVIEN }}</td>
            <td><strong>Khách hàng:</strong> {{ $header->KHACHHANG }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Địa chỉ:</strong> {{ $header->DIACHI }}</td>
        </tr>
        @if($header->MAKHUYENMAI)
            @php
                $promoValue = $header->LOAIKHUYENMAI === 'Giảm %'
                    ? ($header->GIAMGIA ?? 0) . '%'
                    : number_format($header->GIAMGIA ?? 0, 0, ',', '.') . 'đ';
            @endphp
            <tr>
                <td><strong>Khuyến mãi:</strong> {{ $header->MAKHUYENMAI }} - {{ $header->LOAIKHUYENMAI ?? 'Khuyến mãi' }} {{ $header->GIAMGIA !== null ? '(' . $promoValue . ')' : '' }}</td>
                <td><strong>Tiền giảm:</strong> {{ number_format($discountAmount ?? 0, 0, ',', '.') }} ₫</td>
            </tr>
        @endif
        <tr>
            <td><strong>Tổng trước KM:</strong> {{ number_format($subtotal, 0, ',', '.') }} ₫</td>
            <td><strong>Tổng sau KM:</strong> {{ number_format($tongTien, 0, ',', '.') }} ₫</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã SP</th>
                <th>Tên sản phẩm</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $i => $ln)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $ln->MASANPHAM }}</td>
                <td>{{ $ln->TENSANPHAM }}</td>
                <td>{{ $ln->SOLUONG }}</td>
                <td class="right">{{ number_format($ln->DONGIA, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($ln->THANHTIEN, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="right"><strong>Tổng cộng</strong></td>
                <td><strong>{{ $tongSL }}</strong></td>
                <td colspan="2" class="right"><strong>{{ number_format($tongTien, 0, ',', '.') }} ₫</strong></td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <div style="text-align: right;">
        <em>Ngày in: {{ now()->format('d/m/Y H:i') }}</em>
    </div>
</body>
</html>
