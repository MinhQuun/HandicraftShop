document.addEventListener("DOMContentLoaded", () => {
    // Khởi tạo Chart.js cho inoutChart
    try {
        const data = window.INOUT_CHART || { labels: [], in: [], out: [], closing: [] };
        const ctx = document.getElementById('inoutChart');
        if (ctx && data.labels && data.labels.length) {
            // chuẩn bị datasets
            const chart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Nhập',
                            data: data.in,
                            stack: 'stack1',
                            backgroundColor: 'rgba(58,134,255,0.85)',
                        },
                        {
                            label: 'Xuất',
                            data: data.out,
                            stack: 'stack1',
                            backgroundColor: 'rgba(250,100,100,0.9)',
                        },
                        {
                            label: 'Tồn cuối',
                            data: data.closing,
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(26,163,163,0.95)',
                            tension: 0.2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: { autoSkip: false, maxRotation: 40, minRotation: 0 }
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Số lượng' },
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
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
            document.getElementById('modalProductType').textContent = this.dataset.loai;
            document.getElementById('modalProductSupplier').textContent = this.dataset.ncc;
            document.getElementById('modalProductOpening').textContent = this.dataset.opening;
            document.getElementById('modalProductIn').textContent = this.dataset.in;
            document.getElementById('modalProductOut').textContent = this.dataset.out;
            document.getElementById('modalProductClosing').textContent = this.dataset.closing;
            document.getElementById('modalProductGianhap').textContent = Number(this.dataset.gianhap).toLocaleString();
            document.getElementById('modalProductPrice').textContent = Number(this.dataset.price).toLocaleString();
            document.getElementById('modalProductDesc').textContent = this.dataset.mota || '—';
            
            const img = document.getElementById('modalProductImage');
            if(this.dataset.hinh) {
                img.src = this.dataset.hinh;
                img.alt = this.dataset.ten;
            } else {
                img.src = '';
                img.alt = 'Không có hình';
            }

            modal.show();
        });
    });
    // Toast từ flash (dùng SweetAlert2 nếu có)
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

    // Blur selects trong filter khi mở modal (nếu bạn có modal khác trên trang)
    function blurFilterSelects() {
        document.querySelectorAll('.products-filter select, .products-filter .form-select').forEach(el => el.blur());
    }
    document.querySelectorAll('[data-bs-target]').forEach(b => b.addEventListener('click', blurFilterSelects));
});
    $(document).ready(function() {
        $('#productSelect').select2({
            placeholder: "Chọn sản phẩm...",
            allowClear: true,
            width: '100%'
        });
    });

document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('productDetailModal'));
    
    document.querySelectorAll('.product-row').forEach(row => {
        row.addEventListener('click', function() {
            document.getElementById('modalProductName').textContent = this.dataset.ten;
            document.getElementById('modalProductCode').textContent = this.dataset.masp;
            document.getElementById('modalProductOpening').textContent = this.dataset.opening;
            document.getElementById('modalProductIn').textContent = this.dataset.in;
            document.getElementById('modalProductOut').textContent = this.dataset.out;
            document.getElementById('modalProductClosing').textContent = this.dataset.closing;
            document.getElementById('modalProductPrice').textContent = Number(this.dataset.price).toLocaleString();
            
            const img = document.getElementById('modalProductImage');
            if(this.dataset.hinh) {
                img.src = this.dataset.hinh;
                img.alt = this.dataset.ten;
            } else {
                img.src = '';
                img.alt = 'Không có hình';
            }

            modal.show();
        });
    });
});