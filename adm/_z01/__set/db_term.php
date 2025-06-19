<?php
if(!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$db_tbl_sql = " CREATE TABLE term (
    trm_idx         SERIAL PRIMARY KEY,
    trm_idx_parent  INTEGER DEFAULT 0,
    trm_name        VARCHAR(255) DEFAULT '',
    trm_name2       VARCHAR(255) DEFAULT '',
    trm_desc        TEXT,
    trm_category    VARCHAR(50) NOT NULL DEFAULT '',
    trm_sort        INTEGER NOT NULL DEFAULT 0,
    trm_type        VARCHAR(50) DEFAULT '',
    trm_left        INTEGER NOT NULL DEFAULT 0,
    trm_right       INTEGER NOT NULL DEFAULT 0,
    trm_status      VARCHAR(50) DEFAULT 'pending',
    trm_reg_dt      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
); ";

sql_query_pg($db_tbl_sql);
unset($db_tbl_sql);