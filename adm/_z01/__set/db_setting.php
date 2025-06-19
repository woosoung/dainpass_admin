<?php
if(!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$db_tbl_sql = " CREATE TABLE setting (
  set_idx      SERIAL PRIMARY KEY,
  set_com_idx  INTEGER NOT NULL DEFAULT 0,
  set_trm_idx  INTEGER NOT NULL DEFAULT 0, -- 관리부서idx
  set_key      VARCHAR(50) NOT NULL DEFAULT 'tms',
  set_type     VARCHAR(50) NOT NULL DEFAULT '',
  set_name     VARCHAR(50),
  set_value    TEXT,
  set_auto_yn  BOOLEAN DEFAULT TRUE
); ";

sql_query_pg($db_tbl_sql);
unset($db_tbl_sql);