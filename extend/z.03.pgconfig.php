<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/*************************************************************************
PgSQL 관련 상수&함수 모음
*************************************************************************/

// PostgreSQL OR timescale DB connect
// define('G5_PGSQL_HOST', '61.83.89.15');
define('G5_PGSQL_HOST', 'dainpass-dev-pg-cluster-instance-1.cryaauiikrfz.ap-northeast-2.rds.amazonaws.com');//wsd_pgsql16
define('G5_PGSQL_USER', 'wsd');//wsd
define('G5_PGSQL_PASSWORD', 'wsd217');//wsd217
define('G5_PGSQL_DB', 'dainpass_db');//dainpass_db