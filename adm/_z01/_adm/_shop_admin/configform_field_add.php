<?php 
if (!defined('_GNUBOARD_')) exit; //개별 페이지 접근 불가

// $fields = sql_field_names_pg($g5['dain_default_table']);
// print_r2($fields); // 디버깅용 출력
// exit;
// 최대일정날짜수
if (!isset($default2['de_schedule_max_days'])) {
    $fsql = " ALTER TABLE public.{$g5['dain_default_table']} ADD COLUMN de_schedule_max_days INTEGER DEFAULT 0 ";
    sql_query_pg($fsql);
}
// 일정날짜별 코스색상
if (!isset($default2['de_schedule_days_colors'])) {
    $fsql = " ALTER TABLE public.{$g5['dain_default_table']} ADD COLUMN de_schedule_days_colors TEXT DEFAULT '#0000ff' ";
    sql_query_pg($fsql);
}
// 최대일정업체수
if (!isset($default2['de_schedule_com_counts'])) {
    $fsql = " ALTER TABLE public.{$g5['dain_default_table']} ADD COLUMN de_schedule_com_counts INTEGER DEFAULT 0 ";
    sql_query_pg($fsql);
}
// 일정날짜별 코스색상
if (!isset($default2['de_schedule_com_colors'])) {
    $fsql = " ALTER TABLE public.{$g5['dain_default_table']} ADD COLUMN de_schedule_com_colors TEXT DEFAULT '#0000ff' ";
    sql_query_pg($fsql);
}