<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    var serviceReservationRankChart = null;
    var serviceSalesRankChart = null;
    var categoryRatingChart = null;
    var shopRatingRankChart = null;
    var ratingDistributionChart = null;
    var reviewTrendChart = null;

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

    function formatRating(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toFixed(1) + '점';
    }

    function loadServiceReviewStatistics() {
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
            url: './ajax/platform_statistics_service_review_data.php',
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
            renderServiceReservationRankChart(data.service_reservation_rank);
            renderServiceSalesRankChart(data.service_sales_rank);
            renderCategoryRatingChart(data.category_rating);
            renderShopRatingRankChart(data.shop_rating_rank);
            renderRatingDistributionChart(data.rating_distribution);
            renderReviewTrendChart(data.review_trend, periodType);
            renderServiceDetailTable(data.service_detail);
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

        $('#total_review_count').text(formatNumber(summary.total_review_count || 0) + '건');
        $('#avg_rating').text(formatRating(summary.avg_rating || 0));
        $('#review_rate').text(formatPercent(summary.review_rate || 0));
        $('#total_service_count').text(formatNumber(summary.total_service_count || 0) + '개');
        $('#avg_reservation_per_service').text(formatNumber(summary.avg_reservation_per_service || 0) + '건');
        $('#avg_sales_per_service').text(formatCurrency(summary.avg_sales_per_service || 0));
    }

    function renderServiceReservationRankChart(reservationData) {
        if (!reservationData) reservationData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < reservationData.length; i++) {
            var serviceName = reservationData[i].service_name || '미지정';
            var shopName = reservationData[i].shop_name || '미지정';
            labels.push(serviceName + ' (' + shopName + ')');
            data.push(reservationData[i].reservation_count || 0);
        }

        var ctx = document.getElementById('service_reservation_rank_chart');
        if (!ctx) return;

        if (serviceReservationRankChart) {
            serviceReservationRankChart.destroy();
        }

        // 그라데이션 색상 생성
        var colors = [];
        for (var i = 0; i < data.length; i++) {
            var ratio = data.length > 1 ? i / (data.length - 1) : 0;
            var r = Math.floor(54 + (255 - 54) * ratio);
            var g = Math.floor(162 + (99 - 162) * ratio);
            var b = Math.floor(235 + (102 - 235) * ratio);
            colors.push('rgb(' + r + ',' + g + ',' + b + ')');
        }

        serviceReservationRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors,
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

    function renderServiceSalesRankChart(salesData) {
        if (!salesData) salesData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < salesData.length; i++) {
            var serviceName = salesData[i].service_name || '미지정';
            var shopName = salesData[i].shop_name || '미지정';
            labels.push(serviceName + ' (' + shopName + ')');
            data.push(salesData[i].sales_amount || 0);
        }

        var ctx = document.getElementById('service_sales_rank_chart');
        if (!ctx) return;

        if (serviceSalesRankChart) {
            serviceSalesRankChart.destroy();
        }

        // 그라데이션 색상 생성
        var colors = [];
        for (var i = 0; i < data.length; i++) {
            var ratio = data.length > 1 ? i / (data.length - 1) : 0;
            var r = Math.floor(153 + (255 - 153) * ratio);
            var g = Math.floor(102 + (99 - 102) * ratio);
            var b = Math.floor(255 + (102 - 255) * ratio);
            colors.push('rgb(' + r + ',' + g + ',' + b + ')');
        }

        serviceSalesRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors,
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

    function renderCategoryRatingChart(categoryData) {
        if (!categoryData) categoryData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < categoryData.length; i++) {
            labels.push(categoryData[i].category_name || '미지정');
            data.push(categoryData[i].avg_rating || 0);
        }

        var ctx = document.getElementById('category_rating_chart');
        if (!ctx) return;

        if (categoryRatingChart) {
            categoryRatingChart.destroy();
        }

        // 평점별 그라데이션 색상 (낮은 평점: 빨간색, 높은 평점: 녹색)
        var colors = [];
        for (var i = 0; i < data.length; i++) {
            var rating = data[i];
            var ratio = rating / 5.0; // 0~1 사이 값
            var r = Math.floor(244 - (244 - 76) * ratio);
            var g = Math.floor(67 + (175 - 67) * ratio);
            var b = Math.floor(54 + (80 - 54) * ratio);
            colors.push('rgb(' + r + ',' + g + ',' + b + ')');
        }

        categoryRatingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '평균 평점',
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
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '평균 평점: ' + formatRating(context.parsed.y);
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
                                return formatRating(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderShopRatingRankChart(shopData) {
        if (!shopData) shopData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < shopData.length; i++) {
            labels.push(shopData[i].shop_name || '미지정');
            data.push(shopData[i].avg_rating || 0);
        }

        var ctx = document.getElementById('shop_rating_rank_chart');
        if (!ctx) return;

        if (shopRatingRankChart) {
            shopRatingRankChart.destroy();
        }

        // 평점별 그라데이션 색상
        var colors = [];
        for (var i = 0; i < data.length; i++) {
            var ratio = data.length > 1 ? i / (data.length - 1) : 0;
            var r = Math.floor(255 + (76 - 255) * ratio);
            var g = Math.floor(159 + (175 - 159) * ratio);
            var b = Math.floor(64 + (80 - 64) * ratio);
            colors.push('rgb(' + r + ',' + g + ',' + b + ')');
        }

        shopRatingRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '평균 평점',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors,
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
                                return '평균 평점: ' + formatRating(context.parsed.x);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            callback: function(value) {
                                return formatRating(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderRatingDistributionChart(ratingData) {
        if (!ratingData) ratingData = [];

        // 1점부터 5점까지 모든 평점 포함 (없는 평점은 0으로)
        var ratingMap = {};
        for (var i = 0; i < ratingData.length; i++) {
            ratingMap[ratingData[i].rating] = ratingData[i].review_count || 0;
        }

        var labels = [];
        var data = [];
        var colors = ['#F44336', '#FF9800', '#FFC107', '#4CAF50', '#2196F3'];

        for (var rating = 5; rating >= 1; rating--) {
            labels.push(rating + '점');
            data.push(ratingMap[rating] || 0);
        }

        var ctx = document.getElementById('rating_distribution_chart');
        if (!ctx) return;

        if (ratingDistributionChart) {
            ratingDistributionChart.destroy();
        }

        ratingDistributionChart = new Chart(ctx, {
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
                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + '건 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    function renderReviewTrendChart(trendData, periodType) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            var date = trendData[i].date || '';
            if (date) {
                // 날짜 포맷팅
                var dateObj = new Date(date);
                var formattedDate = '';
                if (periodType === 'monthly') {
                    formattedDate = dateObj.getFullYear() + '-' + String(dateObj.getMonth() + 1).padStart(2, '0');
                } else if (periodType === 'weekly') {
                    formattedDate = date;
                } else {
                    formattedDate = date;
                }
                labels.push(formattedDate);
                data.push(trendData[i].count || 0);
            }
        }

        var ctx = document.getElementById('review_trend_chart');
        if (!ctx) return;

        if (reviewTrendChart) {
            reviewTrendChart.destroy();
        }

        reviewTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '리뷰 작성 건수',
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
                                return '리뷰 작성: ' + formatNumber(context.parsed.y) + '건';
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

    function renderServiceDetailTable(detailList) {
        if (!detailList) detailList = [];

        var $tbody = $('#service_detail_table tbody');
        $tbody.empty();

        if (detailList.length === 0) {
            $tbody.append('<tr><td colspan="8" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < detailList.length; i++) {
            var item = detailList[i];
            var row = '<tr>' +
                '<td>' + (item.service_name || '-') + '</td>' +
                '<td>' + (item.shop_name || '-') + '</td>' +
                '<td>' + (item.category_names || '-') + '</td>' +
                '<td class="text-right">' + formatNumber(item.reservation_count) + '건</td>' +
                '<td class="text-right">' + formatCurrency(item.total_sales) + '</td>' +
                '<td class="text-right">' + formatCurrency(item.avg_sales) + '</td>' +
                '<td class="text-right">' + formatRating(item.avg_rating) + '</td>' +
                '<td class="text-right">' + formatNumber(item.review_count) + '건</td>' +
                '</tr>';
            $tbody.append(row);
        }
    }

    // 조회 버튼 이벤트
    $(document).on('click', '#search_btn', function() {
        loadServiceReviewStatistics();
    });

    // 페이지 로드 시 초기 데이터 로드
    $(document).ready(function() {
        loadServiceReviewStatistics();
    });
})(jQuery);
</script>

