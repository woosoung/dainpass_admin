<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
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

            updateSummaryCards(res.summary);
            renderCouponIssueUseTrendChart(res.coupon_issue_use_trend);
            
            // 할인 금액 추이 차트 렌더링 (디버깅용 로그 포함)
            // if (res.discount_amount_trend) {
            //     console.log('할인 금액 추이 데이터:', res.discount_amount_trend);
            //     console.log('할인 금액 추이 데이터 개수:', res.discount_amount_trend.length);
            //     if (res.discount_amount_trend.length > 0) {
            //         console.log('첫 번째 데이터 샘플:', res.discount_amount_trend[0]);
            //     }
            // } else {
            //     console.warn('할인 금액 추이 데이터가 없습니다. res 객체:', res);
            // }
            renderDiscountAmountTrendChart(res.discount_amount_trend);
            renderShopCouponIssueUseChart(res.shop_coupon_issue_use);
            renderCouponTypeDistributionChart(res.coupon_type_distribution);
            renderCouponUsageRateTrendChart(res.coupon_usage_rate_trend);
            renderCouponSalesContributionChart(res.coupon_sales_contribution);
            renderShopCouponDetailTable(res.shop_coupon_detail);
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
            // console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(summary) {
        if (!summary) {
            // console.warn('updateSummaryCards: summary 데이터가 없습니다.');
            summary = {};
        }

        // console.log('주요 지표 카드 데이터:', summary);
        // console.log('총 할인 금액:', summary.total_discount_amount);
        // console.log('예상 매출 증가액:', summary.estimated_sales_increase);

        $('#total_issued_count').text(formatNumber(summary.total_issued_count || 0) + '건');
        $('#total_used_count').text(formatNumber(summary.total_used_count || 0) + '건');
        $('#usage_rate').text(formatPercent(summary.usage_rate || 0));
        $('#total_discount_amount').text(formatCurrency(summary.total_discount_amount || 0));
        $('#avg_order_amount_with_coupon').text(formatCurrency(summary.avg_order_amount_with_coupon || 0));
        $('#estimated_sales_increase').text(formatCurrency(summary.estimated_sales_increase || 0));
    }

    function renderCouponIssueUseTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var issuedData = [];
        var usedData = [];

        for (var i = 0; i < trendData.length; i++) {
            var date = trendData[i].date || '';
            if (date) {
                labels.push(date);
                issuedData.push(trendData[i].issued_count || 0);
                usedData.push(trendData[i].used_count || 0);
            }
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
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: '사용 건수',
                    data: usedData,
                    borderColor: '#FF9F40',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
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
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.parsed.y) + '건';
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
                        title: {
                            display: true,
                            text: '사용 건수'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }

    function renderDiscountAmountTrendChart(trendData) {
        if (!trendData) {
            // console.warn('renderDiscountAmountTrendChart: trendData가 없습니다.');
            trendData = [];
        }

        var labels = [];
        var data = [];

        // console.log('renderDiscountAmountTrendChart 호출, 데이터 개수:', trendData.length);

        for (var i = 0; i < trendData.length; i++) {
            var dateValue = trendData[i].date;
            var discountAmount = parseFloat(trendData[i].discount_amount || 0);
            
            if (dateValue) {
                // 날짜 형식 처리 (YYYY-MM-DD 형식 유지)
                var dateStr = String(dateValue).substring(0, 10);
                labels.push(dateStr);
                data.push(discountAmount);
            } else {
                // console.warn('날짜 값이 없는 데이터:', trendData[i]);
            }
        }

        // console.log('차트 렌더링 준비 - labels:', labels.length, 'data:', data.length);
        // if (labels.length > 0) {
        //     console.log('첫 번째 레이블:', labels[0], '첫 번째 데이터:', data[0]);
        // }

        var ctx = document.getElementById('discount_amount_trend_chart');
        if (!ctx) {
            // console.error('discount_amount_trend_chart canvas element not found');
            return;
        }

        if (discountAmountTrendChart) {
            discountAmountTrendChart.destroy();
        }

        // 데이터가 없어도 차트는 렌더링 (빈 차트 표시)
        try {
            discountAmountTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '할인 금액',
                    data: data,
                    borderColor: '#F44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.1)',
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
                                return '할인 금액: ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
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
        } catch (error) {
            // console.error('할인 금액 추이 차트 렌더링 오류:', error);
            // console.error('차트 데이터:', { labels: labels, data: data });
        }
    }

    function renderShopCouponIssueUseChart(shopData) {
        if (!shopData) shopData = [];

        var labels = [];
        var issuedData = [];
        var usedData = [];

        for (var i = 0; i < shopData.length; i++) {
            labels.push(shopData[i].shop_name || '미지정');
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
                    label: '발급 건수',
                    data: issuedData,
                    backgroundColor: '#36A2EB',
                    borderColor: '#36A2EB',
                    borderWidth: 1
                }, {
                    label: '사용 건수',
                    data: usedData,
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
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.parsed.x) + '건';
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

    function renderCouponTypeDistributionChart(typeData) {
        if (!typeData) typeData = [];

        var labels = [];
        var data = [];

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
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                    borderColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
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

    function renderCouponUsageRateTrendChart(rateData) {
        if (!rateData) rateData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < rateData.length; i++) {
            var date = rateData[i].date || '';
            if (date) {
                labels.push(date);
                data.push(rateData[i].usage_rate || 0);
            }
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
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
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
                                return '사용률: ' + formatPercent(context.parsed.y);
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
                                return formatPercent(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCouponSalesContributionChart(contributionData) {
        if (!contributionData) contributionData = [];

        var labels = [];
        var totalSalesData = [];
        var discountData = [];
        var netSalesData = [];

        for (var i = 0; i < contributionData.length; i++) {
            labels.push(contributionData[i].shop_name || '미지정');
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
                    backgroundColor: '#36A2EB',
                    borderColor: '#36A2EB',
                    borderWidth: 1
                }, {
                    label: '할인 금액',
                    data: discountData,
                    backgroundColor: '#F44336',
                    borderColor: '#F44336',
                    borderWidth: 1
                }, {
                    label: '순 매출',
                    data: netSalesData,
                    backgroundColor: '#4CAF50',
                    borderColor: '#4CAF50',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.x);
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

    function renderShopCouponDetailTable(detailList) {
        if (!detailList) detailList = [];

        var $tbody = $('#shop_coupon_detail_table tbody');
        $tbody.empty();

        if (detailList.length === 0) {
            $tbody.append('<tr><td colspan="9" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < detailList.length; i++) {
            var item = detailList[i];

            var row = '<tr>' +
                '<td>' + (item.shop_name || '-') + '</td>' +
                '<td>' + (item.category_name || '-') + '</td>' +
                '<td class="text-right">' + formatNumber(item.issued_count || 0) + '건</td>' +
                '<td class="text-right">' + formatNumber(item.used_count || 0) + '건</td>' +
                '<td class="text-right">' + formatPercent(item.usage_rate || 0) + '</td>' +
                '<td class="text-right">' + formatCurrency(item.total_discount || 0) + '</td>' +
                '<td class="text-right">' + formatNumber(item.coupon_order_count || 0) + '건</td>' +
                '<td class="text-right">' + formatCurrency(item.total_sales_with_coupon || 0) + '</td>' +
                '<td class="text-right">' + formatCurrency(item.net_sales || 0) + '</td>' +
                '</tr>';
            $tbody.append(row);
        }
    }

    // 조회 버튼 이벤트
    $(document).on('click', '#search_btn', function() {
        loadCouponStatistics();
    });

    // 페이지 로드 시 초기 데이터 로드
    $(document).ready(function() {
        loadCouponStatistics();
    });
})(jQuery);
</script>

