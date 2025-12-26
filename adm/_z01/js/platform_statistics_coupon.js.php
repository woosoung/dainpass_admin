<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
    var couponIssueUseTrendChart = null;
    var discountAmountTrendChart = null;
    var shopCouponIssueUseChart = null;
    var couponTypeDistributionChart = null;
    var couponUsageRateTrendChart = null;
    var couponSalesContributionChart = null;

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    function formatPercent(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toFixed(1) + '%';
    }

    // AJAX 데이터 로드
    function loadCouponStatistics() {
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
            url: './ajax/platform_statistics_coupon_data.php',
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

            var data = res;
            
            updateSummaryCards(data.summary);
            renderCouponIssueUseTrendChart(data.coupon_issue_use_trend);
            renderDiscountAmountTrendChart(data.discount_amount_trend);
            renderShopCouponIssueUseChart(data.shop_coupon_issue_use);
            renderCouponTypeDistributionChart(data.coupon_type_distribution);
            renderCouponUsageRateTrendChart(data.coupon_usage_rate_trend);
            renderCouponSalesContributionChart(data.coupon_sales_contribution);
            renderShopCouponDetailTable(data.shop_coupon_detail);
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

        $('#total_issued_count').text(formatNumber(summary.total_issued_count || 0));
        $('#total_used_count').text(formatNumber(summary.total_used_count || 0));
        $('#usage_rate').text(formatPercent(summary.usage_rate || 0));
        $('#total_discount_amount').text(formatCurrency(summary.total_discount_amount || 0));
        $('#avg_order_amount_with_coupon').text(formatCurrency(summary.avg_order_amount_with_coupon || 0));
        $('#estimated_sales_increase').text(formatCurrency(summary.estimated_sales_increase || 0));
    }

    // 기간별 쿠폰 발급/사용 추이 차트 (이중 Y축)
    function renderCouponIssueUseTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var issuedData = [];
        var usedData = [];

        for (var i = 0; i < trendData.length; i++) {
            labels.push(trendData[i].date || '');
            issuedData.push(trendData[i].issued_count || 0);
            usedData.push(trendData[i].used_count || 0);
        }

        var ctx = document.getElementById('coupon_issue_use_trend_chart');
        if (!ctx) return;

        if (couponIssueUseTrendChart) {
            couponIssueUseTrendChart.destroy();
        }

        couponIssueUseTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '발급 건수',
                    data: issuedData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.2,
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: '사용 건수',
                    data: usedData,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    tension: 0.2,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.parsed.y || 0) + '건';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            }
                        },
                        title: {
                            display: true,
                            text: '발급 건수'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: '사용 건수'
                        }
                    }
                }
            }
        });
    }

    // 기간별 할인 금액 추이 차트
    function renderDiscountAmountTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            labels.push(trendData[i].date || '');
            data.push(trendData[i].discount_amount || 0);
        }

        var ctx = document.getElementById('discount_amount_trend_chart');
        if (!ctx) return;

        if (discountAmountTrendChart) {
            discountAmountTrendChart.destroy();
        }

        discountAmountTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '할인 금액',
                    data: data,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
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
                                return '할인 금액: ' + formatCurrency(context.parsed.y || 0);
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

    // 가맹점별 쿠폰 발급/사용 현황 차트 (그룹형 막대)
    function renderShopCouponIssueUseChart(shopData) {
        if (!shopData) shopData = [];

        var labels = [];
        var issuedData = [];
        var usedData = [];

        for (var i = 0; i < Math.min(shopData.length, 20); i++) {
            var shopName = shopData[i].shop_name || '미지정';
            if (shopName.length > 15) {
                shopName = shopName.substring(0, 15) + '...';
            }
            labels.push(shopName);
            issuedData.push(shopData[i].issued_count || 0);
            usedData.push(shopData[i].used_count || 0);
        }

        var ctx = document.getElementById('shop_coupon_issue_use_chart');
        if (!ctx) return;

        if (shopCouponIssueUseChart) {
            shopCouponIssueUseChart.destroy();
        }

        shopCouponIssueUseChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '발급',
                    data: issuedData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: '사용',
                    data: usedData,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.parsed.x || 0) + '건';
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

    // 쿠폰 타입별 분포 차트 (파이)
    function renderCouponTypeDistributionChart(typeData) {
        if (!typeData) typeData = [];

        var labels = [];
        var data = [];
        var colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

        for (var i = 0; i < typeData.length; i++) {
            labels.push(typeData[i].coupon_type || '기타');
            data.push(typeData[i].coupon_count || 0);
        }

        var ctx = document.getElementById('coupon_type_distribution_chart');
        if (!ctx) return;

        if (couponTypeDistributionChart) {
            couponTypeDistributionChart.destroy();
        }

        couponTypeDistributionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, data.length),
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
                                return label + ': ' + formatNumber(value) + '건 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // 쿠폰 사용률 추이 차트
    function renderCouponUsageRateTrendChart(rateData) {
        if (!rateData) rateData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < rateData.length; i++) {
            labels.push(rateData[i].date || '');
            data.push(rateData[i].usage_rate || 0);
        }

        var ctx = document.getElementById('coupon_usage_rate_trend_chart');
        if (!ctx) return;

        if (couponUsageRateTrendChart) {
            couponUsageRateTrendChart.destroy();
        }

        couponUsageRateTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '사용률',
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
                                return '사용률: ' + formatPercent(context.parsed.y || 0);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // 쿠폰 사용 시 매출 기여도 분석 차트 (그룹형 막대)
    function renderCouponSalesContributionChart(contributionData) {
        if (!contributionData) contributionData = [];

        var labels = [];
        var totalSalesData = [];
        var discountData = [];
        var netSalesData = [];

        for (var i = 0; i < Math.min(contributionData.length, 20); i++) {
            var shopName = contributionData[i].shop_name || '미지정';
            if (shopName.length > 15) {
                shopName = shopName.substring(0, 15) + '...';
            }
            labels.push(shopName);
            totalSalesData.push(contributionData[i].total_sales_with_coupon || 0);
            discountData.push(contributionData[i].total_discount || 0);
            netSalesData.push(contributionData[i].net_sales || 0);
        }

        var ctx = document.getElementById('coupon_sales_contribution_chart');
        if (!ctx) return;

        if (couponSalesContributionChart) {
            couponSalesContributionChart.destroy();
        }

        couponSalesContributionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '총 매출',
                    data: totalSalesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: '할인 금액',
                    data: discountData,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }, {
                    label: '순 매출',
                    data: netSalesData,
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
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.x || 0);
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

    // 가맹점별 쿠폰 마케팅 상세 통계 표 렌더링
    function renderShopCouponDetailTable(shopData) {
        if (!shopData) shopData = [];

        var $tbody = $('#shop_coupon_detail_table tbody');
        $tbody.empty();

        if (shopData.length === 0) {
            $tbody.append('<tr><td colspan="9" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < shopData.length; i++) {
            var item = shopData[i];
            var row = '<tr>' +
                '<td>' + (item.shop_name || '-') + '</td>' +
                '<td>' + (item.category_name || '-') + '</td>' +
                '<td class="text-right">' + formatNumber(item.issued_count || 0) + '</td>' +
                '<td class="text-right">' + formatNumber(item.used_count || 0) + '</td>' +
                '<td class="text-right">' + formatPercent(item.usage_rate || 0) + '</td>' +
                '<td class="text-right">' + formatCurrency(item.total_discount || 0) + '</td>' +
                '<td class="text-right">' + formatNumber(item.coupon_order_count || 0) + '</td>' +
                '<td class="text-right">' + formatCurrency(item.total_sales_with_coupon || 0) + '</td>' +
                '<td class="text-right">' + formatCurrency(item.net_sales || 0) + '</td>' +
                '</tr>';
            $tbody.append(row);
        }
    }

    // 초기화
    $(document).ready(function() {
        // 조회 버튼 클릭
        $('#search_btn').on('click', function() {
            loadCouponStatistics();
        });

        // 기간 타입 변경 시 날짜 필드 표시/숨김
        $('#period_type').on('change', function() {
            var periodType = $(this).val();
            if (periodType === 'custom') {
                $('#start_date, #end_date').show();
            } else {
                $('#start_date, #end_date').show();
            }
        });

        // 초기 데이터 로드
        loadCouponStatistics();
    });
})(jQuery);
</script>
