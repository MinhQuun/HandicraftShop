// public/js/orders.js

document.addEventListener("DOMContentLoaded", function () {
    // Xử lý hủy đơn hàng
    const cancelForms = document.querySelectorAll(".form-cancel-order");
    cancelForms.forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Hủy đơn hàng?',
                text: `Bạn có chắc chắn muốn hủy đơn hàng ${form.dataset.orderId}?`,
                showCancelButton: true,
                confirmButtonText: 'Xác nhận',
                cancelButtonText: 'Hủy',
                reverseButtons: true
            }).then(result => {
                if(result.isConfirmed) form.submit();
            });
        });
    });

    // Xử lý mở modal chi tiết
    document.querySelectorAll(".btn-detail").forEach(btn => {
        btn.addEventListener("click", () => openDetail(btn.dataset.id));
    });
});

// Hàm mở modal chi tiết
async function openDetail(id) {
    try {
        const res = await fetch(`${window.location.origin}/my-orders/${id}/json`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        document.getElementById("md_id").textContent = data.MADONHANG;
        document.getElementById("md_date").textContent = new Date(data.NGAYDAT).toLocaleDateString('vi-VN');
        document.getElementById("md_delivery").textContent = data.NGAYGIAO ? new Date(data.NGAYGIAO).toLocaleDateString('vi-VN') : 'Chưa giao';
        document.getElementById("md_status").textContent = data.TRANGTHAI;
        document.getElementById("md_total_qty").textContent = data.chiTiets.reduce((sum, i) => sum + i.SOLUONG, 0);
        document.getElementById("md_total").textContent = new Intl.NumberFormat('vi-VN', { style:'currency', currency:'VND'}).format(data.TONGTHANHTIEN);
        document.getElementById("md_address").textContent = data.diaChi?.DIACHI ?? '';
        document.getElementById("md_payment").textContent = data.hinhThucTT?.LOAITT ?? '';

        const tbody = document.querySelector("#modalDetail tbody");
        tbody.innerHTML = '';
        data.chiTiets.forEach((item, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${i+1}</td>
                <td>${item.MASANPHAM}</td>
                <td class="text-truncate" title="${item.TENSP}">${item.TENSP}</td>
                <td class="text-end">${item.SOLUONG}</td>
                <td class="text-end">${new Intl.NumberFormat('vi-VN', { style:'currency', currency:'VND'}).format(item.DONGIA)}</td>
                <td class="text-end">${new Intl.NumberFormat('vi-VN', { style:'currency', currency:'VND'}).format(item.THANHTIEN)}</td>
            `;
            tbody.appendChild(tr);
        });

        const detailModal = document.getElementById("modalDetail");
        const bsDetail = new bootstrap.Modal(detailModal);
        bsDetail.show();

    } catch (err) {
        console.error(err);
        Swal.fire({icon:'error', title:'Lỗi', text:'Không thể tải chi tiết đơn hàng.'});
    }
}
