<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$set_key = 'dain';
$set_type = 'com';
$set_sql = " SELECT * FROM {$g5['setting_table']} 
                    WHERE set_key = '{$set_key}'
                        AND set_type = '{$set_type}' ";
$set_res = sql_query_pg($set_sql);

for($i=0;$row=sql_fetch_array_pg($set_res);$i++){
    ${'set_'.$row['set_type']}[$row['set_name']]['set_idx'] = $row['set_idx'];
    ${'set_'.$row['set_type']}[$row['set_name']] = $row['set_value'];
    // echo $row['set_name'].'='.$row['set_value'].'<br>';continue;
    if(preg_match("/(_subject|_content|_title|_ttl|_desc|_description)$/",$row['set_name']) ) continue;
    if(!preg_match("/,/",${'set_'.$row['set_type']}[$row['set_name']])) continue;
    // echo ${'set_'.$row['set_type']}[$row['set_name']].'<br>';continue;
    // A=B 형태를 가지고 있으면 자동 할당
    $set_values = (${'set_'.$row['set_type']}[$row['set_name']]) ? explode(',', ${'set_'.$row['set_type']}[$row['set_name']]) : array();
    
    ${'set_'.$row['set_type']}[$row['set_name'].'_arr'] = $set_values;
    if(preg_match("/=/",${'set_'.$row['set_type']}[$row['set_name']])){
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] = '<p>$set_'.$row['set_type'].'['.$row['set_name'].']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'['.$row['set_name'].'_karr][key]</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'['.$row['set_name'].'_varr][value]</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'['.$row['set_name'].'_arrk]</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'['.$row['set_name'].'_arrv]</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'['.$row['set_name'].'_radio]</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'['.$row['set_name'].'_check]</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'['.$row['set_name'].'_option]</p>'.PHP_EOL;
    }
    else {
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] = '<p>$set_'.$row['set_type'].'['.$row['set_name'].']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'['.$row['set_name'].'_arr][key]</p>'.PHP_EOL;
    }
    
    foreach($set_values as $set_value){
        //변수가 (,),(=)로 구분되어 있을때
        if(preg_match("/=/",$set_value)){
            $comma_equal = 1;
            list($key, $value) = explode('=',$set_value);
            ${'set_'.$row['set_type']}[$row['set_name'].'_karr'][$key] = $value;
            ${'set_'.$row['set_type']}[$row['set_name'].'_varr'][$value] = $key;
            ${'set_'.$row['set_type']}[$row['set_name'].'_arrk'][] = $key;
            ${'set_'.$row['set_type']}[$row['set_name'].'_arrv'][] = $value;
            ${'set_'.$row['set_type']}[$row['set_name'].'_radio'] .= '<label for="'.$row['set_name'].'_'.$key.'" class="'.$row['set_name'].'"><input type="radio" id="'.$row['set_name'].'_'.$key.'" name="'.$row['set_name'].'" value="'.$key.'">'.$value.'</label>';
            ${'set_'.$row['set_type']}[$row['set_name'].'_check'] .= '<label for="'.$row['set_name'].'_'.$key.'"><input type="checkbox" id="'.$row['set_name'].'_'.$key.'" class="'.$row['set_name'].'_chk" name="'.$row['set_name'].'['.$key.']" key="'.$key.'" value="1">'.$value.'</label>';
            ${'set_'.$row['set_type']}[$row['set_name'].'_option'] .= '<option value="'.trim($key).'">'.trim($value).'</option>';
        }
    }
}


//준비중파일 추출 ##############################################################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
        WHERE fle_db_tbl = 'set' 
            AND fle_type = '{$set_type}' 
            AND fle_db_idx = 'preparing' 
        ORDER BY fle_reg_dt DESC 
            LIMIT 1 ";
$rs = sql_fetch_pg($sql);

${'set_'.$set_type}['preparing_img'] = ($rs && is_array($rs)) ? '<img src="'.G5_DATA_URL.$rs['fle_path'].'/'.$rs['fle_name'].'" alt="'.$rs['fle_name_orig'].'">': '';
${'set_'.$set_type}['preparing_url'] = ($rs && is_array($rs)) ? G5_DATA_URL.$rs['fle_path'].'/'.$rs['fle_name'] : '';
${'set_'.$set_type}['preparing_path'] = ($rs && is_array($rs)) ? G5_DATA_PATH.$rs['fle_path'] : '';
${'set_'.$set_type}['preparing_name'] = ($rs && is_array($rs)) ? $rs['fle_name'] : '';
${'set_'.$set_type}['preparing_name_orig'] = ($rs && is_array($rs)) ? $rs['fle_name_orig'] : '';

${'set_'.$set_type}['preparing_str'] = '<p>$set_'.$set_type.'[\'preparing_img\']</p>'.PHP_EOL;
${'set_'.$set_type}['preparing_str'] .= '<p>$set_'.$set_type.'[\'preparing_url\']</p>'.PHP_EOL;
${'set_'.$set_type}['preparing_str'] .= '<p>$set_'.$set_type.'[\'preparing_path\']</p>'.PHP_EOL;
${'set_'.$set_type}['preparing_str'] .= '<p>$set_'.$set_type.'[\'preparing_name\']</p>'.PHP_EOL;
${'set_'.$set_type}['preparing_str'] .= '<p>$set_'.$set_type.'[\'preparing_name_orig\']</p>'.PHP_EOL;

