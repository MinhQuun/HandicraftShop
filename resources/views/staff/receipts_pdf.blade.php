<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Phiếu nhập {{ $header->MAPN }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 15px; }
        .info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #f0f0f0; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h2>PHIẾU NHẬP HÀNG</h2>

    <div class="info">
        <p><strong>Mã PN:</strong> {{ $header->MAPN }}</p>
        <p><strong>Nhà cung cấp:</strong> {{ $header->TENNHACUNGCAP }}</p>
        <p><strong>Nhân viên:</strong> {{ $header->NHANVIEN }}</p>
        <p><strong>Ngày nhập:</strong> {{ $header->NGAYNHAP }}</p>
        <p><strong>Trạng thái:</strong> {{ $header->TRANGTHAI }}</p>
        <p><strong>Ghi chú:</strong> {{ $header->GHICHU ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã SP</th>
                <th>Tên SP</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->MASANPHAM }}</td>
                <td>{{ $item->TENSANPHAM }}</td>
                <td class="text-end">{{ $item->SOLUONG }}</td>
                <td class="text-end">{{ number_format($item->DONGIA) }}</td>
                <td class="text-end">{{ number_format($item->THANHTIEN) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Tổng tiền</strong></td>
                <td class="text-end"><strong>{{ number_format($TONGTIEN) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
