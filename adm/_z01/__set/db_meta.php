<?php
if(!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$db_tbl_sql = " CREATE TABLE meta (
    mta_idx        SERIAL PRIMARY KEY,
    mta_com_idx    INTEGER NOT NULL DEFAULT 0,
    mta_db_tbl     VARCHAR(20) NOT NULL,
    mta_db_idx     VARCHAR(20) DEFAULT '',
    mta_key        VARCHAR(20),
    mta_value      TEXT,
    mta_title      VARCHAR(20),
    mta_num        INTEGER NOT NULL DEFAULT 0,
    mta_status     VARCHAR(20) NOT NULL DEFAULT 'ok',
    mta_reg_dt     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mta_update_dt  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
); ";

sql_query_pg($db_tbl_sql);
unset($db_tbl_sql);