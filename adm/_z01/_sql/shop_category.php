<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$cat_sql = " SELECT 
            s.category_id,
            CASE
                WHEN char_length(s.category_id) = 4
                    THEN p.name || ' > ' || s.name
                ELSE s.name
            END AS display_name
            FROM {$g5['shop_categories_table']} AS s
            LEFT JOIN {$g5['shop_categories_table']} AS p
            ON p.category_id = left(s.category_id, char_length(s.category_id) - 2)
            WHERE s.use_yn = 'Y'
            AND char_length(s.category_id) >= 2
            ORDER BY s.category_id ASC 
";
$cat_res = sql_query_pg($cat_sql);
$cats = array();
for($i=0;$row=sql_fetch_array_pg($cat_res->result);$i++) {
    if(mb_strlen($row['category_id']) == 2){
        $cats[$row['category_id']]['name'] = $row['display_name'];
        $cats[$row['category_id']]['mid'] = array();
    }
    else if(mb_strlen($row['category_id']) == 4){
        $cats[substr($row['category_id'], 0, 2)]['mid'][$row['category_id']] = $row['display_name'];
    }
}
// print_r2($cats);exit;
unset($cat_sql);
unset($cat_res);