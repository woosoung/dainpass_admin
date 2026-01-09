<?php
if (!defined('_GNUBOARD_')) exit;

// 공통 datepicker 함수 include
include_once(G5_Z_PATH . '/js/_common_datepicker.js.php');

// 공통 통계 유틸리티 함수 include
include_once(G5_Z_PATH . '/js/_common_statistics.js.php');
?>
<script>
(function($) {
    // 차트 인스턴스들
    var salesTrendChart = null;
    var appointmentTrendChart = null;
    var hourlyAppointmentChart = null;
    var weeklyAppointmentChart = null;
    var customerTypeChart = null;
    var servicePopularityChart = null;
    var serviceSalesChart = null;
    var customerAmountChart = null;
    var couponIssueUseTrendChart = null;
    var discountAmountTrendChart = null;

    // 공통 함수 별칭
    var formatNumber = StatisticsCommon.formatNumber;
    var formatCurrency = StatisticsCommon.formatCurrency;
    var formatDateLabel = StatisticsCommon.formatDateLabel;

    function loadDashboardData() {
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
            url: '../ajax/index_shop_mng_data.php',
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

            updateSummaryCards(res);
            
            // 차트 렌더링 (period_type 전달)
            var periodType = res.period_type || 'daily';
            renderSalesTrendChart(res.daily_sales, periodType);
            renderAppointmentTrendChart(res.daily_appointments, periodType);
            renderHourlyAppointmentChart(res.hourly_appointments);
            renderWeeklyAppointmentChart(res.weekly_appointments);
            renderCustomerTypeChart(res.customer_type_distribution);
            renderServicePopularityChart(res.service_popularity);
            renderServiceSalesChart(res.service_sales);
            renderCustomerAmountChart(res.top_customers);
            renderCouponIssueUseTrendChart(res.coupon_issue_use_trend, periodType);
            renderDiscountAmountTrendChart(res.discount_amount_trend, periodType);
            
            // 정산 처리 내역 테이블 업데이트
            renderSettlementTable(res.settlement_logs);
        }).fail(function(xhr, status, error) {
            StatisticsCommon.handleAjaxError(xhr, status, error);
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(res) {
        var sales = res.sales_summary || {};
        var reservation = res.reservation_summary || {};
        var customer = res.customer_summary || {};
        var service = res.service_summary || {};
        var coupon = res.coupon_summary || {};
        var settlementDeduction = res.settlement_deduction || {};

        // 매출/정산 통계
        $('#today_sales_amount').text(formatCurrency(sales.today_sales_amount || 0));
        $('#today_appointment_count').text(formatNumber(sales.today_appointment_count || 0));
        $('#month_sales_amount').text(formatCurrency(sales.month_sales_amount || 0));
        $('#month_appointment_count').text(formatNumber(sales.month_appointment_count || 0));
        $('#range_cancel_amount').text(formatCurrency(sales.range_cancel_amount || 0));
        $('#range_cancel_count').text(formatNumber(sales.range_cancel_count || 0));
        var cancelRate = typeof sales.range_cancel_rate !== 'undefined' ? sales.range_cancel_rate : 0;
        $('#range_cancel_rate').text(cancelRate.toFixed(1) + ' %');
        $('#settlement_net_amount').text(formatCurrency(settlementDeduction.total_settlement_amount || 0));
        $('#settlement_total_sales').text(formatCurrency(settlementDeduction.total_sales_amount || 0));

        // 예약/운영 통계
        $('#total_appointment_count').text(formatNumber(reservation.total_appointment_count || 0) + ' 건');
        $('#active_appointment_count').text(formatNumber(reservation.active_appointment_count || 0));
        $('#repeat_visit_rate').text((reservation.repeat_visit_rate || 0).toFixed(1) + ' %');
        $('#repeat_customer_count').text(formatNumber(reservation.repeat_customer_count || 0));
        $('#avg_appointment_per_customer').text((reservation.avg_appointment_per_customer || 0).toFixed(1) + ' 회');

        // 고객 통계
        $('#new_customer_count').text(formatNumber(customer.new_customer_count || 0) + ' 명');
        $('#existing_customer_count').text(formatNumber(customer.existing_customer_count || 0));
        // 평균 예약 금액: 소수점 이하 제거
        var avgAmount = customer.avg_amount_per_customer || 0;
        $('#avg_amount_per_customer').text(formatCurrency(Math.floor(avgAmount)));
        $('#avg_appointment_frequency').text((customer.avg_appointment_frequency || 0).toFixed(1) + ' 회');

        // 서비스/리뷰 통계
        $('#total_services').text(formatNumber(service.total_services || 0) + ' 개');
        $('#total_service_sales').text(formatCurrency(service.total_service_sales || 0));
        $('#avg_rating').text((service.avg_rating || 0).toFixed(1) + ' 점');
        $('#review_count').text(formatNumber(service.review_count || 0));
        $('#avg_appointment_per_service').text((service.avg_appointment_per_service || 0).toFixed(1) + ' 건');

        // 쿠폰 통계
        $('#total_coupon_issued').text(formatNumber(coupon.total_coupon_issued || 0) + ' 개');
        $('#total_coupon_used').text(formatNumber(coupon.total_coupon_used || 0));
        $('#coupon_usage_rate').text((coupon.coupon_usage_rate || 0).toFixed(1) + ' %');
    }

    function renderSalesTrendChart(dailySales, periodType) {
        var labels = [];
        var totalData = [];
        var netData = [];

        if (dailySales && dailySales.length) {
            for (var i = 0; i < dailySales.length; i++) {
                var row = dailySales[i];
                labels.push(formatDateLabel(row.date, periodType));
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
                        tension: 0.2
                    },
                    {
                        label: '순 매출',
                        data: netData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.2
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) label += ': ';
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

    function renderAppointmentTrendChart(dailyAppointments, periodType) {
        var labels = [];
        var totalData = [];
        var cancelData = [];

        if (dailyAppointments && dailyAppointments.length) {
            for (var i = 0; i < dailyAppointments.length; i++) {
                var row = dailyAppointments[i];
                labels.push(formatDateLabel(row.date, periodType));
                totalData.push(row.total_count || 0);
                cancelData.push(row.cancel_count || 0);
            }
        }

        var ctx = document.getElementById('appointment_trend_chart').getContext('2d');
        if (appointmentTrendChart) {
            appointmentTrendChart.destroy();
        }

        appointmentTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '전체 예약',
                        data: totalData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.2
                    },
                    {
                        label: '취소 예약',
                        data: cancelData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.2
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) label += ': ';
                                label += formatNumber(context.parsed.y || 0) + '건';
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

    function renderHourlyAppointmentChart(hourlyAppointments) {
        var labels = [];
        var data = [];

        if (hourlyAppointments && hourlyAppointments.length) {
            for (var i = 0; i < hourlyAppointments.length; i++) {
                var row = hourlyAppointments[i];
                labels.push(row.hour + '시');
                data.push(row.appointment_count || 0);
            }
        }

        var ctx = document.getElementById('hourly_appointment_chart').getContext('2d');
        if (hourlyAppointmentChart) {
            hourlyAppointmentChart.destroy();
        }

        hourlyAppointmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
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

    function renderWeeklyAppointmentChart(weeklyAppointments) {
        var labels = ['일', '월', '화', '수', '목', '금', '토'];
        var data = [0, 0, 0, 0, 0, 0, 0];

        if (weeklyAppointments && weeklyAppointments.length) {
            for (var i = 0; i < weeklyAppointments.length; i++) {
                var row = weeklyAppointments[i];
                var dayIndex = row.day_of_week || 0;
                if (dayIndex >= 0 && dayIndex < 7) {
                    data[dayIndex] = row.appointment_count || 0;
                }
            }
        }

        var ctx = document.getElementById('weekly_appointment_chart').getContext('2d');
        if (weeklyAppointmentChart) {
            weeklyAppointmentChart.destroy();
        }

        weeklyAppointmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
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

    function renderCustomerTypeChart(customerTypeDistribution) {
        var labels = [];
        var data = [];

        if (customerTypeDistribution && customerTypeDistribution.length) {
            for (var i = 0; i < customerTypeDistribution.length; i++) {
                var row = customerTypeDistribution[i];
                labels.push(row.type);
                data.push(row.count || 0);
            }
        }

        var ctx = document.getElementById('customer_type_chart').getContext('2d');
        if (customerTypeChart) {
            customerTypeChart.destroy();
        }

        customerTypeChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    function renderServicePopularityChart(serviceData) {
        var labels = [];
        var data = [];

        if (serviceData && serviceData.length) {
            var maxItems = Math.min(10, serviceData.length);
            for (var i = 0; i < maxItems; i++) {
                var row = serviceData[i];
                labels.push(row.service_name || '서비스 ' + row.service_id);
                data.push(row.appointment_count || 0);
            }
        }

        var ctx = document.getElementById('service_popularity_chart').getContext('2d');
        if (servicePopularityChart) {
            servicePopularityChart.destroy();
        }

        servicePopularityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: 'rgba(255, 206, 86, 0.6)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
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

    function renderServiceSalesChart(serviceData) {
        var labels = [];
        var data = [];

        if (serviceData && serviceData.length) {
            var maxItems = Math.min(10, serviceData.length);
            for (var i = 0; i < maxItems; i++) {
                var row = serviceData[i];
                labels.push(row.service_name || '서비스 ' + row.service_id);
                data.push(row.total_sales || 0);
            }
        }

        var ctx = document.getElementById('service_sales_chart').getContext('2d');
        if (serviceSalesChart) {
            serviceSalesChart.destroy();
        }

        serviceSalesChart = new Chart(ctx, {
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
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return formatCurrency(context.parsed.x || 0);
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

    function renderCustomerAmountChart(topCustomers) {
        var labels = [];
        var data = [];

        if (topCustomers && topCustomers.length) {
            var maxItems = Math.min(10, topCustomers.length);
            for (var i = 0; i < maxItems; i++) {
                var row = topCustomers[i];
                var customerName = row.customer_name || '고객';
                labels.push(customerName);
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
                    label: '예약 금액',
                    data: data,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return formatCurrency(context.parsed.x || 0);
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

    function renderCouponIssueUseTrendChart(couponTrend, periodType) {
        var labels = [];
        var issuedData = [];
        var usedData = [];

        if (couponTrend && couponTrend.length) {
            for (var i = 0; i < couponTrend.length; i++) {
                var row = couponTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
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
                        tension: 0.2
                    },
                    {
                        label: '사용',
                        data: usedData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.2
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: { position: 'top' }
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

    function renderDiscountAmountTrendChart(discountTrend, periodType) {
        var labels = [];
        var data = [];

        if (discountTrend && discountTrend.length) {
            for (var i = 0; i < discountTrend.length; i++) {
                var row = discountTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
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
                datasets: [{
                    label: '할인 금액',
                    data: data,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return formatCurrency(context.parsed.y || 0);
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

        var maxItems = Math.min(10, settlementLogs.length);
        for (var i = 0; i < maxItems; i++) {
            var row = settlementLogs[i];
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
        }
    }

    // 초기화
    $(document).ready(function() {
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

        $('#search_btn').on('click', loadDashboardData);

        // 페이지 로드 시 자동 조회
        loadDashboardData();
    });
})(jQuery);
</script>

