document.addEventListener("DOMContentLoaded", () => {
    // Khởi tạo Chart.js cho revenueChart
    try {
        const data = window.REVENUE_CHART || { labels: [], revenues: [], costs: [], profits: [] };
        const ctx = document.getElementById('revenueChart');
        if (ctx && data.labels && data.labels.length) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Vốn',
                            data: data.costs,
                            stack: 'stack1',
                            backgroundColor: 'rgba(255, 165, 0, 0.85)', // Cam
                        },
                        {
                            label: 'Doanh thu',
                            data: data.revenues,
                            stack: 'stack1',
                            backgroundColor: 'rgba(0, 191, 255, 0.85)', // Xanh dương
                        },
                        {
                            label: 'Lợi nhuận',
                            data: data.profits,
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(148, 0, 211, 0.95)', // Tím
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
                            title: { display: true, text: 'Số tiền (đ)' },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + 'đ';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('vi-VN') + 'đ';
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch (e) {
        console.error('Chart render error', e);
    }

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
});