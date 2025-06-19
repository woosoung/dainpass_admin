<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$set_key = 'dain';
$set_type = 'menu';
$set_menu_sql = " SELECT * FROM {$g5['setting_table']} 
                    WHERE set_key = '{$set_key}'
                        AND set_type = '{$set_type}' ";
$set_menu_res = sql_query_pg($set_menu_sql);

for($i=0;$row=sql_fetch_array_pg($set_menu_res);$i++){
    ${'set_'.$row['set_type']}[$row['set_name']] = $row['set_value'];
    // A=B 형태를 가지고 있으면 자동 할당
    $set_values = explode(',', preg_replace("/\s+/", "", ${'set_'.$row['set_type']}[$row['set_name']]));
    ${'set_'.$row['set_type']}[$row['set_name'].'_arr'] = $set_values;
    foreach($set_values as $set_value){
        //변수가 (,),(=)로 구분되어 있을때
        if(preg_match("/=/",$set_value)){
            list($key, $value) = explode('=',$set_value);
            ${'set_'.$row['set_type']}[$row['set_name']][$key] = $value.'('.$key.')';
            ${'set_'.$row['set_type']}[$row['set_name'].'_karr'][$key] = $value;
            ${'set_'.$row['set_type']}[$row['set_name'].'_rarr'][$value] = $key;
            ${'set_'.$row['set_type']}[$row['set_name'].'_arrk'][] = $key;
            ${'set_'.$row['set_type']}[$row['set_name'].'_arrv'][] = $value;
            ${'set_'.$row['set_type']}[$row['set_name'].'_radio'] .= '<label for="'.$row['set_name'].'_'.$key.'" class="'.$row['set_name'].'"><input type="radio" id="'.$row['set_name'].'_'.$key.'" name="'.$row['set_name'].'" value="'.$key.'">'.$value.'</label>';
            ${'set_'.$row['set_type']}[$row['set_name'].'_check'] .= '<label for="'.$row['set_name'].'_'.$key.'"><input type="checkbox" id="'.$row['set_name'].'_'.$key.'" class="'.$row['set_name'].'_chk" name="'.$row['set_name'].'['.$key.']" key="'.$key.'" value="1">'.$value.'</label>';
            ${'set_'.$row['set_type']}[$row['set_name'].'_option'] .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
        }
    }
}
unset($set_key);
unset($set_type);
unset($set_menu_sql);
unset($set_menu_res);