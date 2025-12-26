<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
    var shopStatusChart = null;
    var shopCategoryChart = null;
    var newShopTrendChart = null;
    var categoryAvgSalesChart = null;
    var shopSalesRankChart = null;
    var shopAppointmentRankChart = null;
    var regionShopChart = null;

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    // AJAX 데이터 로드
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
            // 현재 데이터 저장 (차트 타입 전환용)
            currentChartData = data;
            
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

    // 주요 지표 카드 업데이트
    function updateSummaryCards(summary) {
        if (!summary) summary = {};

        $('#total_shop_count').text(formatNumber(summary.total_shop_count || 0));
        $('#active_shop_count').text(formatNumber(summary.active_shop_count || 0));
        $('#new_shop_count').text(formatNumber(summary.new_shop_count || 0));
        $('#pending_shop_count').text(formatNumber(summary.pending_shop_count || 0));
        $('#closed_shop_count').text(formatNumber(summary.closed_shop_count || 0));
        
        var activationRate = summary.activation_rate || 0;
        $('#activation_rate').text(activationRate.toFixed(1) + '%');
    }

    // 가맹점 상태별 분포 차트 (파이)
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
                    backgroundColor: colors
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

    // 업종별 가맹점 수 분포 차트 (파이 차트만 사용)
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
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                    borderColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
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
                            // 텍스트가 잘리지 않도록 처리
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
        
        // 차트 렌더링 후 legend를 포함한 전체 너비를 계산하여 스크롤 컨테이너 조정
        setTimeout(function() {
            var chartContainer = document.querySelector('.shop-category-chart-scroll');
            var chartInner = document.querySelector('.shop-category-chart-inner');
            if (chartContainer && chartInner && shopCategoryChart) {
                // Chart.js가 생성한 차트 컨테이너 찾기
                var chartWrapper = chartInner.querySelector('div');
                if (chartWrapper) {
                    // 차트와 legend를 포함한 전체 너비 계산
                    var totalWidth = chartWrapper.scrollWidth || chartWrapper.offsetWidth;
                    var containerWidth = chartContainer.clientWidth;
                    
                    // legend가 컨테이너 밖으로 나가면 스크롤 가능하도록 최소 너비 설정
                    if (totalWidth > containerWidth) {
                        chartInner.style.minWidth = totalWidth + 'px';
                    } else {
                        chartInner.style.minWidth = '100%';
                    }
                }
            }
        }, 200);
    }

    // 가맹점 신규 등록 추이 차트 (선 그래프)
    function renderNewShopTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            labels.push(trendData[i].date || '');
            data.push(trendData[i].count || 0);
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
                    label: '신규 등록 수',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '신규 등록: ' + formatNumber(context.parsed.y || 0) + '개';
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

    // 업종별 평균 매출 차트 (가로 막대)
    function renderCategoryAvgSalesChart(salesData) {
        if (!salesData) salesData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < salesData.length; i++) {
            labels.push(salesData[i].category_name || '미지정');
            data.push(salesData[i].avg_sales || 0);
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
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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
                                return '평균 매출: ' + formatCurrency(context.parsed.x || 0);
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

    // 가맹점별 매출 순위 차트 (가로 막대)
    function renderShopSalesRankChart(rankData) {
        if (!rankData) rankData = [];

        var labels = [];
        var data = [];

        // 최대 20개, 가맹점명이 길면 축약
        for (var i = 0; i < Math.min(rankData.length, 20); i++) {
            var shopName = rankData[i].shop_name || '미지정';
            if (shopName.length > 15) {
                shopName = shopName.substring(0, 15) + '...';
            }
            labels.push(shopName);
            data.push(rankData[i].total_sales || 0);
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
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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
                                return '매출: ' + formatCurrency(context.parsed.x || 0);
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

    // 가맹점별 예약 건수 순위 차트 (가로 막대)
    function renderShopAppointmentRankChart(rankData) {
        if (!rankData) rankData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < Math.min(rankData.length, 20); i++) {
            var shopName = rankData[i].shop_name || '미지정';
            if (shopName.length > 15) {
                shopName = shopName.substring(0, 15) + '...';
            }
            labels.push(shopName);
            data.push(rankData[i].appointment_count || 0);
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
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
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
                                return '예약 건수: ' + formatNumber(context.parsed.x || 0) + '건';
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

    // 지역별 가맹점 분포 차트
    var regionShopChartType = 'pie';
    function renderRegionShopChart(regionData, chartType) {
        if (!regionData) regionData = [];
        if (!chartType) chartType = regionShopChartType;

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
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: '가맹점 수',
                    data: data,
                    backgroundColor: chartType === 'pie' ?
                        ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'] :
                        'rgba(153, 102, 255, 0.6)',
                    borderColor: chartType === 'pie' ?
                        ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'] :
                        'rgba(153, 102, 255, 1)',
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
                                var value = context.parsed.y !== undefined ? context.parsed.y : context.parsed;
                                if (chartType === 'pie') {
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + formatNumber(value) + '개 (' + percentage + '%)';
                                } else {
                                    return label + ': ' + formatNumber(value) + '개';
                                }
                            }
                        }
                    }
                },
                scales: chartType === 'bar' ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            }
                        }
                    }
                } : {}
            }
        };

        regionShopChart = new Chart(ctx, chartConfig);
    }

    // 가맹점별 상세 통계 테이블 렌더링
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
            var row = '<tr>';
            row += '<td>' + (item.shop_name || '-') + '</td>';
            row += '<td>' + (item.category_names || '-') + '</td>';
            row += '<td class="text-right">' + formatCurrency(item.total_sales || 0) + '</td>';
            row += '<td class="text-right">' + formatNumber(item.appointment_count || 0) + '건</td>';
            row += '<td class="text-right">' + formatCurrency(item.avg_sales || 0) + '</td>';
            row += '<td>' + (item.status_kr || '-') + '</td>';
            row += '</tr>';
            $tbody.append(row);
        }
    }

    // 현재 차트 데이터 저장
    var currentChartData = {};

    // 차트 타입 전환 버튼 이벤트
    $(document).on('click', '.chart-type-btn', function() {
        var $btn = $(this);
        var chartId = $btn.data('chart');
        var chartType = $btn.data('type');
        var $btnGroup = $btn.siblings('.chart-type-btn').addBack();

        $btnGroup.removeClass('active');
        $btn.addClass('active');

        // 차트 타입 저장 및 재렌더링
        if (chartId === 'region_shop_chart') {
            regionShopChartType = chartType;
            if (currentChartData.region_distribution) {
                renderRegionShopChart(currentChartData.region_distribution, chartType);
            }
        }
    });

    // 데이터 내보내기 버튼 (TODO: 실제 구현 필요)
    $('#export_btn').on('click', function() {
        alert('데이터 내보내기 기능은 추후 구현 예정입니다.');
    });

    // 초기화
    $(document).ready(function() {
        // 초기 데이터 로드
        loadShopStatistics();

        // 조회 버튼 클릭 이벤트
        $('#search_btn').on('click', function() {
            loadShopStatistics();
        });
    });

})(jQuery);
</script>
