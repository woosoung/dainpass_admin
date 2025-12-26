<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    // 차트 인스턴스 저장
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

    // AJAX 데이터 로드
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
            renderReviewTrendChart(data.review_trend);
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

    // 주요 지표 카드 업데이트
    function updateSummaryCards(summary) {
        if (!summary) summary = {};

        $('#total_review_count').text(formatNumber(summary.total_review_count || 0) + '건');
        $('#avg_rating').text(formatRating(summary.avg_rating || 0));
        $('#review_rate').text(formatPercent(summary.review_rate || 0));
        $('#total_service_count').text(formatNumber(summary.total_service_count || 0) + '개');
        $('#avg_reservation_per_service').text(formatNumber(summary.avg_reservation_per_service || 0) + '건');
        $('#avg_sales_per_service').text(formatCurrency(summary.avg_sales_per_service || 0));
    }

    // 서비스별 예약 건수 순위 차트 (가로 막대)
    function renderServiceReservationRankChart(rankData) {
        if (!rankData) rankData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < Math.min(rankData.length, 20); i++) {
            var serviceName = rankData[i].service_name || '미지정';
            var shopName = rankData[i].shop_name || '';
            var label = serviceName;
            if (shopName) {
                label = shopName + ' - ' + serviceName;
            }
            if (label.length > 20) {
                label = label.substring(0, 20) + '...';
            }
            labels.push(label);
            data.push(rankData[i].reservation_count || 0);
        }

        var ctx = document.getElementById('service_reservation_rank_chart');
        if (!ctx) return;

        if (serviceReservationRankChart) {
            serviceReservationRankChart.destroy();
        }

        serviceReservationRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '예약 건수',
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

    // 서비스별 매출 순위 차트 (가로 막대)
    function renderServiceSalesRankChart(rankData) {
        if (!rankData) rankData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < Math.min(rankData.length, 20); i++) {
            var serviceName = rankData[i].service_name || '미지정';
            var shopName = rankData[i].shop_name || '';
            var label = serviceName;
            if (shopName) {
                label = shopName + ' - ' + serviceName;
            }
            if (label.length > 20) {
                label = label.substring(0, 20) + '...';
            }
            labels.push(label);
            data.push(rankData[i].sales_amount || 0);
        }

        var ctx = document.getElementById('service_sales_rank_chart');
        if (!ctx) return;

        if (serviceSalesRankChart) {
            serviceSalesRankChart.destroy();
        }

        serviceSalesRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '매출',
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

    // 업종별 평균 평점 차트 (막대)
    function renderCategoryRatingChart(categoryData) {
        if (!categoryData) categoryData = [];

        var labels = [];
        var data = [];
        var colors = [];

        for (var i = 0; i < categoryData.length; i++) {
            labels.push(categoryData[i].category_name || '미지정');
            var rating = categoryData[i].avg_rating || 0;
            data.push(rating);
            // 평점에 따라 색상 결정 (낮은 평점: 빨간색, 높은 평점: 녹색)
            if (rating >= 4.0) {
                colors.push('rgba(76, 175, 80, 0.6)'); // 녹색
            } else if (rating >= 3.0) {
                colors.push('rgba(255, 193, 7, 0.6)'); // 노란색
            } else {
                colors.push('rgba(244, 67, 54, 0.6)'); // 빨간색
            }
        }

        var ctx = document.getElementById('category_rating_chart');
        if (!ctx) return;

        if (categoryRatingChart) {
            categoryRatingChart.destroy();
        }

        categoryRatingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '평균 평점',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors.map(function(c) { return c.replace('0.6', '1'); }),
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
                                return '평균 평점: ' + formatRating(context.parsed.y || 0) + ' (리뷰 ' + formatNumber(categoryData[context.dataIndex].review_count || 0) + '개)';
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

    // 가맹점별 평균 평점 순위 차트 (가로 막대)
    function renderShopRatingRankChart(rankData) {
        if (!rankData) rankData = [];

        var labels = [];
        var data = [];
        var colors = [];

        for (var i = 0; i < Math.min(rankData.length, 20); i++) {
            var shopName = rankData[i].shop_name || '미지정';
            if (shopName.length > 15) {
                shopName = shopName.substring(0, 15) + '...';
            }
            labels.push(shopName);
            var rating = rankData[i].avg_rating || 0;
            data.push(rating);
            // 평점에 따라 색상 결정
            if (rating >= 4.0) {
                colors.push('rgba(76, 175, 80, 0.6)');
            } else if (rating >= 3.0) {
                colors.push('rgba(255, 193, 7, 0.6)');
            } else {
                colors.push('rgba(244, 67, 54, 0.6)');
            }
        }

        var ctx = document.getElementById('shop_rating_rank_chart');
        if (!ctx) return;

        if (shopRatingRankChart) {
            shopRatingRankChart.destroy();
        }

        shopRatingRankChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '평균 평점',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors.map(function(c) { return c.replace('0.6', '1'); }),
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
                                return '평균 평점: ' + formatRating(context.parsed.x || 0) + ' (리뷰 ' + formatNumber(rankData[context.dataIndex].review_count || 0) + '개)';
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
                                return value.toFixed(1) + '점';
                            }
                        }
                    }
                }
            }
        });
    }

    // 리뷰 평점 분포 차트 (파이)
    function renderRatingDistributionChart(ratingData) {
        if (!ratingData) ratingData = [];

        var labels = [];
        var data = [];
        var colors = ['#F44336', '#FF9800', '#FFC107', '#4CAF50', '#2196F3']; // 1점~5점 색상

        // 1점부터 5점까지 순서대로 정렬
        var ratingMap = {};
        for (var i = 0; i < ratingData.length; i++) {
            ratingMap[ratingData[i].rating] = ratingData[i].review_count;
        }

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
                    backgroundColor: colors.reverse(),
                    borderColor: colors.map(function(c) { return c; }),
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
                                return label + ': ' + formatNumber(value) + '개 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // 기간별 리뷰 작성 추이 차트 (선 그래프)
    function renderReviewTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            labels.push(trendData[i].date || '');
            data.push(trendData[i].count || 0);
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
                    label: '리뷰 작성 수',
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
                                return '리뷰 작성: ' + formatNumber(context.parsed.y || 0) + '건';
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

    // 서비스별 상세 통계 테이블 렌더링
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
            var row = '<tr>';
            row += '<td>' + (item.service_name || '-') + '</td>';
            row += '<td>' + (item.shop_name || '-') + '</td>';
            row += '<td>' + (item.category_names || '-') + '</td>';
            row += '<td class="text-right">' + formatNumber(item.reservation_count || 0) + '건</td>';
            row += '<td class="text-right">' + formatCurrency(item.total_sales || 0) + '</td>';
            row += '<td class="text-right">' + formatCurrency(item.avg_sales || 0) + '</td>';
            row += '<td class="text-right">' + formatRating(item.avg_rating || 0) + '</td>';
            row += '<td class="text-right">' + formatNumber(item.review_count || 0) + '개</td>';
            row += '</tr>';
            $tbody.append(row);
        }
    }

    // 데이터 내보내기 버튼 (TODO: 실제 구현 필요)
    $('#export_btn').on('click', function() {
        alert('데이터 내보내기 기능은 추후 구현 예정입니다.');
    });

    // 초기화
    $(document).ready(function() {
        // 초기 데이터 로드
        loadServiceReviewStatistics();

        // 조회 버튼 클릭 이벤트
        $('#search_btn').on('click', function() {
            loadServiceReviewStatistics();
        });
    });

})(jQuery);
</script>
