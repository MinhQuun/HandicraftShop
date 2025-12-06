<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Phiếu nhập {{ $header->MAPN }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h2 { text-align: center; margin-bottom: 12px; letter-spacing: 0.5px; }
        .meta { margin-bottom: 12px; width: 100%; }
        .meta td { padding: 4px 0; }
        .meta strong { display: inline-block; min-width: 120px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #f5f5f5; }
        .text-end { text-align: right; }
        .totals td { background: #fafafa; font-weight: bold; }
    </style>
</head>
<body>
    <h2>PHIẾU NHẬP HÀNG</h2>

    <table class="meta">
        <tr>
            <td><strong>Mã PN:</strong> #{{ $header->MAPN }}</td>
            <td><strong>Ngày nhập:</strong> {{ \Carbon\Carbon::parse($header->NGAYNHAP)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Nhà cung cấp:</strong> {{ $header->TENNHACUNGCAP }}</td>
            <td><strong>Nhân viên:</strong> {{ $header->NHANVIEN }}</td>
        </tr>
        <tr>
            <td><strong>Trạng thái:</strong> {{ $header->TRANGTHAI }}</td>
            <td><strong>Ghi chú:</strong> {{ $header->GHICHU ?? '—' }}</td>
        </tr>
    </table>

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
                <td class="text-end">{{ number_format($item->DONGIA, 0, ',', '.') }} đ</td>
                <td class="text-end">{{ number_format($item->THANHTIEN, 0, ',', '.') }} đ</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end totals">Tổng SL</td>
                <td class="text-end totals">{{ number_format($TONGSL ?? 0, 0, ',', '.') }}</td>
                <td class="text-end totals" colspan="2">Tổng tiền: {{ number_format($TONGTIEN, 0, ',', '.') }} đ</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
