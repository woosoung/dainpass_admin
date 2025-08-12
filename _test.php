<?php
include_once('./_common.php');

/**
 * PostgreSQL 인용/이스케이프 유틸
 * - 식별자: "..." (pg_escape_identifier 사용 가능 시 사용)
 * - 리터럴: '...' (pg_escape_literal 사용 가능 시 사용)
 */
if (!function_exists('pg_quote_ident')) {
    function pg_quote_ident($ident, $conn = null) {
        if ($conn && function_exists('pg_escape_identifier')) {
            return pg_escape_identifier($conn, $ident);
        }
        return '"' . str_replace('"', '""', $ident) . '"';
    }
}

if (!function_exists('pg_quote_literal')) {
    function pg_quote_literal($val, $conn = null) {
        if ($val === null) return 'NULL';
        if (is_bool($val)) return $val ? 'TRUE' : 'FALSE';
        if (is_int($val) || is_float($val)) return (string)$val;
        if ($conn && function_exists('pg_escape_literal')) {
            return pg_escape_literal($conn, (string)$val);
        }
        return "'" . str_replace("'", "''", (string)$val) . "'";
    }
}

/**
 * information_schema.columns 의 column_default 정규화
 * - 'N'::character varying  →  N
 * - 'Y'::text               →  Y
 * - now() 같은 함수는 그대로 둠
 */
function normalize_column_default($default) {
    if ($default === null || $default === '') return null;
    $clean = trim($default);
    // 끝의 ::type 캐스트 제거
    $clean = preg_replace('/::[\w\s\[\]\.]+$/', '', $clean);
    // 양끝 작은따옴표 제거
    if (preg_match("/^'(.*)'$/s", $clean, $m)) {
        return $m[1];
    }
    return $clean; // 함수/표현식 그대로
}

/**
 * Aurora PostgreSQL 17 기준 컬럼 메타 조회
 */
$pg_sql = "
SELECT
    column_name,
    data_type,
    character_maximum_length,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_schema = 'public'
  AND table_name   = 'dain_default'
ORDER BY ordinal_position
";
$pg_result = sql_query_pg($pg_sql);

// 현재 운영값(원본 시스템) 1행 조회
$sql_current = "SELECT * FROM {$g5['g5_shop_default_table']} LIMIT 1";
$current = sql_fetch($sql_current); // 없으면 false/empty 배열 반환 가능

// PostgreSQL 연결 핸들(있으면 사용)
$pg_conn = isset($GLOBALS['pg_conn']) ? $GLOBALS['pg_conn'] : null;

$columns = [];
$values  = [];

/**
 * 값 결정 규칙:
 * 1) 특수 컬럼: de_lg_mid, de_lg_mert_key
 * 2) 기본값 'N' && 현재값 == 0 → 'N' 유지(기존 로직 계승)
 * 3) boolean/number/string/NULL 타입 정규화
 * 4) 문자 1자리 & 기본값이 'Y'/'N'인 컬럼은 입력값을 Y/N으로 강제(체크 제약 회피)
 */
for ($i = 0; $row = sql_fetch_array_pg($pg_result); $i++) {
    $col_name  = $row['column_name'];
    $data_type = strtolower($row['data_type']);
    $char_len  = $row['character_maximum_length']; // null 또는 정수
    $is_null   = strtoupper($row['is_nullable']) === 'YES';
    $col_def_raw = $row['column_default'];

    $col_default = normalize_column_default($col_def_raw); // 예: N / Y / now()

    // 원본 현재값
    $cur_val = (is_array($current) && array_key_exists($col_name, $current)) ? $current[$col_name] : null;

    // 1) 특수 컬럼 강제 값
    if ($col_name === 'de_lg_mid') {
        $final_val = 'si_dainpass217';
    } else if ($col_name === 'de_lg_mert_key') {
        $final_val = '95160cce09854ef44d2edb2bfb05f9f3';
    } else {
        // 2) 기존 규칙 유지: 기본값 'N'이고 현재값 0이면 'N'
        if ($col_default === 'N' && (string)$cur_val === '0') {
            $final_val = 'N';
        } else {
            $final_val = $cur_val;
        }
    }

    // 3) 타입 정규화
    $normalized = null;

    // 문자 1자리 & 기본값이 Y/N 인 컬럼 → Y/N 강제 (체크 제약 회피)
    $is_char_yn = (
        (strpos($data_type, 'character') !== false || strpos($data_type, 'text') !== false)
        && (int)$char_len === 1
        && ($col_default === 'Y' || $col_default === 'N')
    );

    if ($final_val === '' && $is_null) {
        $normalized = null;
    } else {
        if ($is_char_yn) {
            // 어떤 값이 오든 Y/N로 강제
            $v = strtoupper((string)$final_val);
            $normalized = ($v === 'Y') ? 'Y' : 'N';
        } else if (strpos($data_type, 'boolean') !== false) {
            if ($final_val === null) {
                $normalized = null;
            } else {
                $v = strtoupper(trim((string)$final_val));
                // 'Y','1','TRUE','T' → true, 나머지 → false
                $normalized = in_array($v, ['Y','1','TRUE','T'], true);
            }
        } else if (preg_match('/^(smallint|integer|bigint|numeric|real|double precision)$/', $data_type)) {
            if ($final_val === null || $final_val === '') {
                $normalized = $is_null ? null : 0;
            } else if (is_numeric($final_val)) {
                $normalized = (strpos((string)$final_val, '.') !== false) ? (float)$final_val : (int)$final_val;
            } else {
                // 숫자형인데 비숫자 → 기본값/NULL 정책
                $normalized = $is_null ? null : 0;
            }
        } else {
            // 그 외 문자/JSON 등은 문자열로
            $normalized = ($final_val === null ? null : (string)$final_val);
        }
    }

    $columns[] = pg_quote_ident($col_name, $pg_conn);
    $values[]  = pg_quote_literal($normalized, $pg_conn);
}

// INSERT 조립 & 실행
$cols_sql = implode(',', $columns);
$vals_sql = implode(',', $values);
$insert_sql = "INSERT INTO {$g5['dain_default_table']} ({$cols_sql}) VALUES ({$vals_sql})";

// 디버그시 주석 해제
// echo $insert_sql; exit;

// sql_query_pg($insert_sql);