//favicon 파일 추출 ##############################################################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
        WHERE fle_db_tbl = 'set' 
            AND fle_type = '{$set_type}' 
            AND fle_db_idx = 'favicon' 
        ORDER BY fle_reg_dt DESC 
            LIMIT 1 ";
$rs = sql_fetch_pg($sql);

${'set_'.$set_type}['favicon_img'] = ($rs && is_array($rs)) ? '<img src="'.G5_DATA_URL.$rs['fle_path'].'/'.$rs['fle_name'].'" alt="'.$rs['fle_name_orig'].'">' : '';
${'set_'.$set_type}['favicon_url'] = ($rs && is_array($rs)) ? G5_DATA_URL.$rs['fle_path'].'/'.$rs['fle_name'] : '';
${'set_'.$set_type}['favicon_path'] = ($rs && is_array($rs)) ? G5_DATA_PATH.$rs['fle_path'] : '';
${'set_'.$set_type}['favicon_name'] = ($rs && is_array($rs)) ? $rs['fle_name'] : '';
${'set_'.$set_type}['favicon_name_orig'] = ($rs && is_array($rs)) ? $rs['fle_name_orig'] : '';

${'set_'.$set_type}['favicon_str'] = '<p>$set_'.$set_type.'[\'favicon_img\']</p>'.PHP_EOL;
${'set_'.$set_type}['favicon_str'] .= '<p>$set_'.$set_type.'[\'favicon_url\']</p>'.PHP_EOL;
${'set_'.$set_type}['favicon_str'] .= '<p>$set_'.$set_type.'[\'favicon_path\']</p>'.PHP_EOL;
${'set_'.$set_type}['favicon_str'] .= '<p>$set_'.$set_type.'[\'favicon_name\']</p>'.PHP_EOL;
${'set_'.$set_type}['favicon_str'] .= '<p>$set_'.$set_type.'[\'favicon_name_orig\']</p>'.PHP_EOL;

//og_img 파일 추출 ##############################################################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
        WHERE fle_db_tbl = 'set' 
            AND fle_type = '{$set_type}' 
            AND fle_db_idx = 'ogimg' 
        ORDER BY fle_reg_dt DESC 
            LIMIT 1 ";
$rs = sql_fetch_pg($sql);

${'set_'.$set_type}['ogimg_img'] = ($rs && is_array($rs)) ? '<img src="'.G5_DATA_URL.$rs['fle_path'].'/'.$rs['fle_name'].'" alt="'.$rs['fle_name_orig'].'">' : '';
${'set_'.$set_type}['ogimg_url'] = ($rs && is_array($rs)) ? G5_DATA_URL.$rs['fle_path'].'/'.$rs['fle_name'] : '';
${'set_'.$set_type}['ogimg_path'] = ($rs && is_array($rs)) ? G5_DATA_PATH.$rs['fle_path'] : '';
${'set_'.$set_type}['ogimg_name'] = ($rs && is_array($rs)) ? $rs['fle_name'] : '';
${'set_'.$set_type}['ogimg_name_orig'] = ($rs && is_array($rs)) ? $rs['fle_name_orig'] : '';

${'set_'.$set_type}['ogimg_str'] = '<p>$set_'.$set_type.'[\'ogimg_img\']</p>'.PHP_EOL;
${'set_'.$set_type}['ogimg_str'] .= '<p>$set_'.$set_type.'[\'ogimg_url\']</p>'.PHP_EOL;
${'set_'.$set_type}['ogimg_str'] .= '<p>$set_'.$set_type.'[\'ogimg_path\']</p>'.PHP_EOL;
${'set_'.$set_type}['ogimg_str'] .= '<p>$set_'.$set_type.'[\'ogimg_name\']</p>'.PHP_EOL;
${'set_'.$set_type}['ogimg_str'] .= '<p>$set_'.$set_type.'[\'ogimg_name_orig\']</p>'.PHP_EOL;

//sitemap 파일 추출 ##############################################################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
        WHERE fle_db_tbl = 'set' 
            AND fle_type = '{$set_type}' 
            AND fle_db_idx = 'sitemap' 
        ORDER BY fle_reg_dt DESC 
            LIMIT 1 ";
$rs = sql_fetch_pg($sql);

${'set_'.$set_type}['sitemap_url'] = ($rs && is_array($rs)) ? G5_DATA_URL.$rs['fle_path'].'/'.$rs['fle_name'] : '';
${'set_'.$set_type}['sitemap_path'] = ($rs && is_array($rs)) ? G5_DATA_PATH.$rs['fle_path'] : '';
${'set_'.$set_type}['sitemap_name'] = ($rs && is_array($rs)) ? $rs['fle_name'] : '';
${'set_'.$set_type}['sitemap_name_orig'] = ($rs && is_array($rs)) ? $rs['fle_name_orig'] : '';

${'set_'.$set_type}['sitemap_str'] = '<p>$set_'.$set_type.'[\'sitemap_url\']</p>'.PHP_EOL;
${'set_'.$set_type}['sitemap_str'] .= '<p>$set_'.$set_type.'[\'sitemap_path\']</p>'.PHP_EOL;
${'set_'.$set_type}['sitemap_str'] .= '<p>$set_'.$set_type.'[\'sitemap_name\']</p>'.PHP_EOL;
${'set_'.$set_type}['sitemap_str'] .= '<p>$set_'.$set_type.'[\'sitemap_name_orig\']</p>'.PHP_EOL;

unset($set_key);
unset($set_type);
unset($set_sql);
unset($set_res);
unset($comma_equal);

unset($sql);
unset($rs);
