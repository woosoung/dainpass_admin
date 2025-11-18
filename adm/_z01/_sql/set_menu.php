<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$set_key = 'dain';
$set_type = 'menu';
$set_menu_sql = " SELECT * FROM {$g5['setting_table']} 
                    WHERE set_key = '{$set_key}'
                        AND set_type = '{$set_type}' ";
$set_menu_res = sql_query_pg($set_menu_sql);

for($i=0;$row=sql_fetch_array_pg($set_menu_res->result);$i++){
    ${'set_'.$row['set_type']}[$row['set_name']] = $row['set_value'];
    // if(preg_match("/(_subject|_content|_title|_ttl|_desc|_description)$/",$row['set_name']) ) continue;
    if(preg_match('/^[^,=\^\r\n\t]+$/', ${'set_'.$row['set_type']}[$row['set_name']])) {
    // if(!preg_match('/[,\^\r\n\t]/', ${'set_'.$row['set_type']}[$row['set_name']])) {
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] = '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'\']</p>'.PHP_EOL;
        continue;
    }
    // 줄바꿈이 없고 문자열에서는 오로지 ,만 한 개이상으로 구성 되어 있을며 절대 ,로 시작해서도 안되고 ,로 끝나도 안되는 조건 (일반단순 배열)
    else if(preg_match('/^[^,=\^\r\n]+(?:,[^,=\^\r\n]+)+$/', ${'set_'.$row['set_type']}[$row['set_name']])) {
        ${'set_'.$row['set_type']}[$row['set_name'].'_arr'] = explode(',', ${'set_'.$row['set_type']}[$row['set_name']]);
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] = '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_arr\']</p>'.PHP_EOL;
        continue;
    }
    // ^와 ,와 \n 가 모두 포함되어 있을때 (메뉴관련 권한 설정)
    else if(preg_match('/(?=.*\^)(?=.*,)(?=.*\r|\n)/', ${'set_'.$row['set_type']}[$row['set_name']])){
        // 우선 줄바꿈을 기준으로 배열에 담는 코드
        $temp_lines = preg_split("/\r\n|\n|\r/", ${'set_'.$row['set_type']}[$row['set_name']]);
        // $tmep_lines 배열의 각 요소를 ^ 기준으로 나눠서 첫번째요소를 key로 그 값으로 배열값을 만들어 그 안에 두번째 요소를 auth라는 키의 값, 세번째 요소를 name이라는 키의 값으로 담음
        $set_values = array();
        foreach($temp_lines as $line){
            if(trim($line) == '') continue;
            $parts = explode('^', $line);
            if(count($parts) >= 3){
                $key = trim($parts[0]);
                $set_values[$key] = array(
                    'auth' => trim($parts[1]),
                    'name' => trim($parts[2])
                );
            }
        }
        ${'set_'.$row['set_type']}[$row['set_name'].'_arr'] = $set_values;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] = '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_arr\'][code][\'auth\']</p>'.PHP_EOL;
        ${'set_'.$row['set_type']}[$row['set_name'].'_str'] .= '<p>$set_'.$row['set_type'].'[\''.$row['set_name'].'_arr\'][code][\'name\']</p>'.PHP_EOL;
        continue;
    }
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