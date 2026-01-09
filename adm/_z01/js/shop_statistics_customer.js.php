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
    var customerTypeChart = null;
    var customerAmountChart = null;
    var appointmentFrequencyChart = null;
    var wishTrendChart = null;
    var wishConversionTrendChart = null;

    // 공통 함수 별칭
    var formatNumber = StatisticsCommon.formatNumber;
    var formatCurrency = StatisticsCommon.formatCurrency;
    var formatDateLabel = StatisticsCommon.formatDateLabel;

    function formatCurrencyInteger(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Math.floor(Number(num)).toLocaleString('ko-KR') + '원';
    }

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
            url: './ajax/shop_statistics_customer_data.php',
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
            renderCustomerTypeChart(res.customer_type_distribution);
            renderCustomerAmountChart(res.top_customers);
            renderAppointmentFrequencyChart(res.frequency_distribution);
            renderWishTrendChart(res.wish_trend);
            renderWishConversionTrendChart(res.wish_conversion_trend);
            renderVipCustomerTable(res.vip_customers);
        }).fail(function(xhr, status, error) {
            StatisticsCommon.handleAjaxError(xhr, status, error);
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(summary) {
        if (!summary) summary = {};

        $('#new_customer_count').text(formatNumber(summary.new_customer_count || 0) + ' 명');
        $('#new_customer_rate').text((summary.new_customer_rate || 0).toFixed(2));
        $('#existing_customer_count').text(formatNumber(summary.existing_customer_count || 0) + ' 명');
        $('#existing_customer_rate').text((summary.existing_customer_rate || 0).toFixed(2));
        $('#avg_amount_per_customer').text(formatCurrencyInteger(summary.avg_amount_per_customer || 0));
        $('#avg_appointment_frequency').text((summary.avg_appointment_frequency || 0).toFixed(2) + ' 회');
        $('#vip_customer_count').text(formatNumber(summary.vip_customer_count || 0) + ' 명');
        $('#wish_conversion_rate').text((summary.wish_conversion_rate || 0).toFixed(2) + ' %');
        $('#wish_count').text(formatNumber(summary.wish_count || 0));
    }

    function renderCustomerTypeChart(customerTypeDistribution) {
        var labels = [];
        var data = [];

        if (customerTypeDistribution && customerTypeDistribution.length) {
            for (var i = 0; i < customerTypeDistribution.length; i++) {
                var row = customerTypeDistribution[i];
                labels.push(row.type || '');
                data.push(row.count || 0);
            }
        }

        var ctx = document.getElementById('customer_type_chart').getContext('2d');
        if (customerTypeChart) {
            customerTypeChart.destroy();
        }

        var backgroundColors = [
            'rgba(54, 162, 235, 0.6)',
            'rgba(75, 192, 192, 0.6)'
        ];

        customerTypeChart = new Chart(ctx, {
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
                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + '명 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCustomerAmountChart(topCustomers) {
        var labels = [];
        var data = [];

        if (topCustomers && topCustomers.length) {
            for (var i = 0; i < topCustomers.length; i++) {
                var row = topCustomers[i];
                labels.push(row.customer_name || ('고객 ' + row.customer_id));
                data.push(row.total_amount || 0);
            }
        }

        var ctx = document.getElementById('customer_amount_chart').getContext('2d');
        if (customerAmountChart) {
            customerAmountChart.destroy();
        }

        customerAmountChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '누적 결제 금액',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '누적 결제 금액: ' + formatCurrency(context.parsed.y || 0);
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

    function renderAppointmentFrequencyChart(frequencyDistribution) {
        var labels = [];
        var data = [];

        if (frequencyDistribution && frequencyDistribution.length) {
            for (var i = 0; i < frequencyDistribution.length; i++) {
                var row = frequencyDistribution[i];
                labels.push(row.range || '');
                data.push(row.count || 0);
            }
        }

        var ctx = document.getElementById('appointment_frequency_chart').getContext('2d');
        if (appointmentFrequencyChart) {
            appointmentFrequencyChart.destroy();
        }

        appointmentFrequencyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '고객 수',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '고객 수: ' + formatNumber(context.parsed.y || 0) + '명';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value) + '명';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderWishTrendChart(wishTrend) {
        var labels = [];
        var data = [];

        if (wishTrend && wishTrend.length) {
            for (var i = 0; i < wishTrend.length; i++) {
                var row = wishTrend[i];
                labels.push(row.date);
                data.push(row.wish_count || 0);
            }
        }

        var ctx = document.getElementById('wish_trend_chart').getContext('2d');
        if (wishTrendChart) {
            wishTrendChart.destroy();
        }

        wishTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '찜 목록 추가 수',
                        data: data,
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
                                return context.dataset.label + ': ' + formatNumber(context.parsed.y || 0) + '건';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value) + '건';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderWishConversionTrendChart(wishConversionTrend) {
        var labels = [];
        var conversionRateData = [];

        if (wishConversionTrend && wishConversionTrend.length) {
            for (var i = 0; i < wishConversionTrend.length; i++) {
                var row = wishConversionTrend[i];
                labels.push(row.date);
                conversionRateData.push(row.conversion_rate || 0);
            }
        }

        var ctx = document.getElementById('wish_conversion_trend_chart').getContext('2d');
        if (wishConversionTrendChart) {
            wishConversionTrendChart.destroy();
        }

        wishConversionTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '찜 → 예약 전환률',
                        data: conversionRateData,
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.1)',
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
                                return '전환률: ' + (context.parsed.y || 0).toFixed(2) + '%';
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
                                return value.toFixed(1) + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderVipCustomerTable(vipCustomers) {
        var $tbody = $('#vip_customer_table tbody');
        $tbody.empty();

        if (!vipCustomers || !vipCustomers.length) {
            $tbody.append('<tr><td colspan="5" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        vipCustomers.forEach(function(row) {
            // user_id(nickname) 형식으로 표시
            var customerDisplay = row.customer_display || 
                (row.user_id ? (row.user_id + (row.nickname ? '(' + row.nickname + ')' : '')) : 
                ('고객 ' + row.customer_id));
            
            var tr = '<tr>' +
                '<td class="text-center">' + (row.rank || '-') + '</td>' +
                '<td class="text-center">' + customerDisplay + '</td>' +
                '<td class="text-center">' + formatNumber(row.appointment_count || 0) + '회</td>' +
                '<td class="text-center">' + formatCurrencyInteger(row.total_amount || 0) + '</td>' +
                '<td class="text-center">' + formatCurrencyInteger(row.avg_amount || 0) + '</td>' +
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

