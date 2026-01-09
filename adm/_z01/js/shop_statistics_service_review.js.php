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
    var servicePopularityChart = null;
    var serviceSalesChart = null;
    var reviewTrendChart = null;
    var ratingTrendChart = null;
    var ratingDistributionChart = null;

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
            url: './ajax/shop_statistics_service_review_data.php',
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
            renderServicePopularityChart(res.service_popularity);
            renderServiceSalesChart(res.service_sales);
            renderReviewTrendChart(res.review_trend, periodType);
            renderRatingTrendChart(res.review_trend, periodType);
            renderRatingDistributionChart(res.rating_distribution);
            renderServiceDetailTable(res.service_details);
        }).fail(function(xhr, status, error) {
            StatisticsCommon.handleAjaxError(xhr, status, error);
        }).always(function() {
            $btn.prop('disabled', false).text('조회');
        });
    }

    function updateSummaryCards(summary) {
        if (!summary) summary = {};

        $('#total_services').text(formatNumber(summary.total_services || 0) + ' 개');
        $('#avg_service_price').text(formatCurrency(Math.floor(summary.avg_service_price || 0)));
        $('#total_service_sales').text(formatCurrency(Math.floor(summary.total_service_sales || 0)));
        $('#avg_rating').text((summary.avg_rating || 0).toFixed(2) + ' 점');
        $('#review_count').text(formatNumber(summary.review_count || 0) + ' 건');
        $('#avg_appointment_per_service').text((summary.avg_appointment_per_service || 0).toFixed(2) + ' 건');
    }

    function renderServicePopularityChart(serviceData) {
        var labels = [];
        var data = [];

        if (serviceData && serviceData.length) {
            for (var i = 0; i < serviceData.length; i++) {
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
                                return '예약 건수: ' + formatNumber(context.parsed.y || 0) + '건';
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

    function renderServiceSalesChart(serviceData) {
        var labels = [];
        var data = [];

        if (serviceData && serviceData.length) {
            for (var i = 0; i < serviceData.length; i++) {
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
                plugins: {
                    legend: { display: false },
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
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderReviewTrendChart(reviewTrend, periodType) {
        var labels = [];
        var data = [];

        if (reviewTrend && reviewTrend.length) {
            for (var i = 0; i < reviewTrend.length; i++) {
                var row = reviewTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
                data.push(row.review_count || 0);
            }
        }

        var ctx = document.getElementById('review_trend_chart').getContext('2d');
        if (reviewTrendChart) {
            reviewTrendChart.destroy();
        }

        reviewTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '리뷰 건수',
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

    function renderRatingTrendChart(reviewTrend, periodType) {
        var labels = [];
        var data = [];

        if (reviewTrend && reviewTrend.length) {
            for (var i = 0; i < reviewTrend.length; i++) {
                var row = reviewTrend[i];
                labels.push(formatDateLabel(row.date, periodType));
                data.push(row.avg_rating || 0);
            }
        }

        var ctx = document.getElementById('rating_trend_chart').getContext('2d');
        if (ratingTrendChart) {
            ratingTrendChart.destroy();
        }

        ratingTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '평균 평점',
                        data: data,
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
                                return '평균 평점: ' + (context.parsed.y || 0).toFixed(2) + '점';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1) + '점';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderRatingDistributionChart(ratingDistribution) {
        var labels = [];
        var data = [];

        if (ratingDistribution && ratingDistribution.length) {
            // 1점부터 5점까지 모든 평점 초기화
            var ratingMap = {};
            for (var i = 1; i <= 5; i++) {
                ratingMap[i] = 0;
            }

            // 데이터로 채우기
            for (var i = 0; i < ratingDistribution.length; i++) {
                var row = ratingDistribution[i];
                var rating = parseInt(row.rating) || 0;
                if (rating >= 1 && rating <= 5) {
                    ratingMap[rating] = row.review_count || 0;
                }
            }

            // 배열로 변환
            for (var i = 1; i <= 5; i++) {
                labels.push(i + '점');
                data.push(ratingMap[i]);
            }
        } else {
            // 데이터가 없어도 1~5점 표시
            for (var i = 1; i <= 5; i++) {
                labels.push(i + '점');
                data.push(0);
            }
        }

        var ctx = document.getElementById('rating_distribution_chart').getContext('2d');
        if (ratingDistributionChart) {
            ratingDistributionChart.destroy();
        }

        ratingDistributionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '리뷰 건수',
                    data: data,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
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
                                return '리뷰 건수: ' + formatNumber(context.parsed.y || 0) + '건';
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

    function renderServiceDetailTable(serviceDetails) {
        var $tbody = $('#service_detail_table tbody');
        $tbody.empty();

        if (!serviceDetails || !serviceDetails.length) {
            $tbody.append('<tr><td colspan="5" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        serviceDetails.forEach(function(row) {
            var tr = '<tr>' +
                '<td class="text-center">' + (row.service_name || '-') + '</td>' +
                '<td class="text-center">' + formatCurrency(row.service_price || 0) + '</td>' +
                '<td class="text-center">' + formatNumber(row.appointment_count || 0) + '건</td>' +
                '<td class="text-center">' + formatCurrency(row.total_sales || 0) + '</td>' +
                '<td class="text-center">' + formatCurrency(Math.floor(row.avg_sales_per_appointment || 0)) + '</td>' +
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

