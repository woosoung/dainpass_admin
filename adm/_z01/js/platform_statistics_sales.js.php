<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
    var salesTrendChart = null;
    var monthlySalesChart = null;
    var paymentMethodChart = null;
    var categorySalesChart = null;
    var hourlySalesChart = null;
    var weeklySalesChart = null;
    var shopContributionChart = null;

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    // AJAX 데이터 로드
    function loadSalesStatistics() {
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
            url: './ajax/platform_statistics_sales_data.php',
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

            updateSummaryCards(res.summary);
            renderSalesTrendChart(res.sales_trend);
            renderMonthlySalesChart(res.monthly_sales);
            renderPaymentMethodChart(res.payment_method_distribution);
            renderCategorySalesChart(res.category_sales);
            renderHourlySalesChart(res.hourly_sales);
            renderWeeklySalesChart(res.weekly_sales);
            renderShopContributionChart(res.shop_contribution);
            renderSettlementTable(res.settlement_logs);
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

        $('#total_sales').text(formatCurrency(summary.total_sales || 0));
        $('#total_settlement').text(formatCurrency(summary.total_settlement || 0));
        $('#avg_daily_sales').text(formatCurrency(summary.avg_daily_sales || 0));
        $('#total_payment_count').text(formatNumber(summary.total_payment_count || 0) + '건');
        $('#avg_payment_amount').text(formatCurrency(summary.avg_payment_amount || 0));
        $('#pending_settlement').text(formatCurrency(summary.pending_settlement || 0));
    }

    // 기간별 플랫폼 매출 추이 차트 (선 그래프)
    function renderSalesTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            labels.push(trendData[i].date || '');
            data.push(trendData[i].sales || 0);
        }

        var ctx = document.getElementById('sales_trend_chart');
        if (!ctx) return;

        if (salesTrendChart) {
            salesTrendChart.destroy();
        }

        salesTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
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
                                return '매출: ' + formatCurrency(context.parsed.y || 0);
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

    // 월별 매출 비교 차트 (막대 차트)
    function renderMonthlySalesChart(monthlyData) {
        if (!monthlyData) monthlyData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < monthlyData.length; i++) {
            labels.push(monthlyData[i].month || '');
            data.push(monthlyData[i].sales || 0);
        }

        var ctx = document.getElementById('monthly_sales_chart');
        if (!ctx) return;

        if (monthlySalesChart) {
            monthlySalesChart.destroy();
        }

        monthlySalesChart = new Chart(ctx, {
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
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '매출: ' + formatCurrency(context.parsed.y || 0);
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

    // 결제 수단별 매출 분포 차트 (파이 차트)
    function renderPaymentMethodChart(methodData) {
        if (!methodData) methodData = [];

        var labels = [];
        var data = [];
        var paymentMethodNames = {
            'CARD': '신용카드',
            'BANK_TRANSFER': '계좌이체',
            'VIRTUAL_ACCOUNT': '가상계좌',
            'MOBILE': '휴대폰',
            'POINT': '포인트'
        };

        for (var i = 0; i < methodData.length; i++) {
            var method = methodData[i].payment_method || '미지정';
            labels.push(paymentMethodNames[method] || method);
            data.push(methodData[i].sales_amount || 0);
        }

        var ctx = document.getElementById('payment_method_chart');
        if (!ctx) return;

        if (paymentMethodChart) {
            paymentMethodChart.destroy();
        }

        paymentMethodChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
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
                                return label + ': ' + formatCurrency(value) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // 업종별 매출 분포 차트 (가로 막대)
    function renderCategorySalesChart(categoryData) {
        if (!categoryData) categoryData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < categoryData.length; i++) {
            labels.push(categoryData[i].category_name || '미지정');
            data.push(categoryData[i].sales_amount || 0);
        }

        var ctx = document.getElementById('category_sales_chart');
        if (!ctx) return;

        if (categorySalesChart) {
            categorySalesChart.destroy();
        }

        categorySalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
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

    // 시간대별 매출 분포 차트 (막대 차트)
    function renderHourlySalesChart(hourlyData) {
        if (!hourlyData) hourlyData = [];

        // 0~23시 모든 시간대 데이터 준비
        var hourMap = {};
        for (var i = 0; i < hourlyData.length; i++) {
            hourMap[hourlyData[i].hour] = hourlyData[i].sales_amount || 0;
        }

        var labels = [];
        var data = [];
        for (var h = 0; h < 24; h++) {
            labels.push(h + '시');
            data.push(hourMap[h] || 0);
        }

        var ctx = document.getElementById('hourly_sales_chart');
        if (!ctx) return;

        if (hourlySalesChart) {
            hourlySalesChart.destroy();
        }

        hourlySalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    backgroundColor: function(context) {
                        var hour = context.dataIndex;
                        // 피크 시간대 강조 (10시~22시)
                        if (hour >= 10 && hour <= 22) {
                            return 'rgba(255, 99, 132, 0.6)';
                        }
                        return 'rgba(54, 162, 235, 0.6)';
                    },
                    borderColor: function(context) {
                        var hour = context.dataIndex;
                        if (hour >= 10 && hour <= 22) {
                            return 'rgba(255, 99, 132, 1)';
                        }
                        return 'rgba(54, 162, 235, 1)';
                    },
                    borderWidth: 1
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
                                return '매출: ' + formatCurrency(context.parsed.y || 0);
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

    // 요일별 매출 분포 차트 (막대 차트)
    function renderWeeklySalesChart(weeklyData) {
        if (!weeklyData) weeklyData = [];

        // 요일별 데이터 준비 (월~일 순서)
        var dayMap = {};
        var dayOrder = ['월', '화', '수', '목', '금', '토', '일'];
        for (var i = 0; i < weeklyData.length; i++) {
            var dayNum = weeklyData[i].day_num;
            // PostgreSQL DOW: 0=일, 1=월, ..., 6=토
            // 차트 표시: 월(1), 화(2), 수(3), 목(4), 금(5), 토(6), 일(0)
            if (dayNum === 0) dayNum = 7; // 일요일을 마지막으로
            dayMap[dayNum] = weeklyData[i].sales_amount || 0;
        }

        var labels = [];
        var data = [];
        for (var d = 1; d <= 7; d++) {
            labels.push(dayOrder[d - 1] + '요일');
            data.push(dayMap[d] || 0);
        }

        var ctx = document.getElementById('weekly_sales_chart');
        if (!ctx) return;

        if (weeklySalesChart) {
            weeklySalesChart.destroy();
        }

        weeklySalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    backgroundColor: function(context) {
                        var dayIndex = context.dataIndex;
                        // 주말 강조 (토요일: 5, 일요일: 6)
                        if (dayIndex === 5 || dayIndex === 6) {
                            return 'rgba(255, 206, 86, 0.6)';
                        }
                        return 'rgba(75, 192, 192, 0.6)';
                    },
                    borderColor: function(context) {
                        var dayIndex = context.dataIndex;
                        if (dayIndex === 5 || dayIndex === 6) {
                            return 'rgba(255, 206, 86, 1)';
                        }
                        return 'rgba(75, 192, 192, 1)';
                    },
                    borderWidth: 1
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
                                return '매출: ' + formatCurrency(context.parsed.y || 0);
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

    // 가맹점별 매출 기여도 차트 (가로 막대)
    function renderShopContributionChart(shopData) {
        if (!shopData) shopData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < Math.min(shopData.length, 20); i++) {
            var shopName = shopData[i].shop_name || '미지정';
            if (shopName.length > 15) {
                shopName = shopName.substring(0, 15) + '...';
            }
            labels.push(shopName);
            data.push(shopData[i].sales_amount || 0);
        }

        var ctx = document.getElementById('shop_contribution_chart');
        if (!ctx) return;

        if (shopContributionChart) {
            shopContributionChart.destroy();
        }

        shopContributionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    backgroundColor: function(context) {
                        // 순위별 그라데이션
                        var index = context.dataIndex;
                        var max = 20;
                        var ratio = index / max;
                        var r = Math.floor(75 + (180 - 75) * ratio);
                        var g = Math.floor(192 - (192 - 99) * ratio);
                        var b = Math.floor(192 - (192 - 132) * ratio);
                        return 'rgba(' + r + ', ' + g + ', ' + b + ', 0.6)';
                    },
                    borderColor: function(context) {
                        var index = context.dataIndex;
                        var max = 20;
                        var ratio = index / max;
                        var r = Math.floor(75 + (180 - 75) * ratio);
                        var g = Math.floor(192 - (192 - 99) * ratio);
                        var b = Math.floor(192 - (192 - 132) * ratio);
                        return 'rgba(' + r + ', ' + g + ', ' + b + ', 1)';
                    },
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

    // 정산 처리 내역 표 렌더링
    function renderSettlementTable(settlementData) {
        if (!settlementData) settlementData = [];

        var $tbody = $('#settlement_table tbody');
        $tbody.empty();

        if (settlementData.length === 0) {
            $tbody.append('<tr><td colspan="5" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < settlementData.length; i++) {
            var item = settlementData[i];
            var settlementAt = item.settlement_at || '-';
            if (settlementAt !== '-') {
                var date = new Date(settlementAt);
                settlementAt = date.getFullYear() + '-' + 
                    String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(date.getDate()).padStart(2, '0') + ' ' +
                    String(date.getHours()).padStart(2, '0') + ':' + 
                    String(date.getMinutes()).padStart(2, '0');
            }
            
            var periodStart = item.settlement_start_at || '-';
            var periodEnd = item.settlement_end_at || '-';
            if (periodStart !== '-' && periodEnd !== '-') {
                var startDate = new Date(periodStart);
                var endDate = new Date(periodEnd);
                var periodStr = startDate.getFullYear() + '-' + 
                    String(startDate.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(startDate.getDate()).padStart(2, '0') + ' ~ ' +
                    endDate.getFullYear() + '-' + 
                    String(endDate.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(endDate.getDate()).padStart(2, '0');
            } else {
                var periodStr = '-';
            }
            
            // 정산 유형 한글 변환
            var settlementTypeKr = '-';
            if (item.settlement_type) {
                if (item.settlement_type.toUpperCase() === 'AUTO') {
                    settlementTypeKr = '자동';
                } else if (item.settlement_type.toUpperCase() === 'MANUAL') {
                    settlementTypeKr = '수동';
                } else {
                    settlementTypeKr = item.settlement_type;
                }
            }
            
            var row = '<tr>';
            row += '<td>' + settlementAt + '</td>';
            row += '<td>' + (item.shop_name || '-') + '</td>';
            row += '<td class="text-right">' + formatCurrency(item.settlement_amount || 0) + '</td>';
            row += '<td>' + periodStr + '</td>';
            row += '<td>' + settlementTypeKr + '</td>';
            row += '</tr>';
            $tbody.append(row);
        }
    }

    // 초기화
$(document).ready(function() {
    // 초기 데이터 로드
        loadSalesStatistics();
    
    // 조회 버튼 클릭 이벤트
    $('#search_btn').on('click', function() {
            loadSalesStatistics();
    });
});

})(jQuery);
</script>

