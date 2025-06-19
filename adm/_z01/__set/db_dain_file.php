<?php
if(!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$db_tbl_sql = " CREATE TABLE dain_file (
  fle_idx        SERIAL PRIMARY KEY,
  fle_mb_id      VARCHAR(20) NOT NULL DEFAULT '',
  fle_db_tbl     VARCHAR(20) NOT NULL DEFAULT '',
  fle_db_idx     VARCHAR(20) NOT NULL DEFAULT '',
  fle_width      INTEGER DEFAULT 0,
  fle_height     INTEGER DEFAULT 0,
  fle_desc       TEXT DEFAULT '',
  fle_mime_type  VARCHAR(100) DEFAULT '',
  fle_type       VARCHAR(50) NOT NULL DEFAULT '',
  fle_size       INTEGER NOT NULL DEFAULT 0,
  fle_path       VARCHAR(255) DEFAULT '',
  fle_name       VARCHAR(255) NOT NULL DEFAULT '',
  fle_name_orig  VARCHAR(255) NOT NULL DEFAULT '',
  fle_sort       INTEGER NOT NULL DEFAULT 0,
  fle_status     VARCHAR(20) NOT NULL DEFAULT 'ok',
  fle_reg_dt     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fle_update_dt  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); ";

sql_query_pg($db_tbl_sql);
unset($db_tbl_sql);