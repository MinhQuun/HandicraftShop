// staff-inventory.js

document.addEventListener("DOMContentLoaded", () => {
    const modalEl = document.getElementById('productDetailModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.product-row').forEach(row => {
        row.addEventListener('click', function() {
            const data = this.dataset;

            document.getElementById('modalProductName').textContent = data.ten;
            document.getElementById('modalProductCode').textContent = data.masp;
            document.getElementById('modalProductType').textContent = data.loai;
            document.getElementById('modalProductSupplier').textContent = data.ncc;
            document.getElementById('modalProductStock').textContent = data.stock;
            document.getElementById('modalProductCost').textContent = Number(data.cost ?? 0).toLocaleString();
            document.getElementById('modalProductPrice').textContent = Number(data.price ?? 0).toLocaleString();
            document.getElementById('modalProductDesc').textContent = data.mota || '—';

            const img = document.getElementById('modalProductImage');
            if (data.hinh) {
                img.src = data.hinh;
                img.alt = data.ten;
            } else {
                img.src = '';
                img.alt = 'Không có hình';
            }

            modal.show();
        });
    });

    // Flash messages (SweetAlert2)
    const flash = document.getElementById("flash");
    if (flash && window.Swal) {
        const msg = flash.dataset.success || flash.dataset.error || flash.dataset.info || flash.dataset.warning;
        if (msg) {
            let icon = 'success';
            if (flash.dataset.error) icon = 'error';
            else if (flash.dataset.info) icon = 'info';
            else if (flash.dataset.warning) icon = 'warning';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon,
                title: msg,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
            });
        }
    }

    // Initialize select2
    if (window.jQuery) {
        $('#productSelect').select2({
            placeholder: "Chọn sản phẩm...",
            allowClear: true,
            width: '100%'
        });
    }
});
