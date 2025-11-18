<?php
include_once('./_common.php');
include_once(G5_ZSQL_PATH.'/set_conf.php');

/*
https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/shop/amenity_img/Frame+4588.png

SELECT * FROM dain_file 
    WHERE fle_db_tbl = 'amenities' -- 관련 테이블명
        AND fle_type = 'amnt' -- 파일을 구분하는 고정 타입
        AND fle_dir = 'shop/amenity_img' -- s3 버킷안에 data/shop/amenity_img 폴더
        AND fle_db_idx = '3' -- shop table index(amenity_id)
    ORDER BY fle_sort, fle_reg_dt DESC
LIMIT 1;


*/
$sa_sql = " SELECT  shm.shop_id, shp.name, shp.shop_name, shm.amenity_id, amn.amenity_name, amn.icon_url_enabled AS amn_icon
            FROM {$g5['shop_amenities_table']} shm
            LEFT JOIN {$g5['amenities_table']} amn ON shm.amenity_id = amn.amenity_id
            LEFT JOIN {$g5['shop_table']} shp ON shm.shop_id = shp.shop_id ";
// echo $sa_sql;exit;
$a_sql = " SELECT amenity_id
                , reverse(split_part(reverse(icon_url_enabled), '/', 1)) AS amn_icon
           FROM {$g5['amenities_table']} ";
// echo $a_sql;exit;
// $sa_res = sql_query_pg($a_sql);
/*
0 : 1 / Frame+4588.png
1 : 2 / Frame+4588+(1).png
2 : 3 / Frame+4588+(3).png
3 : 4 / icon.png
4 : 5 / Frame+4588+(5).png
5 : 6 / Frame+4588+(4).png
6 : 7 / Frame+4588+(2).png
7 : 8 / Frame+4588+(6).png
*/
$size_arr = [389, 525, 632, 530, 688, 630, 610, 461];
//24 * 28, image/png, ()
$sql = " INSERT INTO {$g5['dain_file_table']} (fle_mb_id, fle_db_tbl, fle_db_idx, fle_width, fle_height, fle_mime_type, fle_type, fle_size, fle_path, fle_name, fle_name_orig, fle_sort, fle_status, fle_reg_dt, fle_update_dt, fle_dir, fle_main_yn) VALUES ";
for($i=0; $row=sql_fetch_array_pg($sa_res->result); $i++){
    // echo $i.' : '.$row['amenity_id'].' / '.$row['amn_icon'].BR;
    $sql .= ($i > 0) ? ',' : '';
    $sql .= " ('admin', 'amenities', '{$row['amenity_id']}', 24, 28, 'image/png', 'amnt', '{$size_arr[$i]}', '/data/shop/amenity_img/{$row['amn_icon']}', '{$row['amn_icon']}', '{$row['amn_icon']}', {$i}, 'ok', NOW(), NOW(), 'shop/amenity_img', 'N') ";
}
// echo $sql;exit;