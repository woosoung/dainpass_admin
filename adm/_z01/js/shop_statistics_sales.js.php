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
    var salesTrendChart = null;
    var paymentMethodChart = null;
    var settlementTrendChart = null;

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
            url: './ajax/shop_statistics_sales_data.php',
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

            updateSummaryCards(res.summary, res.cancellations, res.settlement_deduction);
            updateDetailSummary(res.summary, res.cancellations, res.range_start, res.range_end);
            renderSalesTrendChart(res.daily_sales);
            renderPaymentMethodChart(res.payment_methods);
            renderSettlementTrendChart(res.settlement_chart);
            renderSettlementTable(res.settlement_logs);
        }).fail(function(xhr, status, error) {
            StatisticsCommon.handleAjaxError(xhr, status, error);
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(summary, cancellations, settlementDeduction) {
        if (!summary) summary = {};
        if (!cancellations) cancellations = {};
        if (!settlementDeduction) settlementDeduction = {};

        $('#today_sales_amount').text(formatCurrency(summary.today_sales_amount || 0));
        $('#today_appointment_count').text(formatNumber(summary.today_appointment_count || 0));
        $('#today_cancel_amount').text(formatCurrency(summary.today_cancel_amount || 0));

        $('#month_sales_amount').text(formatCurrency(summary.month_sales_amount || 0));
        $('#month_appointment_count').text(formatNumber(summary.month_appointment_count || 0));
        var monthRate = typeof summary.month_vs_prev_rate !== 'undefined' ? summary.month_vs_prev_rate : 0;
        $('#month_vs_prev_rate').text((monthRate >= 0 ? '+' : '') + monthRate.toFixed(1) + '%');

        $('#total_sales_amount').text(formatCurrency(summary.total_sales_amount || 0));
        $('#total_appointment_count').text(formatNumber(summary.total_appointment_count || 0));

        $('#range_cancel_amount').text(formatCurrency(summary.range_cancel_amount || 0));
        $('#range_cancel_count').text(formatNumber(summary.range_cancel_count || 0));
        var cancelRate = typeof summary.range_cancel_rate !== 'undefined' ? summary.range_cancel_rate : 0;
        $('#range_cancel_rate').text(cancelRate.toFixed(1) + '%');

        // 정산 순매출 및 차감액 표시
        $('#settlement_net_amount').text(formatCurrency(settlementDeduction.total_settlement_amount || 0));
        $('#settlement_total_sales').text(formatCurrency(settlementDeduction.total_sales_amount || 0));
        $('#settlement_deduction_amount').text(formatCurrency(settlementDeduction.deduction_amount || 0));
        var deductionRate = typeof settlementDeduction.deduction_rate !== 'undefined' ? settlementDeduction.deduction_rate : 0;
        $('#settlement_deduction_rate').text(deductionRate.toFixed(2));
    }

    function updateDetailSummary(summary, cancellations, rangeStart, rangeEnd) {
        if (!summary) summary = {};
        if (!cancellations) cancellations = {};

        var totalSales = summary.range_total_sales_amount || 0;
        var appointmentCount = summary.range_appointment_count || 0;
        var avgAmount = summary.range_avg_amount || 0;
        var cancelAmount = summary.range_cancel_amount || 0;
        var cancelRate = summary.range_cancel_rate || 0;

        var html = '';
        html += '<p>조회 기간: <strong>' + (rangeStart || '-') + '</strong> ~ <strong>' + (rangeEnd || '-') + '</strong></p>';
        html += '<p class="mt-1">';
        html += '총 매출액: <strong>' + formatCurrency(totalSales) + '</strong> · ';
        html += '예약건수: <strong>' + formatNumber(appointmentCount) + '건</strong> · ';
        html += '평균 객단가: <strong>' + formatCurrency(avgAmount.toFixed ? avgAmount.toFixed(0) : avgAmount) + '</strong><br>';
        html += '취소금액: <strong>' + formatCurrency(cancelAmount) + '</strong> · ';
        html += '취소율: <strong>' + cancelRate.toFixed(1) + '%</strong>';
        html += '</p>';

        $('#sales_detail_summary').html(html);
    }

    function renderSalesTrendChart(dailySales) {
        var labels = [];
        var totalData = [];
        var netData = [];

        if (dailySales && dailySales.length) {
            for (var i = 0; i < dailySales.length; i++) {
                var row = dailySales[i];
                labels.push(row.date);
                totalData.push(row.total_sales || 0);
                netData.push(row.net_sales || 0);
            }
        }

        var ctx = document.getElementById('sales_trend_chart').getContext('2d');
        if (salesTrendChart) {
            salesTrendChart.destroy();
        }

        salesTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '총 매출',
                        data: totalData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.2,
                        yAxisID: 'y',
                    },
                    {
                        label: '순 매출',
                        data: netData,
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
                stacked: false,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += formatCurrency(context.parsed.y || 0);
                                return label;
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

    function renderSettlementTrendChart(settlementChartData) {
        var labels = [];
        var amounts = [];

        if (settlementChartData && settlementChartData.length) {
            for (var i = 0; i < settlementChartData.length; i++) {
                var row = settlementChartData[i];
                // month 또는 settlement_date 기준 라벨
                labels.push(row.month || row.settlement_date || row.settlement_at);
                amounts.push(row.total_amount || row.settlement_amount || 0);
            }
        }

        var ctx = document.getElementById('settlement_trend_chart').getContext('2d');
        if (settlementTrendChart) {
            settlementTrendChart.destroy();
        }

        settlementTrendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '정산 금액',
                        data: amounts,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '정산 금액: ' + formatCurrency(context.parsed.y || 0);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return formatNumber(value); }
                        }
                    }
                }
            }
        });
    }

    function getSettlementStatusText(status) {
        if (!status) return '-';
        var statusMap = {
            'done': '완료',
            'pending': '대기',
            'failed': '실패'
        };
        return statusMap[status.toLowerCase()] || status;
    }

    function renderSettlementTable(settlementLogs) {
        var $tbody = $('#settlement_table tbody');
        $tbody.empty();

        if (!settlementLogs || !settlementLogs.length) {
            $tbody.append('<tr><td colspan="4" class="text-center text-gray-500">조회된 정산 내역이 없습니다.</td></tr>');
            return;
        }

        settlementLogs.forEach(function(row) {
            var periodText = '';
            if (row.settlement_start_at || row.settlement_end_at) {
                periodText = (row.settlement_start_at || '-') + ' ~ ' + (row.settlement_end_at || '-');
            }

            var tr = '<tr>' +
                '<td class="text-center">' + (row.settlement_date || row.settlement_at || '-') + '</td>' +
                '<td class="text-center">' + (periodText || '-') + '</td>' +
                '<td class="pr-2 text-right">' + formatCurrency(row.settlement_amount || 0) + '</td>' +
                '<td class="text-center">' + getSettlementStatusText(row.status) + '</td>' +
                '</tr>';
            $tbody.append(tr);
        });
    }

    function renderPaymentMethodChart(paymentMethods) {
        var labels = [];
        var data = [];

        if (paymentMethods && paymentMethods.length) {
            for (var i = 0; i < paymentMethods.length; i++) {
                var row = paymentMethods[i];
                var label = row.payment_method || '기타';
                label += ' (' + (row.ratio !== undefined ? row.ratio.toFixed(1) : '0.0') + '%)';
                labels.push(label);
                data.push(row.total_amount || 0);
            }
        }

        var ctx = document.getElementById('payment_method_chart').getContext('2d');
        if (paymentMethodChart) {
            paymentMethodChart.destroy();
        }

        var backgroundColors = [
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 99, 132, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)'
        ];

        paymentMethodChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                return label + ': ' + formatCurrency(value);
                            }
                        }
                    }
                }
            }
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