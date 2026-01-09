<?php
if (!defined('_GNUBOARD_')) exit;

// jQuery UI datepicker 플러그인
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 공통 datepicker 함수 include
include_once(G5_Z_PATH . '/js/_common_datepicker.js.php');

// 공통 통계 유틸리티 함수 include
include_once(G5_Z_PATH . '/js/_common_statistics.js.php');
?>
<script>
(function($) {
    var couponIssueUseTrendChart = null;
    var couponUsageRateChart = null;
    var discountAmountTrendChart = null;

    // 공통 함수 별칭
    var formatNumber = StatisticsCommon.formatNumber;
    var formatCurrency = StatisticsCommon.formatCurrency;
    var formatDateLabel = StatisticsCommon.formatDateLabel;

    function loadStatistics() {
        var periodType = $('#period_type').val();
        var startDate  = $('#start_date').val();
        var endDate    = $('#end_date').val();

        // 통계 파라미터 검증
        var validation = StatisticsCommon.validateStatisticsParams({
            periodType: periodType,
            startDate: startDate,
            endDate: endDate
        });

        if (!validation.valid) {
            alert(validation.message);
            return;
        }

        var $btn = $('#search_btn');
        $btn.prop('disabled', true).text('조회 중...');

        $.ajax({
            url: './ajax/shop_statistics_coupon_data.php',
            type: 'POST',
            dataType: 'json',
            data: {
                period_type: periodType,
                start_date: startDate,
                end_date: endDate
            }
        }).done(function(res) {
            if (!res || !res.success) {
                alert(res && res.message ? res.message : '데이터 조회에 실패했습니다.');
                return;
            }

            updateSummaryCards(res.summary);
            renderCouponIssueUseTrendChart(res.coupon_issue_use_trend);
            renderCouponUsageRateChart(res.coupon_usage_rate);
            renderDiscountAmountTrendChart(res.discount_amount_trend);
            renderCouponDetailTable(res.coupon_detail_statistics);
        }).fail(function(xhr, status, error) {
            StatisticsCommon.handleAjaxError(xhr, status, error);
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(summary) {
        if (!summary) summary = {};

        // 쿠폰 통계
        $('#total_coupon_issued').text(formatNumber(summary.total_coupon_issued || 0) + ' 개');
        $('#total_coupon_used').text(formatNumber(summary.total_coupon_used || 0) + ' 개');
        $('#coupon_usage_rate').text((summary.coupon_usage_rate || 0).toFixed(2) + ' %');
        $('#total_coupon_discount').text(formatCurrency(summary.total_coupon_discount || 0));
    }

    function renderCouponIssueUseTrendChart(couponTrend) {
        var labels = [];
        var issuedData = [];
        var usedData = [];

        if (couponTrend && couponTrend.length) {
            for (var i = 0; i < couponTrend.length; i++) {
                var row = couponTrend[i];
                labels.push(row.date);
                issuedData.push(row.issued_count || 0);
                usedData.push(row.used_count || 0);
            }
        }

        var ctx = document.getElementById('coupon_issue_use_trend_chart').getContext('2d');
        if (couponIssueUseTrendChart) {
            couponIssueUseTrendChart.destroy();
        }

        couponIssueUseTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '발급',
                        data: issuedData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.2,
                        yAxisID: 'y',
                    },
                    {
                        label: '사용',
                        data: usedData,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.1)',
                        tension: 0.2,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.parsed.y || 0) + '개';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value) + '개';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCouponUsageRateChart(couponUsageRate) {
        var labels = [];
        var data = [];

        if (couponUsageRate && couponUsageRate.length) {
            for (var i = 0; i < couponUsageRate.length; i++) {
                var row = couponUsageRate[i];
                labels.push(row.coupon_name || '쿠폰 ' + row.coupon_id);
                data.push(row.usage_rate || 0);
            }
        }

        var ctx = document.getElementById('coupon_usage_rate_chart').getContext('2d');
        if (couponUsageRateChart) {
            couponUsageRateChart.destroy();
        }

        couponUsageRateChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '사용률 (%)',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '사용률: ' + (context.parsed.x || 0).toFixed(2) + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
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

    function renderDiscountAmountTrendChart(discountTrend) {
        var labels = [];
        var data = [];

        if (discountTrend && discountTrend.length) {
            for (var i = 0; i < discountTrend.length; i++) {
                var row = discountTrend[i];
                labels.push(row.date);
                data.push(row.discount_amount || 0);
            }
        }

        var ctx = document.getElementById('discount_amount_trend_chart').getContext('2d');
        if (discountAmountTrendChart) {
            discountAmountTrendChart.destroy();
        }

        discountAmountTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '할인 금액',
                        data: data,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.2,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { position: 'top' },
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
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCouponDetailTable(couponDetails) {
        var $tbody = $('#coupon_detail_table tbody');
        $tbody.empty();

        if (!couponDetails || !couponDetails.length) {
            $tbody.append('<tr><td colspan="6" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        couponDetails.forEach(function(row) {
            var tr = '<tr>' +
                '<td class="text-center">' + (row.coupon_name || '-') + '</td>' +
                '<td class="text-center">' + (row.coupon_code || '-') + '</td>' +
                '<td class="text-center">' + formatNumber(row.issued_count || 0) + '개</td>' +
                '<td class="text-center">' + formatNumber(row.used_count || 0) + '개</td>' +
                '<td class="text-center">' + (row.usage_rate || 0).toFixed(2) + '%</td>' +
                '<td class="text-center">' + formatCurrency(row.total_discount_amount || 0) + '</td>' +
                '</tr>';
            $tbody.append(tr);
        });
    }

    $(function() {
        // Datepicker 초기화
        initializeStatisticsDatePicker({
            startInputId: 'start_date',
            endInputId: 'end_date'
        });

        // 빠른 선택 버튼 초기화
        initializeQuickSelectButtons({
            startInputId: 'start_date',
            endInputId: 'end_date',
            searchBtnId: 'search_btn',
            autoSearch: true
        });

        $('#search_btn').on('click', function() {
            loadStatistics();
        });

        // 최초 진입 시 자동 조회
        loadStatistics();
    });
})(jQuery);
</script>

