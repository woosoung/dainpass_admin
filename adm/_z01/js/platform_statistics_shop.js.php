<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    var shopStatusChart = null;
    var shopCategoryChart = null;
    var newShopTrendChart = null;
    var categoryAvgSalesChart = null;
    var shopSalesRankChart = null;
    var shopAppointmentRankChart = null;
    var regionShopChart = null;
    var regionShopChartType = 'pie';

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    function loadShopStatistics() {
        var periodType = $('#period_type').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var categoryId = $('#category_filter').val() || '';
        var region = $('#region_filter').val() || '';

        if (!startDate || !endDate) {
            alert('조회 기간을 선택해 주세요.');
            return;
        }

        var $btn = $('#search_btn');
        $btn.prop('disabled', true).text('조회 중...');

        $.ajax({
            url: './ajax/platform_statistics_shop_data.php',
            type: 'POST',
            dataType: 'json',
            data: {
                period_type: periodType,
                start_date: startDate,
                end_date: endDate,
                category_id: categoryId,
                region: region
            }
        }).done(function(res) {
            if (!res || !res.success) {
                alert(res && res.message ? res.message : '데이터 조회에 실패했습니다.');
                return;
            }

            var data = res.data || {};
            updateSummaryCards(data.summary);
            renderShopStatusChart(data.status_distribution);
            renderShopCategoryChart(data.category_distribution);
            renderNewShopTrendChart(data.new_shop_trend);
            renderCategoryAvgSalesChart(data.category_avg_sales);
            renderShopSalesRankChart(data.shop_sales_rank);
            renderShopAppointmentRankChart(data.shop_appointment_rank);
            renderRegionShopChart(data.region_distribution, regionShopChartType);
            renderShopDetailTable(data.shop_detail_list);
        }).fail(function(xhr, status, error) {
            var errorMsg = '서버 통신 중 오류가 발생했습니다.';
            if (xhr.responseText) {
                try {
                    var errorRes = JSON.parse(xhr.responseText);
                    if (errorRes && errorRes.message) {
                        errorMsg = errorRes.message;
                    }
                } catch(e) {
                    // JSON 파싱 실패 시 기본 메시지 사용
                }
            }
            alert(errorMsg);
            console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(summary) {
        if (!summary) summary = {};

        $('#total_shop_count').text(formatNumber(summary.total_shop_count || 0));
        $('#active_shop_count').text(formatNumber(summary.active_shop_count || 0));
        $('#new_shop_count').text(formatNumber(summary.new_shop_count || 0));
        $('#pending_shop_count').text(formatNumber(summary.pending_shop_count || 0));
        $('#closed_shop_count').text(formatNumber(summary.closed_shop_count || 0));
        var activationRate = typeof summary.activation_rate !== 'undefined' ? summary.activation_rate : 0;
        $('#activation_rate').text(activationRate.toFixed(1) + '%');
    }

    function renderShopStatusChart(statusData) {
        if (!statusData) statusData = {};

        var labels = ['정상', '대기', '폐업', '금지'];
        var data = [
            statusData.active || 0,
            statusData.pending || 0,
            statusData.closed || 0,
            statusData.shutdown || 0
        ];
        var colors = ['#4CAF50', '#FFC107', '#9E9E9E', '#F44336'];

        var ctx = document.getElementById('shop_status_chart');
        if (!ctx) return;

        if (shopStatusChart) {
            shopStatusChart.destroy();
        }

        shopStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + '개 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderShopCategoryChart(categoryData) {
        if (!categoryData) categoryData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < categoryData.length; i++) {
            labels.push(categoryData[i].category_name || '미지정');
            data.push(categoryData[i].shop_count || 0);
        }

        var ctx = document.getElementById('shop_category_chart');
        if (!ctx) return;

        if (shopCategoryChart) {
            shopCategoryChart.destroy();
        }

        var chartConfig = {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: '가맹점 수',
                    data: data,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'],
                    borderColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        right: 20
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 8,
                            padding: 5,
                            font: {
                                size: 10
                            },
                            usePointStyle: false,
                            textAlign: 'left'
                        },
                        fullSize: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + '개 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        };

        shopCategoryChart = new Chart(ctx, chartConfig);

        // 차트 렌더링 완료 후 legend 너비를 계산하여 스크롤 컨테이너 조정
        setTimeout(function() {
            if (shopCategoryChart && shopCategoryChart.legend) {
                var legend = shopCategoryChart.legend;
                var legendWidth = legend.width || 0;
                var chartAreaWidth = shopCategoryChart.chartArea ? shopCategoryChart.chartArea.width : 0;
                var totalWidth = chartAreaWidth + legendWidth + 30;

                var $scrollContainer = $(ctx).closest('.shop-category-chart-scroll');
                var $innerContainer = $(ctx).parent('.shop-category-chart-inner');

                if ($scrollContainer.length && $innerContainer.length) {
                    $innerContainer.css('min-width', totalWidth + 'px');
                }
            }
        }, 100);
    }

    function renderNewShopTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            var date = trendData[i].date || '';
            if (date) {
                labels.push(date);
                data.push(trendData[i].count || 0);
            }
        }

        var ctx = document.getElementById('new_shop_trend_chart');
        if (!ctx) return;

        if (newShopTrendChart) {
            newShopTrendChart.destroy();
        }

        newShopTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '신규 가맹점 수',
                    data: data,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '신규 가맹점: ' + formatNumber(context.parsed.y) + '개';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCategoryAvgSalesChart(avgSalesData) {
        if (!avgSalesData) avgSalesData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < avgSalesData.length; i++) {
            labels.push(avgSalesData[i].category_name || '미지정');
            data.push(avgSalesData[i].avg_sales || 0);
        }

        var ctx = document.getElementById('category_avg_sales_chart');
        if (!ctx) return;

        if (categoryAvgSalesChart) {
            categoryAvgSalesChart.destroy();
        }

        categoryAvgSalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '평균 매출',
                    data: data,
                    backgroundColor: '#4BC0C0',
                    borderColor: '#4BC0C0',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '평균 매출: ' + formatCurrency(context.parsed.x);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderShopSalesRankChart(salesRankData) {
        if (!salesRankData) salesRankData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < salesRankData.length; i++) {
            labels.push(salesRankData[i].shop_name || '미지정');
            data.push(salesRankData[i].total_sales || 0);
        }

        var ctx = document.getElementById('shop_sales_rank_chart');
        if (!ctx) return;

        if (shopSalesRankChart) {
            shopSalesRankChart.destroy();
        }

        shopSalesRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    backgroundColor: '#9966FF',
                    borderColor: '#9966FF',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '매출: ' + formatCurrency(context.parsed.x);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderShopAppointmentRankChart(appointmentRankData) {
        if (!appointmentRankData) appointmentRankData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < appointmentRankData.length; i++) {
            labels.push(appointmentRankData[i].shop_name || '미지정');
            data.push(appointmentRankData[i].appointment_count || 0);
        }

        var ctx = document.getElementById('shop_appointment_rank_chart');
        if (!ctx) return;

        if (shopAppointmentRankChart) {
            shopAppointmentRankChart.destroy();
        }

        shopAppointmentRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: '#FF9F40',
                    borderColor: '#FF9F40',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '예약 건수: ' + formatNumber(context.parsed.x) + '건';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderRegionShopChart(regionData, chartType) {
        if (!regionData) regionData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < regionData.length; i++) {
            labels.push(regionData[i].region || '미지정');
            data.push(regionData[i].shop_count || 0);
        }

        var ctx = document.getElementById('region_shop_chart');
        if (!ctx) return;

        if (regionShopChart) {
            regionShopChart.destroy();
        }

        var chartConfig = {
            type: chartType || 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: '가맹점 수',
                    data: data,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'],
                    borderColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: chartType === 'pie' ? 'right' : 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || (chartType === 'pie' ? context.parsed : context.parsed.y);
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + '개 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        };

        if (chartType === 'bar') {
            chartConfig.options.indexAxis = 'y';
            chartConfig.options.scales = {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatNumber(value);
                        }
                    }
                }
            };
        }

        regionShopChart = new Chart(ctx, chartConfig);
    }

    function renderShopDetailTable(detailList) {
        if (!detailList) detailList = [];

        var $tbody = $('#shop_detail_table tbody');
        $tbody.empty();

        if (detailList.length === 0) {
            $tbody.append('<tr><td colspan="6" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < detailList.length; i++) {
            var item = detailList[i];
            var statusText = '';
            switch(item.status) {
                case 'active': statusText = '정상'; break;
                case 'pending': statusText = '대기'; break;
                case 'closed': statusText = '폐업'; break;
                case 'shutdown': statusText = '금지'; break;
                default: statusText = item.status || '-';
            }

            var row = '<tr>' +
                '<td>' + (item.shop_name || '-') + '</td>' +
                '<td>' + (item.category_names || '-') + '</td>' +
                '<td class="text-right">' + formatCurrency(item.total_sales) + '</td>' +
                '<td class="text-right">' + formatNumber(item.appointment_count) + '건</td>' +
                '<td class="text-right">' + formatCurrency(item.avg_sales) + '</td>' +
                '<td>' + statusText + '</td>' +
                '</tr>';
            $tbody.append(row);
        }
    }

    // 차트 타입 전환 버튼 이벤트
    $(document).on('click', '.chart-type-btn', function() {
        var $btn = $(this);
        var chartName = $btn.data('chart');
        var chartType = $btn.data('type');

        $btn.siblings('.chart-type-btn').removeClass('active');
        $btn.addClass('active');

        if (chartName === 'region_shop_chart') {
            regionShopChartType = chartType;
            // 데이터 다시 로드하여 차트 재렌더링
            loadShopStatistics();
        }
    });

    // 조회 버튼 이벤트
    $(document).on('click', '#search_btn', function() {
        loadShopStatistics();
    });

    // 페이지 로드 시 초기 데이터 로드
    $(document).ready(function() {
        loadShopStatistics();
    });
})(jQuery);
</script>

