<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
(function($) {
    var newExistingMemberChart = null;
    var memberStatusChart = null;
    var memberSignupTrendChart = null;
    var memberActivityChart = null;
    var memberReservationAmountChart = null;

    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '- 원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    function loadCustomerStatistics() {
        var periodType = $('#period_type').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if (!startDate || !endDate) {
            alert('조회 기간을 선택해 주세요.');
            return;
        }

        var $btn = $('#search_btn');
        $btn.prop('disabled', true).text('조회 중...');

        $.ajax({
            url: './ajax/platform_statistics_customer_data.php',
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

            var data = res.data || {};
            updateSummaryCards(data.summary);
            renderNewExistingMemberChart(data.new_existing_member_ratio);
            renderMemberStatusChart(data.member_status_distribution);
            renderMemberSignupTrendChart(data.member_signup_trend);
            renderMemberActivityChart(data.member_activity_distribution);
            renderMemberReservationAmountChart(data.member_reservation_amount_distribution);
            renderVipMemberTable(data.vip_member_list);
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

        $('#total_member_count').text(formatNumber(summary.total_member_count || 0));
        $('#new_member_count').text(formatNumber(summary.new_member_count || 0));
        $('#active_member_count').text(formatNumber(summary.active_member_count || 0));
        $('#leave_member_count').text(formatNumber(summary.leave_member_count || 0));
        $('#inactive_member_count').text(formatNumber(summary.inactive_member_count || 0));
        
        var activationRate = summary.activation_rate || 0;
        $('#activation_rate').text(activationRate.toFixed(1) + '%');
    }

    // 신규/기존 회원 비율 차트 (파이)
    function renderNewExistingMemberChart(ratioData) {
        if (!ratioData) ratioData = {};

        var labels = ['신규 회원', '기존 회원'];
        var data = [
            ratioData.new || 0,
            ratioData.existing || 0
        ];
        var colors = ['#3B82F6', '#9CA3AF'];

        var ctx = document.getElementById('new_existing_member_chart');
        if (!ctx) return;

        if (newExistingMemberChart) {
            newExistingMemberChart.destroy();
        }

        newExistingMemberChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors
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
                                return label + ': ' + formatNumber(value) + '명 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // 회원 상태별 분포 차트 (파이)
    function renderMemberStatusChart(statusData) {
        if (!statusData) statusData = {};

        var labels = ['정상 회원', '탈퇴 회원', '비활성 회원'];
        var data = [
            statusData.normal || 0,
            statusData.leave || 0,
            statusData.inactive || 0
        ];
        var colors = ['#4CAF50', '#9E9E9E', '#FFC107'];

        var ctx = document.getElementById('member_status_chart');
        if (!ctx) return;

        if (memberStatusChart) {
            memberStatusChart.destroy();
        }

        memberStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors
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
                                return label + ': ' + formatNumber(value) + '명 (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // 회원 가입 추이 차트 (선 그래프)
    function renderMemberSignupTrendChart(trendData) {
        if (!trendData) trendData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < trendData.length; i++) {
            labels.push(trendData[i].date || '');
            data.push(trendData[i].count || 0);
        }

        var ctx = document.getElementById('member_signup_trend_chart');
        if (!ctx) return;

        if (memberSignupTrendChart) {
            memberSignupTrendChart.destroy();
        }

        memberSignupTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '신규 가입 수',
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
                                return '신규 가입: ' + formatNumber(context.parsed.y || 0) + '명';
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

    // 회원 활성도 분포 차트 (가로 막대)
    function renderMemberActivityChart(activityData) {
        if (!activityData) activityData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < activityData.length; i++) {
            labels.push(activityData[i].period || '');
            data.push(activityData[i].count || 0);
        }

        var ctx = document.getElementById('member_activity_chart');
        if (!ctx) return;

        if (memberActivityChart) {
            memberActivityChart.destroy();
        }

        memberActivityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '회원 수',
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
                                return '회원 수: ' + formatNumber(context.parsed.x || 0) + '명';
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

    // 회원별 예약 금액 분포 차트 (가로 막대)
    function renderMemberReservationAmountChart(amountData) {
        if (!amountData) amountData = [];

        var labels = [];
        var data = [];

        for (var i = 0; i < amountData.length; i++) {
            labels.push((amountData[i].member_name || '') + ' (' + formatNumber(amountData[i].appointment_count || 0) + '건)');
            data.push(amountData[i].total_amount || 0);
        }

        var ctx = document.getElementById('member_reservation_amount_chart');
        if (!ctx) return;

        if (memberReservationAmountChart) {
            memberReservationAmountChart.destroy();
        }

        memberReservationAmountChart = new Chart(ctx, {
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
                                return '예약 금액: ' + formatCurrency(context.parsed.x || 0);
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

    // VIP 회원 목록 테이블 렌더링
    function renderVipMemberTable(vipList) {
        if (!vipList) vipList = [];

        var $tbody = $('#vip_member_list_table tbody');
        $tbody.empty();

        if (vipList.length === 0) {
            $tbody.append('<tr><td colspan="8" class="text-center text-gray-500">조회된 데이터가 없습니다.</td></tr>');
            return;
        }

        for (var i = 0; i < vipList.length; i++) {
            var item = vipList[i];
            var memberName = item.name || item.nickname || item.user_id || '-';
            var lastAppointmentDate = item.last_appointment_date ? item.last_appointment_date.split(' ')[0] : '-';
            var createdAt = item.created_at ? item.created_at.split(' ')[0] : '-';

            var row = '<tr>' +
                '<td>' + (item.user_id || item.customer_id || '-') + '</td>' +
                '<td>' + memberName + '</td>' +
                '<td class="text-right">' + formatCurrency(item.total_amount) + '</td>' +
                '<td class="text-right">' + formatNumber(item.appointment_count) + '건</td>' +
                '<td class="text-right">' + formatCurrency(item.avg_amount) + '</td>' +
                '<td>' + lastAppointmentDate + '</td>' +
                '<td>' + createdAt + '</td>' +
                '<td>' + (item.status || '정상') + '</td>' +
                '</tr>';
            $tbody.append(row);
        }
    }

    // 조회 버튼 이벤트
    $(document).on('click', '#search_btn', function() {
        loadCustomerStatistics();
    });

    // 기간 타입 변경 시 날짜 자동 조정 (선택사항)
    $(document).on('change', '#period_type', function() {
        var periodType = $(this).val();
        // 필요시 날짜 자동 조정 로직 추가
    });

    // 페이지 로드 시 초기 데이터 로드
    $(document).ready(function() {
        loadCustomerStatistics();
    });
})(jQuery);
</script>

