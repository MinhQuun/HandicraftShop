document.addEventListener("DOMContentLoaded", () => {
    // Khởi tạo Chart.js cho lowstockChart
    try {
        const data = window.LOWSTOCK_CHART || { labels: [], counts: [] };
        const ctx = document.getElementById('lowstockChart');
        if (ctx && data.labels && data.labels.length) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Số lượng sản phẩm',
                        data: data.counts,
                        backgroundColor: ['rgba(255, 99, 132, 0.85)', 'rgba(255, 159, 64, 0.85)', 'rgba(75, 192, 192, 0.85)'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true, ticks: { autoSkip: false, maxRotation: 0 } },
                        y: { beginAtZero: true, title: { display: true, text: 'Số lượng' } }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    }
                }
            });
        }
    } catch (e) {
        console.error('Chart render error', e);
    }

    const modal = new bootstrap.Modal(document.getElementById('productDetailModal'));

    document.querySelectorAll('.product-row').forEach(row => {
        row.addEventListener('click', function() {
            document.getElementById('modalProductName').textContent = this.dataset.ten;
            document.getElementById('modalProductCode').textContent = this.dataset.masp;
            document.getElementById('modalProductStock').textContent = this.dataset.stock;
            document.getElementById('modalProductStatus').textContent = this.dataset.stock <= 0 ? 'Hết hàng' : 'Sắp hết';
            document.getElementById('modalProductPrice').textContent = Number(this.dataset.price).toLocaleString();
            document.getElementById('modalProductDesc').textContent = this.dataset.mota || '—';

            modal.show();
        });
    });

    // Toast từ flash
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
});