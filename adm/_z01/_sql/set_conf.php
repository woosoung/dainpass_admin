<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$set_key = 'dain';
$set_type = 'conf';
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
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] = '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_karr\'][key]</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_varr\'][value]</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_arrk\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_arrv\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_radio\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_check\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_option\']</p>'.PHP_EOL;
    }
    else {
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] = '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_arr\'][key]</p>'.PHP_EOL;
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
            ${'set_'.$row['set_type']}[$row['set_name'].'_radio'] = (${ 'set_'.$row['set_type']}[$row['set_name'].'_radio'] ?? '') . '<label for="'.$row['set_name'].'_'.$key.'" class="'.$row['set_name'].'"><input type="radio" id="'.$row['set_name'].'_'.$key.'" name="'.$row['set_name'].'" value="'.$key.'">'.$value.'</label>';
            ${'set_'.$row['set_type']}[$row['set_name'].'_check'] = (${ 'set_'.$row['set_type']}[$row['set_name'].'_check'] ?? '') . '<label for="'.$row['set_name'].'_'.$key.'"><input type="checkbox" id="'.$row['set_name'].'_'.$key.'" class="'.$row['set_name'].'_chk" name="'.$row['set_name'].'['.$key.']" key="'.$key.'" value="1">'.$value.'</label>';
            ${'set_'.$row['set_type']}[$row['set_name'].'_option'] = (${ 'set_'.$row['set_type']}[$row['set_name'].'_option'] ?? '') . '<option value="'.trim($key).'">'.trim($value).'</option>';
        }
    }
}


unset($set_key);
unset($set_type);
unset($set_sql);
unset($set_res);
unset($comma_equal);

