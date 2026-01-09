<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
/**
 * 통계 페이지 공통 유틸리티 함수들
 */
var StatisticsCommon = (function() {
    'use strict';

    /**
     * 숫자 포맷팅 (천 단위 콤마)
     */
    function formatNumber(num) {
        if (num === null || num === undefined || isNaN(num)) return '-';
        return Number(num).toLocaleString('ko-KR');
    }

    /**
     * 통화 포맷팅 (원화)
     */
    function formatCurrency(num) {
        if (num === null || num === undefined || isNaN(num)) return '0원';
        return Number(num).toLocaleString('ko-KR') + '원';
    }

    /**
     * 날짜 라벨 포맷팅 (차트용)
     * @param {string} dateStr - YYYY-MM-DD 형식 날짜 문자열
     * @param {string} periodType - 'daily', 'weekly', 'monthly'
     */
    function formatDateLabel(dateStr, periodType) {
        if (!dateStr) return '';
        var date = new Date(dateStr + 'T00:00:00');
        if (isNaN(date.getTime())) return dateStr;

        switch(periodType) {
            case 'weekly':
                var weekStart = new Date(date);
                weekStart.setDate(date.getDate() - date.getDay());
                var weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                return (weekStart.getMonth() + 1) + '/' + weekStart.getDate() + '~' + (weekEnd.getMonth() + 1) + '/' + weekEnd.getDate();
            case 'monthly':
                return (date.getMonth() + 1) + '월';
            case 'daily':
            default:
                return (date.getMonth() + 1) + '/' + date.getDate();
        }
    }

    /**
     * 날짜 유효성 검증
     * @param {string} dateStr - YYYY-MM-DD 형식 날짜 문자열
     * @returns {boolean} 유효 여부
     */
    function validateDate(dateStr) {
        // 날짜 형식 검증 (YYYY-MM-DD)
        var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(dateStr)) {
            return false;
        }

        // 날짜 유효성 검증
        var date = new Date(dateStr + 'T00:00:00');
        if (isNaN(date.getTime())) {
            return false;
        }

        // 날짜 범위 검증 (2년 전 연도의 1월 1일부터 오늘까지)
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var currentYear = today.getFullYear();
        var minYear = currentYear - 2;
        var minDate = new Date(minYear + '-01-01T00:00:00');

        if (date < minDate || date > today) {
            return false;
        }

        return true;
    }

    /**
     * 통계 조회 파라미터 검증
     * @param {object} params - {periodType, startDate, endDate}
     * @returns {object} {valid: boolean, message: string}
     */
    function validateStatisticsParams(params) {
        var periodType = params.periodType;
        var startDate = params.startDate;
        var endDate = params.endDate;

        // 기본 입력값 검증
        if (!startDate || !endDate) {
            return {valid: false, message: '조회 기간을 선택해 주세요.'};
        }

        // period_type 검증 (화이트리스트)
        var allowedPeriodTypes = ['daily', 'weekly', 'monthly'];
        if (allowedPeriodTypes.indexOf(periodType) === -1) {
            return {valid: false, message: '올바르지 않은 기간 타입입니다.'};
        }

        // 날짜 형식 및 유효성 검증
        if (!validateDate(startDate)) {
            return {valid: false, message: '시작일이 올바르지 않습니다.'};
        }

        if (!validateDate(endDate)) {
            return {valid: false, message: '종료일이 올바르지 않습니다.'};
        }

        // 날짜 순서 검증
        if (new Date(startDate) > new Date(endDate)) {
            return {valid: false, message: '시작일은 종료일보다 이전이어야 합니다.'};
        }

        // 조회 기간 제한 (최대 3년, 윤년 자동 고려)
        var start = new Date(startDate + 'T00:00:00');
        var end = new Date(endDate + 'T00:00:00');
        var maxEndDate = new Date(start);
        maxEndDate.setFullYear(start.getFullYear() + 3);

        if (end > maxEndDate) {
            return {valid: false, message: '조회 기간은 최대 3년까지 가능합니다.'};
        }

        return {valid: true, message: ''};
    }

    /**
     * AJAX 에러 메시지 파싱
     * @param {object} xhr - jQuery XMLHttpRequest 객체
     * @param {string} status - 에러 상태
     * @param {string} error - 에러 메시지
     * @returns {string} 파싱된 에러 메시지
     */
    function parseAjaxError(xhr, status, error) {
        var errorMsg = '서버 통신 중 오류가 발생했습니다.';

        // HTTP 상태 코드별 처리
        if (xhr.status === 401 || xhr.status === 403) {
            return '로그인 세션이 만료되었습니다.\n페이지를 새로고침한 후 다시 로그인해 주세요.';
        }

        if (xhr.status === 404) {
            return '요청한 리소스를 찾을 수 없습니다.\n페이지를 새로고침해 주세요.';
        }

        if (xhr.status === 500) {
            return '서버 내부 오류가 발생했습니다.\n잠시 후 다시 시도해 주세요.';
        }

        // 응답 내용 확인
        if (xhr.responseText) {
            // HTML 응답인지 확인 (로그인 페이지 등)
            var responseText = xhr.responseText.trim();
            if (responseText.toLowerCase().indexOf('<!doctype') === 0 ||
                responseText.toLowerCase().indexOf('<html') === 0) {
                return '로그인 세션이 만료되었습니다.\n페이지를 새로고침한 후 다시 로그인해 주세요.';
            }

            // JSON 응답 파싱 시도
            try {
                var errorRes = JSON.parse(responseText);
                if (errorRes && errorRes.message) {
                    errorMsg = errorRes.message;
                }
            } catch(e) {
                // JSON 파싱 실패 시 기본 메시지 사용
                // parseerror의 경우 상세 정보 제외
                if (status === 'parseerror') {
                    return '서버 응답 처리 중 오류가 발생했습니다.\n페이지를 새로고침해 주세요.';
                }
            }
        }

        // timeout 에러 처리
        if (status === 'timeout') {
            return '서버 응답 시간이 초과되었습니다.\n네트워크 연결을 확인하고 다시 시도해 주세요.';
        }

        // abort 에러 처리 (사용자가 요청 취소)
        if (status === 'abort') {
            return '요청이 취소되었습니다.';
        }

        return errorMsg;
    }

    /**
     * AJAX 에러 핸들러
     * @param {object} xhr - jQuery XMLHttpRequest 객체
     * @param {string} status - 에러 상태
     * @param {string} error - 에러 메시지
     */
    function handleAjaxError(xhr, status, error) {
        var errorMsg = parseAjaxError(xhr, status, error);

        // 사용자에게 친화적인 메시지 표시
        alert(errorMsg);

        <?php if (isset($member['mb_level']) && $member['mb_level'] >= 8) : ?>
        // 개발자를 위한 상세 로그 (mb_level 8 이상만)
        console.error('AJAX Error Details:', {
            status: xhr.status,
            statusText: xhr.statusText,
            errorType: status,
            errorMessage: error,
            responseText: xhr.responseText ? xhr.responseText.substring(0, 500) : null,
            url: xhr.responseURL || 'unknown'
        });
        <?php endif; ?>
    }

    // Public API
    return {
        formatNumber: formatNumber,
        formatCurrency: formatCurrency,
        formatDateLabel: formatDateLabel,
        validateDate: validateDate,
        validateStatisticsParams: validateStatisticsParams,
        parseAjaxError: parseAjaxError,
        handleAjaxError: handleAjaxError
    };
})();
</script>
