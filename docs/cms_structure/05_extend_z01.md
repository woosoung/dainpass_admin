이 파일은 기존 그누보드의 기본 구조에서 '다인패스의 관리자사이트(다인패스관리자, 가맹점관리자)'를 확장하여 개발하기 위해 추가된 디렉토리 및 파일의 구조와 내용을 정리한 파일이다.

# 그누보드 기반에서 다인패스관리자 사이트 확장개발을 위한 추가저거인 디렉토리 및 파일 구조

- 추가적인 dainpass관리자의 디레토리와 파일구조
- adm/
  - _z01/
    - __doc/
      - tomas.php
    - __set/
      - db_dain_default.php
      - db_dain_file.php
      - db_meta.php
      - db_setting.php
      - db_term.php
    - _adm/
      - _shop_admin/
        - _common.php
        - ajax.ca_id.php
        - categoryform.php
        - categoryformupdate.php
        - categorylist.php
        - categorylistupdate.php
      - _sms_admin/
        - _common.php
      - css/
        - menu_list.css
      - js/
        - menu_list.js.php
      - _common.php
      - captcha_file_delete.php
      - index.php
      - member_delete.php
      - member_form_update.php
      - member_form.php
      - member_list_delete.php
      - member_list_update.php
      - member_list.php
      - menu_form_search.php
      - menu_form.php
      - menu_list_update.php
      - menu_list.php
      - session_file_delete.php
    - _replace/
      - replace.php
    - _sql/
      - content.php
      - set_com.php
      - set_conf.php
      - set_menu.php
      - term_department.php
      - term_rank.php
      - term_role.php
    - ajax/
      - _common.php
      - email_check.php
      - hp_check.php
      - mb_id_check.php
      - nick_check.php
      - term_delete.php
    - css/
      - _adm_modal.css
      - _adm_tailwind_utility_class.php
      - _form.css
      - adm_add.css
      - adm_common_custom.css
      - adm_override.css
      - config_menu_form.css
      - config_menu_form.css.php
      - employee_form.css
      - employee_form.css.php
      - widget_form.css
    - form/
      - input_color.skin.php
      - input_range.skin.php
    - img/
      - add_img.png
      - bplug_icon.png
      - c_off.png
      - c_on.png
      - chk_0.png
      - chk_1.png
      - close_b.png
      - close_bg_circle.png
      - close_circle.png
      - close.png
      - delete.png
      - facebook.png
      - gplus.png
      - kakaotalk.png
      - loading copy.gif
      - loading.gif
      - menu-1-1.png
      - menu-1.png
      - menu-2-1.png
      - menu-2.png
      - menu-3-1.png
      - menu-3.png
      - menu-4-1.png
      - menu-4.png
      - menu-6-1.png
      - menu-6.png
      - menu-7-1.png
      - menu-7.png
      - menu-9-1.png
      - menu-9.png
      - menu-a-1.png
      - menu-a.png
      - menu-b-1.png
      - menu-b.png
      - modify.png
      - move_s.png
      - move.png
      - no_thumb.png
      - no_ytb.png
      - off.png
      - on.png
      - op_btn.png
      - op_btn1.gif
      - pattern03.gif
      - pattern04.gif
      - r_off.png
      - r_on.png
      - sub_menu_ico.gif
      - sub_menu_ico2.gif
      - transparent.gif
      - twitter.png
      - upclose.png
    - js/
      - colpick/
      - multifile/
      - adm_dom_control.js.php
      - adm_func.js
      - company_form.js.php
      - company_list.js.php
      - config_com_form.js.php
      - config_conf_form.js.php
      - config_menu_form.js.php
      - employee_form.js.php
      - tailwind.min.js
      - tms_datepicker.js
      - tms_timepicker.js
      - widget_form.js.php
    - lib/
      - _common.php
      - download.php
    - modal/
      - widget_modal.php
    - _adm_custom.php
    - _common.php
    - _win_company_select.php
    - company_form_update.php
    - company_form.php
    - company_list_update.php
    - company_list.php
    - company_member_form_update.php
    - company_member_form.php
    - company_member_list.php
    - config_com_form_file_update.php
    - config_com_form_update.php
    - config_com_form.php
    - config_conf_form_file_update.php
    - config_conf_form.php
    - config_form_update.php
    - config_menu_form.php
    - department_list_update.php
    - department_list.php
    - employee_form_update.php
    - employee_form.php
    - employee_list_update.php
    - employee_list.php
    - rank_list_update.php
    - rank_list.php
    - role_list_update.php
    - role_list.php
- extend/
  - z.01.config.php
  - z.02.function_redefind.php
  - z.03.pgconfig.php
  - z.04.function.php
  - z.05.auth.php
  - z.06.default.php



**설명**: 
각각의 제목은 파일명을 작성한 것으로 앞에 '/'로 파일의 경로위치를 명시할 수 있습니다.
'/'가 명시되어 있지 않으면 project root에 위치한 파일이라고 보면 됩니다.
아래의 기술한 소스코드파일들은 'extend'디렉토리의 하위에 존재하는 모든 파일을 정리하였습니다. 

### extend/z.01.config.php
```php
<?php
// 서비스 시작할때는 반드시 아래 3줄을 주석처리 해제해야 함
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// 공통변수, 상수 선언
define('BR',      		                '<br>');
define('G5_Z_DIR',      		        '_z01');
define('G5_SQL_DIR',      		        '_sql');
define('G5_SET_DIR',      		        '__set');
define('G5_REPLACE_DIR',                '_replace');
define('G5_NDR_DIR',      		        'ndr');
define('G5_NA_DIR',                     '_a');
define('G5_MODAL_DIR',                  'modal');
define('G5_Z_PATH',  				    G5_ADMIN_PATH.'/'.G5_Z_DIR);
define('G5_Z_URL',  				    G5_ADMIN_URL.'/'.G5_Z_DIR);
define('G5_ZSET_PATH',                  G5_Z_PATH.'/'.G5_SET_DIR);
define('G5_ZSET_URL',                   G5_Z_URL.'/'.G5_SET_DIR);
define('G5_ZSQL_PATH',                  G5_Z_PATH.'/'.G5_SQL_DIR);
define('G5_ZSQL_URL',                   G5_Z_URL.'/'.G5_SQL_DIR);
define('G5_ZREPLACE_PATH',              G5_Z_PATH.'/'.G5_REPLACE_DIR);
define('G5_ZREPLACE_URL',               G5_Z_URL.'/'.G5_REPLACE_DIR);
define('G5_ZADM_PATH',                  G5_Z_PATH.'/_adm');
define('G5_ZADM_URL',                   G5_Z_URL.'/_adm');
define('G5_ZMODAL_PATH',                G5_Z_PATH.'/'.G5_MODAL_DIR);
define('G5_ZMODAL_URL',                 G5_Z_URL.'/'.G5_MODAL_DIR);
define('G5_ZSHOP_ADMIN_PATH',           G5_ZADM_PATH.'/_shop_admin');
define('G5_ZSHOP_ADMIN_URL',            G5_ZADM_URL.'/_shop_admin');
define('G5_DATA_NDR_PATH',              G5_DATA_PATH.'/'.G5_NDR_DIR);
define('G5_DATA_NDR_URL',               G5_DATA_URL.'/'.G5_NDR_DIR);
define('G5_NA_PATH',                    G5_THEME_PATH.'/'.G5_NA_DIR);
define('G5_NA_URL',                     G5_THEME_URL.'/'.G5_NA_DIR);
define('G5_WMODAL_PATH',                G5_PATH.'/w'.G5_MODAL_DIR);
define('G5_WMODAL_URL',                 G5_URL.'/w'.G5_MODAL_DIR);

define('G5_DATA_Z_PERMISSION',  0707); // 디렉토리 생성시 퍼미션
define('G5_Z_TABLE_PREFIX', G5_TABLE_PREFIX.'1'); //g5_1

$g5['dain_default_table']                = 'dain_default';
$g5['dain_file_table']                   = 'dain_file';
$g5['meta_table']                        = 'meta';
$g5['setting_table']                     = 'setting';
$g5['term_table']                        = 'term'; //G5_Z_TABLE_PREFIX.'_term';

$g5['shop_table']                        = 'shop'; //가맹점
$g5['shop_categories_table']             = 'shop_categories'; //업종(분류)
$g5['shop_category_relation_table']      = 'shop_category_relation'; //업종-가맹점 크로스 테이블
```
### extend/z.02.function_redefined.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// url에 http:// 를 붙인다
if(!function_exists('set_http2')){
function set_http2($url){
    if (!trim($url)) return;
    
    $htp_s = (G5_HTTPS_DOMAIN == '') ? 'http://' : 'https://';
    if (!preg_match("/^(http|https|ftp|telnet|news|mms)\:\/\//i", $url) && substr($url,0,1)!='#')
        $url = $htp_s.$url;

    return $url;
}
}
```
### extend/z.03.pgconfig.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/*************************************************************************
PgSQL 관련 상수&함수 모음
*************************************************************************/

// PostgreSQL OR timescale DB connect
// define('G5_PGSQL_HOST', '61.83.89.15');
define('G5_PGSQL_HOST', 'dainpass-dev-pg-cluster-instance-1.cryaauiikrfz.ap-northeast-2.rds.amazonaws.com');//wsd_pgsql16
define('G5_PGSQL_USER', 'wsd');//wsd
define('G5_PGSQL_PASSWORD', 'wsd217');//wsd217
define('G5_PGSQL_DB', 'dainpass_db');//dainpass_db
```
### extend/z.04.function.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// DB 연결
if(!function_exists('sql_connect_pg')){
function sql_connect_pg($host, $user, $pass, $db=G5_PGSQL_DB)
{
    $pg_link = @pg_connect(" host = $host dbname = $db user = $user password = $pass ") or die('PgSQL Host, User, Password, DB 정보에 오류가 있습니다.');
    $stat = pg_connection_status($pg_link);
    if ($stat) {
        die('Connect Error: '.$pg_link);
    } 
    return $pg_link;
}
}

$connect_pg = sql_connect_pg(G5_PGSQL_HOST, G5_PGSQL_USER, G5_PGSQL_PASSWORD) or die('PgSQL Connect Error!!!');
$g5['connect_pg'] = $connect_pg;
// postgreSQL DB : end


if(!function_exists('sql_query_pg')){
function sql_query_pg($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null)
{
    global $g5;
    
    if(!$link)
        $link = $g5['connect_pg'];

    // Blind SQL Injection 취약점 해결
    $sql = trim($sql);

    if ($error) {
        $result = pg_query($link, $sql) or die("<p>$sql</p> <p>error file : {$_SERVER['SCRIPT_NAME']}</p>");
    } else {
        try {
            $result = @pg_query($link, $sql);
        } catch (Exception $e) {
            $result = null;
        }
    }

    return $result;
}
}

if(!function_exists('sql_insert_id_pg')){
/*
pg_query($g5['connect_pg'], "INSERT INTO products (name) VALUES ('상품1')");
$insert_id = sql_insert_id_pg('products');
if ($insert_id === false) {
    echo "Insert ID 조회 실패";
} else {
    echo "Insert된 ID: {$insert_id}";
}
*/
function sql_insert_id_pg($table, $link = null)
{
    global $g5;

    if (!$link)
        $link = $g5['connect_pg'];

    // Step 1: Primary Key 컬럼명 추출
    $pk_sql = "
        SELECT a.attname
        FROM   pg_index i
        JOIN   pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
        WHERE  i.indrelid = '{$table}'::regclass
        AND    i.indisprimary
        LIMIT  1
    ";

    $pk_result = pg_query($link, $pk_sql);
    if (!$pk_result || pg_num_rows($pk_result) === 0) {
        error_log("sql_insert_id_pg: PRIMARY KEY column not found for table {$table}");
        return false;
    }

    $pk_row = pg_fetch_assoc($pk_result);
    $id_column = $pk_row['attname'];

    // Step 2: 시퀀스 이름 추출
    $seq_sql = "SELECT pg_get_serial_sequence('{$table}', '{$id_column}') AS seq_name";
    $seq_result = pg_query($link, $seq_sql);
    if (!$seq_result) {
        error_log("sql_insert_id_pg: Failed to retrieve sequence name.");
        return false;
    }

    $seq_row = pg_fetch_assoc($seq_result);
    $seq_name = $seq_row['seq_name'];

    if (!$seq_name) {
        error_log("sql_insert_id_pg: No sequence associated with {$table}.{$id_column}");
        return false;
    }

    // Step 3: currval() 호출 (이전에 nextval이 호출된 적 있는지 확인 필요)
    $currval_sql = "SELECT currval('{$seq_name}')";
    $currval_result = @pg_query($link, $currval_sql);

    if (!$currval_result) {
        error_log("sql_insert_id_pg: currval() failed — INSERT may not have occurred in this session.");
        return false;
    }

    $row = pg_fetch_row($currval_result);
    return $row[0];
}
}



if(!function_exists('sql_num_rows_pg')){
function sql_num_rows_pg($result)
{
    return pg_num_rows($result);
    // return pg_num_rows($result);
}
}

if(!function_exists('sql_field_names_pg')){
function sql_field_names_pg($table, $link=null)
{
    global $g5;

    if(!$link)
        $link = $g5['connect_pg'];

    $columns = array();

    $sql = "SELECT column_name, data_type, character_maximum_length
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = '".$table."'
	";
    $result = sql_query_pg($sql,1);
	while($field = sql_fetch_array_pg($result)) {
		// print_r2($field);
		// echo $field['column_name'].'<br>';
		$columns[] = $field['column_name'];
	}

    return $columns;
}
}

// 쿼리를 실행한 후 결과값에서 한행을 얻는다.
if(!function_exists('sql_fetch_pg')){
function sql_fetch_pg($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null)
{
    global $g5;

    if(!$link)
        $link = $g5['connect_pg'];

    $result = sql_query_pg($sql, $error, $link);
    $row = sql_fetch_array_pg($result);
    return $row;
}
}

// 결과값에서 한행 연관배열(이름으로)로 얻는다.
if(!function_exists('sql_fetch_array_pg')){
function sql_fetch_array_pg($result)
{
    if( ! $result) return array();

    try {
        $row = @pg_fetch_assoc($result);
    } catch (Exception $e) {
        $row = null;
    }

    return $row;
}
}

// TimescaleDB 
// get_table_pg('g5_shop_item','it_id',215021535,'it_name')	// 4번째 매개변수는 테이블명과 같으면 생략할 수 있다.
if(!function_exists('get_table_pg')){
function get_table_pg($db_table,$db_field,$db_id,$db_fields='*')
{
    global $db;

	if(!$db_table||!$db_field||!$db_id)
		return false;
    
    $table_name = 'g5_1_'.$db_table;
    $sql = " SELECT ".$db_fields." FROM ".$table_name." WHERE ".$db_field." = '".$db_id."' LIMIT 1 ";
    $row = sql_fetch_pg($sql);
    return $row;
}
}

if(!function_exists('pg_table_exists')){
/*
if (!pg_table_exists($g5['setting_table'])) {
    include_once(G5_ZSET_PATH.'/db_set.php');
}
*/
function pg_table_exists($table, $link = null)
{
    global $g5;

    if (!$link)
        $link = $g5['connect_pg'];

    $sql = "
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = 'public'
          AND table_name = '".pg_escape_string($table)."'
    ";

    $result = pg_query($link, $sql);
    return $result && pg_num_rows($result) > 0;
}
}

/***********************************/
//--PgSQL 관련 함수 모음 : 여기까지
/***********************************/

// 특정 절대경로의 디렉토리의 하위 디렉토리 목록을 배열로 반환하는 함수
if(!function_exists('dir_list_in_path')){
function dir_list_in_path($dir=''){
    $files = array();
    if(!$dir)
        return $files;
    $cod_path = $dir;
    if(is_dir($cod_path)){
        if($handle = opendir($cod_path)){
            while(($file = readdir($handle)) !== false) {
                if($file != "." && $file != ".." && is_dir($cod_path.'/'.$file)){
                    $files[] = $file;
                }
            }
            closedir($handle);
        }
        sort($files);// 디렉토리 목록을 알파벳순으로 정렬
    }
    return $files;
}
}

//gmail SMTP 설정
//gmailer("수신메일주소", "메일제목", "메일내용");
if(!function_exists('gmailer')){
function gmailer($to, $subject, $content, $type=1)
{
    global $config;
    global $g5;
    // 메일발송 사용을 하지 않는다면
    if (!$config['cf_email_use']) {
        return;
    }
    if ($type != 1) {
        $content = nl2br($content);
    }
    include_once(G5_PHPMAILER_PATH.'/PHPMailerAutoload.php');
    $mail = new PHPMailer(); // defaults to using php "mail()"
    if (defined('G5_SMTP') && G5_SMTP) {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->Username = "tomasjoa21"; //사용할 지메일 계정
        $mail->Password = "jemi0210!@#"; //구글계정 패스워드
    }
    $mail->CharSet = 'UTF-8';
    $mail->From = "tomasjoa21@gmail.com"; //발송메일(=사용할 지메일 계정)
    $mail->FromName = "다인패스"; // 메일발송자명
    $mail->Subject = $subject;
    $mail->AltBody = ""; // optional, comment out and test
    $mail->msgHTML($content);
    $mail->addAddress($to);
    return $mail->send();
}
}

// 기본 디비 배열 + 확장 meta 배열
// get_table_meta('g5_shop_item','it_id',215021535,'shop_item')	// 4번째 매개변수는 테이블명과 같으면 생략할 수 있다.
if(!function_exists('get_table_meta')){
function get_table_meta($db_table,$db_field,$db_id,$db_table2=''){
    global $g5;
    
    if(!$db_table||!$db_field||!$db_id)
        return false;

    // 게시판인 경우
    if($db_field=='wr_id') {
        $table_name = $g5['write_prefix'].$db_table;
    }
    else {
        $table_name = $g5[$db_table.'_table'];
    }

    // db_table2가 없으면 db_table과 같은 값
    $db_table2 = (!$db_table2) ? $db_table : $db_table2;

    $sql = " SELECT * FROM ".$table_name." WHERE ".$db_field." = '".$db_id."' LIMIT 1 ";
    // print_r3($sql);
    //echo $sql.'<br>';
    $row = sql_fetch($sql);
    $row2 = get_meta($db_table2,$db_id);
    if(is_array($row) && is_array($row2))
        $row = array_merge($row, $row2);	// meta 값을 배열로 만들어서 원배열과 병합
    // print_r2($row);

    return $row;
}
}

// 기본 디비 배열 + 확장 meta 배열
// get_table_meta('g5_shop_item','it_id',215021535,'shop_item')	// 4번째 매개변수는 테이블명과 같으면 생략할 수 있다.
if(!function_exists('get_table_meta')){
function get_table_meta_pg($db_table,$db_field,$db_id,$db_table2=''){
    global $g5;
    
    if(!$db_table||!$db_field||!$db_id)
        return false;

    // 게시판인 경우
    if($db_field=='wr_id') {
        $table_name = $g5['write_prefix'].$db_table;
    }
    else {
        $table_name = $g5[$db_table.'_table'];
    }

    // db_table2가 없으면 db_table과 같은 값
    $db_table2 = (!$db_table2) ? $db_table : $db_table2;

    $sql = " SELECT * FROM ".$table_name." WHERE ".$db_field." = '".$db_id."' LIMIT 1 ";
    // print_r3($sql);
    //echo $sql.'<br>';
    $row = sql_fetch_pg($sql);
    $row2 = get_meta($db_table2,$db_id);
    if(is_array($row) && is_array($row2))
        $row = array_merge($row, $row2);	// meta 값을 배열로 만들어서 원배열과 병합
    // print_r2($row);

    return $row;
}
}

//--- 메타 테이블 저장 ---//
if(!function_exists('meta_update')){
function meta_update($meta_array){
    global $g5;
    
    if(!$meta_array['mta_key'])
        return 0;

    if($meta_array['mta_value']){
        $meta_array['mta_value'] = pg_escape_string($meta_array['mta_value']);
    }

    $row1 = sql_fetch_pg("	SELECT * FROM {$g5['meta_table']} 
                            WHERE mta_db_tbl='{$meta_array['mta_db_tbl']}' 
                                AND mta_db_idx='{$meta_array['mta_db_idx']}' 
                                AND mta_key='{$meta_array['mta_key']}' ");
    if($row1['mta_idx']) {
        $sql = " UPDATE {$g5['meta_table']} SET 
                    mta_value='{$meta_array['mta_value']}'
                    , mta_update_dt='".G5_TIME_YMDHIS."' 
                WHERE mta_idx='".$row1['mta_idx']."' ";
        sql_query_pg($sql);
    }
    else {
        $sql = " INSERT INTO {$g5['meta_table']} (
            mta_db_tbl,
            mta_db_idx,
            mta_key,
            mta_value,
            mta_title,
            mta_reg_dt
        ) VALUES (
            '{$meta_array['mta_db_tbl']}',
            '{$meta_array['mta_db_idx']}',
            '{$meta_array['mta_key']}',
            '{$meta_array['mta_value']}',
            '{$meta_array['mta_title']}',
            '".G5_TIME_YMDHIS."'
        ) ";
        sql_query_pg($sql);
        $row1['mta_idx'] = sql_insert_id_pg($g5['meta_table']);
    }
    return $row1['mta_idx'];
}
}

// 확장 메타값 배열로 반환하는 함수
// serialized 되었다면 각 항목별로 분리해서 배열로 만듦
if(!function_exists('get_meta')){
function get_meta($db_table,$db_id,$code64=1)
{
    global $g5;

    if(!$db_table||!$db_id)
        return false;

    $mta2 = []; // 빈 배열로 초기화

    $sql = " SELECT mta_key, mta_value FROM {$g5['meta_table']} WHERE mta_db_tbl = '".$db_table."' AND mta_db_idx = '".$db_id."' ";
    // echo $sql.'<br>';exit;
    $rs = sql_query_pg($sql);
    
    for($i=0;$row=sql_fetch_array_pg($rs);$i++) {
        $mta2[$row['mta_key']] = $row['mta_value'];
        //echo $row['mta_key'].'='.$row['mta_value'].'<br>';
        if(is_serialized($row['mta_value'])) {
            //unset($mta2[$row['mta_key']]); // serialized된 변수는 제거
            $unser = unserialize($row['mta_value']);
            if( is_array($unser) ) {
                foreach ($unser as $k1=>$v1) {
                    //echo $k1.'='.$v1.' -------- <br>';
                    if($code64)
                        $mta2[$k1] = stripslashes64($v1);
                    else
                        $mta2[$k1] = stripslashes($v1);
                }
            }
        }
    }
    return $mta2;
}
}

// is_serialized 함수
if(!function_exists('is_serialized')){
function is_serialized($string) {
    return (@unserialize($string) !== false || $string == 'b:0;');
}
}

// unserialized 한 후 변수 후처리
if(!function_exists('stripslashes64')){
function stripslashes64($str) {
    return stripslashes(base64_decode($str));
}
}

// 숫자로만 구성된 휴대폰번호나 전화번호를 형식에 맞게 (-)하이픈을 넣어서 반환해 주는 함수
if(!function_exists('formatPhoneNumber')){
function formatPhoneNumber($phoneNumber) {
    $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
    
    if (substr($cleaned, 0, 2) === '02') {  // 서울 지역번호
        if (strlen($cleaned) === 9) {
            return '02-' . substr($cleaned, 2, 3) . '-' . substr($cleaned, 5);
        } elseif (strlen($cleaned) === 10) {
            return '02-' . substr($cleaned, 2, 4) . '-' . substr($cleaned, 6);
        }
    } elseif (strlen($cleaned) === 11) {  // 휴대폰 또는 지방 지역번호
        return substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 4) . '-' . substr($cleaned, 7);
    } elseif (strlen($cleaned) === 10) {  // 지방 지역번호
        return substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 3) . '-' . substr($cleaned, 6);
    }
    
    return $phoneNumber;  // 원래 입력을 반환 (유효하지 않은 경우)
}
}

// 사업자번호가 숫자로만 되어 있을때 형식에 맞게 (-)하이픈을 넣어서 반환해 주는 함수
if(!function_exists('formatBizNumber')){
function formatBizNumber($bizNumber) {
    // 입력된 문자열에서 숫자만 남김
    $bizNumber = preg_replace('/[^0-9]/', '', $bizNumber);

    // 사업자 번호가 정확히 10자리인지 확인
    if (strlen($bizNumber) !== 10) {
        return "";//"유효하지 않은 사업자 번호입니다.";
    }

    // xxx-xx-xxxxx 형태로 포맷팅
    return substr($bizNumber, 0, 3) . '-' .
           substr($bizNumber, 3, 2) . '-' .
           substr($bizNumber, 5, 5);
}
}

// 20240101 형식의 날짜를 2024-01-01 형식으로 반환하는 함수
if(!function_exists('formatDate')){
function formatDate($date){
    return ($date)?substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2):'';
}
}

// 테이블에 필드명의 접두어와 필드명을 배열로 반환하는 함수
if(!function_exists('getPrefixFields')){
function getPrefixFields($tbl_name){
    $arr = array();
    $r = sql_query(" desc {$tbl_name} ");
    while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
    $cnt = strpos($db_fields[0], '_');
    $db_prefix = substr($db_fields[0],0,($cnt + 1));
    $arr['prefix'] = $db_prefix;
    $arr['fields'] = $db_fields;
    return $arr;
}    
}

//$data로 넘어온 데이터중에 추출한 접두어를 가진 필드중에 테이블에 존재하지 않는 데이터만 추출해서 배열로 반환
if(!function_exists('getExTableData')){
function getExTableData($prefix,$fields,$data){
    $arr = array();
    foreach($data as $k => $v){
        if(strpos($k,$prefix) !== false){
            if(!in_array($k,$fields)){
                $arr[$k] = $v;
            }
        }
    }
    return $arr;
}
}

// fle_db_tbl, fle_db_idx, fle_type 으로 파일삭제하기
if(!function_exists('delete_db_file')) {
function delete_db_file($fle_db_tbl='',$fle_db_idx='',$fle_type=''){
    global $g5;
    $fr = sql_fetch_pg(" SELECT GROUP_CONCAT(fle_idx) AS fle_idxs FROM {$g5['dain_file_table']} WHERE fle_db_tbl = '{$fle_db_tbl}' AND fle_db_idx = '{$fle_db_idx}' AND fle_type = '{$fle_type}' ");
    if($fr['fle_idxs']) {
        $fle_idx_array = explode(',',$fr['fle_idxs']);
        delete_idx_file($fle_idx_array);
    }
}
}

//fle_idx로 파일삭제하기
if(!function_exists('delete_idx_file')) {
function delete_idx_file($fle_idx_array=array()) {
    global $g5;
    //print_r2($fle_idx_array);
    foreach($fle_idx_array as $k=>$v) {
        $fr = sql_fetch_pg(" SELECT fle_path, fle_name FROM {$g5['dain_file_table']} WHERE fle_idx = '{$v}' ");
        @unlink(G5_DATA_PATH.$fr['fle_path'].'/'.$fr['fle_name']);
        delete_ndr_file_thumbnail($fr['fle_path'], $fr['fle_name']);
        sql_query_pg(" DELETE FROM {$g5['dain_file_table']} WHERE fle_idx = '{$v}' ");
    }
}
}

// ndr file 관련 썸네일 이미지 삭제
if(!function_exists('delete_ndr_file_thumbnail')){
function delete_ndr_file_thumbnail($path, $file)
{
    if(!$path || !$file)
        return;

    $path = G5_DATA_PATH.$path;

    $filename = preg_replace("/\.[^\.]+$/i", "", $file); // 확장자제거
    $files = glob($path.'/thumb-'.$filename.'*');
    if(is_array($files)) {
        foreach($files as $thumb_file) {
            @unlink($thumb_file);
        }
    }
}
}

//멀티일반파일(이미지가 아닌 파일[복수파일])업로드
//인수(1:파일배열, 2.DB테이블명, 3.DB인덱스, 4.파일타입)
if(!function_exists('upload_multi_file')){
function upload_multi_file($_files=array(),$tbl='',$idx=0,$fle_type=''){
    global $g5,$config,$member;
    //echo count($_files['name']);
    $f_flag = (!count($_files['name']) || !$_files['name'][0]) ? false : true;
    if($f_flag){
        for($i=0;$i<count($_files['name']);$i++) {
            if ($_files['name'][$i]) {
                $upfile_info = upload_insert_file(array("fle_idx"=>$fle_idx
                                    ,"fle_mb_id"=>$member['mb_id']
                                    ,"fle_name"=>$_files['tmp_name'][$i]
                                    ,"fle_name_orig"=>$_files['name'][$i]
                                    ,"fle_mime_type"=>$_files['type'][$i]
                                    ,"fle_desc"=>''
                                    ,"fle_path"=>'/ndr/'.$fle_type		//<---- 저장 디렉토리
                                    ,"fle_db_tbl"=>$tbl
                                    ,"fle_db_idx"=>$idx
                                    ,"fle_type"=>$fle_type
                                    ,"fle_sort"=>$i
                ));
                //print_r2($upfile_info);
            }
        }
    }//if($f_flag)
}
}

// Post File 업로드 함수
//설정 변수: fle_mb_id, fle_name, fle_name_orig, fle_mime_type, fle_path, fle_db_tbl, fle_db_idx, fle_sort ....
if(!function_exists('upload_insert_file')){
function upload_insert_file($fle_array){
    global $g5,$config,$member;

    //-- 원본 파일명이 없으면 리턴
    if($fle_array['fle_name_orig'] == "")
        return false;

    //-- 파일명 재설정, 한글인 경우는 변경
    $fle_array['fle_dest_file'] = preg_replace("/\s+/", "", $fle_array['fle_name_orig']);
    $fle_array['fle_dest_file'] = preg_replace("/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/", "", $fle_array['fle_dest_file']);
    $fle_array['fle_dest_file'] = preg_replace_callback(
                            "/[ㄱ-ㅎ|ㅏ-ㅣ|가-힣]+/",
                            create_function('$matches', 'return base64_encode($matches[0]);'),
                            $fle_array['fle_dest_file']);
    $fle_array['fle_dest_file'] = preg_replace("/\+/", "", $fle_array['fle_dest_file']);	// 한글변환후 + 기호가 있으면 제거해야 함
    $fle_array['fle_dest_file'] = preg_replace("/\//", "", $fle_array['fle_dest_file']);	// 한글변환후 / 기호가 있으면 제거해야 함

    // 상태값이 있으면 업데이트
    if($fle_array['fle_status'])
        $sql_status = $fle_array['fle_status'];
    else
        $sql_status = "ok";

    //-- 파일 업로드 처리
    $upload_file = upload_common_file($fle_array['fle_name'], $fle_array['fle_dest_file'], $fle_array['fle_path']);
    //print_r2($upload_file);


    // 파일의 mime_type 추출
    if(!$fle_array['fle_mime_type'])
        $fle_array['fle_mime_type'] = mime_content_type($filename);

    $sql = " INSERT INTO {$g5['dain_file_table']} (
            fle_mb_id,
            fle_db_tbl,
            fle_db_idx,
            fle_type,
            fle_path,
            fle_name,
            fle_name_orig,
            fle_width,
            fle_height,
            fle_desc,
            fle_sort,
            fle_mime_type,
            fle_size,
            fle_reg_dt,
            fle_status
        ) VALUES (
            '{$fle_array['fle_mb_id']}',
            '{$fle_array['fle_db_tbl']}',
            '{$fle_array['fle_db_idx']}',
            '{$fle_array['fle_type']}',
            '{$fle_array['fle_path']}',
            '{$upload_file[0]}',
            '{$fle_array['fle_name_orig']}',
            '{$upload_file[1]}',
            '{$upload_file[2]}',
            '{$fle_array['fle_desc']}',
            '{$fle_array['fle_sort']}',
            '{$fle_array['fle_mime_type']}',
            '{$upload_file[3]}',
            '".G5_TIME_YMDHIS."',
            '{$sql_status}'
        )";
    sql_query_pg($sql);
    $fle_idx = sql_insert_id_pg($g5['dain_file_table']);

    //$fle_return[0] = $upload_file[0];
    //$fle_return[1] = $upload_file[1];
    //$fle_return[2] = $upload_file[2];
    //$fle_return[3] = $upload_file[3];
    //$fle_return[4] = $pfl['fle_idx'];
    //return $fle_return;
    return array("upfile_name"=>$upload_file[0]
                    ,"upfile_width"=>$upload_file[1]
                    ,"upfile_height"=>$upload_file[2]
                    ,"upfile_filesize"=>$upload_file[3]
                    ,"upfile_fle_idx"=>$fle_idx
                    ,"upfile_fle_sort"=>$fle_array['fle_sort']
                    );
}
}

// 파일을 업로드 함
if(!function_exists('upload_common_file')){
function upload_common_file($srcfile, $destfile, $dir)
{
    if ($destfile == "") return false;

    // 디렉토리가 없다면 생성 (퍼미션도 변경!)
    @mkdir(G5_DATA_PATH.$dir, G5_DIR_PERMISSION);
    @chmod(G5_DATA_PATH.$dir, G5_DIR_PERMISSION);

    //-- 디렉토리 재설정
    $dir = G5_DATA_PATH.$dir;

    //-- 디렉토리내 동일 파일명이 존재하면 일련번호 붙인 형태로 생성하고 파일명 리턴
    $file_parts = pathinfo($dir.'/'.$destfile);
    $file_name = $file_parts['filename'];
    $full_name = $file_name.'.'.$file_parts['extension'];
    $file_name_with_path = rtrim($dir,'/').'/'.$full_name;

    if(file_exists($file_name_with_path)) {
        $a = glob($dir.'/'.$file_name.'*');
        natcasesort($a);
        $i=0;
        foreach($a as $key => $val) {
            //echo "/".$file_name."\(/i".'<br>';
            if( preg_match("/".$file_name."\(/i",$val) ) {
                $b[$i] = $val;
                $i++;
            }
        }
        //if(sizeof($b) > 1) {
        if(@sizeof($b)) {
            preg_match_all('/(\([0-9]+\))/',$b[sizeof($b)-1],$match);
            $rows = count($match,0);
            $cols = (count($match,1)/count($match,0))-1;
            $file_no = substr($match[$rows-1][$cols-1],1,-1)+1;
        }
        else
            $file_no = 1;

        //-- 파일명 재 설정 --//
        $full_name = $file_name.'('.$file_no.').'.$file_parts['extension'];
    }
    else
        $full_name = $destfile;

    // 업로드 한후 , 퍼미션을 변경함
    @move_uploaded_file($srcfile, $dir.'/'.$full_name);
    @chmod($dir.'/'.$full_name, G5_FILE_PERMISSION);

    $size = @getimagesize($dir.'/'.$full_name);
    $file_size = filesize($dir.'/'.$destfile);

    return array($full_name,$size[0],$size[1],$file_size);
}
}

//--- 환경설정 변수 저장 ---//
if(!function_exists('set_update')){
function set_update($set_array)
{
    global $g5,$config;

    $set_key = ($set_array['set_key']) ? $set_array['set_key']:'dain';
    $set_auto_yn = (isset($set_array['set_auto_yn']) && $set_array['set_auto_yn'] == 'Y') ? 'Y':'N';
    $set_com_idx = (isset($set_array['set_com_idx']) && $set_array['set_com_idx'] != 0) ? $set_array['set_com_idx']:0;
    $set_trm_idx = (isset($set_array['set_trm_idx']) && $set_array['set_trm_idx'] != 0) ? $set_array['set_trm_idx']:0;
    
    $row1 = sql_fetch_pg(" SELECT * FROM {$g5['setting_table']}
                        WHERE set_name='{$set_array['set_name']}'
                            AND set_key = '{$set_key}'
                            AND set_type = '{$set_array['set_type']}'
                            AND set_name = '{$set_array['set_name']}' ");
    
    if($row1['set_idx']) {
        $u_sql = " UPDATE {$g5['setting_table']} SET
                            set_value='{$set_array['set_value']}',
                            set_auto_yn='$set_auto_yn'
                        WHERE set_idx='".$row1['set_idx']."' ";
        // echo $u_sql;exit;
        sql_query_pg($u_sql);
    }
    else {
        sql_query_pg(" INSERT INTO {$g5['setting_table']} (
            set_com_idx,
            set_trm_idx,
            set_key,
            set_type,
            set_name,
            set_value,
            set_auto_yn
        ) VALUES (
            '{$set_com_idx}',
            '{$set_trm_idx}',
            '{$set_key}',
            '{$set_array['set_type']}',
            '{$set_array['set_name']}',
            '{$set_array['set_value']}',
            '{$set_auto_yn}'
        ) ");
    }
}
}

// 디비 테이블의 시퀀스 초기화 함수
if(!function_exists('dbtable_sequence_reset')){
function dbtable_sequence_reset($table_name){
    $tbl_exist = @sql_query(" DESC ".$table_name." ",false);
    if($tbl_exist){
        $record_exist = sql_fetch(" SELECT EXISTS (SELECT 1 FROM {$table_name}) AS cnt ");
        if(!$record_exist['cnt']) sql_query(" ALTER TABLE {$table_name} auto_increment = 1 ");
    }
}
}

// pgsql 디비 테이블의 시퀀스 초기화 함수
if (!function_exists('dbtable_sequence_reset_pg')) {
    function dbtable_sequence_reset_pg($table_name) {
        // 테이블 존재 여부 확인
        $tbl_exist = @sql_query_pg("SELECT to_regclass('public.{$table_name}')");
        $tbl_info = sql_fetch_array_pg($tbl_exist);
        
        if ($tbl_info[0]) {
            // 해당 테이블에 연결된 SERIAL 시퀀스명 자동 추출
            $seq_result = sql_fetch_pg("SELECT pg_get_serial_sequence('{$table_name}', a.attname) AS seq_name
                FROM pg_class c
                JOIN pg_attribute a ON a.attrelid = c.oid
                WHERE c.relname = '{$table_name}' AND a.attnum > 0 AND a.attisdropped = false
                AND pg_get_serial_sequence('{$table_name}', a.attname) IS NOT NULL
                LIMIT 1");

            $sequence_name = $seq_result['seq_name'];

            if ($sequence_name) {
                // 레코드가 없는 경우에만 초기화
                $record_exist = sql_fetch_pg("SELECT EXISTS (SELECT 1 FROM {$table_name}) AS cnt");
                if (!$record_exist['cnt']) {
                    sql_query_pg("ALTER SEQUENCE {$sequence_name} RESTART WITH 1");
                }
            }
        }
    }
}


//전체 uri의 get변수 중 특정 영역의 변수값을 반환
if(!function_exists('uriReturnGetValue')){
function uriReturnGetValue($getArea,$ky){
    $uri_arr = explode('&',$getArea);
    foreach($uri_arr as $uri_get){
        list($key,$value) = explode('=',trim($uri_get));
        $uriArr[$key] = $value;
    }
    return $uriArr[$ky];
}
}

//전체 url에서 get변수 영역만 추출하는 함수, 전체 uri에서 get변수만 추출하는 함수
if(!function_exists('uriReturnGetArea')){
function uriReturnGetArea($uri,$ky){
    $pos = stripos($uri,$ky);
    if(is_int($pos)){
        return substr($uri,$pos+strlen($ky));
    }
    return false;
}
}

//루프(loop)코드에서 생성하는 자릿수가 일정한 타임베이스의 유니크값을 반환하는 함수
if(!function_exists('loop_time_uniqid')){
function loop_time_uniqid($l=2,$n){ //자릿수, 각루프값(주로 $i값)
    if(!preg_match('/\d/',$n) || !preg_match('/\d/',$l)) return 0;
    $uniq = time().sprintf('%0'.$l.'d',$n);
    return $uniq;
}   
}

//유니크값을 반환하는 함수
if(!function_exists('tms_uniqid')){
function tms_uniqid(){
    $start_ran = mt_rand(0,38);
    $cnt_ran = mt_rand(4,7);
    $uniq = substr(uniqid(md5(rand())),$start_ran,$cnt_ran);
    $uniq2 = substr(uniqid(md5(rand())),$start_ran,$cnt_ran);
    //$uniq3 = substr(uniqid(md5(rand())),$start_ran,$cnt_ran);
    //return tms_get_random_string('az',3).$uniq.$uniq2.$uniq3;
    return tms_get_random_string('az',3).$uniq.$uniq2;
}   
}

if(!function_exists('tms_get_random_string')){
function tms_get_random_string($type = '', $len = 10) {
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numeric = '0123456789'; 
    $special = '`~!@#$%^&*()-_=+\\|[{]};:\'",<.>/?';
    $key = '';
    $token = '';
    if ($type == '') {
        $key = $lowercase.$uppercase.$numeric;
    } else {
        if (strpos($type,'09') > -1) $key .= $numeric;
        if (strpos($type,'az') > -1) $key .= $lowercase; 
        if (strpos($type,'AZ') > -1) $key .= $uppercase;
        if (strpos($type,'$') > -1) $key .= $special;
    }
    
    for ($i = 0; $i < $len; $i++) {
        $token .= $key[mt_rand(0, strlen($key) - 1)];
    }
    return $token;
}
}

// 입력 폼 안내문
if(!function_exists('tms_help')){	
function tms_help($help="",$iup=0,$bgcolor='#ffffff',$fontcolor='#555555'){
    global $g5;
    $iupclass = ($iup) ? "iup" : 'idown';
    $str = ($help) ? '<div class="tms_info_box"><p class="tms_info '.$iupclass.'" style="background:'.$bgcolor.';color:'.$fontcolor.';">'.str_replace("\n", "<br>", $help).'</p></div>' : '';
    return $str;
}
}

//색상/투명도 설정 input form 생성 함수
if(!function_exists('tms_input_color')){
function tms_input_color($name='',$value='#333333',$w='',$alpha_flag=0){
    global $g5,$config,$default,$member,$is_admin;
    
    //if($name == '') return '컬러픽커 name값이 없습니다.';
    
    $aid = tms_get_random_string('az',4).'_'.tms_uniqid();
    $bid = tms_get_random_string('az',4).'_'.tms_uniqid();
    $cid = tms_get_random_string('az',4).'_'.tms_uniqid();
    //그외 랜덤id값
    $did = tms_get_random_string('az',4).'_'.tms_uniqid();
    $eid = tms_get_random_string('az',4).'_'.tms_uniqid();
    
    
    if($alpha_flag){
        if(substr($value,0,1) == '#') $value = 'rgba('.tms_rgb2hex2rgb($value).',1)';
        $input_color = (isset($value)) ? $value : 'rgba(51, 51, 51, 1)';
        //echo $value;
        $bgrgba = substr(substr($input_color,5),0,-1);//처음에 'rgba('를 잘라낸뒤 반환하고, 그다음 끝에 ')'를 잘라내고 '255, 0, 0, 0'를 반환
        $rgba_arr = explode(',',$bgrgba);
        $bgrgb = trim($rgba_arr[0]).','.trim($rgba_arr[1]).','.trim($rgba_arr[2]);
        $bga = trim($rgba_arr[3]);
        //echo $bga;
        $bg16 = ($w == 'u') ? tms_rgb2hex2rgb($bgrgb) : '#333333';//#FF0000
    }
    else{
        if(substr($value,0,4) == 'rgba'){
            $rgb_str_arr = explode(',',substr(substr($value,5),0,-1));
            $rgb_str = $rgb_str_arr[0].','.$rgb_str_arr[1].','.$rgb_str_arr[2];
            $value = tms_rgb2hex2rgb($rgb_str);
        }
        $input_color = ($value) ? $value : '#333333';
    }
    
    ob_start();
    include G5_Z_PATH.'/form/input_color.skin.php';
    $input_content = ob_get_contents();
    ob_end_clean();

    return $input_content;
}
}

//색상코드 16진수를 rgb로, rgb를 16진수로 반환해주는 함수
if(!function_exists('tms_rgb2hex2rgb')){
function tms_rgb2hex2rgb($color){ //인수에 '#ff0000' 또는 '255,0,0'를 넣어 호출하면 된다.
    if(!$color) return false; 
    $color = trim($color); 
    $result = false; 
    if(preg_match("/^[0-9ABCDEFabcdef\#]+$/i", $color)){
        $hex = str_replace('#','', $color);
        if(!$hex) return false;
        if(strlen($hex) == 3):
            $result['r'] = hexdec(substr($hex,0,1).substr($hex,0,1));
            $result['g'] = hexdec(substr($hex,1,1).substr($hex,1,1));
            $result['b'] = hexdec(substr($hex,2,1).substr($hex,2,1));
        else:
            $result['r'] = hexdec(substr($hex,0,2));
            $result['g'] = hexdec(substr($hex,2,2));
            $result['b'] = hexdec(substr($hex,4,2));
        endif;
        $result = $result['r'].','.$result['g'].','.$result['b']; //텍스트(255,0,0)로 표시하고 싶으면 주석 해제해라
    }elseif (preg_match("/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $color)){ 
        $color = str_replace(' ','',$color);
        $rgbstr = str_replace(array(',',' ','.'), ':', $color); 
        $rgbarr = explode(":", $rgbstr);
        $result = '#';
        $result .= str_pad(dechex($rgbarr[0]), 2, "0", STR_PAD_LEFT);
        $result .= str_pad(dechex($rgbarr[1]), 2, "0", STR_PAD_LEFT);
        $result .= str_pad(dechex($rgbarr[2]), 2, "0", STR_PAD_LEFT);
        $result = strtoupper($result); 
    }else{
        $result = false;
    }

    return $result; 
}
}

//ie브라우저인지 확인해 주는 함수 위 browserCheck()함수 사용함
if (!function_exists('tms_is_explorer')){
function tms_is_explorer(){
    /*
    크롬 : Chrome/Safari
    파폭 : Firefox
    익11 : Trident
    익10 : MSIE
    훼일 : Chrome/Whale/Safari
    엣지 : Chrome/Safari/Edge
    */
    $browser_name = tms_browserCheck();
    $ie_flag = false;
    if(preg_match("/ie/", $browser_name)){
        $ie_flag = true;
    }

    return $ie_flag;
}
}

//접속한 브라우저의 이름/버전을 반환해 주는 함수
if (!function_exists('tms_browserCheck')){
function tms_browserCheck(){
    /*
    크롬 : Chrome/Safari
    파폭 : Firefox
    익11 : Trident
    익10 : MSIE
    훼일 : Chrome/Whale/Safari
    엣지 : Chrome/Safari/Edge
    */
    $userAgent = $_SERVER["HTTP_USER_AGENT"];
    //echo $userAgent;
    if ( preg_match("/MSIE*/", $userAgent) ) {
        // 익스플로러
        if ( preg_match("/MSIE 6.0[0-9]*/", $userAgent) ) {
            $browser = "ie6"; //"explorer6";
        }else if ( preg_match("/MSIE 7.0*/", $userAgent) ) {
            $browser = "ie7"; //"explorer7";
        }else if ( preg_match("/MSIE 8.0*/", $userAgent) ) {
            $browser = "ie8"; //"explorer8";
        }else if ( preg_match("/MSIE 9.0*/", $userAgent) ) {
            $browser = "ie9"; //"explorer9";
        }else if ( preg_match("/MSIE 10.0*/", $userAgent) ) {
            $browser = "ie10"; //"explorer10";
        }else{
            // 익스플로러 기타
            $browser = "ie100"; //"explorerETC";
        }
    }
    else if(preg_match("/Trident*/", $userAgent) && preg_match("/rv:11.0*/", $userAgent) && preg_match("/Gecko*/", $userAgent)){
        $browser = "ie11"; //"explorer11";
    }

    else if ( preg_match("/Edge*/", $userAgent) ) {
        // 엣지
        $browser = "edge";
    }
    else if ( preg_match("/Firefox*/", $userAgent) ) {
        // 모질라 (파이어폭스)
        $browser = "firefox";
    }
    //else if ( preg_match("/(Mozilla)*/", $userAgent) ) {
    // // 모질라 (파이어폭스)
    // $browser = "mozilla";
    //}
    //else if ( preg_match("/(Nav|Gold|X11|Mozilla|Nav|Netscape)*/", $userAgent) ) {
    // // 네스케이프, 모질라(파이어폭스)
    // $browser = "Netscape/mozilla";
    //}
    else if ( preg_match("/Safari*/", $userAgent) && preg_match("/WOW/", $userAgent) ) {
        // 사파리
        $browser = "safari";
    }
    else if ( preg_match("/OPR*/", $userAgent) ) {
        // 오페라
        $browser = "opera";
    }
    else if ( preg_match("/DaumApps*/", $userAgent) ) {
        // daum
        $browser = "daum";
    }
    else if ( preg_match("/KAKAOTALK*/", $userAgent) ) {
        // kakaotalk
        $browser = "kakaotalk";
    }
    else if ( preg_match("/NAVER*/", $userAgent) ) {
        // kakaotalk
        $browser = "naver";
    }
    else if ( preg_match("/Whale*/", $userAgent) ) {
        // 크롬
        $browser = "whale";
    }
    else if ( preg_match("/Chrome/", $userAgent) 
        && !preg_match("/Whale/", $userAgent) 
        && !preg_match("/WOW/", $userAgent) 
        && !preg_match("/OPR/", $userAgent) 
        && !preg_match("/DaumApps/", $userAgent) 
        && !preg_match("/KAKAOTALK/", $userAgent) 
        && !preg_match("/NAVER/", $userAgent) 
        && !preg_match("/Edge/", $userAgent) ) {
        // 크롬
        $browser = "chrome";
    }
    
    else{
        $browser = "other";
    }
    return $browser; //$userAgent;//$browser;
}
}


//접속한 디바이스 타입
if (!function_exists('tms_deviceCheck')){
function tms_deviceCheck(){
    if( stristr($_SERVER['HTTP_USER_AGENT'],'ipad') ) {
        $device = "ipad";
    } else if( stristr($_SERVER['HTTP_USER_AGENT'],'iphone') ||
        strstr($_SERVER['HTTP_USER_AGENT'],'iphone') ) {
        $device = "iphone";
    } else if( stristr($_SERVER['HTTP_USER_AGENT'],'blackberry') ) {
        $device = "blackberry";
    } else if( stristr($_SERVER['HTTP_USER_AGENT'],'android') ) {
        $device = "android";
    } else {
        $device = "etc";
    }
    return $device;
}
}

//위젯 해당 첨부파일의 썸네일 삭제
if(!function_exists('delete_wgt_thumbnail')){
function delete_wgt_thumbnail($wgt_idx, $fle_type, $file)
{
    if(!$wgt_idx || !$fle_type || !$file)
        return;

    $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(G5_DATA_NDR_PATH.'/file/'.$wgt_idx.'/'.$fle_type.'/thumb-'.$fn.'*');
    if (is_array($files)) {
        foreach ($files as $filename)
            unlink($filename);
    }
}
}

//환경선택박스
if(!function_exists('tms_select_selected')){
function tms_select_selected($field, $name, $val, $no_val=0, $required=0, $disable=0){
    $tmsf_values = explode(',', preg_replace("/\s+/", "", $field));
    if(!count($tmsf_values)) return false;
    $readonly_str = ($disable) ? 'readonly onFocus="this.initialSelect=this.selectedIndex;" onChange="this.selectedIndex=this.initialSelect;"' : '';
    if($disable)
        $select_tag = '<select '.$readonly_str.' name="'.$name.'" id="'.$name.'"'.(($required) ? ' required':'').' class="'.(($required) ? 'required':'').'">'.PHP_EOL;
    else
        $select_tag = '<select name="'.$name.'" id="'.$name.'"'.(($required) ? ' required':'').' class="'.(($required) ? 'required':'').'">'.PHP_EOL;
        
    $i = 0;
    if($no_val){ //값없는 항목이 존재할때
        $select_tag .= '<option value=""'.((!$val) ? ' selected="selected"' : '').'>선택안됨</option>'.PHP_EOL;
        $i++;
    }
    foreach ($tmsf_values as $tmsf_value) {
        list($key, $value) = explode('=', $tmsf_value);
        $selected = '';
        if($val){ //수정값이 존재하면
            if(is_int($key)){
                $selected = ((int) $val===$key) ? ' selected="selected"' : '';
            }else{
                $selected = ($val===$key) ? ' selected="selected"' : '';
            }
        }else{ //등록 또는 수정값이 존재하지 않은면
            if(!$no_val){//값없는 항목이 존재하지 않을때
                if($i == 0) $selected = ' selected="selected"';
            }
        }
        $select_tag .= '<option value="'.trim($key).'"'.$selected.'>'.trim($value).'</option>'.PHP_EOL;
        $i++;
    }
    $select_tag .= '</select>'.PHP_EOL;
    $i = 0;
    return $select_tag;
}
}

//환경라디오박스
if(!function_exists('tms_radio_checked')){
function tms_radio_checked($field, $name, $val, $disable=0){ //인수('pending=대기,ok=정상,hide=숨김,trash=삭제','bwgf_status','ok',1)	
    $bwgf_values = explode(',', preg_replace("/\s+/", "", $field));
    if(!count($bwgf_values)) return false;
    $i = 0;
    $name = ' '.$name;
    $radio_tag .= (count($bwgf_values) >= 2) ? '<div style="display:inline-block;">' : '';
    foreach ($bwgf_values as $bwgf_value) {
        list($key, $value) = explode('=', $bwgf_value);
        $checked = '';
        $first_child = ($i == 0) ? ' first_child' : '';
        if($val){ //수정값이 존재하면
            if(is_int($key)){
                $checked = ((int) $val===$key) ? ' checked="checked"' : '';
            }else{
                $checked = ($val===$key) ? ' checked="checked"' : '';
            }
        }else{ //등록 또는 수정값이 존재하지 않은면
            if($i == 0) $checked = ' checked="checked"';
        }
        
        $disabled = ($disable) ? ' onclick="return(false);"' : '';
        
        $radio_tag .= '<label for="'.trim($name).'_'.$key.'" class="label_radio'.$first_child.$name.'"><input type="radio" id="'.trim($name).'_'.$key.'" name="'.trim($name).'" value="'.$key.'"'.$checked.$disabled.'><strong></strong><span>'.$value.'</span></label>'.PHP_EOL;
        $i++;
    }
    $radio_tag .= (count($bwgf_values) >= 2) ? '</div>' : '';
    $i = 0;
    return $radio_tag;
}
}
//환경체크박스
if(!function_exists('tms_check_checked')){
function tms_check_checked($name, $label, $val, $default_chk=0){ //네임속성값,라벨텍스트,값,기본값on/off(값이 없을때)
    global $w;
    
    $checked = '';
    if($val){ //수정값이 존재하면
        if($val == 1 || $val == 'on' || $val == 'ON' || $val == 'checked' || $val == 'CHECKED' || $val == 'check' || $val == 'CHECK' || $val == '체크' || $val == '첵크' || $val == 'ok' || $val == 'OK' || $val >= 2)
            $checked = ' checked="checked"';
    }else{ //등록 또는 수정값이 존재하지 않은면
        if($w == '' && $default_chk == 1) $checked = ' checked="checked"';
    }
    $check_tag = '<label for="'.$name.'" class="label_checkbox '.$name.'"><input type="checkbox" id="'.$name.'" name="'.$name.'" value="'.$val.'"'.$checked.'><strong></strong><span>'.$label.'</span></label>'.PHP_EOL;
    return $check_tag;
}
}

//내부url은 G5_URL을 추가해서 반환
if(!function_exists('tms_g5_url_check')){
function tms_g5_url_check($url){
    $complete_url = $url;
    if(substr($url,0,1) == '/' || substr($url,0,1) == '#' || substr($url,0,1) == '?'){
        $complete_url = G5_URL.$url;
    }
    
    return $complete_url;
}
}
```
### extend/z.05.auth.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// 기본관리자는 관리자페이지 접근가능
$is_adm_accessable = ($is_admin && !$member['mb_leave_date'] && !$member['mb_intercept_date']) ? true : false;

// 내가 접근가능한 메인메뉴의 코드를 배열로 저장
$auth_sql = " SELECT DISTINCT LEFT(au_menu, 3) AS menu_cd
                FROM {$g5['auth_table']}
                WHERE mb_id = '{$member['mb_id']}'
                ORDER BY menu_cd ";
$auth_res = sql_query($auth_sql,1);
$member_auth_menus = array();
if($auth_res->num_rows && !$member['mb_leave_date'] && !$member['mb_intercept_date']){
    $is_adm_accessable = true; //사원회원이 관리자페이지에 접근가능한 상태
    while($auth_row = sql_fetch_array($auth_res)){
        array_push($member_auth_menus,'menu'.$auth_row['menu_cd']);
    }
}
unset($auth_sql);
unset($auth_res);

// memeber일 경우 meta_table에 회원정보가 있으면 $member배열에 추가
if($is_member){
    $mta_mb_arr = get_meta('member',$member['mb_id']);
    if(count($mta_mb_arr)){
        $member = array_merge($member,$mta_mb_arr);
    }
}
unset($mta_mb_arr);

// 수퍼관리자 여부
$is_super = ($member['mb_level'] >= 9) ? true : false;
// 관리자 여부
$is_manager = ($member['mb_level'] >= 8) ? true : false;
```
### extend/z.06.default.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// URL에서 디렉토리명, 파일명 추출
//echo basename($_SERVER["SCRIPT_FILENAME"]);
$path_info=pathinfo($_SERVER['SCRIPT_FILENAME']);
$path_info['dirname'] = preg_replace("/\\\/", "/", $path_info['dirname']);
$g5['dir_name'] = substr($path_info['dirname'],strrpos($path_info['dirname'],'/')+1,strlen($path_info['dirname']));
$g5['dir_path'] = preg_replace("|".G5_PATH."|", "", $path_info['dirname']);
$g5['file_name'] = $path_info['filename'];
$g5['file_path'] = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/'.$g5['file_name']));

// 사이트의 루트 디렉토리명을 추출
$root_path_arr = explode('/',$_SERVER['DOCUMENT_ROOT']);
$g5['root_dir'] = $root_path_arr[sizeof($root_path_arr)-1];
unset($root_path_arr);
// 웹 index인지를 확인하는 변수
$g5['is_web_index'] = ($g5['dir_name'] == $g5['root_dir'] && $g5['file_name'] == 'index') ? 1 : 0;
// 쇼핑몰 index인지를 확인하는 변수
$g5['is_shop_index'] = ($g5['dir_name'] == 'shop' && $g5['file_name'] == 'index') ? 1 : 0;
// 또는 웹/쇼핑몰 구분없이 index(단, 그 외 다른경로의 index는 제외)인지를 확인하는 변수
$g5['is_index'] = ($g5['is_web_index'] || $g5['is_shop_index']) ? 1 : 0;

include_once(G5_ZSQL_PATH.'/set_conf.php');
include_once(G5_ZSQL_PATH.'/set_com.php');

// 접근가능한 IP인지, 접근차단된 IP인지 확인 접근불가능시 차단메세지 출력
if(!$is_admin && $g5['dir_name'] != 'bbs' && $g5['file_name'] != 'login'){
    // 접근가능한 IP인지 확인
    // $set_possible_ip = trim($set_com['set_possible_ip']);
    $set_possible_ip = isset($set_com['set_possible_ip']) ? trim($set_com['set_possible_ip']) : '';

    if($set_possible_ip){
        $is_possible_ip = false;
        $pattern = explode("\n", $set_possible_ip);
        for ($i=0; $i<count($pattern); $i++) {
            $pattern[$i] = trim($pattern[$i]);
            if (empty($pattern[$i]))
                continue;

            $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
            $pattern[$i] = str_replace("+", "[0-9\.]+", $pattern[$i]);
            $pat = "/^{$pattern[$i]}$/";
            $is_possible_ip = preg_match($pat, $_SERVER['REMOTE_ADDR']);
            if ($is_possible_ip)
                break;
        }
        if (!$is_possible_ip){
            die ("<meta charset=utf-8>접근이 가능하지 않습니다.");
        }
    }

    // 접근차단 IP
    $is_intercept_ip = false;
    $pattern = isset($set_com['set_intercept_ip']) ? explode("\n", trim($set_com['set_intercept_ip'])) : array();
    for ($i=0; $i<count($pattern); $i++) {
        $pattern[$i] = trim($pattern[$i]);
        if (empty($pattern[$i]))
            continue;
        
        $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
        $pattern[$i] = str_replace("+", "[0-9\.]+", $pattern[$i]);
        $pat = "/^{$pattern[$i]}$/";
        $is_intercept_ip = preg_match($pat, $_SERVER['REMOTE_ADDR']);
        if ($is_intercept_ip){
            die ("<meta charset=utf-8>접근 불가합니다.");
        }
    }
}

// 관리자가 아니면서, 준비중이면서, bbs폴더가 아니면서, login페이지가 아니면 준비중 페이지로 이동
// if(!$is_admin && $set_conf['set_preparing_yn'] && $g5['dir_name'] != 'bbs' && $g5['file_name'] != 'login'){
//     include_once(G5_PATH.'/preparing.php');
//     exit;
// }

if(defined('G5_IS_ADMIN') && is_file(G5_Z_PATH.'/_adm_custom.php')){
    include_once(G5_ZSQL_PATH.'/set_menu.php');//솔루션 환경설정에서 menu에 해당하는 데이터를 가져온다.
    include_once(G5_Z_PATH.'/_adm_custom.php');
    // 관리자단 공통적으로 사용되는 tailwind스타일시트
    if(is_file(G5_Z_PATH.'/css/_common.css.php')) 
        include_once(G5_Z_PATH.'/css/_common.css.php');
    // _z01개별페이지에 필요한 taiwind스타일시트
    if(is_file(G5_ADMIN_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php')) 
        include_once(G5_ADMIN_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php');
    // adm후킹개별페이지에 필요한 tailwind스타일시트
	if(is_file(G5_Z_PATH.'/_'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php'))
        include_once(G5_Z_PATH.'/_'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php'); 
    // shop_admin후킹개별페이지에 필요한 tailwind스타일시트
	if(is_file(G5_ZADM_PATH.'/_'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php'))
        include_once(G5_ZADM_PATH.'/_'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css.php');
}
if(!defined('G5_IS_ADMIN') && defined('G5_THEME_PATH') && is_file(G5_NA_PATH.'/_theme_custom.php')){
    include_once(G5_NA_PATH.'/_theme_custom.php');
    // 사용자단 공통적으로 사용되는 tailwind스타일시트
    if(is_file(G5_NA_PATH.'/css/_common.css.php')) 
        include_once(G5_NA_PATH.'/css/_common.css.php');
    // _a 후킹개별페이지에 필요한 tailwind스타일시트
	if(is_file(G5_NA_PATH.'/css/'.$g5['file_name'].'.css.php'))
        include_once(G5_Z_PATH.'/css/'.$g5['file_name'].'.css.php'); 
    // _a/bbs 또는 _a/shop 후킹개별페이지에 필요한 tailwind스타일시트
	if(is_file(G5_NA_PATH.'/css/'.$g5['dir_name'].'/'.$g5['file_name'].'.css.php'))
        include_once(G5_Z_PATH.'/css/'.$g5['dir_name'].'/'.$g5['file_name'].'.css.php'); 
}


//브라우저
$g5['browser_name'] = tms_browserCheck();
//echo $g5['browser_name'];
//익스여부
$g5['is_explorer'] = tms_is_explorer();
//echo $g5['is_explorer'];
//익스버전
$g5['ie_version'] = 0;
if (preg_match("/ie/", $g5['browser_name']) && $g5['is_explorer']){
	$g5['ie_version'] = (int) substr($g5['browser_name'],2);
}

//실제모바일 디바이스여부
$g5['is_real_mobile'] = is_mobile();

//브라우저 기본정보
$g5['user_agent'] = $_SERVER["HTTP_USER_AGENT"];
//echo $g5['user_agent'];

//디바이스 타입
$g5['device_type'] = tms_deviceCheck();
//echo $g5['device_type'];

//PC유사한 디바이스인가?
$g5['is_device_etc'] = ($g5['device_type'] == 'etc') ? 1 : 0;

//안드로이드 디바이스인가?
$g5['is_device_android'] = ($g5['device_type'] == 'android') ? 1 : 0;

//iphone 디바이스인가?
$g5['is_device_iphone'] = ($g5['device_type'] == 'iphone') ? 1 : 0;

//ipad 디바이스인가?
$g5['is_device_ipad'] = ($g5['device_type'] == 'ipad') ? 1 : 0;

//blackberry 디바이스인가?
$g5['is_device_blackberry'] = ($g5['device_type'] == 'blackberry') ? 1 : 0;

//DB에 dain_default_table이 존재하는지 확인하고 없으면 설치
if(!pg_table_exists($g5['dain_default_table'])){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_dain_default.php');
}

//DB에 setting_table이 존재하는지 확인하고 없으면 설치
if(!pg_table_exists($g5['setting_table'])){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_setting.php');
}

//DB에 meta_table이 존재하는지 확인하고 없으면 설치
if(!pg_table_exists($g5['meta_table'])){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_meta.php');
}


//DB에 file_table이 존재하는지 확인하고 없으면 설치
if(!pg_table_exists($g5['dain_file_table'])){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_dain_file.php');
}


//PostgreSQL DB에 term_table이 존재하는지 확인하고 없으면 설치
// if(!pg_table_exists($g5['term_table'])){
// 	// 테이블 생성 ------------------------------------
// 	include_once(G5_ZSET_PATH.'/db_term.php');
// }
//MySQL DB에 term_table이 존재하는지 확인하고 없으면 설치
$chk_db_tbl = @sql_query(" DESC ".$g5['term_table']." ", false);
if(!$chk_db_tbl){
	// 테이블 생성 ------------------------------------
	include_once(G5_ZSET_PATH.'/db_term.php');
}
unset($chk_db_tbl);



//app data/app_asset
$data_app_asset_dir_path = G5_DATA_PATH.'/app_asset';
$app_asset_permission_str = "chmod 707 -R ".$data_app_asset_dir_path;
if(!is_dir($data_app_asset_dir_path)){
    @mkdir($data_app_asset_dir_path, G5_DATA_Z_PERMISSION);
    @chmod($data_app_asset_dir_path, G5_DATA_Z_PERMISSION);

    exec($app_asset_permission_str);
}

// data폴더에 ndr폴더(각종 파일을 저장하는 디렉토리)생성
$data_ndr_dir_path = G5_DATA_PATH.'/ndr';
$ndr_permission_str = "chmod 707 -R ".$data_ndr_dir_path;
if(!is_dir($data_ndr_dir_path)){
    @mkdir($data_ndr_dir_path, G5_DATA_Z_PERMISSION);
    @chmod($data_ndr_dir_path, G5_DATA_Z_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/set';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/file';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/board';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/main';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/content';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/banner';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/shop';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/seo';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    $data_ndr_in_dir_path = $data_ndr_dir_path.'/temp';
    @mkdir($data_ndr_in_dir_path, G5_DIR_PERMISSION);
    @chmod($data_ndr_in_dir_path, G5_DIR_PERMISSION);

    exec($ndr_permission_str);
}

unset($data_ndr_dir_path);
unset($data_ndr_in_dir_path);
unset($ndr_permission_str);

// 검색어 필터링추가
if (isset($_REQUEST['sfl2']))  {
    $sfl2 = trim($_REQUEST['sfl2']);
    $sfl2 = preg_replace("/[\<\>'\"'\\\"\%\=\(\)\/\^\*\s]/", "", $sfl2);
    if ($sfl2)
        $qstr .= '&amp;sfl=' . urlencode($sfl2); // search field (검색 필드)
} else {
    $sfl2 = '';
}
// 검색어 추가
if (isset($_REQUEST['stx2']))  { // search text (검색어)
    $stx2 = get_search_string(trim($_REQUEST['stx2']));
    if ($stx2 || $stx2 === '0')
        $qstr .= '&amp;stx=' . urlencode(cut_str($stx2, 20, ''));
} else {
    $stx2 = '';
}
// 검색시 정렬 필드 추가
if (isset($_REQUEST['sst2']))  {
    $sst2 = trim($_REQUEST['sst2']);
    $sst2 = preg_replace("/[\<\>'\"'\\\"\%\=\(\)\/\^\*\s]/", "", $sst2);
    if ($sst2)
        $qstr .= '&amp;sst2=' . urlencode($sst2); // search sort (검색 정렬 필드)
} else {
    $sst2 = '';
}
// 검색시 정렬 순서타입 추가
if (isset($_REQUEST['sod2']))  { // search order (검색 오름, 내림차순)
    $sod2 = preg_match("/^(asc|desc)$/i", $sod2) ? $sod2 : '';
    if ($sod2)
        $qstr .= '&amp;sod2=' . urlencode($sod2);
} else {
    $sod2 = '';
}

// 검색시 정렬 필드 추가
if (isset($_REQUEST['sst3']))  {
    $sst3 = trim($_REQUEST['sst3']);
    $sst3 = preg_replace("/[\<\>'\"'\\\"\%\=\(\)\/\^\*\s]/", "", $sst3);
    if ($sst3)
        $qstr .= '&amp;sst3=' . urlencode($sst3); // search sort (검색 정렬 필드)
} else {
    $sst3 = '';
}
// 검색시 정렬 순서타입 추가
if (isset($_REQUEST['sod3']))  { // search order (검색 오름, 내림차순)
    $sod3 = preg_match("/^(asc|desc)$/i", $sod3) ? $sod3 : '';
    if ($sod3)
        $qstr .= '&amp;sod3=' . urlencode($sod3);
} else {
    $sod3 = '';
}
```

**설명**: 
각각의 제목은 파일명을 작성한 것으로 앞에 '/'로 파일의 경로위치를 명시할 수 있습니다.
'/'가 명시되어 있지 않으면 project root에 위치한 파일이라고 보면 됩니다.
아래의 기술한 소스코드파일들은 'adm/_z01/'디렉토리의 하위에 존재하는 모든 파일을 정리하였습니다. 

### adm/_z01/_adm_custom.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//add_event('common_header', 'bpwg_adm_head_file_include',10);
add_event('admin_common', 'z_adm_common_head',10);
add_event('tail_sub', 'z_adm_common_tail', 10);
function z_adm_common_head(){
	global $g5,$member,$default,$config,$set_menu,$set_conf,$set_com,$menu,$menu2,$sub_menu,$co_id,$w,$pg_anchor,$member_auth_menus,$menu_main_titles,$menu_list_tag;

	// 관리자 index.php 페이지는 _z01/_adm/index.php로 리다이렉트
	if($g5['dir_name'] == 'adm' && $g5['file_name'] == 'index'){
		header("location:".G5_ZADM_URL);
		exit;
	}

	$menu2 = $menu;//메뉴배열을 복사 - $menu는 권한에 따라 삭제된다. (employee_form.php에서 사용하기 위해)
	// print_r2($set_menu['set_hide_submenus_arr']);exit;
	// 해당 회원의 접근권한이 없는 메뉴코드는 메뉴배열에서 삭제
	$tmp_menu = array();
	if($config['cf_admin'] != $member['mb_id']){
		foreach($menu as $mcd => $marr){
			// $member_auth_menus는 z.04.auth.php에서 정의된 배열로, 해당 회원이 접근가능한 메뉴코드를 배열로 저장
			if(in_array($mcd, $set_menu['set_hide_mainmenus_arr']) || !in_array($mcd,$member_auth_menus)){
				unset($menu[$mcd]); //접근 권한이 없는 메뉴코드는 메뉴배열에서 삭제
				continue;
			}
			// echo $mcd.BR;
			$tmp_menu[$mcd] = array();
			if(count($menu[$mcd])){
				// print_r2($menu[$mcd]);
				foreach($menu[$mcd] as $i => $v){
					// echo $menu[$mcd][$i][0].BR;
					if(in_array($menu[$mcd][$i][0], $set_menu['set_hide_submenus_arr'])){
						unset($menu[$mcd][$i]); //접근 권한이 없는 서브메뉴코드는 메뉴배열에서 삭제
						continue;
					}
					// echo $menu[$mcd][$i][0].BR;
					// tmp_menu배열에 저장하는 이유는 $menu[$mcd]에서의 
					// [$i]가 중간에 누락된 index가 있을 경우가 있으므로 제대로 된 배열로 만들어 다시 $menu에 재대입하기 위함
					$tmp_menu[$mcd][] = $menu[$mcd][$i];
				}
			}
		}
		// print_r2($tmp_menu);
		// exit;
		$menu = $tmp_menu;
	}
	// print_r2($menu);
	// exit;
	// 해당 후킹디렉토리 위치에 동일한 파일이 있으면 해당 $menu배열 요소의 url경로가 후킹url경로로 변경된다.
	foreach($menu as $k => $v){
		if(count($menu[$k])){
			for($i=0;$i<count($menu[$k]);$i++){
				$dir_file_arr = explode('/',$menu[$k][$i][2]);
				$adir = $dir_file_arr[count($dir_file_arr)-2];
				$afile = $dir_file_arr[count($dir_file_arr)-1];
				$a_h_file_path = G5_Z_PATH.'/_'.$adir.'/'.$afile;
				$a_h_file_url = G5_Z_URL.'/_'.$adir.'/'.$afile;
				$as_h_file_path = G5_ZADM_PATH.'/_'.$adir.'/'.$afile;
				$as_h_file_url = G5_ZADM_URL.'/_'.$adir.'/'.$afile;
				if(is_file($a_h_file_path) && !is_file($as_h_file_path)){
					$menu[$k][$i][2] = $a_h_file_url;
				}else if(!is_file($a_h_file_path) && is_file($as_h_file_path)){
					$menu[$k][$i][2] = $as_h_file_url;
				}
			}
		}
	}
	
	// employee_form.php에서 사원별 관리자메뉴의 접근권한을 설정하기 위해 사용되는 데이터
	$menu_list_tag = '<ul class="ul1_menu">'.PHP_EOL;
	$menu_main_titles = array();
	foreach($menu2 as $k => $v){
		$menu_list_tag .= '<li class="li1_menu">'.PHP_EOL;
		$menu_list_tag .= '<span>##['.$k.']##</span>'.PHP_EOL;
		if(count($menu2[$k])){
			$menu_list_tag .= '<ul class="ul2_menu">'.PHP_EOL;
			for($i=0;$i<count($menu2[$k]);$i++){
				if($i == 0) $menu_main_titles[$k] = $menu2[$k][$i][1];
				$menu_list_tag .= '<li class="li2_menu inline-block">'.PHP_EOL;
				if (isset($menu[$k][$i])) {
					$menu_list_tag .= ' <div>(' . $menu[$k][$i][0] . ':' . $menu[$k][$i][1] . ')</div>'.PHP_EOL;
				} else {
					$menu_list_tag .= ' <div>(접근불가 또는 메뉴 없음)</div>'.PHP_EOL;
				}
				// $menu_list_tag .= ' <div>('.$menu[$k][$i][0].':'.$menu[$k][$i][1].')</div>'.PHP_EOL;
				$menu_list_tag .= '</li>'.PHP_EOL;
			}
			$menu_list_tag .= '</ul>'.PHP_EOL;
		}
		$menu_list_tag .= '</li>'.PHP_EOL;
	}
	$menu_list_tag .= '</ul>'.PHP_EOL;
}


function z_adm_common_tail(){
	global $g5,$member,$default,$set_conf,$set_com,$config,$menu,$sub_menu,$co_id,$w,$pg_anchor;
	
	//대체한 DOM요소를 일단 표시
	@include_once(G5_ZREPLACE_PATH.'/admin.head.php');
	echo ''.PHP_EOL;
	echo '<script>'.PHP_EOL;
	echo 'const amenu = '.json_encode($menu).';'.PHP_EOL;
	echo 'const file_name = "'.$g5['file_name'].'";'.PHP_EOL;
	echo 'const dir_name = "'.$g5['dir_name'].'";'.PHP_EOL;
	echo 'const mb_name = "'.$member['mb_name'].'";'.PHP_EOL;
	echo 'const mb_level = "'.$member['mb_level'].'";'.PHP_EOL;
	echo 'const g5_community_use = "'.G5_COMMUNITY_USE.'"'.PHP_EOL;
	echo '</script>'.PHP_EOL;
	// jquery-ui 스타일시트
	add_stylesheet('<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">',0);
	// 부트스트랩 아이콘
	add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">',0);
	// adm 공통으로 적용할 커스텀 스타일시트
	if(is_file(G5_Z_PATH.'/css/adm_common_custom.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/adm_common_custom.css">',0);
	// 기존의 스타일을 재정의하는 스타일시트
	if(is_file(G5_Z_PATH.'/css/adm_override.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/adm_override.css">',0);
	// 추가적인 스타일시트
	if(is_file(G5_Z_PATH.'/css/adm_add.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/adm_add.css">',0);
	// _z01개별페이지에 필요한 스타일시트
	if(is_file(G5_ADMIN_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css">',0);
	// adm후킹개별페이지에 필요한 스타일시트
	if(is_file(G5_Z_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css">',0);
	// shop_admin후킹개별페이지에 필요한 스타일시트
	if(is_file(G5_ZADM_PATH.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_ZADM_URL.'/'.$g5['dir_name'].'/css/'.$g5['file_name'].'.css">',0);
	
	// jquery-ui
	add_javascript('<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>',0);
	// 관리자단에 필요한 함수를 정의한 파일
	if(is_file(G5_Z_PATH.'/js/adm_func.js')) add_javascript('<script src="'.G5_Z_URL.'/js/adm_func.js"></script>',0);
	if(is_file(G5_Z_PATH.'/js/tms_datepicker.js')) add_javascript('<script src="'.G5_Z_URL.'/js/tms_datepicker.js"></script>',0);
	if(is_file(G5_Z_PATH.'/js/tms_timepicker.js')) add_javascript('<script src="'.G5_Z_URL.'/js/tms_timepicker.js"></script>',0);
	// tailwindcss
	if(is_file(G5_Z_PATH.'/js/tailwind.min.js')) add_javascript('<script src="'.G5_Z_URL.'/js/tailwind.min.js"></script>',0);
	// _z01안에 DOM객체의 편집이 필요할때 사용하느 js파일
	if(is_file(G5_Z_PATH.'/js/adm_dom_control.js.php')) include_once(G5_Z_PATH.'/js/adm_dom_control.js.php');
	// _z01개별페이지에 필요한 js파일
	if(is_file(G5_Z_PATH.'/js/'.$g5['file_name'].'.js.php')) include_once(G5_Z_PATH.'/js/'.$g5['file_name'].'.js.php');
	// adm후킹개별페이지에 필요한 js파일
	if(is_file(G5_ZADM_PATH.'/js/'.$g5['file_name'].'.js.php')) include_once(G5_ZADM_PATH.'/js/'.$g5['file_name'].'.js.php');
	// shop_admin후킹개별페이지에 필요한 js파일
	if(is_file(G5_ZADM_PATH.'/_shop_admin/'.$g5['file_name'].'.js.php')) include_once(G5_ZADM_PATH.'/_shop_admin/'.$g5['file_name'].'.js.php');
	// sms_admin후킹개별페이지에 필요한 js파일
	if(is_file(G5_ZADM_PATH.'/_sms_admin/'.$g5['file_name'].'.js.php')) include_once(G5_ZADM_PATH.'/_sms_admin/'.$g5['file_name'].'.js.php');
}
```
### adm/_z01/_common.php
```php
<?php
define('G5_IS_ADMIN', true);
include_once ('../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

run_event('admin_common');
```
### adm/_z01/_win_company_select.php
```php
<?php
include_once('./_common.php');

$sql_common = " FROM {$g5['company_table']} AS com";

$where = array();
// $where[] = " com_type = '1' ";
$where[] = " com_status NOT IN ('trash','close','stop','prohibit') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case 'com_idx' : 
			$where[] = " com_idx = '{$stx}' ";
			break;
		case 'com_name' :
            $where[] = " ( com_name LIKE '%{$stx}%' OR com_names LIKE '%{$stx}%' ) ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "com_reg_dt";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

// 테이블의 전체 레코드수만 얻음
$sql = " SELECT COUNT(*) as cnt " . $sql_common.$sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 6;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT 
			com_idx,
			com_name,
			com_president
		{$sql_common}
		{$sql_search}
		{$sql_order}
		LIMIT {$from_record}, {$rows} 
";
$result = sql_query($sql,1);
$rcnt = $result->num_rows;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '업체목록';

include_once(G5_PATH.'/head.sub.php');
?>
<style>
html,body{overflow:hidden;}
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}
</style>
<div class="new_win">
	<?php if(G5_IS_MOBILE){ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></a>
	<?php }else{ ?>
	<a href="javascript:" class="btn btn_02 btn_close" onclick="window.close()">닫기</a>
	<?php } ?>
	<h1><?php echo $g5['title']; ?></h1>
	<div id="com_sch_list" class="new_win">
		<div class="local_ov01 local_ov">
			<?php echo $listall ?>
			<span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
		</div>
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch py-2" method="get">
		<label for="sfl" class="sound_only">검색대상</label>
		<select name="sfl" id="sfl" style="">
			<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
			<option value="com_idx"<?php echo get_selected($_GET['sfl'], "com_idx"); ?>>업체번호</option>
		</select>
		<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
		<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="width:130px;">
		<input type="submit" class="btn_submit" value="검색">
		
		</form>
		<div class="tbl_head01 tbl_wrap">
			<table class="table table-bordered table-condensed">
			<caption><?php echo $g5['title']; ?> 목록</caption>
			<thead>
				<th scope="col">업체번호</th>
				<th scope="col">업체명</th>
				<th scope="col">대표</th>
				<th scope="col">관리</th>
			</thead>
			<tbody>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++){
				//print_r2($row);
				$choice = '<a href="javascript:" class="a_mag btn btn_02" com_idx="'.$row['com_idx'].'" com_name="'.$row['com_name'].'">선택</a>';
			?>
				<tr>
				<td class="td_com_idx"><?=$row['com_idx']?></td>
				<td class="td_com_name"><!-- 업체명 -->
					<b><?php echo get_text($row['com_name']); ?></b>
                    <?php if($row['com_branch']){ ?>
                    <br>(<?=$row['com_branch']?>)
                    <?php } ?>
				</td>
				<td class="td_com_mgn"><!-- 마진 -->
					<b><?php echo get_text($row['com_president']); ?></b>
				</td>
				<td class="td_mng" style="text-align:center;"><!-- 관리 -->
					<?=$choice?>
				</td>
				</tr>
			<?php
			}
			if ($rcnt == 0){
				echo "<tr><td class='td_empty' colspan='4'>".PHP_EOL;
				echo "자료가 없습니다.<br>".PHP_EOL;
				echo '<a href="'.G5_Z_URL.'/company_form.php" target="_blank" class="ov_listall" style="margin-top:5px;">업체등록</a>'.PHP_EOL;
				echo "</td></tr>".PHP_EOL;
			}
			?>
			</tbody>
			</table>
		</div>
		<?php
		echo get_paging($config['cf_mobile_pages'], $page, $total_page, '?'.$qstr);
		?>
	</div><!--#com_sch_list-->
</div><!--.new_win-->
<script>
$('body').attr({'onresize':'parent.resizeTo(500,640)','onload':'parent.resizeTo(500,640)'});
$('.a_mag').on('click',function(){
    <?php if($file_name == 'company_form'){ ?>
    opener.document.getElementById('com_idx_parent').value = $(this).attr('com_idx');
    opener.document.getElementById('com_name_parent').value = $(this).attr('com_name');
    <?php }else{ ?>
	opener.document.getElementById('com_idx').value = $(this).attr('com_idx');
	opener.document.getElementById('com_name').value = $(this).attr('com_name');
    <?php } ?>
	window.close();
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
```
### adm/_z01/company_form_update.php
```php
<?php
$sub_menu = "960200";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');
//check_admin_token();

if(!trim($_POST['com_name'])) alert('업체명을 입력해 주세요.');
if(!trim($_POST['com_email'])) alert('이메일을 입력해 주세요.');
if(!trim($_POST['com_president'])) alert('대표자명을 입력해 주세요.');
if(!trim($_POST['com_tel'])) alert('업체전화번호를 입력해 주세요.');

$com_name = trim($_POST['com_name']);
$com_name_eng = trim($_POST['com_name_eng']);
$com_branch = trim($_POST['com_branch']);
$com_email = trim($_POST['com_email']);
$com_url = trim($_POST['com_url']);
$com_president = trim($_POST['com_president']);
$com_tel = trim($_POST['com_tel']);
$com_tel = preg_replace('/[^0-9]/', '', $com_tel); // 전화번호 숫자만 추출
$com_biz_no = trim($_POST['com_biz_no']);
$com_biz_no = preg_replace('/[^0-9]/', '', $com_biz_no); // 사업자번호 숫자만 추출
$com_fax = trim($_POST['com_fax']);
$com_fax = preg_replace('/[^0-9]/', '', $com_fax); // 팩스번호 숫자만 추출
$com_biz_type = trim($_POST['com_biz_type']);
$com_biz_type2 = trim($_POST['com_biz_type2']);
$com_zip = trim($_POST['com_zip']);
$com_addr = trim($_POST['com_addr']);
$com_addr2 = trim($_POST['com_addr2']);
$com_addr3 = trim($_POST['com_addr3']);
$com_addr_jibeon = trim($_POST['com_addr_jibeon']);
$com_longitude = trim($_POST['com_longitude']);
$com_addr_jibeon = trim($_POST['com_addr_jibeon']);
$com_memo = conv_unescape_nl(stripslashes($_POST['com_memo']));

if($com_idx == $com_idx_parent){
    alert('현재업체를 본사업체로 등록할 수 없습니다.');
}

//위도형식에 맞지 않으면 경고창 띄우기
if($com_latitude){
    if(!preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,})?)|(?:[1-8]?\d(?:\.\d{1,})?))$/', $com_latitude)){
        alert('위도의 형식이 올바르지 않습니다.');
    }
}
//경도형식에 맞지 않으면 경고창 띄우기
if($com_longitude){
    if(!preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,})?)|(?:1[0-7]?\d(?:\.\d{1,})?)|(?:\d?\d(?:\.\d{1,})?))$/', $com_longitude)){
        alert('경도의 형식이 올바르지 않습니다.');
    }
}

// 업체정보 추출
if ($w=='u')
	$com = sql_fetch(" SELECT * FROM {$g5['company_table']} WHERE com_idx = '$com_idx' ");


// 업체명 히스토리
if($com['com_name'] != $com_name) {
	$com_names = $com['com_names'].', '.$com_name.'('.substr(G5_TIME_YMD,2).'~)';
}
else {
	$com_names = $_POST['com_names'];
}


$sql_common = "	com_name = '".addslashes($com_name)."'
                , com_branch = '".addslashes($com_branch)."'
                , com_name_eng = '".addslashes($com_name_eng)."'
                , com_names = '".addslashes($com_names)."'
                , com_url = '{$com_url}'
                , com_type = '{$_POST['com_type']}'
                , com_tel = '{$com_tel}'
                , com_fax = '{$com_fax}'
                , com_email = '{$com_email}'
                , com_president = '{$com_president}'
                , com_biz_no = '{$com_biz_no}'
                , com_biz_type = '{$com_biz_type}'
                , com_biz_type2 = '{$com_biz_type2}'
                , com_zip = '{$com_zip}'
                , com_addr = '{$com_addr}'
                , com_addr2 = '{$com_addr2}'
                , com_addr3 = '{$com_addr3}'
                , com_addr_jibeon = '{$com_addr_jibeon}'
                , com_latitude = '{$com_latitude}'
                , com_longitude = '{$com_longitude}'
                , com_memo = '{$com_memo}'
                , com_status = '{$_POST['com_status']}'
";

// API key 생성
// tms_get_random_string('09azAZ',40);
if($key_renewal){
    $com_api_key = tms_get_random_string('09azAZ',40);
    $sql_common .= " , com_api_key = '{$com_api_key}' ";
}
else if($key_clear){
    $sql_common .= " , com_api_key = '' ";
}

$sql_common .= ($head_clear) ? " , com_idx_parent = '' " : " , com_idx_parent = '{$_POST['com_idx_parent']}' ";

// 생성
if ($w == '') {
    // 업체 정보 생성
	$sql = " INSERT into {$g5['company_table']} SET
				{$sql_common}
                , com_reg_dt = '".G5_TIME_YMDHIS."'
                , com_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	$com_idx = sql_insert_id();

}
// 수정
else if ($w == 'u') {

	if (!$com['com_idx'])
		alert('존재하지 않는 업체자료입니다.');
 
    $sql = "	UPDATE {$g5['company_table']} SET 
					{$sql_common}
					, com_update_dt = '".G5_TIME_YMDHIS."'
				WHERE com_idx = '{$com_idx}' 
	";
    sql_query($sql,1);
    //echo $sql.'<br>';
}
else if ($w=="d") {

	if (!$com['com_idx']) {
		alert('존재하지 않는 업체자료입니다.');
	} else {
		// 자료 삭제
        if(!$set_conf['set_del_yn']){
            $sql = " UPDATE {$g5['company_table']} SET com_status = 'trash' WHERE com_idx = $com_idx ";
        }
        else{
            $sql = " DELETE FROM {$g5['company_table']} WHERE com_idx = $com_idx ";
        }
		sql_query($sql,1);
	}
}


if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(@count($com_del)){
        foreach($com_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    if(count($del_arr)) delete_idx_file($del_arr);
    
    //멀티파일처리
    upload_multi_file($_FILES['com_datas'],'company',$com_idx,'com');
}


foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

// exit;
if($w == 'u') {
	//alert('업체 정보를 수정하였습니다.','./company_form.php?'.$qstr.'&amp;w=u&amp;com_idx='.$com_idx, false);
	// alert('업체 정보를 수정하였습니다.','./company_list.php?'.$qstr, false);
    goto_url('./company_list.php?'.$qstr, false);
}
else if($w == 'd') {
    goto_url('./company_list.php?'.$qstr, false);
}
else {
	// alert('업체 정보를 등록하였습니다.','./company_list.php', false);
    goto_url('./company_list.php?'.$qstr, false);
}
```
### adm/_z01/company_form.php
```php
<?php
$sub_menu = "930600";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

$html_title = ($w=='')?'추가':'수정'; 

$g5['title'] = '업체 '.$html_title;
//include_once('./_top_menu_company.php');
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
//echo $g5['container_sub_title'];

if ($w == '') {
    $com_idx = 0;
    $com['com_status'] = 'ok';
    $html_title = '추가';

}
else if ($w == 'u') {
	$com = get_table_meta('company','com_idx',$com_idx);
	if (!$com['com_idx'])
		alert('존재하지 않는 업체자료입니다.');

	$html_title = '수정';

	// 본사 com_idx_parent가 있으면 com_name_parent를 가져온다.
	$pcom = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '{$com['com_idx_parent']}' ");
	$com['com_name_parent'] = ($pcom['com_name']) ? $pcom['com_name'] : '';

	$com['com_name'] = get_text($com['com_name']);
	$com['com_tel'] = get_text($com['com_tel']);
	$com['com_url'] = get_text($com['com_url']);
	$com['com_addr3'] = get_text($com['com_addr3']);
	
	// 관련 파일(post_file) 추출
	// $sql = "SELECT * FROM {$g5['dain_file_table']} 
	// 		WHERE fle_db_tbl = 'company' AND fle_db_idx = '".$com['com_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	// $rs = sql_query($sql,1);

	//관련파일 추출
	$sql = "SELECT * FROM {$g5['dain_file_table']}
        WHERE fle_db_tbl = 'company' AND fle_type = 'com' AND fle_db_idx = '{$com['com_idx']}' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query_pg($sql);
    //echo $rs->num_rows;echo "<br>";
    $com['com_f_arr'] = array();
    $com['com_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
    $com['com_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
    for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
        $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($com['com_f_arr'],array('file'=>$file_down_del));
        @array_push($com['com_fidxs'],$row2['fle_idx']);
    }
	
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_sex');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$com[$check_array[$i]]} = ' checked';
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>

<form name="form01" id="form01" action="./company_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="com_idx" value="<?php echo $com_idx; ?>">
<?=$form_input?>
<div class="local_desc01 local_desc">
    <p>업체정보를 관리해 주세요.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
	</colgroup>
	<tbody>
	<tr>
		<th scope="row">업체명<strong class="sound_only">필수</strong>/지점명</th>
		<td>
			<input type="hidden" name="com_idx" value="<?=$com_idx?>">
			<input type="text" name="com_name" value="<?=$com['com_name']?>" placeholder="업체명" id="com_name" class="frm_input">
			<input type="text" name="com_branch" value="<?=$com['com_branch']?>" placeholder="지점명" id="com_branch" class="frm_input">
		</td>
		<th scope="row">업체구분</th>
		<td>
			<select name="com_type" id="com_type" class="frm_input">
                <?=$set_conf['set_com_type_option']?>
            </select>
            <?php if($w == 'u') { ?>
            <script>$('#com_type').val('<?=$com['com_type']?>');</script>
            <?php } ?>
		</td>
	</tr>
    <tr>
        <th scope="row">업체명(영문)</th>
		<td>
			<input type="text" name="com_name_eng" value="<?=$com['com_name_eng']?>" id="com_name_eng" class="frm_input">
		</td>
        <th scope="row">본사선택</th>
        <td>
			<?php echo help("본사설정을 해제하려면 '본사설정해제'에 체크를 넣고 확인을 눌러 주세요."); ?>
            <input type="hidden" name="com_idx_parent" id="com_idx_parent" value="<?=$com['com_idx_parent']?>">
            <input type="text" name="com_name_parent" id="com_name_parent" value="<?=$com['com_name_parent']?>" readonly class="readonly frm_input">
            <a href="javascript:" data-url="./_win_company_select.php" class="mm-btn com_select">본사선택</a>
			<label for="head_clear" class="ml-2">
                <input type="checkbox" name="head_clear" id="head_clear" value="1" class="border"> 본사설정해제
            </label>
        </td>
    </tr>
	<tr>
		<th scope="row">업체명 히스토리</th>
		<td>
			<?php echo help("업체명이 바뀌면 자동으로 히스토리가 기록됩니다."); ?>
			<input type="<?=($is_admin)?'text':'hidden';?>" name="com_names" value="<?php echo $com['com_names'] ?>" id="com_names" readonly class="readonly frm_input w-[100%]" <?=(!$is_admin)?'readonly':''?>>
            <span style="display:<?=($is_admin)?'none':'';?>"><?php echo $com['com_names'] ?></span>
		</td>
		<th scope="row">API Key</th>
		<td>
			<?php echo help("API Key 할당 또는 갱신할 필요가 있으면 'Key설정'에 체크를 넣고 확인을 눌러 주세요."); ?>
			<input type="text" name="com_api_key" value="<?=$com['com_api_key']?>" id="com_api_key" readonly class="readonly frm_input w-[60%]">
			<label for="key_renewal" class="ml-2 text-blue-600">
				<input type="checkbox" name="key_renewal" id="key_renewal" value="1" class="border"> Key설정
            </label>
			<label for="key_clear" class="ml-2 text-red-600">
				<input type="checkbox" name="key_clear" id="key_clear" value="1" class="border"> Key삭제
            </label>
			<?php echo help("API Key를 삭제하려면 'Key삭제'에 체크를 넣고 확인을 눌러 주세요."); ?>
		</td>
	</tr>
	<tr> 
		<th scope="row">대표이메일<strong class="sound_only">필수</strong></th>
		<td>
			<?php echo help("세금계산서, 계약서, 약정서 등 모든 거래 시 소통할 수 있는 이메일 정보를 필수로 등록하세요."); ?>
			<input type="text" name="com_email" value="<?php echo $com['com_email'] ?>" id="com_email" class="frm_input" style="width:60%;">
			<?=$saler_mark?>
		</td>
		<th scope="row">홈페이지주소</th>
		<td>
			<?php echo help("http(s):// 없이 그냥 홈페이지 주소만 입력해 주세요. ex. www.naver.com "); ?>
			<input type="text" name="com_url" value="<?php echo $com['com_url'] ?>" id="com_url" class="frm_input" style="width:60%">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="com_president">대표자<strong class="sound_only">필수</strong></label></th>
		<td>
			<input type="text" name="com_president" value="<?php echo $com['com_president'] ?>" id="com_president" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
		<th scope="row"><label for="com_tel">업체전화<strong class="sound_only">필수</strong></label></th>
		<td>
			<input type="text" name="com_tel" value="<?=formatPhoneNumber($com['com_tel'])?>" id="com_tel" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<th scope="row">사업자등록번호</th>
		<td>
			<input type="text" name="com_biz_no" value="<?=formatBizNumber($com['com_biz_no'])?>" class="frm_input" size="20" minlength="2" maxlength="12">
		</td>
		<th scope="row">팩스</th>
		<td>
			<input type="text" name="com_fax" value="<?=formatPhoneNumber($com['com_fax'])?>" id="com_fax" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<th scope="row">업종</th>
		<td>
			<input type="text" name="com_biz_type2" value="<?=$com['com_biz_type2']?>" class="frm_input w-[70%]">
		</td>
		<th scope="row">업태</th>
		<td>
			<input type="text" name="com_biz_type" value="<?=$com['com_biz_type']?>" class="frm_input w-[70%]">
		</td>
	</tr>	
	<tr>
		<th scope="row">사업장 주소 <?=$saler_mark?></th>
		<td class="td_addr_line" style="line-height:280%;">
			<?php echo help("사업장 주소가 명확하지 않은 경우 [주소검색]을 통해 정확히 입력해 주세요."); ?>
			<label for="com_zip" class="sound_only">우편번호</label>
			<input type="text" name="com_zip" value="<?php echo $com['com_zip']; ?>" id="com_zip" readonly class="frm_input readonly" maxlength="6" style="width:65px;">
			<?php if(!auth_check($auth[$sub_menu],'d',1) || $w=='') { ?>
			<button type="button" class="btn_frmline" onclick="win_zip('form01', 'com_zip', 'com_addr', 'com_addr2', 'com_addr3', 'com_addr_jibeon');">주소 검색</button>
			<?php } ?>
			<br>
			<input type="text" name="com_addr" value="<?php echo $com['com_addr'] ?>" id="com_addr" readonly class="w-[400px] frm_input readonly">
			<label for="com_addr1">기본주소</label><br>
			<input type="text" name="com_addr2" value="<?php echo $com['com_addr2'] ?>" id="com_addr2" readonly class="w-[400px] frm_input readonly">
			<label for="com_addr2">상세주소</label>
			<br>
			<input type="text" name="com_addr3" value="<?php echo $com['com_addr3'] ?>" id="com_addr3" class="w-[400px] frm_input">
			<label for="com_addr3">참고항목</label>
			<input type="hidden" name="com_addr_jibeon" value="<?php echo $com['com_addr_jibeon']; ?>" id="com_addr_jibeon" class="w-[400px] frm_input">
		</td>
        <th scope="row">업체관련파일</th>
        <td>
            <?php echo help("업체관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file_com" name="com_datas[]" multiple class="">
            <?php
            if(@count($com['com_f_arr'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($com['com_f_arr']);$i++) {
                    echo "<li>[".($i+1).']'.$com['com_f_arr'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
	</tr>
	<tr>
		<th scope="row">위도/경도</th>
		<td>
			<input type="text" name="com_latitude" value="<?=$com['com_latitude']?>" placeholder="위도" id="com_latitude" class="frm_input">
			<input type="text" name="com_longitude" value="<?=$com['com_longitude']?>" placeholder="경도" id="com_longitude" class="frm_input">
		</td>
		<th scope="row"><label for="com_status">상태</label></th>
		<td>
			<?php //echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="com_status" id="com_status">
				<?=$set_conf['set_com_status_option']?>
			</select>
			<script>$('select[name="com_status"]').val('<?=$com['com_status']?>');</script>
		</td>
	</tr>
    <tr>
        <th scope="row"><label for="com_memo">메모</label></th>
        <td colspan="3">
            <textarea name="com_memo" id="mb_memo"><?=$com['com_memo']?></textarea>
        </td>
    </tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./company_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/company_list_update.php
```php
<?php
$sub_menu = "930600";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

if($w == 'u') {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
		$com = sql_fetch(" SELECT * FROM {$g5['company_table']} WHERE com_idx = '".$_POST['com_idx'][$k]."' ");
		$mb = get_member($com['mb_id']);

        if (!$mb['mb_id']) {
            $msg .= $mb['mb_id'].' : 회원자료가 존재하지 않습니다.\\n';
        } else if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level']) {
            $msg .= $mb['mb_id'].' : 자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.\\n';
        } else {
			$sql = " UPDATE {$g5['company_table']} SET
						com_status = '{$_POST['com_status'][$k]}'
					WHERE com_idx = '{$_POST['com_idx'][$k]}' ";
			sql_query($sql,1);
        }
    }

}
// 삭제할 때
else if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
		$com = sql_fetch(" SELECT * FROM {$g5['company_table']} WHERE com_idx = '".$_POST['com_idx'][$k]."' ");

        if (!$com['com_idx']) {
            $msg .= $com['com_idx'].' : 업체자료가 존재하지 않습니다.\\n';
        } else {
            // 해당 com_idx관련 모든 파일 삭제(완전히 삭제)
            delete_db_file('company', $_POST['com_idx'][$k],'com');

            if($set_conf['set_del_yn']){
                // 레코드 삭제
                $sql = " DELETE FROM {$g5['company_table']} WHERE com_idx = '{$_POST['com_idx'][$k]}' ";
                // company_member 삭제
                $sql2 = " DELETE FROM {$g5['company_member_table']} WHERE cmm_com_idx = '{$_POST['com_idx'][$k]}' ";
            }
            else{
                // 레코드 삭제상태로 변경
                $sql = " UPDATE {$g5['company_table']} SET com_status = 'trash' WHERE com_idx = '{$_POST['com_idx'][$k]}' ";
                // company_member 삭제상태로 변경
                $sql2 = " UPDATE {$g5['company_member_table']} SET cmm_status = 'trash' WHERE cmm_com_idx = '{$_POST['com_idx'][$k]}' ";
            }
			sql_query($sql,1);
            sql_query($sql2,1);
        }
    }
}

if ($msg)
    alert($msg);
    //echo '<script> alert("'.$msg.'"); </script>';

    
// 추가적인 검색조건
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
    //    print_r3($key.'='.$value);
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
//                print_r3($key.$k2.'='.$v2);
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

goto_url('./company_list.php?'.$qstr, false);
```
### adm/_z01/company_list.php
```php
<?php
$sub_menu = "930600";
include_once('./_common.php');
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

@auth_check($auth[$sub_menu],"r");


// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}


$com_types = array('purchase','sales','both','etc');
$com_types_string = implode("','", $com_types);
$com_types_string = "'" . $com_types_string . "'";


$sql_common = " FROM {$g5['company_table']} AS com
                LEFT JOIN {$g5['company_member_table']} AS cmm ON com.com_idx = cmm.cmm_com_idx AND cmm_status = 'ok'
                LEFT JOIN {$g5['member_table']} AS mb ON cmm.cmm_mb_id = mb.mb_id AND mb_leave_date = '' AND mb_intercept_date = '' ";

//-- 업종 검색
$sql_com_type = ($com_types_string) ? " AND com_type IN (".$com_types_string.") " : "";

$where = array();
$where[] = " com_status != 'trash' ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case 'com_name' :
            $where[] = " ( com_name LIKE '%{$stx}%' OR com_names LIKE '%{$stx}%' ) ";
            break;
		case ( $sfl == 'mb_id' || $sfl == 'com.com_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_name' || $sfl == 'mb_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "com_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT com.com_idx, com_name, com_names, com_type, com_reg_dt, com_status
            ,com_tel, com_president, com_email, com_fax
            ,GROUP_CONCAT( CONCAT(
                'mb_id=', cmm.cmm_mb_id, '^'
                ,'cmm_rank=', cmm.cmm_rank, '^'
                ,'mb_name=', mb_name, '^'
                ,'mb_hp=', mb_hp
            ) ORDER BY cmm_reg_dt DESC ) AS com_namagers_info
		{$sql_common}
		{$sql_search} {$sql_com_type} {$sql_trm_idx_department}
        GROUP BY com_idx
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";
//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") );
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['company_table']} AS com {$sql_join} WHERE com_status = 'pending' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$colspan = 9;


$g5['title'] = '거래처관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only2">검색대상</label>
<select name="ser_com_type" class="cp_field" title="업종선택">
	<option value="">전체업종</option>
	<?=$g5['set_com_type_options_value']?>
</select>
<script>$('select[name=ser_com_type]').val('<?=$_GET['ser_com_type']?>').attr('selected','selected');</script>
<select name="sfl" id="sfl">
	<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>담당자</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>담당자휴대폰</option>
    <option value="com_president"<?php echo get_selected($_GET['sfl'], "com_president"); ?>>대표자</option>
	<option value="com.com_idx"<?php echo get_selected($_GET['sfl'], "com.com_idx"); ?>>업체고유번호</option>
	<option value="cmm.cmm_mb_id"<?php echo get_selected($_GET['sfl'], "cmm.cmm_mb_id"); ?>>담당자아이디</option>
    <option value="com_status"<?php echo get_selected($_GET['sfl'], "com_status"); ?>>상태</option>
</select>
<label for="stx" class="sound_only2">검색어<strong class="sound_only2"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>업체측 담당자를 관리하시려면 업체담당자 항목의 <i class="bi bi-pencil-square text-blue-800"></i> 편집아이콘을 클릭하세요. 담당자는 여러명일 수 있고 이직을 하는 경우 다른 업체에 소속될 수도 있습니다. </p>
</div>

<form name="form01" id="form01" action="./company_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<?=$form_input?>

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
    <tr class="success">
		<th scope="col">
			<label for="chkall" class="sound_only">업체 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <th scope="col">업체번호</th>
		<th scope="col">업체명</th>
		<th scope="col">대표자명</th>
		<th scope="col">이메일</th>
		<th scope="col" style="width:120px;">대표전화</th>
		<th scope="col">업체담당자</th>
		<th scope="col">업체구분</th>
		<th scope="col" id="mb_list_mng">수정</th>
	</tr>
    </thead>
    <tbody>
    <?php
    for($i=0; $row=sql_fetch_array($result); $i++) { 
        // 메타 분리
        if($row['com_namagers_info']) {
            $pieces = explode(',', $row['com_namagers_info']);
            for ($j1=0; $j1<sizeof($pieces); $j1++) {
                $sub_item = explode('^', $pieces[$j1]);
                for ($j2=0; $j2<sizeof($sub_item); $j2++) {
                    list($key, $value) = explode('=', $sub_item[$j2]);
//                    echo $key.'='.$value.'<br>';
                    $row['com_managers'][$j1][$key] = $value;
                }
            }
            unset($pieces);unset($sub_item);
        }
        // 담당자(들)
        if( is_array($row['com_managers']) ) {
            for ($j=0; $j<sizeof($row['com_managers']); $j++) {
//                echo $key.'='.$value.'<br>';
                $row['com_managers_text'] .= $row['com_managers'][$j]['mb_name'].' ['.$row['com_managers'][$j]['mb_id'].']';
                $row['com_managers_text'] .= $row['com_managers'][$j]['mb_hp'] ? ' <span class="font_size_8">(<i class="bi bi-telephone-fill"></i> '.$row['com_managers'][$j]['mb_hp'].')</span><br>' : '<br>' ;
            }
        }
        //수정버튼
        $s_mod = '<a href="./company_form.php?'.$qstr.'&amp;w=u&amp;com_idx='.$row['com_idx'].'">수정</a>';
    ?>
    <tr class="<?=$bg?>" tr_id="<?=$row['com_idx']?>">
        <td class="td_chk" >
			<input type="hidden" name="com_idx[<?=$i?>]" value="<?=$row['com_idx']?>" id="com_idx_<?=$i?>">
			<label for="chk_<?=$i?>" class="sound_only2"><?=get_text($row['com_name'])?></label>
			<input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
		</td>
        <td class="td_com_idx font_size_8"><!-- 번호 -->
			<?=$row['com_idx']?>
		</td>
        <td class="td_com_name td_left"><!-- 업체명 -->
			<b><?=get_text($row['com_name'])?></b>
		</td>
        <td class="td_com_president"><!-- 대표자명 -->
			<?=get_text($row['com_president'])?>
		</td>
        <td class="td_com_email font_size_8"><!-- 이메일 -->
			<?=cut_str($row['com_email'],21,'..')?>
		</td>
        <td class="td_com_tel"><!-- 대표전화 -->
			<span class="font_size_8"><?=formatPhoneNumber($row['com_tel'])?></span>
		</td>
        <td class="td_com_manager td_left" style="position:relative;padding-left:25px;font-size:1em;vertical-align:top;"><!-- 업체담당자 -->
			<?php echo $row['com_managers_text']; ?>
            <div style="display:<?=($is_admin=='super')?:'no ne'?>">
                <a href="javascript:" com_idx="<?=$row['com_idx']?>" class="btn_manager" style="position:absolute;top:5px;left:5px;font-size:1.1rem;">
                    <i class="bi bi-pencil-square text-blue-800"></i>
                </a>
            </div>
		</td>
        <td class="td_mmg font_size_8"><!-- 업체구분 -->
            <?=$set_conf['set_com_type_karr'][$row['com_type']]?>
		</td>
        <td class="td_mngsmall">
			<?=$s_mod?>
		</td>
    </tr>
    <?php
    }
    if ($i == 0)
    echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <a href="./company_form.php" id="bo_add" class="btn_01 btn">업체추가</a>
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page='); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/company_member_form_update.php
```php
<?php
$sub_menu = "930600";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

//check_admin_token();
$com = get_table_meta('company','com_idx',$com_idx);
// print_r2($com);exit;
if(!$com['com_idx'])
    alert('업체가 존재하지 않습니다.');

// 회원정보
$sql_common1 = " mb_name = '{$_POST['mb_name']}'
                , mb_hp = '{$_POST['mb_hp']}'
                , mb_email = '{$_POST['mb_email']}'
                , mb_memo = '{$_POST['mb_memo']}'
";

// 업체담당자 테이블 정보
$sql_common2 = " cmm_com_idx = '{$_POST['com_idx']}'
                , cmm_rank = '{$_POST['cmm_rank']}'
                , cmm_role = '{$_POST['cmm_role']}'
                , cmm_memo = '{$_POST['cmm_memo']}'
";

if ($w == '') {
    
    // 휴대폰 번호 or 이메일로 중복회원 체크 (중복회원이 있으면 회원정보 생성 안함)
    //$mb1 = sql_fetch(" SELECT mb_id FROM {$g5['member_table']} WHERE REGEXP_REPLACE(mb_hp,'-','') = '".preg_replace("/-/","",$_POST['mb_hp'])."' ");
    $msql = " SELECT mb_id FROM {$g5['member_table']} WHERE REGEXP_REPLACE(mb_hp,'-','') = '".preg_replace("/-/","",$_POST['mb_hp'])."' OR mb_email = '{$_POST['mb_email']}' ";
    
    //echo $msql;exit;
    $mb1 = sql_fetch($msql);

    if($mb1['mb_id']) {
        $mb_id = $mb1['mb_id'];
    }
    else {
        $sql = " INSERT INTO {$g5['member_table']} SET
                        {$sql_common1}
                        , mb_id = '{$mb_id}'
                        , mb_nick = '{$mb_id}'
                        , mb_level = '4'
                        , mb_password = '".get_encrypt_string($mb_id)."'
                        , mb_datetime = '".G5_TIME_YMDHIS."'
                        , mb_ip = '{$_SERVER['REMOTE_ADDR']}'
                        , mb_email_certify = '".G5_TIME_YMDHIS."'
        ";
        sql_query($sql,1);
        $mb_no = sql_insert_id();
    }
    //echo $mb_id;exit;
    $cmrslt = sql_fetch(" SELECT COUNT(*) AS same_cnt FROM {$g5['company_member_table']} WHERE mb_id = '{$mb_id}' ");
    if($cmrslt['same_cnt']){
        alert('동일한 연락처정보를 가진 담당자가 이미 존재합니다.');
    }else{
        $sql = " INSERT INTO {$g5['company_member_table']} SET
                        {$sql_common2}
                        , cmm_mb_id = '{$mb_id}'
                        , cmm_status = 'ok'
                        , cmm_reg_dt = '".G5_TIME_YMDHIS."'
                        , cmm_update_dt = '".G5_TIME_YMDHIS."'
        ";
        //echo $sql;exit;
        sql_query($sql,1);
        $cmm_idx = sql_insert_id();
    }
}
else if ($w == 'u') {

    $sql = "UPDATE {$g5['member_table']} SET
                {$sql_common1}
            WHERE mb_id = '{$mb_id}' ";
    sql_query($sql,1);
    

    $sql = "UPDATE {$g5['company_member_table']} SET
                {$sql_common2}
                , cmm_update_dt = '".G5_TIME_YMDHIS."'
            WHERE cmm_idx = '{$cmm_idx}' ";
    sql_query($sql,1);
    
}
else if ($w == 'd') {
    if($set_conf['set_del_yn']){
        $sql = "DELETE FROM {$g5['company_member_table']} 
                WHERE cmm_idx = '{$cmm_idx}' ";
    }
    else{
        $sql = "UPDATE {$g5['company_member_table']} SET
                cmm_status = 'trash'
            WHERE cmm_idx = '{$cmm_idx}' ";
    }
    sql_query($sql,1);
    goto_url('./company_member_list.php?com_idx='.$com_idx, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


goto_url('./company_member_list.php?'.$qstr.'&amp;w=u&com_idx='.$com_idx, false);
```
### adm/_z01/company_member_form.php
```php
<?php
$sub_menu = "930600";
include_once("./_common.php");
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

auth_check($auth[$sub_menu], 'w');

if ($w == 'u') {
    $cmm = get_table_meta('company_member','cmm_idx',$cmm_idx);
    $com_idx = $cmm['cmm_com_idx'];
}
//print_r2($cmm);
//exit;

$com = get_table_meta('company','com_idx',$com_idx);
//print_r2($com);
//exit;
if(!$com['com_idx'])
    alert('업체 정보가 존재하지 않습니다.');
//	print_r2($com);


if ($w == '') {
    $html_title = '추가';

    $mb['mb_id'] = time();
    $mb['mb_nick'] = time();
    $mb['cmm_status'] = 'ok';
}
else if ($w == 'u') {
    $mb = get_table_meta('member','mb_id',$cmm['cmm_mb_id']);
//	print_r2($mb);

    $html_title = '수정';

    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

$g5['title'] = '담당자 '.$html_title;
include_once(G5_PATH.'/head.sub.php');
add_javascript('<script src="'.G5_ADMIN_URL.'/admin.js?ver='.G5_JS_VER.'"></script>',0);
?>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>
	<?php if(!G5_IS_MOBILE){ ?>
    <div class="local_desc01 local_desc">
        <p>본 페이지는 담당자를 간단하게 관리하는 페이지입니다.(아이디, 비번 임의생성)</p>
        <p>휴대폰 번호 중복 불가! (중복인 경우 이전 회원정보에 추가됩니다.)</p>
        <p>회원가입을 시키시고 관리자 승인 후 사용하게 하는 것이 더 좋습니다.</p>
    </div>
	<?php } ?>

    <form name="form01" id="form01" action="./company_member_form_update.php" onsubmit="return form01_check(this);" method="post">
	<input type="hidden" name="w" value="<?=$w?>">
	<input type="hidden" name="com_idx" value="<?=$com_idx?>">
	<input type="hidden" name="cmm_idx" value="<?=$cmm_idx?>">
	<input type="hidden" name="token" value="">
	<input type="hidden" name="ex_page" value="<?=$ex_page?>">
    <div class=" new_win_con">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_1" style="width:28%;">
                <col class="grid_3">
            </colgroup>
            <tbody>
			<tr>
				<th scope="row">업체명</th>
				<td>
                    <div><?php echo $com['com_name'];?></div>
                    <div class="font_size_9">대표: <?php echo $com['com_president'];?></div>
				</td>
			</tr>
			<tr>
				<th scope="row">담당자명</th>
				<td>
                    <input type="hidden" name="mb_id" value="<?php echo $mb['mb_id'] ?>" id="mb_id" required class="frm_input required">
                    <input type="hidden" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" id="mb_nick" required class="frm_input required">
                    <input type="text" name="mb_name" value="<?=$mb['mb_name']?>" required class="frm_input required" style="width:50% !important;">
				</td>
			</tr>
			<tr>
                <th scope="row">직급/직책</th>
				<td>
                    <select name="cmm_rank">
                        <option value="">직급</option>
                        <?=$rank_opt?>
                    </select>
                    <script>$('select[name=cmm_rank]').val('<?=$cmm['cmm_rank']?>');</script>
					<select name="cmm_role">
						<option value="">직책</option>
                        <?=$role_opt?>
					</select>
					<script>$('select[name=cmm_role]').val('<?=$cmm['cmm_role']?>');</script>
				</td>
			</tr>
			<tr>
				<th scope="row">휴대폰</th>
				<td>
                    <input type="text" name="mb_hp" value="<?=$mb['mb_hp']?>" class="frm_input">
				</td>
			</tr>
			<tr>
				<th scope="row">이메일</th>
				<td>
                    <input type="text" name="mb_email" value="<?=$mb['mb_email']?>" class="frm_input" style="width:100%;">
				</td>
			</tr>
			<tr>
				<th scope="row">메모</th>
				<td><textarea name="mb_memo" id="mb_memo"><?=$mb['mb_memo']?></textarea></td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="btn_fixed_top" style="top:0;">
        <input type="button" class="btn_close btn btn_03" value="창닫기" onclick="javascript:opener.location.reload();window.close();">
        <input type="button" class="btn btn_02 btn_list" value="목록" onClick="self.location='./company_member_list.php?com_idx=<?=$com_idx?>'">
        <input type="button" class="btn_delete btn btn_02" value="삭제" style="display:<?=(!$cmm_idx)?'none':'';?>;">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
	</div>
    </form>

</div>

<script>
var g5_admin_csrf_token_key = "<?php echo (function_exists('admin_csrf_token_key')) ? admin_csrf_token_key() : ''; ?>";

$(function() {
    $(".btn_delete").click(function() {
		if(confirm('정보를 정말 삭제하시겠습니까?')) {
			var token = get_ajax_token();
			self.location="./company_member_form_update.php?token="+token+"&w=d&com_idx=<?=$com_idx?>&cmm_idx=<?=$cmm_idx?>";
		}
	});
});

function form01_check(f) {
    if (f.mb_name.value=='') {
		alert("담당자명을 입력하세요.");
		f.mb_name.select();
		return false;
	}
    
	if (f.mb_hp.value=='') {
		alert("휴대폰을 입력하세요.");
		f.mb_hp.select();
		return false;
	}

    if (f.mb_email.value=='') {
		alert("이메일을 입력하세요.");
		f.mb_email.select();
		return false;
	}

    return true;
}
</script>


<?php
include_once(G5_PATH.'/tail.sub.php');
```
### adm/_z01/company_member_list.php
```php
<?php
$sub_menu = "930600";
include_once("./_common.php");
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');
auth_check($auth[$sub_menu], 'w');

if(!$com_idx)
    alert_close('업체 정보가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);

$sql_common = " FROM {$g5['company_member_table']} AS cmm
                 LEFT JOIN {$g5['member_table']} AS mb ON cmm.cmm_mb_id = mb.mb_id AND mb_leave_date = '' AND mb_intercept_date = '' ";

$where = array();
$where[] = " cmm_status != 'trash' AND cmm.cmm_com_idx = '".$com_idx."' ";   // 디폴트 검색조건

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mb_hp' ) :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "cmm_reg_dt";
    $sod = "DESC";
}

$sql_order = " order by {$sst} {$sod} ";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '업체담당자';
include_once(G5_PATH.'/head.sub.php');

$sql = "SELECT * {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql.'<br>';
$result = sql_query($sql);
add_javascript('<script src="'.G5_ADMIN_URL.'/admin.js?ver='.G5_JS_VER.'"></script>',0);
?>
<style>
    .btn_fixed_top {top: 9px;}
    .member_company_brief {margin:10px 0;}
    .member_company_brief span {font-size:1.3em;}
</style>

<div class="new_win">
    
    <h1><?php echo $g5['title']; ?></h1>
    <div class=" new_win_con">
        <div class="member_company_brief">
        <span><?=$com['com_name']?></span> (대표: <?=$com['com_president']?>)
        </div>
        
        <div class="tbl_head01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
            <tr>
                <th scope="col" id="mb_list_chk" style="display:none;">
                    <label for="chkall" class="sound_only">담당자 전체</label>
                    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                </th>
                <th scope="col">이름</th>
                <th scope="col">ID</th>
                <th scope="col">직급</th>
                <th scope="col">직책</th>
                <th scope="col">휴대폰</th>
				<?php if(!G5_IS_MOBILE){ ?>
                <th scope="col">이메일</th>
				<?php } ?>
                <th scope="col" id="mb_list_mng">관리</th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                //print_r2($row);
                $s_mod = '<a href="./company_member_form.php?'.$qstr.'&amp;w=u&amp;cmm_idx='.$row['cmm_idx'].'" class="btn btn_03">수정</a>';

                $bg = 'bg'.($i%2);
            ?>

            <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['cmm_idx'] ?>" >
                <td headers="mb_list_chk" class="td_chk" style="display:none;">
                    <input type="hidden" name="cmm_idx[<?php echo $i ?>]" value="<?php echo $row['cmm_idx'] ?>" id="cmm_idx_<?php echo $i ?>">
                    <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_name']); ?>님</label>
                    <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                </td>
                <td class="td_mb_name"><?php echo get_text($row['mb_name']); ?></td>
                <td class="td_mb_id"><?php echo get_text($row['mb_id']); ?></td>
                <td class="td_mb_rank"><?=$rank_arr[$row['cmm_rank']]?></td>
                <td class="td_mb_role"><?=$role_arr[$row['cmm_role']]?></td>
                <td class="td_mb_hp"><?=$row['mb_hp']?></td>
                <td class="td_mb_email"><?=$row['mb_email']?></td>
                <td headers="mb_list_mng" class="td_mng td_mng_s">
                    <?php echo $s_mod ?><!-- 수정 -->
                </td>
            </tr>
            <?php
            }
            if ($i == 0)
                echo "<tr><td colspan='8' class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
            </tbody>
            </table>
        </div>

        <div class="btn_fixed_top">
            <a href="javascript:opener.location.reload();window.close();" id="member_add" class="btn btn_02">창닫기</a>
            <a href="./company_member_form.php?com_idx=<?=$com_idx?>" id="btn_add" class="btn btn_01">담당자추가</a>
        </div>        
        
    </div>
</div>

<script>
$(function() {

    $("#btn_member").click(function() {
        var href = $(this).attr("href");
        memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=700,scrollbars=1");
        memberwin.focus();
        return false;
    });
});

function form01_check(f) {

    // 팀개별분배는 아이디 제거해야 함
	if (f.sra_type.value=='team'&&f.cmm_idx_saler.value!='') {
		alert("팀개별분배인 경우 직원아이디값이 공백이어야 합니다.");
		f.cmm_idx_saler.select();
		return false;
	}
	// 개인분배는 아이디값이 반드시 있어야 함
	if (f.sra_type.value=='member'&&f.cmm_idx_saler.value=='') {
		alert("개인분배인 경우 직원아이디값이 존재해야 합니다.");
		f.cmm_idx_saler.select();
		return false;
	}
	if (isNaN(f.sra_price.value)==true) {
		alert("금액은 숫자만 가능합니다.");
		f.sra_price.focus();
		return false;
	}

    return true;
}
</script>


<?php
include_once(G5_PATH.'/tail.sub.php');
```
### adm/_z01/config_com_form_file_update.php
```php
<?php
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    // set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
    if(@count(${$_POST['set_type'].'_del'})){
        foreach(${$_POST['set_type'].'_del'} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    // print_r2($del_arr);exit;
    if(count($del_arr)) delete_idx_file($del_arr);
    
    //준비중 멀티파일처리
    upload_multi_file($_FILES['file_preparing'],'set','preparing','com');
    //favicon 멀티파일처리
    upload_multi_file($_FILES['file_favicon'],'set','favicon','com');
    //ogimg 멀티파일처리
    upload_multi_file($_FILES['file_ogimg'],'set','ogimg','com');
    //siemap 멀티파일처리
    upload_multi_file($_FILES['file_sitemap'],'set','sitemap','com');
}
```
### adm/_z01/config_com_form_update.php
```php
<?php
$sub_menu = "930100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');


//-- 필드명 추출 mb_ 와 같은 앞자리 3자 추출, 앞자리3글자추출 --//
$r = sql_query_pg(" SELECT column_name 
    FROM information_schema.columns 
    WHERE table_schema = 'public' 
      AND table_name = '{$g5['setting_table']}'
    ORDER BY ordinal_position ");
$db_fields = [];
while ( $d = sql_fetch_array_pg($r) ) {$db_fields[] = $d['column_name'];}
$db_prefix = substr($db_fields[0],0,3);


//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$db_fields[] = "set_bg_pattern";	// 건너뛸 변수명은 배열로 추가해 준다.
$db_fields[] = "var_name";
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트, array 타입 변수들도 저장 안 함 --//
	if(!in_array($key,$db_fields) && substr($key,0,3)==$db_prefix && gettype($value) != 'array') {
		//echo $key."=".$_REQUEST[$key]."<br>";
		set_update(array(
			"set_key"=>$_POST['set_key'], // set_XXX.php단에서 unset($set_key);이 있으므로 $_POST['set_key']로 받아야 함
			"set_type"=>$_POST['set_type'],	// set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
			"set_name"=>$key,
			"set_value"=>$value,
			"set_auto_yn"=>'Y'
		));
	}
}

@include_once('./'.$file_name.'_file_update.php');

//exit;
goto_url('./'.$file_name.'.php?'.$qstr, false);
```
### adm/_z01/config_com_form.php
```php
<?php
$sub_menu = "930100";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'com';

$thumb_wd = 200;
$thumb_ht = 150;

//준비중파일 추출 ###########################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = '{$set_type}' AND fle_db_idx = 'preparing' ORDER BY fle_reg_dt DESC ";
$rs = sql_query_pg($sql);
//echo $rs->num_rows;echo "<br>";
$cms['cms_f_arr'] = array();
$cms['cms_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$cms['cms_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    // print_r2($row2);
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($cms['cms_f_arr'],array('file'=>$file_down_del));
    @array_push($cms['cms_fidxs'],$row2['fle_idx']);
}
// exit;
//favicon파일 추출 ###########################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = '{$set_type}' AND fle_db_idx = 'favicon' ORDER BY fle_reg_dt DESC ";
$rs = sql_query_pg($sql);
//echo $rs->num_rows;echo "<br>";
$fvc['fvc_f_arr'] = array();
$fvc['fvc_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$fvc['fvc_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($fvc['fvc_f_arr'],array('file'=>$file_down_del));
    @array_push($fvc['fvc_fidxs'],$row2['fle_idx']);
}

//ogimg파일 추출 ###########################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = '{$set_type}' AND fle_db_idx = 'ogimg' ORDER BY fle_reg_dt DESC ";
$rs = sql_query_pg($sql);
//echo $rs->num_rows;echo "<br>";
$ogi['ogi_f_arr'] = array();
$ogi['ogi_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$ogi['ogi_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($ogi['ogi_f_arr'],array('file'=>$file_down_del));
    @array_push($ogi['ogi_fidxs'],$row2['fle_idx']);
}

//sitemap파일 추출 ###########################################################
$sql = "SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = '{$set_type}' AND fle_db_idx = 'sitemap' ORDER BY fle_reg_dt DESC ";
$rs = sql_query_pg($sql,1);
//echo $rs->num_rows;echo "<br>";
$stm['stm_f_arr'] = array();
$stm['stm_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$stm['stm_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array_pg($rs);$i++) {
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($stm['stm_f_arr'],array('file'=>$file_down_del));
    @array_push($stm['stm_fidxs'],$row2['fle_idx']);
}


$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본설정</a></li>
    <li><a href="#anc_cf_opengraph">오픈그래프</a></li>
    <li><a href="#anc_cf_webmaster">웹마스터</a></li>
</ul>';

$g5['title'] = '기준환경설정';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
@include_once('./css/'.$g5['file_name'].'.css.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/_form.css">',0);
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/js/colpick/colpick.css">', 0);
add_javascript('<script src="'.G5_Z_URL.'/js/colpick/colpick.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/tms_datepicker.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/tms_timepicker.js"></script>',0);
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="" id="token">
<input type="hidden" name="set_key" value="<?=$set_key?>">
<input type="hidden" name="set_type" value="<?=$set_type?>">
<input type="hidden" name="file_name" value="<?=$g5['file_name']?>">
<section id="anc_cf_default">
    <h2 class="h2_frm">기본설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기본설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>홈페이지제목</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : 웹솔루션전문 넷도리",1,'#f9fac6','#333333'); ?>
                <div class="flex gap-6">
                    <input type="text" name="set_title" class="w-[60%]" value="<?=${'set_'.$set_type}['set_title']?>">
                    <?php if($is_admin){ ?>
                    <p>$set_<?=$set_type?>['set_title']</p>
                    <?php } ?>
                </div>
            </td>
            <th>대표관리자이메일</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : woosoung@sample.com",1,'#f9fac6','#333333'); ?>
                <div class="flex gap-6">
                    <input type="text" name="set_adm_email" class="w-[60%]" value="<?=${'set_'.$set_type}['set_adm_email']?>">
                    <?php if($is_admin){ ?>
                    <p>$set_<?=$set_type?>['set_adm_email']</p>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>Favicon 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("'Favicon'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_favicon" name="file_favicon[]" multiple class="">
                        <?php
                        if(@count($fvc['fvc_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($fvc['fvc_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$fvc['fvc_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_admin){ ?>
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['favicon_str']?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>준비중 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("'준비중'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_preparing" name="file_preparing[]" multiple class="">
                        <?php
                        if(@count($cms['cms_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($cms['cms_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$cms['cms_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_admin){ ?>
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['preparing_str']?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="set_possible_ip">접근가능 IP</label></th>
            <td>
                <?=help('입력된 IP의 컴퓨터만 접근할 수 있습니다.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
                <?php if($is_admin){ ?>
                <p>$set_<?=$set_type?>['set_possible_ip']</p>
                <?php } ?>
                <textarea name="set_possible_ip" id="set_possible_ip" class="p-[10px]"><?=get_sanitize_input(${'set_'.$set_type}['set_possible_ip'])?></textarea>
            </td>
            <th><label for="set_intercept_ip">접근차단 IP</label></th>
            <td>
                <?=help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
                <?php if($is_admin){ ?>
                <p>$set_<?=$set_type?>['set_intercept_ip']</p>
                <?php } ?>
                <textarea name="set_intercept_ip" id="set_intercept_ip" class="p-[10px]"><?=get_sanitize_input(${'set_'.$set_type}['set_intercept_ip'])?></textarea>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_default -->

<section id="anc_cf_opengraph">
    <h2 class="h2_frm">오픈그래프</h2>
    <?=$pg_anchor?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>오픈그래프설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>타이틀</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("오픈 그래프의 og:title 부분에 들어갈 타이틀입니다.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_og_title" class="w-[300px]" value="<?=${'set_'.$set_type}['set_og_title']?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_og_title']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>설명</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("오픈 그래프의 og:description 부분에 들어갈 내용입니다.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_og_desc" class="w-[500px]" value="<?=${'set_'.$set_type}['set_og_desc']?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_og_desc']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>ogimg 이미지</th>
            <td colspan="3" class="tms_help">
                <?php echo help("'ogimg'이미지 파일을 관리해 주시면 됩니다."); ?>
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_ogimg" name="file_ogimg[]" multiple class="">
                        <?php
                        if(@count($ogi['ogi_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($ogi['ogi_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$ogi['ogi_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_admin){ ?>
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['ogimg_str']?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_opengraph -->

<section id="anc_cf_webmaster">
    <h2 class="h2_frm">웹마스터</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>웹마스터설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>구글 웹마스터키</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("구글 웹마스타 설정을 위한 <strong style='color:red'>google-site-verification키</strong> 값을 입력해 주세요.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_google_site_verification" class="w-[400px]" value="<?=${'set_'.$set_type}['set_google_site_verification']?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_google_site_verification']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>네이버 웹마스터키</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("네이버 웹마스타 설정을 위한 <strong style='color:red'>naver-site-verification키</strong> 값을 입력해 주세요.",1,'#f9fac6','#333333'); ?>
                <input type="text" name="set_naver_site_verification" class="w-[400px]" value="<?=${'set_'.$set_type}['set_naver_site_verification']?>">
                <?php if($is_admin){ ?>
                <p class="inline-block ml-4">$set_<?=$set_type?>['set_naver_site_verification']</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>사이트맵</th>
            <td colspan="3" class="tms_help">
                <?php echo help("웹마스터 연동을 위한 sitemap.xml 파일을 업로드해 주세요.<br>기존 파일이 존재하면 덮어쓰기 됩니다.<br><strong>파일위치 : <span style='color:blue;'>".G5_DATA_NDR_URL."/seo/sitemap.xml</sapn></strong>"); ?>
                <p>반드시 사이트 오픈후 sitemap.xml파일(파일명 동일하게 작성)을 작성하여 업로드 해 주세요.<br>sitemap.xml을 만들어주는 사이트 여기-> [<a href="http://www.check-domains.com/sitemap/index.php" target="_blank" style="color:orange;">http://www.check-domains.com/sitemap/index.php</a>]<br>방법은 아래 순서를 참고 하세요.</p>
                <p>1. 사이트 기본URL을 Site URL입력란에 입력하세요.</p>
                <p>2. 아래 라디오버튼에서 "Server's response"를 체크하세요.</p>
                <p>3. 그 옆에 있는 "Frequency"의 드롭박스 목록에서 "Always"로 선택하세요.(Monthly가 아닙니다. 주의하세요!)</p>
                <p>4. Site URL입력란 오른쪽에 있는 노란색버튼"Create Sitemap"을 클릭합니다. </p>
                <p>5. 시간이 상당히 오래 걸리기 때문에 페이지를 절대 닫지말고 끝까지 기다리세요.(대략 30분정도 소요됨)</p>
                <p>6. 작성완료되면 다운받고, 파일명이 sitemap.xml인것을 확인후 현 사이트로 돌아와 업로드 해 주세요.</p>
                <hr class="border-b-1 border-gray-200 my-4">
                <div class="tms_hint flex gap-4">
                    <div>
                        <input type="file" id="file_sitemap" name="file_sitemap[]" multiple class="">
                        <?php
                        if(@count($stm['stm_f_arr'])){
                            echo '<ul>'.PHP_EOL;
                            for($i=0;$i<count($stm['stm_f_arr']);$i++) {
                                echo "<li>[".($i+1).']'.$stm['stm_f_arr'][$i]['file']."</li>".PHP_EOL;
                            }
                            echo '</ul>'.PHP_EOL;
                        }
                        ?>
                    </div>
                    <?php if($is_admin){ ?>
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['sitemap_str']?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_webmaster -->

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/config_conf_form_file_update.php
```php
<?php
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    // set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
    if(@count(${$_POST['set_type'].'_del'})){
        foreach(${$_POST['set_type'].'_del'} as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    // print_r2($del_arr);exit;
    if(count($del_arr)) delete_idx_file($del_arr);
    
    //준비중 멀티파일처리
    upload_multi_file($_FILES['file_preparing'],'set','preparing','conf');
    //favicon 멀티파일처리
    upload_multi_file($_FILES['file_favicon'],'set','favicon','conf');
    //ogimg 멀티파일처리
    upload_multi_file($_FILES['file_ogimg'],'set','ogimg','conf');
    //siemap 멀티파일처리
    upload_multi_file($_FILES['file_sitemap'],'set','sitemap','conf');
}
```
### adm/_z01/config_conf_form.php
```php
<?php
$sub_menu = "920200";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'conf';


$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본환경설정</a></li>
    <li><a href="#anc_cf_widget">위젯환경설정</a></li>
</ul>';

$g5['title'] = '환경설정';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
@include_once('./css/'.$g5['file_name'].'.css.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/css/_form.css">',0);
add_stylesheet('<link rel="stylesheet" href="'.G5_Z_URL.'/js/colpick/colpick.css">', 0);
add_javascript('<script src="'.G5_Z_URL.'/js/colpick/colpick.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/tms_datepicker.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/tms_timepicker.js"></script>',0);
// add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="" id="token">
<input type="hidden" name="set_key" value="<?=$set_key?>">
<input type="hidden" name="set_type" value="<?=$set_type?>">
<input type="hidden" name="file_name" value="<?=$g5['file_name']?>">
<section id="anc_cf_default">
    <h2 class="h2_frm">기본환경설정</h2>
    <?php echo $pg_anchor ?>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기본설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">
                사이트기본 배경색상
            </th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("PC버전에서 사이트 전체 기본 배경 색상을 설정하세요.",1,'#f9fac6','#333333'); ?>              <div class="flex">
                    <ul class="flex [&>li]:mr-4">
                        <li>
                        
                        밝은 배경 색상<br>
                        <?=tms_input_color('set_bright_bg',${'set_'.$set_type}['set_bright_bg'],$w)?>
                        </li>
                        <li>
                        보통 배경 색상<br>
                        <?=tms_input_color('set_normal_bg',${'set_'.$set_type}['set_normal_bg'],$w)?>
                        </li>
                        <li>
                        메인 배경 색상<br>
                        <?=tms_input_color('set_main_bg',${'set_'.$set_type}['set_main_bg'],$w)?>
                        </li>
                        <li>
                        다크 배경 색상<br>
                        <?=tms_input_color('set_dark_bg',${'set_'.$set_type}['set_dark_bg'],$w)?>
                        </li>
                    </ul>
                    <div>
                        <p>$set_<?=$set_type?>['set_bright_bg']</p>
                        <p>$set_<?=$set_type?>['set_normal_bg']</p>
                        <p>$set_<?=$set_type?>['set_main_bg']</p>
                        <p>$set_<?=$set_type?>['set_dark_bg']</p>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                사이트기본 폰트색상
            </th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("PC버전에서 사이트 전체 기본 폰트 색상을 설정하세요.",1,'#f9fac6','#333333'); ?>              <div class="flex">
                    <ul class="flex [&>li]:mr-4">
                        <li>
                        밝은 배경 폰트<br>
                        <?=tms_input_color('set_bright_font',${'set_'.$set_type}['set_bright_font'],$w)?>
                        </li>
                        <li>
                        보통 배경 폰트<br>
                        <?=tms_input_color('set_normal_font',${'set_'.$set_type}['set_normal_font'],$w)?>
                        </li>
                        <li>
                        메인 배경 폰트<br>
                        <?=tms_input_color('set_main_font',${'set_'.$set_type}['set_main_font'],$w)?>
                        </li>
                        <li>
                        다크 배경 폰트<br>
                        <?=tms_input_color('set_dark_font',${'set_'.$set_type}['set_dark_font'],$w)?>
                        </li>
                    </ul>
                    <div>
                        <p>$set_<?=$set_type?>['set_bright_font']</p>
                        <p>$set_<?=$set_type?>['set_normal_font']</p>
                        <p>$set_<?=$set_type?>['set_main_font']</p>
                        <p>$set_<?=$set_type?>['set_dark_font']</p>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>삭제처리방법</th>
            <td colspan="3">
                <div class="flex gap-6">
                    <div>
                        <?php
                        $chk_del_yn_0 = (!${'set_'.$set_type}['set_del_yn']) ? 'checked' : '';
                        $chk_del_yn_1 = (${'set_'.$set_type}['set_del_yn']) ? 'checked' : '';
                        ?>
                        <label for="set_del_yn_0" class="label_radio">
                            <input type="radio" id="set_del_yn_0" name="set_del_yn" value="0" <?=$chk_del_yn_0?>>
                            <strong></strong>
                            <span>상태값처리</span>
                        </label>
                        <label for="set_del_yn_1" class="label_radio">
                            <input type="radio" id="set_del_yn_1" name="set_del_yn" value="1" <?=$chk_del_yn_1?>>
                            <strong></strong>
                            <span>삭제처리</span>
                        </label>
                    </div>
                    <div>
                        <p>$set_<?=$set_type?>['set_del_yn']</p>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>사이트 작업중 여부</th>
            <td>
                <div class="flex gap-6">
                    <div>
                        <?php
                        $chk_preparing_yn_0 = (!${'set_'.$set_type}['set_preparing_yn']) ? 'checked' : '';
                        $chk_preparing_yn_1 = (${'set_'.$set_type}['set_preparing_yn']) ? 'checked' : '';
                        ?>
                        <label for="set_preparing_yn_0" class="label_radio">
                            <input type="radio" id="set_preparing_yn_0" name="set_preparing_yn" value="0" <?=$chk_preparing_yn_0?>>
                            <strong></strong>
                            <span>공개중</span>
                        </label>
                        <label for="set_preparing_yn_1" class="label_radio">
                            <input type="radio" id="set_preparing_yn_1" name="set_preparing_yn" value="1" <?=$chk_preparing_yn_1?>>
                            <strong></strong>
                            <span>작업중</span>
                        </label>
                    </div>
                    <div>
                        <p>$set_<?=$set_type?>['set_preparing_yn']</p>
                    </div>
                </div>
            </td>
            <th>기본상태</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : pending=대기,ok=정상",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_status" class="w-[50%]" value="<?=${'set_'.$set_type}['set_status']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_status_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>링크타겟</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : _self=현재창,_blank=새창",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_target" class="w-[50%]" value="<?=${'set_'.$set_type}['set_target']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_target_str']?>
                        </div>
                    </div>
                </div>
            </td>
            <th>표시여부</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : show=표시,hide=비표시",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_show_hide" class="w-[50%]" value="<?=${'set_'.$set_type}['set_show_hide']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_show_hide_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>검색유형</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : shop=상품검색,bbs=게시판검색",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_sch_type" class="w-[220px]" value="<?=${'set_'.$set_type}['set_sch_type']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_sch_type_str']?>
                        </div>
                    </div>
                </div>
            </td>
            <th>업체유형</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : purchase=매입처,sale=매출처,both=매입매출처,etc=기타",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_com_type" class="w-[50%]" value="<?=${'set_'.$set_type}['set_com_type']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_com_type_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>업체상태</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예제 : ok=정상,close=폐업,stop=거래중지,prohibit=거래금지",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_com_status" class="w-[50%]" value="<?=${'set_'.$set_type}['set_com_status']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_com_status_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_default -->
<section id="anc_cf_widget">
    <h2 class="h2_frm">위젯설정</h2>
    <?php echo $pg_anchor ?>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>위젯설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th>캐시시간</th>
            <td class="tms_help">
                <?php echo tms_help("예제 : 0=0초,0.00139=5초,0.0028=10초,0.0056=20초,0.0084=30초,0.012=40초,0.0139=50초,0.0167=60초,1=1시간",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_cachetimes" class="w-[50%]" value="<?=${'set_'.$set_type}['set_cachetimes']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_cachetimes_str']?>
                        </div>
                </div>
            </td>
            <th>BP위젯캐시 저장시간</th>
            <td class="tms_help">
                <!--
                $cache_time은 시간단위 
                1시간=1, 5초=0.00139, 10초=0.0028, 20초=0.0056, 30초=0.0084, 40초=0.012, 50초=0.0139, 60초=0.0167, 3600초=1시간
                -->
                <?php echo tms_help("캐시 저장시간의 값이 작을수록 위젯 수정후 반영되는 시간이 짧아집니다.",1,'#f9fac6','#333333'); ?>
                <?php echo tms_select_selected(${'set_'.$set_type}['set_cachetimes'], 'set_cachetime', ${'set_'.$set_type}['set_cachetime'], 0,0,0);//인수('pending=대기,ok=정상,hide=숨김,trash=삭제','bwgf_status(name속성)','ok(값)',0(값없음활성화),1(필수여부)) ?>
                <span class="ml-4">$set_<?=$set_type?>['set_cachetime']</span>
            </td>
        </tr>
        <tr>
            <th>위젯분류</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예제 : banner=배너,content=콘텐츠,board=게시판,shop=쇼핑몰,item=상품,section=섹션스킨,etc=기타",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_purpose" class="w-[60%]" value="<?=${'set_'.$set_type}['set_purpose']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_purpose_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>텍스트애니메이션</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예제 : flash=플래시,flip=플립,flipInX=플립인X,flipInY=플립인Y,fadeIn=패이드인,fadeInUp=패이드인위쪽,fadeInDown=패이드인아래쪽,fadeInLeft=패이드인왼쪽,fadeInRight=패이드인오른쪽,fadeInUpBig=페이드인위쪽크게,fadeInDownBig=페이드인아래쪽크게,rollIn=롤인,rotateInUpRight=회전위쪽오른쪽,bounceInLeft=바운스인왼쪽,bounceInRight=바운스인오른쪽",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_text_ani" class="w-[80%]" value="<?=${'set_'.$set_type}['set_text_ani']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_text_ani_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품노출분류</th>
            <td colspan="3" class="tms_help">
                <?php echo tms_help("예제 : 1=히트,2=추천,3=최신,4=인기,5=할인,6=분류,7=전체",1,'#f9fac6','#333333'); ?>
                <div class="tms_hint flex gap-6">
                    <input type="text" name="set_item_type" class="w-[60%]" value="<?=${'set_'.$set_type}['set_item_type']?>">
                    <div class="tms_hbox">
                        <div class="tms_hcon">
                            <?=${'set_'.$set_type}['set_item_type_str']?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_widget -->
<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/config_form_update.php
```php
<?php
$sub_menu = "920100";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// print_r2($_POST);exit;
//-- 필드명 추출 mb_ 와 같은 앞자리 3자 추출, 앞자리3글자추출 --//
$r = sql_query_pg(" SELECT column_name 
    FROM information_schema.columns 
    WHERE table_schema = 'public' 
      AND table_name = '{$g5['setting_table']}'
    ORDER BY ordinal_position ");
$db_fields = [];
while ( $d = sql_fetch_array_pg($r) ) {$db_fields[] = $d['column_name'];}
$db_prefix = substr($db_fields[0],0,3);


//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$db_fields[] = "set_bg_pattern";	// 건너뛸 변수명은 배열로 추가해 준다.
$db_fields[] = "var_name";	// 건너뛸 변수명은 배열로 추가해 준다.
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트, array 타입 변수들도 저장 안 함 --//
	if(!in_array($key,$db_fields) && substr($key,0,3)==$db_prefix && gettype($value) != 'array') {
		// echo $key."=".$_REQUEST[$key]."<br>";continue;
		set_update(array(
			"set_key"=>$_POST['set_key'],	// set_XXX.php단에서 unset($set_key);이 있으므로 $_POST['set_key']로 받아야 함
			"set_type"=>$_POST['set_type'],	// set_XXX.php단에서 unset($set_type);이 있으므로 $set_type로 받아야 함
			"set_name"=>$key,
			"set_value"=>$value,
			"set_auto_yn"=>'Y'
		));
	}
}
// exit;
@include_once('./'.$file_name.'_file_update.php');

//exit;
goto_url('./'.$file_name.'.php?'.$qstr, false);
```
### adm/_z01/config_menu_form.php
```php
<?php
$sub_menu = "920100";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

@auth_check($auth[$sub_menu], 'w');

$set_key = 'dain';
$set_type = 'menu';


$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본메뉴설정</a></li>
</ul>';

$g5['title'] = '관리메뉴설정';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
// add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="" id="token">
<input type="hidden" name="set_key" value="<?=$set_key?>">
<input type="hidden" name="set_type" value="<?=$set_type?>">
<input type="hidden" name="file_name" value="<?=$g5['file_name']?>">
<section id="anc_cf_default">
    <h2 class="h2_frm">기본메뉴설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>기본메뉴설정</caption>
        <colgroup>
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
            <col class="grid_4" style="width:15%;">
            <col style="width:35%;">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">
                기본메뉴<br>
                <span id="all_hide_clear" class="<?=$all_hide_clear_btn?>">전체비활성해제</span>
            </th>
            <td colspan="3" class="<?=$menu_h3_class?> <?=$menu_ul_class?> <?=$menu_li_class?>">
                <?php
                // print_r2($set_menu);
                // 비활성 메뉴 가져오기
                $main_arr = isset(${'set_'.$set_type}['set_hide_mainmenus']) ? explode(',',${'set_'.$set_type}['set_hide_mainmenus']) : array();
                $sub_arr = isset(${'set_'.$set_type}['set_hide_submenus']) ? explode(',',${'set_'.$set_type}['set_hide_submenus']) : array();
                ?>
                
                <input type="hidden" name="set_hide_mainmenus" id="set_hide_mainmenus" value="<?=${'set_'.$set_type}['set_hide_mainmenus']?>" class="border w-full">
                <input type="hidden" name="set_hide_submenus" id="set_hide_submenus" value="<?=${'set_'.$set_type}['set_hide_submenus']?>" class="border w-full">
                <?php
                //전체 메뉴구조를 확인하려면 변수($menu_list_tag_)맨끝에 '_'를 제거하세요.
                // echo $menu_list_tag 일때만 메뉴구조 확인 가능
                $menu_list_tag_ = '';
                if($member['mb_level'] == 10){ echo $menu_list_tag_; }
                $auth_list_tag = '<div class="auth_box">'.PHP_EOL;
                foreach($menu2 as $k => $v){
                    if(count($v)){
                        foreach($v as $i => $s){
                            if($i == 0) {
                                $auth_list_tag .= '<div class="auth_div"><h3 class="auth_h3'.((in_array($k,$main_arr)?' unact':'')).'" data-code="'.$k.'" style="font-size:0.9rem;">'.$s[1];
                                $auth_list_tag .= '</h3><ul class="auth_ul">'.PHP_EOL;
                            }
                            if($i >= 1) $auth_list_tag .= '<li data-code="'.$s[0].'" class="auths'.((in_array($s[0],$sub_arr)?' unact':'')).'">'.$s[1].'</li>'.PHP_EOL;
                            if($i == count($v)-1) $auth_list_tag .= '</ul></div>'.PHP_EOL;  
                        }
                    }
                }
                $auth_list_tag .= '</div>'.PHP_EOL;
                echo $auth_list_tag;
                ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div><!-- // .tbl_frm01 -->
</section><!-- // #anc_cf_default -->

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<script>
function fconfigform_submit(f) {
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = "./config_form_update.php";
    return true;
}
</script>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/department_list_update.php
```php
<?php
$sub_menu = "920300";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu],"w");

//print_r2($trm_idx);
//echo "<br>==========<br>";
// print_r2($_POST);
// exit;

//-- depth 설정 및 공백 체크
$prev_depth = 0;
for($i=0;$i<sizeof($trm_depth);$i++) {
	if($i==0 && $trm_depth[$i] > 0) {
		alert('맨 처음 항목은 최상위 레벨이어야 합니다. 단계 설정을 확인해 주세요.');
	}
	if($trm_depth[$i] - $prev_depth > 1) {
		alert(trim($trm_name[$i]) + ' : 단계 설정에 문제가 있습니다. \n\n순서대로 하위 단계를 설정해 주세요.');
	}
	if(trim($trm_name[$i]) == "") {
		alert('조직명이 공백인 항목이 있습니다. \n\n확인하시고 다시 진행해 주세요.');
	}
	$prev_depth = $trm_depth[$i]; 
}
// print_r2($trm_desc);
// exit;

//-- 먼저 left, right 값 초기화
$sql = " UPDATE {$g5['term_table']} SET trm_left = '0', trm_right = '0' WHERE trm_category = '".$category."' ";
sql_query_pg($sql);

// print_r2($trm_desc);exit;
$depth_array = array();
$idx_array = array();	// 부모 idx를 입력하기 위한 정의
$prev_depth = 0;
for($i=0;$i<sizeof($trm_name);$i++) {
    
	//-- leaf node(마지막노드) 체크 / $depth_array[$trm_depth[$i]] = 1
	$depth_array[$trm_depth[$i]]++;	// 형제 갯수를 체크
	if($trm_depth[$i] < $prev_depth) {
		//echo $prev_depth - $trm_depth[$i]."만큼 작아졌네~".$prev_depth."<br>";
		for($j=$trm_depth[$i]+1;$j <= $prev_depth;$j++) {
			//echo $j.'<br>';
			$depth_array[$j] = 0;
		}
	}

    // 정렬번호
    if(!$trm_sort[$i])
        $trm_sort[$i] = $i;

	
	//-- 맨 처음 항목 입력 left=1, right=2 설정
	if($i == 0) {
		$sql = "INSERT INTO {$g5['term_table']} (trm_idx,trm_idx_parent,trm_name,trm_name2,trm_type,trm_category,trm_desc,trm_sort,trm_left,trm_right,trm_status,trm_reg_dt) 
					VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','$trm_desc[$i]','$i', 1, 2, '".$trm_status[$i]."', now())
					ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
                                            , trm_name = '$trm_name[$i]'
                                            , trm_name2 = '$trm_name2[$i]'
                                            , trm_type = '$trm_type[$i]'
                                            , trm_desc = '".$trm_desc[$i]."'
                                            , trm_sort = '".$trm_sort[$i]."'
                                            , trm_status = '".$trm_status[$i]."'
                                            , trm_left = 1
                                            , trm_right = 2
		";
		sql_query_pg($sql);
		// echo $sql.'<br><br>';
	}
	else {

		//-- leaf_node 이면 부모 idx를 참고해서 left, right 생성
		if($depth_array[$trm_depth[$i]] == 1) {
			//echo '부모idx -> '.$idx_array[$trm_depth[$i]-1];

			sql_query_pg("SELECT @myLeft := trm_left FROM {$g5['term_table']} WHERE trm_idx = '".$idx_array[$trm_depth[$i]-1]."' ");
			sql_query_pg("UPDATE {$g5['term_table']} SET trm_right = trm_right + 2 WHERE trm_right > @myLeft AND trm_category = '".$category."' ");
			sql_query_pg("UPDATE {$g5['term_table']} SET trm_left = trm_left + 2 WHERE trm_left > @myLeft AND trm_category = '".$category."' ");
			$sql = "INSERT INTO {$g5['term_table']} (trm_idx, trm_idx_parent, trm_name, trm_name2, trm_type, trm_category, trm_desc, trm_sort, trm_left, trm_right, trm_status, trm_reg_dt) 
						VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','".$trm_desc[$i]."','$i',@myLeft + 1,@myLeft + 2, '".$trm_status[$i]."', now())
						ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
							, trm_name = '$trm_name[$i]'
							, trm_name2 = '$trm_name2[$i]'
							, trm_type = '$trm_type[$i]'
							, trm_desc = '".$trm_desc[$i]."'
							, trm_sort = '".$trm_sort[$i]."'
							, trm_status = '".$trm_status[$i]."'
							, trm_left = @myLeft + 1
							, trm_right = @myLeft + 2
			";
			sql_query_pg($sql,1);
			// echo $sql.'<br><br>';
		}
		//-- leaf_node가 아니면 동 레벨 idx 참조해서 left, right 생성
		else {
			sql_query_pg("SELECT @myRight := trm_right FROM {$g5['term_table']} WHERE trm_idx = '".$idx_array[$trm_depth[$i]]."' ");
			sql_query_pg("UPDATE {$g5['term_table']} SET trm_right = trm_right + 2 WHERE trm_right > @myRight AND trm_category = '".$category."' ");
			sql_query_pg("UPDATE {$g5['term_table']} SET trm_left = trm_left + 2 WHERE trm_left > @myRight AND trm_category = '".$category."' ");
			$sql = "INSERT INTO {$g5['term_table']} (trm_idx, trm_idx_parent, trm_name, trm_name2, trm_type, trm_category, trm_desc, trm_sort, trm_left, trm_right, trm_status, trm_reg_dt) 
						VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','".$trm_desc[$i]."','$i',@myRight + 1,@myRight + 2, '".$trm_status[$i]."', now())
						ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
							, trm_name = '$trm_name[$i]'
							, trm_name2 = '$trm_name2[$i]'
							, trm_type = '$trm_type[$i]'
							, trm_desc = '".$trm_desc[$i]."'
							, trm_sort = '".$trm_sort[$i]."'
							, trm_status = '".$trm_status[$i]."'
							, trm_left = @myRight + 1
							, trm_right = @myRight + 2
			";
			sql_query_pg($sql,1);
			// echo $sql.'<br><br>';
		}
	}
	
	//echo "<br><br>";
	$prev_depth = $trm_depth[$i]; 
	$idx_array[$trm_depth[$i]] = $trm_idx[$i];	//-- left, right 기준 값 저장
	$idx_array[$trm_depth[$i]] = sql_insert_id_pg({$g5['term_table']});	//-- left, right 기준 값 저장
}


// 캐시 파일 삭제 (초기화)
$files = glob(G5_DATA_PATH.'/cache/term-'.$category.'.php');
if (is_array($files)) {
    foreach ($files as $filename)
        unlink($filename);
}


//exit;
// 앞에서 넘어온 파일명으로 다시 돌려보낸다.
goto_url("./".$file_name.".php?category=".$category);
?>
```
### adm/_z01/department_list.php
```php
<?php
$sub_menu = "920300";
include_once('./_common.php');

@auth_check($auth[$sub_menu],"r");

// 용어 설정
$category = ($category) ? $category : 'department';


// include_once(G5_ZSQL_PATH.'/term_department.php');

//아래 AND 조건절에 비어있는 줄에는 원래 아래와 같은 내용은 있었다 (2군데)
//AND term.trm_status = 'ok' AND parent.trm_status = 'ok'
//-- 카테고리 구조 추출 --//
$sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor member: 루트 노드를 가져옴
    SELECT
        trm_idx,
        trm_name,
    	trm_name2,
        CAST(trm_name AS CHAR(255)) AS path,
        trm_desc,
        trm_left,
        trm_right,
		trm_status,
        0 AS depth,
        (SELECT COUNT(*) > 0 FROM {$g5['term_table']} AS child WHERE child.trm_idx_parent = {$g5['term_table']}.trm_idx AND child.trm_status != 'trash') AS has_children
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0  -- 루트 노드
        AND trm_category = '{$category}'
        AND trm_status != 'trash'

    UNION ALL

    -- Recursive member: 하위 노드들을 경로와 함께 가져옴
    SELECT
        t.trm_idx,
        t.trm_name,
    	t.trm_name2,
        CONCAT(tp.path, ' > ', t.trm_name) AS path,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
		t.trm_status,
        (SELECT COUNT(*)
        FROM {$g5['term_table']} AS parent
        WHERE parent.trm_left < t.trm_left
        AND parent.trm_right > t.trm_right) AS depth,
        (SELECT COUNT(*) > 0 FROM {$g5['term_table']} AS child WHERE child.trm_idx_parent = t.trm_idx AND child.trm_status != 'trash') AS has_children
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = '{$category}'
        AND t.trm_status != 'trash'
)
SELECT trm_idx, trm_name, trm_name2, path, trm_desc, trm_left, trm_right, trm_status, depth, has_children FROM TermPaths ORDER BY trm_left;
";

$result = sql_query_pg($sql);
$total_count = sql_num_rows_pg($result);

$g5['title'] = '조직관리';
require_once G5_ADMIN_PATH.'/admin.head.php';
?>


<div class="local_ov01 local_ov">
    <?php echo $listall??'' ?>
    <span class="btn_ov01"><span class="ov_txt">총분류수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>개 </span></span>
    <div style="display:inline-block;float:right;">
        <select name="category" style="display:none;" class="cp_field" title="분류선택" onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'>
            <option value="">분류 선택</option>
            <?=$g5['set_taxonomies_options']?>
        </select>
        <script>
            $('select[name=category]').val('<?=$category?>').attr('selected','selected');
        </script>
    </div>
</div>

<form name="fcarlist" method="post" action="./department_list_update.php" autocomplete="off">
<input type="hidden" name="category" value="<?php echo $category; ?>">
<input type="hidden" name="file_name" value="<?php echo $g5['file_name']; ?>">

<div id="sct" class="tbl_head02 tbl_wrap">
<table id="table01_list">
<caption><?php echo $g5['title']; ?> 목록</caption>
<thead>
<tr>
    <th scope="col" style="width:6%">트리구조</th>
    <th scope="col" style="width:15%">부서명</th>
    <th scope="col" style="width:10%">부서명2</th>
    <th scope="col" style="width:7%"><a href="javascript:" id="sub_toggle">닫기</a></th>
    <th scope="col" style="width:20%">설명</th>
    <th scope="col" style="width:10%">위치이동</th>
    <th scope="col" style="width:5%;white-space:nowrap;">고유코드</th>
	<th scope="col" style="width:6%">숨김</th>
    <th scope="col" style="width:6%">관리</th>
</tr>
</thead>
<tbody>
	<!-- 항목 추가를 위한 DOM (복제후 제거됨) -->
	<tr class="" style="display:none">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_trm_name" style="padding-left:10px">
			<input type="hidden" name="trm_depth[]" value="0">
			<input type="hidden" name="trm_idx[]" value="">
			<input type="text" name="trm_name[]" value="분류명을 입력하세요" required class="frm_input full_input required" style="width:180px;">
	    </td>
	    <td class="td_trm_name" style="padding-left:10px">
			<input type="text" name="trm_name2[]" value="" class="frm_input full_input" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><a href="#">열기</a></td>
	    <td class="td_trm_content"><!-- 삭제조직코드 -->
	        <input type="text" name="trm_desc[]" value="" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center">
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
		<td class="td_idx" style="text-align:center"></td>
		<td class="td_use" style="text-align:center">
			<input type="hidden" name="trm_status[]" value="ok">
			<input type="checkbox" name="trm_use[]">
	    </td>
	    <td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
	<!-- //항목 추가를 위한 DOM (복제후 제거됨) -->
<?php
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	// print_r2($row);
	//-- 들여쓰기
	$row['indent'] = ($row['depth']) ? $row['depth']*50:10;
	
	//-- 하위 열기 닫기
	//$row['sub_toggle'] = ($row['depth']==0) ? '<a href="#">닫기</a>':'-';
	$row['sub_toggle'] = ($row['has_children']==1) ? '<a href="#">닫기</a>':'-';
	
    // 추가 부분 unserialize
    $unser = unserialize(stripslashes($row['trm_more']));
    if( is_array($unser) ) {
        foreach ($unser as $key=>$value) {
            //print_r3($key.'/'.$value);
            $row[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
        }    
    }
	
	$usechecked = ($row['trm_status'] == 'ok') ? '':'checked';
	$status_txt = ($row['trm_status'] == 'ok') ? 'ok':'hide';
    $bg = 'bg'.($i%2);
?>
	<tr class="<?php echo $bg; ?>">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_trm_name" style="padding-left:<?=$row['indent']?>px;text-align:left;">
			<input type="hidden" name="trm_depth[]" value="<?=$row['depth']?>">
			<input type="hidden" name="trm_idx[]" value="<?=$row['trm_idx']?>">
			<input type="text" name="trm_name[]" value="<?php echo get_text(trim($row['trm_name'])); ?>" required class="frm_input full_input required" style="width:180px;">
	    </td>
	    <td class="td_trm_name2">
			<input type="text" name="trm_name2[]" value="<?php echo get_text(trim($row['trm_name2'])); ?>" class="frm_input full_input" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><?=$row['sub_toggle']?></td>
	    <td class="td_trm_content"><!-- 삭제조직코드 -->
	        <input type="text" name="trm_desc[]" value="<?php echo get_text(trim($row['trm_desc'])); ?>" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center"><!-- 위치이동 -->
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
	    <td class="td_idx" style="text-align:center"><!-- 코유코드 -->
			<?=$row['trm_idx']?>
	    </td>
	    <td class="td_use" style="text-align:center"><!-- 숨김 -->
			<input type="hidden" name="trm_status[]" value="<?=$status_txt?>">
	        <input type="checkbox" name="trm_use[]" <?=$usechecked?>>
	    </td>
		<td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
<?php }
if ($i == 0) echo "<tr class=\"no-data\"><td colspan=\"9\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
?>
</tbody>
</table>
</div>

<div class="btn_fixed_top">
    <a href="javascript:insert_item()" id="btn_add_car" class="btn btn_02">항목추가</a>
	<input type="submit" name="act_button" value="확인" class="btn_submit btn">
</div>
</form>

<script>
//----------------------------------------------
$(function() {
    
    // 엑셀업로드 창 열기
    $(document).on('click','#btn_excel_upload',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        win_excel_upload = window.open(href, "win_excel_upload", "left=100,top=100,width=520,height=600,scrollbars=1");
        win_excel_upload.focus();
    });
    
	
	//-- DOM 복제 & 생성 & 초기화 --//
	list_dom01=$("#table01_list tbody");
	orig_dom01=list_dom01.find("tr").eq(0).clone();
	list_dom01.find("tr:eq(0)").remove();	// 복제한 후에 제거
	list01_nothing_display();

	//-- 정렬(Sortable) --//
	var fixHelperModified = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index)
		{
			$(this).width($originals.eq(index).width());
		});
		return $helper;
	};

	$("#table01_list tbody").sortable({
		cancel: "input, textarea, a, i"
		, helper: fixHelperModified
		, items: "tr:not(.no-data)"
		, placeholder: "tr-placeholder"
		, connectWith: "#table01_list tr:not(.no-data)"
		, stop: function(event, ui) {
			//alert(ui.item.html());
			//-- 정렬 후 처리 / 맨 처음 항목이면 최상위 레벨이어야 함
			if($(this).find('tr').index(ui.item) == 0 && ui.item.find('input[name^=trm_depth]').val() > 0) {
				ui.item.find('input[name^=trm_depth]').val(0);
				ui.item.find('.td_trm_name').css('padding-left','0px');
			}
			
			setTimeout(function(){ ui.item.removeAttr('style'); }, 10);
		}
	});
	
	//=====================================================카테고리 사용여부=========================== 
	$('input[type="checkbox"]').click(function(){
		if($(this).is(":checked")){
			$(this).siblings('input[type="hidden"]').val('hide');
			//alert($(this).siblings('input[type="hidden"]').val());
		}else{
			$(this).siblings('input[type="hidden"]').val('ok');
			//alert($(this).siblings('input[type="hidden"]').val());
		}
	});

	//-- 차종추가 경고창 초기 설정
	alert_flag = true; 


	//-- 단계이동 버튼 클릭 --//
//	$('.td_depth a').live('click',function(e) {
	$(document).on('click','.td_depth a',function(e) {
		e.preventDefault();
		//-- 맨 처음 항목은 무조건 최상위 단계이어야 함
		if($(this).parents('tbody:first').find('tr').index($(this).parents('tr:first')) == 0 && $(this).parent().find('a').index($(this)) == 1) {
			alert('맨 처음 항목은 최상위 레벨이어야 합니다. \n\n단계 2로 이동할 수 없습니다.');
			return false;
		}
		
		//-- depth 값 업데이트
		var indent_sign_value = ($(this).parent().find('a').index($(this)) == 0)? -1:1;
		var new_depth = parseInt($(this).parents('tr:first').find('input[name^=trm_depth]').val()) + indent_sign_value;
		if(new_depth < 0) new_depth = 0;
		$(this).parents('tr:first').find('input[name^=trm_depth]').val(new_depth);
		
		//-- 들여쓰기 적용
		var indent_value = (new_depth) ? new_depth * 50:10;
		$(this).parents('tr:first').find('.td_trm_name').css('padding-left',indent_value+'px');
		
		//update_notice();	//-- [일괄수정] 버튼 활성화
	});


	//-- 위치이동 버튼 클릭 --//
//	$('.td_sort a').live('click',function(e) {
	$(document).on('click','.td_sort a',function(e) {
		e.preventDefault();

		var target_tr = $(this).parents('tr:first').clone().hide();
		var flag_up_down = ($(this).parent().find('a').index($(this)) == 0)? 'up':'down';
		var tr_loc = $(this).parents('tbody:first').find('tr').index($(this).parents('tr:first'));
		

		if(flag_up_down == "up" && tr_loc == 0) {
			alert('맨 처음 항목입니다. 더 이상 올라갈 때가 없지 않나요?');
			return false;
		}
		else if(flag_up_down == "down" && tr_loc == $(this).parents('tbody:first').find('tr').length - 1) {
			alert('마지막 항목입니다. 보면 알잖아요~');
			return false;
		}

		$(this).parents('tr:first').stop(true,true).fadeOut('fast',function(){
			$(this).remove();

			if(flag_up_down == "up") {
				target_tr.insertBefore($('#table01_list tbody tr').eq(parseInt(tr_loc)-1)).stop(true,true).fadeIn('fast').removeAttr('style');
			}
			else {
				target_tr.insertAfter($('#table01_list tbody tr').eq(tr_loc)).stop(true,true).fadeIn('fast').removeAttr('style');
			}

		});

		//update_notice();	//-- Submit 버튼 활성화
	});


	//-- 삭제 버튼 클릭시 --//
//	$('.td_del a').live('click',function(e) {
	$(document).on('click','.td_del a',function(e) {
		e.preventDefault();
		
		//-- 추가된 항목은 바로 삭제, 기 등록된 조직은 관련 작업 진행
		if(confirm('하위 카테고리 전체 및 소속 항목들이 전부 삭제됩니다. \n\n후회할 수도 있을 텐데~~ 정말 삭제하시겠습니까?')) {
			if($(this).parents('tr:first').find('input[name^=trm_idx]').val()) {

				//-- 삭제 함수 호출(마지막인수는 trash변경할지 아예 삭제할지의 영부인데 0이면 trash, 1이면 완전삭제)
				trm_delete($(this).parents('tr:first').find('input[name^=trm_idx]').val(),1);

			}
			else {
				$(this).parents('tr:first').remove();
			}
			
			//update_notice();	//-- Submit 버튼 활성화
		}
	});


	//-- 닫기 열기
	$('#sub_toggle').click(function() {
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		$('#table01_list tbody tr').find('input[name^=trm_depth]').each(function() {
			if($(this).val() > 0) {
				if(this_text == "닫기")
					$(this).closest('tr').hide();
				else 
					$(this).closest('tr').show();
			}
			else {
				if(this_text == "닫기") {
					$(this).closest('tr').find('.td_sub_category a').text('열기');
				}
				else 
					$(this).closest('tr').find('.td_sub_category a').text('닫기');
			}
		});
	});


	//-- 서브 부분만 열고 닫기
//	$('.td_sub_category a').live('click',function(e) {
	$(document).on('click','.td_sub_category a',function(e) {
		e.preventDefault();
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		
		var this_depth = $(this).closest('tr').find('input[name^=trm_depth]').val();
		var this_sub_flag = false;
		
		$(this).closest('tr').nextAll('tr').each(function() {
			if($(this).find('input[name^=trm_depth]').val() > this_depth && this_sub_flag == false) {

				if(this_text == "닫기")
					$(this).hide();
				else
					$(this).show();
			}
			else 
				this_sub_flag = true;
		});
	});



});
//----------------------------------------------


//-- 01 No data 처리 --//
function list01_nothing_display() {
	if(list_dom01.find("tr:not(.no-data)").length == 0)
		list_dom01.find('.no-data').show();
	else 
		list_dom01.find('.no-data').hide();
}
//-- //01 No data 처리 --//


//-- 테이블 항목 추가
function insert_item() {
	//-- DOM 복제
	sDom = orig_dom01.clone();

	//-- DOM 입력
	//sDom.insertBefore($('#table01_list tbody tr').eq(0)).show();
	//$('#table01_list tbody tr').eq(0).find('input[name^=trm_name]').select().focus();
	$('#table01_list tbody').append(sDom.show());
	$('#table01_list tbody tr:last').find('input[name^=trm_name]').select().focus();

	list01_nothing_display();
	
	if(alert_flag == true) {
		alert('입력항목을 작성한 후 하단의 [일괄수정] 버튼을 클릭하여 적용해 주시면 됩니다.');
		alert_flag = false;
	}
}


//-- 항목 삭제 함수 --//
function trm_delete(this_trm_idx, fn_delte) {
	//-- 디버깅 Ajax --//
	$.ajax({
		url:'./ajax/term_delete.php',
		type:'get',
		data:{"category":"<?=$category?>", "trm_idx":this_trm_idx,"delete":fn_delte},
		dataType:'json',
		timeout:3000, 
		beforeSend:function(){},
		success:function(data){
			self.location.reload();
		},
		error:function(xmlRequest) {
			alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
			+ ' \n\rresponseText: ' + xmlRequest.responseText);
		} 
	//-- 디버깅 Ajax --//

	});	
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/employee_form_update.php
```php
<?php
$sub_menu = "920600";
include_once('./_common.php');

check_demo();

@auth_check($auth[$sub_menu],"w");

//-- 필드명 추출 & mb_ 와 같은 앞자리 3자 추출 --//
$r = getPrefixFields($g5['member_table']);//arr['prefix'], arr['fields']를 반환

// $_REQUEST로 넘어온 데이터중에 추출한 접두어를 가진 필드중에 테이블에 존재하지 않는 데이터만 추출해서 배열로 반환
// 이 배열은 meta테이블에 따로 저장하기 위해 사용됨
$exArr = getExTableData($r['prefix'],$r['fields'],$_REQUEST);

$mb_id = trim($_POST['mb_id']);
$sql_password = '';
if($_POST['mb_password']){
    $mb_password = trim($_POST['mb_password']);
    $mb_password = get_encrypt_string($mb_password);
    $sql_password = " , mb_password = '{$mb_password}' ";
}
$mb_name = trim($_POST['mb_name']);
$mb_nick = trim($_POST['mb_nick']);
$mb_email = trim($_POST['mb_email']);
$mb_hp = trim($_POST['mb_hp']);
$mb_hp = preg_replace('/[^0-9]/', '', $mb_hp); // 숫자만 추출
$sql_certify = ($w == '') ? " , mb_email_certify = '".G5_TIME_YMDHIS."' " : '';
$sql_open = ($w == '') ? " , mb_open = '1' " : '';
$sql_open_date = ($w == '') ? " , mb_open_date = '".G5_TIME_YMD."' " : '';
$mb_zip1 = substr(trim($_POST['mb_zip']), 0, 3);
$mb_zip2 = substr(trim($_POST['mb_zip']), 3);
$mb_addr1 = trim($_POST['mb_addr1']);
$mb_addr2 = trim($_POST['mb_addr2']);
$mb_addr3 = trim($_POST['mb_addr3']);
$mb_addr_jibeon = trim($_POST['mb_addr_jibeon']);
$mb_memo = conv_unescape_nl(stripslashes($_POST['mb_memo']));
$mb_datetime = $_POST['mb_datetime'].' '.date('H:i:s');
if($mb_leave_date){
    $mb_level = 1;
    $mb_leave_date = preg_replace('/[^0-9]/', '', $mb_leave_date); // 숫자만 추출
}

$sql_common = " mb_id = '{$mb_id}'
                {$sql_password}
                , mb_name = '{$mb_name}'
                , mb_nick = '{$mb_nick}'
                , mb_email = '{$mb_email}'
                , mb_hp = '{$mb_hp}'
                , mb_level = '{$mb_level}'
                , mb_zip1 = '{$mb_zip1}'
                , mb_zip2 = '{$mb_zip2}'
                , mb_addr1 = '{$mb_addr1}'
                , mb_addr2 = '{$mb_addr2}'
                , mb_addr3 = '{$mb_addr3}'
                , mb_addr_jibeon = '{$mb_addr_jibeon}'
                , mb_memo = '{$mb_memo}'
                , mb_datetime = '{$mb_datetime}'
                , mb_leave_date = '{$mb_leave_date}'
                {$sql_open}
                {$sql_open_date}
                {$sql_certify}
";


if($w == ''){
    $sql = " INSERT INTO {$g5['member_table']} SET {$sql_common} ";
}
else if($w == 'u'){
    $sql = " UPDATE {$g5['member_table']} SET {$sql_common} WHERE mb_id = '{$mb_id}' ";
}
sql_query($sql,1);


$skip_arr = array('mb_zip');
if(count($exArr)){
    foreach($exArr as $k => $v){
        if(in_array($k,$skip_arr)) continue;
        meta_update(array("mta_db_tbl"=>"member","mta_db_idx"=>$mb_id,"mta_key"=>$k,"mta_value"=>$v));
    }
}
$auth_renewal = isset($auth_renewal) ? true : false;
// echo $auth_renewal.':';exit;
// $auth_renewal = $auth_renewal ?? true; // 권한갱신 여부
if($auth_renewal){
    $auth_del_sql = " DELETE FROM {$g5['auth_table']} WHERE mb_id = '{$mb_id}' ";
    sql_query($auth_del_sql,1);
    $auth_arr = isset($auths) ? explode(',',$auths) : array();
    if(count($auth_arr)){
        $auth_sql = " INSERT INTO {$g5['auth_table']} VALUES ('{$mb_id}', '100000', 'r') "; // 기본적으로 메인페이지 즉, '대시보드' 권한을 부여
        foreach($auth_arr as $v){
            $arr = explode('_', $v);
            $code = $arr[0];
            $auth_str = $arr[1];
            $auth_str .= isset($arr[2]) ? ','.$arr[2] : '';
            $auth_str .= isset($arr[3]) ? ','.$arr[3] : '';
            $auth_sql .= " ,('{$mb_id}', '{$code}', '{$auth_str}') ";
        }
        sql_query($auth_sql,1);
    }
}

// 퇴사처리시 모든권한 삭제
if($mb_leave_date){
    $auth_del_sql = " DELETE FROM {$g5['auth_table']} WHERE mb_id = '{$mb_id}' ";
    sql_query($auth_del_sql,1);
}


if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(@count($emp_del)){
        foreach($emp_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    if(count($del_arr)) delete_idx_file($del_arr);
    
    //멀티파일처리
    upload_multi_file($_FILES['emp_datas'],'member',$mb_id,'emp');
}



foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

$msg = ($w == '') ? '등록' : '수정';
alert('사원정보가 '.$msg.'되었습니다.','./employee_list.php?'.$qstr, false);
```
### adm/_z01/employee_form.php
```php
<?php
$sub_menu = "920600";
include_once('./_common.php');

include_once(G5_ZSQL_PATH.'/term_department.php');
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

@auth_check($auth[$sub_menu],"r");

$form_input = '';
// 추가 변수 생성
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
    //    print_r3($key.'='.$value);
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
//                print_r3($key.$k2.'='.$v2);
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}


if ($w == '') {
    $required_mb_id = 'required';
    $required_mb_id_class = 'required alnum_';
    $required_mb_password = 'required';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $mb['mb_mailling'] = 1;
    $mb['mb_open'] = 1;
    $mb['mb_level'] = 6;
    $html_title = '추가';
}
else if ($w == 'u')
{
    // $mb = get_member($mb_id);
    $mb = get_table_meta('member','mb_id',$mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

    if ($is_admin != 'super' && $mb['mb_level'] > $member['mb_level'])
        alert('자신보다 권한이 높거나 같은 사원은 수정할 수 없습니다.');

    // print_r2($mb);exit;
    $required_mb_id = 'readonly';
    $required_mb_password = '';
    $html_title = '수정';
    
    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_homepage'] = get_text($mb['mb_homepage']);
    $mb['mb_birth'] = get_text($mb['mb_birth']);
    $mb['mb_tel'] = get_text($mb['mb_tel']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
    $mb['mb_addr1'] = get_text($mb['mb_addr1']);
    $mb['mb_addr2'] = get_text($mb['mb_addr2']);
    $mb['mb_addr3'] = get_text($mb['mb_addr3']);
    $mb['mb_signature'] = get_text($mb['mb_signature']);
    $mb['mb_recommend'] = get_text($mb['mb_recommend']);
    $mb['mb_profile'] = get_text($mb['mb_profile']);
    $mb['mb_1'] = get_text($mb['mb_1']);
    $mb['mb_2'] = get_text($mb['mb_2']);
    $mb['mb_3'] = get_text($mb['mb_3']);
    $mb['mb_4'] = get_text($mb['mb_4']);
    $mb['mb_5'] = get_text($mb['mb_5']);
    $mb['mb_6'] = get_text($mb['mb_6']);
    $mb['mb_7'] = get_text($mb['mb_7']);
    $mb['mb_8'] = get_text($mb['mb_8']);
    $mb['mb_9'] = get_text($mb['mb_9']);
    $mb['mb_10'] = get_text($mb['mb_10']);

    // 사원 권한정보 가져오기
    $auth_sql = " SELECT * FROM {$g5['auth_table']} WHERE mb_id = '{$mb_id}' ORDER BY CAST(au_menu AS SIGNED) ";
    $auth_result = sql_query($auth_sql,1);
    $auth_arr = array();
    $auth_list = array();
    for($i=0;$row=sql_fetch_array($auth_result);$i++) {
        $rw_arr = explode(',',$row['au_auth']);
        $auth_bar = preg_replace('/,/','_',$row['au_auth']);
        $auth_arr[$row['au_menu']] = $rw_arr;
        array_push($auth_list,$row['au_menu'].'_'.$auth_bar);
    }

    //관련파일 추출
	$sql = "SELECT * FROM {$g5['dain_file_table']}
        WHERE fle_db_tbl = 'member' AND fle_type = 'emp' AND fle_db_idx = '{$mb_id}' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query_pg($sql);
    //echo $rs->num_rows;echo "<br>";
    $emp['emp_f_arr'] = array();
    $emp['emp_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
    $emp['emp_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
        $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($emp['emp_f_arr'],array('file'=>$file_down_del));
        @array_push($emp['emp_fidxs'],$row2['fle_idx']);
    }

    //회의관련 파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
    if(@count($emp['emp_fidxs'])) $emp['emp_lst_idx'] = $emp['emp_fidxs'][0];
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 본인확인방법
switch($mb['mb_certify']) {
    case 'hp':
        $mb_certify_case = '휴대폰';
        $mb_certify_val = 'hp';
        break;
    case 'ipin':
        $mb_certify_case = '아이핀';
        $mb_certify_val = 'ipin';
        break;
    case 'admin':
        $mb_certify_case = '관리자 수정';
        $mb_certify_val = 'admin';
        break;
    default:
        $mb_certify_case = '';
        $mb_certify_val = 'admin';
        break;
}

// 본인확인
$mb_certify_yes  =  $mb['mb_certify'] ? 'checked="checked"' : '';
$mb_certify_no   = !$mb['mb_certify'] ? 'checked="checked"' : '';

// 성인인증
$mb_adult_yes       =  $mb['mb_adult']      ? 'checked="checked"' : '';
$mb_adult_no        = !$mb['mb_adult']      ? 'checked="checked"' : '';

//메일수신
$mb_mailling_yes    =  $mb['mb_mailling']   ? 'checked="checked"' : '';
$mb_mailling_no     = !$mb['mb_mailling']   ? 'checked="checked"' : '';

// SMS 수신
$mb_sms_yes         =  $mb['mb_sms']        ? 'checked="checked"' : '';
$mb_sms_no          = !$mb['mb_sms']        ? 'checked="checked"' : '';

// 정보 공개
$mb_open_yes        =  $mb['mb_open']       ? 'checked="checked"' : '';
$mb_open_no         = !$mb['mb_open']       ? 'checked="checked"' : '';


$g5['title'] = $g5['title'] ?? ''; // 초기화
if ($mb['mb_intercept_date']) {
    $g5['title'] = "차단된 ";
}


$g5['title'] = '사원 '.$html_title;
require_once G5_ADMIN_PATH.'/admin.head.php';
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
add_javascript(G5_POSTCODE_JS, 0);//다음 주소 js
add_javascript('<script src="'.G5_Z_URL.'/js/multifile/jquery.MultiFile.min.js"></script>',0);
?>
<form name="fmember" id="fmember" action="./<?=$g5['file_name']?>_update.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?=${$pre."_idx"}?>">
<?=$form_input?>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>사원정보를 관리하는 페이지입니다.(사원의 회원등급은 기본 6입니다.)</p>
</div>
<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4" style="width:10%;">
        <col style="width:40%">
        <col class="grid_4" style="width:10%;">
        <col style="width:40%">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">아이디<?=$sound_only??''?></label></th>
        <td>
            <div class="flex gap-3">
                <input type="text" name="mb_id"<?=(($w!='')?' readonly':'')?> value="<?=$mb['mb_id']?>" id="reg_mb_id" class="frm_input<?=(($w!='')?' readonly':'')?>" size="15"  maxlength="20">
                <?php if($w=='') { ?>
                <span class="s_id_info"></span>
                <?php } ?>
            </div>
        </td>
        <th scope="row"><label for="mb_password">비밀번호<?=$sound_only??''?></label></th>
        <td>
            <div class="flex gap-3">
                <?php if($w==''|| !auth_check($auth[$sub_menu]??'','r,w',1) || $member['mb_level'] == $mb['mb_level']) { ?>
                <input type="password" name="mb_password" id="mb_password" <?php //echo $required_mb_password ?> class="frm_input <?php //echo $required_mb_password ?>" size="15" maxlength="20">
                <?php } else { ?>
                <span style="color:#aaa;" class="inline-block w-[180px]">비밀번호 수정 불가</span>
                <?php } ?>
                <?php echo help('비밀번호는 반드시 영문으로 시작해야하고 이 후 영문숫자 조합으로 6글자이상 입력해 주세요.') ?>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_name">이름(실명)<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="mb_name" value="<?=$mb['mb_name']?>" id="mb_name" class="frm_input" size="15"  maxlength="20" <?php if(@auth_check($auth[$sub_menu],'r,w',1)) echo 'readonly';?>></td>
        <th scope="row"><label for="mb_nick">닉네임<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="mb_nick" value="<?=$mb['mb_nick']?>" id="reg_mb_nick" class="frm_input" size="15"  maxlength="20" <?php if(@auth_check($auth[$sub_menu],'r,w',1)) echo 'readonly';?>>
            <?php if(@!auth_check($auth[$sub_menu],'r,w',1)) { ?>
                <span class="s_nick_info"></span>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_department">부서<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="mb_department" id="mb_department" class="frm_input">
                <option value="">::부서선택::</option>
                <?=$department_opt?>
            </select>
            <script>
            const mb_department = document.querySelector('#mb_department');
            mb_department.value = '<?=$mb['mb_department']?>';
            </script>
        </td>
        <th scope="row"><label for="mb_department">직급<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="mb_rank" id="mb_rank" class="frm_input">
                <option value="">::직급선택::</option>
                <?=$rank_opt?>
            </select>
            <script>
            const mb_rank = document.querySelector('#mb_rank');
            mb_rank.value = '<?=$mb['mb_rank']?>';
            </script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_role">직책<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="mb_role" id="mb_role" class="frm_input">
                <option value="">::직책선택::</option>
                <?=$role_opt?>
            </select>
            <script>
            const mb_role = document.querySelector('#mb_role');
            mb_role.value = '<?=$mb['mb_role']?>';
            </script>
        </td>
        <th scope="row"><label for="mb_email">이메일<strong class="sound_only">필수</strong></label></th>
        <td>
            <div class="flex gap-3">
                <input type="text" name="mb_email" value="<?=$mb['mb_email']?>" id="reg_mb_email" class="frm_input w-[200px]" size="15"  maxlength="100">
                <span class="s_email_info"></span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_hp">휴대폰번호<strong class="sound_only">필수</strong></label></th>
        <td>
            <div class="flex gap-3">
                <input type="text" name="mb_hp" value="<?=formatPhoneNumber($mb['mb_hp'])?>" id="reg_mb_hp" class="frm_input w-[200px]" size="15"  maxlength="20">
                <span class="s_hp_info"></span>
            </div>
        </td>
        <th scope="row"><label for="mb_level">권한등급</label></th>
        <td>
            <select name="mb_level" id="mb_level" class="frm_input">
                <option value="6">lv.6</option>
                <option value="7">lv.7</option>
                <option value="8">lv.8</option>
                <option value="9">lv.9</option>
            </select>
            <script>
            const mb_level = document.querySelector('#mb_level');
            mb_level.value = '<?=$mb['mb_level']?>';
            </script>
        </td>
    </tr>
    <tr>
        <th scope="row">주소</th>
        <td class="td_addr_line">
            <label for="mb_zip" class="sound_only">우편번호</label>
            <input type="text" name="mb_zip" value="<?php echo $mb['mb_zip1'] . $mb['mb_zip2']; ?>" id="mb_zip" class="frm_input readonly w-[60px]" size="5" maxlength="6">
            <button type="button" class="btn_frmline" onclick="win_zip('fmember', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소 검색</button><br>
            <input type="text" name="mb_addr1" value="<?php echo $mb['mb_addr1'] ?>" id="mb_addr1" class="frm_input readonly" size="60">
            <label for="mb_addr1">기본주소</label><br>
            <input type="text" name="mb_addr2" value="<?php echo $mb['mb_addr2'] ?>" id="mb_addr2" class="frm_input" size="60">
            <label for="mb_addr2">상세주소</label>
            <br>
            <input type="text" name="mb_addr3" value="<?php echo $mb['mb_addr3'] ?>" id="mb_addr3" class="frm_input" size="60">
            <label for="mb_addr3">참고항목</label>
            <input type="hidden" name="mb_addr_jibeon" value="<?php echo $mb['mb_addr_jibeon']; ?>"><br>
        </td>
        <th scope="row">사원관련파일</th>
        <td>
            <?php echo help("사원관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file_emp" name="emp_datas[]" multiple class="">
            <?php
            if(@count($emp['emp_f_arr'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($emp['emp_f_arr']);$i++) {
                    echo "<li>[".($i+1).']'.$emp['emp_f_arr'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_memo">메모</label></th>
        <td colspan="3">
            <textarea name="mb_memo" id="mb_memo"><?=$mb['mb_memo']?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_datetime">입사일<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="mb_datetime" value="<?=substr($mb['mb_datetime'],0,10)?>" id="mb_datetime" maxlength="100" readonly class="tms_date readonly frm_input datetime w-[100px]" size="30">
        </td>
        <th scope="row"><label for="mb_leave_date">퇴사일자</label></th>
        <td>
            <input type="text" name="mb_leave_date" value="<?=formatDate($mb['mb_leave_date'])?>" id="mb_leave_date" class="readonly tms_date frm_input w-[100px]" maxlength="8">
        </td>
    </tr>
    <tr>
        <th scope="row">
            관리페이지(권한설정)<br>
            <span id="all_auth_del" class="<?=$all_auth_del_btn?>">전체권한삭제</span>
        </th>
        <td colspan="3" class="<?=$menu_h3_class?> <?=$mneu_hs_class?> <?=$menu_ul_class?> <?=$menu_li_class?> <?=$menu_sp_class?>">
            <?php
            // print_r2($member_auth_menus);
            // print_r2($menu);
            // echo $menu_list_tag;
            // print_r2($auth_arr);//[100300]= array(r,w);
            // print_r2($auth_list);//[0] = 100300_r_w
            $auths_str = (count($auth_list)) ? implode(',',$auth_list) : '';
            ?>
            <label for="auth_renewal" class="<?=$auth_renewal_label?>">
                <input type="checkbox" name="auth_renewal" id="auth_renewal" value="1" class="border"> 메뉴권한재설정
            </label>
            <input type="hidden" name="auths" value="<?=$auths_str?>" class="border w-full">
            <?php
            // print_r2($menu_main_titles);
            //전체 메뉴구조를 확인하려면 변수($menu_list_tag_)맨끝에 '_'를 제거하세요.
            // echo $menu_list_tag 일때만 메뉴구조 확인 가능
            if($member['mb_level'] == 10){ echo $menu_list_tag_??''; }
            $auth_list_tag = '<div class="auth_box">'.PHP_EOL;
            foreach($menu2 as $k => $v){
                if(in_array($k,$set_menu['set_hide_mainmenus_arr'])) continue;
                if(count($v)){
                    $auth_list_tag .= '<div><h3 class="auth_h3" style="font-size:0.9rem;">'.$menu_main_titles[$k];
                    $auth_list_tag .= '<span class="group_y">그룹권한부여</span>';
                    $auth_list_tag .= '<span class="group_n">그룹권한삭제</span>';
                    $auth_list_tag .= '</h3><ul class="auth_ul">'.PHP_EOL;
                    foreach($v as $i => $s){
                        if($i == 0) continue;
                        if(in_array($s[0],$set_menu['set_hide_submenus_arr'])) continue;
                        $auths = $auth_arr[$s[0]] ?? [];
                        $auth_list_tag .= '<li data-code="'.$s[0].'" class="auths'.(array_key_exists($s[0], $auth_arr) ? ' act' : '').'">'.$s[1];
                        $auth_list_tag .= '<span class="auth_r'.(in_array('r', $auths) ? ' act' : '').'">읽기</span>';
                        $auth_list_tag .= '<span class="auth_w'.(in_array('w', $auths) ? ' act' : '').'">쓰기</span>';
                        $auth_list_tag .= '<span class="auth_d'.(in_array('d', $auths) ? ' act' : '').'">삭제</span>';
                        $auth_list_tag .= '</li>' . PHP_EOL;
                    }
                    $auth_list_tag .= '</ul></div>'.PHP_EOL;  
                }
            }
            $auth_list_tag .= '</div>'.PHP_EOL;
            echo $auth_list_tag;
            ?>
        </td>
    </tr>
    </tbody>
	</table>
</div>
<div class="btn_fixed_top">
    <a href="./employee_list.php?<?=$qstr?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/employee_list_update.php
```php
<?php
$sub_menu = "920600";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if(!$is_super && !$is_manager)
    alert('관리권한이 없습니다.');

// print_r2($_POST);exit;
$msg = '';

if ($_POST['act_button'] == "선택수정") {
    for ($i=0; $i<count($_POST['chk']); $i++){
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql_mb_leave_date = '';
        if($mb_leave_date[$k]){
            $mb_level[$k] = 1;
            $mb_leave_date[$k] = preg_replace('/[^0-9]/', '', $mb_leave_date[$k]); // 숫자만 추출해서 재대입
            $sql_mb_leave_date = " , mb_leave_date = '{$mb_leave_date[$k]}' ";
        }

        $mb_datetime[$k] = $mb_datetime[$k].' '.date('H:i:s');

        $sql = " UPDATE {$g5['member_table']} SET
                    mb_level = '{$mb_level[$k]}'
                    , mb_datetime = '{$mb_datetime[$k]}'
                    {$sql_mb_leave_date}
                WHERE mb_id = '{$mb_id[$k]}' ";
        sql_query($sql,1);
        
        // 메타테이블에 저장
        meta_update(array("mta_db_tbl"=>"member","mta_db_idx"=>$mb_id[$k],"mta_key"=>'mb_department',"mta_value"=>$mb_department[$k]));
        meta_update(array("mta_db_tbl"=>"member","mta_db_idx"=>$mb_id[$k],"mta_key"=>'mb_rank',"mta_value"=>$mb_rank[$k]));
        meta_update(array("mta_db_tbl"=>"member","mta_db_idx"=>$mb_id[$k],"mta_key"=>'mb_role',"mta_value"=>$mb_role[$k]));
    }
}
else if($_POST['act_button'] == "선택퇴사"){
    $leave_date = preg_replace('/[^0-9]/', '', G5_TIME_YMD); // 숫자만 추출해서 대입
    for ($i=0; $i<count($_POST['chk']); $i++){
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = " UPDATE {$g5['member_table']} SET
                    mb_level = 1,
                    mb_leave_date = '{$leave_date}'
                WHERE mb_id = '{$mb_id[$k]}' ";
        sql_query($sql,1);
    }
}

foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

goto_url('./employee_list.php?'.$qstr);
```
### adm/_z01/employee_list.php
```php
<?php
$sub_menu = "920600";
include_once('./_common.php');

include_once(G5_ZSQL_PATH.'/term_department.php');
include_once(G5_ZSQL_PATH.'/term_rank.php');
include_once(G5_ZSQL_PATH.'/term_role.php');

@auth_check($auth[$sub_menu], 'r');

$form_input = '';
// 추가적인 검색조건 (ser_로 시작하는 검색필드)
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}


$sql_common = " FROM {$g5['member_table']} "; 

$where = array();
$where[] = " mb_level >= 6 ";   // 디폴트 검색조건
$where[] = " mb_level < 10 ";   // 디폴트 검색조건


// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mb_level' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'mb_hp' ) :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "mb_level";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", mb_name";
    $sod2 = "";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";
// echo $sql_order.BR;exit;
$sql = " SELECT COUNT(*) AS cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql,1);
$total_count = $row['cnt'];
// echo $sql.BR;

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$sql = " SELECT * {$sql_common} {$sql_search} {$sql_order} LIMIT {$from_record}, {$rows} ";
// echo $sql.BR;exit;
$result = sql_query($sql);

$colspan = 12;

$g5['title'] = '사원관리';
require_once G5_ADMIN_PATH.'/admin.head.php';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="mb_name"<?php echo get_selected($_GET['sfl']??'', "mb_name"); ?>>이름</option>
    <option value="mb.mb_id"<?php echo get_selected($_GET['sfl']??'', "mb.mb_id"); ?>>아이디</option>
    <option value="mb_email"<?php echo get_selected($_GET['sfl']??'', "mb_email"); ?>>E-MAIL</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl']??'', "mb_hp"); ?>>휴대폰번호</option>
</select>
<label for="stx" class="sound_only">검색어</label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>사원의 회원등급은 기본 6이상입니다.</p>
</div>

<form name="fmemberlist" id="fmemberlist" action="./employee_list_update.php" onsubmit="return fmemberlist_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sst2" value="<?php echo $sst2 ?>">
<input type="hidden" name="sod2" value="<?php echo $sod2 ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<?=$form_input?>
<div class="tbl_head01 tbl_wrap">
<table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="mb_list_chk">
            <label for="chkall" class="sound_only">사원 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('mb_name') ?>이름</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_id') ?>아이디</a></th>
        <th scope="col">직급</th>
        <th scope="col">직책</th>
        <th scope="col">부서</th>
        <th scope="col">휴대폰</th>
        <th scope="col">이메일</th>
        <th scope="col">권한등급</th>
        <th scope="col"><?php echo subject_sort_link('mb_datetime', '', 'desc') ?>입사일</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_leave_date', '', 'desc') ?>퇴사일</a></th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) { 
        $mta_mb_arr = get_meta('member',$row['mb_id']);
        if(count($mta_mb_arr)){
            $row = array_merge($row,$mta_mb_arr);
        }
        unset($mta_mb_arr);
        $s_mod = '<a href="./employee_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$row['mb_id'].'" class="btn btn_03">수정</a>';
    ?>
    <tr class="<?php echo $bg; ?>" tr_id="<?=$row['mb_id']?>">
        <td class="td_chk">
            <input type="hidden" name="mb_id[<?=$i?>]" value="<?=$row['mb_id']?>" id="mb_id_<?=$i?>">
            <label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['mb_name'])?> <?=get_text($row['mb_nick'])?>님</label>
            <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
        </td><!--선택-->
        <td class="td_mb_name w-[140px]"><?php echo get_text($row['mb_name']); ?></td><!--이름-->
        <td class="td_mb_id"><?php echo $row['mb_id']; ?></td><!--아이디-->
        <td class="td_mb_rank w-[100px]">
            <select name="mb_rank[<?=$i?>]" id="mb_rank_<?=$i?>">
                <option value="">::직급선택::</option>
                <?=$rank_opt?>
            </select>
            <script>$('#mb_rank_<?=$i?>').val('<?=$row['mb_rank']?>');</script>
        </td><!--직급-->
        <td class="td_mb_role w-[100px]">
            <select name="mb_role[<?=$i?>]" id="mb_role_<?=$i?>">
                <option value="">::직책선택::</option>
                <?=$role_opt?>
            </select>
            <script>$('#mb_role_<?=$i?>').val('<?=$row['mb_role']?>');</script>
        </td><!--직책-->
        <td class="td_mb_department w-[200px]">
            <select name="mb_department[<?=$i?>]" id="mb_department_<?=$i?>">
                <option value="">::부서선택::</option>
                <?=$department_opt?>
            </select>
            <script>$('#mb_department_<?=$i?>').val('<?=$row['mb_department']?>');</script>
        </td><!--부서-->
        <td class="td_hp w-[150px]"><?=formatPhoneNumber($row['mb_hp'])?></td><!--휴대폰-->
        <td class="td_mb_email w-[250px]"><?php echo $row['mb_email']; ?></td><!--이메일-->
        <td class="td_level w-[100px]">
            <select name="mb_level[<?=$i?>]" id="mb_level_<?=$i?>">
                <option value="6">lv.6</option>
                <option value="7">lv.7</option>
                <option value="8">lv.8</option>
                <option value="9">lv.9</option>
            </select>
            <script>$('#mb_level_<?=$i?>').val('<?=$row['mb_level']?>');</script>
        </td><!--권한등급-->
        <td class="td_date w-[100px]">
            <input type="text" class="required_date w-[90px] border" name="mb_datetime[<?=$i?>]" id="mb_datetime_<?=$i?>" value="<?=substr($row['mb_datetime'],0,10)?>">
        </td><!--입사일-->
        <td class="td_date w-[100px]">
            <input type="text" class="tms_date w-[90px] border" name="mb_leave_date[<?=$i?>]" id="mb_leave_date_<?=$i?>" value="<?=formatDate($row['mb_leave_date'])?>">
        </td><!--퇴사일-->
        <td class="td_mng td_mng_s"><?php echo $s_mod ?></td><!--관리-->
        <script>
            single_date('#mb_leave_date_<?=$i?>');
            single_date('#mb_intercept_date_<?=$i?>');
        </script>
    </tr>
    <?php 
    } 
    if ($i == 0)
        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
</table>
</div>

<div class="btn_fixed_top">
    <?php if ($is_manager){ // (!auth_check($auth[$sub_menu],'w',1)) { //($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02" style="display:no ne;">
    <input type="submit" name="act_button" value="선택퇴사" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./employee_form.php" id="member_add" class="btn btn_01">사원추가</a>
    <?php } ?>
</div>
</form>
<script>
function fmemberlist_submit(f){
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택퇴사") {
        if(!confirm("선택한 사원을 정말 퇴사처리 하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/rank_list_update.php
```php
<?php
$sub_menu = "920500";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu],"w");

//print_r2($trm_idx);
//echo "<br>==========<br>";
// print_r2($_POST);
// exit;

//-- depth 설정 및 공백 체크
$prev_depth = 0;
for($i=0;$i<sizeof($trm_depth);$i++) {
	if($i==0 && $trm_depth[$i] > 0) {
		alert('맨 처음 항목은 최상위 레벨이어야 합니다. 단계 설정을 확인해 주세요.');
	}
	if($trm_depth[$i] - $prev_depth > 1) {
		alert(trim($trm_name[$i]) + ' : 단계 설정에 문제가 있습니다. \n\n순서대로 하위 단계를 설정해 주세요.');
	}
	if(trim($trm_name[$i]) == "") {
		alert('직급명이 공백인 항목이 있습니다. \n\n확인하시고 다시 진행해 주세요.');
	}
	$prev_depth = $trm_depth[$i]; 
}
// print_r2($trm_desc);
// exit;

//-- 먼저 left, right 값 초기화
$sql = " UPDATE {$g5['term_table']} SET trm_left = '0', trm_right = '0' WHERE trm_category = '".$category."' ";
sql_query($sql);

// print_r2($trm_desc);exit;
$depth_array = array();
$idx_array = array();	// 부모 idx를 입력하기 위한 정의
$prev_depth = 0;
for($i=0;$i<sizeof($trm_name);$i++) {
    
	//-- leaf node(마지막노드) 체크 / $depth_array[$trm_depth[$i]] = 1
	$depth_array[$trm_depth[$i]]++;	// 형제 갯수를 체크
	if($trm_depth[$i] < $prev_depth) {
		//echo $prev_depth - $trm_depth[$i]."만큼 작아졌네~".$prev_depth."<br>";
		for($j=$trm_depth[$i]+1;$j <= $prev_depth;$j++) {
			//echo $j.'<br>';
			$depth_array[$j] = 0;
		}
	}

    // 정렬번호
    if(!$trm_sort[$i])
        $trm_sort[$i] = $i;

	
	//-- 맨 처음 항목 입력 left=1, right=2 설정
	if($i == 0) {
		$sql = "INSERT INTO {$g5['term_table']} (trm_idx,trm_idx_parent,trm_name,trm_name2,trm_type,trm_category,trm_desc,trm_sort,trm_left,trm_right,trm_status,trm_reg_dt) 
					VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','$trm_desc[$i]','$i', 1, 2, '".$trm_status[$i]."', now())
					ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
                                            , trm_name = '$trm_name[$i]'
                                            , trm_name2 = '$trm_name2[$i]'
                                            , trm_type = '$trm_type[$i]'
                                            , trm_desc = '".$trm_desc[$i]."'
                                            , trm_sort = '".$trm_sort[$i]."'
                                            , trm_status = '".$trm_status[$i]."'
                                            , trm_left = 1
                                            , trm_right = 2
		";
		sql_query($sql,1);
		echo $sql.'<br><br>';
	}
	else {

		//-- leaf_node 이면 부모 idx를 참고해서 left, right 생성
		if($depth_array[$trm_depth[$i]] == 1) {
			//echo '부모idx -> '.$idx_array[$trm_depth[$i]-1];

			sql_query("SELECT @myLeft := trm_left FROM {$g5['term_table']} WHERE trm_idx = '".$idx_array[$trm_depth[$i]-1]."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_right = trm_right + 2 WHERE trm_right > @myLeft AND trm_category = '".$category."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_left = trm_left + 2 WHERE trm_left > @myLeft AND trm_category = '".$category."' ");
			$sql = "INSERT INTO {$g5['term_table']} (trm_idx, trm_idx_parent, trm_name, trm_name2, trm_type, trm_category, trm_desc, trm_sort, trm_left, trm_right, trm_status, trm_reg_dt) 
						VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','".$trm_desc[$i]."','$i',@myLeft + 1,@myLeft + 2, '".$trm_status[$i]."', now())
						ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
							, trm_name = '$trm_name[$i]'
							, trm_name2 = '$trm_name2[$i]'
							, trm_type = '$trm_type[$i]'
							, trm_desc = '".$trm_desc[$i]."'
							, trm_sort = '".$trm_sort[$i]."'
							, trm_status = '".$trm_status[$i]."'
							, trm_left = @myLeft + 1
							, trm_right = @myLeft + 2
			";
			sql_query($sql,1);
			echo $sql.'<br><br>';
		}
		//-- leaf_node가 아니면 동 레벨 idx 참조해서 left, right 생성
		else {
			sql_query("SELECT @myRight := trm_right FROM {$g5['term_table']} WHERE trm_idx = '".$idx_array[$trm_depth[$i]]."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_right = trm_right + 2 WHERE trm_right > @myRight AND trm_category = '".$category."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_left = trm_left + 2 WHERE trm_left > @myRight AND trm_category = '".$category."' ");
			$sql = "INSERT INTO {$g5['term_table']} (trm_idx, trm_idx_parent, trm_name, trm_name2, trm_type, trm_category, trm_desc, trm_sort, trm_left, trm_right, trm_status, trm_reg_dt) 
						VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','".$trm_desc[$i]."','$i',@myRight + 1,@myRight + 2, '".$trm_status[$i]."', now())
						ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
							, trm_name = '$trm_name[$i]'
							, trm_name2 = '$trm_name2[$i]'
							, trm_type = '$trm_type[$i]'
							, trm_desc = '".$trm_desc[$i]."'
							, trm_sort = '".$trm_sort[$i]."'
							, trm_status = '".$trm_status[$i]."'
							, trm_left = @myRight + 1
							, trm_right = @myRight + 2
			";
			sql_query($sql,1);
			echo $sql.'<br><br>';
		}
	}
	
	//echo "<br><br>";
	$prev_depth = $trm_depth[$i]; 
	$idx_array[$trm_depth[$i]] = $trm_idx[$i];	//-- left, right 기준 값 저장
	$idx_array[$trm_depth[$i]] = sql_insert_id();	//-- left, right 기준 값 저장
}


// 캐시 파일 삭제 (초기화)
$files = glob(G5_DATA_PATH.'/cache/term-'.$category.'.php');
if (is_array($files)) {
    foreach ($files as $filename)
        unlink($filename);
}


//exit;
// 앞에서 넘어온 파일명으로 다시 돌려보낸다.
goto_url("./".$file_name.".php?category=".$category);
?>
```
### adm/_z01/rank_list.php
```php
<?php
$sub_menu = "920500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 용어 설정
$category = ($category) ? $category : 'rank';


// include_once(G5_ZSQL_PATH.'/term_rank.php');

//아래 AND 조건절에 비어있는 줄에는 원래 아래와 같은 내용은 있었다 (2군데)
//AND term.trm_status = 'ok' AND parent.trm_status = 'ok'
//-- 카테고리 구조 추출 --//
$sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor member: 루트 노드를 가져옴
    SELECT
        trm_idx,
        trm_name,
    	trm_name2,
        CAST(trm_name AS CHAR(255)) AS path,
        trm_desc,
        trm_left,
        trm_right,
		trm_status,
        0 AS depth,
        (SELECT COUNT(*) > 0 FROM {$g5['term_table']} AS child WHERE child.trm_idx_parent = {$g5['term_table']}.trm_idx AND child.trm_status != 'trash') AS has_children
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0  -- 루트 노드
        AND trm_category = 'rank'
        AND trm_status != 'trash'

    UNION ALL

    -- Recursive member: 하위 노드들을 경로와 함께 가져옴
    SELECT
        t.trm_idx,
        t.trm_name,
    	t.trm_name2,
        CONCAT(tp.path, ' > ', t.trm_name) AS path,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
		t.trm_status,
        (SELECT COUNT(*)
        FROM {$g5['term_table']} AS parent
        WHERE parent.trm_left < t.trm_left
        AND parent.trm_right > t.trm_right) AS depth,
        (SELECT COUNT(*) > 0 FROM {$g5['term_table']} AS child WHERE child.trm_idx_parent = t.trm_idx AND child.trm_status != 'trash') AS has_children
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = 'rank'
        AND t.trm_status != 'trash'
)
SELECT trm_idx, trm_name, trm_name2, path, trm_desc, trm_left, trm_right, trm_status, depth, has_children FROM TermPaths ORDER BY trm_left;
";

$result = sql_query($sql);
$total_count = sql_num_rows($result);

$g5['title'] = '직급관리';
require_once G5_ADMIN_PATH.'/admin.head.php';
?>


<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총분류수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>개 </span></span>
</div>

<form name="fcarlist" method="post" action="./department_list_update.php" autocomplete="off">
<input type="hidden" name="category" value="<?php echo $category; ?>">
<input type="hidden" name="file_name" value="<?php echo $g5['file_name']; ?>">

<div id="sct" class="tbl_head02 tbl_wrap">
<table id="table01_list">
<caption><?php echo $g5['title']; ?> 목록</caption>
<thead>
<tr>
    <th scope="col" style="width:6%">트리구조</th>
    <th scope="col" style="width:15%">직급명</th>
    <th scope="col" style="width:10%">직급명2</th>
    <th scope="col" style="width:7%"><a href="javascript:" id="sub_toggle">닫기</a></th>
    <th scope="col" style="width:20%">설명</th>
    <th scope="col" style="width:10%">위치이동</th>
    <th scope="col" style="width:5%;white-space:nowrap;">고유코드</th>
	<th scope="col" style="width:6%">숨김</th>
    <th scope="col" style="width:6%">관리</th>
</tr>
</thead>
<tbody>
	<!-- 항목 추가를 위한 DOM (복제후 제거됨) -->
	<tr class="" style="display:none">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_trm_name" style="padding-left:10px">
			<input type="hidden" name="trm_depth[]" value="0">
			<input type="hidden" name="trm_idx[]" value="">
			<input type="text" name="trm_name[]" value="직급명을 입력하세요" required class="frm_input full_input required" style="width:180px;">
	    </td>
	    <td class="td_trm_name" style="padding-left:10px">
			<input type="text" name="trm_name2[]" value="" class="frm_input full_input" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><a href="#">열기</a></td>
	    <td class="td_trm_content"><!-- 삭제조직코드 -->
	        <input type="text" name="trm_desc[]" value="" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center">
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
		<td class="td_idx" style="text-align:center"></td>
		<td class="td_use" style="text-align:center">
			<input type="hidden" name="trm_status[]" value="ok">
			<input type="checkbox" name="trm_use[]">
	    </td>
	    <td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
	<!-- //항목 추가를 위한 DOM (복제후 제거됨) -->
<?php
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	// print_r2($row);
	//-- 들여쓰기
	$row['indent'] = ($row['depth']) ? $row['depth']*50:10;
	
	//-- 하위 열기 닫기
	//$row['sub_toggle'] = ($row['depth']==0) ? '<a href="#">닫기</a>':'-';
	$row['sub_toggle'] = ($row['has_children']==1) ? '<a href="#">닫기</a>':'-';
	
    // 추가 부분 unserialize
    $unser = unserialize(stripslashes($row['trm_more']));
    if( is_array($unser) ) {
        foreach ($unser as $key=>$value) {
            //print_r3($key.'/'.$value);
            $row[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
        }    
    }
	
	$usechecked = ($row['trm_status'] == 'ok') ? '':'checked';
	$status_txt = ($row['trm_status'] == 'ok') ? 'ok':'hide';
    $bg = 'bg'.($i%2);
?>
	<tr class="<?php echo $bg; ?>">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_trm_name" style="padding-left:<?=$row['indent']?>px;text-align:left;">
			<input type="hidden" name="trm_depth[]" value="<?=$row['depth']?>">
			<input type="hidden" name="trm_idx[]" value="<?=$row['trm_idx']?>">
			<input type="text" name="trm_name[]" value="<?php echo get_text(trim($row['trm_name'])); ?>" required class="frm_input full_input required" style="width:180px;">
	    </td>
	    <td class="td_trm_name2">
			<input type="text" name="trm_name2[]" value="<?php echo get_text(trim($row['trm_name2'])); ?>" class="frm_input full_input" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><?=$row['sub_toggle']?></td>
	    <td class="td_trm_content"><!-- 삭제조직코드 -->
	        <input type="text" name="trm_desc[]" value="<?php echo get_text(trim($row['trm_desc'])); ?>" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center"><!-- 위치이동 -->
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
	    <td class="td_idx" style="text-align:center"><!-- 코유코드 -->
			<?=$row['trm_idx']?>
	    </td>
	    <td class="td_use" style="text-align:center"><!-- 숨김 -->
			<input type="hidden" name="trm_status[]" value="<?=$status_txt?>">
	        <input type="checkbox" name="trm_use[]" <?=$usechecked?>>
	    </td>
		<td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
<?php }
if ($i == 0) echo "<tr class=\"no-data\"><td colspan=\"9\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
?>
</tbody>
</table>
</div>

<div class="btn_fixed_top">
    <a href="javascript:insert_item()" id="btn_add_car" class="btn btn_02">항목추가</a>
	<input type="submit" name="act_button" value="확인" class="btn_submit btn">
</div>
</form>

<script>
//----------------------------------------------
$(function() {
    
    // 엑셀업로드 창 열기
    $(document).on('click','#btn_excel_upload',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        win_excel_upload = window.open(href, "win_excel_upload", "left=100,top=100,width=520,height=600,scrollbars=1");
        win_excel_upload.focus();
    });
    
	
	//-- DOM 복제 & 생성 & 초기화 --//
	list_dom01=$("#table01_list tbody");
	orig_dom01=list_dom01.find("tr").eq(0).clone();
	list_dom01.find("tr:eq(0)").remove();	// 복제한 후에 제거
	list01_nothing_display();

	//-- 정렬(Sortable) --//
	var fixHelperModified = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index)
		{
			$(this).width($originals.eq(index).width());
		});
		return $helper;
	};

	$("#table01_list tbody").sortable({
		cancel: "input, textarea, a, i"
		, helper: fixHelperModified
		, items: "tr:not(.no-data)"
		, placeholder: "tr-placeholder"
		, connectWith: "#table01_list tr:not(.no-data)"
		, stop: function(event, ui) {
			//alert(ui.item.html());
			//-- 정렬 후 처리 / 맨 처음 항목이면 최상위 레벨이어야 함
			if($(this).find('tr').index(ui.item) == 0 && ui.item.find('input[name^=trm_depth]').val() > 0) {
				ui.item.find('input[name^=trm_depth]').val(0);
				ui.item.find('.td_trm_name').css('padding-left','0px');
			}
			
			setTimeout(function(){ ui.item.removeAttr('style'); }, 10);
		}
	});
	
	//=====================================================카테고리 사용여부=========================== 
	$('input[type="checkbox"]').click(function(){
		if($(this).is(":checked")){
			$(this).siblings('input[type="hidden"]').val('hide');
			//alert($(this).siblings('input[type="hidden"]').val());
		}else{
			$(this).siblings('input[type="hidden"]').val('ok');
			//alert($(this).siblings('input[type="hidden"]').val());
		}
	});

	//-- 차종추가 경고창 초기 설정
	alert_flag = true; 


	//-- 단계이동 버튼 클릭 --//
//	$('.td_depth a').live('click',function(e) {
	$(document).on('click','.td_depth a',function(e) {
		e.preventDefault();
		//-- 맨 처음 항목은 무조건 최상위 단계이어야 함
		if($(this).parents('tbody:first').find('tr').index($(this).parents('tr:first')) == 0 && $(this).parent().find('a').index($(this)) == 1) {
			alert('맨 처음 항목은 최상위 레벨이어야 합니다. \n\n단계 2로 이동할 수 없습니다.');
			return false;
		}
		
		//-- depth 값 업데이트
		var indent_sign_value = ($(this).parent().find('a').index($(this)) == 0)? -1:1;
		var new_depth = parseInt($(this).parents('tr:first').find('input[name^=trm_depth]').val()) + indent_sign_value;
		if(new_depth < 0) new_depth = 0;
		$(this).parents('tr:first').find('input[name^=trm_depth]').val(new_depth);
		
		//-- 들여쓰기 적용
		var indent_value = (new_depth) ? new_depth * 50:10;
		$(this).parents('tr:first').find('.td_trm_name').css('padding-left',indent_value+'px');
		
		//update_notice();	//-- [일괄수정] 버튼 활성화
	});


	//-- 위치이동 버튼 클릭 --//
//	$('.td_sort a').live('click',function(e) {
	$(document).on('click','.td_sort a',function(e) {
		e.preventDefault();

		var target_tr = $(this).parents('tr:first').clone().hide();
		var flag_up_down = ($(this).parent().find('a').index($(this)) == 0)? 'up':'down';
		var tr_loc = $(this).parents('tbody:first').find('tr').index($(this).parents('tr:first'));
		

		if(flag_up_down == "up" && tr_loc == 0) {
			alert('맨 처음 항목입니다. 더 이상 올라갈 때가 없지 않나요?');
			return false;
		}
		else if(flag_up_down == "down" && tr_loc == $(this).parents('tbody:first').find('tr').length - 1) {
			alert('마지막 항목입니다. 보면 알잖아요~');
			return false;
		}

		$(this).parents('tr:first').stop(true,true).fadeOut('fast',function(){
			$(this).remove();

			if(flag_up_down == "up") {
				target_tr.insertBefore($('#table01_list tbody tr').eq(parseInt(tr_loc)-1)).stop(true,true).fadeIn('fast').removeAttr('style');
			}
			else {
				target_tr.insertAfter($('#table01_list tbody tr').eq(tr_loc)).stop(true,true).fadeIn('fast').removeAttr('style');
			}

		});

		//update_notice();	//-- Submit 버튼 활성화
	});


	//-- 삭제 버튼 클릭시 --//
//	$('.td_del a').live('click',function(e) {
	$(document).on('click','.td_del a',function(e) {
		e.preventDefault();
		
		//-- 추가된 항목은 바로 삭제, 기 등록된 조직은 관련 작업 진행
		if(confirm('하위 카테고리 전체 및 소속 항목들이 전부 삭제됩니다. \n\n후회할 수도 있을 텐데~~ 정말 삭제하시겠습니까?')) {
			if($(this).parents('tr:first').find('input[name^=trm_idx]').val()) {

				//-- 삭제 함수 호출(마지막인수는 trash변경할지 아예 삭제할지의 영부인데 0이면 trash, 1이면 완전삭제)
				trm_delete($(this).parents('tr:first').find('input[name^=trm_idx]').val(),1);

			}
			else {
				$(this).parents('tr:first').remove();
			}
			
			//update_notice();	//-- Submit 버튼 활성화
		}
	});


	//-- 닫기 열기
	$('#sub_toggle').click(function() {
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		$('#table01_list tbody tr').find('input[name^=trm_depth]').each(function() {
			if($(this).val() > 0) {
				if(this_text == "닫기")
					$(this).closest('tr').hide();
				else 
					$(this).closest('tr').show();
			}
			else {
				if(this_text == "닫기") {
					$(this).closest('tr').find('.td_sub_category a').text('열기');
				}
				else 
					$(this).closest('tr').find('.td_sub_category a').text('닫기');
			}
		});
	});


	//-- 서브 부분만 열고 닫기
//	$('.td_sub_category a').live('click',function(e) {
	$(document).on('click','.td_sub_category a',function(e) {
		e.preventDefault();
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		
		var this_depth = $(this).closest('tr').find('input[name^=trm_depth]').val();
		var this_sub_flag = false;
		
		$(this).closest('tr').nextAll('tr').each(function() {
			if($(this).find('input[name^=trm_depth]').val() > this_depth && this_sub_flag == false) {

				if(this_text == "닫기")
					$(this).hide();
				else
					$(this).show();
			}
			else 
				this_sub_flag = true;
		});
	});



});
//----------------------------------------------


//-- 01 No data 처리 --//
function list01_nothing_display() {
	if(list_dom01.find("tr:not(.no-data)").length == 0)
		list_dom01.find('.no-data').show();
	else 
		list_dom01.find('.no-data').hide();
}
//-- //01 No data 처리 --//


//-- 테이블 항목 추가
function insert_item() {
	//-- DOM 복제
	sDom = orig_dom01.clone();

	//-- DOM 입력
	//sDom.insertBefore($('#table01_list tbody tr').eq(0)).show();
	//$('#table01_list tbody tr').eq(0).find('input[name^=trm_name]').select().focus();
	$('#table01_list tbody').append(sDom.show());
	$('#table01_list tbody tr:last').find('input[name^=trm_name]').select().focus();

	list01_nothing_display();
	
	if(alert_flag == true) {
		alert('입력항목을 작성한 후 하단의 [일괄수정] 버튼을 클릭하여 적용해 주시면 됩니다.');
		alert_flag = false;
	}
}


//-- 항목 삭제 함수 --//
function trm_delete(this_trm_idx, fn_delte) {
	//-- 디버깅 Ajax --//
	$.ajax({
		url:'./ajax/term_delete.php',
		type:'get',
		data:{"category":"<?=$category?>", "trm_idx":this_trm_idx,"delete":fn_delte},
		dataType:'json',
		timeout:3000, 
		beforeSend:function(){},
		success:function(data){
			self.location.reload();
		},
		error:function(xmlRequest) {
			alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
			+ ' \n\rresponseText: ' + xmlRequest.responseText);
		} 
	//-- 디버깅 Ajax --//

	});	
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/role_list_update.php
```php
<?php
$sub_menu = "920400";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu],"w");

//print_r2($trm_idx);
//echo "<br>==========<br>";
// print_r2($_POST);
// exit;

//-- depth 설정 및 공백 체크
$prev_depth = 0;
for($i=0;$i<sizeof($trm_depth);$i++) {
	if($i==0 && $trm_depth[$i] > 0) {
		alert('맨 처음 항목은 최상위 레벨이어야 합니다. 단계 설정을 확인해 주세요.');
	}
	if($trm_depth[$i] - $prev_depth > 1) {
		alert(trim($trm_name[$i]) + ' : 단계 설정에 문제가 있습니다. \n\n순서대로 하위 단계를 설정해 주세요.');
	}
	if(trim($trm_name[$i]) == "") {
		alert('직책명이 공백인 항목이 있습니다. \n\n확인하시고 다시 진행해 주세요.');
	}
	$prev_depth = $trm_depth[$i]; 
}
// print_r2($trm_desc);
// exit;

//-- 먼저 left, right 값 초기화
$sql = " UPDATE {$g5['term_table']} SET trm_left = '0', trm_right = '0' WHERE trm_category = '".$category."' ";
sql_query($sql);

// print_r2($trm_desc);exit;
$depth_array = array();
$idx_array = array();	// 부모 idx를 입력하기 위한 정의
$prev_depth = 0;
for($i=0;$i<sizeof($trm_name);$i++) {
    
	//-- leaf node(마지막노드) 체크 / $depth_array[$trm_depth[$i]] = 1
	$depth_array[$trm_depth[$i]]++;	// 형제 갯수를 체크
	if($trm_depth[$i] < $prev_depth) {
		//echo $prev_depth - $trm_depth[$i]."만큼 작아졌네~".$prev_depth."<br>";
		for($j=$trm_depth[$i]+1;$j <= $prev_depth;$j++) {
			//echo $j.'<br>';
			$depth_array[$j] = 0;
		}
	}

    // 정렬번호
    if(!$trm_sort[$i])
        $trm_sort[$i] = $i;

	
	//-- 맨 처음 항목 입력 left=1, right=2 설정
	if($i == 0) {
		$sql = "INSERT INTO {$g5['term_table']} (trm_idx,trm_idx_parent,trm_name,trm_name2,trm_type,trm_category,trm_desc,trm_sort,trm_left,trm_right,trm_status,trm_reg_dt) 
					VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','$trm_desc[$i]','$i', 1, 2, '".$trm_status[$i]."', now())
					ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
                                            , trm_name = '$trm_name[$i]'
                                            , trm_name2 = '$trm_name2[$i]'
                                            , trm_type = '$trm_type[$i]'
                                            , trm_desc = '".$trm_desc[$i]."'
                                            , trm_sort = '".$trm_sort[$i]."'
                                            , trm_status = '".$trm_status[$i]."'
                                            , trm_left = 1
                                            , trm_right = 2
		";
		sql_query($sql,1);
		echo $sql.'<br><br>';
	}
	else {

		//-- leaf_node 이면 부모 idx를 참고해서 left, right 생성
		if($depth_array[$trm_depth[$i]] == 1) {
			//echo '부모idx -> '.$idx_array[$trm_depth[$i]-1];

			sql_query("SELECT @myLeft := trm_left FROM {$g5['term_table']} WHERE trm_idx = '".$idx_array[$trm_depth[$i]-1]."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_right = trm_right + 2 WHERE trm_right > @myLeft AND trm_category = '".$category."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_left = trm_left + 2 WHERE trm_left > @myLeft AND trm_category = '".$category."' ");
			$sql = "INSERT INTO {$g5['term_table']} (trm_idx, trm_idx_parent, trm_name, trm_name2, trm_type, trm_category, trm_desc, trm_sort, trm_left, trm_right, trm_status, trm_reg_dt) 
						VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','".$trm_desc[$i]."','$i',@myLeft + 1,@myLeft + 2, '".$trm_status[$i]."', now())
						ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
							, trm_name = '$trm_name[$i]'
							, trm_name2 = '$trm_name2[$i]'
							, trm_type = '$trm_type[$i]'
							, trm_desc = '".$trm_desc[$i]."'
							, trm_sort = '".$trm_sort[$i]."'
							, trm_status = '".$trm_status[$i]."'
							, trm_left = @myLeft + 1
							, trm_right = @myLeft + 2
			";
			sql_query($sql,1);
			echo $sql.'<br><br>';
		}
		//-- leaf_node가 아니면 동 레벨 idx 참조해서 left, right 생성
		else {
			sql_query("SELECT @myRight := trm_right FROM {$g5['term_table']} WHERE trm_idx = '".$idx_array[$trm_depth[$i]]."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_right = trm_right + 2 WHERE trm_right > @myRight AND trm_category = '".$category."' ");
			sql_query("UPDATE {$g5['term_table']} SET trm_left = trm_left + 2 WHERE trm_left > @myRight AND trm_category = '".$category."' ");
			$sql = "INSERT INTO {$g5['term_table']} (trm_idx, trm_idx_parent, trm_name, trm_name2, trm_type, trm_category, trm_desc, trm_sort, trm_left, trm_right, trm_status, trm_reg_dt) 
						VALUES ('$trm_idx[$i]','".$idx_array[$trm_depth[$i]-1]."','$trm_name[$i]','$trm_name2[$i]','$trm_type[$i]','".$category."','".$trm_desc[$i]."','$i',@myRight + 1,@myRight + 2, '".$trm_status[$i]."', now())
						ON DUPLICATE KEY UPDATE trm_idx_parent = '".$idx_array[$trm_depth[$i]-1]."'
							, trm_name = '$trm_name[$i]'
							, trm_name2 = '$trm_name2[$i]'
							, trm_type = '$trm_type[$i]'
							, trm_desc = '".$trm_desc[$i]."'
							, trm_sort = '".$trm_sort[$i]."'
							, trm_status = '".$trm_status[$i]."'
							, trm_left = @myRight + 1
							, trm_right = @myRight + 2
			";
			sql_query($sql,1);
			echo $sql.'<br><br>';
		}
	}
	
	//echo "<br><br>";
	$prev_depth = $trm_depth[$i]; 
	$idx_array[$trm_depth[$i]] = $trm_idx[$i];	//-- left, right 기준 값 저장
	$idx_array[$trm_depth[$i]] = sql_insert_id();	//-- left, right 기준 값 저장
}


// 캐시 파일 삭제 (초기화)
$files = glob(G5_DATA_PATH.'/cache/term-'.$category.'.php');
if (is_array($files)) {
    foreach ($files as $filename)
        unlink($filename);
}


//exit;
// 앞에서 넘어온 파일명으로 다시 돌려보낸다.
goto_url("./".$file_name.".php?category=".$category);
?>
```
### adm/_z01/role_list.php
```php
<?php
$sub_menu = "920400";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 용어 설정
$category = ($category) ? $category : 'role';


// include_once(G5_ZSQL_PATH.'/term_role.php');

//아래 AND 조건절에 비어있는 줄에는 원래 아래와 같은 내용은 있었다 (2군데)
//AND term.trm_status = 'ok' AND parent.trm_status = 'ok'
//-- 카테고리 구조 추출 --//
$sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor member: 루트 노드를 가져옴
    SELECT
        trm_idx,
        trm_name,
    	trm_name2,
        CAST(trm_name AS CHAR(255)) AS path,
        trm_desc,
        trm_left,
        trm_right,
		trm_status,
        0 AS depth,
        (SELECT COUNT(*) > 0 FROM {$g5['term_table']} AS child WHERE child.trm_idx_parent = {$g5['term_table']}.trm_idx AND child.trm_status != 'trash') AS has_children
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0  -- 루트 노드
        AND trm_category = 'role'
        AND trm_status != 'trash'

    UNION ALL

    -- Recursive member: 하위 노드들을 경로와 함께 가져옴
    SELECT
        t.trm_idx,
        t.trm_name,
    	t.trm_name2,
        CONCAT(tp.path, ' > ', t.trm_name) AS path,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
		t.trm_status,
        (SELECT COUNT(*)
        FROM {$g5['term_table']} AS parent
        WHERE parent.trm_left < t.trm_left
        AND parent.trm_right > t.trm_right) AS depth,
        (SELECT COUNT(*) > 0 FROM {$g5['term_table']} AS child WHERE child.trm_idx_parent = t.trm_idx AND child.trm_status != 'trash') AS has_children
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = 'role'
        AND t.trm_status != 'trash'
)
SELECT trm_idx, trm_name, trm_name2, path, trm_desc, trm_left, trm_right, trm_status, depth, has_children FROM TermPaths ORDER BY trm_left;
";

$result = sql_query($sql);
$total_count = sql_num_rows($result);

$g5['title'] = '직책관리';
require_once G5_ADMIN_PATH.'/admin.head.php';
?>


<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총분류수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>개 </span></span>
    <div style="display:inline-block;float:right;">
        <select name="category" style="display:none;" class="cp_field" title="분류선택" onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'>
            <option value="">분류 선택</option>
            <?=$g5['set_taxonomies_options']?>
        </select>
        <script>
            $('select[name=category]').val('<?=$category?>').attr('selected','selected');
        </script>
    </div>
</div>

<form name="fcarlist" method="post" action="./department_list_update.php" autocomplete="off">
<input type="hidden" name="category" value="<?php echo $category; ?>">
<input type="hidden" name="file_name" value="<?php echo $g5['file_name']; ?>">

<div id="sct" class="tbl_head02 tbl_wrap">
<table id="table01_list">
<caption><?php echo $g5['title']; ?> 목록</caption>
<thead>
<tr>
    <th scope="col" style="width:6%">트리구조</th>
    <th scope="col" style="width:15%">직책명</th>
    <th scope="col" style="width:10%">직책명2</th>
    <th scope="col" style="width:7%"><a href="javascript:" id="sub_toggle">닫기</a></th>
    <th scope="col" style="width:20%">설명</th>
    <th scope="col" style="width:10%">위치이동</th>
    <th scope="col" style="width:5%;white-space:nowrap;">고유코드</th>
	<th scope="col" style="width:6%">숨김</th>
    <th scope="col" style="width:6%">관리</th>
</tr>
</thead>
<tbody>
	<!-- 항목 추가를 위한 DOM (복제후 제거됨) -->
	<tr class="" style="display:none">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_trm_name" style="padding-left:10px">
			<input type="hidden" name="trm_depth[]" value="0">
			<input type="hidden" name="trm_idx[]" value="">
			<input type="text" name="trm_name[]" value="직책명을 입력하세요" required class="frm_input full_input required" style="width:180px;">
	    </td>
	    <td class="td_trm_name" style="padding-left:10px">
			<input type="text" name="trm_name2[]" value="" class="frm_input full_input" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><a href="#">열기</a></td>
	    <td class="td_trm_content"><!-- 삭제조직코드 -->
	        <input type="text" name="trm_desc[]" value="" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center">
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
		<td class="td_idx" style="text-align:center"></td>
		<td class="td_use" style="text-align:center">
			<input type="hidden" name="trm_status[]" value="ok">
			<input type="checkbox" name="trm_use[]">
	    </td>
	    <td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
	<!-- //항목 추가를 위한 DOM (복제후 제거됨) -->
<?php
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	// print_r2($row);
	//-- 들여쓰기
	$row['indent'] = ($row['depth']) ? $row['depth']*50:10;
	
	//-- 하위 열기 닫기
	//$row['sub_toggle'] = ($row['depth']==0) ? '<a href="#">닫기</a>':'-';
	$row['sub_toggle'] = ($row['has_children']==1) ? '<a href="#">닫기</a>':'-';
	
    // 추가 부분 unserialize
    $unser = unserialize(stripslashes($row['trm_more']));
    if( is_array($unser) ) {
        foreach ($unser as $key=>$value) {
            //print_r3($key.'/'.$value);
            $row[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
        }    
    }
	
	$usechecked = ($row['trm_status'] == 'ok') ? '':'checked';
	$status_txt = ($row['trm_status'] == 'ok') ? 'ok':'hide';
    $bg = 'bg'.($i%2);
?>
	<tr class="<?php echo $bg; ?>">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_trm_name" style="padding-left:<?=$row['indent']?>px;text-align:left;">
			<input type="hidden" name="trm_depth[]" value="<?=$row['depth']?>">
			<input type="hidden" name="trm_idx[]" value="<?=$row['trm_idx']?>">
			<input type="text" name="trm_name[]" value="<?php echo get_text(trim($row['trm_name'])); ?>" required class="frm_input full_input required" style="width:180px;">
	    </td>
	    <td class="td_trm_name2">
			<input type="text" name="trm_name2[]" value="<?php echo get_text(trim($row['trm_name2'])); ?>" class="frm_input full_input" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><?=$row['sub_toggle']?></td>
	    <td class="td_trm_content"><!-- 삭제조직코드 -->
	        <input type="text" name="trm_desc[]" value="<?php echo get_text(trim($row['trm_desc'])); ?>" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center"><!-- 위치이동 -->
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
	    <td class="td_idx" style="text-align:center"><!-- 코유코드 -->
			<?=$row['trm_idx']?>
	    </td>
	    <td class="td_use" style="text-align:center"><!-- 숨김 -->
			<input type="hidden" name="trm_status[]" value="<?=$status_txt?>">
	        <input type="checkbox" name="trm_use[]" <?=$usechecked?>>
	    </td>
		<td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
<?php }
if ($i == 0) echo "<tr class=\"no-data\"><td colspan=\"9\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
?>
</tbody>
</table>
</div>

<div class="btn_fixed_top">
    <a href="javascript:insert_item()" id="btn_add_car" class="btn btn_02">항목추가</a>
	<input type="submit" name="act_button" value="확인" class="btn_submit btn">
</div>
</form>

<script>
//----------------------------------------------
$(function() {
    
    // 엑셀업로드 창 열기
    $(document).on('click','#btn_excel_upload',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        win_excel_upload = window.open(href, "win_excel_upload", "left=100,top=100,width=520,height=600,scrollbars=1");
        win_excel_upload.focus();
    });
    
	
	//-- DOM 복제 & 생성 & 초기화 --//
	list_dom01=$("#table01_list tbody");
	orig_dom01=list_dom01.find("tr").eq(0).clone();
	list_dom01.find("tr:eq(0)").remove();	// 복제한 후에 제거
	list01_nothing_display();

	//-- 정렬(Sortable) --//
	var fixHelperModified = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index)
		{
			$(this).width($originals.eq(index).width());
		});
		return $helper;
	};

	$("#table01_list tbody").sortable({
		cancel: "input, textarea, a, i"
		, helper: fixHelperModified
		, items: "tr:not(.no-data)"
		, placeholder: "tr-placeholder"
		, connectWith: "#table01_list tr:not(.no-data)"
		, stop: function(event, ui) {
			//alert(ui.item.html());
			//-- 정렬 후 처리 / 맨 처음 항목이면 최상위 레벨이어야 함
			if($(this).find('tr').index(ui.item) == 0 && ui.item.find('input[name^=trm_depth]').val() > 0) {
				ui.item.find('input[name^=trm_depth]').val(0);
				ui.item.find('.td_trm_name').css('padding-left','0px');
			}
			
			setTimeout(function(){ ui.item.removeAttr('style'); }, 10);
		}
	});
	
	//=====================================================카테고리 사용여부=========================== 
	$('input[type="checkbox"]').click(function(){
		if($(this).is(":checked")){
			$(this).siblings('input[type="hidden"]').val('hide');
			//alert($(this).siblings('input[type="hidden"]').val());
		}else{
			$(this).siblings('input[type="hidden"]').val('ok');
			//alert($(this).siblings('input[type="hidden"]').val());
		}
	});

	//-- 차종추가 경고창 초기 설정
	alert_flag = true; 


	//-- 단계이동 버튼 클릭 --//
//	$('.td_depth a').live('click',function(e) {
	$(document).on('click','.td_depth a',function(e) {
		e.preventDefault();
		//-- 맨 처음 항목은 무조건 최상위 단계이어야 함
		if($(this).parents('tbody:first').find('tr').index($(this).parents('tr:first')) == 0 && $(this).parent().find('a').index($(this)) == 1) {
			alert('맨 처음 항목은 최상위 레벨이어야 합니다. \n\n단계 2로 이동할 수 없습니다.');
			return false;
		}
		
		//-- depth 값 업데이트
		var indent_sign_value = ($(this).parent().find('a').index($(this)) == 0)? -1:1;
		var new_depth = parseInt($(this).parents('tr:first').find('input[name^=trm_depth]').val()) + indent_sign_value;
		if(new_depth < 0) new_depth = 0;
		$(this).parents('tr:first').find('input[name^=trm_depth]').val(new_depth);
		
		//-- 들여쓰기 적용
		var indent_value = (new_depth) ? new_depth * 50:10;
		$(this).parents('tr:first').find('.td_trm_name').css('padding-left',indent_value+'px');
		
		//update_notice();	//-- [일괄수정] 버튼 활성화
	});


	//-- 위치이동 버튼 클릭 --//
//	$('.td_sort a').live('click',function(e) {
	$(document).on('click','.td_sort a',function(e) {
		e.preventDefault();

		var target_tr = $(this).parents('tr:first').clone().hide();
		var flag_up_down = ($(this).parent().find('a').index($(this)) == 0)? 'up':'down';
		var tr_loc = $(this).parents('tbody:first').find('tr').index($(this).parents('tr:first'));
		

		if(flag_up_down == "up" && tr_loc == 0) {
			alert('맨 처음 항목입니다. 더 이상 올라갈 때가 없지 않나요?');
			return false;
		}
		else if(flag_up_down == "down" && tr_loc == $(this).parents('tbody:first').find('tr').length - 1) {
			alert('마지막 항목입니다. 보면 알잖아요~');
			return false;
		}

		$(this).parents('tr:first').stop(true,true).fadeOut('fast',function(){
			$(this).remove();

			if(flag_up_down == "up") {
				target_tr.insertBefore($('#table01_list tbody tr').eq(parseInt(tr_loc)-1)).stop(true,true).fadeIn('fast').removeAttr('style');
			}
			else {
				target_tr.insertAfter($('#table01_list tbody tr').eq(tr_loc)).stop(true,true).fadeIn('fast').removeAttr('style');
			}

		});

		//update_notice();	//-- Submit 버튼 활성화
	});


	//-- 삭제 버튼 클릭시 --//
//	$('.td_del a').live('click',function(e) {
	$(document).on('click','.td_del a',function(e) {
		e.preventDefault();
		
		//-- 추가된 항목은 바로 삭제, 기 등록된 조직은 관련 작업 진행
		if(confirm('하위 카테고리 전체 및 소속 항목들이 전부 삭제됩니다. \n\n후회할 수도 있을 텐데~~ 정말 삭제하시겠습니까?')) {
			if($(this).parents('tr:first').find('input[name^=trm_idx]').val()) {

				//-- 삭제 함수 호출(마지막인수는 trash변경할지 아예 삭제할지의 영부인데 0이면 trash, 1이면 완전삭제)
				trm_delete($(this).parents('tr:first').find('input[name^=trm_idx]').val(),1);

			}
			else {
				$(this).parents('tr:first').remove();
			}
			
			//update_notice();	//-- Submit 버튼 활성화
		}
	});


	//-- 닫기 열기
	$('#sub_toggle').click(function() {
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		$('#table01_list tbody tr').find('input[name^=trm_depth]').each(function() {
			if($(this).val() > 0) {
				if(this_text == "닫기")
					$(this).closest('tr').hide();
				else 
					$(this).closest('tr').show();
			}
			else {
				if(this_text == "닫기") {
					$(this).closest('tr').find('.td_sub_category a').text('열기');
				}
				else 
					$(this).closest('tr').find('.td_sub_category a').text('닫기');
			}
		});
	});


	//-- 서브 부분만 열고 닫기
//	$('.td_sub_category a').live('click',function(e) {
	$(document).on('click','.td_sub_category a',function(e) {
		e.preventDefault();
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		
		var this_depth = $(this).closest('tr').find('input[name^=trm_depth]').val();
		var this_sub_flag = false;
		
		$(this).closest('tr').nextAll('tr').each(function() {
			if($(this).find('input[name^=trm_depth]').val() > this_depth && this_sub_flag == false) {

				if(this_text == "닫기")
					$(this).hide();
				else
					$(this).show();
			}
			else 
				this_sub_flag = true;
		});
	});



});
//----------------------------------------------


//-- 01 No data 처리 --//
function list01_nothing_display() {
	if(list_dom01.find("tr:not(.no-data)").length == 0)
		list_dom01.find('.no-data').show();
	else 
		list_dom01.find('.no-data').hide();
}
//-- //01 No data 처리 --//


//-- 테이블 항목 추가
function insert_item() {
	//-- DOM 복제
	sDom = orig_dom01.clone();

	//-- DOM 입력
	//sDom.insertBefore($('#table01_list tbody tr').eq(0)).show();
	//$('#table01_list tbody tr').eq(0).find('input[name^=trm_name]').select().focus();
	$('#table01_list tbody').append(sDom.show());
	$('#table01_list tbody tr:last').find('input[name^=trm_name]').select().focus();

	list01_nothing_display();
	
	if(alert_flag == true) {
		alert('입력항목을 작성한 후 하단의 [일괄수정] 버튼을 클릭하여 적용해 주시면 됩니다.');
		alert_flag = false;
	}
}


//-- 항목 삭제 함수 --//
function trm_delete(this_trm_idx, fn_delte) {
	//-- 디버깅 Ajax --//
	$.ajax({
		url:'./ajax/term_delete.php',
		type:'get',
		data:{"category":"<?=$category?>", "trm_idx":this_trm_idx,"delete":fn_delte},
		dataType:'json',
		timeout:3000, 
		beforeSend:function(){},
		success:function(data){
			self.location.reload();
		},
		error:function(xmlRequest) {
			alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
			+ ' \n\rresponseText: ' + xmlRequest.responseText);
		} 
	//-- 디버깅 Ajax --//

	});	
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/__doc/tomas.php
### adm/_z01/__set/db_dain_delete.php
```php
<?php
if(!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$db_tbl_sql = " CREATE TABLE dain_default (
  de_id INTEGER NOT NULL,
  de_admin_company_owner VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_company_name VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_company_saupja_no VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_company_tel VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_company_fax VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_tongsin_no VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_company_zip VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_company_addr VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_info_name VARCHAR(255) NOT NULL DEFAULT '',
  de_admin_info_email VARCHAR(255) NOT NULL DEFAULT '',
  de_shop_skin VARCHAR(255) NOT NULL DEFAULT '',
  de_type1_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_type1_list_mod INTEGER NOT NULL DEFAULT '0',
  de_type1_list_row INTEGER NOT NULL DEFAULT '0',
  de_type1_img_width INTEGER NOT NULL DEFAULT '0',
  de_type1_img_height INTEGER NOT NULL DEFAULT '0',
  de_type2_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_type2_list_mod INTEGER NOT NULL DEFAULT '0',
  de_type2_list_row INTEGER NOT NULL DEFAULT '0',
  de_type2_img_width INTEGER NOT NULL DEFAULT '0',
  de_type2_img_height INTEGER NOT NULL DEFAULT '0',
  de_type3_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_type3_list_mod INTEGER NOT NULL DEFAULT '0',
  de_type3_list_row INTEGER NOT NULL DEFAULT '0',
  de_type3_img_width INTEGER NOT NULL DEFAULT '0',
  de_type3_img_height INTEGER NOT NULL DEFAULT '0',
  de_type4_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_type4_list_mod INTEGER NOT NULL DEFAULT '0',
  de_type4_list_row INTEGER NOT NULL DEFAULT '0',
  de_type4_img_width INTEGER NOT NULL DEFAULT '0',
  de_type4_img_height INTEGER NOT NULL DEFAULT '0',
  de_type5_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_type5_list_mod INTEGER NOT NULL DEFAULT '0',
  de_type5_list_row INTEGER NOT NULL DEFAULT '0',
  de_type5_img_width INTEGER NOT NULL DEFAULT '0',
  de_type5_img_height INTEGER NOT NULL DEFAULT '0',
  de_mobile_type1_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_mobile_type1_list_mod INTEGER NOT NULL DEFAULT '0',
  de_mobile_type1_list_row INTEGER NOT NULL DEFAULT '0',
  de_mobile_type1_img_width INTEGER NOT NULL DEFAULT '0',
  de_mobile_type1_img_height INTEGER NOT NULL DEFAULT '0',
  de_mobile_type2_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_mobile_type2_list_mod INTEGER NOT NULL DEFAULT '0',
  de_mobile_type2_list_row INTEGER NOT NULL DEFAULT '0',
  de_mobile_type2_img_width INTEGER NOT NULL DEFAULT '0',
  de_mobile_type2_img_height INTEGER NOT NULL DEFAULT '0',
  de_mobile_type3_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_mobile_type3_list_mod INTEGER NOT NULL DEFAULT '0',
  de_mobile_type3_list_row INTEGER NOT NULL DEFAULT '0',
  de_mobile_type3_img_width INTEGER NOT NULL DEFAULT '0',
  de_mobile_type3_img_height INTEGER NOT NULL DEFAULT '0',
  de_mobile_type4_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_mobile_type4_list_mod INTEGER NOT NULL DEFAULT '0',
  de_mobile_type4_list_row INTEGER NOT NULL DEFAULT '0',
  de_mobile_type4_img_width INTEGER NOT NULL DEFAULT '0',
  de_mobile_type4_img_height INTEGER NOT NULL DEFAULT '0',
  de_mobile_type5_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_mobile_type5_list_mod INTEGER NOT NULL DEFAULT '0',
  de_mobile_type5_list_row INTEGER NOT NULL DEFAULT '0',
  de_mobile_type5_img_width INTEGER NOT NULL DEFAULT '0',
  de_mobile_type5_img_height INTEGER NOT NULL DEFAULT '0',
  de_rel_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_rel_list_mod INTEGER NOT NULL DEFAULT '0',
  de_rel_img_width INTEGER NOT NULL DEFAULT '0',
  de_rel_img_height INTEGER NOT NULL DEFAULT '0',
  de_mobile_rel_list_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_mobile_rel_list_mod INTEGER NOT NULL DEFAULT '0',
  de_mobile_rel_img_width INTEGER NOT NULL DEFAULT '0',
  de_mobile_rel_img_height INTEGER NOT NULL DEFAULT '0',
  de_search_list_mod INTEGER NOT NULL DEFAULT '0',
  de_search_list_row INTEGER NOT NULL DEFAULT '0',
  de_search_img_width INTEGER NOT NULL DEFAULT '0',
  de_search_img_height INTEGER NOT NULL DEFAULT '0',
  de_mobile_search_list_mod INTEGER NOT NULL DEFAULT '0',
  de_mobile_search_list_row INTEGER NOT NULL DEFAULT '0',
  de_mobile_search_img_width INTEGER NOT NULL DEFAULT '0',
  de_mobile_search_img_height INTEGER NOT NULL DEFAULT '0',
  de_listtype_list_mod INTEGER NOT NULL DEFAULT '0',
  de_listtype_list_row INTEGER NOT NULL DEFAULT '0',
  de_listtype_img_width INTEGER NOT NULL DEFAULT '0',
  de_listtype_img_height INTEGER NOT NULL DEFAULT '0',
  de_mobile_listtype_list_mod INTEGER NOT NULL DEFAULT '0',
  de_mobile_listtype_list_row INTEGER NOT NULL DEFAULT '0',
  de_mobile_listtype_img_width INTEGER NOT NULL DEFAULT '0',
  de_mobile_listtype_img_height INTEGER NOT NULL DEFAULT '0',
  de_bank_use INTEGER NOT NULL DEFAULT '0',
  de_bank_account TEXT NOT NULL,
  de_card_test INTEGER NOT NULL DEFAULT '0',
  de_card_use INTEGER NOT NULL DEFAULT '0',
  de_card_noint_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_card_point INTEGER NOT NULL DEFAULT '0',
  de_settle_min_point INTEGER NOT NULL DEFAULT '0',
  de_settle_max_point INTEGER NOT NULL DEFAULT '0',
  de_settle_point_unit INTEGER NOT NULL DEFAULT '0',
  de_level_sell INTEGER NOT NULL DEFAULT '0',
  de_delivery_company VARCHAR(255) NOT NULL DEFAULT '',
  de_send_cost_case VARCHAR(255) NOT NULL DEFAULT '',
  de_send_cost_limit VARCHAR(255) NOT NULL DEFAULT '',
  de_send_cost_list VARCHAR(255) NOT NULL DEFAULT '',
  de_hope_date_use INTEGER NOT NULL DEFAULT '0',
  de_hope_date_after INTEGER NOT NULL DEFAULT '0',
  de_baesong_content TEXT NOT NULL,
  de_change_content TEXT NOT NULL,
  de_point_days INTEGER NOT NULL DEFAULT '0',
  de_simg_width INTEGER NOT NULL DEFAULT '0',
  de_simg_height INTEGER NOT NULL DEFAULT '0',
  de_mimg_width INTEGER NOT NULL DEFAULT '0',
  de_mimg_height INTEGER NOT NULL DEFAULT '0',
  de_sms_cont1 TEXT NOT NULL,
  de_sms_cont2 TEXT NOT NULL,
  de_sms_cont3 TEXT NOT NULL,
  de_sms_cont4 TEXT NOT NULL,
  de_sms_cont5 TEXT NOT NULL,
  de_sms_use1 BOOLEAN NOT NULL DEFAULT FALSE,
  de_sms_use2 BOOLEAN NOT NULL DEFAULT FALSE,
  de_sms_use3 BOOLEAN NOT NULL DEFAULT FALSE,
  de_sms_use4 BOOLEAN NOT NULL DEFAULT FALSE,
  de_sms_use5 BOOLEAN NOT NULL DEFAULT FALSE,
  de_sms_hp VARCHAR(255) NOT NULL DEFAULT '',
  de_pg_service VARCHAR(255) NOT NULL DEFAULT '',
  de_kcp_mid VARCHAR(255) NOT NULL DEFAULT '',
  de_kcp_site_key VARCHAR(255) NOT NULL DEFAULT '',
  de_inicis_mid VARCHAR(255) NOT NULL DEFAULT '',
  de_inicis_iniapi_key VARCHAR(30) NOT NULL DEFAULT '',
  de_inicis_iniapi_iv VARCHAR(30) NOT NULL DEFAULT '',
  de_inicis_sign_key VARCHAR(255) NOT NULL DEFAULT '',
  de_iche_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_easy_pay_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_easy_pay_services VARCHAR(255) NOT NULL DEFAULT '',
  de_samsung_pay_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_inicis_lpay_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_inicis_kakaopay_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_inicis_cartpoint_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_nicepay_mid VARCHAR(30) NOT NULL DEFAULT '',
  de_nicepay_key VARCHAR(255) NOT NULL DEFAULT '',
  de_item_use_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_item_use_write BOOLEAN NOT NULL DEFAULT FALSE,
  de_cart_keep_term INTEGER NOT NULL DEFAULT '0',
  de_guest_cart_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_admin_buga_no VARCHAR(255) NOT NULL DEFAULT '',
  de_vbank_use VARCHAR(255) NOT NULL DEFAULT '',
  de_taxsave_use BOOLEAN NOT NULL,
  de_taxsave_types TEXT CHECK (\"de_taxsave_types\" IN ('account','vbank','transfer')) NOT NULL DEFAULT 'account',
  de_guest_privacy TEXT NOT NULL,
  de_hp_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_escrow_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_tax_flag_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_kakaopay_mid VARCHAR(255) NOT NULL DEFAULT '',
  de_kakaopay_key VARCHAR(255) NOT NULL DEFAULT '',
  de_kakaopay_enckey VARCHAR(255) NOT NULL DEFAULT '',
  de_kakaopay_hashkey VARCHAR(255) NOT NULL DEFAULT '',
  de_kakaopay_cancelpwd VARCHAR(255) NOT NULL DEFAULT '',
  de_naverpay_mid VARCHAR(255) NOT NULL DEFAULT '',
  de_naverpay_cert_key VARCHAR(255) NOT NULL DEFAULT '',
  de_naverpay_button_key VARCHAR(255) NOT NULL DEFAULT '',
  de_naverpay_test BOOLEAN NOT NULL DEFAULT FALSE,
  de_naverpay_mb_id VARCHAR(255) NOT NULL DEFAULT '',
  de_naverpay_sendcost VARCHAR(255) NOT NULL DEFAULT '',
  de_member_reg_coupon_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_member_reg_coupon_term INTEGER NOT NULL DEFAULT '0',
  de_member_reg_coupon_price INTEGER NOT NULL DEFAULT '0',
  de_member_reg_coupon_minimum INTEGER NOT NULL DEFAULT '0',
  de_toss_use BOOLEAN NOT NULL DEFAULT FALSE,
  de_toss_mid VARCHAR(255) NOT NULL DEFAULT '',
  de_toss_client_key VARCHAR(255) NOT NULL DEFAULT '',
  de_toss_secret_key VARCHAR(255) NOT NULL DEFAULT '',
  de_toss_cancel_pwd VARCHAR(255) NOT NULL DEFAULT ''
); ";

sql_query_pg($db_tbl_sql);
unset($db_tbl_sql);
```
### adm/_z01/__set/db_dain_file.php
```php
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
```
### adm/_z01/__set/db_meta.php
```php
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
```
### adm/_z01/__set/db_setting.php
```php
<?php
if(!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$db_tbl_sql = " CREATE TABLE setting (
  set_idx      SERIAL PRIMARY KEY,
  set_com_idx  INTEGER NOT NULL DEFAULT 0,
  set_trm_idx  INTEGER NOT NULL DEFAULT 0, -- 관리부서idx
  set_key      VARCHAR(50) NOT NULL DEFAULT 'tms',
  set_type     VARCHAR(50) NOT NULL DEFAULT '',
  set_name     VARCHAR(50),
  set_value    TEXT,
  set_auto_yn  BOOLEAN DEFAULT TRUE
); ";

sql_query_pg($db_tbl_sql);
unset($db_tbl_sql);
```
### adm/_z01/__set/db_term.php
```php
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
```
### adm/_z01/_adm/_common.php
```php
<?php
define('G5_IS_ADMIN', true);
include_once ('../../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

run_event('admin_common');
```
### adm/_z01/_adm/captcha_file_delete.php
```php
<?php
$sub_menu = '100910';
require_once './_common.php';

if (!$is_manager) {
    alert('접근권한이 없습니다.', G5_URL);
}

$g5['title'] = '캡챠파일 일괄삭제';
require_once G5_ADMIN_PATH.'/admin.head.php';
?>

<div class="local_desc02 local_desc">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
</div>

<?php
flush();

if (!$dir = @opendir(G5_DATA_PATH . '/cache')) {
    echo '<p>캐시디렉토리를 열지못했습니다.</p>';
}

$cnt = 0;
echo '<ul class="session_del">' . PHP_EOL;

$files = glob(G5_DATA_PATH . '/cache/?captcha-*');
if (is_array($files)) {
    $before_time  = G5_SERVER_TIME - 3600; // 한시간전
    foreach ($files as $gcaptcha_file) {
        $modification_time = filemtime($gcaptcha_file); // 파일접근시간

        if ($modification_time > $before_time) {
            continue;
        }

        $cnt++;
        unlink($gcaptcha_file);
        echo '<li>' . $gcaptcha_file . '</li>' . PHP_EOL;

        flush();

        if ($cnt % 10 == 0) {
            echo PHP_EOL;
        }
    }
}

echo '<li>완료됨</li></ul>' . PHP_EOL;
echo '<div class="local_desc01 local_desc"><p><strong>캡챠파일 ' . $cnt . '건의 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>' . PHP_EOL;
?>

<?php
require_once G5_ADMIN_PATH.'/admin.tail.php';
```
### adm/_z01/_adm/index.php
```php
<?php
$sub_menu = '100000';
require_once './_common.php';

@require_once G5_ADMIN_PATH.'/safe_check.php';

if (function_exists('social_log_file_delete')) {
    //소셜로그인 디버그 파일 24시간 지난것은 삭제
    social_log_file_delete(86400);
}

$g5['title'] = '관리자메인';
require_once G5_ADMIN_PATH.'/admin.head.php';

$new_member_rows = 5;
$new_point_rows = 5;
$new_write_rows = 5;

$addtional_content_before = run_replace('adm_index_addtional_content_before', '', $is_admin, $auth, $member);
if ($addtional_content_before) {
    echo $addtional_content_before;
}

if (!auth_check_menu($auth, '200100', 'r', true)) {
    $sql_common = " from {$g5['member_table']} ";

    $sql_search = " where (1) ";

    if ($is_admin != 'super') {
        $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
    }

    if (!$sst) {
        $sst = "mb_datetime";
        $sod = "desc";
    }

    $sql_order = " order by {$sst} {$sod} ";

    $sql = " SELECT count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    // 탈퇴회원수
    $sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
    $row = sql_fetch($sql);
    $leave_count = $row['cnt'];

    // 차단회원수
    $sql = " SELECT count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
    $row = sql_fetch($sql);
    $intercept_count = $row['cnt'];

    $sql = " SELECT * {$sql_common} {$sql_search} {$sql_order} limit {$new_member_rows} ";
    $result = sql_query($sql);

    $colspan = 12;
    ?>

    <section>
        <h2>신규가입회원 <?php echo $new_member_rows ?>건 목록</h2>
        <div class="local_desc02 local_desc">
            총회원수 <?php echo number_format($total_count) ?>명 중 차단 <?php echo number_format($intercept_count) ?>명, 탈퇴 : <?php echo number_format($leave_count) ?>명
        </div>

        <div class="tbl_head01 tbl_wrap">
            <table>
                <caption>신규가입회원</caption>
                <thead>
                    <tr>
                        <th scope="col">회원아이디</th>
                        <th scope="col">이름</th>
                        <th scope="col">닉네임</th>
                        <th scope="col">권한</th>
                        <th scope="col">포인트</th>
                        <th scope="col">수신</th>
                        <th scope="col">공개</th>
                        <th scope="col">인증</th>
                        <th scope="col">차단</th>
                        <th scope="col">그룹</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i = 0; $row = sql_fetch_array($result); $i++) {
                        // 접근가능한 그룹수
                        $sql2 = " SELECT count(*) as cnt from {$g5['group_member_table']} where mb_id = '{$row['mb_id']}' ";
                        $row2 = sql_fetch($sql2);
                        $group = "";
                        if ($row2['cnt']) {
                            $group = '<a href="./boardgroupmember_form.php?mb_id=' . $row['mb_id'] . '">' . $row2['cnt'] . '</a>';
                        }

                        if ($is_admin == 'group') {
                            $s_mod = '';
                            $s_del = '';
                        } else {
                            $s_mod = '<a href="./member_form.php?$qstr&amp;w=u&amp;mb_id=' . $row['mb_id'] . '">수정</a>';
                            $s_del = '<a href="./member_delete.php?' . $qstr . '&amp;w=d&amp;mb_id=' . $row['mb_id'] . '&amp;url=' . $_SERVER['SCRIPT_NAME'] . '" onclick="return delete_confirm(this);">삭제</a>';
                        }
                        $s_grp = '<a href="./boardgroupmember_form.php?mb_id=' . $row['mb_id'] . '">그룹</a>';

                        $leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date("Ymd", G5_SERVER_TIME);
                        $intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date("Ymd", G5_SERVER_TIME);

                        $mb_nick = get_sideview($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

                        $mb_id = $row['mb_id'];
                        ?>
                        <tr>
                            <td class="td_mbid"><?php echo $mb_id ?></td>
                            <td class="td_mbname"><?php echo get_text($row['mb_name']); ?></td>
                            <td class="td_mbname sv_use">
                                <div><?php echo $mb_nick ?></div>
                            </td>
                            <td class="td_num"><?php echo $row['mb_level'] ?></td>
                            <td><a href="./point_list.php?sfl=mb_id&amp;stx=<?php echo $row['mb_id'] ?>"><?php echo number_format($row['mb_point']) ?></a></td>
                            <td class="td_boolean"><?php echo $row['mb_mailling'] ? '예' : '아니오'; ?></td>
                            <td class="td_boolean"><?php echo $row['mb_open'] ? '예' : '아니오'; ?></td>
                            <td class="td_boolean"><?php echo preg_match('/[1-9]/', $row['mb_email_certify']) ? '예' : '아니오'; ?></td>
                            <td class="td_boolean"><?php echo $row['mb_intercept_date'] ? '예' : '아니오'; ?></td>
                            <td class="td_category"><?php echo $group ?></td>
                        </tr>
                        <?php
                    }
                    if ($i == 0) {
                        echo '<tr><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="btn_list03 btn_list">
            <a href="./member_list.php">회원 전체보기</a>
        </div>
    </section>

    <?php
} //endif 최신 회원

if (!auth_check_menu($auth, '300100', 'r', true)) {

    $sql_common = " from {$g5['board_new_table']} a, {$g5['board_table']} b, {$g5['group_table']} c where a.bo_table = b.bo_table and b.gr_id = c.gr_id ";

    if ($gr_id) {
        $sql_common .= " and b.gr_id = '{$gr_id}' ";
    }
    if (isset($view) && $view) {
        if ($view == 'w') {
            $sql_common .= " and a.wr_id = a.wr_parent ";
        } elseif ($view == 'c') {
            $sql_common .= " and a.wr_id <> a.wr_parent ";
        }
    }
    $sql_order = " order by a.bn_id desc ";

    $sql = " SELECT count(*) as cnt {$sql_common} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $colspan = 5;
    ?>

    <section>
        <h2>최근게시물</h2>

        <div class="tbl_head01 tbl_wrap">
            <table>
                <caption>최근게시물</caption>
                <thead>
                    <tr>
                        <th scope="col">그룹</th>
                        <th scope="col">게시판</th>
                        <th scope="col">제목</th>
                        <th scope="col">이름</th>
                        <th scope="col">일시</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = " SELECT a.*, b.bo_subject, c.gr_subject, c.gr_id {$sql_common} {$sql_order} limit {$new_write_rows} ";
                    $result = sql_query($sql);
                    for ($i = 0; $row = sql_fetch_array($result); $i++) {
                        $tmp_write_table = $g5['write_prefix'] . $row['bo_table'];

                        if ($row['wr_id'] == $row['wr_parent']) {
                            // 원글
                            $comment = "";
                            $comment_link = "";
                            $row2 = sql_fetch(" SELECT * from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");

                            $name = get_sideview($row2['mb_id'], get_text(cut_str($row2['wr_name'], $config['cf_cut_name'])), $row2['wr_email'], $row2['wr_homepage']);
                            // 당일인 경우 시간으로 표시함
                            $datetime = substr($row2['wr_datetime'], 0, 10);
                            $datetime2 = $row2['wr_datetime'];
                            if ($datetime == G5_TIME_YMD) {
                                $datetime2 = substr($datetime2, 11, 5);
                            } else {
                                $datetime2 = substr($datetime2, 5, 5);
                            }
                        } else {
                            // 코멘트
                            $comment = '댓글. ';
                            $comment_link = '#c_' . $row['wr_id'];
                            $row2 = sql_fetch(" SELECT * from {$tmp_write_table} where wr_id = '{$row['wr_parent']}' ");
                            $row3 = sql_fetch(" SELECT mb_id, wr_name, wr_email, wr_homepage, wr_datetime from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");

                            $name = get_sideview($row3['mb_id'], get_text(cut_str($row3['wr_name'], $config['cf_cut_name'])), $row3['wr_email'], $row3['wr_homepage']);
                            // 당일인 경우 시간으로 표시함
                            $datetime = substr($row3['wr_datetime'], 0, 10);
                            $datetime2 = $row3['wr_datetime'];
                            if ($datetime == G5_TIME_YMD) {
                                $datetime2 = substr($datetime2, 11, 5);
                            } else {
                                $datetime2 = substr($datetime2, 5, 5);
                            }
                        }
                        ?>

                        <tr>
                            <td class="td_category"><a href="<?php echo G5_BBS_URL ?>/new.php?gr_id=<?php echo $row['gr_id'] ?>"><?php echo cut_str($row['gr_subject'], 10) ?></a></td>
                            <td class="td_category"><a href="<?php echo get_pretty_url($row['bo_table']) ?>"><?php echo cut_str($row['bo_subject'], 20) ?></a></td>
                            <td><a href="<?php echo get_pretty_url($row['bo_table'], $row2['wr_id']); ?><?php echo $comment_link ?>"><?php echo $comment ?><?php echo conv_subject($row2['wr_subject'], 100) ?></a></td>
                            <td class="td_mbname">
                                <div><?php echo $name ?></div>
                            </td>
                            <td class="td_datetime"><?php echo $datetime ?></td>
                        </tr>

                        <?php
                    }
                    if ($i == 0) {
                        echo '<tr><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="btn_list03 btn_list">
            <a href="<?php echo G5_BBS_URL ?>/new.php">최근게시물 더보기</a>
        </div>
    </section>

    <?php
} //endif 최근게시물

if (!auth_check_menu($auth, '200200', 'r', true)) {

    $sql_common = " from {$g5['point_table']} ";
    $sql_search = " where (1) ";
    $sql_order = " order by po_id desc ";

    $sql = " SELECT count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $sql = " SELECT * {$sql_common} {$sql_search} {$sql_order} limit {$new_point_rows} ";
    $result = sql_query($sql);

    $colspan = 7;
    ?>

    <section>
        <h2>최근 포인트 발생내역</h2>
        <div class="local_desc02 local_desc">
            전체 <?php echo number_format($total_count) ?> 건 중 <?php echo $new_point_rows ?>건 목록
        </div>

        <div class="tbl_head01 tbl_wrap">
            <table>
                <caption>최근 포인트 발생내역</caption>
                <thead>
                    <tr>
                        <th scope="col">회원아이디</th>
                        <th scope="col">이름</th>
                        <th scope="col">닉네임</th>
                        <th scope="col">일시</th>
                        <th scope="col">포인트 내용</th>
                        <th scope="col">포인트</th>
                        <th scope="col">포인트합</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $row2['mb_id'] = '';
                    for ($i = 0; $row = sql_fetch_array($result); $i++) {
                        if ($row2['mb_id'] != $row['mb_id']) {
                            $sql2 = " SELECT mb_id, mb_name, mb_nick, mb_email, mb_homepage, mb_point from {$g5['member_table']} where mb_id = '{$row['mb_id']}' ";
                            $row2 = sql_fetch($sql2);
                        }

                        $mb_nick = get_sideview($row['mb_id'], $row2['mb_nick'], $row2['mb_email'], $row2['mb_homepage']);

                        $link1 = $link2 = "";
                        if (!preg_match("/^\@/", $row['po_rel_table']) && $row['po_rel_table']) {
                            $link1 = '<a href="' . get_pretty_url($row['po_rel_table'], $row['po_rel_id']) . '" target="_blank">';
                            $link2 = '</a>';
                        }
                        ?>

                        <tr>
                            <td class="td_mbid"><a href="./point_list.php?sfl=mb_id&amp;stx=<?php echo $row['mb_id'] ?>"><?php echo $row['mb_id'] ?></a></td>
                            <td class="td_mbname"><?php echo get_text($row2['mb_name']); ?></td>
                            <td class="td_name sv_use">
                                <div><?php echo $mb_nick ?></div>
                            </td>
                            <td class="td_datetime"><?php echo $row['po_datetime'] ?></td>
                            <td><?php echo $link1 . $row['po_content'] . $link2 ?></td>
                            <td class="td_numbig"><?php echo number_format($row['po_point']) ?></td>
                            <td class="td_numbig"><?php echo number_format($row['po_mb_point']) ?></td>
                        </tr>

                        <?php
                    }

                    if ($i == 0) {
                        echo '<tr><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="btn_list03 btn_list">
            <a href="./point_list.php">포인트내역 전체보기</a>
        </div>
    </section>

    <?php
} //endif

$addtional_content_after = run_replace('adm_index_addtional_content_after', '', $is_admin, $auth, $member);
if ($addtional_content_after) {
    echo $addtional_content_after;
}
require_once G5_ADMIN_PATH.'/admin.tail.php';
```
### adm/_z01/_adm/member_delete.php
```php
<?php
$sub_menu = "200100";
require_once "./_common.php";

check_demo();

auth_check_menu($auth, $sub_menu, "d");

$mb = isset($_POST['mb_id']) ? get_member($_POST['mb_id']) : array();

if (!(isset($mb['mb_id']) && $mb['mb_id'])) {
    alert("회원자료가 존재하지 않습니다.");
} elseif ($member['mb_id'] == $mb['mb_id']) {
    alert("로그인 중인 관리자는 삭제 할 수 없습니다.");
} elseif (is_admin($mb['mb_id']) == "super") {
    alert("최고 관리자는 삭제할 수 없습니다.");
} elseif ($mb['mb_level'] >= $member['mb_level'] && $mb['mb_id'] != $member['mb_id']) {
    alert("자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.");
}

check_admin_token();

// 회원자료 삭제
member_delete($mb['mb_id']);

if (isset($url)) {
    goto_url("{$url}?$qstr&amp;w=u&amp;mb_id=" . $mb['mb_id']);
} else {
    goto_url("./member_list.php?$qstr");
}
```
### adm/_z01/_adm/member_form_update.php
```php
<?php
$sub_menu = "200100";
require_once "./_common.php";
require_once G5_LIB_PATH . "/register.lib.php";
require_once G5_LIB_PATH . '/thumbnail.lib.php';

if ($w == 'u') {
    check_demo();
}

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$mb_id          = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
$mb_certify_case = isset($_POST['mb_certify_case']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['mb_certify_case']) : '';
$mb_certify     = isset($_POST['mb_certify']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['mb_certify']) : '';
$mb_zip         = isset($_POST['mb_zip']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['mb_zip']) : '';

// 관리자가 자동등록방지를 사용해야 할 경우 ( 회원의 비밀번호 변경시 캡챠를 체크한다 )
if ($mb_password && function_exists('get_admin_captcha_by') && get_admin_captcha_by()) {
    include_once(G5_CAPTCHA_PATH . '/captcha.lib.php');

    if (!chk_captcha()) {
        alert('자동등록방지 숫자가 틀렸습니다.');
    }
}

// 휴대폰번호 체크
$mb_hp = hyphen_hp_number($_POST['mb_hp']);
if ($mb_hp) {
    $result = exist_mb_hp($mb_hp, $mb_id);
    if ($result) {
        alert($result);
    }
}

// 인증정보처리
if ($mb_certify_case && $mb_certify) {
    $mb_certify = isset($_POST['mb_certify_case']) ? preg_replace('/[^0-9a-z_]/i', '', (string)$_POST['mb_certify_case']) : '';
    $mb_adult = isset($_POST['mb_adult']) ? preg_replace('/[^0-9a-z_]/i', '', (string)$_POST['mb_adult']) : '';
} else {
    $mb_certify = '';
    $mb_adult = 0;
}

$mb_zip1 = substr($mb_zip, 0, 3);
$mb_zip2 = substr($mb_zip, 3);

$mb_email = isset($_POST['mb_email']) ? get_email_address(trim($_POST['mb_email'])) : '';
$mb_nick = isset($_POST['mb_nick']) ? trim(strip_tags($_POST['mb_nick'])) : '';

if ($msg = valid_mb_nick($mb_nick)) {
    alert($msg, "", true, true);
}

$posts = array();
$check_keys = array(
    'mb_name',
    'mb_homepage',
    'mb_tel',
    'mb_addr1',
    'mb_addr2',
    'mb_addr3',
    'mb_addr_jibeon',
    'mb_signature',
    'mb_leave_date',
    'mb_intercept_date',
    'mb_mailling',
    'mb_sms',
    'mb_open',
    'mb_profile',
    'mb_level'
);

for ($i = 1; $i <= 10; $i++) {
    $check_keys[] = 'mb_' . $i;
}

foreach ($check_keys as $key) {
    if( in_array($key, array('mb_signature', 'mb_profile')) ){
        $posts[$key] = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1, 0, 0) : '';
    } else {
        $posts[$key] = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
    }
}

$mb_memo = isset($_POST['mb_memo']) ? $_POST['mb_memo'] : '';

$sql_common = "  mb_name = '{$posts['mb_name']}',
                 mb_nick = '{$mb_nick}',
                 mb_email = '{$mb_email}',
                 mb_homepage = '{$posts['mb_homepage']}',
                 mb_tel = '{$posts['mb_tel']}',
                 mb_hp = '{$mb_hp}',
                 mb_certify = '{$mb_certify}',
                 mb_adult = '{$mb_adult}',
                 mb_zip1 = '$mb_zip1',
                 mb_zip2 = '$mb_zip2',
                 mb_addr1 = '{$posts['mb_addr1']}',
                 mb_addr2 = '{$posts['mb_addr2']}',
                 mb_addr3 = '{$posts['mb_addr3']}',
                 mb_addr_jibeon = '{$posts['mb_addr_jibeon']}',
                 mb_signature = '{$posts['mb_signature']}',
                 mb_leave_date = '{$posts['mb_leave_date']}',
                 mb_intercept_date='{$posts['mb_intercept_date']}',
                 mb_memo = '{$mb_memo}',
                 mb_mailling = '{$posts['mb_mailling']}',
                 mb_sms = '{$posts['mb_sms']}',
                 mb_open = '{$posts['mb_open']}',
                 mb_profile = '{$posts['mb_profile']}',
                 mb_level = '{$posts['mb_level']}',
                 mb_1 = '{$posts['mb_1']}',
                 mb_2 = '{$posts['mb_2']}',
                 mb_3 = '{$posts['mb_3']}',
                 mb_4 = '{$posts['mb_4']}',
                 mb_5 = '{$posts['mb_5']}',
                 mb_6 = '{$posts['mb_6']}',
                 mb_7 = '{$posts['mb_7']}',
                 mb_8 = '{$posts['mb_8']}',
                 mb_9 = '{$posts['mb_9']}',
                 mb_10 = '{$posts['mb_10']}' ";

if ($w == '') {
    $mb = get_member($mb_id);
    if (isset($mb['mb_id']) && $mb['mb_id']) {
        alert('이미 존재하는 회원아이디입니다.\\nＩＤ : ' . $mb['mb_id'] . '\\n이름 : ' . $mb['mb_name'] . '\\n닉네임 : ' . $mb['mb_nick'] . '\\n메일 : ' . $mb['mb_email']);
    }

    // 닉네임중복체크
    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_nick = '{$mb_nick}' ";
    $row = sql_fetch($sql);
    if (isset($row['mb_id']) && $row['mb_id']) {
        alert('이미 존재하는 닉네임입니다.\\nＩＤ : ' . $row['mb_id'] . '\\n이름 : ' . $row['mb_name'] . '\\n닉네임 : ' . $row['mb_nick'] . '\\n메일 : ' . $row['mb_email']);
    }

    // 이메일중복체크
    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_email = '{$mb_email}' ";
    $row = sql_fetch($sql);
    if (isset($row['mb_id']) && $row['mb_id']) {
        alert('이미 존재하는 이메일입니다.\\nＩＤ : ' . $row['mb_id'] . '\\n이름 : ' . $row['mb_name'] . '\\n닉네임 : ' . $row['mb_nick'] . '\\n메일 : ' . $row['mb_email']);
    }

    sql_query(" insert into {$g5['member_table']} set mb_id = '{$mb_id}', mb_password = '" . get_encrypt_string($mb_password) . "', mb_datetime = '" . G5_TIME_YMDHIS . "', mb_ip = '{$_SERVER['REMOTE_ADDR']}', mb_email_certify = '" . G5_TIME_YMDHIS . "', {$sql_common} ");
} elseif ($w == 'u') {
    $mb = get_member($mb_id);
    if (!(isset($mb['mb_id']) && $mb['mb_id'])) {
        alert('존재하지 않는 회원자료입니다.');
    }

    if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level'] && $mb['mb_id'] != $member['mb_id']) {
        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');
    }

    if ($is_admin !== 'super' && is_admin($mb['mb_id']) === 'super') {
        alert('최고관리자의 비밀번호를 수정할수 없습니다.');
    }

    if ($mb_id === $member['mb_id'] && $_POST['mb_level'] != $mb['mb_level']) {
        alert($mb['mb_id'] . ' : 로그인 중인 관리자 레벨은 수정할 수 없습니다.');
    }

    if ($posts['mb_leave_date'] || $posts['mb_intercept_date']){
        if ($member['mb_id'] === $mb['mb_id'] || is_admin($mb['mb_id']) === 'super'){
            alert('해당 관리자의 탈퇴 일자 또는 접근 차단 일자를 수정할 수 없습니다.');
        }
    }

    // 닉네임중복체크
    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_nick = '{$mb_nick}' and mb_id <> '$mb_id' ";
    $row = sql_fetch($sql);
    if (isset($row['mb_id']) && $row['mb_id']) {
        alert('이미 존재하는 닉네임입니다.\\nＩＤ : ' . $row['mb_id'] . '\\n이름 : ' . $row['mb_name'] . '\\n닉네임 : ' . $row['mb_nick'] . '\\n메일 : ' . $row['mb_email']);
    }

    // 이메일중복체크
    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_email = '{$mb_email}' and mb_id <> '$mb_id' ";
    $row = sql_fetch($sql);
    if (isset($row['mb_id']) && $row['mb_id']) {
        alert('이미 존재하는 이메일입니다.\\nＩＤ : ' . $row['mb_id'] . '\\n이름 : ' . $row['mb_name'] . '\\n닉네임 : ' . $row['mb_nick'] . '\\n메일 : ' . $row['mb_email']);
    }

    if ($mb_password) {
        $sql_password = " , mb_password = '" . get_encrypt_string($mb_password) . "' ";
    } else {
        $sql_password = "";
    }

    if (isset($passive_certify) && $passive_certify) {
        $sql_certify = " , mb_email_certify = '" . G5_TIME_YMDHIS . "' ";
    } else {
        $sql_certify = "";
    }

    $sql = " update {$g5['member_table']}
                set {$sql_common}
                     {$sql_password}
                     {$sql_certify}
                where mb_id = '{$mb_id}' ";
    sql_query($sql);
} else {
    alert('제대로 된 값이 넘어오지 않았습니다.');
}

if ($w == '' || $w == 'u') {
    $mb_dir = substr($mb_id, 0, 2);
    $mb_icon_img = get_mb_icon_name($mb_id) . '.gif';

    // 회원 아이콘 삭제
    if (isset($del_mb_icon) && $del_mb_icon) {
        @unlink(G5_DATA_PATH . '/member/' . $mb_dir . '/' . $mb_icon_img);
    }

    $image_regex = "/(\.(gif|jpe?g|png))$/i";

    // 아이콘 업로드
    if (isset($_FILES['mb_icon']) && is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
        if (!preg_match($image_regex, $_FILES['mb_icon']['name'])) {
            alert($_FILES['mb_icon']['name'] . '은(는) 이미지 파일이 아닙니다.');
        }

        if (preg_match($image_regex, $_FILES['mb_icon']['name'])) {
            $mb_icon_dir = G5_DATA_PATH . '/member/' . $mb_dir;
            @mkdir($mb_icon_dir, G5_DIR_PERMISSION);
            @chmod($mb_icon_dir, G5_DIR_PERMISSION);

            $dest_path = $mb_icon_dir . '/' . $mb_icon_img;

            move_uploaded_file($_FILES['mb_icon']['tmp_name'], $dest_path);
            chmod($dest_path, G5_FILE_PERMISSION);

            if (file_exists($dest_path)) {
                $size = @getimagesize($dest_path);
                if ($size) {
                    if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height']) {
                        $thumb = null;
                        if ($size[2] === 2 || $size[2] === 3) {
                            //jpg 또는 png 파일 적용
                            $thumb = thumbnail($mb_icon_img, $mb_icon_dir, $mb_icon_dir, $config['cf_member_icon_width'], $config['cf_member_icon_height'], true, true);
                            if ($thumb) {
                                @unlink($dest_path);
                                rename($mb_icon_dir . '/' . $thumb, $dest_path);
                            }
                        }
                        if (!$thumb) {
                            // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                            @unlink($dest_path);
                        }
                    }
                }
            }
        }
    }

    $mb_img_dir = G5_DATA_PATH . '/member_image/';
    if (!is_dir($mb_img_dir)) {
        @mkdir($mb_img_dir, G5_DIR_PERMISSION);
        @chmod($mb_img_dir, G5_DIR_PERMISSION);
    }
    $mb_img_dir .= substr($mb_id, 0, 2);

    // 회원 이미지 삭제
    if (isset($del_mb_img) && $del_mb_img) {
        @unlink($mb_img_dir . '/' . $mb_icon_img);
    }

    // 아이콘 업로드
    if (isset($_FILES['mb_img']) && is_uploaded_file($_FILES['mb_img']['tmp_name'])) {
        if (!preg_match($image_regex, $_FILES['mb_img']['name'])) {
            alert($_FILES['mb_img']['name'] . '은(는) 이미지 파일이 아닙니다.');
        }

        if (preg_match($image_regex, $_FILES['mb_img']['name'])) {
            @mkdir($mb_img_dir, G5_DIR_PERMISSION);
            @chmod($mb_img_dir, G5_DIR_PERMISSION);

            $dest_path = $mb_img_dir . '/' . $mb_icon_img;

            move_uploaded_file($_FILES['mb_img']['tmp_name'], $dest_path);
            chmod($dest_path, G5_FILE_PERMISSION);

            if (file_exists($dest_path)) {
                $size = @getimagesize($dest_path);
                if ($size) {
                    if ($size[0] > $config['cf_member_img_width'] || $size[1] > $config['cf_member_img_height']) {
                        $thumb = null;
                        if ($size[2] === 2 || $size[2] === 3) {
                            //jpg 또는 png 파일 적용
                            $thumb = thumbnail($mb_icon_img, $mb_img_dir, $mb_img_dir, $config['cf_member_img_width'], $config['cf_member_img_height'], true, true);
                            if ($thumb) {
                                @unlink($dest_path);
                                rename($mb_img_dir . '/' . $thumb, $dest_path);
                            }
                        }
                        if (!$thumb) {
                            // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                            @unlink($dest_path);
                        }
                    }
                }
            }
        }
    }
}

if (function_exists('get_admin_captcha_by')) {
    get_admin_captcha_by('remove');
}


goto_url('./member_form.php?' . $qstr . '&amp;w=u&amp;mb_id=' . $mb_id, false);
```
### adm/_z01/_adm/member_form.php
```php
<?php
$sub_menu = "200100";
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'w');

$mb = array(
    'mb_certify' => null,
    'mb_adult' => null,
    'mb_sms' => null,
    'mb_intercept_date' => null,
    'mb_id' => null,
    'mb_name' => null,
    'mb_nick' => null,
    'mb_point' => null,
    'mb_email' => null,
    'mb_homepage' => null,
    'mb_hp' => null,
    'mb_tel' => null,
    'mb_zip1' => null,
    'mb_zip2' => null,
    'mb_addr1' => null,
    'mb_addr2' => null,
    'mb_addr3' => null,
    'mb_addr_jibeon' => null,
    'mb_signature' => null,
    'mb_profile' => null,
    'mb_memo' => null,
    'mb_leave_date' => null,
    'mb_1' => null,
    'mb_2' => null,
    'mb_3' => null,
    'mb_4' => null,
    'mb_5' => null,
    'mb_6' => null,
    'mb_7' => null,
    'mb_8' => null,
    'mb_9' => null,
    'mb_10' => null,
);

$sound_only = '';
$required_mb_id = '';
$required_mb_id_class = '';
$required_mb_password = '';
$html_title = '';

if ($w == '') {
    $required_mb_id = 'required';
    $required_mb_id_class = 'required alnum_';
    $required_mb_password = 'required';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $mb['mb_mailling'] = 1;
    $mb['mb_open'] = 1;
    $mb['mb_level'] = $config['cf_register_level'];
    $html_title = '추가';
} elseif ($w == 'u') {
    $mb = get_member($mb_id);
    if (!$mb['mb_id']) {
        alert('존재하지 않는 회원자료입니다.');
    }

    if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level'] && $mb['mb_id'] != $member['mb_id']) {
        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');
    }

    $required_mb_id = 'readonly';
    $html_title = '수정';

    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_homepage'] = get_text($mb['mb_homepage']);
    $mb['mb_birth'] = get_text($mb['mb_birth']);
    $mb['mb_tel'] = get_text($mb['mb_tel']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
    $mb['mb_addr1'] = get_text($mb['mb_addr1']);
    $mb['mb_addr2'] = get_text($mb['mb_addr2']);
    $mb['mb_addr3'] = get_text($mb['mb_addr3']);
    $mb['mb_signature'] = get_text($mb['mb_signature']);
    $mb['mb_recommend'] = get_text($mb['mb_recommend']);
    $mb['mb_profile'] = get_text($mb['mb_profile']);
    $mb['mb_1'] = get_text($mb['mb_1']);
    $mb['mb_2'] = get_text($mb['mb_2']);
    $mb['mb_3'] = get_text($mb['mb_3']);
    $mb['mb_4'] = get_text($mb['mb_4']);
    $mb['mb_5'] = get_text($mb['mb_5']);
    $mb['mb_6'] = get_text($mb['mb_6']);
    $mb['mb_7'] = get_text($mb['mb_7']);
    $mb['mb_8'] = get_text($mb['mb_8']);
    $mb['mb_9'] = get_text($mb['mb_9']);
    $mb['mb_10'] = get_text($mb['mb_10']);
} else {
    alert('제대로 된 값이 넘어오지 않았습니다.');
}

// 본인확인방법
switch ($mb['mb_certify']) {
    case 'simple':
        $mb_certify_case = '간편인증';
        $mb_certify_val = 'simple';
        break;
    case 'hp':
        $mb_certify_case = '휴대폰';
        $mb_certify_val = 'hp';
        break;
    case 'ipin':
        $mb_certify_case = '아이핀';
        $mb_certify_val = 'ipin';
        break;
    case 'admin':
        $mb_certify_case = '관리자 수정';
        $mb_certify_val = 'admin';
        break;
    default:
        $mb_certify_case = '';
        $mb_certify_val = 'admin';
        break;
}

// 본인확인
$mb_certify_yes  =  $mb['mb_certify'] ? 'checked="checked"' : '';
$mb_certify_no   = !$mb['mb_certify'] ? 'checked="checked"' : '';

// 성인인증
$mb_adult_yes       =  $mb['mb_adult']      ? 'checked="checked"' : '';
$mb_adult_no        = !$mb['mb_adult']      ? 'checked="checked"' : '';

//메일수신
$mb_mailling_yes    =  $mb['mb_mailling']   ? 'checked="checked"' : '';
$mb_mailling_no     = !$mb['mb_mailling']   ? 'checked="checked"' : '';

// SMS 수신
$mb_sms_yes         =  $mb['mb_sms']        ? 'checked="checked"' : '';
$mb_sms_no          = !$mb['mb_sms']        ? 'checked="checked"' : '';

// 정보 공개
$mb_open_yes        =  $mb['mb_open']       ? 'checked="checked"' : '';
$mb_open_no         = !$mb['mb_open']       ? 'checked="checked"' : '';

if (isset($mb['mb_certify'])) {
    // 날짜시간형이라면 drop 시킴
    if (preg_match("/-/", $mb['mb_certify'])) {
        sql_query(" ALTER TABLE `{$g5['member_table']}` DROP `mb_certify` ", false);
    }
} else {
    sql_query(" ALTER TABLE `{$g5['member_table']}` ADD `mb_certify` TINYINT(4) NOT NULL DEFAULT '0' AFTER `mb_hp` ", false);
}

if (isset($mb['mb_adult'])) {
    sql_query(" ALTER TABLE `{$g5['member_table']}` CHANGE `mb_adult` `mb_adult` TINYINT(4) NOT NULL DEFAULT '0' ", false);
} else {
    sql_query(" ALTER TABLE `{$g5['member_table']}` ADD `mb_adult` TINYINT NOT NULL DEFAULT '0' AFTER `mb_certify` ", false);
}

// 지번주소 필드추가
if (!isset($mb['mb_addr_jibeon'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_addr_jibeon` varchar(255) NOT NULL DEFAULT '' AFTER `mb_addr2` ", false);
}

// 건물명필드추가
if (!isset($mb['mb_addr3'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_addr3` varchar(255) NOT NULL DEFAULT '' AFTER `mb_addr2` ", false);
}

// 중복가입 확인필드 추가
if (!isset($mb['mb_dupinfo'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_dupinfo` varchar(255) NOT NULL DEFAULT '' AFTER `mb_adult` ", false);
}

// 이메일인증 체크 필드추가
if (!isset($mb['mb_email_certify2'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_email_certify2` varchar(255) NOT NULL DEFAULT '' AFTER `mb_email_certify` ", false);
}

// 본인인증 내역 테이블 정보가 dbconfig에 없으면 소셜 테이블 정의
if (!isset($g5['member_cert_history'])) {
    $g5['member_cert_history_table'] = G5_TABLE_PREFIX . 'member_cert_history';
}
// 멤버 본인인증 정보 변경 내역 테이블 없을 경우 생성
if (isset($g5['member_cert_history_table']) && !sql_query(" DESC {$g5['member_cert_history_table']} ", false)) {
    sql_query(
        " CREATE TABLE IF NOT EXISTS `{$g5['member_cert_history_table']}` (
                    `ch_id` int(11) NOT NULL auto_increment,
                    `mb_id` varchar(20) NOT NULL DEFAULT '',
                    `ch_name` varchar(255) NOT NULL DEFAULT '',
                    `ch_hp` varchar(255) NOT NULL DEFAULT '',
                    `ch_birth` varchar(255) NOT NULL DEFAULT '',
                    `ch_type` varchar(20) NOT NULL DEFAULT '',
                    `ch_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
                    PRIMARY KEY (`ch_id`),
                    KEY `mb_id` (`mb_id`)
                ) ",
        true
    );
}

$mb_cert_history = '';
if (isset($mb_id) && $mb_id) {
    $sql = "select * from {$g5['member_cert_history_table']} where mb_id = '{$mb_id}' order by ch_id asc";
    $mb_cert_history = sql_query($sql);
}

if ($mb['mb_intercept_date']) {
    $g5['title'] = "차단된 ";
} else {
    $g5['title'] = "";
}
$g5['title'] .= '회원 ' . $html_title;
require_once G5_ADMIN_PATH.'/admin.head.php';

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="fmember" id="fmember" action="./member_form_update.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">

    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="mb_id">아이디<?php echo $sound_only ?></label></th>
                    <td>
                        <input type="text" name="mb_id" value="<?php echo $mb['mb_id'] ?>" id="mb_id" <?php echo $required_mb_id ?> class="frm_input <?php echo $required_mb_id_class ?>" size="15" maxlength="20">
                        <?php if ($w == 'u') { ?><a href="./boardgroupmember_form.php?mb_id=<?php echo $mb['mb_id'] ?>" class="btn_frmline">접근가능그룹보기</a><?php } ?>
                    </td>
                    <th scope="row"><label for="mb_password">비밀번호<?php echo $sound_only ?></label></th>
                    <td>
                        <div>
                        <input type="password" name="mb_password" id="mb_password" <?php echo $required_mb_password ?> class="frm_input <?php echo $required_mb_password ?>" size="15" maxlength="20">
                        </div>
                        <div id="mb_password_captcha_wrap" style="display:none">
                            <?php
                            require_once G5_CAPTCHA_PATH . '/captcha.lib.php';
                            $captcha_html = captcha_html();
                            $captcha_js   = chk_captcha_js();
                            echo $captcha_html;
                            ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_name">이름(실명)<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="mb_name" value="<?php echo $mb['mb_name'] ?>" id="mb_name" required class="required frm_input" size="15" maxlength="20"></td>
                    <th scope="row"><label for="mb_nick">닉네임<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" id="mb_nick" required class="required frm_input" size="15" maxlength="20"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_level">회원 권한</label></th>
                    <td><?php echo get_member_level_select('mb_level', 1, $member['mb_level'], $mb['mb_level']) ?></td>
                    <th scope="row">포인트</th>
                    <td><a href="./point_list.php?sfl=mb_id&amp;stx=<?php echo $mb['mb_id'] ?>" target="_blank"><?php echo number_format($mb['mb_point']) ?></a> 점</td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_email">E-mail<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="mb_email" value="<?php echo $mb['mb_email'] ?>" id="mb_email" maxlength="100" required class="required frm_input email" size="30"></td>
                    <th scope="row"><label for="mb_homepage">홈페이지</label></th>
                    <td><input type="text" name="mb_homepage" value="<?php echo $mb['mb_homepage'] ?>" id="mb_homepage" class="frm_input" maxlength="255" size="15"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_hp">휴대폰번호</label></th>
                    <td><input type="text" name="mb_hp" value="<?php echo $mb['mb_hp'] ?>" id="mb_hp" class="frm_input" size="15" maxlength="20"></td>
                    <th scope="row"><label for="mb_tel">전화번호</label></th>
                    <td><input type="text" name="mb_tel" value="<?php echo $mb['mb_tel'] ?>" id="mb_tel" class="frm_input" size="15" maxlength="20"></td>
                </tr>
                <tr>
                    <th scope="row">본인확인방법</th>
                    <td colspan="3">
                        <input type="radio" name="mb_certify_case" value="simple" id="mb_certify_sa" <?php if ($mb['mb_certify'] == 'simple') { echo 'checked="checked"'; } ?>>
                        <label for="mb_certify_sa">간편인증</label>
                        <input type="radio" name="mb_certify_case" value="hp" id="mb_certify_hp" <?php if ($mb['mb_certify'] == 'hp') { echo 'checked="checked"'; } ?>>
                        <label for="mb_certify_hp">휴대폰</label>
                        <input type="radio" name="mb_certify_case" value="ipin" id="mb_certify_ipin" <?php if ($mb['mb_certify'] == 'ipin') { echo 'checked="checked"'; } ?>>
                        <label for="mb_certify_ipin">아이핀</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">본인확인</th>
                    <td>
                        <input type="radio" name="mb_certify" value="1" id="mb_certify_yes" <?php echo $mb_certify_yes; ?>>
                        <label for="mb_certify_yes">예</label>
                        <input type="radio" name="mb_certify" value="0" id="mb_certify_no" <?php echo $mb_certify_no; ?>>
                        <label for="mb_certify_no">아니오</label>
                    </td>
                    <th scope="row">성인인증</th>
                    <td>
                        <input type="radio" name="mb_adult" value="1" id="mb_adult_yes" <?php echo $mb_adult_yes; ?>>
                        <label for="mb_adult_yes">예</label>
                        <input type="radio" name="mb_adult" value="0" id="mb_adult_no" <?php echo $mb_adult_no; ?>>
                        <label for="mb_adult_no">아니오</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">주소</th>
                    <td colspan="3" class="td_addr_line">
                        <label for="mb_zip" class="sound_only">우편번호</label>
                        <input type="text" name="mb_zip" value="<?php echo $mb['mb_zip1'] . $mb['mb_zip2']; ?>" id="mb_zip" class="frm_input readonly" size="5" maxlength="6">
                        <button type="button" class="btn_frmline" onclick="win_zip('fmember', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소 검색</button><br>
                        <input type="text" name="mb_addr1" value="<?php echo $mb['mb_addr1'] ?>" id="mb_addr1" class="frm_input readonly" size="60">
                        <label for="mb_addr1">기본주소</label><br>
                        <input type="text" name="mb_addr2" value="<?php echo $mb['mb_addr2'] ?>" id="mb_addr2" class="frm_input" size="60">
                        <label for="mb_addr2">상세주소</label>
                        <br>
                        <input type="text" name="mb_addr3" value="<?php echo $mb['mb_addr3'] ?>" id="mb_addr3" class="frm_input" size="60">
                        <label for="mb_addr3">참고항목</label>
                        <input type="hidden" name="mb_addr_jibeon" value="<?php echo $mb['mb_addr_jibeon']; ?>"><br>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_icon">회원아이콘</label></th>
                    <td colspan="3">
                        <?php echo help('이미지 크기는 <strong>넓이 ' . $config['cf_member_icon_width'] . '픽셀 높이 ' . $config['cf_member_icon_height'] . '픽셀</strong>로 해주세요.') ?>
                        <input type="file" name="mb_icon" id="mb_icon">
                        <?php
                        $mb_dir = substr($mb['mb_id'], 0, 2);
                        $icon_file = G5_DATA_PATH . '/member/' . $mb_dir . '/' . get_mb_icon_name($mb['mb_id']) . '.gif';
                        if (file_exists($icon_file)) {
                            $icon_url = str_replace(G5_DATA_PATH, G5_DATA_URL, $icon_file);
                            $icon_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME) ? '?' . filemtime($icon_file) : '';
                            echo '<img src="' . $icon_url . $icon_filemtile . '" alt="">';
                            echo '<input type="checkbox" id="del_mb_icon" name="del_mb_icon" value="1">삭제';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_img">회원이미지</label></th>
                    <td colspan="3">
                        <?php echo help('이미지 크기는 <strong>넓이 ' . $config['cf_member_img_width'] . '픽셀 높이 ' . $config['cf_member_img_height'] . '픽셀</strong>로 해주세요.') ?>
                        <input type="file" name="mb_img" id="mb_img">
                        <?php
                        $mb_dir = substr($mb['mb_id'], 0, 2);
                        $icon_file = G5_DATA_PATH . '/member_image/' . $mb_dir . '/' . get_mb_icon_name($mb['mb_id']) . '.gif';
                        if (file_exists($icon_file)) {
                            echo get_member_profile_img($mb['mb_id']);
                            echo '<input type="checkbox" id="del_mb_img" name="del_mb_img" value="1">삭제';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">메일 수신</th>
                    <td>
                        <input type="radio" name="mb_mailling" value="1" id="mb_mailling_yes" <?php echo $mb_mailling_yes; ?>>
                        <label for="mb_mailling_yes">예</label>
                        <input type="radio" name="mb_mailling" value="0" id="mb_mailling_no" <?php echo $mb_mailling_no; ?>>
                        <label for="mb_mailling_no">아니오</label>
                    </td>
                    <th scope="row"><label for="mb_sms_yes">SMS 수신</label></th>
                    <td>
                        <input type="radio" name="mb_sms" value="1" id="mb_sms_yes" <?php echo $mb_sms_yes; ?>>
                        <label for="mb_sms_yes">예</label>
                        <input type="radio" name="mb_sms" value="0" id="mb_sms_no" <?php echo $mb_sms_no; ?>>
                        <label for="mb_sms_no">아니오</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">정보 공개</th>
                    <td colspan="3">
                        <input type="radio" name="mb_open" value="1" id="mb_open_yes" <?php echo $mb_open_yes; ?>>
                        <label for="mb_open_yes">예</label>
                        <input type="radio" name="mb_open" value="0" id="mb_open_no" <?php echo $mb_open_no; ?>>
                        <label for="mb_open_no">아니오</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_signature">서명</label></th>
                    <td colspan="3"><textarea name="mb_signature" id="mb_signature"><?php echo html_purifier($mb['mb_signature']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_profile">자기 소개</label></th>
                    <td colspan="3"><textarea name="mb_profile" id="mb_profile"><?php echo html_purifier($mb['mb_profile']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_memo">메모</label></th>
                    <td colspan="3"><textarea name="mb_memo" id="mb_memo"><?php echo html_purifier($mb['mb_memo']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_cert_history">본인인증 내역</label></th>
                    <td colspan="3">
                        <?php
                        $cnt = 0;
                        while ($row = sql_fetch_array($mb_cert_history)) {
                            $cnt++;
                            $cert_type = '';
                            switch ($row['ch_type']) {
                                case 'simple':
                                    $cert_type = '간편인증';
                                    break;
                                case 'hp':
                                    $cert_type = '휴대폰';
                                    break;
                                case 'ipin':
                                    $cert_type = '아이핀';
                                    break;
                            }
                        ?>
                            <div>
                                [<?php echo $row['ch_datetime']; ?>]
                                <?php echo $row['mb_id']; ?> /
                                <?php echo $row['ch_name']; ?> /
                                <?php echo $row['ch_hp']; ?> /
                                <?php echo $cert_type; ?>
                            </div>
                        <?php } ?>

                        <?php if ($cnt == 0) { ?>
                            본인인증 내역이 없습니다.
                        <?php } ?>
                    </td>
                </tr>

                <?php if ($w == 'u') { ?>
                    <tr>
                        <th scope="row">회원가입일</th>
                        <td><?php echo $mb['mb_datetime'] ?></td>
                        <th scope="row">최근접속일</th>
                        <td><?php echo $mb['mb_today_login'] ?></td>
                    </tr>
                    <tr>
                        <th scope="row">IP</th>
                        <td colspan="3"><?php echo $mb['mb_ip'] ?></td>
                    </tr>
                    <?php if ($config['cf_use_email_certify']) { ?>
                        <tr>
                            <th scope="row">인증일시</th>
                            <td colspan="3">
                                <?php if ($mb['mb_email_certify'] == '0000-00-00 00:00:00') { ?>
                                    <?php echo help('회원님이 메일을 수신할 수 없는 경우 등에 직접 인증처리를 하실 수 있습니다.') ?>
                                    <input type="checkbox" name="passive_certify" id="passive_certify">
                                    <label for="passive_certify">수동인증</label>
                                <?php } else { ?>
                                    <?php echo $mb['mb_email_certify'] ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>

                <?php if ($config['cf_use_recommend']) { // 추천인 사용 ?>
                    <tr>
                        <th scope="row">추천인</th>
                        <td colspan="3"><?php echo ($mb['mb_recommend'] ? get_text($mb['mb_recommend']) : '없음'); // 081022 : CSRF 보안 결함으로 인한 코드 수정 ?></td>
                    </tr>
                <?php } ?>

                <tr>
                    <th scope="row"><label for="mb_leave_date">탈퇴일자</label></th>
                    <td>
                        <input type="text" name="mb_leave_date" value="<?php echo $mb['mb_leave_date'] ?>" id="mb_leave_date" class="frm_input" maxlength="8">
                        <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_leave_date_set_today" onclick="if (this.form.mb_leave_date.value==this.form.mb_leave_date.defaultValue) { this.form.mb_leave_date.value=this.value; } else { this.form.mb_leave_date.value=this.form.mb_leave_date.defaultValue; }">
                        <label for="mb_leave_date_set_today">탈퇴일을 오늘로 지정</label>
                    </td>
                    <th scope="row">접근차단일자</th>
                    <td>
                        <input type="text" name="mb_intercept_date" value="<?php echo $mb['mb_intercept_date'] ?>" id="mb_intercept_date" class="frm_input" maxlength="8">
                        <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_intercept_date_set_today" onclick="if (this.form.mb_intercept_date.value==this.form.mb_intercept_date.defaultValue) { this.form.mb_intercept_date.value=this.value; } else { this.form.mb_intercept_date.value=this.form.mb_intercept_date.defaultValue; }">
                        <label for="mb_intercept_date_set_today">접근차단일을 오늘로 지정</label>
                    </td>
                </tr>

                <?php
                //소셜계정이 있다면
                if (function_exists('social_login_link_account') && $mb['mb_id']) {
                    if ($my_social_accounts = social_login_link_account($mb['mb_id'], false, 'get_data')) { ?>
                        <tr>
                            <th>소셜계정목록</th>
                            <td colspan="3">
                                <ul class="social_link_box">
                                    <li class="social_login_container">
                                        <h4>연결된 소셜 계정 목록</h4>
                                        <?php foreach ($my_social_accounts as $account) {     //반복문
                                            if (empty($account)) {
                                                continue;
                                            }

                                            $provider = strtolower($account['provider']);
                                            $provider_name = social_get_provider_service_name($provider);
                                        ?>
                                            <div class="account_provider" data-mpno="social_<?php echo $account['mp_no']; ?>">
                                                <div class="sns-wrap-32 sns-wrap-over">
                                                    <span class="sns-icon sns-<?php echo $provider; ?>" title="<?php echo $provider_name; ?>">
                                                        <span class="ico"></span>
                                                        <span class="txt"><?php echo $provider_name; ?></span>
                                                    </span>

                                                    <span class="provider_name"><?php echo $provider_name;   //서비스이름 ?> ( <?php echo $account['displayname']; ?> )</span>
                                                    <span class="account_hidden" style="display:none"><?php echo $account['mb_id']; ?></span>
                                                </div>
                                                <div class="btn_info"><a href="<?php echo G5_SOCIAL_LOGIN_URL . '/unlink.php?mp_no=' . $account['mp_no'] ?>" class="social_unlink" data-provider="<?php echo $account['mp_no']; ?>">연동해제</a> <span class="sound_only"><?php echo substr($account['mp_register_day'], 2, 14); ?></span></div>
                                            </div>
                                        <?php } //end foreach ?>
                                    </li>
                                </ul>
                                <script>
                                    jQuery(function($) {
                                        $(".account_provider").on("click", ".social_unlink", function(e) {
                                            e.preventDefault();

                                            if (!confirm('정말 이 계정 연결을 삭제하시겠습니까?')) {
                                                return false;
                                            }

                                            var ajax_url = "<?php echo G5_SOCIAL_LOGIN_URL . '/unlink.php' ?>";
                                            var mb_id = '',
                                                mp_no = $(this).attr("data-provider"),
                                                $mp_el = $(this).parents(".account_provider");

                                            mb_id = $mp_el.find(".account_hidden").text();

                                            if (!mp_no) {
                                                alert('잘못된 요청! mp_no 값이 없습니다.');
                                                return;
                                            }

                                            $.ajax({
                                                url: ajax_url,
                                                type: 'POST',
                                                data: {
                                                    'mp_no': mp_no,
                                                    'mb_id': mb_id
                                                },
                                                dataType: 'json',
                                                async: false,
                                                success: function(data, textStatus) {
                                                    if (data.error) {
                                                        alert(data.error);
                                                        return false;
                                                    } else {
                                                        alert("연결이 해제 되었습니다.");
                                                        $mp_el.fadeOut("normal", function() {
                                                            $(this).remove();
                                                        });
                                                    }
                                                }
                                            });

                                            return;
                                        });
                                    });
                                </script>

                            </td>
                        </tr>

                <?php
                    }   //end if
                }   //end if

                run_event('admin_member_form_add', $mb, $w, 'table');
                ?>

                <?php for ($i = 1; $i <= 10; $i++) { ?>
                    <tr>
                        <th scope="row"><label for="mb_<?php echo $i ?>">여분 필드 <?php echo $i ?></label></th>
                        <td colspan="3"><input type="text" name="mb_<?php echo $i ?>" value="<?php echo $mb['mb_' . $i] ?>" id="mb_<?php echo $i ?>" class="frm_input" size="30" maxlength="255"></td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <a href="./member_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
    </div>
</form>

<script>
    function fmember_submit(f) {
        if (!f.mb_icon.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_icon.value) {
            alert('아이콘은 이미지 파일만 가능합니다.');
            return false;
        }

        if (!f.mb_img.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_img.value) {
            alert('회원이미지는 이미지 파일만 가능합니다.');
            return false;
        }

        if( jQuery("#mb_password").val() ){
            <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함 ?>
        }

        return true;
    }

    jQuery(function($){
        $("#captcha_key").prop('required', false).removeAttr("required").removeClass("required");

        $("#mb_password").on("keyup", function(e) {
            var $warp = $("#mb_password_captcha_wrap"),
                tooptipid = "mp_captcha_tooltip",
                $span_text = $("<span>", {id:tooptipid, style:"font-size:0.95em;letter-spacing:-0.1em"}).html("비밀번호를 수정할 경우 캡챠를 입력해야 합니다."),
                $parent = $(this).parent(),
                is_invisible_recaptcha = $("#captcha").hasClass("invisible_recaptcha");

            if($(this).val()){
                $warp.show();
                if(! is_invisible_recaptcha) {
                    $warp.css("margin-top","1em");
                    if(! $("#"+tooptipid).length){ $parent.append($span_text) }
                }
            } else {
                $warp.hide();
                if($("#"+tooptipid).length && ! is_invisible_recaptcha){ $parent.find("#"+tooptipid).remove(); }
            }
        });
    });
</script>
<?php

require_once G5_ADMIN_PATH.'/admin.tail.php';
```
### adm/_z01/_adm/member_list_delete.php
```php
<?php
$sub_menu = "200100";
require_once "./_common.php";

check_demo();

auth_check_menu($auth, $sub_menu, "d");

check_admin_token();

$msg = "";
for ($i = 0; $i < count($chk); $i++) {
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $mb = get_member($_POST['mb_id'][$k]);

    if (!$mb['mb_id']) {
        $msg .= "{$mb['mb_id']} : 회원자료가 존재하지 않습니다.\\n";
    } elseif ($member['mb_id'] == $mb['mb_id']) {
        $msg .= "{$mb['mb_id']} : 로그인 중인 관리자는 삭제 할 수 없습니다.\\n";
    } elseif (is_admin($mb['mb_id']) == "super") {
        $msg .= "{$mb['mb_id']} : 최고 관리자는 삭제할 수 없습니다.\\n";
    } elseif ($is_admin != "super" && $mb['mb_level'] >= $member['mb_level']) {
        $msg .= "{$mb['mb_id']} : 자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.\\n";
    } else {
        // 회원자료 삭제
        member_delete($mb['mb_id']);
    }
}

if ($msg) {
    echo "<script type='text/javascript'> alert('$msg'); </script>";
}

goto_url("./member_list.php?$qstr");
```
### adm/_z01/_adm/member_list_update.php
```php
<?php
$sub_menu = "200100";
require_once './_common.php';

check_demo();

if (!(isset($_POST['chk']) && is_array($_POST['chk']))) {
    alert($_POST['act_button'] . " 하실 항목을 하나 이상 체크하세요.");
}

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$mb_datas = array();
$msg = '';

if ($_POST['act_button'] == "선택수정") {
    for ($i = 0; $i < count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        $post_mb_certify = (isset($_POST['mb_certify'][$k]) && $_POST['mb_certify'][$k]) ? clean_xss_tags($_POST['mb_certify'][$k], 1, 1, 20) : '';
        $post_mb_level = isset($_POST['mb_level'][$k]) ? (int) $_POST['mb_level'][$k] : 0;
        $post_mb_intercept_date = (isset($_POST['mb_intercept_date'][$k]) && $_POST['mb_intercept_date'][$k]) ? clean_xss_tags($_POST['mb_intercept_date'][$k], 1, 1, 8) : '';
        $post_mb_mailling = isset($_POST['mb_mailling'][$k]) ? (int) $_POST['mb_mailling'][$k] : 0;
        $post_mb_sms = isset($_POST['mb_sms'][$k]) ? (int) $_POST['mb_sms'][$k] : 0;
        $post_mb_open = isset($_POST['mb_open'][$k]) ? (int) $_POST['mb_open'][$k] : 0;

        $mb_datas[] = $mb = get_member($_POST['mb_id'][$k]);

        if (!(isset($mb['mb_id']) && $mb['mb_id'])) {
            $msg .= $mb['mb_id'] . ' : 회원자료가 존재하지 않습니다.\\n';
        } elseif ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level']) {
            $msg .= $mb['mb_id'] . ' : 자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.\\n';
        } elseif ($member['mb_id'] == $mb['mb_id']) {
            $msg .= $mb['mb_id'] . ' : 로그인 중인 관리자는 수정 할 수 없습니다.\\n';
        } else {
            if ($post_mb_certify) {
                $mb_adult = isset($_POST['mb_adult'][$k]) ? (int) $_POST['mb_adult'][$k] : 0;
            } else {
                $mb_adult = 0;
            }

            $sql = " update {$g5['member_table']}
                        set mb_level = '" . $post_mb_level . "',
                            mb_intercept_date = '" . sql_real_escape_string($post_mb_intercept_date) . "',
                            mb_mailling = '" . $post_mb_mailling . "',
                            mb_sms = '" . $post_mb_sms . "',
                            mb_open = '" . $post_mb_open . "',
                            mb_certify = '" . sql_real_escape_string($post_mb_certify) . "',
                            mb_adult = '{$mb_adult}'
                        where mb_id = '" . sql_real_escape_string($mb['mb_id']) . "' ";
            sql_query($sql);
        }
    }
} elseif ($_POST['act_button'] == "선택삭제") {
    for ($i = 0; $i < count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        $mb_datas[] = $mb = get_member($_POST['mb_id'][$k]);

        if (!$mb['mb_id']) {
            $msg .= $mb['mb_id'] . ' : 회원자료가 존재하지 않습니다.\\n';
        } elseif ($member['mb_id'] == $mb['mb_id']) {
            $msg .= $mb['mb_id'] . ' : 로그인 중인 관리자는 삭제 할 수 없습니다.\\n';
        } elseif (is_admin($mb['mb_id']) == 'super') {
            $msg .= $mb['mb_id'] . ' : 최고 관리자는 삭제할 수 없습니다.\\n';
        } elseif ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level']) {
            $msg .= $mb['mb_id'] . ' : 자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.\\n';
        } else {
            // 회원자료 삭제
            member_delete($mb['mb_id']);
        }
    }
}

if ($msg) {
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);
}

run_event('admin_member_list_update', $_POST['act_button'], $mb_datas);

goto_url('./member_list.php?' . $qstr);
```
### adm/_z01/_adm/member_list.php
```php
<?php
$sub_menu = "200100";
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from {$g5['member_table']} ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel':
        case 'mb_hp':
            $sql_search .= " ({$sfl} like '%{$stx}') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "mb_datetime";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

// 차단회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = '회원관리';
require_once G5_ADMIN_PATH.'/admin.head.php';

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 16;
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총회원수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>명 </span></span>
    <a href="?sst=mb_intercept_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="차단된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">차단 </span><span class="ov_num"><?php echo number_format($intercept_count) ?>명</span></a>
    <a href="?sst=mb_leave_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="탈퇴된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">탈퇴 </span><span class="ov_num"><?php echo number_format($leave_count) ?>명</span></a>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only2">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="mb_id" <?php echo get_selected($sfl, "mb_id"); ?>>회원아이디</option>
        <option value="mb_nick" <?php echo get_selected($sfl, "mb_nick"); ?>>닉네임</option>
        <option value="mb_name" <?php echo get_selected($sfl, "mb_name"); ?>>이름</option>
        <option value="mb_level" <?php echo get_selected($sfl, "mb_level"); ?>>권한</option>
        <option value="mb_email" <?php echo get_selected($sfl, "mb_email"); ?>>E-MAIL</option>
        <option value="mb_tel" <?php echo get_selected($sfl, "mb_tel"); ?>>전화번호</option>
        <option value="mb_hp" <?php echo get_selected($sfl, "mb_hp"); ?>>휴대폰번호</option>
        <option value="mb_point" <?php echo get_selected($sfl, "mb_point"); ?>>포인트</option>
        <option value="mb_datetime" <?php echo get_selected($sfl, "mb_datetime"); ?>>가입일시</option>
        <option value="mb_ip" <?php echo get_selected($sfl, "mb_ip"); ?>>IP</option>
        <option value="mb_recommend" <?php echo get_selected($sfl, "mb_recommend"); ?>>추천인</option>
    </select>
    <label for="stx" class="sound_only2">검색어<strong class="sound_only2"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
    <input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div>


<form name="fmemberlist" id="fmemberlist" action="./member_list_update.php" onsubmit="return fmemberlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col" id="mb_list_chk" rowspan="2">
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col" id="mb_list_id" colspan="2"><?php echo subject_sort_link('mb_id') ?>아이디</a></th>
                    <th scope="col" rowspan="2" id="mb_list_cert"><?php echo subject_sort_link('mb_certify', '', 'desc') ?>본인확인</a></th>
                    <th scope="col" id="mb_list_mailc"><?php echo subject_sort_link('mb_email_certify', '', 'desc') ?>메일인증</a></th>
                    <th scope="col" id="mb_list_open"><?php echo subject_sort_link('mb_open', '', 'desc') ?>정보공개</a></th>
                    <th scope="col" id="mb_list_mailr"><?php echo subject_sort_link('mb_mailling', '', 'desc') ?>메일수신</a></th>
                    <th scope="col" id="mb_list_auth">상태</th>
                    <th scope="col" id="mb_list_mobile">휴대폰</th>
                    <th scope="col" id="mb_list_lastcall"><?php echo subject_sort_link('mb_today_login', '', 'desc') ?>최종접속</a></th>
                    <th scope="col" id="mb_list_grp">접근그룹</th>
                    <th scope="col" rowspan="2" id="mb_list_mng">관리</th>
                </tr>
                <tr>
                    <th scope="col" id="mb_list_name"><?php echo subject_sort_link('mb_name') ?>이름</a></th>
                    <th scope="col" id="mb_list_nick"><?php echo subject_sort_link('mb_nick') ?>닉네임</a></th>
                    <th scope="col" id="mb_list_sms"><?php echo subject_sort_link('mb_sms', '', 'desc') ?>SMS수신</a></th>
                    <th scope="col" id="mb_list_adultc"><?php echo subject_sort_link('mb_adult', '', 'desc') ?>성인인증</a></th>
                    <th scope="col" id="mb_list_auth"><?php echo subject_sort_link('mb_intercept_date', '', 'desc') ?>접근차단</a></th>
                    <th scope="col" id="mb_list_deny"><?php echo subject_sort_link('mb_level', '', 'desc') ?>권한</a></th>
                    <th scope="col" id="mb_list_tel">전화번호</th>
                    <th scope="col" id="mb_list_join"><?php echo subject_sort_link('mb_datetime', '', 'desc') ?>가입일</a></th>
                    <th scope="col" id="mb_list_point"><?php echo subject_sort_link('mb_point', '', 'desc') ?> 포인트</a></th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    // 접근가능한 그룹수
                    $sql2 = " select count(*) as cnt from {$g5['group_member_table']} where mb_id = '{$row['mb_id']}' ";
                    $row2 = sql_fetch($sql2);
                    $group = '';
                    if ($row2['cnt']) {
                        $group = '<a href="./boardgroupmember_form.php?mb_id=' . $row['mb_id'] . '">' . $row2['cnt'] . '</a>';
                    }

                    if ($is_admin == 'group') {
                        $s_mod = '';
                    } else {
                        $s_mod = '<a href="./member_form.php?' . $qstr . '&amp;w=u&amp;mb_id=' . $row['mb_id'] . '" class="btn btn_03">수정</a>';
                    }
                    $s_grp = '<a href="./boardgroupmember_form.php?mb_id=' . $row['mb_id'] . '" class="btn btn_02">그룹</a>';

                    $leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date('Ymd', G5_SERVER_TIME);
                    $intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date('Ymd', G5_SERVER_TIME);

                    $mb_nick = get_sideview($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

                    $mb_id = $row['mb_id'];
                    $leave_msg = '';
                    $intercept_msg = '';
                    $intercept_title = '';
                    if ($row['mb_leave_date']) {
                        $mb_id = $mb_id;
                        $leave_msg = '<span class="mb_leave_msg">탈퇴함</span>';
                    } elseif ($row['mb_intercept_date']) {
                        $mb_id = $mb_id;
                        $intercept_msg = '<span class="mb_intercept_msg">차단됨</span>';
                        $intercept_title = '차단해제';
                    }
                    if ($intercept_title == '') {
                        $intercept_title = '차단하기';
                    }

                    $address = $row['mb_zip1'] ? print_address($row['mb_addr1'], $row['mb_addr2'], $row['mb_addr3'], $row['mb_addr_jibeon']) : '';

                    $bg = 'bg' . ($i % 2);

                    switch ($row['mb_certify']) {
                        case 'hp':
                            $mb_certify_case = '휴대폰';
                            $mb_certify_val = 'hp';
                            break;
                        case 'ipin':
                            $mb_certify_case = '아이핀';
                            $mb_certify_val = '';
                            break;
                        case 'simple':
                            $mb_certify_case = '간편인증';
                            $mb_certify_val = '';
                            break;
                        case 'admin':
                            $mb_certify_case = '관리자';
                            $mb_certify_val = 'admin';
                            break;
                        default:
                            $mb_certify_case = '&nbsp;';
                            $mb_certify_val = 'admin';
                            break;
                    }
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <td headers="mb_list_chk" class="td_chk" rowspan="2">
                            <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_<?php echo $i ?>">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                        </td>
                        <td headers="mb_list_id" colspan="2" class="td_name sv_use">
                            <?php echo $mb_id ?>
                            <?php
                            //소셜계정이 있다면
                            if (function_exists('social_login_link_account')) {
                                if ($my_social_accounts = social_login_link_account($row['mb_id'], false, 'get_data')) {
                                    echo '<div class="member_social_provider sns-wrap-over sns-wrap-32">';
                                    foreach ((array) $my_social_accounts as $account) {     //반복문
                                        if (empty($account) || empty($account['provider'])) {
                                            continue;
                                        }

                                        $provider = strtolower($account['provider']);
                                        $provider_name = social_get_provider_service_name($provider);

                                        echo '<span class="sns-icon sns-' . $provider . '" title="' . $provider_name . '">';
                                        echo '<span class="ico"></span>';
                                        echo '<span class="txt">' . $provider_name . '</span>';
                                        echo '</span>';
                                    }
                                    echo '</div>';
                                }
                            }
                            ?>
                        </td>
                        <td headers="mb_list_cert" rowspan="2" class="td_mbcert">
                            <input type="radio" name="mb_certify[<?php echo $i; ?>]" value="simple" id="mb_certify_sa_<?php echo $i; ?>" <?php echo $row['mb_certify'] == 'simple' ? 'checked' : ''; ?>>
                            <label for="mb_certify_sa_<?php echo $i; ?>">간편인증</label><br>
                            <input type="radio" name="mb_certify[<?php echo $i; ?>]" value="hp" id="mb_certify_hp_<?php echo $i; ?>" <?php echo $row['mb_certify'] == 'hp' ? 'checked' : ''; ?>>
                            <label for="mb_certify_hp_<?php echo $i; ?>">휴대폰</label><br>
                            <input type="radio" name="mb_certify[<?php echo $i; ?>]" value="ipin" id="mb_certify_ipin_<?php echo $i; ?>" <?php echo $row['mb_certify'] == 'ipin' ? 'checked' : ''; ?>>
                            <label for="mb_certify_ipin_<?php echo $i; ?>">아이핀</label>
                        </td>
                        <td headers="mb_list_mailc"><?php echo preg_match('/[1-9]/', $row['mb_email_certify']) ? '<span class="txt_true">Yes</span>' : '<span class="txt_false">No</span>'; ?></td>
                        <td headers="mb_list_open">
                            <label for="mb_open_<?php echo $i; ?>" class="sound_only">정보공개</label>
                            <input type="checkbox" name="mb_open[<?php echo $i; ?>]" <?php echo $row['mb_open'] ? 'checked' : ''; ?> value="1" id="mb_open_<?php echo $i; ?>">
                        </td>
                        <td headers="mb_list_mailr">
                            <label for="mb_mailling_<?php echo $i; ?>" class="sound_only">메일수신</label>
                            <input type="checkbox" name="mb_mailling[<?php echo $i; ?>]" <?php echo $row['mb_mailling'] ? 'checked' : ''; ?> value="1" id="mb_mailling_<?php echo $i; ?>">
                        </td>
                        <td headers="mb_list_auth" class="td_mbstat">
                            <?php
                            if ($leave_msg || $intercept_msg) {
                                echo $leave_msg . ' ' . $intercept_msg;
                            } else {
                                echo "정상";
                            }
                            ?>
                        </td>
                        <td headers="mb_list_mobile" class="td_tel"><?php echo get_text($row['mb_hp']); ?></td>
                        <td headers="mb_list_lastcall" class="td_date"><?php echo substr($row['mb_today_login'], 2, 8); ?></td>
                        <td headers="mb_list_grp" class="td_numsmall"><?php echo $group ?></td>
                        <td headers="mb_list_mng" rowspan="2" class="td_mng td_mng_s"><?php echo $s_mod ?><?php echo $s_grp ?></td>
                    </tr>
                    <tr class="<?php echo $bg; ?>">
                        <td headers="mb_list_name" class="td_mbname"><?php echo get_text($row['mb_name']); ?></td>
                        <td headers="mb_list_nick" class="td_name sv_use">
                            <div><?php echo $mb_nick ?></div>
                        </td>

                        <td headers="mb_list_sms">
                            <label for="mb_sms_<?php echo $i; ?>" class="sound_only">SMS수신</label>
                            <input type="checkbox" name="mb_sms[<?php echo $i; ?>]" <?php echo $row['mb_sms'] ? 'checked' : ''; ?> value="1" id="mb_sms_<?php echo $i; ?>">
                        </td>
                        <td headers="mb_list_adultc">
                            <label for="mb_adult_<?php echo $i; ?>" class="sound_only">성인인증</label>
                            <input type="checkbox" name="mb_adult[<?php echo $i; ?>]" <?php echo $row['mb_adult'] ? 'checked' : ''; ?> value="1" id="mb_adult_<?php echo $i; ?>">
                        </td>
                        <td headers="mb_list_deny">
                            <?php if (empty($row['mb_leave_date'])) { ?>
                                <input type="checkbox" name="mb_intercept_date[<?php echo $i; ?>]" <?php echo $row['mb_intercept_date'] ? 'checked' : ''; ?> value="<?php echo $intercept_date ?>" id="mb_intercept_date_<?php echo $i ?>" title="<?php echo $intercept_title ?>">
                                <label for="mb_intercept_date_<?php echo $i; ?>" class="sound_only">접근차단</label>
                            <?php } ?>
                        </td>
                        <td headers="mb_list_auth" class="td_mbstat">
                            <?php echo get_member_level_select("mb_level[$i]", 1, $member['mb_level'], $row['mb_level']) ?>
                        </td>
                        <td headers="mb_list_tel" class="td_tel"><?php echo get_text($row['mb_tel']); ?></td>
                        <td headers="mb_list_join" class="td_date"><?php echo substr($row['mb_datetime'], 2, 8); ?></td>
                        <td headers="mb_list_point" class="td_num"><a href="point_list.php?sfl=mb_id&amp;stx=<?php echo $row['mb_id'] ?>"><?php echo number_format($row['mb_point']) ?></a></td>

                    </tr>

                <?php
                }
                if ($i == 0) {
                    echo "<tr><td colspan=\"" . $colspan . "\" class=\"empty_table\">자료가 없습니다.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
        <?php if ($is_admin == 'super') { ?>
            <a href="./member_form.php" id="member_add" class="btn btn_01">회원추가</a>
        <?php } ?>

    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
    function fmemberlist_submit(f) {
        if (!is_checked("chk[]")) {
            alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
            return false;
        }

        if (document.pressed == "선택삭제") {
            if (!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
                return false;
            }
        }

        return true;
    }
</script>

<?php
require_once G5_ADMIN_PATH.'/admin.tail.php';
```
### adm/_z01/_adm/menu_form_search.php
```php
<?php
require_once './_common.php';

if (!$is_manager)
    alert('접근권한이 없습니다.');

$type = isset($_REQUEST['type']) ? preg_replace('/[^0-9a-z_]/i', '', $_REQUEST['type']) : '';

switch ($type) {
    case 'group':
        $sql = " select gr_id as id, gr_subject as subject
                    from {$g5['group_table']}
                    order by gr_order, gr_id ";
        break;
    case 'board':
        $sql = " select bo_table as id, bo_subject as subject, gr_id
                    from {$g5['board_table']}
                    order by bo_order, bo_table ";
        break;
    case 'content':
        $sql = " select co_id as id, co_subject as subject
                    from {$g5['content_table']}
                    order by co_id ";
        break;
    case 'shop':
        $sql = " select ca_id as id, ca_name as subject
                    from {$g5['g5_shop_category_table']}
                    order by ca_order, ca_id ";
        break;
    default:
        $sql = '';
        break;
}

$msql = " SELECT me_link FROM {$g5['menu_table']} ";
$mresult = sql_query($msql);
$sch_me_link = array();
for($i=0;$row=sql_fetch_array($mresult);$i++){
	array_push($sch_me_link,$row['me_link']);
}
//print_r2($sch_me_link);
?>
<?php
if($sql) {
    $result = sql_query($sql);
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0) {

    $bbs_subject_title = ($type == 'board') ? '게시판제목' : '제목';
?>
<div class="tbl_head01 tbl_wrap">
	<div style="padding:10px;">
		<p>* <strong style="color:red;">빨간색</strong>의 제목은 이미 메뉴에 연결되어 경우를 의미합니다.</p>
	</div>
    <table>
    <thead>
    <tr>
        <th scope="col"><?php echo $bbs_subject_title; ?></th>
        <?php if($type == 'board'){ ?>
            <th scope="col">게시판 그룹</th>
        <?php } ?>
        <th scope="col">선택</th>
    </tr>
    </thead>
    <tbody>

<?php }
        switch($type) {
            case 'group':
                $link = G5_BBS_URL.'/group.php?gr_id='.$row['id'];
                break;
            case 'board':
                $link = G5_BBS_URL.'/board.php?bo_table='.$row['id'];
                break;
            case 'content':
                $link = G5_BBS_URL.'/content.php?co_id='.$row['id'];
                break;
            case 'shop':
                $link = G5_SHOP_URL.'/list.php?ca_id='.$row['id'];
                break;
            default:
                $link = '';
                break;
        }
		
		$chk_link = str_replace(G5_URL,"",$link);
		$has_link = (in_array($chk_link,$sch_me_link)) ? 'style="color:red;"' : '';
?>

    <tr>
        <td <?=$has_link?>><?php echo $row['subject']; ?></td>
        <?php
        if($type == 'board'){
        $group = get_call_func_cache('get_group', array($row['gr_id']));
        ?>
        <td><?php echo $group['gr_subject']; ?></td>
        <?php } ?>
        <td class="td_mngsmall">
            <input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $row['subject']); ?>">
            <input type="hidden" name="link[]" value="<?php echo $link; ?>">
            <button type="button" class="add_select btn btn_03"><span class="sound_only"><?php echo $row['subject']; ?> </span>선택</button>
        </td>
    </tr>

<?php }//for($i=0; $row=sql_fetch_array($result); $i++) ?>

    </tbody>
    </table>
	<div style="padding:10px;">
		<p>* <strong style="color:red;">빨간색</strong>의 제목은 이미 메뉴에 연결되어 경우를 의미합니다.</p>
	</div>
</div>

<div class="btn_win02 btn_win">
    <button type="button" class="btn_02 btn" onclick="window.close();">창닫기</button>
</div>

<?php } else { ?>
	<?php
	if($type == 'it_type'){
	$it_type_arr = array('히트상품','추천상품','신상품','인기상품','할인상품');	
	?>
	
	
	<div class="tbl_head01 tbl_wrap">
		<table>
		<thead>
		<tr>
			<th scope="col">상품유형</th>
			<th scope="col">선택</th>
		</tr>
		</thead>
		<tbody>
	<?php 
		for($i=1;$i<=5;$i++){
			$type_subj = $it_type_arr[$i-1];
			$type_link = G5_SHOP_URL.'/listtype.php?type='.$i;
			
			$chk_link = str_replace(G5_URL,"",$type_link);
			$has_link = (in_array($chk_link,$sch_me_link)) ? 'style="color:red;"' : '';
	?>
		<tr>
			<td <?=$has_link?>><?php echo $type_subj; ?></td>
			<td class="td_mngsmall">
				<input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $type_subj); ?>">
				<input type="hidden" name="link[]" value="<?php echo $type_link; ?>">
				<button type="button" class="add_select btn btn_03"><span class="sound_only"><?php echo $type_subj; ?> </span>선택</button>
			</td>
		</tr>

	<?php }//for($i=1;$i<=5;$i++) ?>

		</tbody>
		</table>
		<div style="padding:10px;">
			<p>* <strong style="color:red;">빨간색</strong>의 제목은 이미 메뉴에 연결되어 경우를 의미합니다.</p>
		</div>
	</div>

	<div class="btn_win02 btn_win">
		<button type="button" class="btn_02 btn" onclick="window.close();">창닫기</button>
	</div>
	
	<?php }else{ ?>
		<div class="tbl_frm01 tbl_wrap">
			<table>
			<colgroup>
				<col class="grid_2">
				<col>
			</colgroup>
			<tbody>
			<tr>
				<th scope="row" style="width:50px;text-align:center;"><label for="me_name">메뉴<strong class="sound_only"> 필수</strong></label></th>
				<td><input type="text" name="me_name" id="me_name" required class="frm_input required"></td>
			</tr>
			<tr>
				<th scope="row" style="width:50px;text-align:center;"><label for="me_link">링크<strong class="sound_only"> 필수</strong></label></th>
				<td>
					<?php echo help('링크는 http(s)://를 포함해서 입력해 주세요.'); ?>
					<input type="text" name="me_link" id="me_link" size="50" required class="frm_input full_input required">
				</td>
			</tr>
			</tbody>
			</table>
		</div>

		<div class="btn_win02 btn_win">
			<button type="button" id="add_manual" class="btn_submit btn">추가</button>
			<button type="button" class="btn_02 btn" onclick="window.close();">창닫기</button>
		</div>
	<?php } ?>
<?php } ?>
```
### adm/_z01/_adm/menu_form.php
```php
<?php
$sub_menu = "100290";
include_once('./_common.php');

if (!$is_manager)
    alert('접근권한이 없습니다.');

$g5['title'] = '메뉴 추가';
include_once(G5_PATH.'/head.sub.php');

// [메뉴추가] 버튼 클릭 시 1차 최상위 코드 생성
if($new == 'new' || !$code) {
	$depth = 1;
	$code = base_convert(substr($code,0, 2), 36, 10);
	$code += 36;
	$code = base_convert($code, 10, 36);
	$me_code = $code;

}
// [추가] 버튼 클릭 시 해당 메뉴의 맨 하단 코드 생성
else {
	$depth = strlen($code)/2+1;
	$code_last = substr($me_code_last, $depth*2-2,2);
	$me_code = base_convert(substr($code_last,0, 2), 36, 10);
	$me_code += 36;
	$me_code = $code.base_convert($me_code, 10, 36);
}
//echo $depth.'<br>';
// echo $code.'<br>';
// echo substr($code,0, 2).'<br>';
//echo $me_code.' 해당 그룹 마지막 me_code<br>';

// 들여쓰기 padding-left
$me_padding_left = 5+($depth-1)*15 .'px';
?>

<style>
#menu_frm .tbl_frm01 td{border-top:1px solid #e6e6e6 !important;}
#menu_frm h1{font-weight:600;}
#menu_frm .btn_win02.btn_win{text-align:center;}
#menu_frm .btn_win02.btn_win .btn_submit{float:none;height:30px;line-height:30px;position:relative;top:5px;}
#menu_frm .btn_win02.btn_win .btn_02{position:relative;height:30px;line-height:30px;top:5px;}
#menu_frm .add_select{height:30px;line-height:30px;}
</style>
<div id="menu_frm" class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="fmenuform" id="fmenuform" class="new_win_con">

    <div class="new_win_desc">
        <label for="me_type">대상선택</label>
        <select name="me_type" id="me_type">
            <option value="">직접입력</option>
            <option value="board">게시판</option>
			<?php if(defined('G5_COMMUNITY_USE')&&!G5_COMMUNITY_USE) { ?>
            <option value="shop">쇼핑카테고리</option>
            <option value="it_type">쇼핑상품유형</option>
			<?php } ?>
            <option value="group">게시판그룹</option>
            <option value="content">내용관리</option>
        </select>
    </div>

    <div id="menu_result"></div>

    </form>

</div>

<script>
$(function() {
    $("#menu_result").load(
        "./menu_form_search.php"
    );

    function link_checks_all_chage(){

        var $links = $(opener.document).find("#menulist input[name='me_link[]']"),
            $o_link = $(".td_mng input[name='link[]']"),
            hrefs = [],
            menu_exist = false;
           
        if( $links.length ){
            $links.each(function( index ) {
                hrefs.push( $(this).val() );
            });

            $o_link.each(function( index ) {
                if( $.inArray( $(this).val(), hrefs ) != -1 ){
                    $(this).closest("tr").find("td:eq( 0 )").addClass("exist_menu_link");
                    menu_exist = true;
                }
            });
        }

        if( menu_exist ){
            $(".menu_exists_tip").show();
        } else {
            $(".menu_exists_tip").hide();
        }
    }

    function menu_result_change( type ){
        
        var dfd = new $.Deferred();

        $("#menu_result").empty().load(
            "./menu_form_search.php",
            { type : type },
            function(){
                dfd.resolve('Finished');
            }
        );

        return dfd.promise();
    }

    $("#me_type").on("change", function() {
        var type = $(this).val();

        var promise = menu_result_change( type );

        promise.done(function(message) {
            link_checks_all_chage(type);
        });

    });

    $(document).on("click", "#add_manual", function() {
        var me_name = $.trim($("#me_name").val());
        var me_link = $.trim($("#me_link").val());

        add_menu_list(me_name, me_link, "<?=$code?>", "<?=$me_code_last?>");
    });

    $(document).on("click", ".add_select", function() {
        var me_name = $.trim($(this).siblings("input[name='subject[]']").val());
        var me_link = $.trim($(this).siblings("input[name='link[]']").val());

        add_menu_list(me_name, me_link, "<?=$code?>", "<?=$me_code_last?>");
    });
});

function add_menu_list(name, link, code, me_code_last)
{
    var $menulist = $(".tbl_head01", opener.document);
    var ms = new Date().getTime();
    var me_code = "<?=$me_code?>";
    var sub_menu_class;
    var me_depth = me_code.length/2 - 1;
    <?php if($new == 'new') { ?>
    sub_menu_class = " class=\"td_category depth_"+me_depth+"\"";
    <?php } else { ?>
    sub_menu_class = " class=\"td_category depth_"+me_depth+"\"";
    <?php } ?>

    var list = "<tr class=\"menu_list menu_group_<?=$code?> ui-sortable-handle\" me_code=\"<?=$me_code?>\" data-id=\"\" data-depth=\""+me_depth+"\" data-code=\"<?=$me_code?>\">";
    list += "<td class=\"td_idx\"></td>";
    list += "<td class=\"td_depth w-[60px] text-center\">";
    list += "<a href=\"#\" alt=\"상위단계로\">◀</a> | <a href=\"#\" alt=\"하위단계로\">▶</a>";
    list += "</td>";
    list += "<td"+sub_menu_class+">";
    list += "<label for=\"me_name_"+ms+"\"  class=\"sound_only2\">메뉴<strong class=\"sound_only2\"> 필수</strong></label>";
    list += "<input type=\"hidden\" name=\"code[]\" value=\"<?php echo $code; ?>\">";
    list += "<input type=\"hidden\" name=\"depth[]\" value=\"<?php echo $depth; ?>\">";
    list += "<input type=\"text\" name=\"me_name[]\" value=\""+name+"\" id=\"me_name_"+ms+"\" required class=\"required tbl_input full_input\">";
    list += "</td>";
    list += "<td class=\"td_code w-[80px]\">";
    list += "<label for=\"me_code_"+me_code+"\"  class=\"sound_only\">순서코드</label>";
    list += "<input type=\"text\" name=\"me_code[]\" readonly value=\""+me_code+"\" id=\"me_code_"+ms+"\" class=\"tbl_input readonly\">";
    list += "</td>";
    list += "<td class=\"w-[400px]\">";
    list += "<label for=\"me_link_"+ms+"\"  class=\"sound_only\">링크<strong class=\"sound_only\"> 필수</strong></label>";
    list += "<input type=\"text\" name=\"me_link[]\" value=\""+link+"\" id=\"me_link_"+ms+"\" required class=\"required tbl_input full_input\">";
    list += "</td>";
    list += "<td class=\"w-[100px]\">";
    list += "<label for=\"me_target_"+ms+"\"  class=\"sound_only\">새창</label>";
    list += "<select name=\"me_target[]\" id=\"me_target_"+ms+"\">";
    list += "<option value=\"self\">사용안함</option>";
    list += "<option value=\"blank\">사용함</option>";
    list += "</select>";
    list += "</td>";
    list += "<td class=\"w-[100px]\">";
    list += "<label for=\"me_use_"+ms+"\"  class=\"sound_only\">PC사용</label>";
    list += "<select name=\"me_use[]\" id=\"me_use_"+ms+"\">";
    list += "<option value=\"1\">사용함</option>";
    list += "<option value=\"0\">사용안함</option>";
    list += "</select>";
    list += "</td>";
    list += "<td class=\"w-[100px]\">";
    list += "<label for=\"me_mobile_use_"+ms+"\"  class=\"sound_only\">모바일사용</label>";
    list += "<select name=\"me_mobile_use[]\" id=\"me_mobile_use_"+ms+"\">";
    list += "<option value=\"1\">사용함</option>";
    list += "<option value=\"0\">사용안함</option>";
    list += "</select>";
    list += "</td>";
    list += "<td class=\"td_mng w-[100px]\">";
    list += "<div class=\"flex gap-1 justify-center\">";
    <?php if(strlen($me_code) < 6) { ?>
    list += "<button type=\"button\" class=\"btn_add_submenu btn_03\">추가</button>";
    <?php } ?>
    list += "<button type=\"button\" class=\"btn_del_menu btn_02\">삭제</button>";
    list += "</div>";
    list += "</td>";
    list += "</tr>";

    // 메뉴 삽입 위치
	var $menu_last = null;
    
    if(me_code_last) {
        $menu_last = $menulist.find("tr[me_code="+me_code_last+"]");
	}
    else {
        $menu_last = $menulist.find("tr.menu_list:last");
	}

	// 리스트 항목이 한개라도 있으면 그룹의 마지막 부분에 삽입
	if($menu_last.size() > 0) {
        $menu_last.after(list);
    }
	// 리스트 항목이 없으면 새로운 항목 한개를 생성
	else {
        if($menulist.find("#empty_menu_list").size() > 0)
            $menulist.find("#empty_menu_list").remove();

        $menulist.find("table tbody").append(list);
    }

    // 테이블 리스트 라인 색상 전체 변경
	// $menulist.find("tr.menu_list").each(function(index) {
    //     $(this).removeClass("bg0 bg1")
    //         .addClass("bg"+(index % 2));
    // });

    window.close();
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
```
### adm/_z01/_adm/menu_list_update.php
```php
<?php
$sub_menu = "100290";
include_once('./_common.php');

check_demo();

if (!$is_manager)
    alert('접근권한이 없습니다.');

check_admin_token();

// print_r2($_POST);exit;
// 이전 메뉴정보 삭제
$sql = " delete from {$g5['menu_table']} ";
sql_query($sql);

$group_code = null;
$primary_code = null;
$count = count($_POST['code']);

//g5_menu 테이블에 기존 레코드가 한 개도 없으면 무조건 시퀀스는 1부터 시작한다.
dbtable_sequence_reset($g5['menu_table']);
for ($i=0; $i<$count; $i++)
{
    $_POST = array_map_deep('trim', $_POST);

    $code    = $_POST['code'][$i];
    $me_code    = $_POST['me_code'][$i];
    $me_name = $_POST['me_name'][$i];
//    $me_link = $_POST['me_link'][$i];
    $me_link = str_replace(G5_URL,"",$_POST['me_link'][$i]);
    // $depth    = $_POST['depth'][$i];

    if(!$code || !$me_name || !$me_link)
        continue;

	
	$sql_common .= $comma."( '$me_code', '$me_name', '$me_link', '{$_POST['me_target'][$i]}', '{$_POST['me_order'][$i]}', '{$_POST['me_use'][$i]}', '{$_POST['me_mobile_use'][$i]}' )";
	$comma = ' , ';
}

// MySQL 서버간 동기화 속도가 php 속도를 못 따라가므로 sql 문장을 한개로 만들어서 업데이트합니다.
$sql = " 	INSERT INTO {$g5['menu_table']}
				( me_code, me_name, me_link, me_target, me_order, me_use, me_mobile_use )
			VALUES {$sql_common}
";
// echo $sql;
// exit;
sql_query($sql);


// 모든 관련 캐시 파일 삭제 (for문장)
if ($handle = opendir(G5_DATA_PATH."/cache/")) {
	while ('' != ($file = readdir($handle))) {
		if ($file != '..' && $file != '.') {
				$filename = basename($file);
				if ('' != strstr($filename, 'navi-')) {
					@unlink(G5_DATA_PATH."/cache/".$filename);
				}
		}
	}
	closedir($handle); // 디렉토리 핸들 해제
}
// contents.php 삭제
@unlink(G5_DATA_PATH."/cache/contents.php");

goto_url('./menu_list.php');
```
### adm/_z01/_adm/menu_list.php
```php
<?php
$sub_menu = "100290";
include_once('./_common.php');

if (!$is_manager)
    alert('접근권한이 없습니다.');

// 메뉴테이블 생성
if( !isset($g5['menu_table']) ){
    die('<meta charset="utf-8">dbconfig.php 파일에 <strong>$g5[\'menu_table\'] = G5_TABLE_PREFIX.\'menu\';</strong> 를 추가해 주세요.');
}

if(!sql_query(" DESCRIBE {$g5['menu_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['menu_table']}` (
                  `me_id` int(11) NOT NULL AUTO_INCREMENT,
                  `me_code` varchar(255) NOT NULL DEFAULT '',
                  `me_name` varchar(255) NOT NULL DEFAULT '',
                  `me_link` varchar(255) NOT NULL DEFAULT '',
                  `me_target` varchar(255) NOT NULL DEFAULT '0',
                  `me_order` int(11) NOT NULL DEFAULT '0',
                  `me_use` tinyint(4) NOT NULL DEFAULT '0',
                  `me_mobile_use` tinyint(4) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`me_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
}
$sql = " select * from {$g5['menu_table']} order by me_code ";
$result = sql_query($sql);

$g5['title'] = "메뉴설정";
include(G5_ADMIN_PATH.'/admin.head.php');

$colspan = 9;
?>
<div class="local_desc01 local_desc">
    <p>각 메뉴의 라인을 <strong>상하방향</strong>으로 <strong>드래그&드롭</strong>하여 <strong>나열순서</strong>를 변경할 수 있습니다.</p>
    <p><strong>◀ | ▶</strong> 아이콘을 클릭하여 메뉴의 <strong>트리구조</strong>를 변경할 수 있습니다.</p>
    <p><strong>주의!</strong> 메뉴설정 작업 후 반드시 <strong>확인</strong>을 누르셔야 저장됩니다.</p>
</div>

<form name="fmenulist" id="fmenulist" method="post" action="./menu_list_update.php" onsubmit="return fmenulist_submit(this);">
<input type="hidden" name="token" value="">
<div id="menulist" class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">트리설정</th>
        <th scope="col">메뉴</th>
        <th scope="col">순서코드</th>
        <th scope="col">링크</th>
        <th scope="col">새창</th>
        <th scope="col">PC사용</th>
        <th scope="col">모바일사용</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $bg = 'bg'.($i%2);
        $sub_menu_class = '';
        if(strlen($row['me_code']) == 4) {
            $sub_menu_class = ' sub_menu_class';
            $sub_menu_info = '<span class="sound_only">'.$row['me_name'].'의 서브</span>';
            $sub_menu_ico = '<span class="sub_menu_ico"></span>';
        }

        $search  = array('"', "'");
        $replace = array('&#034;', '&#039;');
        $me_name = str_replace($search, $replace, $row['me_name']);

		// 들여쓰기 padding-left
		$row['me_padding_left'] = 5+(strlen($row['me_code'])/2-1)*15 .'px';
        // depth 
        $row['me_depth'] = strlen($row['me_code'])/2 - 1;
		
		// 링크 수정
		$row['me_link'] = (substr($row['me_link'],0,1)=='/' && !preg_match("/http/i",$row['me_link'])) ? G5_URL.$row['me_link'] : bpwg_set_http($row['me_link']);
		$slen = 2;
        if(strlen($row['me_code']) == 4) {
            $slen = 2;
        }
        else if(strlen($row['me_code']) == 6) {
            $slen = 4;
        }
    ?>
    <tr class="menu_list menu_group_<?=substr($row['me_code'], 0, $slen)?>" me_code="<?=$row['me_code']?>" data-id="<?=$row['me_id']?>" data-depth="<?=$row['me_depth']?>" data-code="<?=$row['me_code']?>">
        <td class="td_idx"><?=$row['me_id']?></td>
        <td class="td_depth w-[60px] text-center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
        <td class="td_category depth_<?=$row['me_depth']?>">
            <input type="hidden" name="code[]" value="<?php echo substr($row['me_code'], 0, 2) ?>">
            <input type="hidden" name="depth[]" value="<?php echo strlen($row['me_code'])/2 ?>">
            <label for="me_name_<?php echo $i; ?>" class="sound_only2"><?php echo $sub_menu_info; ?> 메뉴<strong class="sound_only2"> 필수</strong></label>
            <input type="text" name="me_name[]" value="<?php echo $me_name; ?>" id="me_name_<?php echo $i; ?>" required class="required tbl_input full_input">
        </td>
        <td class="td_code w-[80px]">
            <label for="me_code_<?php echo $i; ?>" class="sound_only">순서코드</label>
            <input type="text" name="me_code[]" readonly value="<?php echo $row['me_code'] ?>" id="me_code_<?php echo $i; ?>" class="tbl_input readonly">
        </td>
        <td class="w-[400px]">
            <label for="me_link_<?php echo $i; ?>" class="sound_only2">링크<strong class="sound_only2"> 필수</strong></label>
            <input type="text" name="me_link[]" value="<?php echo $row['me_link'] ?>" id="me_link_<?php echo $i; ?>" required class="required tbl_input full_input">
        </td>
        <td class="w-[100px]">
            <label for="me_target_<?php echo $i; ?>" class="sound_only">새창</label>
            <select name="me_target[]" id="me_target_<?php echo $i; ?>">
                <option value="self"<?php echo get_selected($row['me_target'], 'self', true); ?>>사용안함</option>
                <option value="blank"<?php echo get_selected($row['me_target'], 'blank', true); ?>>사용함</option>
            </select>
        </td>
        <td class="w-[100px]">
            <label for="me_use_<?php echo $i; ?>" class="sound_only">PC사용</label>
            <select name="me_use[]" id="me_use_<?php echo $i; ?>">
                <option value="1"<?php echo get_selected($row['me_use'], '1', true); ?>>사용함</option>
                <option value="0"<?php echo get_selected($row['me_use'], '0', true); ?>>사용안함</option>
            </select>
        </td>
        <td class="w-[100px]">
            <label for="me_mobile_use_<?php echo $i; ?>" class="sound_only">모바일사용</label>
            <select name="me_mobile_use[]" id="me_mobile_use_<?php echo $i; ?>">
                <option value="1"<?php echo get_selected($row['me_mobile_use'], '1', true); ?>>사용함</option>
                <option value="0"<?php echo get_selected($row['me_mobile_use'], '0', true); ?>>사용안함</option>
            </select>
        </td>
        <td class="td_mng w-[100px]">
            <div class="flex gap-1 justify-center">
                <?php if(strlen($row['me_code']) < 6) { ?>
                <button type="button" class="btn_add_submenu btn_03">추가</button>
                <?php } ?>
                <button type="button" class="btn_del_menu btn_02">삭제</button>
            </div>
        </td>
    </tr>
    <?php
    }

    if ($i==0)
        echo '<tr id="empty_menu_list"><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <button type="button" onclick="return add_menu();" class="btn btn_02">메뉴추가<span class="sound_only"> 새창</span></button>
    <input type="submit" name="act_button" value="확인" class="btn_submit btn ">
</div>

</form>



<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/_adm/session_file_delete.php
```php
<?php
$sub_menu = "100800";
include_once("./_common.php");

if (!$is_manager)
    alert("접근권한이 없습니다.", G5_URL);

$g5['title'] = "세션파일 일괄삭제";
include_once(G5_ADMIN_PATH."/admin.head.php");
?>

<div class="local_desc02 local_desc">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    </p>
</div>

    <?php
    flush();

    $list_tag_st = "";
    $list_tag_end = "";
    if (!$dir=@opendir(G5_DATA_PATH.'/session')) {
      echo "<p>세션 디렉토리를 열지못했습니다.</p>";
    } else {
        $list_tag_st = "<ul class=\"session_del\">\n<li>완료됨</li>\n";
        $list_tag_end = "</ul>\n";
    }

    $cnt=0;
    echo $list_tag_st;
    while($file=readdir($dir)) {

        if (!strstr($file,'sess_')) continue;
        if (strpos($file,'sess_')!=0) continue;

        $session_file = G5_DATA_PATH.'/session/'.$file;

        if (!$atime=@fileatime($session_file)) {
            continue;
        }
        if (time() > $atime + (3600 * 6)) {  // 지난시간을 초로 계산해서 적어주시면 됩니다. default : 6시간전
            $cnt++;
            $return = unlink($session_file);
            //echo "<script>document.getElementById('ct').innerHTML += '{$session_file}<br/>';</script>\n";
            echo "<li>{$session_file}</li>\n";

            flush();

            if ($cnt%10==0)
                //echo "<script>document.getElementById('ct').innerHTML = '';</script>\n";
                echo "\n";
        }
    }
    echo $list_tag_end;
    echo '<div class="local_desc01 local_desc"><p><strong>세션데이터 '.$cnt.'건 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>'.PHP_EOL;
?>

<?php
include_once(G5_ADMIN_PATH."/admin.tail.php");
```
### adm/_z01/_adm/_shop_admin/_common.php
```php
<?php
define('G5_IS_ADMIN', true);
include_once ('../../../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

run_event('admin_common');
```
### adm/_z01/_adm/_shop_admin/ajax.ca_id.php
```php
<?php
include_once('./_common.php');

$ca_id = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
if (preg_match("/[^0-9a-z]/i", $ca_id)) {
    die("{\"error\":\"업종코드는 영문자 숫자 만 입력 가능합니다.\"}");
}

$sql = " SELECT name FROM {$g5['shop_categories_table']} WHERE category_id = '{$ca_id}' ";
$row = sql_fetch_pg($sql);
if (isset($row['name']) && $row['name']) {
    $ca_name = addslashes($row['name']);
    die("{\"error\":\"이미 등록된 업종코드 입니다.\\n\\n업종명 : {$ca_name}\"}");
}

die("{\"error\":\"\"}"); // 정상;
```
### adm/_z01/_adm/_shop_admin/categoryform.php
```php
<?php
$sub_menu = '920700';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "w");

$ca_id = isset($_GET['category_id']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['category_id']) : '';
$ca = array(
    'name'=>'',
    'description'=>'',
    'sort_order'=>0,
    'use_yn'=>'Y',
    'cert_use_yn'=>'N',
    'adult_use_yn'=>'N',
    'img_url'=>'',
    'img2_url'=>'',
);

$sql_common = " from {$g5['shop_categories_table']} ";


if ($w == "")
{
    if (($is_admin != 'super' && !$ca_id) || ($member['mb_level'] < 9 && !$ca_id))
        alert("최고관리자만 1단계 분류를 추가할 수 있습니다.");

    $len = strlen($ca_id);
    if ($len == 6) //($len == 10)
        alert("분류를 더 이상 추가할 수 없습니다.\\n\\n3단계 업종까지만 가능합니다."); //alert("분류를 더 이상 추가할 수 없습니다.\\n\\n5단계 업종까지만 가능합니다.");

    $len2 = $len + 1;

    $sql = " SELECT MAX(SUBSTRING(category_id FROM {$len2} FOR 2)) AS max_subid
                FROM {$g5['shop_categories_table']}
            WHERE SUBSTRING(category_id FROM 1 FOR {$len}) = '{$ca_id}' ";
    $row = sql_fetch_pg($sql);

    $subid = base_convert((string)$row['max_subid'], 36, 10);
    $subid += 36;
    if ($subid >= 36 * 36)
    {
        //alert("분류를 더 이상 추가할 수 없습니다.");
        // 빈상태로
        $subid = "  ";
    }
    $subid = base_convert($subid, 10, 36);
    $subid = substr("00" . $subid, -2);
    $subid = $ca_id . $subid;

    $sublen = strlen($subid);

    if ($ca_id) // 2단계이상 분류
    {
        $sql = " SELECT * FROM {$g5['shop_categories_table']} WHERE category_id = '$ca_id' ";
        $ca = sql_fetch_pg($sql);
        $html_title = $ca['name'] . " 하위업종추가";
        $ca['name'] = "";
    }
    else // 1단계 분류
    {
        $html_title = "1단계업종추가";
        $ca['use_yn'] = 'Y';
    }

    $cert_use_y = '';
    $cert_use_n = 'checked="checked"';
    $adult_use_y = '';
    $adult_use_n = 'checked="checked"';
}
else if ($w == "u")
{
    $sql = " SELECT * FROM {$g5['shop_categories_table']} WHERE category_id = '$ca_id' ";
    
    $ca = sql_fetch_pg($sql);
    if (! (isset($ca['category_id']) && $ca['category_id']))
        alert("자료가 없습니다.");

    $html_title = $ca['name'] . " 수정";
    $ca['name'] = get_text($ca['name']);

    $cert_use_y = ($ca['cert_use_yn'] == 'Y') ? 'checked="checked"' : '';
    $cert_use_n = ($ca['cert_use_yn'] != 'Y') ? 'checked="checked"' : '';
    $adult_use_y = ($ca['adult_use_yn'] == 'Y') ? 'checked="checked"' : '';
    $adult_use_n = ($ca['adult_use_yn'] != 'Y') ? 'checked="checked"' : '';
}

$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');



//caicon파일 추출 ###########################################################
$sql = " SELECT * FROM {$g5['dain_file_table']}
WHERE fle_db_tbl = 'set' AND fle_type = 'category' AND fle_db_idx = 'caicon' ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sql,1);
//echo $rs->num_rows;echo "<br>";
$fvc['cac_f_arr'] = array();
$fvc['cac_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$fvc['cac_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array($rs);$i++) {
    //등록 이미지 섬네일 생성
    $row2['thumb'] = '';
	if(strpos($row2['fle_mime_type'],'image') !== false) { // 'image' 문자열이 포함되어 있으면 섬네일 생성
		$thumbf = thumbnail($row2['fle_name'],G5_DATA_PATH.$row2['fle_path'],G5_DATA_PATH.$row2['fle_path'],$thumb_wd,$thumb_ht,false,false,'center');
        $thumbf_url = G5_DATA_URL.$row2['fle_path'].'/'.$thumbf;
		$row2['thumb_url'] = $thumbf_url;
        $row2['thumb'] = '<img src="'.$thumbf_url.'" alt="'.$row2['fle_name_orig'].'" style="margin-left:20px;border:1px solid #ddd;"><br>&nbsp;&nbsp;&nbsp;&nbsp;<span>'.$row2['fle_width'].' X '.$row2['fle_height'].'</span>'.PHP_EOL;
	}
    $file_down_del = (is_file(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_Z_URL.'/lib/download.php?file_fullpath='.urlencode(G5_DATA_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label><br>'.$row2['thumb']:''.PHP_EOL;
    @array_push($fvc['fvc_f_arr'],array('file'=>$file_down_del));
    @array_push($fvc['fvc_fidxs'],$row2['fle_idx']);
}




$pg_anchor ='<ul class="anchor">
<li><a href="#anc_scatefrm_basic">필수입력</a></li>
<li><a href="#anc_cf_icon">아이콘이미지</a></li>';
if ($w == 'u') $pg_anchor .= '<li><a href="#frm_etc">기타설정</a></li>';
$pg_anchor .= '</ul>';


?>

<form name="fcategoryform" action="./categoryformupdate.php" onsubmit="return fcategoryformcheck(this);" method="post" enctype="multipart/form-data">

<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<section id="anc_scatefrm_basic">
    <h2 class="h2_frm">필수입력</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>업종 추가 필수입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="category_id">분류코드</label></th>
            <td>
            <?php if ($w == "") { ?>
                <?php echo help("자동으로 보여지는 업종코드를 사용하시길 권해드리지만 직접 입력한 값으로도 사용할 수 있습니다.\n업종코드는 나중에 수정이 되지 않으므로 신중하게 결정하여 사용하십시오.\n\n업종코드는 2자리씩 10자리를 사용하여 5단계를 표현할 수 있습니다.\n0~z까지 입력이 가능하며 한 업종당 최대 1296가지를 표현할 수 있습니다.\n그러므로 총 3656158440062976가지의 업종을 사용할 수 있습니다."); ?>
                <input type="text" name="category_id" value="<?php echo $subid; ?>" id="category_id" required class="required frm_input" size="<?php echo $sublen; ?>" maxlength="<?php echo $sublen; ?>">
            <?php } else { ?>
                <input type="hidden" name="category_id" value="<?php echo $ca['category_id']; ?>">
                <span class="frm_ca_id"><?php echo $ca['category_id']; ?></span>
                <a href="./categoryform.php?category_id=<?php echo $ca_id; ?>&amp;<?php echo $qstr; ?>" class="btn_frmline">하위업종 추가</a>
            <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="name">업종명</label></th>
            <td><input type="text" name="name" value="<?php echo $ca['name']; ?>" id="name" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="sort_order">출력순서</label></th>
            <td>
                <?php echo help("숫자가 작을 수록 상위에 출력됩니다. 음수 입력도 가능하며 입력 가능 범위는 -2147483648 부터 2147483647 까지입니다.\n<b>입력하지 않으면 자동으로 출력됩니다.</b>"); ?>
                <input type="text" name="sort_order" value="<?php echo $ca['sort_order']; ?>" id="sort_order" class="frm_input" size="12">
            </td>
        </tr>
        <tr>
            <th scope="row">본인확인 체크</th>
            <td>
                <input type="radio" name="cert_use_yn" value="1" id="cert_use_yes" <?=$cert_use_y?>>
                <label for="cert_use_yes">사용함</label>&nbsp;&nbsp;&nbsp;
                <input type="radio" name="cert_use_yn" value="0" id="ca_cert_use_no" <?=$cert_use_n?>>
                <label for="ca_cert_use_no">사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row">성인인증 체크</th>
            <td>
                <input type="radio" name="adult_use_yn" value="1" id="adult_use_yes" <?=$adult_use_y?>>
                <label for="adult_use_yes">사용함</label>&nbsp;&nbsp;&nbsp;
                <input type="radio" name="adult_use_yn" value="0" id="adult_use_no" <?=$adult_use_n?>>
                <label for="adult_use_no">사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="use_yn">판매가능</label></th>
            <td>
                <?php echo help("일시적으로 예약 중단하시려면 체크 해제하십시오.\n체크 해제하시면 가맹점 출력을 하지 않으며, 예약도 받지 않습니다."); ?>
                <input type="checkbox" name="use_yn" <?php echo ($ca['use_yn'] == 'Y') ? 'checked="checked"' : ""; ?> value="1" id="use_yn">
                예
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_cf_icon">
    <h2 class="h2_frm">아이콘이미지</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>아이콘이미지 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">하위분류</th>
            <td>
                <?php echo help("이 분류의 코드가 10 이라면 10 으로 시작하는 하위분류의 설정값을 이 분류와 동일하게 설정합니다.\n<strong>이 작업은 실행 후 복구할 수 없습니다.</strong>"); ?>
                <label for="sub_category">이 분류의 하위분류 설정을, 이 분류와 동일하게 일괄수정</label>
                <input type="checkbox" name="sub_category" value="1" id="sub_category" onclick="if (this.checked) if (confirm('이 분류에 속한 하위 분류의 속성을 똑같이 변경합니다.\n\n이 작업은 되돌릴 방법이 없습니다.\n\n그래도 변경하시겠습니까?')) return ; this.checked = false;">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<?php if ($w == "u") { ?>
<section id="frm_etc">
    <h2 class="h2_frm">기타설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>업종 추가 기타설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">하위분류</th>
            <td>
                <?php echo help("이 분류의 코드가 10 이라면 10 으로 시작하는 하위분류의 설정값을 이 분류와 동일하게 설정합니다.\n<strong>이 작업은 실행 후 복구할 수 없습니다.</strong>"); ?>
                <label for="sub_category">이 분류의 하위분류 설정을, 이 분류와 동일하게 일괄수정</label>
                <input type="checkbox" name="sub_category" value="1" id="sub_category" onclick="if (this.checked) if (confirm('이 분류에 속한 하위 분류의 속성을 똑같이 변경합니다.\n\n이 작업은 되돌릴 방법이 없습니다.\n\n그래도 변경하시겠습니까?')) return ; this.checked = false;">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<?php } ?>
<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    <a href="./categorylist.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
</div>
</form>

<script>

function fcategoryformcheck(f)
{
    if (f.w.value == "") {
        var error = "";
        $.ajax({
            url: "./ajax.ca_id.php",
            type: "POST",
            data: {
                "category_id": f.category_id.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                error = data.error;
            }
        });

        if (error) {
            alert(error);
            return false;
        }
    }


    return true;
}


/*document.fcategoryform.ca_name.focus(); 포커스 해제*/
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/_adm/_shop_admin/categoryformupdate.php
```php
<?php
$sub_menu = '920700';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$ca_include_head = isset($_POST['ca_include_head']) ? trim($_POST['ca_include_head']) : '';
$ca_include_tail = isset($_POST['ca_include_tail']) ? trim($_POST['ca_include_tail']) : '';
$ca_id = isset($_REQUEST['ca_id']) ? preg_replace('/[^0-9a-z]/i', '', $_REQUEST['ca_id']) : '';

if( ! $ca_id ){
    alert('', G5_SHOP_URL);
}

if ($file = $ca_include_head) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if (! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || !preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("상단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if ($file = $ca_include_tail) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if (! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || !preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("하단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if( $ca_id ){
    $sql = " select * from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
    $ca = sql_fetch($sql);

    if ($ca && ($ca['ca_include_head'] !== $ca_include_head || $ca['ca_include_tail'] !== $ca_include_tail) && function_exists('get_admin_captcha_by') && get_admin_captcha_by()){
        include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

        if (!chk_captcha()) {
            alert('자동등록방지 숫자가 틀렸습니다.');
        }
    }
}

$check_str_keys = array(
'ca_order'=>'int',
'ca_img_width'=>'int',
'ca_img_height'=>'int',
'ca_name'=>'str',
'ca_mb_id'=>'str',
'ca_nocoupon'=>'str',
'ca_mobile_skin_dir'=>'str',
'ca_skin'=>'str',
'ca_mobile_skin'=>'str',
'ca_list_mod'=>'int',
'ca_list_row'=>'int',
'ca_mobile_img_width'=>'int',
'ca_mobile_img_height'=>'int',
'ca_mobile_list_mod'=>'int',
'ca_mobile_list_row'=>'int',
'ca_sell_email'=>'str',
'ca_use'=>'int',
'ca_stock_qty'=>'int',
'ca_explan_html'=>'int',
'ca_cert_use'=>'int',
'ca_adult_use'=>'int',
'ca_skin_dir'=>'str'
);

for($i=0;$i<=10;$i++){
    $check_str_keys['ca_'.$i.'_subj'] = 'str';
    $check_str_keys['ca_'.$i] = 'str';
}

foreach( $check_str_keys as $key=>$val ){
    if( $val === 'int' ){
        $value = isset($_POST[$key]) ? (int) $_POST[$key] : 0;
    } else {
        $value = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
    }
    $$key = $_POST[$key] = $value;
}

$ca_head_html = isset($_POST['ca_head_html']) ? $_POST['ca_head_html'] : '';
$ca_tail_html = isset($_POST['ca_tail_html']) ? $_POST['ca_tail_html'] : '';
$ca_mobile_head_html = isset($_POST['ca_mobile_head_html']) ? $_POST['ca_mobile_head_html'] : '';
$ca_mobile_tail_html = isset($_POST['ca_mobile_tail_html']) ? $_POST['ca_mobile_tail_html'] : '';

if(!is_include_path_check($ca_include_head, 1)) {
    alert('상단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

if(!is_include_path_check($ca_include_tail, 1)) {
    alert('하단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

$check_keys = array('ca_skin_dir', 'ca_mobile_skin_dir', 'ca_skin', 'ca_mobile_skin'); 

foreach( $check_keys as $key ){
    if( isset($$key) && preg_match('#\.+(\/|\\\)#', $$key) ){
        alert('스킨명 또는 경로에 포함시킬수 없는 문자열이 있습니다.');
    }
}

if( function_exists('filter_input_include_path') ){
    $ca_include_head = filter_input_include_path($ca_include_head);
    $ca_include_tail = filter_input_include_path($ca_include_tail);
}

if ($w == "u" || $w == "d")
    check_demo();

auth_check_menu($auth, $sub_menu, "d");

check_admin_token();

if ($w == 'd' && $is_admin != 'super')
    alert("최고관리자만 분류를 삭제할 수 있습니다.");

if ($w == "" || $w == "u")
{
    if ($ca_mb_id)
    {
        $sql = " select mb_id from {$g5['member_table']} where mb_id = '$ca_mb_id' ";
        $row = sql_fetch($sql);
        if (!$row['mb_id'])
            alert("\'$ca_mb_id\' 은(는) 존재하는 회원아이디가 아닙니다.");
    }
}

if( $ca_skin && ! is_include_path_check($ca_skin) ){
    alert('오류 : 데이터폴더가 포함된 path 를 포함할수 없습니다.');
}

$sql_common = " ca_order                = '$ca_order',
                ca_skin_dir             = '$ca_skin_dir',
                ca_mobile_skin_dir      = '$ca_mobile_skin_dir',
                ca_skin                 = '$ca_skin',
                ca_mobile_skin          = '$ca_mobile_skin',
                ca_img_width            = '$ca_img_width',
                ca_img_height           = '$ca_img_height',
				ca_list_mod             = '$ca_list_mod',
				ca_list_row             = '$ca_list_row',
                ca_mobile_img_width     = '$ca_mobile_img_width',
                ca_mobile_img_height    = '$ca_mobile_img_height',
				ca_mobile_list_mod      = '$ca_mobile_list_mod',
                ca_mobile_list_row      = '$ca_mobile_list_row',
                ca_sell_email           = '$ca_sell_email',
                ca_use                  = '$ca_use',
                ca_stock_qty            = '$ca_stock_qty',
                ca_explan_html          = '$ca_explan_html',
                ca_head_html            = '$ca_head_html',
                ca_tail_html            = '$ca_tail_html',
                ca_mobile_head_html     = '$ca_mobile_head_html',
                ca_mobile_tail_html     = '$ca_mobile_tail_html',
                ca_include_head         = '$ca_include_head',
                ca_include_tail         = '$ca_include_tail',
                ca_mb_id                = '$ca_mb_id',
                ca_cert_use             = '$ca_cert_use',
                ca_adult_use            = '$ca_adult_use',
                ca_nocoupon             = '$ca_nocoupon',
                ca_1_subj               = '$ca_1_subj',
                ca_2_subj               = '$ca_2_subj',
                ca_3_subj               = '$ca_3_subj',
                ca_4_subj               = '$ca_4_subj',
                ca_5_subj               = '$ca_5_subj',
                ca_6_subj               = '$ca_6_subj',
                ca_7_subj               = '$ca_7_subj',
                ca_8_subj               = '$ca_8_subj',
                ca_9_subj               = '$ca_9_subj',
                ca_10_subj              = '$ca_10_subj',
                ca_1                    = '$ca_1',
                ca_2                    = '$ca_2',
                ca_3                    = '$ca_3',
                ca_4                    = '$ca_4',
                ca_5                    = '$ca_5',
                ca_6                    = '$ca_6',
                ca_7                    = '$ca_7',
                ca_8                    = '$ca_8',
                ca_9                    = '$ca_9',
                ca_10                   = '$ca_10' ";


if ($w == "")
{
    if (!trim($ca_id))
        alert("분류 코드가 없으므로 분류를 추가하실 수 없습니다.");

    // 소문자로 변환
    $ca_id = strtolower($ca_id);

    $sql = " insert {$g5['g5_shop_category_table']}
                set ca_id   = '$ca_id',
                    ca_name = '$ca_name',
                    $sql_common ";
    sql_query($sql);
    run_event('shop_admin_category_created', $ca_id);
}
else if ($w == "u")
{
    $sql = " update {$g5['g5_shop_category_table']}
                set ca_name = '$ca_name',
                    $sql_common
              where ca_id = '$ca_id' ";
    sql_query($sql);

    // 하위분류를 똑같은 설정으로 반영
    if (isset($_POST['sub_category']) && $_POST['sub_category']) {
        $len = strlen($ca_id);
        $sql = " update {$g5['g5_shop_category_table']}
                    set $sql_common
                  where SUBSTRING(ca_id,1,$len) = '$ca_id' ";
        if ($is_admin != 'super')
            $sql .= " and ca_mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }
    run_event('shop_admin_category_updated', $ca_id);
}
else if ($w == "d")
{
    // 분류의 길이
    $len = strlen($ca_id);

    $sql = " select COUNT(*) as cnt from {$g5['g5_shop_category_table']}
              where SUBSTRING(ca_id,1,$len) = '$ca_id'
                and ca_id <> '$ca_id' ";
    $row = sql_fetch($sql);
    if ($row['cnt'] > 0)
        alert("이 분류에 속한 하위 분류가 있으므로 삭제 할 수 없습니다.\\n\\n하위분류를 우선 삭제하여 주십시오.");

    $str = $comma = "";
    $sql = " select it_id from {$g5['g5_shop_item_table']} where ca_id = '$ca_id' ";
    $result = sql_query($sql);
    $i=0;
    while ($row = sql_fetch_array($result))
    {
        $i++;
        if ($i % 10 == 0) $str .= "\\n";
        $str .= "$comma{$row['it_id']}";
        $comma = " , ";
    }

    if ($str)
        alert("이 분류와 관련된 상품이 총 {$i} 건 존재하므로 상품을 삭제한 후 분류를 삭제하여 주십시오.\\n\\n$str");

    // 분류 삭제
    $sql = " delete from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
    sql_query($sql);
    run_event('shop_admin_category_deleted', $ca_id);
}

if(function_exists('get_admin_captcha_by'))
    get_admin_captcha_by('remove');

if ($w == "" || $w == "u")
{
    goto_url("./categoryform.php?w=u&amp;ca_id=$ca_id&amp;$qstr");
} else {
    goto_url("./categorylist.php?$qstr");
}
```
### adm/_z01/_adm/_shop_admin/categorylist.php
```php
<?php
$sub_menu = '920700';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '업종관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$where = " where ";
$sql_search = "";

$sfl = in_array($sfl, array('name', 'category_id')) ? $sfl : '';

if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if (isset($save_stx) && $save_stx && ($save_stx != $stx))
        $page = 1;
}

$sql_common = " FROM {$g5['shop_categories_table']} ";
// if ($is_admin != 'super')
//     $sql_search .= " $where ca_mb_id = '{$member['mb_id']}' ";
$sql_common .= $sql_search;


// 테이블의 전체 레코드수만 얻음
$sql = " SELECT COUNT(*) AS cnt " . $sql_common;

$row = sql_fetch_pg($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst)
{
    $sst  = "category_id";
    $sod = "ASC";
}
$sql_order = "ORDER BY $sst $sod";

// 출력할 레코드를 얻음
$sql  = " SELECT *
             $sql_common
             $sql_order
             LIMIT $rows OFFSET $from_record ";

$result = sql_query_pg($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">생성된  분류 수</span><span class="ov_num">  <?php echo number_format($total_count); ?>개</span></span>
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="name"<?php echo get_selected($sfl, "name", true); ?>>업종명</option>
    <option value="category_id"<?php echo get_selected($sfl, "category_id", true); ?>>업종코드</option>
</select>

<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="fcategorylist" method="post" action="./categorylistupdate.php" autocomplete="off">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div id="sct" class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="2"><?php echo subject_sort_link("category_id"); ?>업종코드</a></th>
        <th scope="col" id="sct_cate" rowspan="2">업종명</th>
        <th scope="col" id="sct_amount">가맹점갯수</th>
        <th scope="col" id="sct_hpcert">본인인증여부</th>
        <th scope="col" id="sct_hpcert">성인인증여부</th>
        <th scope="col" id="sct_sell"><?php echo subject_sort_link("use_yn"); ?>활성화여부</a></th>
        <th scope="col" rowspan="2">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    $s_add = $s_vie = $s_upd = $s_del = '';
    for ($i=0; $row=sql_fetch_array_pg($result); $i++)
    {
        $level = strlen($row['category_id']) / 2 - 1;
        $p_ca_name = '';

        if ($level > 0) {
            $class = 'class="name_lbl"'; // 2단 이상 분류의 label 에 스타일 부여 - 지운아빠 2013-04-02
            // 상위단계의 분류명
            $p_ca_id = substr($row['category_id'], 0, $level*2);
            $sql = " SELECT name FROM {$g5['shop_categories_table']} where category_id = '$p_ca_id' ";
            $temp = sql_fetch_pg($sql);
            $p_ca_name = $temp['name'].'의하위';
        } else {
            $class = '';
        }

        $s_level = '<div><label for="ca_name_'.$i.'" '.$class.'><span class="sound_only">'.$p_ca_name.''.($level+1).'단 분류</span></label></div>';
        $s_level_input_size = 25 - $level *2; // 하위 분류일 수록 입력칸 넓이 작아짐 - 지운아빠 2013-04-02

        if ($level+2 < 6) $s_add = '<a href="./categoryform.php?category_id='.$row['category_id'].'&amp;'.$qstr.'" class="btn btn_03">추가</a> '; // 분류는 5단계까지만 가능
        else $s_add = '';
        $s_upd = '<a href="./categoryform.php?w=u&amp;category_id='.$row['category_id'].'&amp;'.$qstr.'" class="btn btn_02"><span class="sound_only">'.get_text($row['name']).' </span>수정</a> ';

        if ($is_admin == 'super' || $member['mb_level'] >= 9) {
            $s_del = '<a href="./categoryformupdate.php?w=d&amp;category_id='.$row['category_id'].'&amp;'.$qstr.'" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only">'.get_text($row['name']).' </span>삭제</a> ';
        }
        // 해당 분류에 속한 가맹점의 수(한 개의 가맹점이 여러 개의 업종에 속할 수 있으므로, 가맹점 수가 아닌 업종에 속한 가맹점 수를 구함)
        $sql1 = " SELECT COUNT(*) AS cnt FROM {$g5['shop_category_relation_table']}
                      WHERE category_id = '{$row['category_id']}' ";
        // echo $sql1."<br>";continue;
        $row1 = sql_fetch_pg($sql1);

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_code">
            <input type="hidden" name="category_id[<?php echo $i; ?>]" value="<?php echo $row['category_id']; ?>">
            <a href="<?php echo 'javascript:void(0);';//shop_category_url($row['category_id']); ?>"><?php echo $row['category_id']; ?></a>
        </td>
        <td headers="sct_cate" class="sct_name<?php echo $level; ?>"><?php echo $s_level; ?> <input type="text" name="name[<?php echo $i; ?>]" value="<?php echo get_text($row['name']); ?>" id="name_<?php echo $i; ?>" required class="tbl_input full_input required"></td>
        <td headers="sct_amount" class="td_amount"><?php echo $row1['cnt']; ?></td>
        <td headers="sct_hpcert" class="td_possible">
            <input type="checkbox" name="cert_use_yn[<?php echo $i; ?>]" value="1" id="cert_use_yes<?php echo $i; ?>" <?php if($row['cert_use_yn'] == 'Y') echo 'checked="checked"'; ?>>
            <label for="cert_use_yes<?php echo $i; ?>">사용</label>
        </td>
        <td headers="sct_adultcert" class="td_possible">
            <input type="checkbox" name="adult_use_yn[<?php echo $i; ?>]" value="1" id="adult_use_yes<?php echo $i; ?>" <?php if($row['adult_use_yn'] == 'Y') echo 'checked="checked"'; ?>>
            <label for="adult_use_yes<?php echo $i; ?>">사용</label>
        </td>
        <td headers="sct_sell" class="td_possible">
            <input type="checkbox" name="use_yn[<?php echo $i; ?>]" value="1" id="use_yn<?php echo $i; ?>" <?php echo ($row['use_yn'] == 'Y' ? "checked" : ""); ?>>
            <label for="use_yn<?php echo $i; ?>">활성화</label>
        </td>
        <td class="td_mng td_mng_s">
            <?php echo $s_add; ?>
            <?php echo $s_vie; ?>
            <?php echo $s_upd; ?>
            <?php echo $s_del; ?>
        </td>
    </tr>
    <?php }
    if ($i == 0) echo "<tr><td colspan=\"7\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" value="일괄수정" class="btn_02 btn">

    <?php if ($is_admin == 'super' || $member['mb_level'] >= 9) {?>
    <a href="./categoryform.php" id="cate_add" class="btn btn_01">분류 추가</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $("select.skin_dir").on("change", function() {
        var type = "";
        var dir = $(this).val();
        if(!dir)
            return false;

        var id = $(this).attr("id");
        var $sel = $(this).siblings("select");
        var sval = $sel.find("option:selected").val();

        if(id.search("mobile") > -1)
            type = "mobile";

        $sel.load(
            "./ajax.skinfile.php",
            { dir : dir, type : type, sval: sval }
        );
    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
```
### adm/_z01/_adm/_shop_admin/categorylistupdate.php
```php
<?php
$sub_menu = '920700';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$post_ca_id_count = (isset($_POST['category_id']) && is_array($_POST['category_id'])) ? count($_POST['category_id']) : 0;

// print_r2($_POST);exit;

for ($i=0; $i<$post_ca_id_count; $i++)
{
    $p_ca_name = is_array($_POST['name']) ? strip_tags(clean_xss_attributes($_POST['name'][$i])) : '';
    
    $_POST['cert_use_yn'][$i] = isset($_POST['cert_use_yn'][$i]) && $_POST['cert_use_yn'][$i] ? 'Y' : 'N';
    $_POST['adult_use_yn'][$i] = isset($_POST['adult_use_yn'][$i]) && $_POST['adult_use_yn'][$i] ? 'Y' : 'N';
    $_POST['use_yn'][$i] = isset($_POST['use_yn'][$i]) && $_POST['use_yn'][$i] ? 'Y' : 'N';
    
    $posts = array();

    $check_keys = array('category_id', 'use_yn', 'cert_use_yn', 'adult_use_yn');

    foreach($check_keys as $key){
        $posts[$key] = (isset($_POST[$key]) && isset($_POST[$key][$i])) ? $_POST[$key][$i] : '';
    }
    
    $sql = " UPDATE {$g5['shop_categories_table']}
                set name             = '".$p_ca_name."',
                    use_yn           = '".sql_real_escape_string(strip_tags($_POST['use_yn'][$i]))."',
                    cert_use_yn      = '".sql_real_escape_string(strip_tags($_POST['cert_use_yn'][$i]))."',
                    adult_use_yn     = '".sql_real_escape_string(strip_tags($_POST['adult_use_yn'][$i]))."'
              where category_id = '".sql_real_escape_string($posts['category_id'])."' ";
    // echo $sql . "<br>";continue;
    sql_query_pg($sql);

}
// exit;
goto_url("./categorylist.php?$qstr");
```
### adm/_z01/_adm/_sms_admin/_common.php
```php
<?php
define('G5_IS_ADMIN', true);
include_once ('../../../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

run_event('admin_common');
```
### adm/_z01/_adm/css/menu_list.css
```css
@charset "utf-8";
.tbl_head01 tbody tr:nth-child(even) {background:#fff;}
.tbl_head01 tbody tr{background:#fff;}
```
### adm/_z01/_adm/js/menu_list.js.php
```php
<script>
$(function() {
    //-- 정렬(Sortable) --//
	var fixHelperModified = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index)
		{
			$(this).width($originals.eq(index).width());
		});
		return $helper;
	};
    
    $(".tbl_head01 tbody").sortable({
		cancel: "input, textarea, a, i"
		, helper: fixHelperModified
		, items: "tr:not(.no-data)"
		, placeholder: "tr-placeholder"
		, connectWith: ".tbl_head01 tr:not(.no-data)"
		, stop: function(event, ui) {
            min_depth = 2;
			//alert(ui.item.html());
            const po = (ui.item.prev().attr('data-id') !== undefined)?ui.item.prev():null;//prev object
            const co = ui.item; //current object
            const no = (ui.item.next().attr('data-id') !== undefined)?ui.item.next():null; //next object
            let cdepth = 0; //새로 갱신된 depth
            // 우선 현재객체의 depth_X 클래스를 제거한다.
            co.find('.td_category').removeClass((idx,cls) => {
                return (cls.match(/(^|\s)depth_\S+/g || []).join(' '));
            });
			if(!po){ //맨 위로 이동 했을 경우 ==============================================
                co.attr('data-depth', 0);
                cdepth = 0;
                co.find('.td_category').addClass('depth_0');
            }
            else if(po && no){ //사이범위에서 이동 했을 경우 =================================
                let po_depth = Number(po.attr('data-depth'));
                let no_depth = Number(no.attr('data-depth'));
                
                if(po_depth == no_depth){ //이전과 다음이 같은 depth일때 - 이전과 같은 depth
                    co.attr('data-depth', po_depth);
                    cdepth = po_depth;
                    co.find('.td_category').addClass('depth_'+po_depth);
                }
                else if(po_depth < no_depth){ // 이전보다 다음 depth가 작을때 - 다음과 같은 depth
                    co.attr('data-depth', no_depth);
                    cdepth = no_depth;
                    co.find('.td_category').addClass('depth_'+no_depth);
                }
                else if(po_depth > no_depth){ // 이전보다 다음 depth가 클때 - 이전과 같은 depth
                    co.attr('data-depth', po_depth);
                    cdepth = po_depth;
                    co.find('.td_category').addClass('depth_'+po_depth);
                }
            }
            else{ //맨 아래로 이동 했을 경우 ===============================================
                const po_depth = Number(po.attr('data-depth'));
                co.attr('data-depth', po_depth);
                cdepth = po_depth;
                co.find('.td_category').addClass('depth_'+po_depth);
            }

            if(cdepth < 2){ // 계층이 0 또는 1 일경우 '추가'버튼이 필요하다.
                if(!co.find('.btn_add_submenu').length){
                    $('<button type="button" class="btn_add_submenu btn_03">추가</button>').prependTo(co.find('.td_mng').find('div'));
                }
            }
            else{ // 계층이 2 이상일경우 '추가'버튼이 필요없다.
                if(co.find('.btn_add_submenu').length){
                    co.find('.btn_add_submenu').remove();
                }
            }

            resortCode();
		}
	});

    $(document).on('click','.td_depth a',function(e) {
		e.preventDefault();
        let cur_tr = $(this).closest('tr');
        const prev_depth = $(this).closest('tr').prev().attr('data-id') == undefined?null:Number($(this).closest('tr').prev().attr('data-depth'));
        const next_depth = $(this).closest('tr').next().attr('data-id') == undefined?null:Number($(this).closest('tr').next().attr('data-depth'));
        let cur_depth = Number($(this).closest('tr').attr('data-depth'));
        const direct = ($(this).index() == 0)?'left':'right';
        if(prev_depth == null){
            alert('첫번째 메뉴는 좌우로 이동할 수 없습니다.');
            return false;
        }
        else if(cur_depth == 0 && direct == 'left'){
            alert('1차메뉴(최상위메뉴)는 좌측로 이동할 수 없습니다.');
            return false;
        }
        else if(cur_depth == 2 && direct == 'right'){
            alert('3차메뉴(하위메뉴)는 우측으로 이동할 수 없습니다.');
            return false;
        }
        else if(prev_depth < cur_depth && cur_depth < next_depth){
            alert('상위메뉴와 하위메뉴가 존재하면 이동할 수 없습니다.');
            return false;
        }
        else if(prev_depth < cur_depth && direct == 'right'){
            alert('상위메뉴 보다 2단계 하위로 이동할 수 없습니다.');
            return false;
        }
        else if(cur_depth < next_depth && direct == 'left'){
            alert('하위메뉴 보다 2단계 상위로 이동할 수 없습니다.');
            return false;
        }
        
        cur_depth = (direct == 'left')?cur_depth - 1:cur_depth + 1;

        if(cur_depth < 2){
            if(!cur_tr.find('.btn_add_submenu').length){
                $('<button type="button" class="btn_add_submenu btn_03">추가</button>').prependTo(cur_tr.find('.td_mng').find('div'));
            }
        }else{
            if(cur_tr.find('.btn_add_submenu').length){
                cur_tr.find('.btn_add_submenu').remove();
            }
        }

        // 우선 현재객체의 depth_X 클래스를 제거한다.
        cur_tr.find('.td_category').removeClass((idx,cls) => {
            return (cls.match(/(^|\s)depth_\S+/g || []).join(' '));
        });
        cur_tr.attr('data-depth', cur_depth);
        cur_tr.find('.td_category').addClass('depth_'+cur_depth);
        resortCode();
    });

	$(document).on("click", ".btn_add_submenu", function() {
        var code = $(this).closest("tr").find("input[name='me_code[]']").val();
		
		// 해당 메뉴 그룹의 맨 마지막 me_code를 같이 던져줘야 한다. (생성 dom 추가 위치 & 코드)
		var me_code_last = code;
		$('.tbl_head01 tbody tr').each(function(i,v){
			// console.log( code +'='+ $(this).attr('me_code') + ' 길이=' + code.length + ' 잘린코드=' + $(this).attr('me_code').substring(0,code.length) );
			if( code == $(this).attr('me_code').substring(0,code.length) ) {
				me_code_last = $(this).attr('me_code');
			}
		});
		
        add_submenu(code, me_code_last);
    });

	$(document).on("click", ".btn_del_menu", function() {
        if(!confirm("메뉴를 삭제하시겠습니까?"))
            return false;
		
		var tr_me_code = $(this).closest("tr").attr('me_code');

		$('.tbl_head01 tr[me_code^='+tr_me_code+']').remove();

        if($(".tbl_head01 tr.menu_list").size() < 1) {
            var list = "<tr id=\"empty_menu_list\"><td colspan=\"<?php echo $colspan; ?>\" class=\"empty_table\">자료가 없습니다.</td></tr>\n";
            $(".tbl_head01 table tbody").append(list);
        }
    });
});
// console.log(makeCode());
// console.log(nextCode('Z0ZZ','n'));
// console.log(str2.slice(0,-2));
// console.log(str2);
// console.log(str);
// console.log(nextCode('10','n'));
// 메뉴 코드 재설정
function resortCode(){
    $(".tbl_head01 tr.menu_list").each(function(idx) {
        const prev_depth = ($(this).prev().attr('data-depth') !== undefined)?Number($(this).prev().attr('data-depth')):null;
        const prev_code = ($(this).prev().attr('me_code') !== undefined)?$(this).prev().attr('me_code'):null;
        let cur_depth = Number($(this).attr('data-depth'));
        let cur_tr = $(this);
        let cur_code = '';
        
        if(idx == 0){
            cur_code = nextMenuCode(); // '10'으로 셋팅
            cur_tr.find('.td_category').removeClass((idx,cls) => {
                return (cls.match(/(^|\s)depth_\S+/g || []).join(' '));
            });
            cur_depth = 0;
            cur_tr.attr('data-depth', cur_depth);
            cur_tr.find('.td_category').addClass('depth_'+cur_depth);
        }
        else{
            // 만약 상위메뉴가 현재메뉴의 2단계 상위일경우 현재메뉴를 1단계 상위로 변경
            if(prev_depth + 2 == cur_depth) cur_depth = cur_depth - 1; 
            
            if(prev_depth == cur_depth){ // 이전과 같은 계층일때
                // console.log('prev_code='+prev_code);
                cur_code = nextMenuCode(prev_code, 'n');
            }
            else if(prev_depth < cur_depth){ // 이전보다 하위계층일때
                cur_code = nextMenuCode(prev_code, 'c');
            }
            else if(prev_depth > cur_depth){ // 이전보다 상위계층일때
                if(cur_depth == 0){
                    cur_code = nextMenuCode(prev_code.substring(0,2), 'n');
                } else if(cur_depth == 1){
                    cur_code = nextMenuCode(prev_code.substring(0,4), 'n');
                }
            }
        }
        
        const code = (cur_depth == 0 || cur_depth == 1)?cur_code.substring(0,2):cur_code.substring(0,4);

        cur_tr.removeClass((id,cls) => { // menu_group_로 시작하는 클래스를 제거한다.
            return (cls.match(/(^|\s)menu_group_\S+/g || []).join(' '));
        });
        cur_tr.addClass('menu_group_'+code); // 새로운 코드로 클래스를 추가한다.
        cur_tr.attr('me_code', cur_code); // 새로운 코드를 me_code 속성 변경한다.
        cur_tr.attr('data-depth', cur_depth); // 새로운 depth를 data-depth 속성 변경한다.
        cur_tr.find('.td_category').removeClass((id,cls) => { // depth_로 시작하는 클래스를 제거한다.
            return (cls.match(/(^|\s)depth_\S+/g || []).join(' '));
        });
        cur_tr.find('.td_category').addClass('depth_' + cur_depth); // 새로운 depth로 클래스를 추가한다.
        cur_tr.find("input[name='code[]']").val(code); // 새로운 코드를 input[name='code[]'] 속성 변경한다.
        cur_tr.find("input[name='me_code[]']").val(cur_code); // 새로운 코드를 input[name='me_code[]'] 속성 변경한다.
        cur_tr.find("input[name='depth[]']").val(cur_depth); // 새로운 depth를 input[name='depth[]'] 속성 변경한다.
        cur_tr.find("label[for^='me_name_']").attr('for','me_name_' + cur_depth); // 라벨명을 변경한다.

        if(cur_depth < 2){
            if(!cur_tr.find('.btn_add_submenu').length){
                $('<button type="button" class="btn_add_submenu btn_03">추가</button>').prependTo(cur_tr.find('.td_mng').find('div'));
            }
        }else{
            if(cur_tr.find('.btn_add_submenu').length){
                cur_tr.find('.btn_add_submenu').remove();
            }
        }
    });
}

// console.log(nextMenuCode('10','c'));

function nextMenuCode(code = '', type = '') { // type = c:자식코드, n:다음코드
    let cd = (code != '')?code:'10';
    let result = '';
    if (type == '') {
        result = cd;
        return result;
    }
    
    const carr = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
    let fixcode = cd.slice(0,-2); // 끝에서 2번째 문자까지 자르고 나머지 문자열 저장
    // fixcode = fixcode.slice(0,-2);
    // carr.indexOf('Z')
    // cd.charAt(code.length - 2) // 끝에서 2번째 문자
    // cd.slice(0,-2) // 끝에서 2번째 문자까지 자르고 나머지 문자열
    let cur_front = '';
    let cur_rear = '';
    let next_front = '';
    let next_rear = '';

    if (type == 'c') { // 자식코드
        result = cd+'10';
    }
    else if(type == 'n') { // 다음코드
        cur_front = cd.charAt(cd.length - 2); // 코드의 끝에서 2번째 문자
        cur_rear = cd.charAt(cd.length - 1); // 코드의 끝에서 1번째 문자
        
        if(cur_front == 'Z'){
            next_front = 'Z';
            if(cur_rear == 'Z'){
                return false;
            }
            else{
                next_rear = carr[carr.indexOf(cur_rear) + 1];
            }
        }
        else{
            next_front = carr[carr.indexOf(cur_front) + 1];
            next_rear = '0';
        }
        result = fixcode + next_front + next_rear;
    }
    return result;
}

function add_menu()
{
    var max_code = base_convert(0, 10, 36);
    $(".tbl_head01 tr.menu_list").each(function() {
        var me_code = $(this).find("input[name='code[]']").val().substr(0, 2);
        if(max_code < me_code)
            max_code = me_code;
    });

    var url = "./menu_form.php?code="+max_code+"&new=new";
    window.open(url, "add_menu", "left=100,top=100,width=550,height=650,scrollbars=yes,resizable=yes");
    return false;
}

function add_submenu(code, me_code_last){
    var url = "./menu_form.php?code="+code+'&me_code_last='+me_code_last;
    window.open(url, "add_menu", "left=100,top=100,width=550,height=650,scrollbars=yes,resizable=yes");
    return false;
}

function base_convert(number, frombase, tobase) {
  //  discuss at: http://phpjs.org/functions/base_convert/
  // original by: Philippe Baumann
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  //   example 1: base_convert('A37334', 16, 2);
  //   returns 1: '101000110111001100110100'

  return parseInt(number + '', frombase | 0)
    .toString(tobase | 0);
}

function fmenulist_submit(f){

    var me_links = document.getElementsByName('me_link[]');
    var reg = /^javascript/; 

	for (i=0; i<me_links.length; i++){
        
	    if( reg.test(me_links[i].value) ){ 
        
            alert('링크에 자바스크립트문을 입력할수 없습니다.');
            me_links[i].focus();
            return false;
        }
    }

    return true;
}
</script>
```
### adm/_z01/_replace/replace.php
```php

```
### adm/_z01/_sql/content.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$cont['cont_karr'] = array();
$cont['cont_varr'] = array();
$cont['cont_option'] = '';
if(sql_query(" DESCRIBE {$g5['content_table']} ", false)){
	$consql = " SELECT co_id, co_subject FROM {$g5['content_table']} ORDER BY co_subject ";
	$conres = sql_query($consql,1);
	for($i=0;$conrow=sql_fetch_array($conres);$i++){
		$cont['cont_karr'][$conrow['co_id']] = $conrow['co_subject'];
		$cont['cont_varr'][$conrow['co_subject']] = $conrow['co_id'];
		$cont['cont_option'] .= '<option value="'.$conrow['co_id'].'">'.$conrow['co_subject'].'</option>';
	}
	unset($consql);
	unset($conres);
	unset($i);
	unset($conrow);
}
```
### adm/_z01/_sql/set_com.php
```php
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
```
### adm/_z01/_sql/set_conf.php
```php
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
```
### adm/_z01/_sql/set_menu.php
```php
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
```
### adm/_z01/_sql/term_department.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$dpt_sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor
    SELECT
        trm_idx,
        trm_name,
        trm_name2,
        trm_name::TEXT AS path,
        trm_idx::TEXT AS idxs,
        trm_desc,
        trm_left,
        trm_right,
        0::BIGINT AS trm_depth,  -- 명시적으로 BIGINT로 변경
        trm_status
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0
      AND trm_category = 'department'
      AND trm_status = 'ok'

    UNION ALL

    -- Recursive
    SELECT
        t.trm_idx,
        t.trm_name,
        t.trm_name2,
        (tp.path || ' > ' || t.trm_name)::TEXT AS path,
        (tp.idxs || ',' || t.trm_idx::TEXT)::TEXT AS idxs,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
        (
            SELECT COUNT(*)::BIGINT  -- BIGINT로 명시
            FROM {$g5['term_table']} parent
            WHERE parent.trm_left < t.trm_left
              AND parent.trm_right > t.trm_right
              AND parent.trm_category = 'department'
              AND parent.trm_status = 'ok'
        ) AS trm_depth,
        t.trm_status
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = 'department'
      AND t.trm_status = 'ok'
      AND tp.trm_status = 'ok'
)
SELECT
    trm_idx, trm_name, trm_name2, path, idxs,
    trm_desc, trm_left, trm_right, trm_depth
FROM TermPaths
WHERE trm_status = 'ok'
ORDER BY trm_left;
";

$dpt_res = sql_query_pg($dpt_sql);

$department_arr = array();
$department_opt = '';
// if($dpt_res->num_rows > 0){
for($i=0;$row=sql_fetch_array_pg($dpt_res);$i++){
    $department_arr[$row['trm_idx']] = $row['trm_name'];
    $department_opt .= '<option value="'.$row['trm_idx'].'">'.$row['path'].'</option>';
}
// }
unset($dpt_sql);
unset($dpt_res);
```
### adm/_z01/_sql/term_rank.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$rank_sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor
    SELECT
        trm_idx,
        trm_name,
        trm_name2,
        trm_name::TEXT AS path,
        trm_idx::TEXT AS idxs,
        trm_desc,
        trm_left,
        trm_right,
        0::BIGINT AS trm_depth,  -- 명시적으로 BIGINT로 변경
        trm_status
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0
      AND trm_category = 'rank'
      AND trm_status = 'ok'

    UNION ALL

    -- Recursive
    SELECT
        t.trm_idx,
        t.trm_name,
        t.trm_name2,
        (tp.path || ' > ' || t.trm_name)::TEXT AS path,
        (tp.idxs || ',' || t.trm_idx::TEXT)::TEXT AS idxs,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
        (
            SELECT COUNT(*)::BIGINT  -- BIGINT로 명시
            FROM {$g5['term_table']} parent
            WHERE parent.trm_left < t.trm_left
              AND parent.trm_right > t.trm_right
              AND parent.trm_category = 'rank'
              AND parent.trm_status = 'ok'
        ) AS trm_depth,
        t.trm_status
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = 'rank'
      AND t.trm_status = 'ok'
      AND tp.trm_status = 'ok'
)
SELECT
    trm_idx, trm_name, trm_name2, path, idxs,
    trm_desc, trm_left, trm_right, trm_depth
FROM TermPaths
WHERE trm_status = 'ok'
ORDER BY trm_left;
";

$rank_res = sql_query_pg($rank_sql,1);

$rank_arr = array();
$rank_opt = '';
// if($rank_res->num_rows > 0){
for($i=0;$row=sql_fetch_array_pg($rank_res);$i++){
    $rank_arr[$row['trm_idx']] = $row['trm_name'];
    $rank_opt .= '<option value="'.$row['trm_idx'].'">'.$row['path'].'</option>';
}
// }
unset($rank_sql);
unset($rank_res);
```
### adm/_z01/_sql/term_role.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$role_sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor
    SELECT
        trm_idx,
        trm_name,
        trm_name2,
        trm_name::TEXT AS path,
        trm_idx::TEXT AS idxs,
        trm_desc,
        trm_left,
        trm_right,
        0::BIGINT AS trm_depth,  -- 명시적으로 BIGINT로 변경
        trm_status
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0
      AND trm_category = 'role'
      AND trm_status = 'ok'

    UNION ALL

    -- Recursive
    SELECT
        t.trm_idx,
        t.trm_name,
        t.trm_name2,
        (tp.path || ' > ' || t.trm_name)::TEXT AS path,
        (tp.idxs || ',' || t.trm_idx::TEXT)::TEXT AS idxs,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
        (
            SELECT COUNT(*)::BIGINT  -- BIGINT로 명시
            FROM {$g5['term_table']} parent
            WHERE parent.trm_left < t.trm_left
              AND parent.trm_right > t.trm_right
              AND parent.trm_category = 'role'
              AND parent.trm_status = 'ok'
        ) AS trm_depth,
        t.trm_status
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = 'role'
      AND t.trm_status = 'ok'
      AND tp.trm_status = 'ok'
)
SELECT
    trm_idx, trm_name, trm_name2, path, idxs,
    trm_desc, trm_left, trm_right, trm_depth
FROM TermPaths
WHERE trm_status = 'ok'
ORDER BY trm_left;
";

$role_res = sql_query_pg($role_sql);

$role_arr = array();
$role_opt = '';
// if($role_res->num_rows > 0){
for($i=0;$row=sql_fetch_array_pg($role_res);$i++){
    $role_arr[$row['trm_idx']] = $row['trm_name'];
    $role_opt .= '<option value="'.$row['trm_idx'].'">'.$row['path'].'</option>';
}
// }
unset($role_sql);
unset($role_res);
```
### adm/_z01/ajax/_common.php
```php
<?php
define('G5_IS_ADMIN', true);
define('G5_IS_Z01', true);
include_once ('../../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_ADMIN_PATH.'/shop_admin/admin.shop.lib.php');

//-- REQUEST 변수 재정의 (변수명이 너무 길어~) --//
foreach($_REQUEST as $key => $value ) {
	${$key} = $value;
}
```
### adm/_z01/ajax/email_check.php
```php
<?php
include_once('./_common.php');

$json = file_get_contents('php://input');

$d = json_decode($json, true);
$mb_email = trim($d['mb_email']);
$mb_id = trim($d['mb_id']);
$w = $d['w'];

$mb_id_where = ($w == 'u') ? " AND mb_id <> '{$mb_id}' " : '';
$mb_no = 0;

if($mb_email){
    $sql = " SELECT mb_no FROM {$g5['member_table']} WHERE mb_email = '{$mb_email}' {$mb_id_where} ";
    $res = sql_fetch($sql);
    $mb_no = $res['mb_no'];
}

$status = 0;

if(!$mb_no){
    $status = 1;
}

echo $status;
```
### adm/_z01/ajax/hp_check.php
```php
<?php
include_once('./_common.php');

$json = file_get_contents('php://input');

$d = json_decode($json, true);
$mb_hp = trim($d['mb_hp']);
$mb_hp = preg_replace("/[^0-9]/", "", $mb_hp); // 숫자만 추출
$mb_id = trim($d['mb_id']);
$w = $d['w'];

$mb_id_where = ($w == 'u') ? " AND mb_id <> '{$mb_id}' " : '';
$mb_no = 0;

if($mb_hp){
    $sql = " SELECT mb_no FROM {$g5['member_table']} WHERE mb_hp = '{$mb_hp}' {$mb_id_where} ";
    $res = sql_fetch($sql);
    $mb_no = $res['mb_no'];
}

$status = 0;

if(!$mb_no){
    $status = 1;
}

echo $status;
```
### adm/_z01/ajax/mb_id_check.php
```php
<?php
include_once('./_common.php');

$mb_id = trim($mb_id);
$mb_no = 0;
if($mb_id){
    $res = sql_fetch(" SELECT mb_no FROM {$g5['member_table']} WHERE mb_id = '{$mb_id}' ");
    $mb_no = $res['mb_no'];
}

$status = 0;

if(!$mb_no){
    $status = 1;
}

echo $status;
```
### adm/_z01/ajax/nick_check.php
```php
<?php
include_once('./_common.php');

$json = file_get_contents('php://input');

$d = json_decode($json, true);

$mb_nick = trim($d['mb_nick']);
$mb_id = trim($d['mb_id']);
$w = $d['w'];

$mb_id_where = ($w == 'u') ? " AND mb_id <> '{$mb_id}' " : '';
$mb_no = 0;

if($mb_nick){
    $sql = " SELECT mb_no FROM {$g5['member_table']} WHERE mb_nick = '{$mb_nick}' {$mb_id_where} ";
    $res = sql_fetch($sql);
    $mb_no = $res['mb_no'];
}

$status = 0;

if(!$mb_no){
    $status = 1;
}

echo $status;
```
### adm/_z01/ajax/term_delete.php
```php
<?php
include_once('./_common.php');

// print_r2($GET);exit;
// echo json_encode($_GET);exit;
//-- 디폴트 상태 (실패) --//
$response = new stdClass();
$response->result=false;

//-- 하위 카테고리(들) 추출
$sub_idxs_fetch = sql_fetch(" SELECT GROUP_CONCAT(DISTINCT cast(terms.trm_idx as char)) trm_idxs
									, GROUP_CONCAT(DISTINCT terms.trm_name) trm_names
								FROM {$g5['term_table']} AS terms,
								        {$g5['term_table']} AS parent,
								        {$g5['term_table']} AS sub_parent,
								        (
									        SELECT terms.trm_idx, terms.trm_name, (COUNT(parent.trm_idx) - 1) AS depth
									        FROM {$g5['term_table']} AS terms,
									        {$g5['term_table']} AS parent
									        WHERE terms.trm_left BETWEEN parent.trm_left AND parent.trm_right
									        AND terms.trm_idx = '".$trm_idx."'
									        GROUP BY terms.trm_idx
									        ORDER BY terms.trm_left
								        )AS sub_tree
								WHERE terms.trm_left BETWEEN parent.trm_left AND parent.trm_right
								        AND terms.trm_left BETWEEN sub_parent.trm_left AND sub_parent.trm_right
								        AND sub_parent.trm_idx = sub_tree.trm_idx
								        AND terms.trm_category = '$category'
								GROUP BY sw
								ORDER BY terms.trm_left
");

//-- 조직구조 삭제인 경우
if ($category == "department") {

	//-- 직원 탈퇴 처리
	// sql_query("UPDATE {$g5['member_table']} mbr INNER JOIN {$g5['term_relation_table']} tmr
	// 				ON mbr.mb_id = tmr.tmr_db_id
	// 					AND tmr.tmr_db_table = 'member'
	// 					AND tmr.trm_idx in (".$sub_idxs_fetch[trm_idxs].")
	// 				SET mbr.mb_leave_date = '".date('Ymd', G5_SERVER_TIME)."'
	// 			");
	
	//-- 교차 테이블에서 레코드 삭제
	// $sql = " DELETE FROM {$g5['term_relation_table']} WHERE tmr_db_table = 'member' AND trm_idx in (".$sub_idxs_fetch[trm_idxs].") ";
	// sql_query($sql);
	
}						


//-- 관련 카테고리 모두 삭제 & left, right 업데이트
sql_query(" SELECT @myLeft := trm_left, @myRight := trm_right, @myWidth := trm_right - trm_left + 1
				FROM {$g5['term_table']}
				WHERE trm_idx = '".$trm_idx."' 
			");
if($delete == 1) {	// 완전 삭제인 경우
	sql_query(" DELETE FROM {$g5['term_table']} WHERE trm_left BETWEEN @myLeft AND @myRight AND trm_category = '$category' ");

	$rs = sql_fetch(" SELECT COUNT(*) AS rows FROM {$g5['term_table']} ");
	if(!$rs['rows']){
		sql_query(" ALTER TABLE {$g5['term_table']} AUTO_INCREMENT = 1 ");
	}
}
else {
	sql_query(" UPDATE {$g5['term_table']} SET trm_status = 'trash' WHERE trm_left BETWEEN @myLeft AND @myRight AND trm_category = '$category' ");
}

sql_query(" UPDATE {$g5['term_table']} SET trm_right = trm_right - @myWidth WHERE trm_right > @myRight AND trm_category = '$category' ");
sql_query(" UPDATE {$g5['term_table']} SET trm_left = trm_left - @myWidth WHERE trm_left > @myRight AND trm_category = '$category' ");




// 캐시 파일 삭제 (초기화)
unlink(G5_DATA_PATH.'/cache/department.php');


echo json_encode($response);
exit;
```
### adm/_z01/css/_adm_modal.css
```css
@charset "utf-8";
/*모달 공통*/
.modal_btn{text-align:center;padding:10px 0 0;}

/*스킨설정모달 스타일*/
#skin_select_modal{position:fixed;left:0;top:0;width:100%;height:100%;z-index:1000;display:none;}
#skin_select_modal #skin_select_tbl{display:table;width:100%;height:100%;}
#skin_select_modal #skin_select_tbl #skin_select_td{position:relative;display:table-cell;width:100%;height:100%;vertical-align:middle;padding:0 100px;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_bg{position:absolute;z-index:0;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);cursor:pointer;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box{position:relative;background:#fff;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box h3#skin_select_title{font-size:1.3em;padding:10px;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box #skin_select_modal_close{position:absolute;top:-45px;right:-45px;width:40px;height:40px;cursor:pointer;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box #skin_select_con{padding:10px;padding-left:0px;padding-top:0px;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box #skin_select_con:after{display:block;visibility:hidden;clear:both;content:"";}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box #skin_select_con .skin_lst{float:left;margin-left:10px;margin-top:10px;cursor:pointer;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box #skin_select_con .skin_lst img{display:block;width:100px;height:auto;border:1px solid #fff;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box #skin_select_con .skin_lst.selected img{border:1px solid #f00;}
#skin_select_modal #skin_select_tbl #skin_select_td #skin_select_box #skin_select_con .skin_lst .skin_name{text-align:center;}

/*콘텐츠 이미지 등록 모달 스타일*/
#confile_reg_modal{position:fixed;left:0;top:0;width:100%;height:100%;z-index:1000;display:none;}
#confile_reg_modal #confile_reg_tbl{display:table;width:100%;height:100%;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td{position:relative;display:table-cell;width:100%;height:100%;vertical-align:middle;text-align:center;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_bg{position:absolute;z-index:0;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);cursor:pointer;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box{position:relative;background:#fff;width:100%;text-align:left;max-width:600px;display:inline-block;padding-bottom:10px;border-radius:5px;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box h3#confile_reg_title{font-size:1.3em;padding:10px;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box #confile_reg_modal_close{position:absolute;top:-45px;right:-45px;width:40px;height:40px;cursor:pointer;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box #confile_reg_con{padding:0px;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box #confile_reg_con:after{display:block;visibility:hidden;clear:both;content:"";}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box #confile_reg_con #cur_img{position:relative;height:140px;text-align:left;background:#efefef;padding:10px;padding-left:210px;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box #confile_reg_con #cur_img #file_box{position:absolute;left:10px;top:10px;width:190px;height:120px;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box #confile_reg_con #cur_img #file_box input[type="file"]{width:100%;}
#confile_reg_modal #confile_reg_tbl #confile_reg_td #confile_reg_box #confile_reg_con form{padding:10px;}

/*옵션/콘텐츠 이미지 변경 모달 스타일*/
#img_change_modal{position:fixed;left:0;top:0;width:100%;height:100%;z-index:1000;display:none;}
#img_change_modal #img_change_tbl{display:table;width:100%;height:100%;}
#img_change_modal #img_change_tbl #img_change_td{position:relative;display:table-cell;width:100%;height:100%;vertical-align:middle;text-align:center;}
#img_change_modal #img_change_tbl #img_change_td #img_change_bg{position:absolute;z-index:0;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);cursor:pointer;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box{position:relative;background:#fff;width:100%;text-align:left;max-width:600px;display:inline-block;padding-bottom:10px;border-radius:5px;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box h3#img_change_title{font-size:1.3em;padding:10px;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box #img_change_modal_close{position:absolute;top:-45px;right:-45px;width:40px;height:40px;cursor:pointer;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box #img_change_con{padding:0px;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box #img_change_con:after{display:block;visibility:hidden;clear:both;content:"";}
#img_change_modal #img_change_tbl #img_change_td #img_change_box #img_change_con #cur_img{position:relative;height:140px;text-align:left;background:#efefef;padding:10px;padding-left:210px;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box #img_change_con #cur_img .cur_img_box{position:absolute;top:5px;left:5px;width:195px;height:135px;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box #img_change_con #cur_img img{position:absolute;left:0px;top:0px;width:100%;height:100%;object-fit:contain;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box #img_change_con form{padding:10px;}
#img_change_modal #img_change_tbl #img_change_td #img_change_box #img_change_con form #file_box{border-bottom:1px solid #dddddd;padding:10px 0;}
```
### adm/_z01/css/_adm_tailwind_utility_class.php
```php
<!-- Utility Class (외부파일로 인쿠르드 불가능하다, 이렇게 동일한 파일내에서 정의해야 한다.) -->
<!-- include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php'); -->
<!-- tailwindcss custom정의 -->
<script>
const brightbg = <?= json_encode($set_conf['set_bright_bg'] ?? '') ?>;
const brightbgfont = <?= json_encode($set_conf['set_bright_font'] ?? '') ?>;
const normalbg = <?= json_encode($set_conf['set_normal_bg'] ?? '') ?>;
const normalbgfont = <?= json_encode($set_conf['set_normal_font'] ?? '') ?>;
const mainbg = <?= json_encode($set_conf['set_main_bg'] ?? '') ?>;
const mainbgfont = <?= json_encode($set_conf['set_main_font'] ?? '') ?>;
const darkbg = <?= json_encode($set_conf['set_dark_bg'] ?? '') ?>;
const darkbgfont = <?= json_encode($set_conf['set_dark_font'] ?? '') ?>;
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                /* 키는 '-'하이픈을 사용할 수 없고, '_'언더바를 사용할 수 있다.
                sans: ['Noto Sans KR', 'Arial', 'sans-serif'],
                notosans: ['Noto Sans KR', 'sans-serif'],
                single: ["Single Day", 'cursive'],
                montserrat: ['Montserrat', 'sans-serif'],
                blackhansans: ['Black Han Sans', 'sans-serif'],
                nanumpen: ['Nanum Pen Script', 'cursive'],
                pretendard: ['Pretendard-Regular'],
                yclover: ['YClover-Bold'],
                */
            },
            colors: {
                /* 키는 '-'하이픈을 사용할 수 없고, '_'언더바를 사용할 수 있다.
                mygreen: {
                    100: '#1abc9c',
                    200: '#2ecc71',
                    300: '#16a085',
                    400: '#27ae60',
                },
                */
                brightbg: brightbg,
                brightbgfont: brightbgfont,
                normalbg: normalbg,
                normalbgfont: normalbgfont,
                mainbg: mainbg,
                mainbgfont: mainbgfont,
                darkbg: darkbg,
                darkbgfont: darkbgfont,
            },
        }
    }
}
</script>
<style type="text/tailwindcss">
@layer utilities {
    /*
    .shadow-box {
        @apply border-2 border-gray-200 shadow-lg rounded-3xl p-5 text-center text-xl;
    }
    .centering{
        @apply flex justify-center items-center;
    }
    */

    .mm-btn { /* 관리자단 컨텐츠영역에서 사용하는 메인버튼 */
        @apply inline-block bg-mainbg !text-mainbgfont leading-[35px] h-[35px] px-4 rounded-md;
    }
}
</style>
```
### adm/_z01/css/_form.css
```css
@charset "utf-8";

/*색상/투명도 설정*/
.color_ul{display:inline-block;position:relative;top:3px;border:1px solid #333;border-radius:4px;overflow:hidden;}
.color_ul:after{display:block;visibility:hidden;clear:both;content:"";}
.color_ul .color_li{float:left;}
.color_ul .color_li1{}
.color_ul .color_li1 input[type="color"]{width:50px;height:23px;}
.color_ul .color_li2{}
.color_ul .color_li2:after{display:block;visibility:hidden;clear:both;content:"";}
.color_ul .color_li2 span{display:block;float:left;}
.color_ul .color_li2 span.color_alpha_ttl{padding:0 5px;}
.color_ul .color_li3{width:30px;}
.color_ul .color_li3 .color_result_bg{height:23px;width:100%;border:1px solid #dddddd;overflow:hidden;background:url(../img/transparent.gif);}
.color_ul .color_li3 .color_result_bg .color_result{width:100%;height:100%;}

/*범위 range*/

/*------------------------------공통---------------------------------*/
.range_span{display:block;position:relative;}
.range_span input{position:relative;top:-1px;}
.range_span .output_span{position:absolute;top:0px;right:0px;}
.range_span .output_span:after{display:block;visibility:hidden;clear:both;content:"";}
.range_span .output_span output{float:left;padding-left:5px;}
.range_span .output_span .unit_span{float:left;padding-left:5px;}
input[type=range]{
	-webkit-appearance:none;/*chrom,safari,opera*/
    border: 1px solid white;/* white fix for FF unable to apply focus style bug  */ 
    width: 100%;/*required for proper track sizing in FF*/
}

/*----------------------chrom,safari,opera---------------------------*/
input[type=range]::-webkit-slider-runnable-track {
	width:100%;height:5px;background:#ddd;border:none;border-radius:3px;
}

input[type=range]::-webkit-slider-thumb {
    -webkit-appearance:none;border:none;height:16px;width:16px;margin-top:-6px;border-radius:50%;background:#3f51b5;
}

input[type=range]:focus {outline:none;}

input[type=range]:focus::-webkit-slider-runnable-track{background:#ccc;}

/*--------------------------firfox------------------------------------*/

input[type=range]::-moz-range-track {
    width:100%;height:5px;background:#ddd;border:none;border-radius:3px;
}

input[type=range]::-moz-range-thumb {
    border:none;height:16px;width:16px;border-radius:50%;background:#3f51b5;
}

/*hide the outline behind the border*/
input[type=range]:-moz-focusring{
    outline:1px solid white;outline-offset:-1px;
}

input[type=range]:focus::-moz-range-track {background:#ccc;}

/*-------------------------------ie10+-------------------------------*/
input[type=range]::-ms-track {
    width: 100%;
    height: 5px;
    /*remove bg colour from the track, we'll use ms-fill-lower and ms-fill-upper instead */
    background: transparent;
    /*leave room for the larger thumb to overflow with a transparent border */
    border-color: transparent;
    border-width: 6px 0;
    /*remove default tick marks*/
    color: transparent;
}
input[type=range]::-ms-fill-lower {
    background: #777;border-radius: 10px;
}
input[type=range]::-ms-fill-upper {
    background: #ddd;border-radius: 10px;
}
input[type=range]::-ms-thumb {
    border:none;height:16px;width:16px;border-radius:50%;background:#3f51b5;
}
input[type=range]:focus::-ms-fill-lower {
    background: #888;
}
input[type=range]:focus::-ms-fill-upper {
    background: #ccc;
}

/*#########################################################*/

input[type="button"]{cursor:pointer;}
input[type="text"],input[type="password"],input[type="url"],input[type="number"]{
	display:inline-block;height:26px;line-height:26px;position:relative;border:1px solid #e1e1e1;padding:0 3px;border-radius:3px;
}
textarea{border:1px solid #e1e1e1;border-radius:3px;width:100%;}
select:focus,input[type="text"]:focus,input[type="password"]:focus,input[type="url"]:focus,textarea:focus{border:1px solid skyblue;}
/*선택박스*/
select{height:26px;line-height:26px;border-radius:3px;}
button{position:relative;top:-1px;background:none;border:0;}
button span{position:relative;top:2px;margin-left:3px;color:#888;}
/*라디오박스*/
/*
<label for="bwgs_status_pending" class="label_radio bwgs_status">
	<input type="radio" id="bwgs_status_pending" name="bwgs_status" value="pending" checked="checked">
	<strong></strong>
	<span>대기</span>
</label>
*/
.label_radio{display:inline-block;cursor:pointer;margin-left:7px;position:relative;top:-2px;}
.label_radio.first_child{margin-left:0 !important;}
.label_radio:after{display:block;visibility:hidden;clear:both;content:"";}
.label_radio input{display:none;}
.label_radio strong{display:block;width:16px;height:16px;background:url(../img/r_off.png) no-repeat center center;background-size:100% 100%;float:left;}
.label_radio input:checked + strong{width:16px;height:16px;background:url(../img/r_on.png) no-repeat center center;background-size:100% 100%;}
.label_radio span{display:block;float:right;height:16px;line-height:16px;padding-left:3px;font-size:1.1em;color:#777;}
.label_radio input:checked + strong + span{color:#000;}

/*체크박스*/
/*
<label for="bwgs_use" class="label_checkbox bwgs_use">
	<input type="checkbox" id="bwgs_use" name="bwgs_use" value="0" checked="checked">
	<strong></strong>
	<span>체크사용</span>
</label>
*/
.label_checkbox{display:inline-block;cursor:pointer;margin-left:7px;position:relative;top:-2px;}
.label_checkbox.first_child{margin-left:0;}
.label_checkbox:after{display:block;visibility:hidden;clear:both;content:"";}
.label_checkbox input{display:none;}
.label_checkbox strong{display:block;width:16px;height:16px;background:url(../img/c_off.png) no-repeat center center;background-size:100% 100%;float:left;}
.label_checkbox input:checked + strong{width:16px;height:16px;background:url(../img/c_on.png) no-repeat center center;background-size:100% 100%;}
.label_checkbox span{display:block;float:right;height:16px;line-height:16px;padding-left:3px;font-size:1.1em;color:#777;}
.label_checkbox input:checked + strong + span{color:#000;}

/*입력박스관련 정보문장*/
/*
<div class="bwg_info_box">
	<p class="bwg_info (iup | idown) bwgs_cd_info">해당입력란 관련 설명</p>
</div>
*/
.tms_help .tms_info_box{position:relative;display:none;}
.tms_help:hover .tms_info_box{display:block;}
.tms_help .tms_info_box .tms_info{position:absolute;left:0px;border:1px solid #dddddd;border-radius:3px;overflow:hidden;padding:5px;line-height:1.2em;}
.tms_help .tms_info_box .tms_info.iup{bottom:0px;box-shadow:3px -3px 6px #eeeeee;}
.tms_help .tms_info_box .tms_info.idown{top:0px;box-shadow:3px 3px 6px #eeeeee;}

/*입력박스관련 정보문장*/
/*
<div class="tms_hint flex gap-6">
    <input type="text" class="" value="">
    <div class="tms_hbox">
        <div class="tms_hcon">
            <?=${'set_'.$set_type}['set_cachetimes_str']?>
        </div>
    </div>
</div>
*/
.tms_hint .tms_hbox{position:relative;}
.tms_hint .tms_hbox .tms_hcon{position:absolute;left:0px;top:0px;width:max-content;height:24px;overflow:hidden;border:1px solid #ddd;background:#e7e9f6;z-index:1;}
.tms_hint:hover .tms_hbox .tms_hcon{height:max-content;z-index:1000;}
.tms_hint .tms_hbox .tms_hcon p{padding:2px 10px;}
```
### adm/_z01/css/adm_add.css
```css
@charset "utf-8";
/* 기존 디폴트.css에서 사용되는 클래스와 동일한 클래스의 버전2를 정의할때 */
.sound_only2 {display:inline-block !important;position:absolute;top:0;left:0;margin:0 !important;padding:0 !important;width:1px !important;height:1px !important;font-size:0;line-height:0;border:0 !important;overflow:hidden !important}

/* menu 메뉴 */
#menulist .sub_menu_class2 {padding-left:50px;background:url('../img/sub_menu_ico2.gif') 5px 15px no-repeat}
```
### adm/_z01/css/adm_common_custom.css
```css
@charset "utf-8";
.require::before{
    content:'*';
    position:relative;
    top:2px;
    margin-left:4px;
    color:red;
}

.readonly{
    background-color:#f0f0f0;
    color:#000;
}

/* 멀티파일업로드 */
input[type="file"]{position:relative;width:250px;height:80px;border-radius:10px;overflow:hidden;cursor:pointer;}
input[type="file"]::before{display:block;content:'';position:absolute;left:0;top:0;width:100%;height:100%;background:#ddd;opacity:1;z-index:3;}
input[type="file"]::after{display:block;content:'파일선택\A(드래그앤드롭 가능)';position:absolute;z-index:4;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;}
.MultiFile-wrap ~ ul{margin-top:10px;}
.MultiFile-wrap ~ ul > li{margin-top:10px;}
.MultiFile-wrap .MultiFile-list{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label{position:relative;padding-left:25px;margin-top:10px;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove{position:absolute;top:0;left:0;font-size:0;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove::after{content:'×';display:block;position:absolute;left:0;top:0;width:20px;height:20px;border:1px solid #ccc;border-radius:50%;font-size:14px;line-height:20px;text-align:center;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span span.MultiFile-label{font-size:14px;border:1px solid #ccc;background:#eee;padding:2px 5px;border-radius:3px;line-height:1.2em;}

/* indent depth 들여쓰기 */
.depth_0{padding-left:0;}
.depth_1{padding-left:30px !important;}
.depth_2{padding-left:60px !important;}
.depth_3{padding-left:90px !important;}
.depth_4{padding-left:120px !important;}
.depth_5{padding-left:150px !important;}

/* 상단 가로방향 탭버튼 */
.top_tab{margin-bottom:10px;}
.top_tab::after{display:block;visibility:hidden;clear:both;content:'';}
.top_tab .a_tab{display:block;float:left;padding:10px 20px;border:1px solid #60718b;margin-left:-1px;}
.top_tab .a_tab:first-child{margin-left:0px;}
.top_tab .focus{background-color:#6f809a;color:#fff;}

/* 타임픽커 스타일 */
.from_time select,
.end_time select{width:100px;}
.from_time i,
.end_time i{font-size:1.2em;cursor:pointer;}


/* 버튼(button) 스타일 */
.btn_primary{padding:0 15px !important;background-color:#3f72af !important;border-color:#3f72af !important;color:#fff !important;}
.btn_primary:hover{background-color:#006aee !important;border-color:#006aee !important;}
.btn_secondary{padding:0 15px !important;background-color:#545b62 !important;border-color:#545b62 !important;color:#fff !important;}
.btn_secondary:hover{background-color:#544a51 !important;border-color:#544a51 !important;}
.btn_success{padding:0 15px !important;background-color:#1e7e34 !important;border-color:#1e7e34 !important;color:#fff !important;}
.btn_success:hover{background-color:#1e6c23 !important;border-color:#1e6c23 !important;}
.btn_danger{padding:0 15px !important;background-color:#bd2232 !important;border-color:#bd2232 !important;color:#fff !important;}
.btn_danger:hover{background-color:#ac1221 !important;border-color:#ac1221 !important;}
.btn_warning{padding:0 15px !important;background-color:#ffc107 !important;border-color:#ffc107 !important;color:#000 !important;}
.btn_warning:hover{background-color:#efc107 !important;border-color:#efc107 !important;}
.btn_info{padding:0 15px !important;background-color:#17a2b8 !important;border-color:#17a2b8 !important;color:#fff !important;}
.btn_info:hover{background-color:#117a8b !important;border-color:#117a8b !important;}
.btn_gray{padding:0 15px !important;background-color:#aaaaaa !important;border-color:#aaaaaa !important;color:#fff !important;}
.btn_gray:hover{background-color:#888888 !important;border-color:#888888 !important;}
.btn_dark{padding:0 15px !important;background-color:#343a40 !important;border-color:#343a40 !important;color:#fff !important;}
.btn_dark:hover{background-color:#1d2124 !important;border-color:#1d2124 !important;}

.btn_s_primary{padding:1px 5px 2px !important;border-radius:3px !important;background-color:#3f72af !important;border-color:#3f72af !important;color:#fff !important;}
.btn_s_primary:hover{background-color:#006aee !important;border-color:#006aee !important;}
.btn_s_secondary{padding:1px 5px 2px !important;border-radius:3px !important;background-color:#545b62 !important;border-color:#545b62 !important;color:#fff !important;}
.btn_s_secondary:hover{background-color:#544a51 !important;border-color:#544a51 !important;}
.btn_s_success{padding:1px 5px 2px !important;border-radius:3px !important;background-color:#1e7e34 !important;border-color:#1e7e34 !important;color:#fff !important;}
.btn_s_success:hover{background-color:#1e6c23 !important;border-color:#1e6c23 !important;}
.btn_s_danger{padding:1px 5px 2px !important;border-radius:3px !important;background-color:#bd2232 !important;border-color:#bd2232 !important;color:#fff !important;}
.btn_s_danger:hover{background-color:#ac1221 !important;border-color:#ac1221 !important;}
.btn_s_warning{padding:1px 5px 2px !important;border-radius:3px !important;background-color:#ffc107 !important;border-color:#ffc107 !important;color:#000 !important;}
.btn_s_warning:hover{background-color:#efc107 !important;border-color:#efc107 !important;}
.btn_s_info{padding:1px 5px 2px !important;border-radius:3px !important;background-color:#17a2b8 !important;border-color:#17a2b8 !important;color:#fff !important;}
.btn_s_info:hover{background-color:#117a8b !important;border-color:#117a8b !important;}
.btn_s_dark{padding:1px 5px 2px !important;border-radius:3px !important;background-color:#343a40 !important;border-color:#343a40 !important;color:#fff !important;}
.btn_s_dark:hover{background-color:#1d2124 !important;border-color:#1d2124 !important;}

.btn_m_primary{padding:5px 5px 5px !important;border-radius:3px !important;background-color:#3f72af !important;border-color:#3f72af !important;color:#fff !important;}
.btn_m_primary:hover{background-color:#006aee !important;border-color:#006aee !important;}
.btn_m_secondary{padding:3px 5px 4px !important;border-radius:3px !important;background-color:#545b62 !important;border-color:#545b62 !important;color:#fff !important;}
.btn_m_secondary:hover{background-color:#544a51 !important;border-color:#544a51 !important;}
.btn_m_success{padding:2px 5px 3px !important;border-radius:3px !important;background-color:#1e7e34 !important;border-color:#1e7e34 !important;color:#fff !important;}
.btn_m_success:hover{background-color:#1e6c23 !important;border-color:#1e6c23 !important;}
.btn_m_danger{padding:2px 5px 3px !important;border-radius:3px !important;background-color:#bd2232 !important;border-color:#bd2232 !important;color:#fff !important;}
.btn_m_danger:hover{background-color:#ac1221 !important;border-color:#ac1221 !important;}
.btn_m_warning{padding:2px 5px 3px !important;border-radius:3px !important;background-color:#ffc107 !important;border-color:#ffc107 !important;color:#000 !important;}
.btn_m_warning:hover{background-color:#efc107 !important;border-color:#efc107 !important;}
.btn_m_info{padding:2px 5px 3px !important;border-radius:3px !important;background-color:#17a2b8 !important;border-color:#17a2b8 !important;color:#fff !important;}
.btn_m_info:hover{background-color:#117a8b !important;border-color:#117a8b !important;}
.btn_m_dark{padding:2px 5px 3px !important;border-radius:3px !important;background-color:#343a40 !important;border-color:#343a40 !important;color:#fff !important;}
.btn_m_dark:hover{background-color:#1d2124 !important;border-color:#1d2124 !important;}
```
### adm/_z01/css/adm_override.css
```css
@charset "utf-8";
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css");
/* 테마설정페이지의 '사용중'버튼 스타일 */
#theme_list li .theme_sl_use{background:#800000;}
/* 최상단 */
#hd_top{background-color:#333;}

/* 최상단 좌측 아이콘 */
#btn_gnb{
    text-indent: 0;
    background:none;
    background-color:#333;
    font-size:1.5em;
    color:#fff;
}
#btn_gnb .bi-arrow-right-square{display:none;}
#btn_gnb.btn_gnb_open{
    text-indent: 0;
    background:none;
    background-color:#333;
    font-size:1.5em;
    color:#fff;
}
#btn_gnb.btn_gnb_open .bi-arrow-left-square{display:none;}
#btn_gnb.btn_gnb_open .bi-arrow-right-square{display:block;}

/* 최상단 ADMINISTRATOR 대체 */
#logo {text-align:left;background:#555}
#logo a {color:#fff;font-weight:700;font-size:1.5em;margin-left:11px;}


#tnb li{margin-left:8px;}
#tnb button{background:#555;}
#tnb button:hover{background:#777;}
#tnb button span{background:url(../img/op_btn.png) 50% 50% no-repeat #666666}
/* 최상단 shop 아이콘 변경 */
#tnb .tnb_shop{
    display:flex;
    justify-content:center;
    align-items:center;
    text-indent: 0;
    background:none;
    font-size:1.4em;
    color:#fff;
    text-align:center;
}
#tnb .tnb_shop:hover{background:#777;}
/* 최상단 community 아이콘 변경 */
#tnb .tnb_community{
    display:flex;
    justify-content:center;
    align-items:center;
    text-indent: 0;
    background:none;
    font-size:1.6em;
    color:#fff;
    text-align:center;
}
#tnb .tnb_community:hover{background:#777;}
#tnb .tnb_service{background:#555;}
#tnb .tnb_service:hover{background:#777;}
#tnb .tnb_mb_area{background:#666666;}

/* 좌측 메뉴 */
#gnb .gnb_ul {background: #444;}
#gnb .gnb_li {border-bottom: 1px solid #222;}
#gnb .gnb_li button{background:#444;}
#gnb .gnb_li .btn_op{background:url(../img/menu_default.png) 50% 50% no-repeat #ebebeb}
#gnb .on .btn_op{background:url(../img/menu_default_on.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-100{background:url(../img/menu-1-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-100{background:url(../img/menu-1.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-200{background:url(../img/menu-2-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-200{background:url(../img/menu-2.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-300{background:url(../img/menu-3-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-300{background:url(../img/menu-3.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-400{background:url(../img/menu-7-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-400{background:url(../img/menu-7.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-500{background:url(../img/menu-6-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-500{background:url(../img/menu-6.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-900{background:url(../img/menu-4-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-900{background:url(../img/menu-4.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-910{background:url(../img/menu-9-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-910{background:url(../img/menu-9.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-920{background:url(../img/menu-a-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-920{background:url(../img/menu-a.png) 50% 50% no-repeat #fff}
#gnb .gnb_li .btn_op.menu-930{background:url(../img/menu-b-1.png) 50% 50% no-repeat #444 }
#gnb .on .btn_op.menu-930{background:url(../img/menu-b.png) 50% 50% no-repeat #fff}

/* 각 버튼 스타일 */
#wr_cont input[type="submit"],
.btn_submit,
.btn_01, 
a.btn_01{background:#800000;}
.btn_submit:hover,
.btn_01:hover, 
a.btn_01:hover{background:#900000;}
.local_sch03 button,
.btn_frmline,
a.btn_frmline,
.btn_02, 
a.btn_02{background:#555}
.btn_02:hover, a.btn_02:hover{background:#666}
#processing button{background:#800000;}

/* 팝업윈도우 */
.new_win .local_sch, 
.new_win .local_cmd, 
.new_win .local_desc01, 
.new_win .local_desc02, 
.new_win .local_ov{margin:0;}

/* 테이블공통 */
.td_category{width:auto;}
.td_code{width:150px;}

/* shop_categories 관련 */
#sct .sct_name1 {padding-left:25px;background-image:url('../img/sub_menu_ico.gif');background-repeat:no-repeat;background-size:auto;background-position:5px center;}
#sct .sct_name2 {padding-left:50px;background-image:url('../img/sub_menu_ico2.gif');background-repeat:no-repeat;background-size:auto;background-position:10px center;}
#sct .sct_name3 {padding-left:75px;}
#sct .sct_name4 {padding-left:100px;}
```
### adm/_z01/css/config_menu_form.css
```css
@charset "utf-8";
.auth_h3{border:2px solid #435ffe;display:inline-block;}
.auth_h3.unact{background:#f1f1f1;color:#999;border:2px solid #9ba8f7;text-decoration:line-through;}

.auths{}
.auths.unact{background:#f1f1f1;color:#999;border:2px solid #f7ce9b;text-decoration:line-through;}
```
### adm/_z01/css/config_menu_form.css.php
```php
<?php
$menu_h3_class = '[&>div>div]:mb-4 [&>div>div:last-child]:mt-0 [&>div>div>h3]:py-1 [&>div>div>h3]:px-2 [&>div>div>h3]:rounded-md [&>div>div>h3]:text-blue-500';
$menu_ul_class = '[&>div>div>ul]:mt-2 [&>div>div>ul]:flex [&>div>div>ul]:gap-2 [&>div>div>ul]:flex-wrap [&>div>div>ul]:leading-[0.9rem]';
$menu_li_class = '[&>div>div>ul>li]:text-nowrap [&>div>div>ul>li]:border-2 [&>div>div>ul>li]:border-orange-500 [&>div>div>ul>li]:px-2 [&>div>div>ul>li]:pt-2 [&>div>div>ul>li]:pb-1 [&>div>div>ul>li]:rounded-md [&>div>div>ul>li]:flex [&>div>div>ul>li]:items-center';

$all_hide_clear_btn = 'inline-block border py-1 px-2 rounded-md bg-gray-500 text-white cursor-pointer hover:bg-gray-600';
```
#### adm/_z01/css/employee_form.css
```css
@charset "utf-8";

.auth_box{}
.auth_box li.act{background:dodgerblue;color:#fff;border:2px solid #8484d8;}
.auth_box li span{cursor:pointer;color:#999;}
.auth_box li .auth_r,
.auth_box li.act .auth_r{background:#fff;color:#999;}
.auth_box li.act .auth_r.act{background:indigo;color:#fff;}
.auth_box li .auth_w,
.auth_box li.act .auth_w{background:#fff;color:#999;}
.auth_box li.act .auth_w.act{background:purple;color:#fff;}
.auth_box li .auth_d,
.auth_box li.act .auth_d{background:#fff;color:#999;}
.auth_box li.act .auth_d.act{background:red;color:#fff;}
```
### adm/_z01/css/employee_form.css.php
```php
<?php
$auth_renewal_label = 'inline-block mb-4 cursor-pointer text-blue-800';
$menu_h3_class = '[&>div>div]:mb-3 [&>div>div:last-child]:mt-0 [&>div>div>h3]:py-1 [&>div>div>h3]:text-orange-500 [&>div>div>h3]:flex [&>div>div>h3]:items-center [&>div>div>h3]:gap-2';
$mneu_hs_class = '[&>div>div>h3>span]:cursor-pointer [&>div>div>h3>span]:border [&>div>div>h3>span]:border-gray-500 [&>div>div>h3>span]:pt-[2px] [&>div>div>h3>span]:px-[5px] [&>div>div>h3>span]:text-[0.8rem] [&>div>div>h3>span]:relative [&>div>div>h3>span]:top-[-2px] [&>div>div>h3>span]:rounded-md [&>div>div>h3>span]:bg-gray-500 [&>div>div>h3>span:hover]:bg-gray-600 [&>div>div>h3>span]:text-white [&>div>div>h3>span:last-child]:border-red-500 [&>div>div>h3>span:last-child]:bg-red-500 [&>div>div>h3>span:last-child:hover]:bg-red-600';
$menu_ul_class = '[&>div>div>ul]:flex [&>div>div>ul]:gap-2 [&>div>div>ul]:flex-wrap [&>div>div>ul]:leading-[0.9rem]';
$menu_li_class = '[&>div>div>ul>li]:text-nowrap [&>div>div>ul>li]:border-2 [&>div>div>ul>li]:px-2 [&>div>div>ul>li]:pt-2 [&>div>div>ul>li]:pb-1 [&>div>div>ul>li]:rounded-md [&>div>div>ul>li]:flex [&>div>div>ul>li]:items-center';
$menu_sp_class = '[&>div>div>ul>li>span]:ml-[3px] [&>div>div>ul>li>span]:border [&>div>div>ul>li>span]:border-gray-300 [&>div>div>ul>li>span]:px-2 [&>div>div>ul>li>span]:pt-1 [&>div>div>ul>li>span]:relative [&>div>div>ul>li>span]:top-[-2px] [&>div>div>ul>li>span]:rounded-md';

$all_auth_del_btn = 'inline-block border py-1 px-2 rounded-md bg-gray-500 text-white cursor-pointer hover:bg-gray-600';
```
### adm/_z01/css/widget_form.css
```css
@charset "utf-8";

#td_widget_skin{width:120px;}
#td_widget_skin select{max-width:120px;}
#td_widget_skin select option{}
#td_widget_skin_btn{display:no ne;position:absolute;left:0;top:0;width:100%;height:100%;z-index:100;background:rgba(0,0,0,0);cursor:pointer;}

.td_wgt_start_dt i,
.td_wgt_end_dt i {font-size:1.2em;cursor:pointer;margin-left:5px;}
```
### adm/_z01/form/input_color.skin.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<ul class="color_ul">
	<?php if($alpha_flag) {?>
	<li class="color_li color_li1">
		<input type="color" id="<?=$aid?>" readonly value="<?=$bg16?>" style="width:50px;">
	</li>
	<li class="color_li color_li2">
		<span class="color_alpha_ttl">투명도</span>
		<?php //echo bpwg_input_range('',$bga,$w,0,1,0.05,100); ?>
		<span class="range_span bp_wdx100" style="padding-right:29px;">
			<input type="range" value="<?=$bga?>" id="<?=$bid?>" min="0" max="1" step="0.05" size="30">
			<span class="output_span">
				<output id="<?=$did?>" style="padding-right:5px;"><?=$bga?></output>
			</span>
		</span>
	</li>
	<li class="color_li color_li3">
		<input type="hidden" name="<?=$name?>" id="<?=$cid?>" value="<?=$input_color?>">
		<div class="color_result_bg"><div class="color_result"></div></div>
	</li>
	<?php }else{ ?>
	<li class="color_li color_li1"><input type="color" name="<?=$name?>" id="<?=$aid?>" readonly value="<?=$input_color?>" style="width:50px;"></li>
	<?php } ?>
</ul>
<script>
$(function(){
	//여기서 부터는 ie버전에서 input 박스의 색상을 표시해 준다.
	<?php if($g5['is_explorer']){?>
	var <?=$aid?>_val = $('#<?=$aid?>').val();
	$('#<?=$aid?>').css({'border':'1px solid #ddd','font-size':0,'background':<?=$aid?>_val});
	<?php } ?>

	<?php if($alpha_flag) {?>
		//색상과 투명도 설정
		var <?=$eid?>_rgbacolor = bwg_hex2rgba($('#<?=$aid?>').val(), <?=$bga?>);
		//console.log(<?=$eid?>_rgbacolor);
		//console.log('<?=$bga?>');
		//console.log('<?=$input_color?>');
		//console.log($('#<?=$bid?>').val());
		//console.log($('#<?=$bid?>').length);
		$('#<?=$cid?>').val(<?=$eid?>_rgbacolor);
		$('#<?=$cid?>').siblings('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);

		$('#<?=$aid?>').colpick({
			onSubmit:function(hsb,hex,rgb,el,bySetColor) {
				$(el).val('#'+hex);
				$(el).colpickHide();
				<?=$eid?>_rgbacolor = bwg_hex2rgba($(el).val(), $('#<?=$bid?>').val());
				$(el).parent().siblings('.color_li3').find('input').val(<?=$eid?>_rgbacolor);
				$(el).parent().siblings('.color_li3').find('input').siblings('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);
				
				
				<?php if($g5['is_explorer']){?>
				$(el).css({'border':'1px solid #ddd','font-size':0,'background':$(el).val()});
				<?php } ?>
			}
		});
		
		$('#<?=$bid?>').on('change',function(){
			<?=$eid?>_rgbacolor = bwg_hex2rgba($('#<?=$aid?>').val(), $(this).val());
			$(this).siblings('.output_span').find('output').text($(this).val());
			//console.log(<?=$eid?>_rgbacolor);
			$(this).parent().parent().siblings('.color_li3').find('input').val(<?=$eid?>_rgbacolor);
			$(this).parent().parent().siblings('.color_li3').find('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);
			$(this).parent().parent().siblings('.color_li3').find('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);
		});
	<?php }else{ // 여기까지는 $alpha_flag == true ?>
		//색상만 설정
		$('#<?=$aid?>').colpick({
			onSubmit:function(hsb,hex,rgb,el,bySetColor) {
				$(el).val('#'+hex);
				$(el).colpickHide();
				<?php if($g5['is_explorer']){?>
				$(el).css({'border':'1px solid #ddd','font-size':0,'background':$(el).val()});
				<?php } ?>
			}
		});
	<?php } // 여기까지는 $alpha_flag == false ?>
});
</script>
```
### adm/_z01/form/input_range.skin.php
```php
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<span class="range_span<?=$wd_class?>" style="<?=$padding_right_style?>" unit="<?=$unit?>">
<input type="range" name="<?=$rname?>" value="<?=$val?>" id="<?=$rinid?>" min="<?=$min?>" max="<?=$max?>" step="<?=$step?>" size="30">
<span class="output_span">
	<output style="<?=$output_show?>"><?=$val?></output>
	<?php if($unit){ ?>
	<span class="unit_span"><?=$unit?></span>
	<?php } ?>
</span>
</span>
<script>
$('#<?=$rinid?>').on('change',function(){
	$(this).siblings('.output_span').find('output').text($(this).val());
});
</script>
```
### adm/_z01/js/adm_dom_control.js.php
```php
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 모든 h3 태그를 가져옴
    const h3Elements = document.querySelectorAll("h3");
    // 텍스트가 정확히 '환경설정'인 요소를 찾음
    const targetH3 = Array.from(h3Elements).find(h3 => h3.textContent.trim() === "환경설정");
    if (targetH3) {
        // 새로운 <a> 요소 생성
        const link = document.createElement("a");
        link.href = g5_admin_url;
        link.textContent = "대시보드";

        // 기존 h3의 내용 지우고 <a> 요소 추가
        targetH3.textContent = "";  // 기존 텍스트 삭제
        targetH3.appendChild(link); // 링크 삽입
    }

    const btnGnb = document.querySelector("#btn_gnb"); //최상단 좌측 아이콘
    const logoA = document.querySelector("#logo a");
    const logoImg = document.querySelector("#logo a img");
    const shopBtn = document.querySelector('#tnb .tnb_shop');
    const homeBtn = document.querySelector('#tnb .tnb_community');
    // 기존 '관리자'로 고정되어 있는 것을 로그인한 회원 이름으로 변경
    const tnb_mb_btn = document.querySelector('.tnb_mb_btn');
    if(tnb_mb_btn){
        let tnb_mb_btn_txt = tnb_mb_btn.childNodes[0];
        tnb_mb_btn_txt.nodeValue = mb_name;
    }

    if (btnGnb) {
        // 이전 텍스트를 제거합니다.
        btnGnb.textContent = '';

        // <i> 태그를 생성하고 클래스를 추가합니다.
        var icon1 = document.createElement("i");
        icon1.className = "bi bi-arrow-left-square";
        var icon2 = document.createElement("i");
        icon2.className = "bi bi-arrow-right-square";
        
        // <i> 태그를 #btn_gnb에 삽입합니다.
        btnGnb.appendChild(icon1);
        btnGnb.appendChild(icon2);
    }

    // 최상단 ADMINISTRATOR 로고이미지를 제거하고 텍스트로 변경합니다.
    if (logoImg) {
        logoImg.remove();
        logoA.textContent = 'ADMINISTRATOR';
    }

    // 최상산 shop버튼 대체
    if (shopBtn) {
        // 이전 텍스트를 제거합니다.
        shopBtn.textContent = '';
        // <i> 태그를 생성하고 클래스를 추가합니다.
        var icon1 = document.createElement("i");
        icon1.className = "bi bi-bag";
        // <i> 태그를 shopBtn에 삽입합니다.
        shopBtn.appendChild(icon1);
    }

    // 최상산 home버튼 대체
    if (homeBtn) {
        // 이전 텍스트를 제거합니다.
        homeBtn.textContent = '';
        // <i> 태그를 생성하고 클래스를 추가합니다.
        var icon1 = document.createElement("i");
        icon1.className = "bi bi-house";
        // <i> 태그를 shopBtn에 삽입합니다.
        homeBtn.appendChild(icon1);
    }


    // 필수라는 의미의 *를 붙이기 위한 조건에 맞는 요소들을 찾습니다.
    // const elements = Array.from(document.getElementsByClassName('sound_only')).filter(el => el.textContent.includes('필수'));
    const elements = Array.from(document.getElementsByClassName('sound_only')).filter(el => {
        const nextEl = el.nextElementSibling;
        return el.textContent.includes('필수') && (!nextEl || nextEl.id !== 'stx');
    });

    // elements 배열의 길이가 0보다 큰 경우에만 코드를 실행합니다.
    if (elements.length > 0) {
        // 각 요소에 대해 새로운 span을 생성하고 추가합니다.
        elements.forEach(element => {
            // 새로운 span 요소를 생성합니다.
            const newSpan = document.createElement('span');
            newSpan.className = 'require';
    
            // 새로운 span을 현재 요소의 바로 다음에 삽입합니다.
            element.parentNode.insertBefore(newSpan, element.nextSibling);
        });
    }

    // 불필요한 require 클래스를 가진 요소들을 찾아 제거합니다.
    if(file_name == 'auth_list'
        || file_name == 'personalpaylist'
        || file_name == 'itemqalist'
        || file_name == 'itemuselist'
        || file_name == 'itemstocklist'
        || file_name == 'itemtypelist'
        || file_name == 'optionstocklist'
        || file_name == 'couponlist'
        || file_name == 'couponzonelist'
        || file_name == 'inorderlist'
        || file_name == 'itemstocksms'
        || file_name == 'itemeventlist'
        || file_name == 'history_list'
        || file_name == 'history_num'
        || file_name == 'form_group'
        || file_name == 'form_list'
        || file_name == 'num_group'
        || file_name == 'num_book'
        || file_name == '_win_company_select'
    ){
        const span_require = document.querySelectorAll('.require');
        span_require.forEach((el) => {
            el.remove();
        });
    }
    
    // 관리자단에서 favicon 파비콘을 title태그 다음에 추가
    var title = document.querySelector('title');
    var faviEle = document.createElement('link');
    faviEle.rel = 'icon';
    faviEle.type = 'image/png';
    faviEle.href = '<?=$set_com['favicon_url']?>'; // PHP 변수가 적절히 처리되었다고 가정
    if(title) title.insertAdjacentElement('afterend', faviEle);

});
</script>
```
### adm/_z01/js/adm_func.js
```js
//hex to rgba
if(typeof(tms_hex2rgba) != 'function'){	
function tms_hex2rgba(hex, alpha) {
    var r = parseInt(hex.slice(1, 3), 16),
        g = parseInt(hex.slice(3, 5), 16),
        b = parseInt(hex.slice(5, 7), 16);

    return "rgba(" + r + ", " + g + ", " + b + ", " + alpha + ")";
    
    //else {
    //    return "rgb(" + r + ", " + g + ", " + b + ")";
    //}
}
}
//rgba to hex
if(typeof(tms_rgba2hex) != 'function'){
function tms_rgba2hex(rgba){
    var backtxt = rgba.substring(rgba.indexOf('(')+1);//rgba(까지 잘라낸 나머지 문자열 대입
    var oktxt = backtxt.substr(0,backtxt.length-1); //마지막 )를 잘라낸 나머지 문자열 대입
    var okarr = oktxt.split(',');//(,)로 분할해서 배열변수에 대입
    var okr = $.trim(okarr[0]);
    var okg = $.trim(okarr[1]);
    var okb = $.trim(okarr[2]);
    var oka = $.trim(okarr[3]);
    var result = '';
    
    result = "#"+
        ("0"+parseInt(okr,10).toString(16)).slice(-2) +
        ("0"+parseInt(okg,10).toString(16)).slice(-2) +
        ("0"+parseInt(okb,10).toString(16)).slice(-2);
    
    if(oka){
        return {"color":result,"opacity":parseFloat(oka)};
    }else{
        return {"color":result,"opacity":0};
    }
}
}


//첨부파일 한 개씩 삭제처리하는 함수
if(typeof(file_single_del) != 'function'){
function file_single_del(fle_db_tbl,fle_idx){
    if(confirm("선택한 파일을 정말 삭제 하시겠습니까?")){
        var single_file_url = g5_url+'/adm/_z01/ajax/wgt_file_single_del.php';
        $.ajax({
            type:"POST",
            url:single_file_url,
            dataType:"text",
            data:{'fle_db_tbl':fle_db_tbl,'fle_idx':fle_idx},
            success:function(res){
                if(res){
                    alert(res);
                }else{
                    location.reload();
                }
            },
            error:function(e){
                alert(e.responseText);
            }
        });
    }
}
}

//한 줄의 첨부파일들을 일괄 삭제처리하는 함수
if(typeof(files_row_del) != 'function'){
function files_row_del(fle_db_tbl,fle_idxs){
    if(confirm("선택한 파일을 전부 삭제 하시겠습니까?")){
        var row_files_url = g5_url+'/adm/_z01/ajax/wgt_files_row_del.php';
        $.ajax({
            type:"POST",
            url:row_files_url,
            dataType:"text",
            data:{'fle_db_tbl':fle_db_tbl,'fle_idxs':fle_idxs},
            success:function(res){
                if(res){
                    alert(res);
                }else{
                    location.reload();
                }
            },
            error:function(e){
                alert(e.responseText);
            }
        });
    }
}
}

//
if(typeof(check_all_tms) != 'function'){
function check_all_tms(f)
{
    //alert(f.chkall.checked);return false;
    var chk = $('input[name^="chk["]');
    
    chk.each(function(){
        $(this).attr('checked',f.chkall.checked);
    });
}
}

//목록페이지 checkbox 체크되어 있는항목이 한 개라도 존재하는 확인하는 함수
if(typeof(is_checked_tms) != 'function'){
function is_checked_tms(hidden_chk_list_name){
    var checked = false;
    var chk = $('input[name^="'+hidden_chk_list_name+'["]');
    
    chk.each(function(){
        if($(this).attr('checked'))
            checked = true;
    });
    
    return checked;
}
}    
```
### adm/_z01/js/company_form.js.php
```php
<script>
//업체관련 멀티파일
$('#multi_file_com').MultiFile();

const com_select = document.querySelector('.com_select');
com_select.addEventListener('click', () => {
    const url = com_select.getAttribute('data-url') + '?file_name=' + file_name;
    const win_com_select = window.open(url, "win_com_select", "width=500,height=540,scrollbars=yes");
    win_com_select.focus();
    return false;
});

const key_renewal = document.querySelector('#key_renewal');
const key_clear = document.querySelector('#key_clear');
key_renewal.addEventListener('change', (e) => {
    if (e.target.checked) {
        key_clear.checked = false;
    }
});

key_clear.addEventListener('change', (e) => {
    if (e.target.checked) {
        key_renewal.checked = false;
    }
});

function form01_submit(f) {
    if (f.com_name.value.trim() === '') {
        alert('업체명을 입력해 주십시오.');
        f.com_name.focus();
        return false;
    }

    if (f.com_email.value == '') {
        alert('이메일을 입력해 주십시오2.');
        f.com_email.focus();
        return false;
    }
    // 이메일 검증에 사용할 정규식 (이메일정규식)
    var emailRegExp = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
    if (f.com_email.value.match(emailRegExp) == null) {
        alert("올바른 이메일 주소가 아닙니다.");
        f.com_email.focus();
        return false; 
    }


    if (f.com_president.value == '') {
        alert('대표자명을 입력해 주십시오.');
        f.com_president.focus();
        return false;
    }

    if (f.com_tel.value == '') {
        alert('업체전화번호를 입력해 주십시오.');
        f.com_tel.focus();
        return false;
    }

    // 사업자번호에 해당하는 정보가 있으면 사업자번호 검증을 함(사업자번호정규식)
    if(f.com_biz_no.value.trim() !== ''){
        var bizNoRegExp = /^(\d{3}-\d{2}-\d{5}|\d{10})$/;
        if(f.com_biz_no.value.match(bizNoRegExp) == null){
            alert("올바른 사업자번호가 아닙니다.");
            f.com_biz_no.focus();
            return false;
        }
    }

    // 홈페이지 주소가 있으면 홈페이지 주소 검증을 함(홈페이지주소정규식,도메인정규식,URL정규식,url정규식)
    if(f.com_url.value.trim() !== ''){
        var urlRegExp = /^(https?:\/\/)?(www\.)?[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+(\.[a-zA-Z]{2,})?(\S*)?$/;
        if(f.com_url.value.match(urlRegExp) == null){
            alert("올바른 홈페이지 주소가 아닙니다.");
            f.com_url.focus();
            return false;
        }
    }

    return true;
}
</script>
```
### adm/_z01/js/company_list.js.php
```php
<script>
const btn_mngs = document.querySelectorAll('.btn_manager');
btn_mngs.forEach(btn_mng => {
    btn_mng.addEventListener('click', () => {
        const href = "./company_member_list.php?com_idx=" + btn_mng.getAttribute('com_idx');
        const winCompanyMember = window.open(href, "winCompanyMember", "width=800, height=700, left=100, top=100, scrollbars=yes");
        winCompanyMember.focus();
    })
});


function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
	}
	
	if(document.pressed == "선택삭제") {
		if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
			return false;
		}
		else {
			$('input[name="w"]').val('d');
		} 
	}
    return true;
}
</script>
```
### adm/_z01/js/config_com_form.js.php
```php
<script>
$('#file_preparing').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif',
});
$('#file_favicon').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif',
});
$('#file_ogimg').MultiFile({
    max:1,
    accept:'jpg|jpeg|png|gif',
});
$('#file_sitemap').MultiFile({
    max:1,
    accept:'xml',
});
function fconfigform_submit(f) {
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = "./config_com_form_update.php";
    return true;
}
</script>
```
### adm/_z01/js/config_conf_form.js.php
```php
<script>
function fconfigform_submit(f) {
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = "./config_form_update.php";
    return true;
}
</script>
```
### adm/_z01/js/config_menu_form.js.php
```php
<script>
// 관리페이지권한
const auth_div = document.querySelectorAll('.auth_div');

auth_div.forEach((v) => {
    v.addEventListener('click', authClick);
});

function authClick(e){
    if(!e.target.closest('.auth_h3') && !e.target.closest('.auths')) return;

    let pel = (e.target.classList.contains('auth_h3')) ? e.target.parentNode : e.target.parentNode.parentNode;
    let auth_h3 = (e.target.classList.contains('auth_h3')) ? e.target : pel.querySelector('.auth_h3');
    let auth_li = (e.target.classList.contains('auths')) ? e.target : null;
    let auth_list = pel.querySelectorAll('.auths');

    if(e.target.classList.contains('auth_h3')){
        auth_h3.classList.toggle('unact');
        if(auth_h3.classList.contains('unact')){ // 만약 메인메뉴가 비활성화되면 하위메뉴는
            auth_list.forEach((v) => {
                v.classList.remove('unact');
            });
        }
    }
    else if(e.target.classList.contains('auths')){
        auth_li.classList.toggle('unact');
        if(auth_li.classList.contains('unact')){ // 만약 하위메뉴가 비활성화되면 메인메뉴도
            auth_h3.classList.remove('unact');
            let unact_cnt = 0;
            auth_list.forEach((v) => {
                if(v.classList.contains('unact')) unact_cnt++;
            });
            
            if(unact_cnt == auth_list.length){
                auth_h3.classList.add('unact');
                auth_list.forEach((v) => {
                    v.classList.remove('unact');
                });
            }
        }
    }
    allAuthInputUpdate();
}

function allAuthInputUpdate(){
    const set_hide_mainmenus = document.querySelector('#set_hide_mainmenus');
    const set_hide_submenus = document.querySelector('#set_hide_submenus');
    const auth_h3s = document.querySelectorAll('.auth_h3');
    const auths = document.querySelectorAll('.auths');
    let mainmenus = '';
    let submenus = '';

    let i = 0;
    auth_h3s.forEach((v) => {
        if(v.classList.contains('unact')){
            mainmenus += (i == 0)?v.getAttribute('data-code') : ',' + v.getAttribute('data-code');
            i++;
        }
    });
    set_hide_mainmenus.value = mainmenus;
    
    let n = 0;
    auths.forEach((v) => {
        if(v.classList.contains('unact')){
            submenus += (n == 0)?v.getAttribute('data-code') : ',' + v.getAttribute('data-code');
            n++;
        }
    });
    set_hide_submenus.value = submenus;  
}

// 전체비활성해제
const all_hide_clear = document.querySelector('#all_hide_clear');
all_hide_clear.addEventListener('click', allAuthInputClear);

function allAuthInputClear(){
    const main = document.querySelector('input[name="set_hide_mainmenus"]');
    const sub = document.querySelector('input[name="set_hide_submenus"]');
    const auth_h3s = document.querySelectorAll('.auth_h3.unact');
    const auths = document.querySelectorAll('.auths.unact');
    if(auth_h3s.length) {
        auth_h3s.forEach((v) => {
            v.classList.remove('unact');
        });
        main.value = '';
    }
    if(auths.length) {
        auths.forEach((v) => {
            v.classList.remove('unact');
        });
        sub.value = '';
    }
}
</script>
```
### adm/_z01/js/employee_form.js.php
```php
<script>
//사원관련 멀티파일
$('#multi_file_emp').MultiFile();

const reg_mb_id = document.querySelector('#reg_mb_id');
let mb_id_flag = true;
let mb_email_flag = true;
let mb_nick_flag = true;
let mb_hp_flag = true;

<?php if($w=='') { //등록모드일때 ################################### ?>
const url = '<?=G5_Z_URL?>/ajax/mb_id_check.php';
const s_id_info = document.querySelector('.s_id_info');
const mb_id_pattern = /^[a-zA-Z][a-zA-Z0-9]{4,19}$/;
let timer;
reg_mb_id.addEventListener('keydown', function(){
    clearTimeout(timer);
    timer = setTimeout(mbIdCheck, 500);
});

async function mbIdCheck(){
    const mb_id = reg_mb_id.value.trim();

    // 입력값 유효성 검사
    if(mb_id.length === 0){
        return;
    }

    // 최소5글자이상 최대 20글자 미만
    if(mb_id.length < 5 || mb_id.length > 20){
        s_id_info.textContent = '아이디는 5글자 이상, 20글자 미만이어야 합니다.';
        s_id_info.style.color = 'red';
        mb_id_flag = false;
        return;
    }

    // 최대20글자이하
    if(!mb_id_pattern.test(mb_id)){
        s_id_info.textContent = '아이디는 영문 또는 영문숫자 조합으로 해서 5글자 이상, 20글자 미만이어야 합니다.';
        s_id_info.style.color = 'red';
        mb_id_flag = false;
        return;
    }

    try{
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'mb_id=' + encodeURIComponent(mb_id),
        });

        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

        const rst = await res.text();
        
        if(rst == '1'){
            s_id_info.textContent = '사용가능한 아이디입니다.';
            s_id_info.style.color = 'blue';
            mb_id_flag = true;
        }
        else if(rst == '0'){
            s_id_info.textContent = '이미 사용중인 아이디입니다.';
            s_id_info.style.color = 'red';
            mb_id_flag = false;
        }
        else{
            throw new Error('서버 오류가 발생했습니다.');
            s_id_info.textContent = '';
            s_id_info.style.color = 'black';
            mb_id_flag = false;
        }
    }
    catch(err){
        console.error('Error:',err);
        s_id_info.textContent = err;
        s_id_info.style.color = 'red';
        mb_id_flag = false;
    }
}
<?php } ?>
const url2 = '<?=G5_Z_URL?>/ajax/email_check.php';
const reg_mb_email = document.querySelector('#reg_mb_email');
const s_email_info = document.querySelector('.s_email_info');
const mb_email_pattern = /^[^ ]+@[^ ]+\.[a-z]{2,4}$/;
let timer2;
reg_mb_email.addEventListener('keydown', function(){
    clearTimeout(timer2);
    timer2 = setTimeout(mbEmailCheck, 500);
});

async function mbEmailCheck(){
    const mb_id = reg_mb_id.value.trim();
    const mb_email = reg_mb_email.value.trim();
    const w = '<?=$w?>';
    
    // 입력값 유효성 검사
    if(mb_id.length === 0){
        s_email_info.textContent = '아이디 입력이 안되어 있습니다.';
        s_email_info.style.color = 'red';
        mb_email_flag = false;
        return;
    }

    // 이메일 형식 검사
    if(!mb_email_pattern.test(mb_email)){
        s_email_info.textContent = '이메일 형식에 맞지 않습니다.';
        s_email_info.style.color = 'red';
        mb_email_flag = false;
        return;
    }

    try{
        const data = {
            mb_id: mb_id,
            mb_email: mb_email,
            w: w,
        };
        
        const res = await fetch(url2, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            // body: 'mb_id=' + encodeURIComponent(mb_id) + '&mb_email=' + encodeURIComponent(mb_email) + '&w=' + encodeURIComponent(w),
            body: JSON.stringify(data),
        });

        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

        const rst = await res.text();
        // console.log(rst);return;
        if(rst == '1'){
            s_email_info.textContent = '사용가능한 이메일입니다.';
            s_email_info.style.color = 'blue';
            mb_email_flag = true;
        }
        else if(rst == '0'){
            s_email_info.textContent = '이미 다른 사용자가 사용중인 이메일입니다.';
            s_email_info.style.color = 'red';
            mb_email_flag = false;
        }
        else{
            throw new Error('서버 오류가 발생했습니다.');
            s_email_info.textContent = '';
            s_email_info.style.color = 'black';
            mb_email_flag = false;
        }
    }
    catch(err){
        console.error('Error:',err);
        s_email_info.textContent = err;
        s_email_info.style.color = 'red';
        mb_email_flag = false;
    }
}

// 닉네임 검사
const url3 = '<?=G5_Z_URL?>/ajax/nick_check.php';
const reg_mb_nick = document.querySelector('#reg_mb_nick');
const s_nick_info = document.querySelector('.s_nick_info');
const mb_nick_pattern = /^[a-zA-Z가-힣]+[a-zA-Z가-힣0-9]*$/;
let timer3;
reg_mb_nick.addEventListener('keydown', function(){
    clearTimeout(timer3);
    timer3 = setTimeout(mbNickCheck, 500);
});

async function mbNickCheck(){
    const mb_id = reg_mb_id.value.trim();
    const mb_nick = reg_mb_nick.value.trim();
    const w = '<?=$w?>';
    
    // 입력값 유효성 검사
    if(mb_id.length === 0){
        s_nick_info.textContent = '아이디 입력이 안되어 있습니다.';
        s_nick_info.style.color = 'red';
        mb_nick_flag = false;
        return;
    }

    // 닉네임 형식 검사
    if(!mb_nick_pattern.test(mb_nick)){
        s_nick_info.textContent = '닉네임 형식에 맞지 않습니다.';
        s_nick_info.style.color = 'red';
        mb_nick_flag = false;
        return;
    }

    try{
        const data = {
            mb_id: mb_id,
            mb_nick: mb_nick,
            w: w,
        };
        
        const res = await fetch(url3, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            // body: 'mb_id=' + encodeURIComponent(mb_id) + '&mb_nick=' + encodeURIComponent(mb_nick) + '&w=' + encodeURIComponent(w),
            body: JSON.stringify(data),
        });

        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

        const rst = await res.text();
        // console.log(rst);return;
        if(rst == '1'){
            s_nick_info.textContent = '사용가능한 닉네임입니다.';
            s_nick_info.style.color = 'blue';
            mb_nick_flag = true;
        }
        else if(rst == '0'){
            s_nick_info.textContent = '이미 다른 사용자가 사용중인 닉네임입니다.';
            s_nick_info.style.color = 'red';
            mb_nick_flag = false;
        }
        else{
            throw new Error('서버 오류가 발생했습니다.');
            s_nick_info.textContent = '';
            s_nick_info.style.color = 'black';
            mb_nick_flag = false;
        }
    }
    catch(err){
        console.error('Error:',err);
        s_nick_info.textContent = err;
        s_nick_info.style.color = 'red';
        mb_nick_flag = false;
    }
}


// 휴대폰 검사
const url4 = '<?=G5_Z_URL?>/ajax/hp_check.php';
const reg_mb_hp = document.querySelector('#reg_mb_hp');
const s_hp_info = document.querySelector('.s_hp_info');
const mb_hp_pattern = /^01([0|1|6|7|8|9])-?([0-9]{3,4})-?([0-9]{4})$/;
let timer4;
reg_mb_hp.addEventListener('keydown', function(){
    clearTimeout(timer4);
    timer4 = setTimeout(mbHpCheck, 500);
});

async function mbHpCheck(){
    const mb_id = reg_mb_id.value.trim();
    const mb_hp = reg_mb_hp.value.trim();
    const w = '<?=$w?>';
    
    // 입력값 유효성 검사
    if(mb_id.length === 0){
        s_hp_info.textContent = '아이디 입력이 안되어 있습니다.';
        s_hp_info.style.color = 'red';
        mb_hp_flag = false;
        return;
    }

    // 휴대폰 형식 검사
    if(!mb_hp_pattern.test(mb_hp)){
        s_hp_info.textContent = '휴대폰번호 형식에 맞지 않습니다.';
        s_hp_info.style.color = 'red';
        mb_hp_flag = false;
        return;
    }

    try{
        const data = {
            mb_id: mb_id,
            mb_hp: mb_hp,
            w: w,
        };
        
        const res = await fetch(url4, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            // body: 'mb_id=' + encodeURIComponent(mb_id) + '&mb_hp=' + encodeURIComponent(mb_hp) + '&w=' + encodeURIComponent(w),
            body: JSON.stringify(data),
        });

        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

        const rst = await res.text();
        // console.log(rst);return;
        if(rst == '1'){
            s_hp_info.textContent = '사용가능한 휴대폰번호입니다.';
            s_hp_info.style.color = 'blue';
            mb_hp_flag = true;
        }
        else if(rst == '0'){
            s_hp_info.textContent = '이미 다른 사용자가 사용중인 휴대폰번호입니다.';
            s_hp_info.style.color = 'red';
            mb_hp_flag = false;
        }
        else{
            throw new Error('서버 오류가 발생했습니다.');
            s_hp_info.textContent = '';
            s_hp_info.style.color = 'black';
            mb_hp_flag = false;
        }
    }
    catch(err){
        console.error('Error:',err);
        s_hp_info.textContent = err;
        s_hp_info.style.color = 'red';
        mb_hp_flag = false;
    }
}


// 관리페이지권한
const auth_ul = document.querySelectorAll('.auth_ul');

auth_ul.forEach((v) => {
    v.addEventListener('click', authClick);
});

function authClick(e){
    if(!e.target.closest('.auth_r') && !e.target.closest('.auth_w') && !e.target.closest('.auth_d')) return;
    
    const pl = e.target.parentNode;
    const el = e.target;
    let el_r = pl.querySelector('.auth_r').classList.contains('act');
    let el_w = pl.querySelector('.auth_w').classList.contains('act');
    let el_d = pl.querySelector('.auth_d').classList.contains('act');
    
    // console.log(el_r);return;
    // console.log(pl.getAttribute('data-code'));
    if(el.classList.contains('act')){
        el.classList.remove('act');
        if(el.classList.contains('auth_r')){
            el_r = false;
        }
        if(el.classList.contains('auth_w')){
            el_w = false;
        }
        if(el.classList.contains('auth_d')){
            el_d = false;
        }

        if(!el_r && !el_w && !el_d){
            pl.classList.remove('act');
        }
    }
    else{
        el.classList.add('act');
        if(el.classList.contains('auth_r')){
            el_r = true;
        }
        if(el.classList.contains('auth_w')){
            el_w = true;
        }
        if(el.classList.contains('auth_d')){
            el_d = true;
        }

        if(!pl.classList.contains('act')){
            pl.classList.add('act');
        }
    }

    allAuthInputUpdate();
}

function allAuthInputUpdate(){
    const inp = document.querySelector('input[name="auths"]');
    const auths = document.querySelectorAll('.auths.act');
    if(!auths.length){
        inp.value = '';
        return;
    }
    inp_str = '';
    let n = 0;
    auths.forEach((v) => {
        const code = v.getAttribute('data-code');
        const r = v.querySelector('.auth_r').classList.contains('act');
        const w = v.querySelector('.auth_w').classList.contains('act');
        const d = v.querySelector('.auth_d').classList.contains('act');
        const str = code + (r ? '_r' : '') + (w ? '_w' : '') + (d ? '_d' : '');
        inp_str += (n == 0) ? str : ',' + str;
        n++;
    });
    inp.value = inp_str;
}

// 전체권한삭제
const all_auth_del = document.querySelector('#all_auth_del');
all_auth_del.addEventListener('click', allAuthInputDelete);

function allAuthInputDelete(){
    const inp = document.querySelector('input[name="auths"]');
    const auths = document.querySelectorAll('.auths.act');
    if(!auths.length) return;
    auths.forEach((v) => {
        const code = v.classList.remove('act');
        const r = v.querySelector('.auth_r').classList.remove('act');
        const w = v.querySelector('.auth_w').classList.remove('act');
        const d = v.querySelector('.auth_d').classList.remove('act');
    });
    inp.value = '';
}

// 그룹권한버튼
const auth_h3 = document.querySelectorAll('.auth_h3');
auth_h3.forEach((v) => {
    v.addEventListener('click', authH3Click);
});

function authH3Click(e){
    if(!e.target.closest('.group_y') && !e.target.closest('.group_n')) return;
    const pl = e.target.closest('div');
    const ul = pl.querySelector('ul');
    const el = e.target;
    if(el.classList.contains('group_y')){
        groupAuthY(ul);
    }
    else if(el.classList.contains('group_n')){
        groupAuthN(ul);
    }
}

function groupAuthY(el){
    const auths = el.querySelectorAll('.auths');
    auths.forEach((v) => {
        v.classList.add('act');
        v.querySelector('.auth_r').classList.add('act');
        v.querySelector('.auth_w').classList.add('act');
        v.querySelector('.auth_d').classList.add('act');
    });
    allAuthInputUpdate();
}

function groupAuthN(el){
    const auths = el.querySelectorAll('.auths');
    auths.forEach((v) => {
        v.classList.remove('act');
        v.querySelector('.auth_r').classList.remove('act');
        v.querySelector('.auth_w').classList.remove('act');
        v.querySelector('.auth_d').classList.remove('act');
    });
    allAuthInputUpdate();
}

// 마지막 유효성검사후 DB에 데이터 전송
function fmember_submit(f){
    // 아이디검사
    if (!mb_id_flag || !f.mb_id.value){ 
        alert('올바른 아이디를 입력해 주십시오.');
        f.mb_id.focus();
        return false;
    }
    <?php if($w=='') { ?>
    // 비밀번호검사
    if (!f.mb_password.value){
        alert('비밀번호를 입력해 주십시오.');
        f.mb_password.focus();
        return false;
    }
    <?php } ?>
    // 이름검사
    if (!f.mb_name.value){
        alert('이름을 입력해 주십시오.');
        f.mb_name.focus();
        return false;
    }
    //닉네임검사
    if (!mb_nick_flag || !f.mb_nick.value){
        alert('올바른 닉네임을 입력해 주십시오.');
        f.mb_nick.focus();
        return false;
    }
    // 부서검사
    if(!f.mb_department.value){
        alert('부서를 선택해 주십시오.');
        f.mb_department.focus();
        return false;
    }
    // 직급검사
    if(!f.mb_rank.value){
        alert('직급을 선택해 주십시오.');
        f.mb_rank.focus();
        return false;
    }
    //직책검사
    if(!f.mb_role.value){
        alert('직책을 선택해 주십시오.');
        f.mb_role.focus();
        return false;
    }
    // 이메일검사
    if (!mb_email_flag || !f.mb_email.value){
        alert('올바른 이메일을 입력해 주십시오.');
        f.mb_email.focus();
        return false;
    }
    // 휴대폰번호검사
    if (!mb_hp_flag || !f.mb_hp.value){
        alert('올바른 휴대폰번호를 입력해 주십시오.');
        f.mb_hp.focus();
        return false;
    }
    // 입사일검사
    if(!f.mb_datetime.value){
        alert('입사일을 선택해 주십시오.');
        f.mb_datetime.focus();
        return false;
    }

    return true;
}
</script>
```
### adm/_z01/js/tailwind.min.js
```js
(()=>{var wb=Object.create;var li=Object.defineProperty;var bb=Object.getOwnPropertyDescriptor;var vb=Object.getOwnPropertyNames;var xb=Object.getPrototypeOf,kb=Object.prototype.hasOwnProperty;var au=i=>li(i,"__esModule",{value:!0});var ou=i=>{if(typeof require!="undefined")return require(i);throw new Error('Dynamic require of "'+i+'" is not supported')};var C=(i,e)=>()=>(i&&(e=i(i=0)),e);var v=(i,e)=>()=>(e||i((e={exports:{}}).exports,e),e.exports),Ae=(i,e)=>{au(i);for(var t in e)li(i,t,{get:e[t],enumerable:!0})},Sb=(i,e,t)=>{if(e&&typeof e=="object"||typeof e=="function")for(let r of vb(e))!kb.call(i,r)&&r!=="default"&&li(i,r,{get:()=>e[r],enumerable:!(t=bb(e,r))||t.enumerable});return i},X=i=>Sb(au(li(i!=null?wb(xb(i)):{},"default",i&&i.__esModule&&"default"in i?{get:()=>i.default,enumerable:!0}:{value:i,enumerable:!0})),i);var h,l=C(()=>{h={platform:"",env:{},versions:{node:"14.17.6"}}});var Cb,te,je=C(()=>{l();Cb=0,te={readFileSync:i=>self[i]||"",statSync:()=>({mtimeMs:Cb++}),promises:{readFile:i=>Promise.resolve(self[i]||"")}}});var Qn=v((PO,uu)=>{l();"use strict";var lu=class{constructor(e={}){if(!(e.maxSize&&e.maxSize>0))throw new TypeError("`maxSize` must be a number greater than 0");if(typeof e.maxAge=="number"&&e.maxAge===0)throw new TypeError("`maxAge` must be a number greater than 0");this.maxSize=e.maxSize,this.maxAge=e.maxAge||1/0,this.onEviction=e.onEviction,this.cache=new Map,this.oldCache=new Map,this._size=0}_emitEvictions(e){if(typeof this.onEviction=="function")for(let[t,r]of e)this.onEviction(t,r.value)}_deleteIfExpired(e,t){return typeof t.expiry=="number"&&t.expiry<=Date.now()?(typeof this.onEviction=="function"&&this.onEviction(e,t.value),this.delete(e)):!1}_getOrDeleteIfExpired(e,t){if(this._deleteIfExpired(e,t)===!1)return t.value}_getItemValue(e,t){return t.expiry?this._getOrDeleteIfExpired(e,t):t.value}_peek(e,t){let r=t.get(e);return this._getItemValue(e,r)}_set(e,t){this.cache.set(e,t),this._size++,this._size>=this.maxSize&&(this._size=0,this._emitEvictions(this.oldCache),this.oldCache=this.cache,this.cache=new Map)}_moveToRecent(e,t){this.oldCache.delete(e),this._set(e,t)}*_entriesAscending(){for(let e of this.oldCache){let[t,r]=e;this.cache.has(t)||this._deleteIfExpired(t,r)===!1&&(yield e)}for(let e of this.cache){let[t,r]=e;this._deleteIfExpired(t,r)===!1&&(yield e)}}get(e){if(this.cache.has(e)){let t=this.cache.get(e);return this._getItemValue(e,t)}if(this.oldCache.has(e)){let t=this.oldCache.get(e);if(this._deleteIfExpired(e,t)===!1)return this._moveToRecent(e,t),t.value}}set(e,t,{maxAge:r=this.maxAge===1/0?void 0:Date.now()+this.maxAge}={}){this.cache.has(e)?this.cache.set(e,{value:t,maxAge:r}):this._set(e,{value:t,expiry:r})}has(e){return this.cache.has(e)?!this._deleteIfExpired(e,this.cache.get(e)):this.oldCache.has(e)?!this._deleteIfExpired(e,this.oldCache.get(e)):!1}peek(e){if(this.cache.has(e))return this._peek(e,this.cache);if(this.oldCache.has(e))return this._peek(e,this.oldCache)}delete(e){let t=this.cache.delete(e);return t&&this._size--,this.oldCache.delete(e)||t}clear(){this.cache.clear(),this.oldCache.clear(),this._size=0}resize(e){if(!(e&&e>0))throw new TypeError("`maxSize` must be a number greater than 0");let t=[...this._entriesAscending()],r=t.length-e;r<0?(this.cache=new Map(t),this.oldCache=new Map,this._size=t.length):(r>0&&this._emitEvictions(t.slice(0,r)),this.oldCache=new Map(t.slice(r)),this.cache=new Map,this._size=0),this.maxSize=e}*keys(){for(let[e]of this)yield e}*values(){for(let[,e]of this)yield e}*[Symbol.iterator](){for(let e of this.cache){let[t,r]=e;this._deleteIfExpired(t,r)===!1&&(yield[t,r.value])}for(let e of this.oldCache){let[t,r]=e;this.cache.has(t)||this._deleteIfExpired(t,r)===!1&&(yield[t,r.value])}}*entriesDescending(){let e=[...this.cache];for(let t=e.length-1;t>=0;--t){let r=e[t],[n,a]=r;this._deleteIfExpired(n,a)===!1&&(yield[n,a.value])}e=[...this.oldCache];for(let t=e.length-1;t>=0;--t){let r=e[t],[n,a]=r;this.cache.has(n)||this._deleteIfExpired(n,a)===!1&&(yield[n,a.value])}}*entriesAscending(){for(let[e,t]of this._entriesAscending())yield[e,t.value]}get size(){if(!this._size)return this.oldCache.size;let e=0;for(let t of this.oldCache.keys())this.cache.has(t)||e++;return Math.min(this._size+e,this.maxSize)}};uu.exports=lu});var fu,cu=C(()=>{l();fu=i=>i&&i._hash});function ui(i){return fu(i,{ignoreUnknown:!0})}var pu=C(()=>{l();cu()});function Xe(i){if(i=`${i}`,i==="0")return"0";if(/^[+-]?(\d+|\d*\.\d+)(e[+-]?\d+)?(%|\w+)?$/.test(i))return i.replace(/^[+-]?/,t=>t==="-"?"":"-");let e=["var","calc","min","max","clamp"];for(let t of e)if(i.includes(`${t}(`))return`calc(${i} * -1)`}var fi=C(()=>{l()});var du,hu=C(()=>{l();du=["preflight","container","accessibility","pointerEvents","visibility","position","inset","isolation","zIndex","order","gridColumn","gridColumnStart","gridColumnEnd","gridRow","gridRowStart","gridRowEnd","float","clear","margin","boxSizing","lineClamp","display","aspectRatio","size","height","maxHeight","minHeight","width","minWidth","maxWidth","flex","flexShrink","flexGrow","flexBasis","tableLayout","captionSide","borderCollapse","borderSpacing","transformOrigin","translate","rotate","skew","scale","transform","animation","cursor","touchAction","userSelect","resize","scrollSnapType","scrollSnapAlign","scrollSnapStop","scrollMargin","scrollPadding","listStylePosition","listStyleType","listStyleImage","appearance","columns","breakBefore","breakInside","breakAfter","gridAutoColumns","gridAutoFlow","gridAutoRows","gridTemplateColumns","gridTemplateRows","flexDirection","flexWrap","placeContent","placeItems","alignContent","alignItems","justifyContent","justifyItems","gap","space","divideWidth","divideStyle","divideColor","divideOpacity","placeSelf","alignSelf","justifySelf","overflow","overscrollBehavior","scrollBehavior","textOverflow","hyphens","whitespace","textWrap","wordBreak","borderRadius","borderWidth","borderStyle","borderColor","borderOpacity","backgroundColor","backgroundOpacity","backgroundImage","gradientColorStops","boxDecorationBreak","backgroundSize","backgroundAttachment","backgroundClip","backgroundPosition","backgroundRepeat","backgroundOrigin","fill","stroke","strokeWidth","objectFit","objectPosition","padding","textAlign","textIndent","verticalAlign","fontFamily","fontSize","fontWeight","textTransform","fontStyle","fontVariantNumeric","lineHeight","letterSpacing","textColor","textOpacity","textDecoration","textDecorationColor","textDecorationStyle","textDecorationThickness","textUnderlineOffset","fontSmoothing","placeholderColor","placeholderOpacity","caretColor","accentColor","opacity","backgroundBlendMode","mixBlendMode","boxShadow","boxShadowColor","outlineStyle","outlineWidth","outlineOffset","outlineColor","ringWidth","ringColor","ringOpacity","ringOffsetWidth","ringOffsetColor","blur","brightness","contrast","dropShadow","grayscale","hueRotate","invert","saturate","sepia","filter","backdropBlur","backdropBrightness","backdropContrast","backdropGrayscale","backdropHueRotate","backdropInvert","backdropOpacity","backdropSaturate","backdropSepia","backdropFilter","transitionProperty","transitionDelay","transitionDuration","transitionTimingFunction","willChange","contain","content","forcedColorAdjust"]});function mu(i,e){return i===void 0?e:Array.isArray(i)?i:[...new Set(e.filter(r=>i!==!1&&i[r]!==!1).concat(Object.keys(i).filter(r=>i[r]!==!1)))]}var gu=C(()=>{l()});var yu={};Ae(yu,{default:()=>_e});var _e,ci=C(()=>{l();_e=new Proxy({},{get:()=>String})});function Jn(i,e,t){typeof h!="undefined"&&h.env.JEST_WORKER_ID||t&&wu.has(t)||(t&&wu.add(t),console.warn(""),e.forEach(r=>console.warn(i,"-",r)))}function Xn(i){return _e.dim(i)}var wu,F,Oe=C(()=>{l();ci();wu=new Set;F={info(i,e){Jn(_e.bold(_e.cyan("info")),...Array.isArray(i)?[i]:[e,i])},warn(i,e){["content-problems"].includes(i)||Jn(_e.bold(_e.yellow("warn")),...Array.isArray(i)?[i]:[e,i])},risk(i,e){Jn(_e.bold(_e.magenta("risk")),...Array.isArray(i)?[i]:[e,i])}}});var bu={};Ae(bu,{default:()=>Kn});function ar({version:i,from:e,to:t}){F.warn(`${e}-color-renamed`,[`As of Tailwind CSS ${i}, \`${e}\` has been renamed to \`${t}\`.`,"Update your configuration file to silence this warning."])}var Kn,Zn=C(()=>{l();Oe();Kn={inherit:"inherit",current:"currentColor",transparent:"transparent",black:"#000",white:"#fff",slate:{50:"#f8fafc",100:"#f1f5f9",200:"#e2e8f0",300:"#cbd5e1",400:"#94a3b8",500:"#64748b",600:"#475569",700:"#334155",800:"#1e293b",900:"#0f172a",950:"#020617"},gray:{50:"#f9fafb",100:"#f3f4f6",200:"#e5e7eb",300:"#d1d5db",400:"#9ca3af",500:"#6b7280",600:"#4b5563",700:"#374151",800:"#1f2937",900:"#111827",950:"#030712"},zinc:{50:"#fafafa",100:"#f4f4f5",200:"#e4e4e7",300:"#d4d4d8",400:"#a1a1aa",500:"#71717a",600:"#52525b",700:"#3f3f46",800:"#27272a",900:"#18181b",950:"#09090b"},neutral:{50:"#fafafa",100:"#f5f5f5",200:"#e5e5e5",300:"#d4d4d4",400:"#a3a3a3",500:"#737373",600:"#525252",700:"#404040",800:"#262626",900:"#171717",950:"#0a0a0a"},stone:{50:"#fafaf9",100:"#f5f5f4",200:"#e7e5e4",300:"#d6d3d1",400:"#a8a29e",500:"#78716c",600:"#57534e",700:"#44403c",800:"#292524",900:"#1c1917",950:"#0c0a09"},red:{50:"#fef2f2",100:"#fee2e2",200:"#fecaca",300:"#fca5a5",400:"#f87171",500:"#ef4444",600:"#dc2626",700:"#b91c1c",800:"#991b1b",900:"#7f1d1d",950:"#450a0a"},orange:{50:"#fff7ed",100:"#ffedd5",200:"#fed7aa",300:"#fdba74",400:"#fb923c",500:"#f97316",600:"#ea580c",700:"#c2410c",800:"#9a3412",900:"#7c2d12",950:"#431407"},amber:{50:"#fffbeb",100:"#fef3c7",200:"#fde68a",300:"#fcd34d",400:"#fbbf24",500:"#f59e0b",600:"#d97706",700:"#b45309",800:"#92400e",900:"#78350f",950:"#451a03"},yellow:{50:"#fefce8",100:"#fef9c3",200:"#fef08a",300:"#fde047",400:"#facc15",500:"#eab308",600:"#ca8a04",700:"#a16207",800:"#854d0e",900:"#713f12",950:"#422006"},lime:{50:"#f7fee7",100:"#ecfccb",200:"#d9f99d",300:"#bef264",400:"#a3e635",500:"#84cc16",600:"#65a30d",700:"#4d7c0f",800:"#3f6212",900:"#365314",950:"#1a2e05"},green:{50:"#f0fdf4",100:"#dcfce7",200:"#bbf7d0",300:"#86efac",400:"#4ade80",500:"#22c55e",600:"#16a34a",700:"#15803d",800:"#166534",900:"#14532d",950:"#052e16"},emerald:{50:"#ecfdf5",100:"#d1fae5",200:"#a7f3d0",300:"#6ee7b7",400:"#34d399",500:"#10b981",600:"#059669",700:"#047857",800:"#065f46",900:"#064e3b",950:"#022c22"},teal:{50:"#f0fdfa",100:"#ccfbf1",200:"#99f6e4",300:"#5eead4",400:"#2dd4bf",500:"#14b8a6",600:"#0d9488",700:"#0f766e",800:"#115e59",900:"#134e4a",950:"#042f2e"},cyan:{50:"#ecfeff",100:"#cffafe",200:"#a5f3fc",300:"#67e8f9",400:"#22d3ee",500:"#06b6d4",600:"#0891b2",700:"#0e7490",800:"#155e75",900:"#164e63",950:"#083344"},sky:{50:"#f0f9ff",100:"#e0f2fe",200:"#bae6fd",300:"#7dd3fc",400:"#38bdf8",500:"#0ea5e9",600:"#0284c7",700:"#0369a1",800:"#075985",900:"#0c4a6e",950:"#082f49"},blue:{50:"#eff6ff",100:"#dbeafe",200:"#bfdbfe",300:"#93c5fd",400:"#60a5fa",500:"#3b82f6",600:"#2563eb",700:"#1d4ed8",800:"#1e40af",900:"#1e3a8a",950:"#172554"},indigo:{50:"#eef2ff",100:"#e0e7ff",200:"#c7d2fe",300:"#a5b4fc",400:"#818cf8",500:"#6366f1",600:"#4f46e5",700:"#4338ca",800:"#3730a3",900:"#312e81",950:"#1e1b4b"},violet:{50:"#f5f3ff",100:"#ede9fe",200:"#ddd6fe",300:"#c4b5fd",400:"#a78bfa",500:"#8b5cf6",600:"#7c3aed",700:"#6d28d9",800:"#5b21b6",900:"#4c1d95",950:"#2e1065"},purple:{50:"#faf5ff",100:"#f3e8ff",200:"#e9d5ff",300:"#d8b4fe",400:"#c084fc",500:"#a855f7",600:"#9333ea",700:"#7e22ce",800:"#6b21a8",900:"#581c87",950:"#3b0764"},fuchsia:{50:"#fdf4ff",100:"#fae8ff",200:"#f5d0fe",300:"#f0abfc",400:"#e879f9",500:"#d946ef",600:"#c026d3",700:"#a21caf",800:"#86198f",900:"#701a75",950:"#4a044e"},pink:{50:"#fdf2f8",100:"#fce7f3",200:"#fbcfe8",300:"#f9a8d4",400:"#f472b6",500:"#ec4899",600:"#db2777",700:"#be185d",800:"#9d174d",900:"#831843",950:"#500724"},rose:{50:"#fff1f2",100:"#ffe4e6",200:"#fecdd3",300:"#fda4af",400:"#fb7185",500:"#f43f5e",600:"#e11d48",700:"#be123c",800:"#9f1239",900:"#881337",950:"#4c0519"},get lightBlue(){return ar({version:"v2.2",from:"lightBlue",to:"sky"}),this.sky},get warmGray(){return ar({version:"v3.0",from:"warmGray",to:"stone"}),this.stone},get trueGray(){return ar({version:"v3.0",from:"trueGray",to:"neutral"}),this.neutral},get coolGray(){return ar({version:"v3.0",from:"coolGray",to:"gray"}),this.gray},get blueGray(){return ar({version:"v3.0",from:"blueGray",to:"slate"}),this.slate}}});function es(i,...e){for(let t of e){for(let r in t)i?.hasOwnProperty?.(r)||(i[r]=t[r]);for(let r of Object.getOwnPropertySymbols(t))i?.hasOwnProperty?.(r)||(i[r]=t[r])}return i}var vu=C(()=>{l()});function Ke(i){if(Array.isArray(i))return i;let e=i.split("[").length-1,t=i.split("]").length-1;if(e!==t)throw new Error(`Path is invalid. Has unbalanced brackets: ${i}`);return i.split(/\.(?![^\[]*\])|[\[\]]/g).filter(Boolean)}var pi=C(()=>{l()});function K(i,e){return di.future.includes(e)?i.future==="all"||(i?.future?.[e]??xu[e]??!1):di.experimental.includes(e)?i.experimental==="all"||(i?.experimental?.[e]??xu[e]??!1):!1}function ku(i){return i.experimental==="all"?di.experimental:Object.keys(i?.experimental??{}).filter(e=>di.experimental.includes(e)&&i.experimental[e])}function Su(i){if(h.env.JEST_WORKER_ID===void 0&&ku(i).length>0){let e=ku(i).map(t=>_e.yellow(t)).join(", ");F.warn("experimental-flags-enabled",[`You have enabled experimental features: ${e}`,"Experimental features in Tailwind CSS are not covered by semver, may introduce breaking changes, and can change at any time."])}}var xu,di,ze=C(()=>{l();ci();Oe();xu={optimizeUniversalDefaults:!1,generalizedModifiers:!0,disableColorOpacityUtilitiesByDefault:!1,relativeContentPathsByDefault:!1},di={future:["hoverOnlyWhenSupported","respectDefaultRingColorOpacity","disableColorOpacityUtilitiesByDefault","relativeContentPathsByDefault"],experimental:["optimizeUniversalDefaults","generalizedModifiers"]}});function Cu(i){(()=>{if(i.purge||!i.content||!Array.isArray(i.content)&&!(typeof i.content=="object"&&i.content!==null))return!1;if(Array.isArray(i.content))return i.content.every(t=>typeof t=="string"?!0:!(typeof t?.raw!="string"||t?.extension&&typeof t?.extension!="string"));if(typeof i.content=="object"&&i.content!==null){if(Object.keys(i.content).some(t=>!["files","relative","extract","transform"].includes(t)))return!1;if(Array.isArray(i.content.files)){if(!i.content.files.every(t=>typeof t=="string"?!0:!(typeof t?.raw!="string"||t?.extension&&typeof t?.extension!="string")))return!1;if(typeof i.content.extract=="object"){for(let t of Object.values(i.content.extract))if(typeof t!="function")return!1}else if(!(i.content.extract===void 0||typeof i.content.extract=="function"))return!1;if(typeof i.content.transform=="object"){for(let t of Object.values(i.content.transform))if(typeof t!="function")return!1}else if(!(i.content.transform===void 0||typeof i.content.transform=="function"))return!1;if(typeof i.content.relative!="boolean"&&typeof i.content.relative!="undefined")return!1}return!0}return!1})()||F.warn("purge-deprecation",["The `purge`/`content` options have changed in Tailwind CSS v3.0.","Update your configuration file to eliminate this warning.","https://tailwindcss.com/docs/upgrade-guide#configure-content-sources"]),i.safelist=(()=>{let{content:t,purge:r,safelist:n}=i;return Array.isArray(n)?n:Array.isArray(t?.safelist)?t.safelist:Array.isArray(r?.safelist)?r.safelist:Array.isArray(r?.options?.safelist)?r.options.safelist:[]})(),i.blocklist=(()=>{let{blocklist:t}=i;if(Array.isArray(t)){if(t.every(r=>typeof r=="string"))return t;F.warn("blocklist-invalid",["The `blocklist` option must be an array of strings.","https://tailwindcss.com/docs/content-configuration#discarding-classes"])}return[]})(),typeof i.prefix=="function"?(F.warn("prefix-function",["As of Tailwind CSS v3.0, `prefix` cannot be a function.","Update `prefix` in your configuration to be a string to eliminate this warning.","https://tailwindcss.com/docs/upgrade-guide#prefix-cannot-be-a-function"]),i.prefix=""):i.prefix=i.prefix??"",i.content={relative:(()=>{let{content:t}=i;return t?.relative?t.relative:K(i,"relativeContentPathsByDefault")})(),files:(()=>{let{content:t,purge:r}=i;return Array.isArray(r)?r:Array.isArray(r?.content)?r.content:Array.isArray(t)?t:Array.isArray(t?.content)?t.content:Array.isArray(t?.files)?t.files:[]})(),extract:(()=>{let t=(()=>i.purge?.extract?i.purge.extract:i.content?.extract?i.content.extract:i.purge?.extract?.DEFAULT?i.purge.extract.DEFAULT:i.content?.extract?.DEFAULT?i.content.extract.DEFAULT:i.purge?.options?.extractors?i.purge.options.extractors:i.content?.options?.extractors?i.content.options.extractors:{})(),r={},n=(()=>{if(i.purge?.options?.defaultExtractor)return i.purge.options.defaultExtractor;if(i.content?.options?.defaultExtractor)return i.content.options.defaultExtractor})();if(n!==void 0&&(r.DEFAULT=n),typeof t=="function")r.DEFAULT=t;else if(Array.isArray(t))for(let{extensions:a,extractor:s}of t??[])for(let o of a)r[o]=s;else typeof t=="object"&&t!==null&&Object.assign(r,t);return r})(),transform:(()=>{let t=(()=>i.purge?.transform?i.purge.transform:i.content?.transform?i.content.transform:i.purge?.transform?.DEFAULT?i.purge.transform.DEFAULT:i.content?.transform?.DEFAULT?i.content.transform.DEFAULT:{})(),r={};return typeof t=="function"&&(r.DEFAULT=t),typeof t=="object"&&t!==null&&Object.assign(r,t),r})()};for(let t of i.content.files)if(typeof t=="string"&&/{([^,]*?)}/g.test(t)){F.warn("invalid-glob-braces",[`The glob pattern ${Xn(t)} in your Tailwind CSS configuration is invalid.`,`Update it to ${Xn(t.replace(/{([^,]*?)}/g,"$1"))} to silence this warning.`]);break}return i}var Au=C(()=>{l();ze();Oe()});function ie(i){if(Object.prototype.toString.call(i)!=="[object Object]")return!1;let e=Object.getPrototypeOf(i);return e===null||Object.getPrototypeOf(e)===null}var St=C(()=>{l()});function Ze(i){return Array.isArray(i)?i.map(e=>Ze(e)):typeof i=="object"&&i!==null?Object.fromEntries(Object.entries(i).map(([e,t])=>[e,Ze(t)])):i}var hi=C(()=>{l()});function gt(i){return i.replace(/\\,/g,"\\2c ")}var mi=C(()=>{l()});var ts,_u=C(()=>{l();ts={aliceblue:[240,248,255],antiquewhite:[250,235,215],aqua:[0,255,255],aquamarine:[127,255,212],azure:[240,255,255],beige:[245,245,220],bisque:[255,228,196],black:[0,0,0],blanchedalmond:[255,235,205],blue:[0,0,255],blueviolet:[138,43,226],brown:[165,42,42],burlywood:[222,184,135],cadetblue:[95,158,160],chartreuse:[127,255,0],chocolate:[210,105,30],coral:[255,127,80],cornflowerblue:[100,149,237],cornsilk:[255,248,220],crimson:[220,20,60],cyan:[0,255,255],darkblue:[0,0,139],darkcyan:[0,139,139],darkgoldenrod:[184,134,11],darkgray:[169,169,169],darkgreen:[0,100,0],darkgrey:[169,169,169],darkkhaki:[189,183,107],darkmagenta:[139,0,139],darkolivegreen:[85,107,47],darkorange:[255,140,0],darkorchid:[153,50,204],darkred:[139,0,0],darksalmon:[233,150,122],darkseagreen:[143,188,143],darkslateblue:[72,61,139],darkslategray:[47,79,79],darkslategrey:[47,79,79],darkturquoise:[0,206,209],darkviolet:[148,0,211],deeppink:[255,20,147],deepskyblue:[0,191,255],dimgray:[105,105,105],dimgrey:[105,105,105],dodgerblue:[30,144,255],firebrick:[178,34,34],floralwhite:[255,250,240],forestgreen:[34,139,34],fuchsia:[255,0,255],gainsboro:[220,220,220],ghostwhite:[248,248,255],gold:[255,215,0],goldenrod:[218,165,32],gray:[128,128,128],green:[0,128,0],greenyellow:[173,255,47],grey:[128,128,128],honeydew:[240,255,240],hotpink:[255,105,180],indianred:[205,92,92],indigo:[75,0,130],ivory:[255,255,240],khaki:[240,230,140],lavender:[230,230,250],lavenderblush:[255,240,245],lawngreen:[124,252,0],lemonchiffon:[255,250,205],lightblue:[173,216,230],lightcoral:[240,128,128],lightcyan:[224,255,255],lightgoldenrodyellow:[250,250,210],lightgray:[211,211,211],lightgreen:[144,238,144],lightgrey:[211,211,211],lightpink:[255,182,193],lightsalmon:[255,160,122],lightseagreen:[32,178,170],lightskyblue:[135,206,250],lightslategray:[119,136,153],lightslategrey:[119,136,153],lightsteelblue:[176,196,222],lightyellow:[255,255,224],lime:[0,255,0],limegreen:[50,205,50],linen:[250,240,230],magenta:[255,0,255],maroon:[128,0,0],mediumaquamarine:[102,205,170],mediumblue:[0,0,205],mediumorchid:[186,85,211],mediumpurple:[147,112,219],mediumseagreen:[60,179,113],mediumslateblue:[123,104,238],mediumspringgreen:[0,250,154],mediumturquoise:[72,209,204],mediumvioletred:[199,21,133],midnightblue:[25,25,112],mintcream:[245,255,250],mistyrose:[255,228,225],moccasin:[255,228,181],navajowhite:[255,222,173],navy:[0,0,128],oldlace:[253,245,230],olive:[128,128,0],olivedrab:[107,142,35],orange:[255,165,0],orangered:[255,69,0],orchid:[218,112,214],palegoldenrod:[238,232,170],palegreen:[152,251,152],paleturquoise:[175,238,238],palevioletred:[219,112,147],papayawhip:[255,239,213],peachpuff:[255,218,185],peru:[205,133,63],pink:[255,192,203],plum:[221,160,221],powderblue:[176,224,230],purple:[128,0,128],rebeccapurple:[102,51,153],red:[255,0,0],rosybrown:[188,143,143],royalblue:[65,105,225],saddlebrown:[139,69,19],salmon:[250,128,114],sandybrown:[244,164,96],seagreen:[46,139,87],seashell:[255,245,238],sienna:[160,82,45],silver:[192,192,192],skyblue:[135,206,235],slateblue:[106,90,205],slategray:[112,128,144],slategrey:[112,128,144],snow:[255,250,250],springgreen:[0,255,127],steelblue:[70,130,180],tan:[210,180,140],teal:[0,128,128],thistle:[216,191,216],tomato:[255,99,71],turquoise:[64,224,208],violet:[238,130,238],wheat:[245,222,179],white:[255,255,255],whitesmoke:[245,245,245],yellow:[255,255,0],yellowgreen:[154,205,50]}});function or(i,{loose:e=!1}={}){if(typeof i!="string")return null;if(i=i.trim(),i==="transparent")return{mode:"rgb",color:["0","0","0"],alpha:"0"};if(i in ts)return{mode:"rgb",color:ts[i].map(a=>a.toString())};let t=i.replace(_b,(a,s,o,u,c)=>["#",s,s,o,o,u,u,c?c+c:""].join("")).match(Ab);if(t!==null)return{mode:"rgb",color:[parseInt(t[1],16),parseInt(t[2],16),parseInt(t[3],16)].map(a=>a.toString()),alpha:t[4]?(parseInt(t[4],16)/255).toString():void 0};let r=i.match(Ob)??i.match(Eb);if(r===null)return null;let n=[r[2],r[3],r[4]].filter(Boolean).map(a=>a.toString());return n.length===2&&n[0].startsWith("var(")?{mode:r[1],color:[n[0]],alpha:n[1]}:!e&&n.length!==3||n.length<3&&!n.some(a=>/^var\(.*?\)$/.test(a))?null:{mode:r[1],color:n,alpha:r[5]?.toString?.()}}function rs({mode:i,color:e,alpha:t}){let r=t!==void 0;return i==="rgba"||i==="hsla"?`${i}(${e.join(", ")}${r?`, ${t}`:""})`:`${i}(${e.join(" ")}${r?` / ${t}`:""})`}var Ab,_b,et,gi,Ou,tt,Ob,Eb,is=C(()=>{l();_u();Ab=/^#([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})?$/i,_b=/^#([a-f\d])([a-f\d])([a-f\d])([a-f\d])?$/i,et=/(?:\d+|\d*\.\d+)%?/,gi=/(?:\s*,\s*|\s+)/,Ou=/\s*[,/]\s*/,tt=/var\(--(?:[^ )]*?)(?:,(?:[^ )]*?|var\(--[^ )]*?\)))?\)/,Ob=new RegExp(`^(rgba?)\\(\\s*(${et.source}|${tt.source})(?:${gi.source}(${et.source}|${tt.source}))?(?:${gi.source}(${et.source}|${tt.source}))?(?:${Ou.source}(${et.source}|${tt.source}))?\\s*\\)$`),Eb=new RegExp(`^(hsla?)\\(\\s*((?:${et.source})(?:deg|rad|grad|turn)?|${tt.source})(?:${gi.source}(${et.source}|${tt.source}))?(?:${gi.source}(${et.source}|${tt.source}))?(?:${Ou.source}(${et.source}|${tt.source}))?\\s*\\)$`)});function Ie(i,e,t){if(typeof i=="function")return i({opacityValue:e});let r=or(i,{loose:!0});return r===null?t:rs({...r,alpha:e})}function se({color:i,property:e,variable:t}){let r=[].concat(e);if(typeof i=="function")return{[t]:"1",...Object.fromEntries(r.map(a=>[a,i({opacityVariable:t,opacityValue:`var(${t})`})]))};let n=or(i);return n===null?Object.fromEntries(r.map(a=>[a,i])):n.alpha!==void 0?Object.fromEntries(r.map(a=>[a,i])):{[t]:"1",...Object.fromEntries(r.map(a=>[a,rs({...n,alpha:`var(${t})`})]))}}var lr=C(()=>{l();is()});function ae(i,e){let t=[],r=[],n=0,a=!1;for(let s=0;s<i.length;s++){let o=i[s];t.length===0&&o===e[0]&&!a&&(e.length===1||i.slice(s,s+e.length)===e)&&(r.push(i.slice(n,s)),n=s+e.length),a?a=!1:o==="\\"&&(a=!0),o==="("||o==="["||o==="{"?t.push(o):(o===")"&&t[t.length-1]==="("||o==="]"&&t[t.length-1]==="["||o==="}"&&t[t.length-1]==="{")&&t.pop()}return r.push(i.slice(n)),r}var Ct=C(()=>{l()});function yi(i){return ae(i,",").map(t=>{let r=t.trim(),n={raw:r},a=r.split(Pb),s=new Set;for(let o of a)Eu.lastIndex=0,!s.has("KEYWORD")&&Tb.has(o)?(n.keyword=o,s.add("KEYWORD")):Eu.test(o)?s.has("X")?s.has("Y")?s.has("BLUR")?s.has("SPREAD")||(n.spread=o,s.add("SPREAD")):(n.blur=o,s.add("BLUR")):(n.y=o,s.add("Y")):(n.x=o,s.add("X")):n.color?(n.unknown||(n.unknown=[]),n.unknown.push(o)):n.color=o;return n.valid=n.x!==void 0&&n.y!==void 0,n})}function Tu(i){return i.map(e=>e.valid?[e.keyword,e.x,e.y,e.blur,e.spread,e.color].filter(Boolean).join(" "):e.raw).join(", ")}var Tb,Pb,Eu,ns=C(()=>{l();Ct();Tb=new Set(["inset","inherit","initial","revert","unset"]),Pb=/\ +(?![^(]*\))/g,Eu=/^-?(\d+|\.\d+)(.*?)$/g});function ss(i){return Db.some(e=>new RegExp(`^${e}\\(.*\\)`).test(i))}function N(i,e=null,t=!0){let r=e&&Ib.has(e.property);return i.startsWith("--")&&!r?`var(${i})`:i.includes("url(")?i.split(/(url\(.*?\))/g).filter(Boolean).map(n=>/^url\(.*?\)$/.test(n)?n:N(n,e,!1)).join(""):(i=i.replace(/([^\\])_+/g,(n,a)=>a+" ".repeat(n.length-1)).replace(/^_/g," ").replace(/\\_/g,"_"),t&&(i=i.trim()),i=qb(i),i)}function qb(i){let e=["theme"],t=["min-content","max-content","fit-content","safe-area-inset-top","safe-area-inset-right","safe-area-inset-bottom","safe-area-inset-left","titlebar-area-x","titlebar-area-y","titlebar-area-width","titlebar-area-height","keyboard-inset-top","keyboard-inset-right","keyboard-inset-bottom","keyboard-inset-left","keyboard-inset-width","keyboard-inset-height","radial-gradient","linear-gradient","conic-gradient","repeating-radial-gradient","repeating-linear-gradient","repeating-conic-gradient"];return i.replace(/(calc|min|max|clamp)\(.+\)/g,r=>{let n="";function a(){let s=n.trimEnd();return s[s.length-1]}for(let s=0;s<r.length;s++){let o=function(f){return f.split("").every((d,p)=>r[s+p]===d)},u=function(f){let d=1/0;for(let m of f){let b=r.indexOf(m,s);b!==-1&&b<d&&(d=b)}let p=r.slice(s,d);return s+=p.length-1,p},c=r[s];if(o("var"))n+=u([")",","]);else if(t.some(f=>o(f))){let f=t.find(d=>o(d));n+=f,s+=f.length-1}else e.some(f=>o(f))?n+=u([")"]):o("[")?n+=u(["]"]):["+","-","*","/"].includes(c)&&!["(","+","-","*","/",","].includes(a())?n+=` ${c} `:n+=c}return n.replace(/\s+/g," ")})}function as(i){return i.startsWith("url(")}function os(i){return!isNaN(Number(i))||ss(i)}function ur(i){return i.endsWith("%")&&os(i.slice(0,-1))||ss(i)}function fr(i){return i==="0"||new RegExp(`^[+-]?[0-9]*.?[0-9]+(?:[eE][+-]?[0-9]+)?${Mb}$`).test(i)||ss(i)}function Pu(i){return Bb.has(i)}function Du(i){let e=yi(N(i));for(let t of e)if(!t.valid)return!1;return!0}function Iu(i){let e=0;return ae(i,"_").every(r=>(r=N(r),r.startsWith("var(")?!0:or(r,{loose:!0})!==null?(e++,!0):!1))?e>0:!1}function qu(i){let e=0;return ae(i,",").every(r=>(r=N(r),r.startsWith("var(")?!0:as(r)||Nb(r)||["element(","image(","cross-fade(","image-set("].some(n=>r.startsWith(n))?(e++,!0):!1))?e>0:!1}function Nb(i){i=N(i);for(let e of Fb)if(i.startsWith(`${e}(`))return!0;return!1}function Ru(i){let e=0;return ae(i,"_").every(r=>(r=N(r),r.startsWith("var(")?!0:Lb.has(r)||fr(r)||ur(r)?(e++,!0):!1))?e>0:!1}function Mu(i){let e=0;return ae(i,",").every(r=>(r=N(r),r.startsWith("var(")?!0:r.includes(" ")&&!/(['"])([^"']+)\1/g.test(r)||/^\d/g.test(r)?!1:(e++,!0)))?e>0:!1}function Bu(i){return $b.has(i)}function Fu(i){return jb.has(i)}function Nu(i){return zb.has(i)}var Db,Ib,Rb,Mb,Bb,Fb,Lb,$b,jb,zb,cr=C(()=>{l();is();ns();Ct();Db=["min","max","clamp","calc"];Ib=new Set(["scroll-timeline-name","timeline-scope","view-timeline-name","font-palette","scroll-timeline","animation-timeline","view-timeline"]);Rb=["cm","mm","Q","in","pc","pt","px","em","ex","ch","rem","lh","rlh","vw","vh","vmin","vmax","vb","vi","svw","svh","lvw","lvh","dvw","dvh","cqw","cqh","cqi","cqb","cqmin","cqmax"],Mb=`(?:${Rb.join("|")})`;Bb=new Set(["thin","medium","thick"]);Fb=new Set(["conic-gradient","linear-gradient","radial-gradient","repeating-conic-gradient","repeating-linear-gradient","repeating-radial-gradient"]);Lb=new Set(["center","top","right","bottom","left"]);$b=new Set(["serif","sans-serif","monospace","cursive","fantasy","system-ui","ui-serif","ui-sans-serif","ui-monospace","ui-rounded","math","emoji","fangsong"]);jb=new Set(["xx-small","x-small","small","medium","large","x-large","xx-large","xxx-large"]);zb=new Set(["larger","smaller"])});function Lu(i){let e=["cover","contain"];return ae(i,",").every(t=>{let r=ae(t,"_").filter(Boolean);return r.length===1&&e.includes(r[0])?!0:r.length!==1&&r.length!==2?!1:r.every(n=>fr(n)||ur(n)||n==="auto")})}var $u=C(()=>{l();cr();Ct()});function ju(i,e){i.walkClasses(t=>{t.value=e(t.value),t.raws&&t.raws.value&&(t.raws.value=gt(t.raws.value))})}function zu(i,e){if(!rt(i))return;let t=i.slice(1,-1);if(!!e(t))return N(t)}function Vb(i,e={},t){let r=e[i];if(r!==void 0)return Xe(r);if(rt(i)){let n=zu(i,t);return n===void 0?void 0:Xe(n)}}function wi(i,e={},{validate:t=()=>!0}={}){let r=e.values?.[i];return r!==void 0?r:e.supportsNegativeValues&&i.startsWith("-")?Vb(i.slice(1),e.values,t):zu(i,t)}function rt(i){return i.startsWith("[")&&i.endsWith("]")}function Vu(i){let e=i.lastIndexOf("/"),t=i.lastIndexOf("[",e),r=i.indexOf("]",e);return i[e-1]==="]"||i[e+1]==="["||t!==-1&&r!==-1&&t<e&&e<r&&(e=i.lastIndexOf("/",t)),e===-1||e===i.length-1?[i,void 0]:rt(i)&&!i.includes("]/[")?[i,void 0]:[i.slice(0,e),i.slice(e+1)]}function At(i){if(typeof i=="string"&&i.includes("<alpha-value>")){let e=i;return({opacityValue:t=1})=>e.replace(/<alpha-value>/g,t)}return i}function Uu(i){return N(i.slice(1,-1))}function Ub(i,e={},{tailwindConfig:t={}}={}){if(e.values?.[i]!==void 0)return At(e.values?.[i]);let[r,n]=Vu(i);if(n!==void 0){let a=e.values?.[r]??(rt(r)?r.slice(1,-1):void 0);return a===void 0?void 0:(a=At(a),rt(n)?Ie(a,Uu(n)):t.theme?.opacity?.[n]===void 0?void 0:Ie(a,t.theme.opacity[n]))}return wi(i,e,{validate:Iu})}function Wb(i,e={}){return e.values?.[i]}function me(i){return(e,t)=>wi(e,t,{validate:i})}function Gb(i,e){let t=i.indexOf(e);return t===-1?[void 0,i]:[i.slice(0,t),i.slice(t+1)]}function us(i,e,t,r){if(t.values&&e in t.values)for(let{type:a}of i??[]){let s=ls[a](e,t,{tailwindConfig:r});if(s!==void 0)return[s,a,null]}if(rt(e)){let a=e.slice(1,-1),[s,o]=Gb(a,":");if(!/^[\w-_]+$/g.test(s))o=a;else if(s!==void 0&&!Wu.includes(s))return[];if(o.length>0&&Wu.includes(s))return[wi(`[${o}]`,t),s,null]}let n=fs(i,e,t,r);for(let a of n)return a;return[]}function*fs(i,e,t,r){let n=K(r,"generalizedModifiers"),[a,s]=Vu(e);if(n&&t.modifiers!=null&&(t.modifiers==="any"||typeof t.modifiers=="object"&&(s&&rt(s)||s in t.modifiers))||(a=e,s=void 0),s!==void 0&&a===""&&(a="DEFAULT"),s!==void 0&&typeof t.modifiers=="object"){let u=t.modifiers?.[s]??null;u!==null?s=u:rt(s)&&(s=Uu(s))}for(let{type:u}of i??[]){let c=ls[u](a,t,{tailwindConfig:r});c!==void 0&&(yield[c,u,s??null])}}var ls,Wu,pr=C(()=>{l();mi();lr();cr();fi();$u();ze();ls={any:wi,color:Ub,url:me(as),image:me(qu),length:me(fr),percentage:me(ur),position:me(Ru),lookup:Wb,"generic-name":me(Bu),"family-name":me(Mu),number:me(os),"line-width":me(Pu),"absolute-size":me(Fu),"relative-size":me(Nu),shadow:me(Du),size:me(Lu)},Wu=Object.keys(ls)});function L(i){return typeof i=="function"?i({}):i}var cs=C(()=>{l()});function _t(i){return typeof i=="function"}function dr(i,...e){let t=e.pop();for(let r of e)for(let n in r){let a=t(i[n],r[n]);a===void 0?ie(i[n])&&ie(r[n])?i[n]=dr({},i[n],r[n],t):i[n]=r[n]:i[n]=a}return i}function Hb(i,...e){return _t(i)?i(...e):i}function Yb(i){return i.reduce((e,{extend:t})=>dr(e,t,(r,n)=>r===void 0?[n]:Array.isArray(r)?[n,...r]:[n,r]),{})}function Qb(i){return{...i.reduce((e,t)=>es(e,t),{}),extend:Yb(i)}}function Gu(i,e){if(Array.isArray(i)&&ie(i[0]))return i.concat(e);if(Array.isArray(e)&&ie(e[0])&&ie(i))return[i,...e];if(Array.isArray(e))return e}function Jb({extend:i,...e}){return dr(e,i,(t,r)=>!_t(t)&&!r.some(_t)?dr({},t,...r,Gu):(n,a)=>dr({},...[t,...r].map(s=>Hb(s,n,a)),Gu))}function*Xb(i){let e=Ke(i);if(e.length===0||(yield e,Array.isArray(i)))return;let t=/^(.*?)\s*\/\s*([^/]+)$/,r=i.match(t);if(r!==null){let[,n,a]=r,s=Ke(n);s.alpha=a,yield s}}function Kb(i){let e=(t,r)=>{for(let n of Xb(t)){let a=0,s=i;for(;s!=null&&a<n.length;)s=s[n[a++]],s=_t(s)&&(n.alpha===void 0||a<=n.length-1)?s(e,ps):s;if(s!==void 0){if(n.alpha!==void 0){let o=At(s);return Ie(o,n.alpha,L(o))}return ie(s)?Ze(s):s}}return r};return Object.assign(e,{theme:e,...ps}),Object.keys(i).reduce((t,r)=>(t[r]=_t(i[r])?i[r](e,ps):i[r],t),{})}function Hu(i){let e=[];return i.forEach(t=>{e=[...e,t];let r=t?.plugins??[];r.length!==0&&r.forEach(n=>{n.__isOptionsFunction&&(n=n()),e=[...e,...Hu([n?.config??{}])]})}),e}function Zb(i){return[...i].reduceRight((t,r)=>_t(r)?r({corePlugins:t}):mu(r,t),du)}function e0(i){return[...i].reduceRight((t,r)=>[...t,...r],[])}function ds(i){let e=[...Hu(i),{prefix:"",important:!1,separator:":"}];return Cu(es({theme:Kb(Jb(Qb(e.map(t=>t?.theme??{})))),corePlugins:Zb(e.map(t=>t.corePlugins)),plugins:e0(i.map(t=>t?.plugins??[]))},...e))}var ps,Yu=C(()=>{l();fi();hu();gu();Zn();vu();pi();Au();St();hi();pr();lr();cs();ps={colors:Kn,negative(i){return Object.keys(i).filter(e=>i[e]!=="0").reduce((e,t)=>{let r=Xe(i[t]);return r!==void 0&&(e[`-${t}`]=r),e},{})},breakpoints(i){return Object.keys(i).filter(e=>typeof i[e]=="string").reduce((e,t)=>({...e,[`screen-${t}`]:i[t]}),{})}}});var bi=v((qE,Qu)=>{l();Qu.exports={content:[],presets:[],darkMode:"media",theme:{accentColor:({theme:i})=>({...i("colors"),auto:"auto"}),animation:{none:"none",spin:"spin 1s linear infinite",ping:"ping 1s cubic-bezier(0, 0, 0.2, 1) infinite",pulse:"pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite",bounce:"bounce 1s infinite"},aria:{busy:'busy="true"',checked:'checked="true"',disabled:'disabled="true"',expanded:'expanded="true"',hidden:'hidden="true"',pressed:'pressed="true"',readonly:'readonly="true"',required:'required="true"',selected:'selected="true"'},aspectRatio:{auto:"auto",square:"1 / 1",video:"16 / 9"},backdropBlur:({theme:i})=>i("blur"),backdropBrightness:({theme:i})=>i("brightness"),backdropContrast:({theme:i})=>i("contrast"),backdropGrayscale:({theme:i})=>i("grayscale"),backdropHueRotate:({theme:i})=>i("hueRotate"),backdropInvert:({theme:i})=>i("invert"),backdropOpacity:({theme:i})=>i("opacity"),backdropSaturate:({theme:i})=>i("saturate"),backdropSepia:({theme:i})=>i("sepia"),backgroundColor:({theme:i})=>i("colors"),backgroundImage:{none:"none","gradient-to-t":"linear-gradient(to top, var(--tw-gradient-stops))","gradient-to-tr":"linear-gradient(to top right, var(--tw-gradient-stops))","gradient-to-r":"linear-gradient(to right, var(--tw-gradient-stops))","gradient-to-br":"linear-gradient(to bottom right, var(--tw-gradient-stops))","gradient-to-b":"linear-gradient(to bottom, var(--tw-gradient-stops))","gradient-to-bl":"linear-gradient(to bottom left, var(--tw-gradient-stops))","gradient-to-l":"linear-gradient(to left, var(--tw-gradient-stops))","gradient-to-tl":"linear-gradient(to top left, var(--tw-gradient-stops))"},backgroundOpacity:({theme:i})=>i("opacity"),backgroundPosition:{bottom:"bottom",center:"center",left:"left","left-bottom":"left bottom","left-top":"left top",right:"right","right-bottom":"right bottom","right-top":"right top",top:"top"},backgroundSize:{auto:"auto",cover:"cover",contain:"contain"},blur:{0:"0",none:"0",sm:"4px",DEFAULT:"8px",md:"12px",lg:"16px",xl:"24px","2xl":"40px","3xl":"64px"},borderColor:({theme:i})=>({...i("colors"),DEFAULT:i("colors.gray.200","currentColor")}),borderOpacity:({theme:i})=>i("opacity"),borderRadius:{none:"0px",sm:"0.125rem",DEFAULT:"0.25rem",md:"0.375rem",lg:"0.5rem",xl:"0.75rem","2xl":"1rem","3xl":"1.5rem",full:"9999px"},borderSpacing:({theme:i})=>({...i("spacing")}),borderWidth:{DEFAULT:"1px",0:"0px",2:"2px",4:"4px",8:"8px"},boxShadow:{sm:"0 1px 2px 0 rgb(0 0 0 / 0.05)",DEFAULT:"0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)",md:"0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)",lg:"0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)",xl:"0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)","2xl":"0 25px 50px -12px rgb(0 0 0 / 0.25)",inner:"inset 0 2px 4px 0 rgb(0 0 0 / 0.05)",none:"none"},boxShadowColor:({theme:i})=>i("colors"),brightness:{0:"0",50:".5",75:".75",90:".9",95:".95",100:"1",105:"1.05",110:"1.1",125:"1.25",150:"1.5",200:"2"},caretColor:({theme:i})=>i("colors"),colors:({colors:i})=>({inherit:i.inherit,current:i.current,transparent:i.transparent,black:i.black,white:i.white,slate:i.slate,gray:i.gray,zinc:i.zinc,neutral:i.neutral,stone:i.stone,red:i.red,orange:i.orange,amber:i.amber,yellow:i.yellow,lime:i.lime,green:i.green,emerald:i.emerald,teal:i.teal,cyan:i.cyan,sky:i.sky,blue:i.blue,indigo:i.indigo,violet:i.violet,purple:i.purple,fuchsia:i.fuchsia,pink:i.pink,rose:i.rose}),columns:{auto:"auto",1:"1",2:"2",3:"3",4:"4",5:"5",6:"6",7:"7",8:"8",9:"9",10:"10",11:"11",12:"12","3xs":"16rem","2xs":"18rem",xs:"20rem",sm:"24rem",md:"28rem",lg:"32rem",xl:"36rem","2xl":"42rem","3xl":"48rem","4xl":"56rem","5xl":"64rem","6xl":"72rem","7xl":"80rem"},container:{},content:{none:"none"},contrast:{0:"0",50:".5",75:".75",100:"1",125:"1.25",150:"1.5",200:"2"},cursor:{auto:"auto",default:"default",pointer:"pointer",wait:"wait",text:"text",move:"move",help:"help","not-allowed":"not-allowed",none:"none","context-menu":"context-menu",progress:"progress",cell:"cell",crosshair:"crosshair","vertical-text":"vertical-text",alias:"alias",copy:"copy","no-drop":"no-drop",grab:"grab",grabbing:"grabbing","all-scroll":"all-scroll","col-resize":"col-resize","row-resize":"row-resize","n-resize":"n-resize","e-resize":"e-resize","s-resize":"s-resize","w-resize":"w-resize","ne-resize":"ne-resize","nw-resize":"nw-resize","se-resize":"se-resize","sw-resize":"sw-resize","ew-resize":"ew-resize","ns-resize":"ns-resize","nesw-resize":"nesw-resize","nwse-resize":"nwse-resize","zoom-in":"zoom-in","zoom-out":"zoom-out"},divideColor:({theme:i})=>i("borderColor"),divideOpacity:({theme:i})=>i("borderOpacity"),divideWidth:({theme:i})=>i("borderWidth"),dropShadow:{sm:"0 1px 1px rgb(0 0 0 / 0.05)",DEFAULT:["0 1px 2px rgb(0 0 0 / 0.1)","0 1px 1px rgb(0 0 0 / 0.06)"],md:["0 4px 3px rgb(0 0 0 / 0.07)","0 2px 2px rgb(0 0 0 / 0.06)"],lg:["0 10px 8px rgb(0 0 0 / 0.04)","0 4px 3px rgb(0 0 0 / 0.1)"],xl:["0 20px 13px rgb(0 0 0 / 0.03)","0 8px 5px rgb(0 0 0 / 0.08)"],"2xl":"0 25px 25px rgb(0 0 0 / 0.15)",none:"0 0 #0000"},fill:({theme:i})=>({none:"none",...i("colors")}),flex:{1:"1 1 0%",auto:"1 1 auto",initial:"0 1 auto",none:"none"},flexBasis:({theme:i})=>({auto:"auto",...i("spacing"),"1/2":"50%","1/3":"33.333333%","2/3":"66.666667%","1/4":"25%","2/4":"50%","3/4":"75%","1/5":"20%","2/5":"40%","3/5":"60%","4/5":"80%","1/6":"16.666667%","2/6":"33.333333%","3/6":"50%","4/6":"66.666667%","5/6":"83.333333%","1/12":"8.333333%","2/12":"16.666667%","3/12":"25%","4/12":"33.333333%","5/12":"41.666667%","6/12":"50%","7/12":"58.333333%","8/12":"66.666667%","9/12":"75%","10/12":"83.333333%","11/12":"91.666667%",full:"100%"}),flexGrow:{0:"0",DEFAULT:"1"},flexShrink:{0:"0",DEFAULT:"1"},fontFamily:{sans:["ui-sans-serif","system-ui","sans-serif",'"Apple Color Emoji"','"Segoe UI Emoji"','"Segoe UI Symbol"','"Noto Color Emoji"'],serif:["ui-serif","Georgia","Cambria",'"Times New Roman"',"Times","serif"],mono:["ui-monospace","SFMono-Regular","Menlo","Monaco","Consolas",'"Liberation Mono"','"Courier New"',"monospace"]},fontSize:{xs:["0.75rem",{lineHeight:"1rem"}],sm:["0.875rem",{lineHeight:"1.25rem"}],base:["1rem",{lineHeight:"1.5rem"}],lg:["1.125rem",{lineHeight:"1.75rem"}],xl:["1.25rem",{lineHeight:"1.75rem"}],"2xl":["1.5rem",{lineHeight:"2rem"}],"3xl":["1.875rem",{lineHeight:"2.25rem"}],"4xl":["2.25rem",{lineHeight:"2.5rem"}],"5xl":["3rem",{lineHeight:"1"}],"6xl":["3.75rem",{lineHeight:"1"}],"7xl":["4.5rem",{lineHeight:"1"}],"8xl":["6rem",{lineHeight:"1"}],"9xl":["8rem",{lineHeight:"1"}]},fontWeight:{thin:"100",extralight:"200",light:"300",normal:"400",medium:"500",semibold:"600",bold:"700",extrabold:"800",black:"900"},gap:({theme:i})=>i("spacing"),gradientColorStops:({theme:i})=>i("colors"),gradientColorStopPositions:{"0%":"0%","5%":"5%","10%":"10%","15%":"15%","20%":"20%","25%":"25%","30%":"30%","35%":"35%","40%":"40%","45%":"45%","50%":"50%","55%":"55%","60%":"60%","65%":"65%","70%":"70%","75%":"75%","80%":"80%","85%":"85%","90%":"90%","95%":"95%","100%":"100%"},grayscale:{0:"0",DEFAULT:"100%"},gridAutoColumns:{auto:"auto",min:"min-content",max:"max-content",fr:"minmax(0, 1fr)"},gridAutoRows:{auto:"auto",min:"min-content",max:"max-content",fr:"minmax(0, 1fr)"},gridColumn:{auto:"auto","span-1":"span 1 / span 1","span-2":"span 2 / span 2","span-3":"span 3 / span 3","span-4":"span 4 / span 4","span-5":"span 5 / span 5","span-6":"span 6 / span 6","span-7":"span 7 / span 7","span-8":"span 8 / span 8","span-9":"span 9 / span 9","span-10":"span 10 / span 10","span-11":"span 11 / span 11","span-12":"span 12 / span 12","span-full":"1 / -1"},gridColumnEnd:{auto:"auto",1:"1",2:"2",3:"3",4:"4",5:"5",6:"6",7:"7",8:"8",9:"9",10:"10",11:"11",12:"12",13:"13"},gridColumnStart:{auto:"auto",1:"1",2:"2",3:"3",4:"4",5:"5",6:"6",7:"7",8:"8",9:"9",10:"10",11:"11",12:"12",13:"13"},gridRow:{auto:"auto","span-1":"span 1 / span 1","span-2":"span 2 / span 2","span-3":"span 3 / span 3","span-4":"span 4 / span 4","span-5":"span 5 / span 5","span-6":"span 6 / span 6","span-7":"span 7 / span 7","span-8":"span 8 / span 8","span-9":"span 9 / span 9","span-10":"span 10 / span 10","span-11":"span 11 / span 11","span-12":"span 12 / span 12","span-full":"1 / -1"},gridRowEnd:{auto:"auto",1:"1",2:"2",3:"3",4:"4",5:"5",6:"6",7:"7",8:"8",9:"9",10:"10",11:"11",12:"12",13:"13"},gridRowStart:{auto:"auto",1:"1",2:"2",3:"3",4:"4",5:"5",6:"6",7:"7",8:"8",9:"9",10:"10",11:"11",12:"12",13:"13"},gridTemplateColumns:{none:"none",subgrid:"subgrid",1:"repeat(1, minmax(0, 1fr))",2:"repeat(2, minmax(0, 1fr))",3:"repeat(3, minmax(0, 1fr))",4:"repeat(4, minmax(0, 1fr))",5:"repeat(5, minmax(0, 1fr))",6:"repeat(6, minmax(0, 1fr))",7:"repeat(7, minmax(0, 1fr))",8:"repeat(8, minmax(0, 1fr))",9:"repeat(9, minmax(0, 1fr))",10:"repeat(10, minmax(0, 1fr))",11:"repeat(11, minmax(0, 1fr))",12:"repeat(12, minmax(0, 1fr))"},gridTemplateRows:{none:"none",subgrid:"subgrid",1:"repeat(1, minmax(0, 1fr))",2:"repeat(2, minmax(0, 1fr))",3:"repeat(3, minmax(0, 1fr))",4:"repeat(4, minmax(0, 1fr))",5:"repeat(5, minmax(0, 1fr))",6:"repeat(6, minmax(0, 1fr))",7:"repeat(7, minmax(0, 1fr))",8:"repeat(8, minmax(0, 1fr))",9:"repeat(9, minmax(0, 1fr))",10:"repeat(10, minmax(0, 1fr))",11:"repeat(11, minmax(0, 1fr))",12:"repeat(12, minmax(0, 1fr))"},height:({theme:i})=>({auto:"auto",...i("spacing"),"1/2":"50%","1/3":"33.333333%","2/3":"66.666667%","1/4":"25%","2/4":"50%","3/4":"75%","1/5":"20%","2/5":"40%","3/5":"60%","4/5":"80%","1/6":"16.666667%","2/6":"33.333333%","3/6":"50%","4/6":"66.666667%","5/6":"83.333333%",full:"100%",screen:"100vh",svh:"100svh",lvh:"100lvh",dvh:"100dvh",min:"min-content",max:"max-content",fit:"fit-content"}),hueRotate:{0:"0deg",15:"15deg",30:"30deg",60:"60deg",90:"90deg",180:"180deg"},inset:({theme:i})=>({auto:"auto",...i("spacing"),"1/2":"50%","1/3":"33.333333%","2/3":"66.666667%","1/4":"25%","2/4":"50%","3/4":"75%",full:"100%"}),invert:{0:"0",DEFAULT:"100%"},keyframes:{spin:{to:{transform:"rotate(360deg)"}},ping:{"75%, 100%":{transform:"scale(2)",opacity:"0"}},pulse:{"50%":{opacity:".5"}},bounce:{"0%, 100%":{transform:"translateY(-25%)",animationTimingFunction:"cubic-bezier(0.8,0,1,1)"},"50%":{transform:"none",animationTimingFunction:"cubic-bezier(0,0,0.2,1)"}}},letterSpacing:{tighter:"-0.05em",tight:"-0.025em",normal:"0em",wide:"0.025em",wider:"0.05em",widest:"0.1em"},lineHeight:{none:"1",tight:"1.25",snug:"1.375",normal:"1.5",relaxed:"1.625",loose:"2",3:".75rem",4:"1rem",5:"1.25rem",6:"1.5rem",7:"1.75rem",8:"2rem",9:"2.25rem",10:"2.5rem"},listStyleType:{none:"none",disc:"disc",decimal:"decimal"},listStyleImage:{none:"none"},margin:({theme:i})=>({auto:"auto",...i("spacing")}),lineClamp:{1:"1",2:"2",3:"3",4:"4",5:"5",6:"6"},maxHeight:({theme:i})=>({...i("spacing"),none:"none",full:"100%",screen:"100vh",svh:"100svh",lvh:"100lvh",dvh:"100dvh",min:"min-content",max:"max-content",fit:"fit-content"}),maxWidth:({theme:i,breakpoints:e})=>({...i("spacing"),none:"none",xs:"20rem",sm:"24rem",md:"28rem",lg:"32rem",xl:"36rem","2xl":"42rem","3xl":"48rem","4xl":"56rem","5xl":"64rem","6xl":"72rem","7xl":"80rem",full:"100%",min:"min-content",max:"max-content",fit:"fit-content",prose:"65ch",...e(i("screens"))}),minHeight:({theme:i})=>({...i("spacing"),full:"100%",screen:"100vh",svh:"100svh",lvh:"100lvh",dvh:"100dvh",min:"min-content",max:"max-content",fit:"fit-content"}),minWidth:({theme:i})=>({...i("spacing"),full:"100%",min:"min-content",max:"max-content",fit:"fit-content"}),objectPosition:{bottom:"bottom",center:"center",left:"left","left-bottom":"left bottom","left-top":"left top",right:"right","right-bottom":"right bottom","right-top":"right top",top:"top"},opacity:{0:"0",5:"0.05",10:"0.1",15:"0.15",20:"0.2",25:"0.25",30:"0.3",35:"0.35",40:"0.4",45:"0.45",50:"0.5",55:"0.55",60:"0.6",65:"0.65",70:"0.7",75:"0.75",80:"0.8",85:"0.85",90:"0.9",95:"0.95",100:"1"},order:{first:"-9999",last:"9999",none:"0",1:"1",2:"2",3:"3",4:"4",5:"5",6:"6",7:"7",8:"8",9:"9",10:"10",11:"11",12:"12"},outlineColor:({theme:i})=>i("colors"),outlineOffset:{0:"0px",1:"1px",2:"2px",4:"4px",8:"8px"},outlineWidth:{0:"0px",1:"1px",2:"2px",4:"4px",8:"8px"},padding:({theme:i})=>i("spacing"),placeholderColor:({theme:i})=>i("colors"),placeholderOpacity:({theme:i})=>i("opacity"),ringColor:({theme:i})=>({DEFAULT:i("colors.blue.500","#3b82f6"),...i("colors")}),ringOffsetColor:({theme:i})=>i("colors"),ringOffsetWidth:{0:"0px",1:"1px",2:"2px",4:"4px",8:"8px"},ringOpacity:({theme:i})=>({DEFAULT:"0.5",...i("opacity")}),ringWidth:{DEFAULT:"3px",0:"0px",1:"1px",2:"2px",4:"4px",8:"8px"},rotate:{0:"0deg",1:"1deg",2:"2deg",3:"3deg",6:"6deg",12:"12deg",45:"45deg",90:"90deg",180:"180deg"},saturate:{0:"0",50:".5",100:"1",150:"1.5",200:"2"},scale:{0:"0",50:".5",75:".75",90:".9",95:".95",100:"1",105:"1.05",110:"1.1",125:"1.25",150:"1.5"},screens:{sm:"640px",md:"768px",lg:"1024px",xl:"1280px","2xl":"1536px"},scrollMargin:({theme:i})=>({...i("spacing")}),scrollPadding:({theme:i})=>i("spacing"),sepia:{0:"0",DEFAULT:"100%"},skew:{0:"0deg",1:"1deg",2:"2deg",3:"3deg",6:"6deg",12:"12deg"},space:({theme:i})=>({...i("spacing")}),spacing:{px:"1px",0:"0px",.5:"0.125rem",1:"0.25rem",1.5:"0.375rem",2:"0.5rem",2.5:"0.625rem",3:"0.75rem",3.5:"0.875rem",4:"1rem",5:"1.25rem",6:"1.5rem",7:"1.75rem",8:"2rem",9:"2.25rem",10:"2.5rem",11:"2.75rem",12:"3rem",14:"3.5rem",16:"4rem",20:"5rem",24:"6rem",28:"7rem",32:"8rem",36:"9rem",40:"10rem",44:"11rem",48:"12rem",52:"13rem",56:"14rem",60:"15rem",64:"16rem",72:"18rem",80:"20rem",96:"24rem"},stroke:({theme:i})=>({none:"none",...i("colors")}),strokeWidth:{0:"0",1:"1",2:"2"},supports:{},data:{},textColor:({theme:i})=>i("colors"),textDecorationColor:({theme:i})=>i("colors"),textDecorationThickness:{auto:"auto","from-font":"from-font",0:"0px",1:"1px",2:"2px",4:"4px",8:"8px"},textIndent:({theme:i})=>({...i("spacing")}),textOpacity:({theme:i})=>i("opacity"),textUnderlineOffset:{auto:"auto",0:"0px",1:"1px",2:"2px",4:"4px",8:"8px"},transformOrigin:{center:"center",top:"top","top-right":"top right",right:"right","bottom-right":"bottom right",bottom:"bottom","bottom-left":"bottom left",left:"left","top-left":"top left"},transitionDelay:{0:"0s",75:"75ms",100:"100ms",150:"150ms",200:"200ms",300:"300ms",500:"500ms",700:"700ms",1e3:"1000ms"},transitionDuration:{DEFAULT:"150ms",0:"0s",75:"75ms",100:"100ms",150:"150ms",200:"200ms",300:"300ms",500:"500ms",700:"700ms",1e3:"1000ms"},transitionProperty:{none:"none",all:"all",DEFAULT:"color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter",colors:"color, background-color, border-color, text-decoration-color, fill, stroke",opacity:"opacity",shadow:"box-shadow",transform:"transform"},transitionTimingFunction:{DEFAULT:"cubic-bezier(0.4, 0, 0.2, 1)",linear:"linear",in:"cubic-bezier(0.4, 0, 1, 1)",out:"cubic-bezier(0, 0, 0.2, 1)","in-out":"cubic-bezier(0.4, 0, 0.2, 1)"},translate:({theme:i})=>({...i("spacing"),"1/2":"50%","1/3":"33.333333%","2/3":"66.666667%","1/4":"25%","2/4":"50%","3/4":"75%",full:"100%"}),size:({theme:i})=>({auto:"auto",...i("spacing"),"1/2":"50%","1/3":"33.333333%","2/3":"66.666667%","1/4":"25%","2/4":"50%","3/4":"75%","1/5":"20%","2/5":"40%","3/5":"60%","4/5":"80%","1/6":"16.666667%","2/6":"33.333333%","3/6":"50%","4/6":"66.666667%","5/6":"83.333333%","1/12":"8.333333%","2/12":"16.666667%","3/12":"25%","4/12":"33.333333%","5/12":"41.666667%","6/12":"50%","7/12":"58.333333%","8/12":"66.666667%","9/12":"75%","10/12":"83.333333%","11/12":"91.666667%",full:"100%",min:"min-content",max:"max-content",fit:"fit-content"}),width:({theme:i})=>({auto:"auto",...i("spacing"),"1/2":"50%","1/3":"33.333333%","2/3":"66.666667%","1/4":"25%","2/4":"50%","3/4":"75%","1/5":"20%","2/5":"40%","3/5":"60%","4/5":"80%","1/6":"16.666667%","2/6":"33.333333%","3/6":"50%","4/6":"66.666667%","5/6":"83.333333%","1/12":"8.333333%","2/12":"16.666667%","3/12":"25%","4/12":"33.333333%","5/12":"41.666667%","6/12":"50%","7/12":"58.333333%","8/12":"66.666667%","9/12":"75%","10/12":"83.333333%","11/12":"91.666667%",full:"100%",screen:"100vw",svw:"100svw",lvw:"100lvw",dvw:"100dvw",min:"min-content",max:"max-content",fit:"fit-content"}),willChange:{auto:"auto",scroll:"scroll-position",contents:"contents",transform:"transform"},zIndex:{auto:"auto",0:"0",10:"10",20:"20",30:"30",40:"40",50:"50"}},plugins:[]}});function vi(i){let e=(i?.presets??[Ju.default]).slice().reverse().flatMap(n=>vi(n instanceof Function?n():n)),t={respectDefaultRingColorOpacity:{theme:{ringColor:({theme:n})=>({DEFAULT:"#3b82f67f",...n("colors")})}},disableColorOpacityUtilitiesByDefault:{corePlugins:{backgroundOpacity:!1,borderOpacity:!1,divideOpacity:!1,placeholderOpacity:!1,ringOpacity:!1,textOpacity:!1}}},r=Object.keys(t).filter(n=>K(i,n)).map(n=>t[n]);return[i,...r,...e]}var Ju,Xu=C(()=>{l();Ju=X(bi());ze()});var Ku={};Ae(Ku,{default:()=>hr});function hr(...i){let[,...e]=vi(i[0]);return ds([...i,...e])}var hs=C(()=>{l();Yu();Xu()});var Zu={};Ae(Zu,{default:()=>Z});var Z,yt=C(()=>{l();Z={resolve:i=>i,extname:i=>"."+i.split(".").pop()}});function xi(i){return typeof i=="object"&&i!==null}function r0(i){return Object.keys(i).length===0}function ef(i){return typeof i=="string"||i instanceof String}function ms(i){return xi(i)&&i.config===void 0&&!r0(i)?null:xi(i)&&i.config!==void 0&&ef(i.config)?Z.resolve(i.config):xi(i)&&i.config!==void 0&&xi(i.config)?null:ef(i)?Z.resolve(i):i0()}function i0(){for(let i of t0)try{let e=Z.resolve(i);return te.accessSync(e),e}catch(e){}return null}var t0,tf=C(()=>{l();je();yt();t0=["./tailwind.config.js","./tailwind.config.cjs","./tailwind.config.mjs","./tailwind.config.ts"]});var rf={};Ae(rf,{default:()=>gs});var gs,ys=C(()=>{l();gs={parse:i=>({href:i})}});var ws=v(()=>{l()});var ki=v((VE,af)=>{l();"use strict";var nf=(ci(),yu),sf=ws(),Ot=class extends Error{constructor(e,t,r,n,a,s){super(e);this.name="CssSyntaxError",this.reason=e,a&&(this.file=a),n&&(this.source=n),s&&(this.plugin=s),typeof t!="undefined"&&typeof r!="undefined"&&(typeof t=="number"?(this.line=t,this.column=r):(this.line=t.line,this.column=t.column,this.endLine=r.line,this.endColumn=r.column)),this.setMessage(),Error.captureStackTrace&&Error.captureStackTrace(this,Ot)}setMessage(){this.message=this.plugin?this.plugin+": ":"",this.message+=this.file?this.file:"<css input>",typeof this.line!="undefined"&&(this.message+=":"+this.line+":"+this.column),this.message+=": "+this.reason}showSourceCode(e){if(!this.source)return"";let t=this.source;e==null&&(e=nf.isColorSupported),sf&&e&&(t=sf(t));let r=t.split(/\r?\n/),n=Math.max(this.line-3,0),a=Math.min(this.line+2,r.length),s=String(a).length,o,u;if(e){let{bold:c,red:f,gray:d}=nf.createColors(!0);o=p=>c(f(p)),u=p=>d(p)}else o=u=c=>c;return r.slice(n,a).map((c,f)=>{let d=n+1+f,p=" "+(" "+d).slice(-s)+" | ";if(d===this.line){let m=u(p.replace(/\d/g," "))+c.slice(0,this.column-1).replace(/[^\t]/g," ");return o(">")+u(p)+c+`
 `+m+o("^")}return" "+u(p)+c}).join(`
`)}toString(){let e=this.showSourceCode();return e&&(e=`

`+e+`
`),this.name+": "+this.message+e}};af.exports=Ot;Ot.default=Ot});var Si=v((UE,bs)=>{l();"use strict";bs.exports.isClean=Symbol("isClean");bs.exports.my=Symbol("my")});var vs=v((WE,lf)=>{l();"use strict";var of={colon:": ",indent:"    ",beforeDecl:`
`,beforeRule:`
`,beforeOpen:" ",beforeClose:`
`,beforeComment:`
`,after:`
`,emptyBody:"",commentLeft:" ",commentRight:" ",semicolon:!1};function n0(i){return i[0].toUpperCase()+i.slice(1)}var Ci=class{constructor(e){this.builder=e}stringify(e,t){if(!this[e.type])throw new Error("Unknown AST node type "+e.type+". Maybe you need to change PostCSS stringifier.");this[e.type](e,t)}document(e){this.body(e)}root(e){this.body(e),e.raws.after&&this.builder(e.raws.after)}comment(e){let t=this.raw(e,"left","commentLeft"),r=this.raw(e,"right","commentRight");this.builder("/*"+t+e.text+r+"*/",e)}decl(e,t){let r=this.raw(e,"between","colon"),n=e.prop+r+this.rawValue(e,"value");e.important&&(n+=e.raws.important||" !important"),t&&(n+=";"),this.builder(n,e)}rule(e){this.block(e,this.rawValue(e,"selector")),e.raws.ownSemicolon&&this.builder(e.raws.ownSemicolon,e,"end")}atrule(e,t){let r="@"+e.name,n=e.params?this.rawValue(e,"params"):"";if(typeof e.raws.afterName!="undefined"?r+=e.raws.afterName:n&&(r+=" "),e.nodes)this.block(e,r+n);else{let a=(e.raws.between||"")+(t?";":"");this.builder(r+n+a,e)}}body(e){let t=e.nodes.length-1;for(;t>0&&e.nodes[t].type==="comment";)t-=1;let r=this.raw(e,"semicolon");for(let n=0;n<e.nodes.length;n++){let a=e.nodes[n],s=this.raw(a,"before");s&&this.builder(s),this.stringify(a,t!==n||r)}}block(e,t){let r=this.raw(e,"between","beforeOpen");this.builder(t+r+"{",e,"start");let n;e.nodes&&e.nodes.length?(this.body(e),n=this.raw(e,"after")):n=this.raw(e,"after","emptyBody"),n&&this.builder(n),this.builder("}",e,"end")}raw(e,t,r){let n;if(r||(r=t),t&&(n=e.raws[t],typeof n!="undefined"))return n;let a=e.parent;if(r==="before"&&(!a||a.type==="root"&&a.first===e||a&&a.type==="document"))return"";if(!a)return of[r];let s=e.root();if(s.rawCache||(s.rawCache={}),typeof s.rawCache[r]!="undefined")return s.rawCache[r];if(r==="before"||r==="after")return this.beforeAfter(e,r);{let o="raw"+n0(r);this[o]?n=this[o](s,e):s.walk(u=>{if(n=u.raws[t],typeof n!="undefined")return!1})}return typeof n=="undefined"&&(n=of[r]),s.rawCache[r]=n,n}rawSemicolon(e){let t;return e.walk(r=>{if(r.nodes&&r.nodes.length&&r.last.type==="decl"&&(t=r.raws.semicolon,typeof t!="undefined"))return!1}),t}rawEmptyBody(e){let t;return e.walk(r=>{if(r.nodes&&r.nodes.length===0&&(t=r.raws.after,typeof t!="undefined"))return!1}),t}rawIndent(e){if(e.raws.indent)return e.raws.indent;let t;return e.walk(r=>{let n=r.parent;if(n&&n!==e&&n.parent&&n.parent===e&&typeof r.raws.before!="undefined"){let a=r.raws.before.split(`
`);return t=a[a.length-1],t=t.replace(/\S/g,""),!1}}),t}rawBeforeComment(e,t){let r;return e.walkComments(n=>{if(typeof n.raws.before!="undefined")return r=n.raws.before,r.includes(`
`)&&(r=r.replace(/[^\n]+$/,"")),!1}),typeof r=="undefined"?r=this.raw(t,null,"beforeDecl"):r&&(r=r.replace(/\S/g,"")),r}rawBeforeDecl(e,t){let r;return e.walkDecls(n=>{if(typeof n.raws.before!="undefined")return r=n.raws.before,r.includes(`
`)&&(r=r.replace(/[^\n]+$/,"")),!1}),typeof r=="undefined"?r=this.raw(t,null,"beforeRule"):r&&(r=r.replace(/\S/g,"")),r}rawBeforeRule(e){let t;return e.walk(r=>{if(r.nodes&&(r.parent!==e||e.first!==r)&&typeof r.raws.before!="undefined")return t=r.raws.before,t.includes(`
`)&&(t=t.replace(/[^\n]+$/,"")),!1}),t&&(t=t.replace(/\S/g,"")),t}rawBeforeClose(e){let t;return e.walk(r=>{if(r.nodes&&r.nodes.length>0&&typeof r.raws.after!="undefined")return t=r.raws.after,t.includes(`
`)&&(t=t.replace(/[^\n]+$/,"")),!1}),t&&(t=t.replace(/\S/g,"")),t}rawBeforeOpen(e){let t;return e.walk(r=>{if(r.type!=="decl"&&(t=r.raws.between,typeof t!="undefined"))return!1}),t}rawColon(e){let t;return e.walkDecls(r=>{if(typeof r.raws.between!="undefined")return t=r.raws.between.replace(/[^\s:]/g,""),!1}),t}beforeAfter(e,t){let r;e.type==="decl"?r=this.raw(e,null,"beforeDecl"):e.type==="comment"?r=this.raw(e,null,"beforeComment"):t==="before"?r=this.raw(e,null,"beforeRule"):r=this.raw(e,null,"beforeClose");let n=e.parent,a=0;for(;n&&n.type!=="root";)a+=1,n=n.parent;if(r.includes(`
`)){let s=this.raw(e,null,"indent");if(s.length)for(let o=0;o<a;o++)r+=s}return r}rawValue(e,t){let r=e[t],n=e.raws[t];return n&&n.value===r?n.raw:r}};lf.exports=Ci;Ci.default=Ci});var mr=v((GE,uf)=>{l();"use strict";var s0=vs();function xs(i,e){new s0(e).stringify(i)}uf.exports=xs;xs.default=xs});var gr=v((HE,ff)=>{l();"use strict";var{isClean:Ai,my:a0}=Si(),o0=ki(),l0=vs(),u0=mr();function ks(i,e){let t=new i.constructor;for(let r in i){if(!Object.prototype.hasOwnProperty.call(i,r)||r==="proxyCache")continue;let n=i[r],a=typeof n;r==="parent"&&a==="object"?e&&(t[r]=e):r==="source"?t[r]=n:Array.isArray(n)?t[r]=n.map(s=>ks(s,t)):(a==="object"&&n!==null&&(n=ks(n)),t[r]=n)}return t}var _i=class{constructor(e={}){this.raws={},this[Ai]=!1,this[a0]=!0;for(let t in e)if(t==="nodes"){this.nodes=[];for(let r of e[t])typeof r.clone=="function"?this.append(r.clone()):this.append(r)}else this[t]=e[t]}error(e,t={}){if(this.source){let{start:r,end:n}=this.rangeBy(t);return this.source.input.error(e,{line:r.line,column:r.column},{line:n.line,column:n.column},t)}return new o0(e)}warn(e,t,r){let n={node:this};for(let a in r)n[a]=r[a];return e.warn(t,n)}remove(){return this.parent&&this.parent.removeChild(this),this.parent=void 0,this}toString(e=u0){e.stringify&&(e=e.stringify);let t="";return e(this,r=>{t+=r}),t}assign(e={}){for(let t in e)this[t]=e[t];return this}clone(e={}){let t=ks(this);for(let r in e)t[r]=e[r];return t}cloneBefore(e={}){let t=this.clone(e);return this.parent.insertBefore(this,t),t}cloneAfter(e={}){let t=this.clone(e);return this.parent.insertAfter(this,t),t}replaceWith(...e){if(this.parent){let t=this,r=!1;for(let n of e)n===this?r=!0:r?(this.parent.insertAfter(t,n),t=n):this.parent.insertBefore(t,n);r||this.remove()}return this}next(){if(!this.parent)return;let e=this.parent.index(this);return this.parent.nodes[e+1]}prev(){if(!this.parent)return;let e=this.parent.index(this);return this.parent.nodes[e-1]}before(e){return this.parent.insertBefore(this,e),this}after(e){return this.parent.insertAfter(this,e),this}root(){let e=this;for(;e.parent&&e.parent.type!=="document";)e=e.parent;return e}raw(e,t){return new l0().raw(this,e,t)}cleanRaws(e){delete this.raws.before,delete this.raws.after,e||delete this.raws.between}toJSON(e,t){let r={},n=t==null;t=t||new Map;let a=0;for(let s in this){if(!Object.prototype.hasOwnProperty.call(this,s)||s==="parent"||s==="proxyCache")continue;let o=this[s];if(Array.isArray(o))r[s]=o.map(u=>typeof u=="object"&&u.toJSON?u.toJSON(null,t):u);else if(typeof o=="object"&&o.toJSON)r[s]=o.toJSON(null,t);else if(s==="source"){let u=t.get(o.input);u==null&&(u=a,t.set(o.input,a),a++),r[s]={inputId:u,start:o.start,end:o.end}}else r[s]=o}return n&&(r.inputs=[...t.keys()].map(s=>s.toJSON())),r}positionInside(e){let t=this.toString(),r=this.source.start.column,n=this.source.start.line;for(let a=0;a<e;a++)t[a]===`
`?(r=1,n+=1):r+=1;return{line:n,column:r}}positionBy(e){let t=this.source.start;if(e.index)t=this.positionInside(e.index);else if(e.word){let r=this.toString().indexOf(e.word);r!==-1&&(t=this.positionInside(r))}return t}rangeBy(e){let t={line:this.source.start.line,column:this.source.start.column},r=this.source.end?{line:this.source.end.line,column:this.source.end.column+1}:{line:t.line,column:t.column+1};if(e.word){let n=this.toString().indexOf(e.word);n!==-1&&(t=this.positionInside(n),r=this.positionInside(n+e.word.length))}else e.start?t={line:e.start.line,column:e.start.column}:e.index&&(t=this.positionInside(e.index)),e.end?r={line:e.end.line,column:e.end.column}:e.endIndex?r=this.positionInside(e.endIndex):e.index&&(r=this.positionInside(e.index+1));return(r.line<t.line||r.line===t.line&&r.column<=t.column)&&(r={line:t.line,column:t.column+1}),{start:t,end:r}}getProxyProcessor(){return{set(e,t,r){return e[t]===r||(e[t]=r,(t==="prop"||t==="value"||t==="name"||t==="params"||t==="important"||t==="text")&&e.markDirty()),!0},get(e,t){return t==="proxyOf"?e:t==="root"?()=>e.root().toProxy():e[t]}}}toProxy(){return this.proxyCache||(this.proxyCache=new Proxy(this,this.getProxyProcessor())),this.proxyCache}addToError(e){if(e.postcssNode=this,e.stack&&this.source&&/\n\s{4}at /.test(e.stack)){let t=this.source;e.stack=e.stack.replace(/\n\s{4}at /,`$&${t.input.from}:${t.start.line}:${t.start.column}$&`)}return e}markDirty(){if(this[Ai]){this[Ai]=!1;let e=this;for(;e=e.parent;)e[Ai]=!1}}get proxyOf(){return this}};ff.exports=_i;_i.default=_i});var yr=v((YE,cf)=>{l();"use strict";var f0=gr(),Oi=class extends f0{constructor(e){e&&typeof e.value!="undefined"&&typeof e.value!="string"&&(e={...e,value:String(e.value)});super(e);this.type="decl"}get variable(){return this.prop.startsWith("--")||this.prop[0]==="$"}};cf.exports=Oi;Oi.default=Oi});var Ss=v((QE,pf)=>{l();pf.exports=function(i,e){return{generate:()=>{let t="";return i(e,r=>{t+=r}),[t]}}}});var wr=v((JE,df)=>{l();"use strict";var c0=gr(),Ei=class extends c0{constructor(e){super(e);this.type="comment"}};df.exports=Ei;Ei.default=Ei});var it=v((XE,kf)=>{l();"use strict";var{isClean:hf,my:mf}=Si(),gf=yr(),yf=wr(),p0=gr(),wf,Cs,As,bf;function vf(i){return i.map(e=>(e.nodes&&(e.nodes=vf(e.nodes)),delete e.source,e))}function xf(i){if(i[hf]=!1,i.proxyOf.nodes)for(let e of i.proxyOf.nodes)xf(e)}var we=class extends p0{push(e){return e.parent=this,this.proxyOf.nodes.push(e),this}each(e){if(!this.proxyOf.nodes)return;let t=this.getIterator(),r,n;for(;this.indexes[t]<this.proxyOf.nodes.length&&(r=this.indexes[t],n=e(this.proxyOf.nodes[r],r),n!==!1);)this.indexes[t]+=1;return delete this.indexes[t],n}walk(e){return this.each((t,r)=>{let n;try{n=e(t,r)}catch(a){throw t.addToError(a)}return n!==!1&&t.walk&&(n=t.walk(e)),n})}walkDecls(e,t){return t?e instanceof RegExp?this.walk((r,n)=>{if(r.type==="decl"&&e.test(r.prop))return t(r,n)}):this.walk((r,n)=>{if(r.type==="decl"&&r.prop===e)return t(r,n)}):(t=e,this.walk((r,n)=>{if(r.type==="decl")return t(r,n)}))}walkRules(e,t){return t?e instanceof RegExp?this.walk((r,n)=>{if(r.type==="rule"&&e.test(r.selector))return t(r,n)}):this.walk((r,n)=>{if(r.type==="rule"&&r.selector===e)return t(r,n)}):(t=e,this.walk((r,n)=>{if(r.type==="rule")return t(r,n)}))}walkAtRules(e,t){return t?e instanceof RegExp?this.walk((r,n)=>{if(r.type==="atrule"&&e.test(r.name))return t(r,n)}):this.walk((r,n)=>{if(r.type==="atrule"&&r.name===e)return t(r,n)}):(t=e,this.walk((r,n)=>{if(r.type==="atrule")return t(r,n)}))}walkComments(e){return this.walk((t,r)=>{if(t.type==="comment")return e(t,r)})}append(...e){for(let t of e){let r=this.normalize(t,this.last);for(let n of r)this.proxyOf.nodes.push(n)}return this.markDirty(),this}prepend(...e){e=e.reverse();for(let t of e){let r=this.normalize(t,this.first,"prepend").reverse();for(let n of r)this.proxyOf.nodes.unshift(n);for(let n in this.indexes)this.indexes[n]=this.indexes[n]+r.length}return this.markDirty(),this}cleanRaws(e){if(super.cleanRaws(e),this.nodes)for(let t of this.nodes)t.cleanRaws(e)}insertBefore(e,t){let r=this.index(e),n=r===0?"prepend":!1,a=this.normalize(t,this.proxyOf.nodes[r],n).reverse();r=this.index(e);for(let o of a)this.proxyOf.nodes.splice(r,0,o);let s;for(let o in this.indexes)s=this.indexes[o],r<=s&&(this.indexes[o]=s+a.length);return this.markDirty(),this}insertAfter(e,t){let r=this.index(e),n=this.normalize(t,this.proxyOf.nodes[r]).reverse();r=this.index(e);for(let s of n)this.proxyOf.nodes.splice(r+1,0,s);let a;for(let s in this.indexes)a=this.indexes[s],r<a&&(this.indexes[s]=a+n.length);return this.markDirty(),this}removeChild(e){e=this.index(e),this.proxyOf.nodes[e].parent=void 0,this.proxyOf.nodes.splice(e,1);let t;for(let r in this.indexes)t=this.indexes[r],t>=e&&(this.indexes[r]=t-1);return this.markDirty(),this}removeAll(){for(let e of this.proxyOf.nodes)e.parent=void 0;return this.proxyOf.nodes=[],this.markDirty(),this}replaceValues(e,t,r){return r||(r=t,t={}),this.walkDecls(n=>{t.props&&!t.props.includes(n.prop)||t.fast&&!n.value.includes(t.fast)||(n.value=n.value.replace(e,r))}),this.markDirty(),this}every(e){return this.nodes.every(e)}some(e){return this.nodes.some(e)}index(e){return typeof e=="number"?e:(e.proxyOf&&(e=e.proxyOf),this.proxyOf.nodes.indexOf(e))}get first(){if(!!this.proxyOf.nodes)return this.proxyOf.nodes[0]}get last(){if(!!this.proxyOf.nodes)return this.proxyOf.nodes[this.proxyOf.nodes.length-1]}normalize(e,t){if(typeof e=="string")e=vf(wf(e).nodes);else if(Array.isArray(e)){e=e.slice(0);for(let n of e)n.parent&&n.parent.removeChild(n,"ignore")}else if(e.type==="root"&&this.type!=="document"){e=e.nodes.slice(0);for(let n of e)n.parent&&n.parent.removeChild(n,"ignore")}else if(e.type)e=[e];else if(e.prop){if(typeof e.value=="undefined")throw new Error("Value field is missed in node creation");typeof e.value!="string"&&(e.value=String(e.value)),e=[new gf(e)]}else if(e.selector)e=[new Cs(e)];else if(e.name)e=[new As(e)];else if(e.text)e=[new yf(e)];else throw new Error("Unknown node type in node creation");return e.map(n=>(n[mf]||we.rebuild(n),n=n.proxyOf,n.parent&&n.parent.removeChild(n),n[hf]&&xf(n),typeof n.raws.before=="undefined"&&t&&typeof t.raws.before!="undefined"&&(n.raws.before=t.raws.before.replace(/\S/g,"")),n.parent=this.proxyOf,n))}getProxyProcessor(){return{set(e,t,r){return e[t]===r||(e[t]=r,(t==="name"||t==="params"||t==="selector")&&e.markDirty()),!0},get(e,t){return t==="proxyOf"?e:e[t]?t==="each"||typeof t=="string"&&t.startsWith("walk")?(...r)=>e[t](...r.map(n=>typeof n=="function"?(a,s)=>n(a.toProxy(),s):n)):t==="every"||t==="some"?r=>e[t]((n,...a)=>r(n.toProxy(),...a)):t==="root"?()=>e.root().toProxy():t==="nodes"?e.nodes.map(r=>r.toProxy()):t==="first"||t==="last"?e[t].toProxy():e[t]:e[t]}}}getIterator(){this.lastEach||(this.lastEach=0),this.indexes||(this.indexes={}),this.lastEach+=1;let e=this.lastEach;return this.indexes[e]=0,e}};we.registerParse=i=>{wf=i};we.registerRule=i=>{Cs=i};we.registerAtRule=i=>{As=i};we.registerRoot=i=>{bf=i};kf.exports=we;we.default=we;we.rebuild=i=>{i.type==="atrule"?Object.setPrototypeOf(i,As.prototype):i.type==="rule"?Object.setPrototypeOf(i,Cs.prototype):i.type==="decl"?Object.setPrototypeOf(i,gf.prototype):i.type==="comment"?Object.setPrototypeOf(i,yf.prototype):i.type==="root"&&Object.setPrototypeOf(i,bf.prototype),i[mf]=!0,i.nodes&&i.nodes.forEach(e=>{we.rebuild(e)})}});var Ti=v((KE,Af)=>{l();"use strict";var d0=it(),Sf,Cf,Et=class extends d0{constructor(e){super({type:"document",...e});this.nodes||(this.nodes=[])}toResult(e={}){return new Sf(new Cf,this,e).stringify()}};Et.registerLazyResult=i=>{Sf=i};Et.registerProcessor=i=>{Cf=i};Af.exports=Et;Et.default=Et});var _s=v((ZE,Of)=>{l();"use strict";var _f={};Of.exports=function(e){_f[e]||(_f[e]=!0,typeof console!="undefined"&&console.warn&&console.warn(e))}});var Os=v((eT,Ef)=>{l();"use strict";var Pi=class{constructor(e,t={}){if(this.type="warning",this.text=e,t.node&&t.node.source){let r=t.node.rangeBy(t);this.line=r.start.line,this.column=r.start.column,this.endLine=r.end.line,this.endColumn=r.end.column}for(let r in t)this[r]=t[r]}toString(){return this.node?this.node.error(this.text,{plugin:this.plugin,index:this.index,word:this.word}).message:this.plugin?this.plugin+": "+this.text:this.text}};Ef.exports=Pi;Pi.default=Pi});var Ii=v((tT,Tf)=>{l();"use strict";var h0=Os(),Di=class{constructor(e,t,r){this.processor=e,this.messages=[],this.root=t,this.opts=r,this.css=void 0,this.map=void 0}toString(){return this.css}warn(e,t={}){t.plugin||this.lastPlugin&&this.lastPlugin.postcssPlugin&&(t.plugin=this.lastPlugin.postcssPlugin);let r=new h0(e,t);return this.messages.push(r),r}warnings(){return this.messages.filter(e=>e.type==="warning")}get content(){return this.css}};Tf.exports=Di;Di.default=Di});var Rf=v((rT,qf)=>{l();"use strict";var Es="'".charCodeAt(0),Pf='"'.charCodeAt(0),qi="\\".charCodeAt(0),Df="/".charCodeAt(0),Ri=`
`.charCodeAt(0),br=" ".charCodeAt(0),Mi="\f".charCodeAt(0),Bi="	".charCodeAt(0),Fi="\r".charCodeAt(0),m0="[".charCodeAt(0),g0="]".charCodeAt(0),y0="(".charCodeAt(0),w0=")".charCodeAt(0),b0="{".charCodeAt(0),v0="}".charCodeAt(0),x0=";".charCodeAt(0),k0="*".charCodeAt(0),S0=":".charCodeAt(0),C0="@".charCodeAt(0),Ni=/[\t\n\f\r "#'()/;[\\\]{}]/g,Li=/[\t\n\f\r !"#'():;@[\\\]{}]|\/(?=\*)/g,A0=/.[\n"'(/\\]/,If=/[\da-f]/i;qf.exports=function(e,t={}){let r=e.css.valueOf(),n=t.ignoreErrors,a,s,o,u,c,f,d,p,m,b,x=r.length,y=0,w=[],k=[];function S(){return y}function _(R){throw e.error("Unclosed "+R,y)}function E(){return k.length===0&&y>=x}function I(R){if(k.length)return k.pop();if(y>=x)return;let J=R?R.ignoreUnclosed:!1;switch(a=r.charCodeAt(y),a){case Ri:case br:case Bi:case Fi:case Mi:{s=y;do s+=1,a=r.charCodeAt(s);while(a===br||a===Ri||a===Bi||a===Fi||a===Mi);b=["space",r.slice(y,s)],y=s-1;break}case m0:case g0:case b0:case v0:case S0:case x0:case w0:{let ue=String.fromCharCode(a);b=[ue,ue,y];break}case y0:{if(p=w.length?w.pop()[1]:"",m=r.charCodeAt(y+1),p==="url"&&m!==Es&&m!==Pf&&m!==br&&m!==Ri&&m!==Bi&&m!==Mi&&m!==Fi){s=y;do{if(f=!1,s=r.indexOf(")",s+1),s===-1)if(n||J){s=y;break}else _("bracket");for(d=s;r.charCodeAt(d-1)===qi;)d-=1,f=!f}while(f);b=["brackets",r.slice(y,s+1),y,s],y=s}else s=r.indexOf(")",y+1),u=r.slice(y,s+1),s===-1||A0.test(u)?b=["(","(",y]:(b=["brackets",u,y,s],y=s);break}case Es:case Pf:{o=a===Es?"'":'"',s=y;do{if(f=!1,s=r.indexOf(o,s+1),s===-1)if(n||J){s=y+1;break}else _("string");for(d=s;r.charCodeAt(d-1)===qi;)d-=1,f=!f}while(f);b=["string",r.slice(y,s+1),y,s],y=s;break}case C0:{Ni.lastIndex=y+1,Ni.test(r),Ni.lastIndex===0?s=r.length-1:s=Ni.lastIndex-2,b=["at-word",r.slice(y,s+1),y,s],y=s;break}case qi:{for(s=y,c=!0;r.charCodeAt(s+1)===qi;)s+=1,c=!c;if(a=r.charCodeAt(s+1),c&&a!==Df&&a!==br&&a!==Ri&&a!==Bi&&a!==Fi&&a!==Mi&&(s+=1,If.test(r.charAt(s)))){for(;If.test(r.charAt(s+1));)s+=1;r.charCodeAt(s+1)===br&&(s+=1)}b=["word",r.slice(y,s+1),y,s],y=s;break}default:{a===Df&&r.charCodeAt(y+1)===k0?(s=r.indexOf("*/",y+2)+1,s===0&&(n||J?s=r.length:_("comment")),b=["comment",r.slice(y,s+1),y,s],y=s):(Li.lastIndex=y+1,Li.test(r),Li.lastIndex===0?s=r.length-1:s=Li.lastIndex-2,b=["word",r.slice(y,s+1),y,s],w.push(b),y=s);break}}return y++,b}function q(R){k.push(R)}return{back:q,nextToken:I,endOfFile:E,position:S}}});var $i=v((iT,Bf)=>{l();"use strict";var Mf=it(),vr=class extends Mf{constructor(e){super(e);this.type="atrule"}append(...e){return this.proxyOf.nodes||(this.nodes=[]),super.append(...e)}prepend(...e){return this.proxyOf.nodes||(this.nodes=[]),super.prepend(...e)}};Bf.exports=vr;vr.default=vr;Mf.registerAtRule(vr)});var Tt=v((nT,$f)=>{l();"use strict";var Ff=it(),Nf,Lf,wt=class extends Ff{constructor(e){super(e);this.type="root",this.nodes||(this.nodes=[])}removeChild(e,t){let r=this.index(e);return!t&&r===0&&this.nodes.length>1&&(this.nodes[1].raws.before=this.nodes[r].raws.before),super.removeChild(e)}normalize(e,t,r){let n=super.normalize(e);if(t){if(r==="prepend")this.nodes.length>1?t.raws.before=this.nodes[1].raws.before:delete t.raws.before;else if(this.first!==t)for(let a of n)a.raws.before=t.raws.before}return n}toResult(e={}){return new Nf(new Lf,this,e).stringify()}};wt.registerLazyResult=i=>{Nf=i};wt.registerProcessor=i=>{Lf=i};$f.exports=wt;wt.default=wt;Ff.registerRoot(wt)});var Ts=v((sT,jf)=>{l();"use strict";var xr={split(i,e,t){let r=[],n="",a=!1,s=0,o=!1,u="",c=!1;for(let f of i)c?c=!1:f==="\\"?c=!0:o?f===u&&(o=!1):f==='"'||f==="'"?(o=!0,u=f):f==="("?s+=1:f===")"?s>0&&(s-=1):s===0&&e.includes(f)&&(a=!0),a?(n!==""&&r.push(n.trim()),n="",a=!1):n+=f;return(t||n!=="")&&r.push(n.trim()),r},space(i){let e=[" ",`
`,"	"];return xr.split(i,e)},comma(i){return xr.split(i,[","],!0)}};jf.exports=xr;xr.default=xr});var ji=v((aT,Vf)=>{l();"use strict";var zf=it(),_0=Ts(),kr=class extends zf{constructor(e){super(e);this.type="rule",this.nodes||(this.nodes=[])}get selectors(){return _0.comma(this.selector)}set selectors(e){let t=this.selector?this.selector.match(/,\s*/):null,r=t?t[0]:","+this.raw("between","beforeOpen");this.selector=e.join(r)}};Vf.exports=kr;kr.default=kr;zf.registerRule(kr)});var Yf=v((oT,Hf)=>{l();"use strict";var O0=yr(),E0=Rf(),T0=wr(),P0=$i(),D0=Tt(),Uf=ji(),Wf={empty:!0,space:!0};function I0(i){for(let e=i.length-1;e>=0;e--){let t=i[e],r=t[3]||t[2];if(r)return r}}var Gf=class{constructor(e){this.input=e,this.root=new D0,this.current=this.root,this.spaces="",this.semicolon=!1,this.customProperty=!1,this.createTokenizer(),this.root.source={input:e,start:{offset:0,line:1,column:1}}}createTokenizer(){this.tokenizer=E0(this.input)}parse(){let e;for(;!this.tokenizer.endOfFile();)switch(e=this.tokenizer.nextToken(),e[0]){case"space":this.spaces+=e[1];break;case";":this.freeSemicolon(e);break;case"}":this.end(e);break;case"comment":this.comment(e);break;case"at-word":this.atrule(e);break;case"{":this.emptyRule(e);break;default:this.other(e);break}this.endFile()}comment(e){let t=new T0;this.init(t,e[2]),t.source.end=this.getPosition(e[3]||e[2]);let r=e[1].slice(2,-2);if(/^\s*$/.test(r))t.text="",t.raws.left=r,t.raws.right="";else{let n=r.match(/^(\s*)([^]*\S)(\s*)$/);t.text=n[2],t.raws.left=n[1],t.raws.right=n[3]}}emptyRule(e){let t=new Uf;this.init(t,e[2]),t.selector="",t.raws.between="",this.current=t}other(e){let t=!1,r=null,n=!1,a=null,s=[],o=e[1].startsWith("--"),u=[],c=e;for(;c;){if(r=c[0],u.push(c),r==="("||r==="[")a||(a=c),s.push(r==="("?")":"]");else if(o&&n&&r==="{")a||(a=c),s.push("}");else if(s.length===0)if(r===";")if(n){this.decl(u,o);return}else break;else if(r==="{"){this.rule(u);return}else if(r==="}"){this.tokenizer.back(u.pop()),t=!0;break}else r===":"&&(n=!0);else r===s[s.length-1]&&(s.pop(),s.length===0&&(a=null));c=this.tokenizer.nextToken()}if(this.tokenizer.endOfFile()&&(t=!0),s.length>0&&this.unclosedBracket(a),t&&n){if(!o)for(;u.length&&(c=u[u.length-1][0],!(c!=="space"&&c!=="comment"));)this.tokenizer.back(u.pop());this.decl(u,o)}else this.unknownWord(u)}rule(e){e.pop();let t=new Uf;this.init(t,e[0][2]),t.raws.between=this.spacesAndCommentsFromEnd(e),this.raw(t,"selector",e),this.current=t}decl(e,t){let r=new O0;this.init(r,e[0][2]);let n=e[e.length-1];for(n[0]===";"&&(this.semicolon=!0,e.pop()),r.source.end=this.getPosition(n[3]||n[2]||I0(e));e[0][0]!=="word";)e.length===1&&this.unknownWord(e),r.raws.before+=e.shift()[1];for(r.source.start=this.getPosition(e[0][2]),r.prop="";e.length;){let c=e[0][0];if(c===":"||c==="space"||c==="comment")break;r.prop+=e.shift()[1]}r.raws.between="";let a;for(;e.length;)if(a=e.shift(),a[0]===":"){r.raws.between+=a[1];break}else a[0]==="word"&&/\w/.test(a[1])&&this.unknownWord([a]),r.raws.between+=a[1];(r.prop[0]==="_"||r.prop[0]==="*")&&(r.raws.before+=r.prop[0],r.prop=r.prop.slice(1));let s=[],o;for(;e.length&&(o=e[0][0],!(o!=="space"&&o!=="comment"));)s.push(e.shift());this.precheckMissedSemicolon(e);for(let c=e.length-1;c>=0;c--){if(a=e[c],a[1].toLowerCase()==="!important"){r.important=!0;let f=this.stringFrom(e,c);f=this.spacesFromEnd(e)+f,f!==" !important"&&(r.raws.important=f);break}else if(a[1].toLowerCase()==="important"){let f=e.slice(0),d="";for(let p=c;p>0;p--){let m=f[p][0];if(d.trim().indexOf("!")===0&&m!=="space")break;d=f.pop()[1]+d}d.trim().indexOf("!")===0&&(r.important=!0,r.raws.important=d,e=f)}if(a[0]!=="space"&&a[0]!=="comment")break}e.some(c=>c[0]!=="space"&&c[0]!=="comment")&&(r.raws.between+=s.map(c=>c[1]).join(""),s=[]),this.raw(r,"value",s.concat(e),t),r.value.includes(":")&&!t&&this.checkMissedSemicolon(e)}atrule(e){let t=new P0;t.name=e[1].slice(1),t.name===""&&this.unnamedAtrule(t,e),this.init(t,e[2]);let r,n,a,s=!1,o=!1,u=[],c=[];for(;!this.tokenizer.endOfFile();){if(e=this.tokenizer.nextToken(),r=e[0],r==="("||r==="["?c.push(r==="("?")":"]"):r==="{"&&c.length>0?c.push("}"):r===c[c.length-1]&&c.pop(),c.length===0)if(r===";"){t.source.end=this.getPosition(e[2]),this.semicolon=!0;break}else if(r==="{"){o=!0;break}else if(r==="}"){if(u.length>0){for(a=u.length-1,n=u[a];n&&n[0]==="space";)n=u[--a];n&&(t.source.end=this.getPosition(n[3]||n[2]))}this.end(e);break}else u.push(e);else u.push(e);if(this.tokenizer.endOfFile()){s=!0;break}}t.raws.between=this.spacesAndCommentsFromEnd(u),u.length?(t.raws.afterName=this.spacesAndCommentsFromStart(u),this.raw(t,"params",u),s&&(e=u[u.length-1],t.source.end=this.getPosition(e[3]||e[2]),this.spaces=t.raws.between,t.raws.between="")):(t.raws.afterName="",t.params=""),o&&(t.nodes=[],this.current=t)}end(e){this.current.nodes&&this.current.nodes.length&&(this.current.raws.semicolon=this.semicolon),this.semicolon=!1,this.current.raws.after=(this.current.raws.after||"")+this.spaces,this.spaces="",this.current.parent?(this.current.source.end=this.getPosition(e[2]),this.current=this.current.parent):this.unexpectedClose(e)}endFile(){this.current.parent&&this.unclosedBlock(),this.current.nodes&&this.current.nodes.length&&(this.current.raws.semicolon=this.semicolon),this.current.raws.after=(this.current.raws.after||"")+this.spaces}freeSemicolon(e){if(this.spaces+=e[1],this.current.nodes){let t=this.current.nodes[this.current.nodes.length-1];t&&t.type==="rule"&&!t.raws.ownSemicolon&&(t.raws.ownSemicolon=this.spaces,this.spaces="")}}getPosition(e){let t=this.input.fromOffset(e);return{offset:e,line:t.line,column:t.col}}init(e,t){this.current.push(e),e.source={start:this.getPosition(t),input:this.input},e.raws.before=this.spaces,this.spaces="",e.type!=="comment"&&(this.semicolon=!1)}raw(e,t,r,n){let a,s,o=r.length,u="",c=!0,f,d;for(let p=0;p<o;p+=1)a=r[p],s=a[0],s==="space"&&p===o-1&&!n?c=!1:s==="comment"?(d=r[p-1]?r[p-1][0]:"empty",f=r[p+1]?r[p+1][0]:"empty",!Wf[d]&&!Wf[f]?u.slice(-1)===","?c=!1:u+=a[1]:c=!1):u+=a[1];if(!c){let p=r.reduce((m,b)=>m+b[1],"");e.raws[t]={value:u,raw:p}}e[t]=u}spacesAndCommentsFromEnd(e){let t,r="";for(;e.length&&(t=e[e.length-1][0],!(t!=="space"&&t!=="comment"));)r=e.pop()[1]+r;return r}spacesAndCommentsFromStart(e){let t,r="";for(;e.length&&(t=e[0][0],!(t!=="space"&&t!=="comment"));)r+=e.shift()[1];return r}spacesFromEnd(e){let t,r="";for(;e.length&&(t=e[e.length-1][0],t==="space");)r=e.pop()[1]+r;return r}stringFrom(e,t){let r="";for(let n=t;n<e.length;n++)r+=e[n][1];return e.splice(t,e.length-t),r}colon(e){let t=0,r,n,a;for(let[s,o]of e.entries()){if(r=o,n=r[0],n==="("&&(t+=1),n===")"&&(t-=1),t===0&&n===":")if(!a)this.doubleColon(r);else{if(a[0]==="word"&&a[1]==="progid")continue;return s}a=r}return!1}unclosedBracket(e){throw this.input.error("Unclosed bracket",{offset:e[2]},{offset:e[2]+1})}unknownWord(e){throw this.input.error("Unknown word",{offset:e[0][2]},{offset:e[0][2]+e[0][1].length})}unexpectedClose(e){throw this.input.error("Unexpected }",{offset:e[2]},{offset:e[2]+1})}unclosedBlock(){let e=this.current.source.start;throw this.input.error("Unclosed block",e.line,e.column)}doubleColon(e){throw this.input.error("Double colon",{offset:e[2]},{offset:e[2]+e[1].length})}unnamedAtrule(e,t){throw this.input.error("At-rule without name",{offset:t[2]},{offset:t[2]+t[1].length})}precheckMissedSemicolon(){}checkMissedSemicolon(e){let t=this.colon(e);if(t===!1)return;let r=0,n;for(let a=t-1;a>=0&&(n=e[a],!(n[0]!=="space"&&(r+=1,r===2)));a--);throw this.input.error("Missed semicolon",n[0]==="word"?n[3]+1:n[2])}};Hf.exports=Gf});var Qf=v(()=>{l()});var Xf=v((fT,Jf)=>{l();var q0="useandom-26T198340PX75pxJACKVERYMINDBUSHWOLF_GQZbfghjklqvwyzrict",R0=(i,e=21)=>(t=e)=>{let r="",n=t;for(;n--;)r+=i[Math.random()*i.length|0];return r},M0=(i=21)=>{let e="",t=i;for(;t--;)e+=q0[Math.random()*64|0];return e};Jf.exports={nanoid:M0,customAlphabet:R0}});var Ps=v((cT,Kf)=>{l();Kf.exports={}});var Vi=v((pT,rc)=>{l();"use strict";var{SourceMapConsumer:B0,SourceMapGenerator:F0}=Qf(),{fileURLToPath:Zf,pathToFileURL:zi}=(ys(),rf),{resolve:Ds,isAbsolute:Is}=(yt(),Zu),{nanoid:N0}=Xf(),qs=ws(),ec=ki(),L0=Ps(),Rs=Symbol("fromOffsetCache"),$0=Boolean(B0&&F0),tc=Boolean(Ds&&Is),Sr=class{constructor(e,t={}){if(e===null||typeof e=="undefined"||typeof e=="object"&&!e.toString)throw new Error(`PostCSS received ${e} instead of CSS string`);if(this.css=e.toString(),this.css[0]==="\uFEFF"||this.css[0]==="\uFFFE"?(this.hasBOM=!0,this.css=this.css.slice(1)):this.hasBOM=!1,t.from&&(!tc||/^\w+:\/\//.test(t.from)||Is(t.from)?this.file=t.from:this.file=Ds(t.from)),tc&&$0){let r=new L0(this.css,t);if(r.text){this.map=r;let n=r.consumer().file;!this.file&&n&&(this.file=this.mapResolve(n))}}this.file||(this.id="<input css "+N0(6)+">"),this.map&&(this.map.file=this.from)}fromOffset(e){let t,r;if(this[Rs])r=this[Rs];else{let a=this.css.split(`
`);r=new Array(a.length);let s=0;for(let o=0,u=a.length;o<u;o++)r[o]=s,s+=a[o].length+1;this[Rs]=r}t=r[r.length-1];let n=0;if(e>=t)n=r.length-1;else{let a=r.length-2,s;for(;n<a;)if(s=n+(a-n>>1),e<r[s])a=s-1;else if(e>=r[s+1])n=s+1;else{n=s;break}}return{line:n+1,col:e-r[n]+1}}error(e,t,r,n={}){let a,s,o;if(t&&typeof t=="object"){let c=t,f=r;if(typeof c.offset=="number"){let d=this.fromOffset(c.offset);t=d.line,r=d.col}else t=c.line,r=c.column;if(typeof f.offset=="number"){let d=this.fromOffset(f.offset);s=d.line,o=d.col}else s=f.line,o=f.column}else if(!r){let c=this.fromOffset(t);t=c.line,r=c.col}let u=this.origin(t,r,s,o);return u?a=new ec(e,u.endLine===void 0?u.line:{line:u.line,column:u.column},u.endLine===void 0?u.column:{line:u.endLine,column:u.endColumn},u.source,u.file,n.plugin):a=new ec(e,s===void 0?t:{line:t,column:r},s===void 0?r:{line:s,column:o},this.css,this.file,n.plugin),a.input={line:t,column:r,endLine:s,endColumn:o,source:this.css},this.file&&(zi&&(a.input.url=zi(this.file).toString()),a.input.file=this.file),a}origin(e,t,r,n){if(!this.map)return!1;let a=this.map.consumer(),s=a.originalPositionFor({line:e,column:t});if(!s.source)return!1;let o;typeof r=="number"&&(o=a.originalPositionFor({line:r,column:n}));let u;Is(s.source)?u=zi(s.source):u=new URL(s.source,this.map.consumer().sourceRoot||zi(this.map.mapFile));let c={url:u.toString(),line:s.line,column:s.column,endLine:o&&o.line,endColumn:o&&o.column};if(u.protocol==="file:")if(Zf)c.file=Zf(u);else throw new Error("file: protocol is not available in this PostCSS build");let f=a.sourceContentFor(s.source);return f&&(c.source=f),c}mapResolve(e){return/^\w+:\/\//.test(e)?e:Ds(this.map.consumer().sourceRoot||this.map.root||".",e)}get from(){return this.file||this.id}toJSON(){let e={};for(let t of["hasBOM","css","file","id"])this[t]!=null&&(e[t]=this[t]);return this.map&&(e.map={...this.map},e.map.consumerCache&&(e.map.consumerCache=void 0)),e}};rc.exports=Sr;Sr.default=Sr;qs&&qs.registerInput&&qs.registerInput(Sr)});var Wi=v((dT,ic)=>{l();"use strict";var j0=it(),z0=Yf(),V0=Vi();function Ui(i,e){let t=new V0(i,e),r=new z0(t);try{r.parse()}catch(n){throw n}return r.root}ic.exports=Ui;Ui.default=Ui;j0.registerParse(Ui)});var Fs=v((mT,oc)=>{l();"use strict";var{isClean:qe,my:U0}=Si(),W0=Ss(),G0=mr(),H0=it(),Y0=Ti(),hT=_s(),nc=Ii(),Q0=Wi(),J0=Tt(),X0={document:"Document",root:"Root",atrule:"AtRule",rule:"Rule",decl:"Declaration",comment:"Comment"},K0={postcssPlugin:!0,prepare:!0,Once:!0,Document:!0,Root:!0,Declaration:!0,Rule:!0,AtRule:!0,Comment:!0,DeclarationExit:!0,RuleExit:!0,AtRuleExit:!0,CommentExit:!0,RootExit:!0,DocumentExit:!0,OnceExit:!0},Z0={postcssPlugin:!0,prepare:!0,Once:!0},Pt=0;function Cr(i){return typeof i=="object"&&typeof i.then=="function"}function sc(i){let e=!1,t=X0[i.type];return i.type==="decl"?e=i.prop.toLowerCase():i.type==="atrule"&&(e=i.name.toLowerCase()),e&&i.append?[t,t+"-"+e,Pt,t+"Exit",t+"Exit-"+e]:e?[t,t+"-"+e,t+"Exit",t+"Exit-"+e]:i.append?[t,Pt,t+"Exit"]:[t,t+"Exit"]}function ac(i){let e;return i.type==="document"?e=["Document",Pt,"DocumentExit"]:i.type==="root"?e=["Root",Pt,"RootExit"]:e=sc(i),{node:i,events:e,eventIndex:0,visitors:[],visitorIndex:0,iterator:0}}function Ms(i){return i[qe]=!1,i.nodes&&i.nodes.forEach(e=>Ms(e)),i}var Bs={},Ve=class{constructor(e,t,r){this.stringified=!1,this.processed=!1;let n;if(typeof t=="object"&&t!==null&&(t.type==="root"||t.type==="document"))n=Ms(t);else if(t instanceof Ve||t instanceof nc)n=Ms(t.root),t.map&&(typeof r.map=="undefined"&&(r.map={}),r.map.inline||(r.map.inline=!1),r.map.prev=t.map);else{let a=Q0;r.syntax&&(a=r.syntax.parse),r.parser&&(a=r.parser),a.parse&&(a=a.parse);try{n=a(t,r)}catch(s){this.processed=!0,this.error=s}n&&!n[U0]&&H0.rebuild(n)}this.result=new nc(e,n,r),this.helpers={...Bs,result:this.result,postcss:Bs},this.plugins=this.processor.plugins.map(a=>typeof a=="object"&&a.prepare?{...a,...a.prepare(this.result)}:a)}get[Symbol.toStringTag](){return"LazyResult"}get processor(){return this.result.processor}get opts(){return this.result.opts}get css(){return this.stringify().css}get content(){return this.stringify().content}get map(){return this.stringify().map}get root(){return this.sync().root}get messages(){return this.sync().messages}warnings(){return this.sync().warnings()}toString(){return this.css}then(e,t){return this.async().then(e,t)}catch(e){return this.async().catch(e)}finally(e){return this.async().then(e,e)}async(){return this.error?Promise.reject(this.error):this.processed?Promise.resolve(this.result):(this.processing||(this.processing=this.runAsync()),this.processing)}sync(){if(this.error)throw this.error;if(this.processed)return this.result;if(this.processed=!0,this.processing)throw this.getAsyncError();for(let e of this.plugins){let t=this.runOnRoot(e);if(Cr(t))throw this.getAsyncError()}if(this.prepareVisitors(),this.hasListener){let e=this.result.root;for(;!e[qe];)e[qe]=!0,this.walkSync(e);if(this.listeners.OnceExit)if(e.type==="document")for(let t of e.nodes)this.visitSync(this.listeners.OnceExit,t);else this.visitSync(this.listeners.OnceExit,e)}return this.result}stringify(){if(this.error)throw this.error;if(this.stringified)return this.result;this.stringified=!0,this.sync();let e=this.result.opts,t=G0;e.syntax&&(t=e.syntax.stringify),e.stringifier&&(t=e.stringifier),t.stringify&&(t=t.stringify);let n=new W0(t,this.result.root,this.result.opts).generate();return this.result.css=n[0],this.result.map=n[1],this.result}walkSync(e){e[qe]=!0;let t=sc(e);for(let r of t)if(r===Pt)e.nodes&&e.each(n=>{n[qe]||this.walkSync(n)});else{let n=this.listeners[r];if(n&&this.visitSync(n,e.toProxy()))return}}visitSync(e,t){for(let[r,n]of e){this.result.lastPlugin=r;let a;try{a=n(t,this.helpers)}catch(s){throw this.handleError(s,t.proxyOf)}if(t.type!=="root"&&t.type!=="document"&&!t.parent)return!0;if(Cr(a))throw this.getAsyncError()}}runOnRoot(e){this.result.lastPlugin=e;try{if(typeof e=="object"&&e.Once){if(this.result.root.type==="document"){let t=this.result.root.nodes.map(r=>e.Once(r,this.helpers));return Cr(t[0])?Promise.all(t):t}return e.Once(this.result.root,this.helpers)}else if(typeof e=="function")return e(this.result.root,this.result)}catch(t){throw this.handleError(t)}}getAsyncError(){throw new Error("Use process(css).then(cb) to work with async plugins")}handleError(e,t){let r=this.result.lastPlugin;try{t&&t.addToError(e),this.error=e,e.name==="CssSyntaxError"&&!e.plugin?(e.plugin=r.postcssPlugin,e.setMessage()):r.postcssVersion}catch(n){console&&console.error&&console.error(n)}return e}async runAsync(){this.plugin=0;for(let e=0;e<this.plugins.length;e++){let t=this.plugins[e],r=this.runOnRoot(t);if(Cr(r))try{await r}catch(n){throw this.handleError(n)}}if(this.prepareVisitors(),this.hasListener){let e=this.result.root;for(;!e[qe];){e[qe]=!0;let t=[ac(e)];for(;t.length>0;){let r=this.visitTick(t);if(Cr(r))try{await r}catch(n){let a=t[t.length-1].node;throw this.handleError(n,a)}}}if(this.listeners.OnceExit)for(let[t,r]of this.listeners.OnceExit){this.result.lastPlugin=t;try{if(e.type==="document"){let n=e.nodes.map(a=>r(a,this.helpers));await Promise.all(n)}else await r(e,this.helpers)}catch(n){throw this.handleError(n)}}}return this.processed=!0,this.stringify()}prepareVisitors(){this.listeners={};let e=(t,r,n)=>{this.listeners[r]||(this.listeners[r]=[]),this.listeners[r].push([t,n])};for(let t of this.plugins)if(typeof t=="object")for(let r in t){if(!K0[r]&&/^[A-Z]/.test(r))throw new Error(`Unknown event ${r} in ${t.postcssPlugin}. Try to update PostCSS (${this.processor.version} now).`);if(!Z0[r])if(typeof t[r]=="object")for(let n in t[r])n==="*"?e(t,r,t[r][n]):e(t,r+"-"+n.toLowerCase(),t[r][n]);else typeof t[r]=="function"&&e(t,r,t[r])}this.hasListener=Object.keys(this.listeners).length>0}visitTick(e){let t=e[e.length-1],{node:r,visitors:n}=t;if(r.type!=="root"&&r.type!=="document"&&!r.parent){e.pop();return}if(n.length>0&&t.visitorIndex<n.length){let[s,o]=n[t.visitorIndex];t.visitorIndex+=1,t.visitorIndex===n.length&&(t.visitors=[],t.visitorIndex=0),this.result.lastPlugin=s;try{return o(r.toProxy(),this.helpers)}catch(u){throw this.handleError(u,r)}}if(t.iterator!==0){let s=t.iterator,o;for(;o=r.nodes[r.indexes[s]];)if(r.indexes[s]+=1,!o[qe]){o[qe]=!0,e.push(ac(o));return}t.iterator=0,delete r.indexes[s]}let a=t.events;for(;t.eventIndex<a.length;){let s=a[t.eventIndex];if(t.eventIndex+=1,s===Pt){r.nodes&&r.nodes.length&&(r[qe]=!0,t.iterator=r.getIterator());return}else if(this.listeners[s]){t.visitors=this.listeners[s];return}}e.pop()}};Ve.registerPostcss=i=>{Bs=i};oc.exports=Ve;Ve.default=Ve;J0.registerLazyResult(Ve);Y0.registerLazyResult(Ve)});var uc=v((yT,lc)=>{l();"use strict";var ev=Ss(),tv=mr(),gT=_s(),rv=Wi(),iv=Ii(),Gi=class{constructor(e,t,r){t=t.toString(),this.stringified=!1,this._processor=e,this._css=t,this._opts=r,this._map=void 0;let n,a=tv;this.result=new iv(this._processor,n,this._opts),this.result.css=t;let s=this;Object.defineProperty(this.result,"root",{get(){return s.root}});let o=new ev(a,n,this._opts,t);if(o.isMap()){let[u,c]=o.generate();u&&(this.result.css=u),c&&(this.result.map=c)}}get[Symbol.toStringTag](){return"NoWorkResult"}get processor(){return this.result.processor}get opts(){return this.result.opts}get css(){return this.result.css}get content(){return this.result.css}get map(){return this.result.map}get root(){if(this._root)return this._root;let e,t=rv;try{e=t(this._css,this._opts)}catch(r){this.error=r}if(this.error)throw this.error;return this._root=e,e}get messages(){return[]}warnings(){return[]}toString(){return this._css}then(e,t){return this.async().then(e,t)}catch(e){return this.async().catch(e)}finally(e){return this.async().then(e,e)}async(){return this.error?Promise.reject(this.error):Promise.resolve(this.result)}sync(){if(this.error)throw this.error;return this.result}};lc.exports=Gi;Gi.default=Gi});var cc=v((wT,fc)=>{l();"use strict";var nv=uc(),sv=Fs(),av=Ti(),ov=Tt(),Dt=class{constructor(e=[]){this.version="8.4.24",this.plugins=this.normalize(e)}use(e){return this.plugins=this.plugins.concat(this.normalize([e])),this}process(e,t={}){return this.plugins.length===0&&typeof t.parser=="undefined"&&typeof t.stringifier=="undefined"&&typeof t.syntax=="undefined"?new nv(this,e,t):new sv(this,e,t)}normalize(e){let t=[];for(let r of e)if(r.postcss===!0?r=r():r.postcss&&(r=r.postcss),typeof r=="object"&&Array.isArray(r.plugins))t=t.concat(r.plugins);else if(typeof r=="object"&&r.postcssPlugin)t.push(r);else if(typeof r=="function")t.push(r);else if(!(typeof r=="object"&&(r.parse||r.stringify)))throw new Error(r+" is not a PostCSS plugin");return t}};fc.exports=Dt;Dt.default=Dt;ov.registerProcessor(Dt);av.registerProcessor(Dt)});var dc=v((bT,pc)=>{l();"use strict";var lv=yr(),uv=Ps(),fv=wr(),cv=$i(),pv=Vi(),dv=Tt(),hv=ji();function Ar(i,e){if(Array.isArray(i))return i.map(n=>Ar(n));let{inputs:t,...r}=i;if(t){e=[];for(let n of t){let a={...n,__proto__:pv.prototype};a.map&&(a.map={...a.map,__proto__:uv.prototype}),e.push(a)}}if(r.nodes&&(r.nodes=i.nodes.map(n=>Ar(n,e))),r.source){let{inputId:n,...a}=r.source;r.source=a,n!=null&&(r.source.input=e[n])}if(r.type==="root")return new dv(r);if(r.type==="decl")return new lv(r);if(r.type==="rule")return new hv(r);if(r.type==="comment")return new fv(r);if(r.type==="atrule")return new cv(r);throw new Error("Unknown node type: "+i.type)}pc.exports=Ar;Ar.default=Ar});var ge=v((vT,vc)=>{l();"use strict";var mv=ki(),hc=yr(),gv=Fs(),yv=it(),Ns=cc(),wv=mr(),bv=dc(),mc=Ti(),vv=Os(),gc=wr(),yc=$i(),xv=Ii(),kv=Vi(),Sv=Wi(),Cv=Ts(),wc=ji(),bc=Tt(),Av=gr();function j(...i){return i.length===1&&Array.isArray(i[0])&&(i=i[0]),new Ns(i)}j.plugin=function(e,t){let r=!1;function n(...s){console&&console.warn&&!r&&(r=!0,console.warn(e+`: postcss.plugin was deprecated. Migration guide:
https://evilmartians.com/chronicles/postcss-8-plugin-migration`),h.env.LANG&&h.env.LANG.startsWith("cn")&&console.warn(e+`: \u91CC\u9762 postcss.plugin \u88AB\u5F03\u7528. \u8FC1\u79FB\u6307\u5357:
https://www.w3ctech.com/topic/2226`));let o=t(...s);return o.postcssPlugin=e,o.postcssVersion=new Ns().version,o}let a;return Object.defineProperty(n,"postcss",{get(){return a||(a=n()),a}}),n.process=function(s,o,u){return j([n(u)]).process(s,o)},n};j.stringify=wv;j.parse=Sv;j.fromJSON=bv;j.list=Cv;j.comment=i=>new gc(i);j.atRule=i=>new yc(i);j.decl=i=>new hc(i);j.rule=i=>new wc(i);j.root=i=>new bc(i);j.document=i=>new mc(i);j.CssSyntaxError=mv;j.Declaration=hc;j.Container=yv;j.Processor=Ns;j.Document=mc;j.Comment=gc;j.Warning=vv;j.AtRule=yc;j.Result=xv;j.Input=kv;j.Rule=wc;j.Root=bc;j.Node=Av;gv.registerPostcss(j);vc.exports=j;j.default=j});var U,z,xT,kT,ST,CT,AT,_T,OT,ET,TT,PT,DT,IT,qT,RT,MT,BT,FT,NT,LT,$T,jT,zT,VT,UT,nt=C(()=>{l();U=X(ge()),z=U.default,xT=U.default.stringify,kT=U.default.fromJSON,ST=U.default.plugin,CT=U.default.parse,AT=U.default.list,_T=U.default.document,OT=U.default.comment,ET=U.default.atRule,TT=U.default.rule,PT=U.default.decl,DT=U.default.root,IT=U.default.CssSyntaxError,qT=U.default.Declaration,RT=U.default.Container,MT=U.default.Processor,BT=U.default.Document,FT=U.default.Comment,NT=U.default.Warning,LT=U.default.AtRule,$T=U.default.Result,jT=U.default.Input,zT=U.default.Rule,VT=U.default.Root,UT=U.default.Node});var Ls=v((GT,xc)=>{l();xc.exports=function(i,e,t,r,n){for(e=e.split?e.split("."):e,r=0;r<e.length;r++)i=i?i[e[r]]:n;return i===n?t:i}});var Yi=v((Hi,kc)=>{l();"use strict";Hi.__esModule=!0;Hi.default=Ev;function _v(i){for(var e=i.toLowerCase(),t="",r=!1,n=0;n<6&&e[n]!==void 0;n++){var a=e.charCodeAt(n),s=a>=97&&a<=102||a>=48&&a<=57;if(r=a===32,!s)break;t+=e[n]}if(t.length!==0){var o=parseInt(t,16),u=o>=55296&&o<=57343;return u||o===0||o>1114111?["\uFFFD",t.length+(r?1:0)]:[String.fromCodePoint(o),t.length+(r?1:0)]}}var Ov=/\\/;function Ev(i){var e=Ov.test(i);if(!e)return i;for(var t="",r=0;r<i.length;r++){if(i[r]==="\\"){var n=_v(i.slice(r+1,r+7));if(n!==void 0){t+=n[0],r+=n[1];continue}if(i[r+1]==="\\"){t+="\\",r++;continue}i.length===r+1&&(t+=i[r]);continue}t+=i[r]}return t}kc.exports=Hi.default});var Cc=v((Qi,Sc)=>{l();"use strict";Qi.__esModule=!0;Qi.default=Tv;function Tv(i){for(var e=arguments.length,t=new Array(e>1?e-1:0),r=1;r<e;r++)t[r-1]=arguments[r];for(;t.length>0;){var n=t.shift();if(!i[n])return;i=i[n]}return i}Sc.exports=Qi.default});var _c=v((Ji,Ac)=>{l();"use strict";Ji.__esModule=!0;Ji.default=Pv;function Pv(i){for(var e=arguments.length,t=new Array(e>1?e-1:0),r=1;r<e;r++)t[r-1]=arguments[r];for(;t.length>0;){var n=t.shift();i[n]||(i[n]={}),i=i[n]}}Ac.exports=Ji.default});var Ec=v((Xi,Oc)=>{l();"use strict";Xi.__esModule=!0;Xi.default=Dv;function Dv(i){for(var e="",t=i.indexOf("/*"),r=0;t>=0;){e=e+i.slice(r,t);var n=i.indexOf("*/",t+2);if(n<0)return e;r=n+2,t=i.indexOf("/*",r)}return e=e+i.slice(r),e}Oc.exports=Xi.default});var _r=v(Re=>{l();"use strict";Re.__esModule=!0;Re.unesc=Re.stripComments=Re.getProp=Re.ensureObject=void 0;var Iv=Ki(Yi());Re.unesc=Iv.default;var qv=Ki(Cc());Re.getProp=qv.default;var Rv=Ki(_c());Re.ensureObject=Rv.default;var Mv=Ki(Ec());Re.stripComments=Mv.default;function Ki(i){return i&&i.__esModule?i:{default:i}}});var Ue=v((Or,Dc)=>{l();"use strict";Or.__esModule=!0;Or.default=void 0;var Tc=_r();function Pc(i,e){for(var t=0;t<e.length;t++){var r=e[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(i,r.key,r)}}function Bv(i,e,t){return e&&Pc(i.prototype,e),t&&Pc(i,t),Object.defineProperty(i,"prototype",{writable:!1}),i}var Fv=function i(e,t){if(typeof e!="object"||e===null)return e;var r=new e.constructor;for(var n in e)if(!!e.hasOwnProperty(n)){var a=e[n],s=typeof a;n==="parent"&&s==="object"?t&&(r[n]=t):a instanceof Array?r[n]=a.map(function(o){return i(o,r)}):r[n]=i(a,r)}return r},Nv=function(){function i(t){t===void 0&&(t={}),Object.assign(this,t),this.spaces=this.spaces||{},this.spaces.before=this.spaces.before||"",this.spaces.after=this.spaces.after||""}var e=i.prototype;return e.remove=function(){return this.parent&&this.parent.removeChild(this),this.parent=void 0,this},e.replaceWith=function(){if(this.parent){for(var r in arguments)this.parent.insertBefore(this,arguments[r]);this.remove()}return this},e.next=function(){return this.parent.at(this.parent.index(this)+1)},e.prev=function(){return this.parent.at(this.parent.index(this)-1)},e.clone=function(r){r===void 0&&(r={});var n=Fv(this);for(var a in r)n[a]=r[a];return n},e.appendToPropertyAndEscape=function(r,n,a){this.raws||(this.raws={});var s=this[r],o=this.raws[r];this[r]=s+n,o||a!==n?this.raws[r]=(o||s)+a:delete this.raws[r]},e.setPropertyAndEscape=function(r,n,a){this.raws||(this.raws={}),this[r]=n,this.raws[r]=a},e.setPropertyWithoutEscape=function(r,n){this[r]=n,this.raws&&delete this.raws[r]},e.isAtPosition=function(r,n){if(this.source&&this.source.start&&this.source.end)return!(this.source.start.line>r||this.source.end.line<r||this.source.start.line===r&&this.source.start.column>n||this.source.end.line===r&&this.source.end.column<n)},e.stringifyProperty=function(r){return this.raws&&this.raws[r]||this[r]},e.valueToString=function(){return String(this.stringifyProperty("value"))},e.toString=function(){return[this.rawSpaceBefore,this.valueToString(),this.rawSpaceAfter].join("")},Bv(i,[{key:"rawSpaceBefore",get:function(){var r=this.raws&&this.raws.spaces&&this.raws.spaces.before;return r===void 0&&(r=this.spaces&&this.spaces.before),r||""},set:function(r){(0,Tc.ensureObject)(this,"raws","spaces"),this.raws.spaces.before=r}},{key:"rawSpaceAfter",get:function(){var r=this.raws&&this.raws.spaces&&this.raws.spaces.after;return r===void 0&&(r=this.spaces.after),r||""},set:function(r){(0,Tc.ensureObject)(this,"raws","spaces"),this.raws.spaces.after=r}}]),i}();Or.default=Nv;Dc.exports=Or.default});var ne=v(W=>{l();"use strict";W.__esModule=!0;W.UNIVERSAL=W.TAG=W.STRING=W.SELECTOR=W.ROOT=W.PSEUDO=W.NESTING=W.ID=W.COMMENT=W.COMBINATOR=W.CLASS=W.ATTRIBUTE=void 0;var Lv="tag";W.TAG=Lv;var $v="string";W.STRING=$v;var jv="selector";W.SELECTOR=jv;var zv="root";W.ROOT=zv;var Vv="pseudo";W.PSEUDO=Vv;var Uv="nesting";W.NESTING=Uv;var Wv="id";W.ID=Wv;var Gv="comment";W.COMMENT=Gv;var Hv="combinator";W.COMBINATOR=Hv;var Yv="class";W.CLASS=Yv;var Qv="attribute";W.ATTRIBUTE=Qv;var Jv="universal";W.UNIVERSAL=Jv});var Zi=v((Er,Mc)=>{l();"use strict";Er.__esModule=!0;Er.default=void 0;var Xv=Zv(Ue()),We=Kv(ne());function Ic(i){if(typeof WeakMap!="function")return null;var e=new WeakMap,t=new WeakMap;return(Ic=function(n){return n?t:e})(i)}function Kv(i,e){if(!e&&i&&i.__esModule)return i;if(i===null||typeof i!="object"&&typeof i!="function")return{default:i};var t=Ic(e);if(t&&t.has(i))return t.get(i);var r={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var a in i)if(a!=="default"&&Object.prototype.hasOwnProperty.call(i,a)){var s=n?Object.getOwnPropertyDescriptor(i,a):null;s&&(s.get||s.set)?Object.defineProperty(r,a,s):r[a]=i[a]}return r.default=i,t&&t.set(i,r),r}function Zv(i){return i&&i.__esModule?i:{default:i}}function ex(i,e){var t=typeof Symbol!="undefined"&&i[Symbol.iterator]||i["@@iterator"];if(t)return(t=t.call(i)).next.bind(t);if(Array.isArray(i)||(t=tx(i))||e&&i&&typeof i.length=="number"){t&&(i=t);var r=0;return function(){return r>=i.length?{done:!0}:{done:!1,value:i[r++]}}}throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function tx(i,e){if(!!i){if(typeof i=="string")return qc(i,e);var t=Object.prototype.toString.call(i).slice(8,-1);if(t==="Object"&&i.constructor&&(t=i.constructor.name),t==="Map"||t==="Set")return Array.from(i);if(t==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t))return qc(i,e)}}function qc(i,e){(e==null||e>i.length)&&(e=i.length);for(var t=0,r=new Array(e);t<e;t++)r[t]=i[t];return r}function Rc(i,e){for(var t=0;t<e.length;t++){var r=e[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(i,r.key,r)}}function rx(i,e,t){return e&&Rc(i.prototype,e),t&&Rc(i,t),Object.defineProperty(i,"prototype",{writable:!1}),i}function ix(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,$s(i,e)}function $s(i,e){return $s=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},$s(i,e)}var nx=function(i){ix(e,i);function e(r){var n;return n=i.call(this,r)||this,n.nodes||(n.nodes=[]),n}var t=e.prototype;return t.append=function(n){return n.parent=this,this.nodes.push(n),this},t.prepend=function(n){return n.parent=this,this.nodes.unshift(n),this},t.at=function(n){return this.nodes[n]},t.index=function(n){return typeof n=="number"?n:this.nodes.indexOf(n)},t.removeChild=function(n){n=this.index(n),this.at(n).parent=void 0,this.nodes.splice(n,1);var a;for(var s in this.indexes)a=this.indexes[s],a>=n&&(this.indexes[s]=a-1);return this},t.removeAll=function(){for(var n=ex(this.nodes),a;!(a=n()).done;){var s=a.value;s.parent=void 0}return this.nodes=[],this},t.empty=function(){return this.removeAll()},t.insertAfter=function(n,a){a.parent=this;var s=this.index(n);this.nodes.splice(s+1,0,a),a.parent=this;var o;for(var u in this.indexes)o=this.indexes[u],s<=o&&(this.indexes[u]=o+1);return this},t.insertBefore=function(n,a){a.parent=this;var s=this.index(n);this.nodes.splice(s,0,a),a.parent=this;var o;for(var u in this.indexes)o=this.indexes[u],o<=s&&(this.indexes[u]=o+1);return this},t._findChildAtPosition=function(n,a){var s=void 0;return this.each(function(o){if(o.atPosition){var u=o.atPosition(n,a);if(u)return s=u,!1}else if(o.isAtPosition(n,a))return s=o,!1}),s},t.atPosition=function(n,a){if(this.isAtPosition(n,a))return this._findChildAtPosition(n,a)||this},t._inferEndPosition=function(){this.last&&this.last.source&&this.last.source.end&&(this.source=this.source||{},this.source.end=this.source.end||{},Object.assign(this.source.end,this.last.source.end))},t.each=function(n){this.lastEach||(this.lastEach=0),this.indexes||(this.indexes={}),this.lastEach++;var a=this.lastEach;if(this.indexes[a]=0,!!this.length){for(var s,o;this.indexes[a]<this.length&&(s=this.indexes[a],o=n(this.at(s),s),o!==!1);)this.indexes[a]+=1;if(delete this.indexes[a],o===!1)return!1}},t.walk=function(n){return this.each(function(a,s){var o=n(a,s);if(o!==!1&&a.length&&(o=a.walk(n)),o===!1)return!1})},t.walkAttributes=function(n){var a=this;return this.walk(function(s){if(s.type===We.ATTRIBUTE)return n.call(a,s)})},t.walkClasses=function(n){var a=this;return this.walk(function(s){if(s.type===We.CLASS)return n.call(a,s)})},t.walkCombinators=function(n){var a=this;return this.walk(function(s){if(s.type===We.COMBINATOR)return n.call(a,s)})},t.walkComments=function(n){var a=this;return this.walk(function(s){if(s.type===We.COMMENT)return n.call(a,s)})},t.walkIds=function(n){var a=this;return this.walk(function(s){if(s.type===We.ID)return n.call(a,s)})},t.walkNesting=function(n){var a=this;return this.walk(function(s){if(s.type===We.NESTING)return n.call(a,s)})},t.walkPseudos=function(n){var a=this;return this.walk(function(s){if(s.type===We.PSEUDO)return n.call(a,s)})},t.walkTags=function(n){var a=this;return this.walk(function(s){if(s.type===We.TAG)return n.call(a,s)})},t.walkUniversals=function(n){var a=this;return this.walk(function(s){if(s.type===We.UNIVERSAL)return n.call(a,s)})},t.split=function(n){var a=this,s=[];return this.reduce(function(o,u,c){var f=n.call(a,u);return s.push(u),f?(o.push(s),s=[]):c===a.length-1&&o.push(s),o},[])},t.map=function(n){return this.nodes.map(n)},t.reduce=function(n,a){return this.nodes.reduce(n,a)},t.every=function(n){return this.nodes.every(n)},t.some=function(n){return this.nodes.some(n)},t.filter=function(n){return this.nodes.filter(n)},t.sort=function(n){return this.nodes.sort(n)},t.toString=function(){return this.map(String).join("")},rx(e,[{key:"first",get:function(){return this.at(0)}},{key:"last",get:function(){return this.at(this.length-1)}},{key:"length",get:function(){return this.nodes.length}}]),e}(Xv.default);Er.default=nx;Mc.exports=Er.default});var zs=v((Tr,Fc)=>{l();"use strict";Tr.__esModule=!0;Tr.default=void 0;var sx=ox(Zi()),ax=ne();function ox(i){return i&&i.__esModule?i:{default:i}}function Bc(i,e){for(var t=0;t<e.length;t++){var r=e[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(i,r.key,r)}}function lx(i,e,t){return e&&Bc(i.prototype,e),t&&Bc(i,t),Object.defineProperty(i,"prototype",{writable:!1}),i}function ux(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,js(i,e)}function js(i,e){return js=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},js(i,e)}var fx=function(i){ux(e,i);function e(r){var n;return n=i.call(this,r)||this,n.type=ax.ROOT,n}var t=e.prototype;return t.toString=function(){var n=this.reduce(function(a,s){return a.push(String(s)),a},[]).join(",");return this.trailingComma?n+",":n},t.error=function(n,a){return this._error?this._error(n,a):new Error(n)},lx(e,[{key:"errorGenerator",set:function(n){this._error=n}}]),e}(sx.default);Tr.default=fx;Fc.exports=Tr.default});var Us=v((Pr,Nc)=>{l();"use strict";Pr.__esModule=!0;Pr.default=void 0;var cx=dx(Zi()),px=ne();function dx(i){return i&&i.__esModule?i:{default:i}}function hx(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,Vs(i,e)}function Vs(i,e){return Vs=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},Vs(i,e)}var mx=function(i){hx(e,i);function e(t){var r;return r=i.call(this,t)||this,r.type=px.SELECTOR,r}return e}(cx.default);Pr.default=mx;Nc.exports=Pr.default});var en=v((QT,Lc)=>{l();"use strict";var gx={},yx=gx.hasOwnProperty,wx=function(e,t){if(!e)return t;var r={};for(var n in t)r[n]=yx.call(e,n)?e[n]:t[n];return r},bx=/[ -,\.\/:-@\[-\^`\{-~]/,vx=/[ -,\.\/:-@\[\]\^`\{-~]/,xx=/(^|\\+)?(\\[A-F0-9]{1,6})\x20(?![a-fA-F0-9\x20])/g,Ws=function i(e,t){t=wx(t,i.options),t.quotes!="single"&&t.quotes!="double"&&(t.quotes="single");for(var r=t.quotes=="double"?'"':"'",n=t.isIdentifier,a=e.charAt(0),s="",o=0,u=e.length;o<u;){var c=e.charAt(o++),f=c.charCodeAt(),d=void 0;if(f<32||f>126){if(f>=55296&&f<=56319&&o<u){var p=e.charCodeAt(o++);(p&64512)==56320?f=((f&1023)<<10)+(p&1023)+65536:o--}d="\\"+f.toString(16).toUpperCase()+" "}else t.escapeEverything?bx.test(c)?d="\\"+c:d="\\"+f.toString(16).toUpperCase()+" ":/[\t\n\f\r\x0B]/.test(c)?d="\\"+f.toString(16).toUpperCase()+" ":c=="\\"||!n&&(c=='"'&&r==c||c=="'"&&r==c)||n&&vx.test(c)?d="\\"+c:d=c;s+=d}return n&&(/^-[-\d]/.test(s)?s="\\-"+s.slice(1):/\d/.test(a)&&(s="\\3"+a+" "+s.slice(1))),s=s.replace(xx,function(m,b,x){return b&&b.length%2?m:(b||"")+x}),!n&&t.wrap?r+s+r:s};Ws.options={escapeEverything:!1,isIdentifier:!1,quotes:"single",wrap:!1};Ws.version="3.0.0";Lc.exports=Ws});var Hs=v((Dr,zc)=>{l();"use strict";Dr.__esModule=!0;Dr.default=void 0;var kx=$c(en()),Sx=_r(),Cx=$c(Ue()),Ax=ne();function $c(i){return i&&i.__esModule?i:{default:i}}function jc(i,e){for(var t=0;t<e.length;t++){var r=e[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(i,r.key,r)}}function _x(i,e,t){return e&&jc(i.prototype,e),t&&jc(i,t),Object.defineProperty(i,"prototype",{writable:!1}),i}function Ox(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,Gs(i,e)}function Gs(i,e){return Gs=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},Gs(i,e)}var Ex=function(i){Ox(e,i);function e(r){var n;return n=i.call(this,r)||this,n.type=Ax.CLASS,n._constructed=!0,n}var t=e.prototype;return t.valueToString=function(){return"."+i.prototype.valueToString.call(this)},_x(e,[{key:"value",get:function(){return this._value},set:function(n){if(this._constructed){var a=(0,kx.default)(n,{isIdentifier:!0});a!==n?((0,Sx.ensureObject)(this,"raws"),this.raws.value=a):this.raws&&delete this.raws.value}this._value=n}}]),e}(Cx.default);Dr.default=Ex;zc.exports=Dr.default});var Qs=v((Ir,Vc)=>{l();"use strict";Ir.__esModule=!0;Ir.default=void 0;var Tx=Dx(Ue()),Px=ne();function Dx(i){return i&&i.__esModule?i:{default:i}}function Ix(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,Ys(i,e)}function Ys(i,e){return Ys=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},Ys(i,e)}var qx=function(i){Ix(e,i);function e(t){var r;return r=i.call(this,t)||this,r.type=Px.COMMENT,r}return e}(Tx.default);Ir.default=qx;Vc.exports=Ir.default});var Xs=v((qr,Uc)=>{l();"use strict";qr.__esModule=!0;qr.default=void 0;var Rx=Bx(Ue()),Mx=ne();function Bx(i){return i&&i.__esModule?i:{default:i}}function Fx(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,Js(i,e)}function Js(i,e){return Js=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},Js(i,e)}var Nx=function(i){Fx(e,i);function e(r){var n;return n=i.call(this,r)||this,n.type=Mx.ID,n}var t=e.prototype;return t.valueToString=function(){return"#"+i.prototype.valueToString.call(this)},e}(Rx.default);qr.default=Nx;Uc.exports=qr.default});var tn=v((Rr,Hc)=>{l();"use strict";Rr.__esModule=!0;Rr.default=void 0;var Lx=Wc(en()),$x=_r(),jx=Wc(Ue());function Wc(i){return i&&i.__esModule?i:{default:i}}function Gc(i,e){for(var t=0;t<e.length;t++){var r=e[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(i,r.key,r)}}function zx(i,e,t){return e&&Gc(i.prototype,e),t&&Gc(i,t),Object.defineProperty(i,"prototype",{writable:!1}),i}function Vx(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,Ks(i,e)}function Ks(i,e){return Ks=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},Ks(i,e)}var Ux=function(i){Vx(e,i);function e(){return i.apply(this,arguments)||this}var t=e.prototype;return t.qualifiedName=function(n){return this.namespace?this.namespaceString+"|"+n:n},t.valueToString=function(){return this.qualifiedName(i.prototype.valueToString.call(this))},zx(e,[{key:"namespace",get:function(){return this._namespace},set:function(n){if(n===!0||n==="*"||n==="&"){this._namespace=n,this.raws&&delete this.raws.namespace;return}var a=(0,Lx.default)(n,{isIdentifier:!0});this._namespace=n,a!==n?((0,$x.ensureObject)(this,"raws"),this.raws.namespace=a):this.raws&&delete this.raws.namespace}},{key:"ns",get:function(){return this._namespace},set:function(n){this.namespace=n}},{key:"namespaceString",get:function(){if(this.namespace){var n=this.stringifyProperty("namespace");return n===!0?"":n}else return""}}]),e}(jx.default);Rr.default=Ux;Hc.exports=Rr.default});var ea=v((Mr,Yc)=>{l();"use strict";Mr.__esModule=!0;Mr.default=void 0;var Wx=Hx(tn()),Gx=ne();function Hx(i){return i&&i.__esModule?i:{default:i}}function Yx(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,Zs(i,e)}function Zs(i,e){return Zs=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},Zs(i,e)}var Qx=function(i){Yx(e,i);function e(t){var r;return r=i.call(this,t)||this,r.type=Gx.TAG,r}return e}(Wx.default);Mr.default=Qx;Yc.exports=Mr.default});var ra=v((Br,Qc)=>{l();"use strict";Br.__esModule=!0;Br.default=void 0;var Jx=Kx(Ue()),Xx=ne();function Kx(i){return i&&i.__esModule?i:{default:i}}function Zx(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,ta(i,e)}function ta(i,e){return ta=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},ta(i,e)}var e1=function(i){Zx(e,i);function e(t){var r;return r=i.call(this,t)||this,r.type=Xx.STRING,r}return e}(Jx.default);Br.default=e1;Qc.exports=Br.default});var na=v((Fr,Jc)=>{l();"use strict";Fr.__esModule=!0;Fr.default=void 0;var t1=i1(Zi()),r1=ne();function i1(i){return i&&i.__esModule?i:{default:i}}function n1(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,ia(i,e)}function ia(i,e){return ia=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},ia(i,e)}var s1=function(i){n1(e,i);function e(r){var n;return n=i.call(this,r)||this,n.type=r1.PSEUDO,n}var t=e.prototype;return t.toString=function(){var n=this.length?"("+this.map(String).join(",")+")":"";return[this.rawSpaceBefore,this.stringifyProperty("value"),n,this.rawSpaceAfter].join("")},e}(t1.default);Fr.default=s1;Jc.exports=Fr.default});var Xc={};Ae(Xc,{deprecate:()=>a1});function a1(i){return i}var Kc=C(()=>{l()});var ep=v((JT,Zc)=>{l();Zc.exports=(Kc(),Xc).deprecate});var fa=v($r=>{l();"use strict";$r.__esModule=!0;$r.default=void 0;$r.unescapeValue=la;var Nr=aa(en()),o1=aa(Yi()),l1=aa(tn()),u1=ne(),sa;function aa(i){return i&&i.__esModule?i:{default:i}}function tp(i,e){for(var t=0;t<e.length;t++){var r=e[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(i,r.key,r)}}function f1(i,e,t){return e&&tp(i.prototype,e),t&&tp(i,t),Object.defineProperty(i,"prototype",{writable:!1}),i}function c1(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,oa(i,e)}function oa(i,e){return oa=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},oa(i,e)}var Lr=ep(),p1=/^('|")([^]*)\1$/,d1=Lr(function(){},"Assigning an attribute a value containing characters that might need to be escaped is deprecated. Call attribute.setValue() instead."),h1=Lr(function(){},"Assigning attr.quoted is deprecated and has no effect. Assign to attr.quoteMark instead."),m1=Lr(function(){},"Constructing an Attribute selector with a value without specifying quoteMark is deprecated. Note: The value should be unescaped now.");function la(i){var e=!1,t=null,r=i,n=r.match(p1);return n&&(t=n[1],r=n[2]),r=(0,o1.default)(r),r!==i&&(e=!0),{deprecatedUsage:e,unescaped:r,quoteMark:t}}function g1(i){if(i.quoteMark!==void 0||i.value===void 0)return i;m1();var e=la(i.value),t=e.quoteMark,r=e.unescaped;return i.raws||(i.raws={}),i.raws.value===void 0&&(i.raws.value=i.value),i.value=r,i.quoteMark=t,i}var rn=function(i){c1(e,i);function e(r){var n;return r===void 0&&(r={}),n=i.call(this,g1(r))||this,n.type=u1.ATTRIBUTE,n.raws=n.raws||{},Object.defineProperty(n.raws,"unquoted",{get:Lr(function(){return n.value},"attr.raws.unquoted is deprecated. Call attr.value instead."),set:Lr(function(){return n.value},"Setting attr.raws.unquoted is deprecated and has no effect. attr.value is unescaped by default now.")}),n._constructed=!0,n}var t=e.prototype;return t.getQuotedValue=function(n){n===void 0&&(n={});var a=this._determineQuoteMark(n),s=ua[a],o=(0,Nr.default)(this._value,s);return o},t._determineQuoteMark=function(n){return n.smart?this.smartQuoteMark(n):this.preferredQuoteMark(n)},t.setValue=function(n,a){a===void 0&&(a={}),this._value=n,this._quoteMark=this._determineQuoteMark(a),this._syncRawValue()},t.smartQuoteMark=function(n){var a=this.value,s=a.replace(/[^']/g,"").length,o=a.replace(/[^"]/g,"").length;if(s+o===0){var u=(0,Nr.default)(a,{isIdentifier:!0});if(u===a)return e.NO_QUOTE;var c=this.preferredQuoteMark(n);if(c===e.NO_QUOTE){var f=this.quoteMark||n.quoteMark||e.DOUBLE_QUOTE,d=ua[f],p=(0,Nr.default)(a,d);if(p.length<u.length)return f}return c}else return o===s?this.preferredQuoteMark(n):o<s?e.DOUBLE_QUOTE:e.SINGLE_QUOTE},t.preferredQuoteMark=function(n){var a=n.preferCurrentQuoteMark?this.quoteMark:n.quoteMark;return a===void 0&&(a=n.preferCurrentQuoteMark?n.quoteMark:this.quoteMark),a===void 0&&(a=e.DOUBLE_QUOTE),a},t._syncRawValue=function(){var n=(0,Nr.default)(this._value,ua[this.quoteMark]);n===this._value?this.raws&&delete this.raws.value:this.raws.value=n},t._handleEscapes=function(n,a){if(this._constructed){var s=(0,Nr.default)(a,{isIdentifier:!0});s!==a?this.raws[n]=s:delete this.raws[n]}},t._spacesFor=function(n){var a={before:"",after:""},s=this.spaces[n]||{},o=this.raws.spaces&&this.raws.spaces[n]||{};return Object.assign(a,s,o)},t._stringFor=function(n,a,s){a===void 0&&(a=n),s===void 0&&(s=rp);var o=this._spacesFor(a);return s(this.stringifyProperty(n),o)},t.offsetOf=function(n){var a=1,s=this._spacesFor("attribute");if(a+=s.before.length,n==="namespace"||n==="ns")return this.namespace?a:-1;if(n==="attributeNS"||(a+=this.namespaceString.length,this.namespace&&(a+=1),n==="attribute"))return a;a+=this.stringifyProperty("attribute").length,a+=s.after.length;var o=this._spacesFor("operator");a+=o.before.length;var u=this.stringifyProperty("operator");if(n==="operator")return u?a:-1;a+=u.length,a+=o.after.length;var c=this._spacesFor("value");a+=c.before.length;var f=this.stringifyProperty("value");if(n==="value")return f?a:-1;a+=f.length,a+=c.after.length;var d=this._spacesFor("insensitive");return a+=d.before.length,n==="insensitive"&&this.insensitive?a:-1},t.toString=function(){var n=this,a=[this.rawSpaceBefore,"["];return a.push(this._stringFor("qualifiedAttribute","attribute")),this.operator&&(this.value||this.value==="")&&(a.push(this._stringFor("operator")),a.push(this._stringFor("value")),a.push(this._stringFor("insensitiveFlag","insensitive",function(s,o){return s.length>0&&!n.quoted&&o.before.length===0&&!(n.spaces.value&&n.spaces.value.after)&&(o.before=" "),rp(s,o)}))),a.push("]"),a.push(this.rawSpaceAfter),a.join("")},f1(e,[{key:"quoted",get:function(){var n=this.quoteMark;return n==="'"||n==='"'},set:function(n){h1()}},{key:"quoteMark",get:function(){return this._quoteMark},set:function(n){if(!this._constructed){this._quoteMark=n;return}this._quoteMark!==n&&(this._quoteMark=n,this._syncRawValue())}},{key:"qualifiedAttribute",get:function(){return this.qualifiedName(this.raws.attribute||this.attribute)}},{key:"insensitiveFlag",get:function(){return this.insensitive?"i":""}},{key:"value",get:function(){return this._value},set:function(n){if(this._constructed){var a=la(n),s=a.deprecatedUsage,o=a.unescaped,u=a.quoteMark;if(s&&d1(),o===this._value&&u===this._quoteMark)return;this._value=o,this._quoteMark=u,this._syncRawValue()}else this._value=n}},{key:"insensitive",get:function(){return this._insensitive},set:function(n){n||(this._insensitive=!1,this.raws&&(this.raws.insensitiveFlag==="I"||this.raws.insensitiveFlag==="i")&&(this.raws.insensitiveFlag=void 0)),this._insensitive=n}},{key:"attribute",get:function(){return this._attribute},set:function(n){this._handleEscapes("attribute",n),this._attribute=n}}]),e}(l1.default);$r.default=rn;rn.NO_QUOTE=null;rn.SINGLE_QUOTE="'";rn.DOUBLE_QUOTE='"';var ua=(sa={"'":{quotes:"single",wrap:!0},'"':{quotes:"double",wrap:!0}},sa[null]={isIdentifier:!0},sa);function rp(i,e){return""+e.before+i+e.after}});var pa=v((jr,ip)=>{l();"use strict";jr.__esModule=!0;jr.default=void 0;var y1=b1(tn()),w1=ne();function b1(i){return i&&i.__esModule?i:{default:i}}function v1(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,ca(i,e)}function ca(i,e){return ca=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},ca(i,e)}var x1=function(i){v1(e,i);function e(t){var r;return r=i.call(this,t)||this,r.type=w1.UNIVERSAL,r.value="*",r}return e}(y1.default);jr.default=x1;ip.exports=jr.default});var ha=v((zr,np)=>{l();"use strict";zr.__esModule=!0;zr.default=void 0;var k1=C1(Ue()),S1=ne();function C1(i){return i&&i.__esModule?i:{default:i}}function A1(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,da(i,e)}function da(i,e){return da=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},da(i,e)}var _1=function(i){A1(e,i);function e(t){var r;return r=i.call(this,t)||this,r.type=S1.COMBINATOR,r}return e}(k1.default);zr.default=_1;np.exports=zr.default});var ga=v((Vr,sp)=>{l();"use strict";Vr.__esModule=!0;Vr.default=void 0;var O1=T1(Ue()),E1=ne();function T1(i){return i&&i.__esModule?i:{default:i}}function P1(i,e){i.prototype=Object.create(e.prototype),i.prototype.constructor=i,ma(i,e)}function ma(i,e){return ma=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(r,n){return r.__proto__=n,r},ma(i,e)}var D1=function(i){P1(e,i);function e(t){var r;return r=i.call(this,t)||this,r.type=E1.NESTING,r.value="&",r}return e}(O1.default);Vr.default=D1;sp.exports=Vr.default});var op=v((nn,ap)=>{l();"use strict";nn.__esModule=!0;nn.default=I1;function I1(i){return i.sort(function(e,t){return e-t})}ap.exports=nn.default});var ya=v(D=>{l();"use strict";D.__esModule=!0;D.word=D.tilde=D.tab=D.str=D.space=D.slash=D.singleQuote=D.semicolon=D.plus=D.pipe=D.openSquare=D.openParenthesis=D.newline=D.greaterThan=D.feed=D.equals=D.doubleQuote=D.dollar=D.cr=D.comment=D.comma=D.combinator=D.colon=D.closeSquare=D.closeParenthesis=D.caret=D.bang=D.backslash=D.at=D.asterisk=D.ampersand=void 0;var q1=38;D.ampersand=q1;var R1=42;D.asterisk=R1;var M1=64;D.at=M1;var B1=44;D.comma=B1;var F1=58;D.colon=F1;var N1=59;D.semicolon=N1;var L1=40;D.openParenthesis=L1;var $1=41;D.closeParenthesis=$1;var j1=91;D.openSquare=j1;var z1=93;D.closeSquare=z1;var V1=36;D.dollar=V1;var U1=126;D.tilde=U1;var W1=94;D.caret=W1;var G1=43;D.plus=G1;var H1=61;D.equals=H1;var Y1=124;D.pipe=Y1;var Q1=62;D.greaterThan=Q1;var J1=32;D.space=J1;var lp=39;D.singleQuote=lp;var X1=34;D.doubleQuote=X1;var K1=47;D.slash=K1;var Z1=33;D.bang=Z1;var ek=92;D.backslash=ek;var tk=13;D.cr=tk;var rk=12;D.feed=rk;var ik=10;D.newline=ik;var nk=9;D.tab=nk;var sk=lp;D.str=sk;var ak=-1;D.comment=ak;var ok=-2;D.word=ok;var lk=-3;D.combinator=lk});var cp=v(Ur=>{l();"use strict";Ur.__esModule=!0;Ur.FIELDS=void 0;Ur.default=mk;var O=uk(ya()),It,V;function up(i){if(typeof WeakMap!="function")return null;var e=new WeakMap,t=new WeakMap;return(up=function(n){return n?t:e})(i)}function uk(i,e){if(!e&&i&&i.__esModule)return i;if(i===null||typeof i!="object"&&typeof i!="function")return{default:i};var t=up(e);if(t&&t.has(i))return t.get(i);var r={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var a in i)if(a!=="default"&&Object.prototype.hasOwnProperty.call(i,a)){var s=n?Object.getOwnPropertyDescriptor(i,a):null;s&&(s.get||s.set)?Object.defineProperty(r,a,s):r[a]=i[a]}return r.default=i,t&&t.set(i,r),r}var fk=(It={},It[O.tab]=!0,It[O.newline]=!0,It[O.cr]=!0,It[O.feed]=!0,It),ck=(V={},V[O.space]=!0,V[O.tab]=!0,V[O.newline]=!0,V[O.cr]=!0,V[O.feed]=!0,V[O.ampersand]=!0,V[O.asterisk]=!0,V[O.bang]=!0,V[O.comma]=!0,V[O.colon]=!0,V[O.semicolon]=!0,V[O.openParenthesis]=!0,V[O.closeParenthesis]=!0,V[O.openSquare]=!0,V[O.closeSquare]=!0,V[O.singleQuote]=!0,V[O.doubleQuote]=!0,V[O.plus]=!0,V[O.pipe]=!0,V[O.tilde]=!0,V[O.greaterThan]=!0,V[O.equals]=!0,V[O.dollar]=!0,V[O.caret]=!0,V[O.slash]=!0,V),wa={},fp="0123456789abcdefABCDEF";for(sn=0;sn<fp.length;sn++)wa[fp.charCodeAt(sn)]=!0;var sn;function pk(i,e){var t=e,r;do{if(r=i.charCodeAt(t),ck[r])return t-1;r===O.backslash?t=dk(i,t)+1:t++}while(t<i.length);return t-1}function dk(i,e){var t=e,r=i.charCodeAt(t+1);if(!fk[r])if(wa[r]){var n=0;do t++,n++,r=i.charCodeAt(t+1);while(wa[r]&&n<6);n<6&&r===O.space&&t++}else t++;return t}var hk={TYPE:0,START_LINE:1,START_COL:2,END_LINE:3,END_COL:4,START_POS:5,END_POS:6};Ur.FIELDS=hk;function mk(i){var e=[],t=i.css.valueOf(),r=t,n=r.length,a=-1,s=1,o=0,u=0,c,f,d,p,m,b,x,y,w,k,S,_,E;function I(q,R){if(i.safe)t+=R,w=t.length-1;else throw i.error("Unclosed "+q,s,o-a,o)}for(;o<n;){switch(c=t.charCodeAt(o),c===O.newline&&(a=o,s+=1),c){case O.space:case O.tab:case O.newline:case O.cr:case O.feed:w=o;do w+=1,c=t.charCodeAt(w),c===O.newline&&(a=w,s+=1);while(c===O.space||c===O.newline||c===O.tab||c===O.cr||c===O.feed);E=O.space,p=s,d=w-a-1,u=w;break;case O.plus:case O.greaterThan:case O.tilde:case O.pipe:w=o;do w+=1,c=t.charCodeAt(w);while(c===O.plus||c===O.greaterThan||c===O.tilde||c===O.pipe);E=O.combinator,p=s,d=o-a,u=w;break;case O.asterisk:case O.ampersand:case O.bang:case O.comma:case O.equals:case O.dollar:case O.caret:case O.openSquare:case O.closeSquare:case O.colon:case O.semicolon:case O.openParenthesis:case O.closeParenthesis:w=o,E=c,p=s,d=o-a,u=w+1;break;case O.singleQuote:case O.doubleQuote:_=c===O.singleQuote?"'":'"',w=o;do for(m=!1,w=t.indexOf(_,w+1),w===-1&&I("quote",_),b=w;t.charCodeAt(b-1)===O.backslash;)b-=1,m=!m;while(m);E=O.str,p=s,d=o-a,u=w+1;break;default:c===O.slash&&t.charCodeAt(o+1)===O.asterisk?(w=t.indexOf("*/",o+2)+1,w===0&&I("comment","*/"),f=t.slice(o,w+1),y=f.split(`
`),x=y.length-1,x>0?(k=s+x,S=w-y[x].length):(k=s,S=a),E=O.comment,s=k,p=k,d=w-S):c===O.slash?(w=o,E=c,p=s,d=o-a,u=w+1):(w=pk(t,o),E=O.word,p=s,d=w-a),u=w+1;break}e.push([E,s,o-a,p,d,o,u]),S&&(a=S,S=null),o=u}return e}});var bp=v((Wr,wp)=>{l();"use strict";Wr.__esModule=!0;Wr.default=void 0;var gk=be(zs()),ba=be(Us()),yk=be(Hs()),pp=be(Qs()),wk=be(Xs()),bk=be(ea()),va=be(ra()),vk=be(na()),dp=an(fa()),xk=be(pa()),xa=be(ha()),kk=be(ga()),Sk=be(op()),A=an(cp()),T=an(ya()),Ck=an(ne()),Y=_r(),bt,ka;function hp(i){if(typeof WeakMap!="function")return null;var e=new WeakMap,t=new WeakMap;return(hp=function(n){return n?t:e})(i)}function an(i,e){if(!e&&i&&i.__esModule)return i;if(i===null||typeof i!="object"&&typeof i!="function")return{default:i};var t=hp(e);if(t&&t.has(i))return t.get(i);var r={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var a in i)if(a!=="default"&&Object.prototype.hasOwnProperty.call(i,a)){var s=n?Object.getOwnPropertyDescriptor(i,a):null;s&&(s.get||s.set)?Object.defineProperty(r,a,s):r[a]=i[a]}return r.default=i,t&&t.set(i,r),r}function be(i){return i&&i.__esModule?i:{default:i}}function mp(i,e){for(var t=0;t<e.length;t++){var r=e[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(i,r.key,r)}}function Ak(i,e,t){return e&&mp(i.prototype,e),t&&mp(i,t),Object.defineProperty(i,"prototype",{writable:!1}),i}var Sa=(bt={},bt[T.space]=!0,bt[T.cr]=!0,bt[T.feed]=!0,bt[T.newline]=!0,bt[T.tab]=!0,bt),_k=Object.assign({},Sa,(ka={},ka[T.comment]=!0,ka));function gp(i){return{line:i[A.FIELDS.START_LINE],column:i[A.FIELDS.START_COL]}}function yp(i){return{line:i[A.FIELDS.END_LINE],column:i[A.FIELDS.END_COL]}}function vt(i,e,t,r){return{start:{line:i,column:e},end:{line:t,column:r}}}function qt(i){return vt(i[A.FIELDS.START_LINE],i[A.FIELDS.START_COL],i[A.FIELDS.END_LINE],i[A.FIELDS.END_COL])}function Ca(i,e){if(!!i)return vt(i[A.FIELDS.START_LINE],i[A.FIELDS.START_COL],e[A.FIELDS.END_LINE],e[A.FIELDS.END_COL])}function Rt(i,e){var t=i[e];if(typeof t=="string")return t.indexOf("\\")!==-1&&((0,Y.ensureObject)(i,"raws"),i[e]=(0,Y.unesc)(t),i.raws[e]===void 0&&(i.raws[e]=t)),i}function Aa(i,e){for(var t=-1,r=[];(t=i.indexOf(e,t+1))!==-1;)r.push(t);return r}function Ok(){var i=Array.prototype.concat.apply([],arguments);return i.filter(function(e,t){return t===i.indexOf(e)})}var Ek=function(){function i(t,r){r===void 0&&(r={}),this.rule=t,this.options=Object.assign({lossy:!1,safe:!1},r),this.position=0,this.css=typeof this.rule=="string"?this.rule:this.rule.selector,this.tokens=(0,A.default)({css:this.css,error:this._errorGenerator(),safe:this.options.safe});var n=Ca(this.tokens[0],this.tokens[this.tokens.length-1]);this.root=new gk.default({source:n}),this.root.errorGenerator=this._errorGenerator();var a=new ba.default({source:{start:{line:1,column:1}}});this.root.append(a),this.current=a,this.loop()}var e=i.prototype;return e._errorGenerator=function(){var r=this;return function(n,a){return typeof r.rule=="string"?new Error(n):r.rule.error(n,a)}},e.attribute=function(){var r=[],n=this.currToken;for(this.position++;this.position<this.tokens.length&&this.currToken[A.FIELDS.TYPE]!==T.closeSquare;)r.push(this.currToken),this.position++;if(this.currToken[A.FIELDS.TYPE]!==T.closeSquare)return this.expected("closing square bracket",this.currToken[A.FIELDS.START_POS]);var a=r.length,s={source:vt(n[1],n[2],this.currToken[3],this.currToken[4]),sourceIndex:n[A.FIELDS.START_POS]};if(a===1&&!~[T.word].indexOf(r[0][A.FIELDS.TYPE]))return this.expected("attribute",r[0][A.FIELDS.START_POS]);for(var o=0,u="",c="",f=null,d=!1;o<a;){var p=r[o],m=this.content(p),b=r[o+1];switch(p[A.FIELDS.TYPE]){case T.space:if(d=!0,this.options.lossy)break;if(f){(0,Y.ensureObject)(s,"spaces",f);var x=s.spaces[f].after||"";s.spaces[f].after=x+m;var y=(0,Y.getProp)(s,"raws","spaces",f,"after")||null;y&&(s.raws.spaces[f].after=y+m)}else u=u+m,c=c+m;break;case T.asterisk:if(b[A.FIELDS.TYPE]===T.equals)s.operator=m,f="operator";else if((!s.namespace||f==="namespace"&&!d)&&b){u&&((0,Y.ensureObject)(s,"spaces","attribute"),s.spaces.attribute.before=u,u=""),c&&((0,Y.ensureObject)(s,"raws","spaces","attribute"),s.raws.spaces.attribute.before=u,c=""),s.namespace=(s.namespace||"")+m;var w=(0,Y.getProp)(s,"raws","namespace")||null;w&&(s.raws.namespace+=m),f="namespace"}d=!1;break;case T.dollar:if(f==="value"){var k=(0,Y.getProp)(s,"raws","value");s.value+="$",k&&(s.raws.value=k+"$");break}case T.caret:b[A.FIELDS.TYPE]===T.equals&&(s.operator=m,f="operator"),d=!1;break;case T.combinator:if(m==="~"&&b[A.FIELDS.TYPE]===T.equals&&(s.operator=m,f="operator"),m!=="|"){d=!1;break}b[A.FIELDS.TYPE]===T.equals?(s.operator=m,f="operator"):!s.namespace&&!s.attribute&&(s.namespace=!0),d=!1;break;case T.word:if(b&&this.content(b)==="|"&&r[o+2]&&r[o+2][A.FIELDS.TYPE]!==T.equals&&!s.operator&&!s.namespace)s.namespace=m,f="namespace";else if(!s.attribute||f==="attribute"&&!d){u&&((0,Y.ensureObject)(s,"spaces","attribute"),s.spaces.attribute.before=u,u=""),c&&((0,Y.ensureObject)(s,"raws","spaces","attribute"),s.raws.spaces.attribute.before=c,c=""),s.attribute=(s.attribute||"")+m;var S=(0,Y.getProp)(s,"raws","attribute")||null;S&&(s.raws.attribute+=m),f="attribute"}else if(!s.value&&s.value!==""||f==="value"&&!(d||s.quoteMark)){var _=(0,Y.unesc)(m),E=(0,Y.getProp)(s,"raws","value")||"",I=s.value||"";s.value=I+_,s.quoteMark=null,(_!==m||E)&&((0,Y.ensureObject)(s,"raws"),s.raws.value=(E||I)+m),f="value"}else{var q=m==="i"||m==="I";(s.value||s.value==="")&&(s.quoteMark||d)?(s.insensitive=q,(!q||m==="I")&&((0,Y.ensureObject)(s,"raws"),s.raws.insensitiveFlag=m),f="insensitive",u&&((0,Y.ensureObject)(s,"spaces","insensitive"),s.spaces.insensitive.before=u,u=""),c&&((0,Y.ensureObject)(s,"raws","spaces","insensitive"),s.raws.spaces.insensitive.before=c,c="")):(s.value||s.value==="")&&(f="value",s.value+=m,s.raws.value&&(s.raws.value+=m))}d=!1;break;case T.str:if(!s.attribute||!s.operator)return this.error("Expected an attribute followed by an operator preceding the string.",{index:p[A.FIELDS.START_POS]});var R=(0,dp.unescapeValue)(m),J=R.unescaped,ue=R.quoteMark;s.value=J,s.quoteMark=ue,f="value",(0,Y.ensureObject)(s,"raws"),s.raws.value=m,d=!1;break;case T.equals:if(!s.attribute)return this.expected("attribute",p[A.FIELDS.START_POS],m);if(s.value)return this.error('Unexpected "=" found; an operator was already defined.',{index:p[A.FIELDS.START_POS]});s.operator=s.operator?s.operator+m:m,f="operator",d=!1;break;case T.comment:if(f)if(d||b&&b[A.FIELDS.TYPE]===T.space||f==="insensitive"){var de=(0,Y.getProp)(s,"spaces",f,"after")||"",De=(0,Y.getProp)(s,"raws","spaces",f,"after")||de;(0,Y.ensureObject)(s,"raws","spaces",f),s.raws.spaces[f].after=De+m}else{var ee=s[f]||"",oe=(0,Y.getProp)(s,"raws",f)||ee;(0,Y.ensureObject)(s,"raws"),s.raws[f]=oe+m}else c=c+m;break;default:return this.error('Unexpected "'+m+'" found.',{index:p[A.FIELDS.START_POS]})}o++}Rt(s,"attribute"),Rt(s,"namespace"),this.newNode(new dp.default(s)),this.position++},e.parseWhitespaceEquivalentTokens=function(r){r<0&&(r=this.tokens.length);var n=this.position,a=[],s="",o=void 0;do if(Sa[this.currToken[A.FIELDS.TYPE]])this.options.lossy||(s+=this.content());else if(this.currToken[A.FIELDS.TYPE]===T.comment){var u={};s&&(u.before=s,s=""),o=new pp.default({value:this.content(),source:qt(this.currToken),sourceIndex:this.currToken[A.FIELDS.START_POS],spaces:u}),a.push(o)}while(++this.position<r);if(s){if(o)o.spaces.after=s;else if(!this.options.lossy){var c=this.tokens[n],f=this.tokens[this.position-1];a.push(new va.default({value:"",source:vt(c[A.FIELDS.START_LINE],c[A.FIELDS.START_COL],f[A.FIELDS.END_LINE],f[A.FIELDS.END_COL]),sourceIndex:c[A.FIELDS.START_POS],spaces:{before:s,after:""}}))}}return a},e.convertWhitespaceNodesToSpace=function(r,n){var a=this;n===void 0&&(n=!1);var s="",o="";r.forEach(function(c){var f=a.lossySpace(c.spaces.before,n),d=a.lossySpace(c.rawSpaceBefore,n);s+=f+a.lossySpace(c.spaces.after,n&&f.length===0),o+=f+c.value+a.lossySpace(c.rawSpaceAfter,n&&d.length===0)}),o===s&&(o=void 0);var u={space:s,rawSpace:o};return u},e.isNamedCombinator=function(r){return r===void 0&&(r=this.position),this.tokens[r+0]&&this.tokens[r+0][A.FIELDS.TYPE]===T.slash&&this.tokens[r+1]&&this.tokens[r+1][A.FIELDS.TYPE]===T.word&&this.tokens[r+2]&&this.tokens[r+2][A.FIELDS.TYPE]===T.slash},e.namedCombinator=function(){if(this.isNamedCombinator()){var r=this.content(this.tokens[this.position+1]),n=(0,Y.unesc)(r).toLowerCase(),a={};n!==r&&(a.value="/"+r+"/");var s=new xa.default({value:"/"+n+"/",source:vt(this.currToken[A.FIELDS.START_LINE],this.currToken[A.FIELDS.START_COL],this.tokens[this.position+2][A.FIELDS.END_LINE],this.tokens[this.position+2][A.FIELDS.END_COL]),sourceIndex:this.currToken[A.FIELDS.START_POS],raws:a});return this.position=this.position+3,s}else this.unexpected()},e.combinator=function(){var r=this;if(this.content()==="|")return this.namespace();var n=this.locateNextMeaningfulToken(this.position);if(n<0||this.tokens[n][A.FIELDS.TYPE]===T.comma){var a=this.parseWhitespaceEquivalentTokens(n);if(a.length>0){var s=this.current.last;if(s){var o=this.convertWhitespaceNodesToSpace(a),u=o.space,c=o.rawSpace;c!==void 0&&(s.rawSpaceAfter+=c),s.spaces.after+=u}else a.forEach(function(E){return r.newNode(E)})}return}var f=this.currToken,d=void 0;n>this.position&&(d=this.parseWhitespaceEquivalentTokens(n));var p;if(this.isNamedCombinator()?p=this.namedCombinator():this.currToken[A.FIELDS.TYPE]===T.combinator?(p=new xa.default({value:this.content(),source:qt(this.currToken),sourceIndex:this.currToken[A.FIELDS.START_POS]}),this.position++):Sa[this.currToken[A.FIELDS.TYPE]]||d||this.unexpected(),p){if(d){var m=this.convertWhitespaceNodesToSpace(d),b=m.space,x=m.rawSpace;p.spaces.before=b,p.rawSpaceBefore=x}}else{var y=this.convertWhitespaceNodesToSpace(d,!0),w=y.space,k=y.rawSpace;k||(k=w);var S={},_={spaces:{}};w.endsWith(" ")&&k.endsWith(" ")?(S.before=w.slice(0,w.length-1),_.spaces.before=k.slice(0,k.length-1)):w.startsWith(" ")&&k.startsWith(" ")?(S.after=w.slice(1),_.spaces.after=k.slice(1)):_.value=k,p=new xa.default({value:" ",source:Ca(f,this.tokens[this.position-1]),sourceIndex:f[A.FIELDS.START_POS],spaces:S,raws:_})}return this.currToken&&this.currToken[A.FIELDS.TYPE]===T.space&&(p.spaces.after=this.optionalSpace(this.content()),this.position++),this.newNode(p)},e.comma=function(){if(this.position===this.tokens.length-1){this.root.trailingComma=!0,this.position++;return}this.current._inferEndPosition();var r=new ba.default({source:{start:gp(this.tokens[this.position+1])}});this.current.parent.append(r),this.current=r,this.position++},e.comment=function(){var r=this.currToken;this.newNode(new pp.default({value:this.content(),source:qt(r),sourceIndex:r[A.FIELDS.START_POS]})),this.position++},e.error=function(r,n){throw this.root.error(r,n)},e.missingBackslash=function(){return this.error("Expected a backslash preceding the semicolon.",{index:this.currToken[A.FIELDS.START_POS]})},e.missingParenthesis=function(){return this.expected("opening parenthesis",this.currToken[A.FIELDS.START_POS])},e.missingSquareBracket=function(){return this.expected("opening square bracket",this.currToken[A.FIELDS.START_POS])},e.unexpected=function(){return this.error("Unexpected '"+this.content()+"'. Escaping special characters with \\ may help.",this.currToken[A.FIELDS.START_POS])},e.unexpectedPipe=function(){return this.error("Unexpected '|'.",this.currToken[A.FIELDS.START_POS])},e.namespace=function(){var r=this.prevToken&&this.content(this.prevToken)||!0;if(this.nextToken[A.FIELDS.TYPE]===T.word)return this.position++,this.word(r);if(this.nextToken[A.FIELDS.TYPE]===T.asterisk)return this.position++,this.universal(r);this.unexpectedPipe()},e.nesting=function(){if(this.nextToken){var r=this.content(this.nextToken);if(r==="|"){this.position++;return}}var n=this.currToken;this.newNode(new kk.default({value:this.content(),source:qt(n),sourceIndex:n[A.FIELDS.START_POS]})),this.position++},e.parentheses=function(){var r=this.current.last,n=1;if(this.position++,r&&r.type===Ck.PSEUDO){var a=new ba.default({source:{start:gp(this.tokens[this.position-1])}}),s=this.current;for(r.append(a),this.current=a;this.position<this.tokens.length&&n;)this.currToken[A.FIELDS.TYPE]===T.openParenthesis&&n++,this.currToken[A.FIELDS.TYPE]===T.closeParenthesis&&n--,n?this.parse():(this.current.source.end=yp(this.currToken),this.current.parent.source.end=yp(this.currToken),this.position++);this.current=s}else{for(var o=this.currToken,u="(",c;this.position<this.tokens.length&&n;)this.currToken[A.FIELDS.TYPE]===T.openParenthesis&&n++,this.currToken[A.FIELDS.TYPE]===T.closeParenthesis&&n--,c=this.currToken,u+=this.parseParenthesisToken(this.currToken),this.position++;r?r.appendToPropertyAndEscape("value",u,u):this.newNode(new va.default({value:u,source:vt(o[A.FIELDS.START_LINE],o[A.FIELDS.START_COL],c[A.FIELDS.END_LINE],c[A.FIELDS.END_COL]),sourceIndex:o[A.FIELDS.START_POS]}))}if(n)return this.expected("closing parenthesis",this.currToken[A.FIELDS.START_POS])},e.pseudo=function(){for(var r=this,n="",a=this.currToken;this.currToken&&this.currToken[A.FIELDS.TYPE]===T.colon;)n+=this.content(),this.position++;if(!this.currToken)return this.expected(["pseudo-class","pseudo-element"],this.position-1);if(this.currToken[A.FIELDS.TYPE]===T.word)this.splitWord(!1,function(s,o){n+=s,r.newNode(new vk.default({value:n,source:Ca(a,r.currToken),sourceIndex:a[A.FIELDS.START_POS]})),o>1&&r.nextToken&&r.nextToken[A.FIELDS.TYPE]===T.openParenthesis&&r.error("Misplaced parenthesis.",{index:r.nextToken[A.FIELDS.START_POS]})});else return this.expected(["pseudo-class","pseudo-element"],this.currToken[A.FIELDS.START_POS])},e.space=function(){var r=this.content();this.position===0||this.prevToken[A.FIELDS.TYPE]===T.comma||this.prevToken[A.FIELDS.TYPE]===T.openParenthesis||this.current.nodes.every(function(n){return n.type==="comment"})?(this.spaces=this.optionalSpace(r),this.position++):this.position===this.tokens.length-1||this.nextToken[A.FIELDS.TYPE]===T.comma||this.nextToken[A.FIELDS.TYPE]===T.closeParenthesis?(this.current.last.spaces.after=this.optionalSpace(r),this.position++):this.combinator()},e.string=function(){var r=this.currToken;this.newNode(new va.default({value:this.content(),source:qt(r),sourceIndex:r[A.FIELDS.START_POS]})),this.position++},e.universal=function(r){var n=this.nextToken;if(n&&this.content(n)==="|")return this.position++,this.namespace();var a=this.currToken;this.newNode(new xk.default({value:this.content(),source:qt(a),sourceIndex:a[A.FIELDS.START_POS]}),r),this.position++},e.splitWord=function(r,n){for(var a=this,s=this.nextToken,o=this.content();s&&~[T.dollar,T.caret,T.equals,T.word].indexOf(s[A.FIELDS.TYPE]);){this.position++;var u=this.content();if(o+=u,u.lastIndexOf("\\")===u.length-1){var c=this.nextToken;c&&c[A.FIELDS.TYPE]===T.space&&(o+=this.requiredSpace(this.content(c)),this.position++)}s=this.nextToken}var f=Aa(o,".").filter(function(b){var x=o[b-1]==="\\",y=/^\d+\.\d+%$/.test(o);return!x&&!y}),d=Aa(o,"#").filter(function(b){return o[b-1]!=="\\"}),p=Aa(o,"#{");p.length&&(d=d.filter(function(b){return!~p.indexOf(b)}));var m=(0,Sk.default)(Ok([0].concat(f,d)));m.forEach(function(b,x){var y=m[x+1]||o.length,w=o.slice(b,y);if(x===0&&n)return n.call(a,w,m.length);var k,S=a.currToken,_=S[A.FIELDS.START_POS]+m[x],E=vt(S[1],S[2]+b,S[3],S[2]+(y-1));if(~f.indexOf(b)){var I={value:w.slice(1),source:E,sourceIndex:_};k=new yk.default(Rt(I,"value"))}else if(~d.indexOf(b)){var q={value:w.slice(1),source:E,sourceIndex:_};k=new wk.default(Rt(q,"value"))}else{var R={value:w,source:E,sourceIndex:_};Rt(R,"value"),k=new bk.default(R)}a.newNode(k,r),r=null}),this.position++},e.word=function(r){var n=this.nextToken;return n&&this.content(n)==="|"?(this.position++,this.namespace()):this.splitWord(r)},e.loop=function(){for(;this.position<this.tokens.length;)this.parse(!0);return this.current._inferEndPosition(),this.root},e.parse=function(r){switch(this.currToken[A.FIELDS.TYPE]){case T.space:this.space();break;case T.comment:this.comment();break;case T.openParenthesis:this.parentheses();break;case T.closeParenthesis:r&&this.missingParenthesis();break;case T.openSquare:this.attribute();break;case T.dollar:case T.caret:case T.equals:case T.word:this.word();break;case T.colon:this.pseudo();break;case T.comma:this.comma();break;case T.asterisk:this.universal();break;case T.ampersand:this.nesting();break;case T.slash:case T.combinator:this.combinator();break;case T.str:this.string();break;case T.closeSquare:this.missingSquareBracket();case T.semicolon:this.missingBackslash();default:this.unexpected()}},e.expected=function(r,n,a){if(Array.isArray(r)){var s=r.pop();r=r.join(", ")+" or "+s}var o=/^[aeiou]/.test(r[0])?"an":"a";return a?this.error("Expected "+o+" "+r+', found "'+a+'" instead.',{index:n}):this.error("Expected "+o+" "+r+".",{index:n})},e.requiredSpace=function(r){return this.options.lossy?" ":r},e.optionalSpace=function(r){return this.options.lossy?"":r},e.lossySpace=function(r,n){return this.options.lossy?n?" ":"":r},e.parseParenthesisToken=function(r){var n=this.content(r);return r[A.FIELDS.TYPE]===T.space?this.requiredSpace(n):n},e.newNode=function(r,n){return n&&(/^ +$/.test(n)&&(this.options.lossy||(this.spaces=(this.spaces||"")+n),n=!0),r.namespace=n,Rt(r,"namespace")),this.spaces&&(r.spaces.before=this.spaces,this.spaces=""),this.current.append(r)},e.content=function(r){return r===void 0&&(r=this.currToken),this.css.slice(r[A.FIELDS.START_POS],r[A.FIELDS.END_POS])},e.locateNextMeaningfulToken=function(r){r===void 0&&(r=this.position+1);for(var n=r;n<this.tokens.length;)if(_k[this.tokens[n][A.FIELDS.TYPE]]){n++;continue}else return n;return-1},Ak(i,[{key:"currToken",get:function(){return this.tokens[this.position]}},{key:"nextToken",get:function(){return this.tokens[this.position+1]}},{key:"prevToken",get:function(){return this.tokens[this.position-1]}}]),i}();Wr.default=Ek;wp.exports=Wr.default});var xp=v((Gr,vp)=>{l();"use strict";Gr.__esModule=!0;Gr.default=void 0;var Tk=Pk(bp());function Pk(i){return i&&i.__esModule?i:{default:i}}var Dk=function(){function i(t,r){this.func=t||function(){},this.funcRes=null,this.options=r}var e=i.prototype;return e._shouldUpdateSelector=function(r,n){n===void 0&&(n={});var a=Object.assign({},this.options,n);return a.updateSelector===!1?!1:typeof r!="string"},e._isLossy=function(r){r===void 0&&(r={});var n=Object.assign({},this.options,r);return n.lossless===!1},e._root=function(r,n){n===void 0&&(n={});var a=new Tk.default(r,this._parseOptions(n));return a.root},e._parseOptions=function(r){return{lossy:this._isLossy(r)}},e._run=function(r,n){var a=this;return n===void 0&&(n={}),new Promise(function(s,o){try{var u=a._root(r,n);Promise.resolve(a.func(u)).then(function(c){var f=void 0;return a._shouldUpdateSelector(r,n)&&(f=u.toString(),r.selector=f),{transform:c,root:u,string:f}}).then(s,o)}catch(c){o(c);return}})},e._runSync=function(r,n){n===void 0&&(n={});var a=this._root(r,n),s=this.func(a);if(s&&typeof s.then=="function")throw new Error("Selector processor returned a promise to a synchronous call.");var o=void 0;return n.updateSelector&&typeof r!="string"&&(o=a.toString(),r.selector=o),{transform:s,root:a,string:o}},e.ast=function(r,n){return this._run(r,n).then(function(a){return a.root})},e.astSync=function(r,n){return this._runSync(r,n).root},e.transform=function(r,n){return this._run(r,n).then(function(a){return a.transform})},e.transformSync=function(r,n){return this._runSync(r,n).transform},e.process=function(r,n){return this._run(r,n).then(function(a){return a.string||a.root.toString()})},e.processSync=function(r,n){var a=this._runSync(r,n);return a.string||a.root.toString()},i}();Gr.default=Dk;vp.exports=Gr.default});var kp=v(G=>{l();"use strict";G.__esModule=!0;G.universal=G.tag=G.string=G.selector=G.root=G.pseudo=G.nesting=G.id=G.comment=G.combinator=G.className=G.attribute=void 0;var Ik=ve(fa()),qk=ve(Hs()),Rk=ve(ha()),Mk=ve(Qs()),Bk=ve(Xs()),Fk=ve(ga()),Nk=ve(na()),Lk=ve(zs()),$k=ve(Us()),jk=ve(ra()),zk=ve(ea()),Vk=ve(pa());function ve(i){return i&&i.__esModule?i:{default:i}}var Uk=function(e){return new Ik.default(e)};G.attribute=Uk;var Wk=function(e){return new qk.default(e)};G.className=Wk;var Gk=function(e){return new Rk.default(e)};G.combinator=Gk;var Hk=function(e){return new Mk.default(e)};G.comment=Hk;var Yk=function(e){return new Bk.default(e)};G.id=Yk;var Qk=function(e){return new Fk.default(e)};G.nesting=Qk;var Jk=function(e){return new Nk.default(e)};G.pseudo=Jk;var Xk=function(e){return new Lk.default(e)};G.root=Xk;var Kk=function(e){return new $k.default(e)};G.selector=Kk;var Zk=function(e){return new jk.default(e)};G.string=Zk;var eS=function(e){return new zk.default(e)};G.tag=eS;var tS=function(e){return new Vk.default(e)};G.universal=tS});var _p=v($=>{l();"use strict";$.__esModule=!0;$.isComment=$.isCombinator=$.isClassName=$.isAttribute=void 0;$.isContainer=dS;$.isIdentifier=void 0;$.isNamespace=hS;$.isNesting=void 0;$.isNode=_a;$.isPseudo=void 0;$.isPseudoClass=pS;$.isPseudoElement=Ap;$.isUniversal=$.isTag=$.isString=$.isSelector=$.isRoot=void 0;var Q=ne(),fe,rS=(fe={},fe[Q.ATTRIBUTE]=!0,fe[Q.CLASS]=!0,fe[Q.COMBINATOR]=!0,fe[Q.COMMENT]=!0,fe[Q.ID]=!0,fe[Q.NESTING]=!0,fe[Q.PSEUDO]=!0,fe[Q.ROOT]=!0,fe[Q.SELECTOR]=!0,fe[Q.STRING]=!0,fe[Q.TAG]=!0,fe[Q.UNIVERSAL]=!0,fe);function _a(i){return typeof i=="object"&&rS[i.type]}function xe(i,e){return _a(e)&&e.type===i}var Sp=xe.bind(null,Q.ATTRIBUTE);$.isAttribute=Sp;var iS=xe.bind(null,Q.CLASS);$.isClassName=iS;var nS=xe.bind(null,Q.COMBINATOR);$.isCombinator=nS;var sS=xe.bind(null,Q.COMMENT);$.isComment=sS;var aS=xe.bind(null,Q.ID);$.isIdentifier=aS;var oS=xe.bind(null,Q.NESTING);$.isNesting=oS;var Oa=xe.bind(null,Q.PSEUDO);$.isPseudo=Oa;var lS=xe.bind(null,Q.ROOT);$.isRoot=lS;var uS=xe.bind(null,Q.SELECTOR);$.isSelector=uS;var fS=xe.bind(null,Q.STRING);$.isString=fS;var Cp=xe.bind(null,Q.TAG);$.isTag=Cp;var cS=xe.bind(null,Q.UNIVERSAL);$.isUniversal=cS;function Ap(i){return Oa(i)&&i.value&&(i.value.startsWith("::")||i.value.toLowerCase()===":before"||i.value.toLowerCase()===":after"||i.value.toLowerCase()===":first-letter"||i.value.toLowerCase()===":first-line")}function pS(i){return Oa(i)&&!Ap(i)}function dS(i){return!!(_a(i)&&i.walk)}function hS(i){return Sp(i)||Cp(i)}});var Op=v(Ee=>{l();"use strict";Ee.__esModule=!0;var Ea=ne();Object.keys(Ea).forEach(function(i){i==="default"||i==="__esModule"||i in Ee&&Ee[i]===Ea[i]||(Ee[i]=Ea[i])});var Ta=kp();Object.keys(Ta).forEach(function(i){i==="default"||i==="__esModule"||i in Ee&&Ee[i]===Ta[i]||(Ee[i]=Ta[i])});var Pa=_p();Object.keys(Pa).forEach(function(i){i==="default"||i==="__esModule"||i in Ee&&Ee[i]===Pa[i]||(Ee[i]=Pa[i])})});var Me=v((Hr,Tp)=>{l();"use strict";Hr.__esModule=!0;Hr.default=void 0;var mS=wS(xp()),gS=yS(Op());function Ep(i){if(typeof WeakMap!="function")return null;var e=new WeakMap,t=new WeakMap;return(Ep=function(n){return n?t:e})(i)}function yS(i,e){if(!e&&i&&i.__esModule)return i;if(i===null||typeof i!="object"&&typeof i!="function")return{default:i};var t=Ep(e);if(t&&t.has(i))return t.get(i);var r={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var a in i)if(a!=="default"&&Object.prototype.hasOwnProperty.call(i,a)){var s=n?Object.getOwnPropertyDescriptor(i,a):null;s&&(s.get||s.set)?Object.defineProperty(r,a,s):r[a]=i[a]}return r.default=i,t&&t.set(i,r),r}function wS(i){return i&&i.__esModule?i:{default:i}}var Da=function(e){return new mS.default(e)};Object.assign(Da,gS);delete Da.__esModule;var bS=Da;Hr.default=bS;Tp.exports=Hr.default});function Ge(i){return["fontSize","outline"].includes(i)?e=>(typeof e=="function"&&(e=e({})),Array.isArray(e)&&(e=e[0]),e):i==="fontFamily"?e=>{typeof e=="function"&&(e=e({}));let t=Array.isArray(e)&&ie(e[1])?e[0]:e;return Array.isArray(t)?t.join(", "):t}:["boxShadow","transitionProperty","transitionDuration","transitionDelay","transitionTimingFunction","backgroundImage","backgroundSize","backgroundColor","cursor","animation"].includes(i)?e=>(typeof e=="function"&&(e=e({})),Array.isArray(e)&&(e=e.join(", ")),e):["gridTemplateColumns","gridTemplateRows","objectPosition"].includes(i)?e=>(typeof e=="function"&&(e=e({})),typeof e=="string"&&(e=z.list.comma(e).join(" ")),e):(e,t={})=>(typeof e=="function"&&(e=e(t)),e)}var Yr=C(()=>{l();nt();St()});var Bp=v((a3,Ba)=>{l();var{Rule:Pp,AtRule:vS}=ge(),Dp=Me();function Ia(i,e){let t;try{Dp(r=>{t=r}).processSync(i)}catch(r){throw i.includes(":")?e?e.error("Missed semicolon"):r:e?e.error(r.message):r}return t.at(0)}function Ip(i,e){let t=!1;return i.each(r=>{if(r.type==="nesting"){let n=e.clone({});r.value!=="&"?r.replaceWith(Ia(r.value.replace("&",n.toString()))):r.replaceWith(n),t=!0}else"nodes"in r&&r.nodes&&Ip(r,e)&&(t=!0)}),t}function qp(i,e){let t=[];return i.selectors.forEach(r=>{let n=Ia(r,i);e.selectors.forEach(a=>{if(!a)return;let s=Ia(a,e);Ip(s,n)||(s.prepend(Dp.combinator({value:" "})),s.prepend(n.clone({}))),t.push(s.toString())})}),t}function on(i,e){let t=i.prev();for(e.after(i);t&&t.type==="comment";){let r=t.prev();e.after(t),t=r}return i}function xS(i){return function e(t,r,n,a=n){let s=[];if(r.each(o=>{o.type==="rule"&&n?a&&(o.selectors=qp(t,o)):o.type==="atrule"&&o.nodes?i[o.name]?e(t,o,a):r[Ra]!==!1&&s.push(o):s.push(o)}),n&&s.length){let o=t.clone({nodes:[]});for(let u of s)o.append(u);r.prepend(o)}}}function qa(i,e,t){let r=new Pp({selector:i,nodes:[]});return r.append(e),t.after(r),r}function Rp(i,e){let t={};for(let r of i)t[r]=!0;if(e)for(let r of e)t[r.replace(/^@/,"")]=!0;return t}function kS(i){i=i.trim();let e=i.match(/^\((.*)\)$/);if(!e)return{type:"basic",selector:i};let t=e[1].match(/^(with(?:out)?):(.+)$/);if(t){let r=t[1]==="with",n=Object.fromEntries(t[2].trim().split(/\s+/).map(s=>[s,!0]));if(r&&n.all)return{type:"noop"};let a=s=>!!n[s];return n.all?a=()=>!0:r&&(a=s=>s==="all"?!1:!n[s]),{type:"withrules",escapes:a}}return{type:"unknown"}}function SS(i){let e=[],t=i.parent;for(;t&&t instanceof vS;)e.push(t),t=t.parent;return e}function CS(i){let e=i[Mp];if(!e)i.after(i.nodes);else{let t=i.nodes,r,n=-1,a,s,o,u=SS(i);if(u.forEach((c,f)=>{if(e(c.name))r=c,n=f,s=o;else{let d=o;o=c.clone({nodes:[]}),d&&o.append(d),a=a||o}}),r?s?(a.append(t),r.after(s)):r.after(t):i.after(t),i.next()&&r){let c;u.slice(0,n+1).forEach((f,d,p)=>{let m=c;c=f.clone({nodes:[]}),m&&c.append(m);let b=[],y=(p[d-1]||i).next();for(;y;)b.push(y),y=y.next();c.append(b)}),c&&(s||t[t.length-1]).after(c)}}i.remove()}var Ra=Symbol("rootRuleMergeSel"),Mp=Symbol("rootRuleEscapes");function AS(i){let{params:e}=i,{type:t,selector:r,escapes:n}=kS(e);if(t==="unknown")throw i.error(`Unknown @${i.name} parameter ${JSON.stringify(e)}`);if(t==="basic"&&r){let a=new Pp({selector:r,nodes:i.nodes});i.removeAll(),i.append(a)}i[Mp]=n,i[Ra]=n?!n("all"):t==="noop"}var Ma=Symbol("hasRootRule");Ba.exports=(i={})=>{let e=Rp(["media","supports","layer","container"],i.bubble),t=xS(e),r=Rp(["document","font-face","keyframes","-webkit-keyframes","-moz-keyframes"],i.unwrap),n=(i.rootRuleName||"at-root").replace(/^@/,""),a=i.preserveEmpty;return{postcssPlugin:"postcss-nested",Once(s){s.walkAtRules(n,o=>{AS(o),s[Ma]=!0})},Rule(s){let o=!1,u=s,c=!1,f=[];s.each(d=>{d.type==="rule"?(f.length&&(u=qa(s.selector,f,u),f=[]),c=!0,o=!0,d.selectors=qp(s,d),u=on(d,u)):d.type==="atrule"?(f.length&&(u=qa(s.selector,f,u),f=[]),d.name===n?(o=!0,t(s,d,!0,d[Ra]),u=on(d,u)):e[d.name]?(c=!0,o=!0,t(s,d,!0),u=on(d,u)):r[d.name]?(c=!0,o=!0,t(s,d,!1),u=on(d,u)):c&&f.push(d)):d.type==="decl"&&c&&f.push(d)}),f.length&&(u=qa(s.selector,f,u)),o&&a!==!0&&(s.raws.semicolon=!0,s.nodes.length===0&&s.remove())},RootExit(s){s[Ma]&&(s.walkAtRules(n,CS),s[Ma]=!1)}}};Ba.exports.postcss=!0});var $p=v((o3,Lp)=>{l();"use strict";var Fp=/-(\w|$)/g,Np=(i,e)=>e.toUpperCase(),_S=i=>(i=i.toLowerCase(),i==="float"?"cssFloat":i.startsWith("-ms-")?i.substr(1).replace(Fp,Np):i.replace(Fp,Np));Lp.exports=_S});var La=v((l3,jp)=>{l();var OS=$p(),ES={boxFlex:!0,boxFlexGroup:!0,columnCount:!0,flex:!0,flexGrow:!0,flexPositive:!0,flexShrink:!0,flexNegative:!0,fontWeight:!0,lineClamp:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,tabSize:!0,widows:!0,zIndex:!0,zoom:!0,fillOpacity:!0,strokeDashoffset:!0,strokeOpacity:!0,strokeWidth:!0};function Fa(i){return typeof i.nodes=="undefined"?!0:Na(i)}function Na(i){let e,t={};return i.each(r=>{if(r.type==="atrule")e="@"+r.name,r.params&&(e+=" "+r.params),typeof t[e]=="undefined"?t[e]=Fa(r):Array.isArray(t[e])?t[e].push(Fa(r)):t[e]=[t[e],Fa(r)];else if(r.type==="rule"){let n=Na(r);if(t[r.selector])for(let a in n)t[r.selector][a]=n[a];else t[r.selector]=n}else if(r.type==="decl"){r.prop[0]==="-"&&r.prop[1]==="-"||r.parent&&r.parent.selector===":export"?e=r.prop:e=OS(r.prop);let n=r.value;!isNaN(r.value)&&ES[e]&&(n=parseFloat(r.value)),r.important&&(n+=" !important"),typeof t[e]=="undefined"?t[e]=n:Array.isArray(t[e])?t[e].push(n):t[e]=[t[e],n]}}),t}jp.exports=Na});var ln=v((u3,Wp)=>{l();var Qr=ge(),zp=/\s*!important\s*$/i,TS={"box-flex":!0,"box-flex-group":!0,"column-count":!0,flex:!0,"flex-grow":!0,"flex-positive":!0,"flex-shrink":!0,"flex-negative":!0,"font-weight":!0,"line-clamp":!0,"line-height":!0,opacity:!0,order:!0,orphans:!0,"tab-size":!0,widows:!0,"z-index":!0,zoom:!0,"fill-opacity":!0,"stroke-dashoffset":!0,"stroke-opacity":!0,"stroke-width":!0};function PS(i){return i.replace(/([A-Z])/g,"-$1").replace(/^ms-/,"-ms-").toLowerCase()}function Vp(i,e,t){t===!1||t===null||(e.startsWith("--")||(e=PS(e)),typeof t=="number"&&(t===0||TS[e]?t=t.toString():t+="px"),e==="css-float"&&(e="float"),zp.test(t)?(t=t.replace(zp,""),i.push(Qr.decl({prop:e,value:t,important:!0}))):i.push(Qr.decl({prop:e,value:t})))}function Up(i,e,t){let r=Qr.atRule({name:e[1],params:e[3]||""});typeof t=="object"&&(r.nodes=[],$a(t,r)),i.push(r)}function $a(i,e){let t,r,n;for(t in i)if(r=i[t],!(r===null||typeof r=="undefined"))if(t[0]==="@"){let a=t.match(/@(\S+)(\s+([\W\w]*)\s*)?/);if(Array.isArray(r))for(let s of r)Up(e,a,s);else Up(e,a,r)}else if(Array.isArray(r))for(let a of r)Vp(e,t,a);else typeof r=="object"?(n=Qr.rule({selector:t}),$a(r,n),e.push(n)):Vp(e,t,r)}Wp.exports=function(i){let e=Qr.root();return $a(i,e),e}});var ja=v((f3,Gp)=>{l();var DS=La();Gp.exports=function(e){return console&&console.warn&&e.warnings().forEach(t=>{let r=t.plugin||"PostCSS";console.warn(r+": "+t.text)}),DS(e.root)}});var Yp=v((c3,Hp)=>{l();var IS=ge(),qS=ja(),RS=ln();Hp.exports=function(e){let t=IS(e);return async r=>{let n=await t.process(r,{parser:RS,from:void 0});return qS(n)}}});var Jp=v((p3,Qp)=>{l();var MS=ge(),BS=ja(),FS=ln();Qp.exports=function(i){let e=MS(i);return t=>{let r=e.process(t,{parser:FS,from:void 0});return BS(r)}}});var Kp=v((d3,Xp)=>{l();var NS=La(),LS=ln(),$S=Yp(),jS=Jp();Xp.exports={objectify:NS,parse:LS,async:$S,sync:jS}});var Mt,Zp,h3,m3,g3,y3,ed=C(()=>{l();Mt=X(Kp()),Zp=Mt.default,h3=Mt.default.objectify,m3=Mt.default.parse,g3=Mt.default.async,y3=Mt.default.sync});function Bt(i){return Array.isArray(i)?i.flatMap(e=>z([(0,td.default)({bubble:["screen"]})]).process(e,{parser:Zp}).root.nodes):Bt([i])}var td,za=C(()=>{l();nt();td=X(Bp());ed()});function Ft(i,e,t=!1){if(i==="")return e;let r=typeof e=="string"?(0,rd.default)().astSync(e):e;return r.walkClasses(n=>{let a=n.value,s=t&&a.startsWith("-");n.value=s?`-${i}${a.slice(1)}`:`${i}${a}`}),typeof e=="string"?r.toString():r}var rd,un=C(()=>{l();rd=X(Me())});function ce(i){let e=id.default.className();return e.value=i,gt(e?.raws?.value??e.value)}var id,Nt=C(()=>{l();id=X(Me());mi()});function Va(i){return gt(`.${ce(i)}`)}function fn(i,e){return Va(Jr(i,e))}function Jr(i,e){return e==="DEFAULT"?i:e==="-"||e==="-DEFAULT"?`-${i}`:e.startsWith("-")?`-${i}${e}`:e.startsWith("/")?`${i}${e}`:`${i}-${e}`}var Ua=C(()=>{l();Nt();mi()});function P(i,e=[[i,[i]]],{filterDefault:t=!1,...r}={}){let n=Ge(i);return function({matchUtilities:a,theme:s}){for(let o of e){let u=Array.isArray(o[0])?o:[o];a(u.reduce((c,[f,d])=>Object.assign(c,{[f]:p=>d.reduce((m,b)=>Array.isArray(b)?Object.assign(m,{[b[0]]:b[1]}):Object.assign(m,{[b]:n(p)}),{})}),{}),{...r,values:t?Object.fromEntries(Object.entries(s(i)??{}).filter(([c])=>c!=="DEFAULT")):s(i)})}}}var nd=C(()=>{l();Yr()});function st(i){return i=Array.isArray(i)?i:[i],i.map(e=>{let t=e.values.map(r=>r.raw!==void 0?r.raw:[r.min&&`(min-width: ${r.min})`,r.max&&`(max-width: ${r.max})`].filter(Boolean).join(" and "));return e.not?`not all and ${t}`:t}).join(", ")}var cn=C(()=>{l()});function Wa(i){return i.split(YS).map(t=>{let r=t.trim(),n={value:r},a=r.split(QS),s=new Set;for(let o of a)!s.has("DIRECTIONS")&&zS.has(o)?(n.direction=o,s.add("DIRECTIONS")):!s.has("PLAY_STATES")&&VS.has(o)?(n.playState=o,s.add("PLAY_STATES")):!s.has("FILL_MODES")&&US.has(o)?(n.fillMode=o,s.add("FILL_MODES")):!s.has("ITERATION_COUNTS")&&(WS.has(o)||JS.test(o))?(n.iterationCount=o,s.add("ITERATION_COUNTS")):!s.has("TIMING_FUNCTION")&&GS.has(o)||!s.has("TIMING_FUNCTION")&&HS.some(u=>o.startsWith(`${u}(`))?(n.timingFunction=o,s.add("TIMING_FUNCTION")):!s.has("DURATION")&&sd.test(o)?(n.duration=o,s.add("DURATION")):!s.has("DELAY")&&sd.test(o)?(n.delay=o,s.add("DELAY")):s.has("NAME")?(n.unknown||(n.unknown=[]),n.unknown.push(o)):(n.name=o,s.add("NAME"));return n})}var zS,VS,US,WS,GS,HS,YS,QS,sd,JS,ad=C(()=>{l();zS=new Set(["normal","reverse","alternate","alternate-reverse"]),VS=new Set(["running","paused"]),US=new Set(["none","forwards","backwards","both"]),WS=new Set(["infinite"]),GS=new Set(["linear","ease","ease-in","ease-out","ease-in-out","step-start","step-end"]),HS=["cubic-bezier","steps"],YS=/\,(?![^(]*\))/g,QS=/\ +(?![^(]*\))/g,sd=/^(-?[\d.]+m?s)$/,JS=/^(\d+)$/});var od,re,ld=C(()=>{l();od=i=>Object.assign({},...Object.entries(i??{}).flatMap(([e,t])=>typeof t=="object"?Object.entries(od(t)).map(([r,n])=>({[e+(r==="DEFAULT"?"":`-${r}`)]:n})):[{[`${e}`]:t}])),re=od});var fd,ud=C(()=>{fd="3.4.4"});function at(i,e=!0){return Array.isArray(i)?i.map(t=>{if(e&&Array.isArray(t))throw new Error("The tuple syntax is not supported for `screens`.");if(typeof t=="string")return{name:t.toString(),not:!1,values:[{min:t,max:void 0}]};let[r,n]=t;return r=r.toString(),typeof n=="string"?{name:r,not:!1,values:[{min:n,max:void 0}]}:Array.isArray(n)?{name:r,not:!1,values:n.map(a=>pd(a))}:{name:r,not:!1,values:[pd(n)]}}):at(Object.entries(i??{}),!1)}function pn(i){return i.values.length!==1?{result:!1,reason:"multiple-values"}:i.values[0].raw!==void 0?{result:!1,reason:"raw-values"}:i.values[0].min!==void 0&&i.values[0].max!==void 0?{result:!1,reason:"min-and-max"}:{result:!0,reason:null}}function cd(i,e,t){let r=dn(e,i),n=dn(t,i),a=pn(r),s=pn(n);if(a.reason==="multiple-values"||s.reason==="multiple-values")throw new Error("Attempted to sort a screen with multiple values. This should never happen. Please open a bug report.");if(a.reason==="raw-values"||s.reason==="raw-values")throw new Error("Attempted to sort a screen with raw values. This should never happen. Please open a bug report.");if(a.reason==="min-and-max"||s.reason==="min-and-max")throw new Error("Attempted to sort a screen with both min and max values. This should never happen. Please open a bug report.");let{min:o,max:u}=r.values[0],{min:c,max:f}=n.values[0];e.not&&([o,u]=[u,o]),t.not&&([c,f]=[f,c]),o=o===void 0?o:parseFloat(o),u=u===void 0?u:parseFloat(u),c=c===void 0?c:parseFloat(c),f=f===void 0?f:parseFloat(f);let[d,p]=i==="min"?[o,c]:[f,u];return d-p}function dn(i,e){return typeof i=="object"?i:{name:"arbitrary-screen",values:[{[e]:i}]}}function pd({"min-width":i,min:e=i,max:t,raw:r}={}){return{min:e,max:t,raw:r}}var hn=C(()=>{l()});function mn(i,e){i.walkDecls(t=>{if(e.includes(t.prop)){t.remove();return}for(let r of e)t.value.includes(`/ var(${r})`)&&(t.value=t.value.replace(`/ var(${r})`,""))})}var dd=C(()=>{l()});var H,Te,Be,Fe,hd,md=C(()=>{l();je();yt();nt();nd();cn();Nt();ad();ld();lr();cs();St();Yr();ud();Oe();hn();ns();dd();ze();cr();Xr();H={childVariant:({addVariant:i})=>{i("*","& > *")},pseudoElementVariants:({addVariant:i})=>{i("first-letter","&::first-letter"),i("first-line","&::first-line"),i("marker",[({container:e})=>(mn(e,["--tw-text-opacity"]),"& *::marker"),({container:e})=>(mn(e,["--tw-text-opacity"]),"&::marker")]),i("selection",["& *::selection","&::selection"]),i("file","&::file-selector-button"),i("placeholder","&::placeholder"),i("backdrop","&::backdrop"),i("before",({container:e})=>(e.walkRules(t=>{let r=!1;t.walkDecls("content",()=>{r=!0}),r||t.prepend(z.decl({prop:"content",value:"var(--tw-content)"}))}),"&::before")),i("after",({container:e})=>(e.walkRules(t=>{let r=!1;t.walkDecls("content",()=>{r=!0}),r||t.prepend(z.decl({prop:"content",value:"var(--tw-content)"}))}),"&::after"))},pseudoClassVariants:({addVariant:i,matchVariant:e,config:t,prefix:r})=>{let n=[["first","&:first-child"],["last","&:last-child"],["only","&:only-child"],["odd","&:nth-child(odd)"],["even","&:nth-child(even)"],"first-of-type","last-of-type","only-of-type",["visited",({container:s})=>(mn(s,["--tw-text-opacity","--tw-border-opacity","--tw-bg-opacity"]),"&:visited")],"target",["open","&[open]"],"default","checked","indeterminate","placeholder-shown","autofill","optional","required","valid","invalid","in-range","out-of-range","read-only","empty","focus-within",["hover",K(t(),"hoverOnlyWhenSupported")?"@media (hover: hover) and (pointer: fine) { &:hover }":"&:hover"],"focus","focus-visible","active","enabled","disabled"].map(s=>Array.isArray(s)?s:[s,`&:${s}`]);for(let[s,o]of n)i(s,u=>typeof o=="function"?o(u):o);let a={group:(s,{modifier:o})=>o?[`:merge(${r(".group")}\\/${ce(o)})`," &"]:[`:merge(${r(".group")})`," &"],peer:(s,{modifier:o})=>o?[`:merge(${r(".peer")}\\/${ce(o)})`," ~ &"]:[`:merge(${r(".peer")})`," ~ &"]};for(let[s,o]of Object.entries(a))e(s,(u="",c)=>{let f=N(typeof u=="function"?u(c):u);f.includes("&")||(f="&"+f);let[d,p]=o("",c),m=null,b=null,x=0;for(let y=0;y<f.length;++y){let w=f[y];w==="&"?m=y:w==="'"||w==='"'?x+=1:m!==null&&w===" "&&!x&&(b=y)}return m!==null&&b===null&&(b=f.length),f.slice(0,m)+d+f.slice(m+1,b)+p+f.slice(b)},{values:Object.fromEntries(n),[ot]:{respectPrefix:!1}})},directionVariants:({addVariant:i})=>{i("ltr",'&:where([dir="ltr"], [dir="ltr"] *)'),i("rtl",'&:where([dir="rtl"], [dir="rtl"] *)')},reducedMotionVariants:({addVariant:i})=>{i("motion-safe","@media (prefers-reduced-motion: no-preference)"),i("motion-reduce","@media (prefers-reduced-motion: reduce)")},darkVariants:({config:i,addVariant:e})=>{let[t,r=".dark"]=[].concat(i("darkMode","media"));if(t===!1&&(t="media",F.warn("darkmode-false",["The `darkMode` option in your Tailwind CSS configuration is set to `false`, which now behaves the same as `media`.","Change `darkMode` to `media` or remove it entirely.","https://tailwindcss.com/docs/upgrade-guide#remove-dark-mode-configuration"])),t==="variant"){let n;if(Array.isArray(r)||typeof r=="function"?n=r:typeof r=="string"&&(n=[r]),Array.isArray(n))for(let a of n)a===".dark"?(t=!1,F.warn("darkmode-variant-without-selector",["When using `variant` for `darkMode`, you must provide a selector.",'Example: `darkMode: ["variant", ".your-selector &"]`'])):a.includes("&")||(t=!1,F.warn("darkmode-variant-without-ampersand",["When using `variant` for `darkMode`, your selector must contain `&`.",'Example `darkMode: ["variant", ".your-selector &"]`']));r=n}t==="selector"?e("dark",`&:where(${r}, ${r} *)`):t==="media"?e("dark","@media (prefers-color-scheme: dark)"):t==="variant"?e("dark",r):t==="class"&&e("dark",`&:is(${r} *)`)},printVariant:({addVariant:i})=>{i("print","@media print")},screenVariants:({theme:i,addVariant:e,matchVariant:t})=>{let r=i("screens")??{},n=Object.values(r).every(w=>typeof w=="string"),a=at(i("screens")),s=new Set([]);function o(w){return w.match(/(\D+)$/)?.[1]??"(none)"}function u(w){w!==void 0&&s.add(o(w))}function c(w){return u(w),s.size===1}for(let w of a)for(let k of w.values)u(k.min),u(k.max);let f=s.size<=1;function d(w){return Object.fromEntries(a.filter(k=>pn(k).result).map(k=>{let{min:S,max:_}=k.values[0];if(w==="min"&&S!==void 0)return k;if(w==="min"&&_!==void 0)return{...k,not:!k.not};if(w==="max"&&_!==void 0)return k;if(w==="max"&&S!==void 0)return{...k,not:!k.not}}).map(k=>[k.name,k]))}function p(w){return(k,S)=>cd(w,k.value,S.value)}let m=p("max"),b=p("min");function x(w){return k=>{if(n)if(f){if(typeof k=="string"&&!c(k))return F.warn("minmax-have-mixed-units",["The `min-*` and `max-*` variants are not supported with a `screens` configuration containing mixed units."]),[]}else return F.warn("mixed-screen-units",["The `min-*` and `max-*` variants are not supported with a `screens` configuration containing mixed units."]),[];else return F.warn("complex-screen-config",["The `min-*` and `max-*` variants are not supported with a `screens` configuration containing objects."]),[];return[`@media ${st(dn(k,w))}`]}}t("max",x("max"),{sort:m,values:n?d("max"):{}});let y="min-screens";for(let w of a)e(w.name,`@media ${st(w)}`,{id:y,sort:n&&f?b:void 0,value:w});t("min",x("min"),{id:y,sort:b})},supportsVariants:({matchVariant:i,theme:e})=>{i("supports",(t="")=>{let r=N(t),n=/^\w*\s*\(/.test(r);return r=n?r.replace(/\b(and|or|not)\b/g," $1 "):r,n?`@supports ${r}`:(r.includes(":")||(r=`${r}: var(--tw)`),r.startsWith("(")&&r.endsWith(")")||(r=`(${r})`),`@supports ${r}`)},{values:e("supports")??{}})},hasVariants:({matchVariant:i,prefix:e})=>{i("has",t=>`&:has(${N(t)})`,{values:{},[ot]:{respectPrefix:!1}}),i("group-has",(t,{modifier:r})=>r?`:merge(${e(".group")}\\/${r}):has(${N(t)}) &`:`:merge(${e(".group")}):has(${N(t)}) &`,{values:{},[ot]:{respectPrefix:!1}}),i("peer-has",(t,{modifier:r})=>r?`:merge(${e(".peer")}\\/${r}):has(${N(t)}) ~ &`:`:merge(${e(".peer")}):has(${N(t)}) ~ &`,{values:{},[ot]:{respectPrefix:!1}})},ariaVariants:({matchVariant:i,theme:e})=>{i("aria",t=>`&[aria-${N(t)}]`,{values:e("aria")??{}}),i("group-aria",(t,{modifier:r})=>r?`:merge(.group\\/${r})[aria-${N(t)}] &`:`:merge(.group)[aria-${N(t)}] &`,{values:e("aria")??{}}),i("peer-aria",(t,{modifier:r})=>r?`:merge(.peer\\/${r})[aria-${N(t)}] ~ &`:`:merge(.peer)[aria-${N(t)}] ~ &`,{values:e("aria")??{}})},dataVariants:({matchVariant:i,theme:e})=>{i("data",t=>`&[data-${N(t)}]`,{values:e("data")??{}}),i("group-data",(t,{modifier:r})=>r?`:merge(.group\\/${r})[data-${N(t)}] &`:`:merge(.group)[data-${N(t)}] &`,{values:e("data")??{}}),i("peer-data",(t,{modifier:r})=>r?`:merge(.peer\\/${r})[data-${N(t)}] ~ &`:`:merge(.peer)[data-${N(t)}] ~ &`,{values:e("data")??{}})},orientationVariants:({addVariant:i})=>{i("portrait","@media (orientation: portrait)"),i("landscape","@media (orientation: landscape)")},prefersContrastVariants:({addVariant:i})=>{i("contrast-more","@media (prefers-contrast: more)"),i("contrast-less","@media (prefers-contrast: less)")},forcedColorsVariants:({addVariant:i})=>{i("forced-colors","@media (forced-colors: active)")}},Te=["translate(var(--tw-translate-x), var(--tw-translate-y))","rotate(var(--tw-rotate))","skewX(var(--tw-skew-x))","skewY(var(--tw-skew-y))","scaleX(var(--tw-scale-x))","scaleY(var(--tw-scale-y))"].join(" "),Be=["var(--tw-blur)","var(--tw-brightness)","var(--tw-contrast)","var(--tw-grayscale)","var(--tw-hue-rotate)","var(--tw-invert)","var(--tw-saturate)","var(--tw-sepia)","var(--tw-drop-shadow)"].join(" "),Fe=["var(--tw-backdrop-blur)","var(--tw-backdrop-brightness)","var(--tw-backdrop-contrast)","var(--tw-backdrop-grayscale)","var(--tw-backdrop-hue-rotate)","var(--tw-backdrop-invert)","var(--tw-backdrop-opacity)","var(--tw-backdrop-saturate)","var(--tw-backdrop-sepia)"].join(" "),hd={preflight:({addBase:i})=>{let e=z.parse(`*,::after,::before{box-sizing:border-box;border-width:0;border-style:solid;border-color:theme('borderColor.DEFAULT', currentColor)}::after,::before{--tw-content:''}:host,html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:theme('fontFamily.sans', ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji");font-feature-settings:theme('fontFamily.sans[1].fontFeatureSettings', normal);font-variation-settings:theme('fontFamily.sans[1].fontVariationSettings', normal);-webkit-tap-highlight-color:transparent}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,pre,samp{font-family:theme('fontFamily.mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace);font-feature-settings:theme('fontFamily.mono[1].fontFeatureSettings', normal);font-variation-settings:theme('fontFamily.mono[1].fontVariationSettings', normal);font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-size:100%;font-weight:inherit;line-height:inherit;letter-spacing:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}button,input:where([type=button]),input:where([type=reset]),input:where([type=submit]){-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dd,dl,figure,h1,h2,h3,h4,h5,h6,hr,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}menu,ol,ul{list-style:none;margin:0;padding:0}dialog{padding:0}textarea{resize:vertical}input::placeholder,textarea::placeholder{opacity:1;color:theme('colors.gray.4', #9ca3af)}[role=button],button{cursor:pointer}:disabled{cursor:default}audio,canvas,embed,iframe,img,object,svg,video{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]{display:none}`);i([z.comment({text:`! tailwindcss v${fd} | MIT License | https://tailwindcss.com`}),...e.nodes])},container:(()=>{function i(t=[]){return t.flatMap(r=>r.values.map(n=>n.min)).filter(r=>r!==void 0)}function e(t,r,n){if(typeof n=="undefined")return[];if(!(typeof n=="object"&&n!==null))return[{screen:"DEFAULT",minWidth:0,padding:n}];let a=[];n.DEFAULT&&a.push({screen:"DEFAULT",minWidth:0,padding:n.DEFAULT});for(let s of t)for(let o of r)for(let{min:u}of o.values)u===s&&a.push({minWidth:s,padding:n[o.name]});return a}return function({addComponents:t,theme:r}){let n=at(r("container.screens",r("screens"))),a=i(n),s=e(a,n,r("container.padding")),o=c=>{let f=s.find(d=>d.minWidth===c);return f?{paddingRight:f.padding,paddingLeft:f.padding}:{}},u=Array.from(new Set(a.slice().sort((c,f)=>parseInt(c)-parseInt(f)))).map(c=>({[`@media (min-width: ${c})`]:{".container":{"max-width":c,...o(c)}}}));t([{".container":Object.assign({width:"100%"},r("container.center",!1)?{marginRight:"auto",marginLeft:"auto"}:{},o(0))},...u])}})(),accessibility:({addUtilities:i})=>{i({".sr-only":{position:"absolute",width:"1px",height:"1px",padding:"0",margin:"-1px",overflow:"hidden",clip:"rect(0, 0, 0, 0)",whiteSpace:"nowrap",borderWidth:"0"},".not-sr-only":{position:"static",width:"auto",height:"auto",padding:"0",margin:"0",overflow:"visible",clip:"auto",whiteSpace:"normal"}})},pointerEvents:({addUtilities:i})=>{i({".pointer-events-none":{"pointer-events":"none"},".pointer-events-auto":{"pointer-events":"auto"}})},visibility:({addUtilities:i})=>{i({".visible":{visibility:"visible"},".invisible":{visibility:"hidden"},".collapse":{visibility:"collapse"}})},position:({addUtilities:i})=>{i({".static":{position:"static"},".fixed":{position:"fixed"},".absolute":{position:"absolute"},".relative":{position:"relative"},".sticky":{position:"sticky"}})},inset:P("inset",[["inset",["inset"]],[["inset-x",["left","right"]],["inset-y",["top","bottom"]]],[["start",["inset-inline-start"]],["end",["inset-inline-end"]],["top",["top"]],["right",["right"]],["bottom",["bottom"]],["left",["left"]]]],{supportsNegativeValues:!0}),isolation:({addUtilities:i})=>{i({".isolate":{isolation:"isolate"},".isolation-auto":{isolation:"auto"}})},zIndex:P("zIndex",[["z",["zIndex"]]],{supportsNegativeValues:!0}),order:P("order",void 0,{supportsNegativeValues:!0}),gridColumn:P("gridColumn",[["col",["gridColumn"]]]),gridColumnStart:P("gridColumnStart",[["col-start",["gridColumnStart"]]],{supportsNegativeValues:!0}),gridColumnEnd:P("gridColumnEnd",[["col-end",["gridColumnEnd"]]],{supportsNegativeValues:!0}),gridRow:P("gridRow",[["row",["gridRow"]]]),gridRowStart:P("gridRowStart",[["row-start",["gridRowStart"]]],{supportsNegativeValues:!0}),gridRowEnd:P("gridRowEnd",[["row-end",["gridRowEnd"]]],{supportsNegativeValues:!0}),float:({addUtilities:i})=>{i({".float-start":{float:"inline-start"},".float-end":{float:"inline-end"},".float-right":{float:"right"},".float-left":{float:"left"},".float-none":{float:"none"}})},clear:({addUtilities:i})=>{i({".clear-start":{clear:"inline-start"},".clear-end":{clear:"inline-end"},".clear-left":{clear:"left"},".clear-right":{clear:"right"},".clear-both":{clear:"both"},".clear-none":{clear:"none"}})},margin:P("margin",[["m",["margin"]],[["mx",["margin-left","margin-right"]],["my",["margin-top","margin-bottom"]]],[["ms",["margin-inline-start"]],["me",["margin-inline-end"]],["mt",["margin-top"]],["mr",["margin-right"]],["mb",["margin-bottom"]],["ml",["margin-left"]]]],{supportsNegativeValues:!0}),boxSizing:({addUtilities:i})=>{i({".box-border":{"box-sizing":"border-box"},".box-content":{"box-sizing":"content-box"}})},lineClamp:({matchUtilities:i,addUtilities:e,theme:t})=>{i({"line-clamp":r=>({overflow:"hidden",display:"-webkit-box","-webkit-box-orient":"vertical","-webkit-line-clamp":`${r}`})},{values:t("lineClamp")}),e({".line-clamp-none":{overflow:"visible",display:"block","-webkit-box-orient":"horizontal","-webkit-line-clamp":"none"}})},display:({addUtilities:i})=>{i({".block":{display:"block"},".inline-block":{display:"inline-block"},".inline":{display:"inline"},".flex":{display:"flex"},".inline-flex":{display:"inline-flex"},".table":{display:"table"},".inline-table":{display:"inline-table"},".table-caption":{display:"table-caption"},".table-cell":{display:"table-cell"},".table-column":{display:"table-column"},".table-column-group":{display:"table-column-group"},".table-footer-group":{display:"table-footer-group"},".table-header-group":{display:"table-header-group"},".table-row-group":{display:"table-row-group"},".table-row":{display:"table-row"},".flow-root":{display:"flow-root"},".grid":{display:"grid"},".inline-grid":{display:"inline-grid"},".contents":{display:"contents"},".list-item":{display:"list-item"},".hidden":{display:"none"}})},aspectRatio:P("aspectRatio",[["aspect",["aspect-ratio"]]]),size:P("size",[["size",["width","height"]]]),height:P("height",[["h",["height"]]]),maxHeight:P("maxHeight",[["max-h",["maxHeight"]]]),minHeight:P("minHeight",[["min-h",["minHeight"]]]),width:P("width",[["w",["width"]]]),minWidth:P("minWidth",[["min-w",["minWidth"]]]),maxWidth:P("maxWidth",[["max-w",["maxWidth"]]]),flex:P("flex"),flexShrink:P("flexShrink",[["flex-shrink",["flex-shrink"]],["shrink",["flex-shrink"]]]),flexGrow:P("flexGrow",[["flex-grow",["flex-grow"]],["grow",["flex-grow"]]]),flexBasis:P("flexBasis",[["basis",["flex-basis"]]]),tableLayout:({addUtilities:i})=>{i({".table-auto":{"table-layout":"auto"},".table-fixed":{"table-layout":"fixed"}})},captionSide:({addUtilities:i})=>{i({".caption-top":{"caption-side":"top"},".caption-bottom":{"caption-side":"bottom"}})},borderCollapse:({addUtilities:i})=>{i({".border-collapse":{"border-collapse":"collapse"},".border-separate":{"border-collapse":"separate"}})},borderSpacing:({addDefaults:i,matchUtilities:e,theme:t})=>{i("border-spacing",{"--tw-border-spacing-x":0,"--tw-border-spacing-y":0}),e({"border-spacing":r=>({"--tw-border-spacing-x":r,"--tw-border-spacing-y":r,"@defaults border-spacing":{},"border-spacing":"var(--tw-border-spacing-x) var(--tw-border-spacing-y)"}),"border-spacing-x":r=>({"--tw-border-spacing-x":r,"@defaults border-spacing":{},"border-spacing":"var(--tw-border-spacing-x) var(--tw-border-spacing-y)"}),"border-spacing-y":r=>({"--tw-border-spacing-y":r,"@defaults border-spacing":{},"border-spacing":"var(--tw-border-spacing-x) var(--tw-border-spacing-y)"})},{values:t("borderSpacing")})},transformOrigin:P("transformOrigin",[["origin",["transformOrigin"]]]),translate:P("translate",[[["translate-x",[["@defaults transform",{}],"--tw-translate-x",["transform",Te]]],["translate-y",[["@defaults transform",{}],"--tw-translate-y",["transform",Te]]]]],{supportsNegativeValues:!0}),rotate:P("rotate",[["rotate",[["@defaults transform",{}],"--tw-rotate",["transform",Te]]]],{supportsNegativeValues:!0}),skew:P("skew",[[["skew-x",[["@defaults transform",{}],"--tw-skew-x",["transform",Te]]],["skew-y",[["@defaults transform",{}],"--tw-skew-y",["transform",Te]]]]],{supportsNegativeValues:!0}),scale:P("scale",[["scale",[["@defaults transform",{}],"--tw-scale-x","--tw-scale-y",["transform",Te]]],[["scale-x",[["@defaults transform",{}],"--tw-scale-x",["transform",Te]]],["scale-y",[["@defaults transform",{}],"--tw-scale-y",["transform",Te]]]]],{supportsNegativeValues:!0}),transform:({addDefaults:i,addUtilities:e})=>{i("transform",{"--tw-translate-x":"0","--tw-translate-y":"0","--tw-rotate":"0","--tw-skew-x":"0","--tw-skew-y":"0","--tw-scale-x":"1","--tw-scale-y":"1"}),e({".transform":{"@defaults transform":{},transform:Te},".transform-cpu":{transform:Te},".transform-gpu":{transform:Te.replace("translate(var(--tw-translate-x), var(--tw-translate-y))","translate3d(var(--tw-translate-x), var(--tw-translate-y), 0)")},".transform-none":{transform:"none"}})},animation:({matchUtilities:i,theme:e,config:t})=>{let r=a=>ce(t("prefix")+a),n=Object.fromEntries(Object.entries(e("keyframes")??{}).map(([a,s])=>[a,{[`@keyframes ${r(a)}`]:s}]));i({animate:a=>{let s=Wa(a);return[...s.flatMap(o=>n[o.name]),{animation:s.map(({name:o,value:u})=>o===void 0||n[o]===void 0?u:u.replace(o,r(o))).join(", ")}]}},{values:e("animation")})},cursor:P("cursor"),touchAction:({addDefaults:i,addUtilities:e})=>{i("touch-action",{"--tw-pan-x":" ","--tw-pan-y":" ","--tw-pinch-zoom":" "});let t="var(--tw-pan-x) var(--tw-pan-y) var(--tw-pinch-zoom)";e({".touch-auto":{"touch-action":"auto"},".touch-none":{"touch-action":"none"},".touch-pan-x":{"@defaults touch-action":{},"--tw-pan-x":"pan-x","touch-action":t},".touch-pan-left":{"@defaults touch-action":{},"--tw-pan-x":"pan-left","touch-action":t},".touch-pan-right":{"@defaults touch-action":{},"--tw-pan-x":"pan-right","touch-action":t},".touch-pan-y":{"@defaults touch-action":{},"--tw-pan-y":"pan-y","touch-action":t},".touch-pan-up":{"@defaults touch-action":{},"--tw-pan-y":"pan-up","touch-action":t},".touch-pan-down":{"@defaults touch-action":{},"--tw-pan-y":"pan-down","touch-action":t},".touch-pinch-zoom":{"@defaults touch-action":{},"--tw-pinch-zoom":"pinch-zoom","touch-action":t},".touch-manipulation":{"touch-action":"manipulation"}})},userSelect:({addUtilities:i})=>{i({".select-none":{"user-select":"none"},".select-text":{"user-select":"text"},".select-all":{"user-select":"all"},".select-auto":{"user-select":"auto"}})},resize:({addUtilities:i})=>{i({".resize-none":{resize:"none"},".resize-y":{resize:"vertical"},".resize-x":{resize:"horizontal"},".resize":{resize:"both"}})},scrollSnapType:({addDefaults:i,addUtilities:e})=>{i("scroll-snap-type",{"--tw-scroll-snap-strictness":"proximity"}),e({".snap-none":{"scroll-snap-type":"none"},".snap-x":{"@defaults scroll-snap-type":{},"scroll-snap-type":"x var(--tw-scroll-snap-strictness)"},".snap-y":{"@defaults scroll-snap-type":{},"scroll-snap-type":"y var(--tw-scroll-snap-strictness)"},".snap-both":{"@defaults scroll-snap-type":{},"scroll-snap-type":"both var(--tw-scroll-snap-strictness)"},".snap-mandatory":{"--tw-scroll-snap-strictness":"mandatory"},".snap-proximity":{"--tw-scroll-snap-strictness":"proximity"}})},scrollSnapAlign:({addUtilities:i})=>{i({".snap-start":{"scroll-snap-align":"start"},".snap-end":{"scroll-snap-align":"end"},".snap-center":{"scroll-snap-align":"center"},".snap-align-none":{"scroll-snap-align":"none"}})},scrollSnapStop:({addUtilities:i})=>{i({".snap-normal":{"scroll-snap-stop":"normal"},".snap-always":{"scroll-snap-stop":"always"}})},scrollMargin:P("scrollMargin",[["scroll-m",["scroll-margin"]],[["scroll-mx",["scroll-margin-left","scroll-margin-right"]],["scroll-my",["scroll-margin-top","scroll-margin-bottom"]]],[["scroll-ms",["scroll-margin-inline-start"]],["scroll-me",["scroll-margin-inline-end"]],["scroll-mt",["scroll-margin-top"]],["scroll-mr",["scroll-margin-right"]],["scroll-mb",["scroll-margin-bottom"]],["scroll-ml",["scroll-margin-left"]]]],{supportsNegativeValues:!0}),scrollPadding:P("scrollPadding",[["scroll-p",["scroll-padding"]],[["scroll-px",["scroll-padding-left","scroll-padding-right"]],["scroll-py",["scroll-padding-top","scroll-padding-bottom"]]],[["scroll-ps",["scroll-padding-inline-start"]],["scroll-pe",["scroll-padding-inline-end"]],["scroll-pt",["scroll-padding-top"]],["scroll-pr",["scroll-padding-right"]],["scroll-pb",["scroll-padding-bottom"]],["scroll-pl",["scroll-padding-left"]]]]),listStylePosition:({addUtilities:i})=>{i({".list-inside":{"list-style-position":"inside"},".list-outside":{"list-style-position":"outside"}})},listStyleType:P("listStyleType",[["list",["listStyleType"]]]),listStyleImage:P("listStyleImage",[["list-image",["listStyleImage"]]]),appearance:({addUtilities:i})=>{i({".appearance-none":{appearance:"none"},".appearance-auto":{appearance:"auto"}})},columns:P("columns",[["columns",["columns"]]]),breakBefore:({addUtilities:i})=>{i({".break-before-auto":{"break-before":"auto"},".break-before-avoid":{"break-before":"avoid"},".break-before-all":{"break-before":"all"},".break-before-avoid-page":{"break-before":"avoid-page"},".break-before-page":{"break-before":"page"},".break-before-left":{"break-before":"left"},".break-before-right":{"break-before":"right"},".break-before-column":{"break-before":"column"}})},breakInside:({addUtilities:i})=>{i({".break-inside-auto":{"break-inside":"auto"},".break-inside-avoid":{"break-inside":"avoid"},".break-inside-avoid-page":{"break-inside":"avoid-page"},".break-inside-avoid-column":{"break-inside":"avoid-column"}})},breakAfter:({addUtilities:i})=>{i({".break-after-auto":{"break-after":"auto"},".break-after-avoid":{"break-after":"avoid"},".break-after-all":{"break-after":"all"},".break-after-avoid-page":{"break-after":"avoid-page"},".break-after-page":{"break-after":"page"},".break-after-left":{"break-after":"left"},".break-after-right":{"break-after":"right"},".break-after-column":{"break-after":"column"}})},gridAutoColumns:P("gridAutoColumns",[["auto-cols",["gridAutoColumns"]]]),gridAutoFlow:({addUtilities:i})=>{i({".grid-flow-row":{gridAutoFlow:"row"},".grid-flow-col":{gridAutoFlow:"column"},".grid-flow-dense":{gridAutoFlow:"dense"},".grid-flow-row-dense":{gridAutoFlow:"row dense"},".grid-flow-col-dense":{gridAutoFlow:"column dense"}})},gridAutoRows:P("gridAutoRows",[["auto-rows",["gridAutoRows"]]]),gridTemplateColumns:P("gridTemplateColumns",[["grid-cols",["gridTemplateColumns"]]]),gridTemplateRows:P("gridTemplateRows",[["grid-rows",["gridTemplateRows"]]]),flexDirection:({addUtilities:i})=>{i({".flex-row":{"flex-direction":"row"},".flex-row-reverse":{"flex-direction":"row-reverse"},".flex-col":{"flex-direction":"column"},".flex-col-reverse":{"flex-direction":"column-reverse"}})},flexWrap:({addUtilities:i})=>{i({".flex-wrap":{"flex-wrap":"wrap"},".flex-wrap-reverse":{"flex-wrap":"wrap-reverse"},".flex-nowrap":{"flex-wrap":"nowrap"}})},placeContent:({addUtilities:i})=>{i({".place-content-center":{"place-content":"center"},".place-content-start":{"place-content":"start"},".place-content-end":{"place-content":"end"},".place-content-between":{"place-content":"space-between"},".place-content-around":{"place-content":"space-around"},".place-content-evenly":{"place-content":"space-evenly"},".place-content-baseline":{"place-content":"baseline"},".place-content-stretch":{"place-content":"stretch"}})},placeItems:({addUtilities:i})=>{i({".place-items-start":{"place-items":"start"},".place-items-end":{"place-items":"end"},".place-items-center":{"place-items":"center"},".place-items-baseline":{"place-items":"baseline"},".place-items-stretch":{"place-items":"stretch"}})},alignContent:({addUtilities:i})=>{i({".content-normal":{"align-content":"normal"},".content-center":{"align-content":"center"},".content-start":{"align-content":"flex-start"},".content-end":{"align-content":"flex-end"},".content-between":{"align-content":"space-between"},".content-around":{"align-content":"space-around"},".content-evenly":{"align-content":"space-evenly"},".content-baseline":{"align-content":"baseline"},".content-stretch":{"align-content":"stretch"}})},alignItems:({addUtilities:i})=>{i({".items-start":{"align-items":"flex-start"},".items-end":{"align-items":"flex-end"},".items-center":{"align-items":"center"},".items-baseline":{"align-items":"baseline"},".items-stretch":{"align-items":"stretch"}})},justifyContent:({addUtilities:i})=>{i({".justify-normal":{"justify-content":"normal"},".justify-start":{"justify-content":"flex-start"},".justify-end":{"justify-content":"flex-end"},".justify-center":{"justify-content":"center"},".justify-between":{"justify-content":"space-between"},".justify-around":{"justify-content":"space-around"},".justify-evenly":{"justify-content":"space-evenly"},".justify-stretch":{"justify-content":"stretch"}})},justifyItems:({addUtilities:i})=>{i({".justify-items-start":{"justify-items":"start"},".justify-items-end":{"justify-items":"end"},".justify-items-center":{"justify-items":"center"},".justify-items-stretch":{"justify-items":"stretch"}})},gap:P("gap",[["gap",["gap"]],[["gap-x",["columnGap"]],["gap-y",["rowGap"]]]]),space:({matchUtilities:i,addUtilities:e,theme:t})=>{i({"space-x":r=>(r=r==="0"?"0px":r,{"& > :not([hidden]) ~ :not([hidden])":{"--tw-space-x-reverse":"0","margin-right":`calc(${r} * var(--tw-space-x-reverse))`,"margin-left":`calc(${r} * calc(1 - var(--tw-space-x-reverse)))`}}),"space-y":r=>(r=r==="0"?"0px":r,{"& > :not([hidden]) ~ :not([hidden])":{"--tw-space-y-reverse":"0","margin-top":`calc(${r} * calc(1 - var(--tw-space-y-reverse)))`,"margin-bottom":`calc(${r} * var(--tw-space-y-reverse))`}})},{values:t("space"),supportsNegativeValues:!0}),e({".space-y-reverse > :not([hidden]) ~ :not([hidden])":{"--tw-space-y-reverse":"1"},".space-x-reverse > :not([hidden]) ~ :not([hidden])":{"--tw-space-x-reverse":"1"}})},divideWidth:({matchUtilities:i,addUtilities:e,theme:t})=>{i({"divide-x":r=>(r=r==="0"?"0px":r,{"& > :not([hidden]) ~ :not([hidden])":{"@defaults border-width":{},"--tw-divide-x-reverse":"0","border-right-width":`calc(${r} * var(--tw-divide-x-reverse))`,"border-left-width":`calc(${r} * calc(1 - var(--tw-divide-x-reverse)))`}}),"divide-y":r=>(r=r==="0"?"0px":r,{"& > :not([hidden]) ~ :not([hidden])":{"@defaults border-width":{},"--tw-divide-y-reverse":"0","border-top-width":`calc(${r} * calc(1 - var(--tw-divide-y-reverse)))`,"border-bottom-width":`calc(${r} * var(--tw-divide-y-reverse))`}})},{values:t("divideWidth"),type:["line-width","length","any"]}),e({".divide-y-reverse > :not([hidden]) ~ :not([hidden])":{"@defaults border-width":{},"--tw-divide-y-reverse":"1"},".divide-x-reverse > :not([hidden]) ~ :not([hidden])":{"@defaults border-width":{},"--tw-divide-x-reverse":"1"}})},divideStyle:({addUtilities:i})=>{i({".divide-solid > :not([hidden]) ~ :not([hidden])":{"border-style":"solid"},".divide-dashed > :not([hidden]) ~ :not([hidden])":{"border-style":"dashed"},".divide-dotted > :not([hidden]) ~ :not([hidden])":{"border-style":"dotted"},".divide-double > :not([hidden]) ~ :not([hidden])":{"border-style":"double"},".divide-none > :not([hidden]) ~ :not([hidden])":{"border-style":"none"}})},divideColor:({matchUtilities:i,theme:e,corePlugins:t})=>{i({divide:r=>t("divideOpacity")?{["& > :not([hidden]) ~ :not([hidden])"]:se({color:r,property:"border-color",variable:"--tw-divide-opacity"})}:{["& > :not([hidden]) ~ :not([hidden])"]:{"border-color":L(r)}}},{values:(({DEFAULT:r,...n})=>n)(re(e("divideColor"))),type:["color","any"]})},divideOpacity:({matchUtilities:i,theme:e})=>{i({"divide-opacity":t=>({["& > :not([hidden]) ~ :not([hidden])"]:{"--tw-divide-opacity":t}})},{values:e("divideOpacity")})},placeSelf:({addUtilities:i})=>{i({".place-self-auto":{"place-self":"auto"},".place-self-start":{"place-self":"start"},".place-self-end":{"place-self":"end"},".place-self-center":{"place-self":"center"},".place-self-stretch":{"place-self":"stretch"}})},alignSelf:({addUtilities:i})=>{i({".self-auto":{"align-self":"auto"},".self-start":{"align-self":"flex-start"},".self-end":{"align-self":"flex-end"},".self-center":{"align-self":"center"},".self-stretch":{"align-self":"stretch"},".self-baseline":{"align-self":"baseline"}})},justifySelf:({addUtilities:i})=>{i({".justify-self-auto":{"justify-self":"auto"},".justify-self-start":{"justify-self":"start"},".justify-self-end":{"justify-self":"end"},".justify-self-center":{"justify-self":"center"},".justify-self-stretch":{"justify-self":"stretch"}})},overflow:({addUtilities:i})=>{i({".overflow-auto":{overflow:"auto"},".overflow-hidden":{overflow:"hidden"},".overflow-clip":{overflow:"clip"},".overflow-visible":{overflow:"visible"},".overflow-scroll":{overflow:"scroll"},".overflow-x-auto":{"overflow-x":"auto"},".overflow-y-auto":{"overflow-y":"auto"},".overflow-x-hidden":{"overflow-x":"hidden"},".overflow-y-hidden":{"overflow-y":"hidden"},".overflow-x-clip":{"overflow-x":"clip"},".overflow-y-clip":{"overflow-y":"clip"},".overflow-x-visible":{"overflow-x":"visible"},".overflow-y-visible":{"overflow-y":"visible"},".overflow-x-scroll":{"overflow-x":"scroll"},".overflow-y-scroll":{"overflow-y":"scroll"}})},overscrollBehavior:({addUtilities:i})=>{i({".overscroll-auto":{"overscroll-behavior":"auto"},".overscroll-contain":{"overscroll-behavior":"contain"},".overscroll-none":{"overscroll-behavior":"none"},".overscroll-y-auto":{"overscroll-behavior-y":"auto"},".overscroll-y-contain":{"overscroll-behavior-y":"contain"},".overscroll-y-none":{"overscroll-behavior-y":"none"},".overscroll-x-auto":{"overscroll-behavior-x":"auto"},".overscroll-x-contain":{"overscroll-behavior-x":"contain"},".overscroll-x-none":{"overscroll-behavior-x":"none"}})},scrollBehavior:({addUtilities:i})=>{i({".scroll-auto":{"scroll-behavior":"auto"},".scroll-smooth":{"scroll-behavior":"smooth"}})},textOverflow:({addUtilities:i})=>{i({".truncate":{overflow:"hidden","text-overflow":"ellipsis","white-space":"nowrap"},".overflow-ellipsis":{"text-overflow":"ellipsis"},".text-ellipsis":{"text-overflow":"ellipsis"},".text-clip":{"text-overflow":"clip"}})},hyphens:({addUtilities:i})=>{i({".hyphens-none":{hyphens:"none"},".hyphens-manual":{hyphens:"manual"},".hyphens-auto":{hyphens:"auto"}})},whitespace:({addUtilities:i})=>{i({".whitespace-normal":{"white-space":"normal"},".whitespace-nowrap":{"white-space":"nowrap"},".whitespace-pre":{"white-space":"pre"},".whitespace-pre-line":{"white-space":"pre-line"},".whitespace-pre-wrap":{"white-space":"pre-wrap"},".whitespace-break-spaces":{"white-space":"break-spaces"}})},textWrap:({addUtilities:i})=>{i({".text-wrap":{"text-wrap":"wrap"},".text-nowrap":{"text-wrap":"nowrap"},".text-balance":{"text-wrap":"balance"},".text-pretty":{"text-wrap":"pretty"}})},wordBreak:({addUtilities:i})=>{i({".break-normal":{"overflow-wrap":"normal","word-break":"normal"},".break-words":{"overflow-wrap":"break-word"},".break-all":{"word-break":"break-all"},".break-keep":{"word-break":"keep-all"}})},borderRadius:P("borderRadius",[["rounded",["border-radius"]],[["rounded-s",["border-start-start-radius","border-end-start-radius"]],["rounded-e",["border-start-end-radius","border-end-end-radius"]],["rounded-t",["border-top-left-radius","border-top-right-radius"]],["rounded-r",["border-top-right-radius","border-bottom-right-radius"]],["rounded-b",["border-bottom-right-radius","border-bottom-left-radius"]],["rounded-l",["border-top-left-radius","border-bottom-left-radius"]]],[["rounded-ss",["border-start-start-radius"]],["rounded-se",["border-start-end-radius"]],["rounded-ee",["border-end-end-radius"]],["rounded-es",["border-end-start-radius"]],["rounded-tl",["border-top-left-radius"]],["rounded-tr",["border-top-right-radius"]],["rounded-br",["border-bottom-right-radius"]],["rounded-bl",["border-bottom-left-radius"]]]]),borderWidth:P("borderWidth",[["border",[["@defaults border-width",{}],"border-width"]],[["border-x",[["@defaults border-width",{}],"border-left-width","border-right-width"]],["border-y",[["@defaults border-width",{}],"border-top-width","border-bottom-width"]]],[["border-s",[["@defaults border-width",{}],"border-inline-start-width"]],["border-e",[["@defaults border-width",{}],"border-inline-end-width"]],["border-t",[["@defaults border-width",{}],"border-top-width"]],["border-r",[["@defaults border-width",{}],"border-right-width"]],["border-b",[["@defaults border-width",{}],"border-bottom-width"]],["border-l",[["@defaults border-width",{}],"border-left-width"]]]],{type:["line-width","length"]}),borderStyle:({addUtilities:i})=>{i({".border-solid":{"border-style":"solid"},".border-dashed":{"border-style":"dashed"},".border-dotted":{"border-style":"dotted"},".border-double":{"border-style":"double"},".border-hidden":{"border-style":"hidden"},".border-none":{"border-style":"none"}})},borderColor:({matchUtilities:i,theme:e,corePlugins:t})=>{i({border:r=>t("borderOpacity")?se({color:r,property:"border-color",variable:"--tw-border-opacity"}):{"border-color":L(r)}},{values:(({DEFAULT:r,...n})=>n)(re(e("borderColor"))),type:["color","any"]}),i({"border-x":r=>t("borderOpacity")?se({color:r,property:["border-left-color","border-right-color"],variable:"--tw-border-opacity"}):{"border-left-color":L(r),"border-right-color":L(r)},"border-y":r=>t("borderOpacity")?se({color:r,property:["border-top-color","border-bottom-color"],variable:"--tw-border-opacity"}):{"border-top-color":L(r),"border-bottom-color":L(r)}},{values:(({DEFAULT:r,...n})=>n)(re(e("borderColor"))),type:["color","any"]}),i({"border-s":r=>t("borderOpacity")?se({color:r,property:"border-inline-start-color",variable:"--tw-border-opacity"}):{"border-inline-start-color":L(r)},"border-e":r=>t("borderOpacity")?se({color:r,property:"border-inline-end-color",variable:"--tw-border-opacity"}):{"border-inline-end-color":L(r)},"border-t":r=>t("borderOpacity")?se({color:r,property:"border-top-color",variable:"--tw-border-opacity"}):{"border-top-color":L(r)},"border-r":r=>t("borderOpacity")?se({color:r,property:"border-right-color",variable:"--tw-border-opacity"}):{"border-right-color":L(r)},"border-b":r=>t("borderOpacity")?se({color:r,property:"border-bottom-color",variable:"--tw-border-opacity"}):{"border-bottom-color":L(r)},"border-l":r=>t("borderOpacity")?se({color:r,property:"border-left-color",variable:"--tw-border-opacity"}):{"border-left-color":L(r)}},{values:(({DEFAULT:r,...n})=>n)(re(e("borderColor"))),type:["color","any"]})},borderOpacity:P("borderOpacity",[["border-opacity",["--tw-border-opacity"]]]),backgroundColor:({matchUtilities:i,theme:e,corePlugins:t})=>{i({bg:r=>t("backgroundOpacity")?se({color:r,property:"background-color",variable:"--tw-bg-opacity"}):{"background-color":L(r)}},{values:re(e("backgroundColor")),type:["color","any"]})},backgroundOpacity:P("backgroundOpacity",[["bg-opacity",["--tw-bg-opacity"]]]),backgroundImage:P("backgroundImage",[["bg",["background-image"]]],{type:["lookup","image","url"]}),gradientColorStops:(()=>{function i(e){return Ie(e,0,"rgb(255 255 255 / 0)")}return function({matchUtilities:e,theme:t,addDefaults:r}){r("gradient-color-stops",{"--tw-gradient-from-position":" ","--tw-gradient-via-position":" ","--tw-gradient-to-position":" "});let n={values:re(t("gradientColorStops")),type:["color","any"]},a={values:t("gradientColorStopPositions"),type:["length","percentage"]};e({from:s=>{let o=i(s);return{"@defaults gradient-color-stops":{},"--tw-gradient-from":`${L(s)} var(--tw-gradient-from-position)`,"--tw-gradient-to":`${o} var(--tw-gradient-to-position)`,"--tw-gradient-stops":"var(--tw-gradient-from), var(--tw-gradient-to)"}}},n),e({from:s=>({"--tw-gradient-from-position":s})},a),e({via:s=>{let o=i(s);return{"@defaults gradient-color-stops":{},"--tw-gradient-to":`${o}  var(--tw-gradient-to-position)`,"--tw-gradient-stops":`var(--tw-gradient-from), ${L(s)} var(--tw-gradient-via-position), var(--tw-gradient-to)`}}},n),e({via:s=>({"--tw-gradient-via-position":s})},a),e({to:s=>({"@defaults gradient-color-stops":{},"--tw-gradient-to":`${L(s)} var(--tw-gradient-to-position)`})},n),e({to:s=>({"--tw-gradient-to-position":s})},a)}})(),boxDecorationBreak:({addUtilities:i})=>{i({".decoration-slice":{"box-decoration-break":"slice"},".decoration-clone":{"box-decoration-break":"clone"},".box-decoration-slice":{"box-decoration-break":"slice"},".box-decoration-clone":{"box-decoration-break":"clone"}})},backgroundSize:P("backgroundSize",[["bg",["background-size"]]],{type:["lookup","length","percentage","size"]}),backgroundAttachment:({addUtilities:i})=>{i({".bg-fixed":{"background-attachment":"fixed"},".bg-local":{"background-attachment":"local"},".bg-scroll":{"background-attachment":"scroll"}})},backgroundClip:({addUtilities:i})=>{i({".bg-clip-border":{"background-clip":"border-box"},".bg-clip-padding":{"background-clip":"padding-box"},".bg-clip-content":{"background-clip":"content-box"},".bg-clip-text":{"background-clip":"text"}})},backgroundPosition:P("backgroundPosition",[["bg",["background-position"]]],{type:["lookup",["position",{preferOnConflict:!0}]]}),backgroundRepeat:({addUtilities:i})=>{i({".bg-repeat":{"background-repeat":"repeat"},".bg-no-repeat":{"background-repeat":"no-repeat"},".bg-repeat-x":{"background-repeat":"repeat-x"},".bg-repeat-y":{"background-repeat":"repeat-y"},".bg-repeat-round":{"background-repeat":"round"},".bg-repeat-space":{"background-repeat":"space"}})},backgroundOrigin:({addUtilities:i})=>{i({".bg-origin-border":{"background-origin":"border-box"},".bg-origin-padding":{"background-origin":"padding-box"},".bg-origin-content":{"background-origin":"content-box"}})},fill:({matchUtilities:i,theme:e})=>{i({fill:t=>({fill:L(t)})},{values:re(e("fill")),type:["color","any"]})},stroke:({matchUtilities:i,theme:e})=>{i({stroke:t=>({stroke:L(t)})},{values:re(e("stroke")),type:["color","url","any"]})},strokeWidth:P("strokeWidth",[["stroke",["stroke-width"]]],{type:["length","number","percentage"]}),objectFit:({addUtilities:i})=>{i({".object-contain":{"object-fit":"contain"},".object-cover":{"object-fit":"cover"},".object-fill":{"object-fit":"fill"},".object-none":{"object-fit":"none"},".object-scale-down":{"object-fit":"scale-down"}})},objectPosition:P("objectPosition",[["object",["object-position"]]]),padding:P("padding",[["p",["padding"]],[["px",["padding-left","padding-right"]],["py",["padding-top","padding-bottom"]]],[["ps",["padding-inline-start"]],["pe",["padding-inline-end"]],["pt",["padding-top"]],["pr",["padding-right"]],["pb",["padding-bottom"]],["pl",["padding-left"]]]]),textAlign:({addUtilities:i})=>{i({".text-left":{"text-align":"left"},".text-center":{"text-align":"center"},".text-right":{"text-align":"right"},".text-justify":{"text-align":"justify"},".text-start":{"text-align":"start"},".text-end":{"text-align":"end"}})},textIndent:P("textIndent",[["indent",["text-indent"]]],{supportsNegativeValues:!0}),verticalAlign:({addUtilities:i,matchUtilities:e})=>{i({".align-baseline":{"vertical-align":"baseline"},".align-top":{"vertical-align":"top"},".align-middle":{"vertical-align":"middle"},".align-bottom":{"vertical-align":"bottom"},".align-text-top":{"vertical-align":"text-top"},".align-text-bottom":{"vertical-align":"text-bottom"},".align-sub":{"vertical-align":"sub"},".align-super":{"vertical-align":"super"}}),e({align:t=>({"vertical-align":t})})},fontFamily:({matchUtilities:i,theme:e})=>{i({font:t=>{let[r,n={}]=Array.isArray(t)&&ie(t[1])?t:[t],{fontFeatureSettings:a,fontVariationSettings:s}=n;return{"font-family":Array.isArray(r)?r.join(", "):r,...a===void 0?{}:{"font-feature-settings":a},...s===void 0?{}:{"font-variation-settings":s}}}},{values:e("fontFamily"),type:["lookup","generic-name","family-name"]})},fontSize:({matchUtilities:i,theme:e})=>{i({text:(t,{modifier:r})=>{let[n,a]=Array.isArray(t)?t:[t];if(r)return{"font-size":n,"line-height":r};let{lineHeight:s,letterSpacing:o,fontWeight:u}=ie(a)?a:{lineHeight:a};return{"font-size":n,...s===void 0?{}:{"line-height":s},...o===void 0?{}:{"letter-spacing":o},...u===void 0?{}:{"font-weight":u}}}},{values:e("fontSize"),modifiers:e("lineHeight"),type:["absolute-size","relative-size","length","percentage"]})},fontWeight:P("fontWeight",[["font",["fontWeight"]]],{type:["lookup","number","any"]}),textTransform:({addUtilities:i})=>{i({".uppercase":{"text-transform":"uppercase"},".lowercase":{"text-transform":"lowercase"},".capitalize":{"text-transform":"capitalize"},".normal-case":{"text-transform":"none"}})},fontStyle:({addUtilities:i})=>{i({".italic":{"font-style":"italic"},".not-italic":{"font-style":"normal"}})},fontVariantNumeric:({addDefaults:i,addUtilities:e})=>{let t="var(--tw-ordinal) var(--tw-slashed-zero) var(--tw-numeric-figure) var(--tw-numeric-spacing) var(--tw-numeric-fraction)";i("font-variant-numeric",{"--tw-ordinal":" ","--tw-slashed-zero":" ","--tw-numeric-figure":" ","--tw-numeric-spacing":" ","--tw-numeric-fraction":" "}),e({".normal-nums":{"font-variant-numeric":"normal"},".ordinal":{"@defaults font-variant-numeric":{},"--tw-ordinal":"ordinal","font-variant-numeric":t},".slashed-zero":{"@defaults font-variant-numeric":{},"--tw-slashed-zero":"slashed-zero","font-variant-numeric":t},".lining-nums":{"@defaults font-variant-numeric":{},"--tw-numeric-figure":"lining-nums","font-variant-numeric":t},".oldstyle-nums":{"@defaults font-variant-numeric":{},"--tw-numeric-figure":"oldstyle-nums","font-variant-numeric":t},".proportional-nums":{"@defaults font-variant-numeric":{},"--tw-numeric-spacing":"proportional-nums","font-variant-numeric":t},".tabular-nums":{"@defaults font-variant-numeric":{},"--tw-numeric-spacing":"tabular-nums","font-variant-numeric":t},".diagonal-fractions":{"@defaults font-variant-numeric":{},"--tw-numeric-fraction":"diagonal-fractions","font-variant-numeric":t},".stacked-fractions":{"@defaults font-variant-numeric":{},"--tw-numeric-fraction":"stacked-fractions","font-variant-numeric":t}})},lineHeight:P("lineHeight",[["leading",["lineHeight"]]]),letterSpacing:P("letterSpacing",[["tracking",["letterSpacing"]]],{supportsNegativeValues:!0}),textColor:({matchUtilities:i,theme:e,corePlugins:t})=>{i({text:r=>t("textOpacity")?se({color:r,property:"color",variable:"--tw-text-opacity"}):{color:L(r)}},{values:re(e("textColor")),type:["color","any"]})},textOpacity:P("textOpacity",[["text-opacity",["--tw-text-opacity"]]]),textDecoration:({addUtilities:i})=>{i({".underline":{"text-decoration-line":"underline"},".overline":{"text-decoration-line":"overline"},".line-through":{"text-decoration-line":"line-through"},".no-underline":{"text-decoration-line":"none"}})},textDecorationColor:({matchUtilities:i,theme:e})=>{i({decoration:t=>({"text-decoration-color":L(t)})},{values:re(e("textDecorationColor")),type:["color","any"]})},textDecorationStyle:({addUtilities:i})=>{i({".decoration-solid":{"text-decoration-style":"solid"},".decoration-double":{"text-decoration-style":"double"},".decoration-dotted":{"text-decoration-style":"dotted"},".decoration-dashed":{"text-decoration-style":"dashed"},".decoration-wavy":{"text-decoration-style":"wavy"}})},textDecorationThickness:P("textDecorationThickness",[["decoration",["text-decoration-thickness"]]],{type:["length","percentage"]}),textUnderlineOffset:P("textUnderlineOffset",[["underline-offset",["text-underline-offset"]]],{type:["length","percentage","any"]}),fontSmoothing:({addUtilities:i})=>{i({".antialiased":{"-webkit-font-smoothing":"antialiased","-moz-osx-font-smoothing":"grayscale"},".subpixel-antialiased":{"-webkit-font-smoothing":"auto","-moz-osx-font-smoothing":"auto"}})},placeholderColor:({matchUtilities:i,theme:e,corePlugins:t})=>{i({placeholder:r=>t("placeholderOpacity")?{"&::placeholder":se({color:r,property:"color",variable:"--tw-placeholder-opacity"})}:{"&::placeholder":{color:L(r)}}},{values:re(e("placeholderColor")),type:["color","any"]})},placeholderOpacity:({matchUtilities:i,theme:e})=>{i({"placeholder-opacity":t=>({["&::placeholder"]:{"--tw-placeholder-opacity":t}})},{values:e("placeholderOpacity")})},caretColor:({matchUtilities:i,theme:e})=>{i({caret:t=>({"caret-color":L(t)})},{values:re(e("caretColor")),type:["color","any"]})},accentColor:({matchUtilities:i,theme:e})=>{i({accent:t=>({"accent-color":L(t)})},{values:re(e("accentColor")),type:["color","any"]})},opacity:P("opacity",[["opacity",["opacity"]]]),backgroundBlendMode:({addUtilities:i})=>{i({".bg-blend-normal":{"background-blend-mode":"normal"},".bg-blend-multiply":{"background-blend-mode":"multiply"},".bg-blend-screen":{"background-blend-mode":"screen"},".bg-blend-overlay":{"background-blend-mode":"overlay"},".bg-blend-darken":{"background-blend-mode":"darken"},".bg-blend-lighten":{"background-blend-mode":"lighten"},".bg-blend-color-dodge":{"background-blend-mode":"color-dodge"},".bg-blend-color-burn":{"background-blend-mode":"color-burn"},".bg-blend-hard-light":{"background-blend-mode":"hard-light"},".bg-blend-soft-light":{"background-blend-mode":"soft-light"},".bg-blend-difference":{"background-blend-mode":"difference"},".bg-blend-exclusion":{"background-blend-mode":"exclusion"},".bg-blend-hue":{"background-blend-mode":"hue"},".bg-blend-saturation":{"background-blend-mode":"saturation"},".bg-blend-color":{"background-blend-mode":"color"},".bg-blend-luminosity":{"background-blend-mode":"luminosity"}})},mixBlendMode:({addUtilities:i})=>{i({".mix-blend-normal":{"mix-blend-mode":"normal"},".mix-blend-multiply":{"mix-blend-mode":"multiply"},".mix-blend-screen":{"mix-blend-mode":"screen"},".mix-blend-overlay":{"mix-blend-mode":"overlay"},".mix-blend-darken":{"mix-blend-mode":"darken"},".mix-blend-lighten":{"mix-blend-mode":"lighten"},".mix-blend-color-dodge":{"mix-blend-mode":"color-dodge"},".mix-blend-color-burn":{"mix-blend-mode":"color-burn"},".mix-blend-hard-light":{"mix-blend-mode":"hard-light"},".mix-blend-soft-light":{"mix-blend-mode":"soft-light"},".mix-blend-difference":{"mix-blend-mode":"difference"},".mix-blend-exclusion":{"mix-blend-mode":"exclusion"},".mix-blend-hue":{"mix-blend-mode":"hue"},".mix-blend-saturation":{"mix-blend-mode":"saturation"},".mix-blend-color":{"mix-blend-mode":"color"},".mix-blend-luminosity":{"mix-blend-mode":"luminosity"},".mix-blend-plus-darker":{"mix-blend-mode":"plus-darker"},".mix-blend-plus-lighter":{"mix-blend-mode":"plus-lighter"}})},boxShadow:(()=>{let i=Ge("boxShadow"),e=["var(--tw-ring-offset-shadow, 0 0 #0000)","var(--tw-ring-shadow, 0 0 #0000)","var(--tw-shadow)"].join(", ");return function({matchUtilities:t,addDefaults:r,theme:n}){r("box-shadow",{"--tw-ring-offset-shadow":"0 0 #0000","--tw-ring-shadow":"0 0 #0000","--tw-shadow":"0 0 #0000","--tw-shadow-colored":"0 0 #0000"}),t({shadow:a=>{a=i(a);let s=yi(a);for(let o of s)!o.valid||(o.color="var(--tw-shadow-color)");return{"@defaults box-shadow":{},"--tw-shadow":a==="none"?"0 0 #0000":a,"--tw-shadow-colored":a==="none"?"0 0 #0000":Tu(s),"box-shadow":e}}},{values:n("boxShadow"),type:["shadow"]})}})(),boxShadowColor:({matchUtilities:i,theme:e})=>{i({shadow:t=>({"--tw-shadow-color":L(t),"--tw-shadow":"var(--tw-shadow-colored)"})},{values:re(e("boxShadowColor")),type:["color","any"]})},outlineStyle:({addUtilities:i})=>{i({".outline-none":{outline:"2px solid transparent","outline-offset":"2px"},".outline":{"outline-style":"solid"},".outline-dashed":{"outline-style":"dashed"},".outline-dotted":{"outline-style":"dotted"},".outline-double":{"outline-style":"double"}})},outlineWidth:P("outlineWidth",[["outline",["outline-width"]]],{type:["length","number","percentage"]}),outlineOffset:P("outlineOffset",[["outline-offset",["outline-offset"]]],{type:["length","number","percentage","any"],supportsNegativeValues:!0}),outlineColor:({matchUtilities:i,theme:e})=>{i({outline:t=>({"outline-color":L(t)})},{values:re(e("outlineColor")),type:["color","any"]})},ringWidth:({matchUtilities:i,addDefaults:e,addUtilities:t,theme:r,config:n})=>{let a=(()=>{if(K(n(),"respectDefaultRingColorOpacity"))return r("ringColor.DEFAULT");let s=r("ringOpacity.DEFAULT","0.5");return r("ringColor")?.DEFAULT?Ie(r("ringColor")?.DEFAULT,s,`rgb(147 197 253 / ${s})`):`rgb(147 197 253 / ${s})`})();e("ring-width",{"--tw-ring-inset":" ","--tw-ring-offset-width":r("ringOffsetWidth.DEFAULT","0px"),"--tw-ring-offset-color":r("ringOffsetColor.DEFAULT","#fff"),"--tw-ring-color":a,"--tw-ring-offset-shadow":"0 0 #0000","--tw-ring-shadow":"0 0 #0000","--tw-shadow":"0 0 #0000","--tw-shadow-colored":"0 0 #0000"}),i({ring:s=>({"@defaults ring-width":{},"--tw-ring-offset-shadow":"var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color)","--tw-ring-shadow":`var(--tw-ring-inset) 0 0 0 calc(${s} + var(--tw-ring-offset-width)) var(--tw-ring-color)`,"box-shadow":["var(--tw-ring-offset-shadow)","var(--tw-ring-shadow)","var(--tw-shadow, 0 0 #0000)"].join(", ")})},{values:r("ringWidth"),type:"length"}),t({".ring-inset":{"@defaults ring-width":{},"--tw-ring-inset":"inset"}})},ringColor:({matchUtilities:i,theme:e,corePlugins:t})=>{i({ring:r=>t("ringOpacity")?se({color:r,property:"--tw-ring-color",variable:"--tw-ring-opacity"}):{"--tw-ring-color":L(r)}},{values:Object.fromEntries(Object.entries(re(e("ringColor"))).filter(([r])=>r!=="DEFAULT")),type:["color","any"]})},ringOpacity:i=>{let{config:e}=i;return P("ringOpacity",[["ring-opacity",["--tw-ring-opacity"]]],{filterDefault:!K(e(),"respectDefaultRingColorOpacity")})(i)},ringOffsetWidth:P("ringOffsetWidth",[["ring-offset",["--tw-ring-offset-width"]]],{type:"length"}),ringOffsetColor:({matchUtilities:i,theme:e})=>{i({"ring-offset":t=>({"--tw-ring-offset-color":L(t)})},{values:re(e("ringOffsetColor")),type:["color","any"]})},blur:({matchUtilities:i,theme:e})=>{i({blur:t=>({"--tw-blur":`blur(${t})`,"@defaults filter":{},filter:Be})},{values:e("blur")})},brightness:({matchUtilities:i,theme:e})=>{i({brightness:t=>({"--tw-brightness":`brightness(${t})`,"@defaults filter":{},filter:Be})},{values:e("brightness")})},contrast:({matchUtilities:i,theme:e})=>{i({contrast:t=>({"--tw-contrast":`contrast(${t})`,"@defaults filter":{},filter:Be})},{values:e("contrast")})},dropShadow:({matchUtilities:i,theme:e})=>{i({"drop-shadow":t=>({"--tw-drop-shadow":Array.isArray(t)?t.map(r=>`drop-shadow(${r})`).join(" "):`drop-shadow(${t})`,"@defaults filter":{},filter:Be})},{values:e("dropShadow")})},grayscale:({matchUtilities:i,theme:e})=>{i({grayscale:t=>({"--tw-grayscale":`grayscale(${t})`,"@defaults filter":{},filter:Be})},{values:e("grayscale")})},hueRotate:({matchUtilities:i,theme:e})=>{i({"hue-rotate":t=>({"--tw-hue-rotate":`hue-rotate(${t})`,"@defaults filter":{},filter:Be})},{values:e("hueRotate"),supportsNegativeValues:!0})},invert:({matchUtilities:i,theme:e})=>{i({invert:t=>({"--tw-invert":`invert(${t})`,"@defaults filter":{},filter:Be})},{values:e("invert")})},saturate:({matchUtilities:i,theme:e})=>{i({saturate:t=>({"--tw-saturate":`saturate(${t})`,"@defaults filter":{},filter:Be})},{values:e("saturate")})},sepia:({matchUtilities:i,theme:e})=>{i({sepia:t=>({"--tw-sepia":`sepia(${t})`,"@defaults filter":{},filter:Be})},{values:e("sepia")})},filter:({addDefaults:i,addUtilities:e})=>{i("filter",{"--tw-blur":" ","--tw-brightness":" ","--tw-contrast":" ","--tw-grayscale":" ","--tw-hue-rotate":" ","--tw-invert":" ","--tw-saturate":" ","--tw-sepia":" ","--tw-drop-shadow":" "}),e({".filter":{"@defaults filter":{},filter:Be},".filter-none":{filter:"none"}})},backdropBlur:({matchUtilities:i,theme:e})=>{i({"backdrop-blur":t=>({"--tw-backdrop-blur":`blur(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropBlur")})},backdropBrightness:({matchUtilities:i,theme:e})=>{i({"backdrop-brightness":t=>({"--tw-backdrop-brightness":`brightness(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropBrightness")})},backdropContrast:({matchUtilities:i,theme:e})=>{i({"backdrop-contrast":t=>({"--tw-backdrop-contrast":`contrast(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropContrast")})},backdropGrayscale:({matchUtilities:i,theme:e})=>{i({"backdrop-grayscale":t=>({"--tw-backdrop-grayscale":`grayscale(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropGrayscale")})},backdropHueRotate:({matchUtilities:i,theme:e})=>{i({"backdrop-hue-rotate":t=>({"--tw-backdrop-hue-rotate":`hue-rotate(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropHueRotate"),supportsNegativeValues:!0})},backdropInvert:({matchUtilities:i,theme:e})=>{i({"backdrop-invert":t=>({"--tw-backdrop-invert":`invert(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropInvert")})},backdropOpacity:({matchUtilities:i,theme:e})=>{i({"backdrop-opacity":t=>({"--tw-backdrop-opacity":`opacity(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropOpacity")})},backdropSaturate:({matchUtilities:i,theme:e})=>{i({"backdrop-saturate":t=>({"--tw-backdrop-saturate":`saturate(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropSaturate")})},backdropSepia:({matchUtilities:i,theme:e})=>{i({"backdrop-sepia":t=>({"--tw-backdrop-sepia":`sepia(${t})`,"@defaults backdrop-filter":{},"backdrop-filter":Fe})},{values:e("backdropSepia")})},backdropFilter:({addDefaults:i,addUtilities:e})=>{i("backdrop-filter",{"--tw-backdrop-blur":" ","--tw-backdrop-brightness":" ","--tw-backdrop-contrast":" ","--tw-backdrop-grayscale":" ","--tw-backdrop-hue-rotate":" ","--tw-backdrop-invert":" ","--tw-backdrop-opacity":" ","--tw-backdrop-saturate":" ","--tw-backdrop-sepia":" "}),e({".backdrop-filter":{"@defaults backdrop-filter":{},"backdrop-filter":Fe},".backdrop-filter-none":{"backdrop-filter":"none"}})},transitionProperty:({matchUtilities:i,theme:e})=>{let t=e("transitionTimingFunction.DEFAULT"),r=e("transitionDuration.DEFAULT");i({transition:n=>({"transition-property":n,...n==="none"?{}:{"transition-timing-function":t,"transition-duration":r}})},{values:e("transitionProperty")})},transitionDelay:P("transitionDelay",[["delay",["transitionDelay"]]]),transitionDuration:P("transitionDuration",[["duration",["transitionDuration"]]],{filterDefault:!0}),transitionTimingFunction:P("transitionTimingFunction",[["ease",["transitionTimingFunction"]]],{filterDefault:!0}),willChange:P("willChange",[["will-change",["will-change"]]]),contain:({addDefaults:i,addUtilities:e})=>{let t="var(--tw-contain-size) var(--tw-contain-layout) var(--tw-contain-paint) var(--tw-contain-style)";i("contain",{"--tw-contain-size":" ","--tw-contain-layout":" ","--tw-contain-paint":" ","--tw-contain-style":" "}),e({".contain-none":{contain:"none"},".contain-content":{contain:"content"},".contain-strict":{contain:"strict"},".contain-size":{"@defaults contain":{},"--tw-contain-size":"size",contain:t},".contain-inline-size":{"@defaults contain":{},"--tw-contain-size":"inline-size",contain:t},".contain-layout":{"@defaults contain":{},"--tw-contain-layout":"layout",contain:t},".contain-paint":{"@defaults contain":{},"--tw-contain-paint":"paint",contain:t},".contain-style":{"@defaults contain":{},"--tw-contain-style":"style",contain:t}})},content:P("content",[["content",["--tw-content",["content","var(--tw-content)"]]]]),forcedColorAdjust:({addUtilities:i})=>{i({".forced-color-adjust-auto":{"forced-color-adjust":"auto"},".forced-color-adjust-none":{"forced-color-adjust":"none"}})}}});function KS(i){if(i===void 0)return!1;if(i==="true"||i==="1")return!0;if(i==="false"||i==="0")return!1;if(i==="*")return!0;let e=i.split(",").map(t=>t.split(":")[0]);return e.includes("-tailwindcss")?!1:!!e.includes("tailwindcss")}var Pe,gd,yd,gn,Ga,He,Kr,lt=C(()=>{l();Pe=typeof h!="undefined"?{NODE_ENV:"production",DEBUG:KS(h.env.DEBUG)}:{NODE_ENV:"production",DEBUG:!1},gd=new Map,yd=new Map,gn=new Map,Ga=new Map,He=new String("*"),Kr=Symbol("__NONE__")});function Lt(i){let e=[],t=!1;for(let r=0;r<i.length;r++){let n=i[r];if(n===":"&&!t&&e.length===0)return!1;if(ZS.has(n)&&i[r-1]!=="\\"&&(t=!t),!t&&i[r-1]!=="\\"){if(wd.has(n))e.push(n);else if(bd.has(n)){let a=bd.get(n);if(e.length<=0||e.pop()!==a)return!1}}}return!(e.length>0)}var wd,bd,ZS,Ha=C(()=>{l();wd=new Map([["{","}"],["[","]"],["(",")"]]),bd=new Map(Array.from(wd.entries()).map(([i,e])=>[e,i])),ZS=new Set(['"',"'","`"])});function $t(i){let[e]=vd(i);return e.forEach(([t,r])=>t.removeChild(r)),i.nodes.push(...e.map(([,t])=>t)),i}function vd(i){let e=[],t=null;for(let r of i.nodes)if(r.type==="combinator")e=e.filter(([,n])=>Qa(n).includes("jumpable")),t=null;else if(r.type==="pseudo"){eC(r)?(t=r,e.push([i,r,null])):t&&tC(r,t)?e.push([i,r,t]):t=null;for(let n of r.nodes??[]){let[a,s]=vd(n);t=s||t,e.push(...a)}}return[e,t]}function xd(i){return i.value.startsWith("::")||Ya[i.value]!==void 0}function eC(i){return xd(i)&&Qa(i).includes("terminal")}function tC(i,e){return i.type!=="pseudo"||xd(i)?!1:Qa(e).includes("actionable")}function Qa(i){return Ya[i.value]??Ya.__default__}var Ya,yn=C(()=>{l();Ya={"::after":["terminal","jumpable"],"::backdrop":["terminal","jumpable"],"::before":["terminal","jumpable"],"::cue":["terminal"],"::cue-region":["terminal"],"::first-letter":["terminal","jumpable"],"::first-line":["terminal","jumpable"],"::grammar-error":["terminal"],"::marker":["terminal","jumpable"],"::part":["terminal","actionable"],"::placeholder":["terminal","jumpable"],"::selection":["terminal","jumpable"],"::slotted":["terminal"],"::spelling-error":["terminal"],"::target-text":["terminal"],"::file-selector-button":["terminal","actionable"],"::deep":["actionable"],"::v-deep":["actionable"],"::ng-deep":["actionable"],":after":["terminal","jumpable"],":before":["terminal","jumpable"],":first-letter":["terminal","jumpable"],":first-line":["terminal","jumpable"],":where":[],":is":[],":has":[],__default__:["terminal","actionable"]}});function jt(i,{context:e,candidate:t}){let r=e?.tailwindConfig.prefix??"",n=i.map(s=>{let o=(0,Ne.default)().astSync(s.format);return{...s,ast:s.respectPrefix?Ft(r,o):o}}),a=Ne.default.root({nodes:[Ne.default.selector({nodes:[Ne.default.className({value:ce(t)})]})]});for(let{ast:s}of n)[a,s]=iC(a,s),s.walkNesting(o=>o.replaceWith(...a.nodes[0].nodes)),a=s;return a}function Sd(i){let e=[];for(;i.prev()&&i.prev().type!=="combinator";)i=i.prev();for(;i&&i.type!=="combinator";)e.push(i),i=i.next();return e}function rC(i){return i.sort((e,t)=>e.type==="tag"&&t.type==="class"?-1:e.type==="class"&&t.type==="tag"?1:e.type==="class"&&t.type==="pseudo"&&t.value.startsWith("::")?-1:e.type==="pseudo"&&e.value.startsWith("::")&&t.type==="class"?1:i.index(e)-i.index(t)),i}function Xa(i,e){let t=!1;i.walk(r=>{if(r.type==="class"&&r.value===e)return t=!0,!1}),t||i.remove()}function wn(i,e,{context:t,candidate:r,base:n}){let a=t?.tailwindConfig?.separator??":";n=n??ae(r,a).pop();let s=(0,Ne.default)().astSync(i);if(s.walkClasses(f=>{f.raws&&f.value.includes(n)&&(f.raws.value=ce((0,kd.default)(f.raws.value)))}),s.each(f=>Xa(f,n)),s.length===0)return null;let o=Array.isArray(e)?jt(e,{context:t,candidate:r}):e;if(o===null)return s.toString();let u=Ne.default.comment({value:"/*__simple__*/"}),c=Ne.default.comment({value:"/*__simple__*/"});return s.walkClasses(f=>{if(f.value!==n)return;let d=f.parent,p=o.nodes[0].nodes;if(d.nodes.length===1){f.replaceWith(...p);return}let m=Sd(f);d.insertBefore(m[0],u),d.insertAfter(m[m.length-1],c);for(let x of p)d.insertBefore(m[0],x.clone());f.remove(),m=Sd(u);let b=d.index(u);d.nodes.splice(b,m.length,...rC(Ne.default.selector({nodes:m})).nodes),u.remove(),c.remove()}),s.walkPseudos(f=>{f.value===Ja&&f.replaceWith(f.nodes)}),s.each(f=>$t(f)),s.toString()}function iC(i,e){let t=[];return i.walkPseudos(r=>{r.value===Ja&&t.push({pseudo:r,value:r.nodes[0].toString()})}),e.walkPseudos(r=>{if(r.value!==Ja)return;let n=r.nodes[0].toString(),a=t.find(c=>c.value===n);if(!a)return;let s=[],o=r.next();for(;o&&o.type!=="combinator";)s.push(o),o=o.next();let u=o;a.pseudo.parent.insertAfter(a.pseudo,Ne.default.selector({nodes:s.map(c=>c.clone())})),r.remove(),s.forEach(c=>c.remove()),u&&u.type==="combinator"&&u.remove()}),[i,e]}var Ne,kd,Ja,Ka=C(()=>{l();Ne=X(Me()),kd=X(Yi());Nt();un();yn();Ct();Ja=":merge"});function bn(i,e){let t=(0,Za.default)().astSync(i);return t.each(r=>{r.nodes[0].type==="pseudo"&&r.nodes[0].value===":is"&&r.nodes.every(a=>a.type!=="combinator")||(r.nodes=[Za.default.pseudo({value:":is",nodes:[r.clone()]})]),$t(r)}),`${e} ${t.toString()}`}var Za,eo=C(()=>{l();Za=X(Me());yn()});function to(i){return nC.transformSync(i)}function*sC(i){let e=1/0;for(;e>=0;){let t,r=!1;if(e===1/0&&i.endsWith("]")){let s=i.indexOf("[");i[s-1]==="-"?t=s-1:i[s-1]==="/"?(t=s-1,r=!0):t=-1}else e===1/0&&i.includes("/")?(t=i.lastIndexOf("/"),r=!0):t=i.lastIndexOf("-",e);if(t<0)break;let n=i.slice(0,t),a=i.slice(r?t:t+1);e=t-1,!(n===""||a==="/")&&(yield[n,a])}}function aC(i,e){if(i.length===0||e.tailwindConfig.prefix==="")return i;for(let t of i){let[r]=t;if(r.options.respectPrefix){let n=z.root({nodes:[t[1].clone()]}),a=t[1].raws.tailwind.classCandidate;n.walkRules(s=>{let o=a.startsWith("-");s.selector=Ft(e.tailwindConfig.prefix,s.selector,o)}),t[1]=n.nodes[0]}}return i}function oC(i,e){if(i.length===0)return i;let t=[];function r(n){return n.parent&&n.parent.type==="atrule"&&n.parent.name==="keyframes"}for(let[n,a]of i){let s=z.root({nodes:[a.clone()]});s.walkRules(o=>{if(r(o))return;let u=(0,vn.default)().astSync(o.selector);u.each(c=>Xa(c,e)),ju(u,c=>c===e?`!${c}`:c),o.selector=u.toString(),o.walkDecls(c=>c.important=!0)}),t.push([{...n,important:!0},s.nodes[0]])}return t}function lC(i,e,t){if(e.length===0)return e;let r={modifier:null,value:Kr};{let[n,...a]=ae(i,"/");if(a.length>1&&(n=n+"/"+a.slice(0,-1).join("/"),a=a.slice(-1)),a.length&&!t.variantMap.has(i)&&(i=n,r.modifier=a[0],!K(t.tailwindConfig,"generalizedModifiers")))return[]}if(i.endsWith("]")&&!i.startsWith("[")){let n=/(.)(-?)\[(.*)\]/g.exec(i);if(n){let[,a,s,o]=n;if(a==="@"&&s==="-")return[];if(a!=="@"&&s==="")return[];i=i.replace(`${s}[${o}]`,""),r.value=o}}if(no(i)&&!t.variantMap.has(i)){let n=t.offsets.recordVariant(i),a=N(i.slice(1,-1)),s=ae(a,",");if(s.length>1)return[];if(!s.every(Cn))return[];let o=s.map((u,c)=>[t.offsets.applyParallelOffset(n,c),Zr(u.trim())]);t.variantMap.set(i,o)}if(t.variantMap.has(i)){let n=no(i),a=t.variantOptions.get(i)?.[ot]??{},s=t.variantMap.get(i).slice(),o=[],u=(()=>!(n||a.respectPrefix===!1))();for(let[c,f]of e){if(c.layer==="user")continue;let d=z.root({nodes:[f.clone()]});for(let[p,m,b]of s){let w=function(){x.raws.neededBackup||(x.raws.neededBackup=!0,x.walkRules(E=>E.raws.originalSelector=E.selector))},k=function(E){return w(),x.each(I=>{I.type==="rule"&&(I.selectors=I.selectors.map(q=>E({get className(){return to(q)},selector:q})))}),x},x=(b??d).clone(),y=[],S=m({get container(){return w(),x},separator:t.tailwindConfig.separator,modifySelectors:k,wrap(E){let I=x.nodes;x.removeAll(),E.append(I),x.append(E)},format(E){y.push({format:E,respectPrefix:u})},args:r});if(Array.isArray(S)){for(let[E,I]of S.entries())s.push([t.offsets.applyParallelOffset(p,E),I,x.clone()]);continue}if(typeof S=="string"&&y.push({format:S,respectPrefix:u}),S===null)continue;x.raws.neededBackup&&(delete x.raws.neededBackup,x.walkRules(E=>{let I=E.raws.originalSelector;if(!I||(delete E.raws.originalSelector,I===E.selector))return;let q=E.selector,R=(0,vn.default)(J=>{J.walkClasses(ue=>{ue.value=`${i}${t.tailwindConfig.separator}${ue.value}`})}).processSync(I);y.push({format:q.replace(R,"&"),respectPrefix:u}),E.selector=I})),x.nodes[0].raws.tailwind={...x.nodes[0].raws.tailwind,parentLayer:c.layer};let _=[{...c,sort:t.offsets.applyVariantOffset(c.sort,p,Object.assign(r,t.variantOptions.get(i))),collectedFormats:(c.collectedFormats??[]).concat(y)},x.nodes[0]];o.push(_)}}return o}return[]}function ro(i,e,t={}){return!ie(i)&&!Array.isArray(i)?[[i],t]:Array.isArray(i)?ro(i[0],e,i[1]):(e.has(i)||e.set(i,Bt(i)),[e.get(i),t])}function fC(i){return uC.test(i)}function cC(i){if(!i.includes("://"))return!1;try{let e=new URL(i);return e.scheme!==""&&e.host!==""}catch(e){return!1}}function Cd(i){let e=!0;return i.walkDecls(t=>{if(!Ad(t.prop,t.value))return e=!1,!1}),e}function Ad(i,e){if(cC(`${i}:${e}`))return!1;try{return z.parse(`a{${i}:${e}}`).toResult(),!0}catch(t){return!1}}function pC(i,e){let[,t,r]=i.match(/^\[([a-zA-Z0-9-_]+):(\S+)\]$/)??[];if(r===void 0||!fC(t)||!Lt(r))return null;let n=N(r,{property:t});return Ad(t,n)?[[{sort:e.offsets.arbitraryProperty(i),layer:"utilities",options:{respectImportant:!0}},()=>({[Va(i)]:{[t]:n}})]]:null}function*dC(i,e){e.candidateRuleMap.has(i)&&(yield[e.candidateRuleMap.get(i),"DEFAULT"]),yield*function*(o){o!==null&&(yield[o,"DEFAULT"])}(pC(i,e));let t=i,r=!1,n=e.tailwindConfig.prefix,a=n.length,s=t.startsWith(n)||t.startsWith(`-${n}`);t[a]==="-"&&s&&(r=!0,t=n+t.slice(a+1)),r&&e.candidateRuleMap.has(t)&&(yield[e.candidateRuleMap.get(t),"-DEFAULT"]);for(let[o,u]of sC(t))e.candidateRuleMap.has(o)&&(yield[e.candidateRuleMap.get(o),r?`-${u}`:u])}function hC(i,e){return i===He?[He]:ae(i,e)}function*mC(i,e){for(let t of i)t[1].raws.tailwind={...t[1].raws.tailwind,classCandidate:e,preserveSource:t[0].options?.preserveSource??!1},yield t}function*io(i,e){let t=e.tailwindConfig.separator,[r,...n]=hC(i,t).reverse(),a=!1;r.startsWith("!")&&(a=!0,r=r.slice(1));for(let s of dC(r,e)){let o=[],u=new Map,[c,f]=s,d=c.length===1;for(let[p,m]of c){let b=[];if(typeof m=="function")for(let x of[].concat(m(f,{isOnlyPlugin:d}))){let[y,w]=ro(x,e.postCssNodeCache);for(let k of y)b.push([{...p,options:{...p.options,...w}},k])}else if(f==="DEFAULT"||f==="-DEFAULT"){let x=m,[y,w]=ro(x,e.postCssNodeCache);for(let k of y)b.push([{...p,options:{...p.options,...w}},k])}if(b.length>0){let x=Array.from(fs(p.options?.types??[],f,p.options??{},e.tailwindConfig)).map(([y,w])=>w);x.length>0&&u.set(b,x),o.push(b)}}if(no(f)){if(o.length>1){let b=function(y){return y.length===1?y[0]:y.find(w=>{let k=u.get(w);return w.some(([{options:S},_])=>Cd(_)?S.types.some(({type:E,preferOnConflict:I})=>k.includes(E)&&I):!1)})},[p,m]=o.reduce((y,w)=>(w.some(([{options:S}])=>S.types.some(({type:_})=>_==="any"))?y[0].push(w):y[1].push(w),y),[[],[]]),x=b(m)??b(p);if(x)o=[x];else{let y=o.map(k=>new Set([...u.get(k)??[]]));for(let k of y)for(let S of k){let _=!1;for(let E of y)k!==E&&E.has(S)&&(E.delete(S),_=!0);_&&k.delete(S)}let w=[];for(let[k,S]of y.entries())for(let _ of S){let E=o[k].map(([,I])=>I).flat().map(I=>I.toString().split(`
`).slice(1,-1).map(q=>q.trim()).map(q=>`      ${q}`).join(`
`)).join(`

`);w.push(`  Use \`${i.replace("[",`[${_}:`)}\` for \`${E.trim()}\``);break}F.warn([`The class \`${i}\` is ambiguous and matches multiple utilities.`,...w,`If this is content and not a class, replace it with \`${i.replace("[","&lsqb;").replace("]","&rsqb;")}\` to silence this warning.`]);continue}}o=o.map(p=>p.filter(m=>Cd(m[1])))}o=o.flat(),o=Array.from(mC(o,r)),o=aC(o,e),a&&(o=oC(o,r));for(let p of n)o=lC(p,o,e);for(let p of o)p[1].raws.tailwind={...p[1].raws.tailwind,candidate:i},p=gC(p,{context:e,candidate:i}),p!==null&&(yield p)}}function gC(i,{context:e,candidate:t}){if(!i[0].collectedFormats)return i;let r=!0,n;try{n=jt(i[0].collectedFormats,{context:e,candidate:t})}catch{return null}let a=z.root({nodes:[i[1].clone()]});return a.walkRules(s=>{if(!xn(s))try{let o=wn(s.selector,n,{candidate:t,context:e});if(o===null){s.remove();return}s.selector=o}catch{return r=!1,!1}}),!r||a.nodes.length===0?null:(i[1]=a.nodes[0],i)}function xn(i){return i.parent&&i.parent.type==="atrule"&&i.parent.name==="keyframes"}function yC(i){if(i===!0)return e=>{xn(e)||e.walkDecls(t=>{t.parent.type==="rule"&&!xn(t.parent)&&(t.important=!0)})};if(typeof i=="string")return e=>{xn(e)||(e.selectors=e.selectors.map(t=>bn(t,i)))}}function kn(i,e,t=!1){let r=[],n=yC(e.tailwindConfig.important);for(let a of i){if(e.notClassCache.has(a))continue;if(e.candidateRuleCache.has(a)){r=r.concat(Array.from(e.candidateRuleCache.get(a)));continue}let s=Array.from(io(a,e));if(s.length===0){e.notClassCache.add(a);continue}e.classCache.set(a,s);let o=e.candidateRuleCache.get(a)??new Set;e.candidateRuleCache.set(a,o);for(let u of s){let[{sort:c,options:f},d]=u;if(f.respectImportant&&n){let m=z.root({nodes:[d.clone()]});m.walkRules(n),d=m.nodes[0]}let p=[c,t?d.clone():d];o.add(p),e.ruleCache.add(p),r.push(p)}}return r}function no(i){return i.startsWith("[")&&i.endsWith("]")}var vn,nC,uC,Sn=C(()=>{l();nt();vn=X(Me());za();St();un();pr();Oe();lt();Ka();Ua();cr();Xr();Ha();Ct();ze();eo();nC=(0,vn.default)(i=>i.first.filter(({type:e})=>e==="class").pop().value);uC=/^[a-z_-]/});var _d,Od=C(()=>{l();_d={}});function wC(i){try{return _d.createHash("md5").update(i,"utf-8").digest("binary")}catch(e){return""}}function Ed(i,e){let t=e.toString();if(!t.includes("@tailwind"))return!1;let r=Ga.get(i),n=wC(t),a=r!==n;return Ga.set(i,n),a}var Td=C(()=>{l();Od();lt()});function An(i){return(i>0n)-(i<0n)}var Pd=C(()=>{l()});function Dd(i,e){let t=0n,r=0n;for(let[n,a]of e)i&n&&(t=t|n,r=r|a);return i&~t|r}var Id=C(()=>{l()});function qd(i){let e=null;for(let t of i)e=e??t,e=e>t?e:t;return e}function bC(i,e){let t=i.length,r=e.length,n=t<r?t:r;for(let a=0;a<n;a++){let s=i.charCodeAt(a)-e.charCodeAt(a);if(s!==0)return s}return t-r}var so,Rd=C(()=>{l();Pd();Id();so=class{constructor(){this.offsets={defaults:0n,base:0n,components:0n,utilities:0n,variants:0n,user:0n},this.layerPositions={defaults:0n,base:1n,components:2n,utilities:3n,user:4n,variants:5n},this.reservedVariantBits=0n,this.variantOffsets=new Map}create(e){return{layer:e,parentLayer:e,arbitrary:0n,variants:0n,parallelIndex:0n,index:this.offsets[e]++,propertyOffset:0n,property:"",options:[]}}arbitraryProperty(e){return{...this.create("utilities"),arbitrary:1n,property:e}}forVariant(e,t=0){let r=this.variantOffsets.get(e);if(r===void 0)throw new Error(`Cannot find offset for unknown variant ${e}`);return{...this.create("variants"),variants:r<<BigInt(t)}}applyVariantOffset(e,t,r){return r.variant=t.variants,{...e,layer:"variants",parentLayer:e.layer==="variants"?e.parentLayer:e.layer,variants:e.variants|t.variants,options:r.sort?[].concat(r,e.options):e.options,parallelIndex:qd([e.parallelIndex,t.parallelIndex])}}applyParallelOffset(e,t){return{...e,parallelIndex:BigInt(t)}}recordVariants(e,t){for(let r of e)this.recordVariant(r,t(r))}recordVariant(e,t=1){return this.variantOffsets.set(e,1n<<this.reservedVariantBits),this.reservedVariantBits+=BigInt(t),{...this.create("variants"),variants:this.variantOffsets.get(e)}}compare(e,t){if(e.layer!==t.layer)return this.layerPositions[e.layer]-this.layerPositions[t.layer];if(e.parentLayer!==t.parentLayer)return this.layerPositions[e.parentLayer]-this.layerPositions[t.parentLayer];for(let r of e.options)for(let n of t.options){if(r.id!==n.id||!r.sort||!n.sort)continue;let a=qd([r.variant,n.variant])??0n,s=~(a|a-1n),o=e.variants&s,u=t.variants&s;if(o!==u)continue;let c=r.sort({value:r.value,modifier:r.modifier},{value:n.value,modifier:n.modifier});if(c!==0)return c}return e.variants!==t.variants?e.variants-t.variants:e.parallelIndex!==t.parallelIndex?e.parallelIndex-t.parallelIndex:e.arbitrary!==t.arbitrary?e.arbitrary-t.arbitrary:e.propertyOffset!==t.propertyOffset?e.propertyOffset-t.propertyOffset:e.index-t.index}recalculateVariantOffsets(){let e=Array.from(this.variantOffsets.entries()).filter(([n])=>n.startsWith("[")).sort(([n],[a])=>bC(n,a)),t=e.map(([,n])=>n).sort((n,a)=>An(n-a));return e.map(([,n],a)=>[n,t[a]]).filter(([n,a])=>n!==a)}remapArbitraryVariantOffsets(e){let t=this.recalculateVariantOffsets();return t.length===0?e:e.map(r=>{let[n,a]=r;return n={...n,variants:Dd(n.variants,t)},[n,a]})}sortArbitraryProperties(e){let t=new Set;for(let[s]of e)s.arbitrary===1n&&t.add(s.property);if(t.size===0)return e;let r=Array.from(t).sort(),n=new Map,a=1n;for(let s of r)n.set(s,a++);return e.map(s=>{let[o,u]=s;return o={...o,propertyOffset:n.get(o.property)??0n},[o,u]})}sort(e){return e=this.remapArbitraryVariantOffsets(e),e=this.sortArbitraryProperties(e),e.sort(([t],[r])=>An(this.compare(t,r)))}}});function uo(i,e){let t=i.tailwindConfig.prefix;return typeof t=="function"?t(e):t+e}function Bd({type:i="any",...e}){let t=[].concat(i);return{...e,types:t.map(r=>Array.isArray(r)?{type:r[0],...r[1]}:{type:r,preferOnConflict:!1})}}function vC(i){let e=[],t="",r=0;for(let n=0;n<i.length;n++){let a=i[n];if(a==="\\")t+="\\"+i[++n];else if(a==="{")++r,e.push(t.trim()),t="";else if(a==="}"){if(--r<0)throw new Error("Your { and } are unbalanced.");e.push(t.trim()),t=""}else t+=a}return t.length>0&&e.push(t.trim()),e=e.filter(n=>n!==""),e}function xC(i,e,{before:t=[]}={}){if(t=[].concat(t),t.length<=0){i.push(e);return}let r=i.length-1;for(let n of t){let a=i.indexOf(n);a!==-1&&(r=Math.min(r,a))}i.splice(r,0,e)}function Fd(i){return Array.isArray(i)?i.flatMap(e=>!Array.isArray(e)&&!ie(e)?e:Bt(e)):Fd([i])}function kC(i,e){return(0,ao.default)(r=>{let n=[];return e&&e(r),r.walkClasses(a=>{n.push(a.value)}),n}).transformSync(i)}function SC(i){i.walkPseudos(e=>{e.value===":not"&&e.remove()})}function CC(i,e={containsNonOnDemandable:!1},t=0){let r=[],n=[];i.type==="rule"?n.push(...i.selectors):i.type==="atrule"&&i.walkRules(a=>n.push(...a.selectors));for(let a of n){let s=kC(a,SC);s.length===0&&(e.containsNonOnDemandable=!0);for(let o of s)r.push(o)}return t===0?[e.containsNonOnDemandable||r.length===0,r]:r}function _n(i){return Fd(i).flatMap(e=>{let t=new Map,[r,n]=CC(e);return r&&n.unshift(He),n.map(a=>(t.has(e)||t.set(e,e),[a,t.get(e)]))})}function Cn(i){return i.startsWith("@")||i.includes("&")}function Zr(i){i=i.replace(/\n+/g,"").replace(/\s{1,}/g," ").trim();let e=vC(i).map(t=>{if(!t.startsWith("@"))return({format:a})=>a(t);let[,r,n]=/@(\S*)( .+|[({].*)?/g.exec(t);return({wrap:a})=>a(z.atRule({name:r,params:n?.trim()??""}))}).reverse();return t=>{for(let r of e)r(t)}}function AC(i,e,{variantList:t,variantMap:r,offsets:n,classList:a}){function s(p,m){return p?(0,Md.default)(i,p,m):i}function o(p){return Ft(i.prefix,p)}function u(p,m){return p===He?He:m.respectPrefix?e.tailwindConfig.prefix+p:p}function c(p,m,b={}){let x=Ke(p),y=s(["theme",...x],m);return Ge(x[0])(y,b)}let f=0,d={postcss:z,prefix:o,e:ce,config:s,theme:c,corePlugins:p=>Array.isArray(i.corePlugins)?i.corePlugins.includes(p):s(["corePlugins",p],!0),variants:()=>[],addBase(p){for(let[m,b]of _n(p)){let x=u(m,{}),y=n.create("base");e.candidateRuleMap.has(x)||e.candidateRuleMap.set(x,[]),e.candidateRuleMap.get(x).push([{sort:y,layer:"base"},b])}},addDefaults(p,m){let b={[`@defaults ${p}`]:m};for(let[x,y]of _n(b)){let w=u(x,{});e.candidateRuleMap.has(w)||e.candidateRuleMap.set(w,[]),e.candidateRuleMap.get(w).push([{sort:n.create("defaults"),layer:"defaults"},y])}},addComponents(p,m){m=Object.assign({},{preserveSource:!1,respectPrefix:!0,respectImportant:!1},Array.isArray(m)?{}:m);for(let[x,y]of _n(p)){let w=u(x,m);a.add(w),e.candidateRuleMap.has(w)||e.candidateRuleMap.set(w,[]),e.candidateRuleMap.get(w).push([{sort:n.create("components"),layer:"components",options:m},y])}},addUtilities(p,m){m=Object.assign({},{preserveSource:!1,respectPrefix:!0,respectImportant:!0},Array.isArray(m)?{}:m);for(let[x,y]of _n(p)){let w=u(x,m);a.add(w),e.candidateRuleMap.has(w)||e.candidateRuleMap.set(w,[]),e.candidateRuleMap.get(w).push([{sort:n.create("utilities"),layer:"utilities",options:m},y])}},matchUtilities:function(p,m){m=Bd({...{respectPrefix:!0,respectImportant:!0,modifiers:!1},...m});let x=n.create("utilities");for(let y in p){let S=function(E,{isOnlyPlugin:I}){let[q,R,J]=us(m.types,E,m,i);if(q===void 0)return[];if(!m.types.some(({type:ee})=>ee===R))if(I)F.warn([`Unnecessary typehint \`${R}\` in \`${y}-${E}\`.`,`You can safely update it to \`${y}-${E.replace(R+":","")}\`.`]);else return[];if(!Lt(q))return[];let ue={get modifier(){return m.modifiers||F.warn(`modifier-used-without-options-for-${y}`,["Your plugin must set `modifiers: true` in its options to support modifiers."]),J}},de=K(i,"generalizedModifiers");return[].concat(de?k(q,ue):k(q)).filter(Boolean).map(ee=>({[fn(y,E)]:ee}))},w=u(y,m),k=p[y];a.add([w,m]);let _=[{sort:x,layer:"utilities",options:m},S];e.candidateRuleMap.has(w)||e.candidateRuleMap.set(w,[]),e.candidateRuleMap.get(w).push(_)}},matchComponents:function(p,m){m=Bd({...{respectPrefix:!0,respectImportant:!1,modifiers:!1},...m});let x=n.create("components");for(let y in p){let S=function(E,{isOnlyPlugin:I}){let[q,R,J]=us(m.types,E,m,i);if(q===void 0)return[];if(!m.types.some(({type:ee})=>ee===R))if(I)F.warn([`Unnecessary typehint \`${R}\` in \`${y}-${E}\`.`,`You can safely update it to \`${y}-${E.replace(R+":","")}\`.`]);else return[];if(!Lt(q))return[];let ue={get modifier(){return m.modifiers||F.warn(`modifier-used-without-options-for-${y}`,["Your plugin must set `modifiers: true` in its options to support modifiers."]),J}},de=K(i,"generalizedModifiers");return[].concat(de?k(q,ue):k(q)).filter(Boolean).map(ee=>({[fn(y,E)]:ee}))},w=u(y,m),k=p[y];a.add([w,m]);let _=[{sort:x,layer:"components",options:m},S];e.candidateRuleMap.has(w)||e.candidateRuleMap.set(w,[]),e.candidateRuleMap.get(w).push(_)}},addVariant(p,m,b={}){m=[].concat(m).map(x=>{if(typeof x!="string")return(y={})=>{let{args:w,modifySelectors:k,container:S,separator:_,wrap:E,format:I}=y,q=x(Object.assign({modifySelectors:k,container:S,separator:_},b.type===oo.MatchVariant&&{args:w,wrap:E,format:I}));if(typeof q=="string"&&!Cn(q))throw new Error(`Your custom variant \`${p}\` has an invalid format string. Make sure it's an at-rule or contains a \`&\` placeholder.`);return Array.isArray(q)?q.filter(R=>typeof R=="string").map(R=>Zr(R)):q&&typeof q=="string"&&Zr(q)(y)};if(!Cn(x))throw new Error(`Your custom variant \`${p}\` has an invalid format string. Make sure it's an at-rule or contains a \`&\` placeholder.`);return Zr(x)}),xC(t,p,b),r.set(p,m),e.variantOptions.set(p,b)},matchVariant(p,m,b){let x=b?.id??++f,y=p==="@",w=K(i,"generalizedModifiers");for(let[S,_]of Object.entries(b?.values??{}))S!=="DEFAULT"&&d.addVariant(y?`${p}${S}`:`${p}-${S}`,({args:E,container:I})=>m(_,w?{modifier:E?.modifier,container:I}:{container:I}),{...b,value:_,id:x,type:oo.MatchVariant,variantInfo:lo.Base});let k="DEFAULT"in(b?.values??{});d.addVariant(p,({args:S,container:_})=>S?.value===Kr&&!k?null:m(S?.value===Kr?b.values.DEFAULT:S?.value??(typeof S=="string"?S:""),w?{modifier:S?.modifier,container:_}:{container:_}),{...b,id:x,type:oo.MatchVariant,variantInfo:lo.Dynamic})}};return d}function On(i){return fo.has(i)||fo.set(i,new Map),fo.get(i)}function Nd(i,e){let t=!1,r=new Map;for(let n of i){if(!n)continue;let a=gs.parse(n),s=a.hash?a.href.replace(a.hash,""):a.href;s=a.search?s.replace(a.search,""):s;let o=te.statSync(decodeURIComponent(s),{throwIfNoEntry:!1})?.mtimeMs;!o||((!e.has(n)||o>e.get(n))&&(t=!0),r.set(n,o))}return[t,r]}function Ld(i){i.walkAtRules(e=>{["responsive","variants"].includes(e.name)&&(Ld(e),e.before(e.nodes),e.remove())})}function _C(i){let e=[];return i.each(t=>{t.type==="atrule"&&["responsive","variants"].includes(t.name)&&(t.name="layer",t.params="utilities")}),i.walkAtRules("layer",t=>{if(Ld(t),t.params==="base"){for(let r of t.nodes)e.push(function({addBase:n}){n(r,{respectPrefix:!1})});t.remove()}else if(t.params==="components"){for(let r of t.nodes)e.push(function({addComponents:n}){n(r,{respectPrefix:!1,preserveSource:!0})});t.remove()}else if(t.params==="utilities"){for(let r of t.nodes)e.push(function({addUtilities:n}){n(r,{respectPrefix:!1,preserveSource:!0})});t.remove()}}),e}function OC(i,e){let t=Object.entries({...H,...hd}).map(([u,c])=>i.tailwindConfig.corePlugins.includes(u)?c:null).filter(Boolean),r=i.tailwindConfig.plugins.map(u=>(u.__isOptionsFunction&&(u=u()),typeof u=="function"?u:u.handler)),n=_C(e),a=[H.childVariant,H.pseudoElementVariants,H.pseudoClassVariants,H.hasVariants,H.ariaVariants,H.dataVariants],s=[H.supportsVariants,H.reducedMotionVariants,H.prefersContrastVariants,H.screenVariants,H.orientationVariants,H.directionVariants,H.darkVariants,H.forcedColorsVariants,H.printVariant];return(i.tailwindConfig.darkMode==="class"||Array.isArray(i.tailwindConfig.darkMode)&&i.tailwindConfig.darkMode[0]==="class")&&(s=[H.supportsVariants,H.reducedMotionVariants,H.prefersContrastVariants,H.darkVariants,H.screenVariants,H.orientationVariants,H.directionVariants,H.forcedColorsVariants,H.printVariant]),[...t,...a,...r,...s,...n]}function EC(i,e){let t=[],r=new Map;e.variantMap=r;let n=new so;e.offsets=n;let a=new Set,s=AC(e.tailwindConfig,e,{variantList:t,variantMap:r,offsets:n,classList:a});for(let f of i)if(Array.isArray(f))for(let d of f)d(s);else f?.(s);n.recordVariants(t,f=>r.get(f).length);for(let[f,d]of r.entries())e.variantMap.set(f,d.map((p,m)=>[n.forVariant(f,m),p]));let o=(e.tailwindConfig.safelist??[]).filter(Boolean);if(o.length>0){let f=[];for(let d of o){if(typeof d=="string"){e.changedContent.push({content:d,extension:"html"});continue}if(d instanceof RegExp){F.warn("root-regex",["Regular expressions in `safelist` work differently in Tailwind CSS v3.0.","Update your `safelist` configuration to eliminate this warning.","https://tailwindcss.com/docs/content-configuration#safelisting-classes"]);continue}f.push(d)}if(f.length>0){let d=new Map,p=e.tailwindConfig.prefix.length,m=f.some(b=>b.pattern.source.includes("!"));for(let b of a){let x=Array.isArray(b)?(()=>{let[y,w]=b,S=Object.keys(w?.values??{}).map(_=>Jr(y,_));return w?.supportsNegativeValues&&(S=[...S,...S.map(_=>"-"+_)],S=[...S,...S.map(_=>_.slice(0,p)+"-"+_.slice(p))]),w.types.some(({type:_})=>_==="color")&&(S=[...S,...S.flatMap(_=>Object.keys(e.tailwindConfig.theme.opacity).map(E=>`${_}/${E}`))]),m&&w?.respectImportant&&(S=[...S,...S.map(_=>"!"+_)]),S})():[b];for(let y of x)for(let{pattern:w,variants:k=[]}of f)if(w.lastIndex=0,d.has(w)||d.set(w,0),!!w.test(y)){d.set(w,d.get(w)+1),e.changedContent.push({content:y,extension:"html"});for(let S of k)e.changedContent.push({content:S+e.tailwindConfig.separator+y,extension:"html"})}}for(let[b,x]of d.entries())x===0&&F.warn([`The safelist pattern \`${b}\` doesn't match any Tailwind CSS classes.`,"Fix this pattern or remove it from your `safelist` configuration.","https://tailwindcss.com/docs/content-configuration#safelisting-classes"])}}let u=[].concat(e.tailwindConfig.darkMode??"media")[1]??"dark",c=[uo(e,u),uo(e,"group"),uo(e,"peer")];e.getClassOrder=function(d){let p=[...d].sort((y,w)=>y===w?0:y<w?-1:1),m=new Map(p.map(y=>[y,null])),b=kn(new Set(p),e,!0);b=e.offsets.sort(b);let x=BigInt(c.length);for(let[,y]of b){let w=y.raws.tailwind.candidate;m.set(w,m.get(w)??x++)}return d.map(y=>{let w=m.get(y)??null,k=c.indexOf(y);return w===null&&k!==-1&&(w=BigInt(k)),[y,w]})},e.getClassList=function(d={}){let p=[];for(let m of a)if(Array.isArray(m)){let[b,x]=m,y=[],w=Object.keys(x?.modifiers??{});x?.types?.some(({type:_})=>_==="color")&&w.push(...Object.keys(e.tailwindConfig.theme.opacity??{}));let k={modifiers:w},S=d.includeMetadata&&w.length>0;for(let[_,E]of Object.entries(x?.values??{})){if(E==null)continue;let I=Jr(b,_);if(p.push(S?[I,k]:I),x?.supportsNegativeValues&&Xe(E)){let q=Jr(b,`-${_}`);y.push(S?[q,k]:q)}}p.push(...y)}else p.push(m);return p},e.getVariants=function(){let d=Math.random().toString(36).substring(7).toUpperCase(),p=[];for(let[m,b]of e.variantOptions.entries())b.variantInfo!==lo.Base&&p.push({name:m,isArbitrary:b.type===Symbol.for("MATCH_VARIANT"),values:Object.keys(b.values??{}),hasDash:m!=="@",selectors({modifier:x,value:y}={}){let w=`TAILWINDPLACEHOLDER${d}`,k=z.rule({selector:`.${w}`}),S=z.root({nodes:[k.clone()]}),_=S.toString(),E=(e.variantMap.get(m)??[]).flatMap(([oe,he])=>he),I=[];for(let oe of E){let he=[],ai={args:{modifier:x,value:b.values?.[y]??y},separator:e.tailwindConfig.separator,modifySelectors(Ce){return S.each(Yn=>{Yn.type==="rule"&&(Yn.selectors=Yn.selectors.map(su=>Ce({get className(){return to(su)},selector:su})))}),S},format(Ce){he.push(Ce)},wrap(Ce){he.push(`@${Ce.name} ${Ce.params} { & }`)},container:S},oi=oe(ai);if(he.length>0&&I.push(he),Array.isArray(oi))for(let Ce of oi)he=[],Ce(ai),I.push(he)}let q=[],R=S.toString();_!==R&&(S.walkRules(oe=>{let he=oe.selector,ai=(0,ao.default)(oi=>{oi.walkClasses(Ce=>{Ce.value=`${m}${e.tailwindConfig.separator}${Ce.value}`})}).processSync(he);q.push(he.replace(ai,"&").replace(w,"&"))}),S.walkAtRules(oe=>{q.push(`@${oe.name} (${oe.params}) { & }`)}));let J=!(y in(b.values??{})),ue=b[ot]??{},de=(()=>!(J||ue.respectPrefix===!1))();I=I.map(oe=>oe.map(he=>({format:he,respectPrefix:de}))),q=q.map(oe=>({format:oe,respectPrefix:de}));let De={candidate:w,context:e},ee=I.map(oe=>wn(`.${w}`,jt(oe,De),De).replace(`.${w}`,"&").replace("{ & }","").trim());return q.length>0&&ee.push(jt(q,De).toString().replace(`.${w}`,"&")),ee}});return p}}function $d(i,e){!i.classCache.has(e)||(i.notClassCache.add(e),i.classCache.delete(e),i.applyClassCache.delete(e),i.candidateRuleMap.delete(e),i.candidateRuleCache.delete(e),i.stylesheetCache=null)}function TC(i,e){let t=e.raws.tailwind.candidate;if(!!t){for(let r of i.ruleCache)r[1].raws.tailwind.candidate===t&&i.ruleCache.delete(r);$d(i,t)}}function co(i,e=[],t=z.root()){let r={disposables:[],ruleCache:new Set,candidateRuleCache:new Map,classCache:new Map,applyClassCache:new Map,notClassCache:new Set(i.blocklist??[]),postCssNodeCache:new Map,candidateRuleMap:new Map,tailwindConfig:i,changedContent:e,variantMap:new Map,stylesheetCache:null,variantOptions:new Map,markInvalidUtilityCandidate:a=>$d(r,a),markInvalidUtilityNode:a=>TC(r,a)},n=OC(r,t);return EC(n,r),r}function jd(i,e,t,r,n,a){let s=e.opts.from,o=r!==null;Pe.DEBUG&&console.log("Source path:",s);let u;if(o&&zt.has(s))u=zt.get(s);else if(ei.has(n)){let p=ei.get(n);ut.get(p).add(s),zt.set(s,p),u=p}let c=Ed(s,i);if(u){let[p,m]=Nd([...a],On(u));if(!p&&!c)return[u,!1,m]}if(zt.has(s)){let p=zt.get(s);if(ut.has(p)&&(ut.get(p).delete(s),ut.get(p).size===0)){ut.delete(p);for(let[m,b]of ei)b===p&&ei.delete(m);for(let m of p.disposables.splice(0))m(p)}}Pe.DEBUG&&console.log("Setting up new context...");let f=co(t,[],i);Object.assign(f,{userConfigPath:r});let[,d]=Nd([...a],On(f));return ei.set(n,f),zt.set(s,f),ut.has(f)||ut.set(f,new Set),ut.get(f).add(s),[f,!0,d]}var Md,ao,ot,oo,lo,fo,zt,ei,ut,Xr=C(()=>{l();je();ys();nt();Md=X(Ls()),ao=X(Me());Yr();za();un();St();Nt();Ua();pr();md();lt();lt();pi();Oe();fi();Ha();Sn();Td();Rd();ze();Ka();ot=Symbol(),oo={AddVariant:Symbol.for("ADD_VARIANT"),MatchVariant:Symbol.for("MATCH_VARIANT")},lo={Base:1<<0,Dynamic:1<<1};fo=new WeakMap;zt=gd,ei=yd,ut=gn});function po(i){return i.ignore?[]:i.glob?h.env.ROLLUP_WATCH==="true"?[{type:"dependency",file:i.base}]:[{type:"dir-dependency",dir:i.base,glob:i.glob}]:[{type:"dependency",file:i.base}]}var zd=C(()=>{l()});function Vd(i,e){return{handler:i,config:e}}var Ud,Wd=C(()=>{l();Vd.withOptions=function(i,e=()=>({})){let t=function(r){return{__options:r,handler:i(r),config:e(r)}};return t.__isOptionsFunction=!0,t.__pluginFunction=i,t.__configFunction=e,t};Ud=Vd});var ho={};Ae(ho,{default:()=>PC});var PC,mo=C(()=>{l();Wd();PC=Ud});var Hd=v((c6,Gd)=>{l();var DC=(mo(),ho).default,IC={overflow:"hidden",display:"-webkit-box","-webkit-box-orient":"vertical"},qC=DC(function({matchUtilities:i,addUtilities:e,theme:t,variants:r}){let n=t("lineClamp");i({"line-clamp":a=>({...IC,"-webkit-line-clamp":`${a}`})},{values:n}),e([{".line-clamp-none":{"-webkit-line-clamp":"unset"}}],r("lineClamp"))},{theme:{lineClamp:{1:"1",2:"2",3:"3",4:"4",5:"5",6:"6"}},variants:{lineClamp:["responsive"]}});Gd.exports=qC});function go(i){i.content.files.length===0&&F.warn("content-problems",["The `content` option in your Tailwind CSS configuration is missing or empty.","Configure your content sources or your generated CSS will be missing styles.","https://tailwindcss.com/docs/content-configuration"]);try{let e=Hd();i.plugins.includes(e)&&(F.warn("line-clamp-in-core",["As of Tailwind CSS v3.3, the `@tailwindcss/line-clamp` plugin is now included by default.","Remove it from the `plugins` array in your configuration to eliminate this warning."]),i.plugins=i.plugins.filter(t=>t!==e))}catch{}return i}var Yd=C(()=>{l();Oe()});var Qd,Jd=C(()=>{l();Qd=()=>!1});var En,Xd=C(()=>{l();En={sync:i=>[].concat(i),generateTasks:i=>[{dynamic:!1,base:".",negative:[],positive:[].concat(i),patterns:[].concat(i)}],escapePath:i=>i}});var yo,Kd=C(()=>{l();yo=i=>i});var Zd,eh=C(()=>{l();Zd=()=>""});function th(i){let e=i,t=Zd(i);return t!=="."&&(e=i.substr(t.length),e.charAt(0)==="/"&&(e=e.substr(1))),e.substr(0,2)==="./"&&(e=e.substr(2)),e.charAt(0)==="/"&&(e=e.substr(1)),{base:t,glob:e}}var rh=C(()=>{l();eh()});function ih(i,e){let t=e.content.files;t=t.filter(o=>typeof o=="string"),t=t.map(yo);let r=En.generateTasks(t),n=[],a=[];for(let o of r)n.push(...o.positive.map(u=>nh(u,!1))),a.push(...o.negative.map(u=>nh(u,!0)));let s=[...n,...a];return s=MC(i,s),s=s.flatMap(BC),s=s.map(RC),s}function nh(i,e){let t={original:i,base:i,ignore:e,pattern:i,glob:null};return Qd(i)&&Object.assign(t,th(i)),t}function RC(i){let e=yo(i.base);return e=En.escapePath(e),i.pattern=i.glob?`${e}/${i.glob}`:e,i.pattern=i.ignore?`!${i.pattern}`:i.pattern,i}function MC(i,e){let t=[];return i.userConfigPath&&i.tailwindConfig.content.relative&&(t=[Z.dirname(i.userConfigPath)]),e.map(r=>(r.base=Z.resolve(...t,r.base),r))}function BC(i){let e=[i];try{let t=te.realpathSync(i.base);t!==i.base&&e.push({...i,base:t})}catch{}return e}function sh(i,e,t){let r=i.tailwindConfig.content.files.filter(s=>typeof s.raw=="string").map(({raw:s,extension:o="html"})=>({content:s,extension:o})),[n,a]=FC(e,t);for(let s of n){let o=Z.extname(s).slice(1);r.push({file:s,extension:o})}return[r,a]}function FC(i,e){let t=i.map(s=>s.pattern),r=new Map,n=new Set;Pe.DEBUG&&console.time("Finding changed files");let a=En.sync(t,{absolute:!0});for(let s of a){let o=e.get(s)||-1/0,u=te.statSync(s).mtimeMs;u>o&&(n.add(s),r.set(s,u))}return Pe.DEBUG&&console.timeEnd("Finding changed files"),[n,r]}var ah=C(()=>{l();je();yt();Jd();Xd();Kd();rh();lt()});function oh(){}var lh=C(()=>{l()});function jC(i,e){for(let t of e){let r=`${i}${t}`;if(te.existsSync(r)&&te.statSync(r).isFile())return r}for(let t of e){let r=`${i}/index${t}`;if(te.existsSync(r))return r}return null}function*uh(i,e,t,r=Z.extname(i)){let n=jC(Z.resolve(e,i),NC.includes(r)?LC:$C);if(n===null||t.has(n))return;t.add(n),yield n,e=Z.dirname(n),r=Z.extname(n);let a=te.readFileSync(n,"utf-8");for(let s of[...a.matchAll(/import[\s\S]*?['"](.{3,}?)['"]/gi),...a.matchAll(/import[\s\S]*from[\s\S]*?['"](.{3,}?)['"]/gi),...a.matchAll(/require\(['"`](.+)['"`]\)/gi)])!s[1].startsWith(".")||(yield*uh(s[1],e,t,r))}function wo(i){return i===null?new Set:new Set(uh(i,Z.dirname(i),new Set))}var NC,LC,$C,fh=C(()=>{l();je();yt();NC=[".js",".cjs",".mjs"],LC=["",".js",".cjs",".mjs",".ts",".cts",".mts",".jsx",".tsx"],$C=["",".ts",".cts",".mts",".tsx",".js",".cjs",".mjs",".jsx"]});function zC(i,e){if(bo.has(i))return bo.get(i);let t=ih(i,e);return bo.set(i,t).get(i)}function VC(i){let e=ms(i);if(e!==null){let[r,n,a,s]=ph.get(e)||[],o=wo(e),u=!1,c=new Map;for(let p of o){let m=te.statSync(p).mtimeMs;c.set(p,m),(!s||!s.has(p)||m>s.get(p))&&(u=!0)}if(!u)return[r,e,n,a];for(let p of o)delete ou.cache[p];let f=go(hr(oh(e))),d=ui(f);return ph.set(e,[f,d,o,c]),[f,e,d,o]}let t=hr(i?.config??i??{});return t=go(t),[t,null,ui(t),[]]}function vo(i){return({tailwindDirectives:e,registerDependency:t})=>(r,n)=>{let[a,s,o,u]=VC(i),c=new Set(u);if(e.size>0){c.add(n.opts.from);for(let b of n.messages)b.type==="dependency"&&c.add(b.file)}let[f,,d]=jd(r,n,a,s,o,c),p=On(f),m=zC(f,a);if(e.size>0){for(let y of m)for(let w of po(y))t(w);let[b,x]=sh(f,m,p);for(let y of b)f.changedContent.push(y);for(let[y,w]of x.entries())d.set(y,w)}for(let b of u)t({type:"dependency",file:b});for(let[b,x]of d.entries())p.set(b,x);return f}}var ch,ph,bo,dh=C(()=>{l();je();ch=X(Qn());pu();hs();tf();Xr();zd();Yd();ah();lh();fh();ph=new ch.default({maxSize:100}),bo=new WeakMap});function xo(i){let e=new Set,t=new Set,r=new Set;if(i.walkAtRules(n=>{n.name==="apply"&&r.add(n),n.name==="import"&&(n.params==='"tailwindcss/base"'||n.params==="'tailwindcss/base'"?(n.name="tailwind",n.params="base"):n.params==='"tailwindcss/components"'||n.params==="'tailwindcss/components'"?(n.name="tailwind",n.params="components"):n.params==='"tailwindcss/utilities"'||n.params==="'tailwindcss/utilities'"?(n.name="tailwind",n.params="utilities"):(n.params==='"tailwindcss/screens"'||n.params==="'tailwindcss/screens'"||n.params==='"tailwindcss/variants"'||n.params==="'tailwindcss/variants'")&&(n.name="tailwind",n.params="variants")),n.name==="tailwind"&&(n.params==="screens"&&(n.params="variants"),e.add(n.params)),["layer","responsive","variants"].includes(n.name)&&(["responsive","variants"].includes(n.name)&&F.warn(`${n.name}-at-rule-deprecated`,[`The \`@${n.name}\` directive has been deprecated in Tailwind CSS v3.0.`,"Use `@layer utilities` or `@layer components` instead.","https://tailwindcss.com/docs/upgrade-guide#replace-variants-with-layer"]),t.add(n))}),!e.has("base")||!e.has("components")||!e.has("utilities")){for(let n of t)if(n.name==="layer"&&["base","components","utilities"].includes(n.params)){if(!e.has(n.params))throw n.error(`\`@layer ${n.params}\` is used but no matching \`@tailwind ${n.params}\` directive is present.`)}else if(n.name==="responsive"){if(!e.has("utilities"))throw n.error("`@responsive` is used but `@tailwind utilities` is missing.")}else if(n.name==="variants"&&!e.has("utilities"))throw n.error("`@variants` is used but `@tailwind utilities` is missing.")}return{tailwindDirectives:e,applyDirectives:r}}var hh=C(()=>{l();Oe()});function xt(i,e=void 0,t=void 0){return i.map(r=>{let n=r.clone();return t!==void 0&&(n.raws.tailwind={...n.raws.tailwind,...t}),e!==void 0&&mh(n,a=>{if(a.raws.tailwind?.preserveSource===!0&&a.source)return!1;a.source=e}),n})}function mh(i,e){e(i)!==!1&&i.each?.(t=>mh(t,e))}var gh=C(()=>{l()});function ko(i){return i=Array.isArray(i)?i:[i],i=i.map(e=>e instanceof RegExp?e.source:e),i.join("")}function ye(i){return new RegExp(ko(i),"g")}function ft(i){return`(?:${i.map(ko).join("|")})`}function So(i){return`(?:${ko(i)})?`}function wh(i){return i&&UC.test(i)?i.replace(yh,"\\$&"):i||""}var yh,UC,bh=C(()=>{l();yh=/[\\^$.*+?()[\]{}|]/g,UC=RegExp(yh.source)});function vh(i){let e=Array.from(WC(i));return t=>{let r=[];for(let n of e)for(let a of t.match(n)??[])r.push(YC(a));return r}}function*WC(i){let e=i.tailwindConfig.separator,t=i.tailwindConfig.prefix!==""?So(ye([/-?/,wh(i.tailwindConfig.prefix)])):"",r=ft([/\[[^\s:'"`]+:[^\s\[\]]+\]/,/\[[^\s:'"`\]]+:[^\s]+?\[[^\s]+\][^\s]+?\]/,ye([ft([/-?(?:\w+)/,/@(?:\w+)/]),So(ft([ye([ft([/-(?:\w+-)*\['[^\s]+'\]/,/-(?:\w+-)*\["[^\s]+"\]/,/-(?:\w+-)*\[`[^\s]+`\]/,/-(?:\w+-)*\[(?:[^\s\[\]]+\[[^\s\[\]]+\])*[^\s:\[\]]+\]/]),/(?![{([]])/,/(?:\/[^\s'"`\\><$]*)?/]),ye([ft([/-(?:\w+-)*\['[^\s]+'\]/,/-(?:\w+-)*\["[^\s]+"\]/,/-(?:\w+-)*\[`[^\s]+`\]/,/-(?:\w+-)*\[(?:[^\s\[\]]+\[[^\s\[\]]+\])*[^\s\[\]]+\]/]),/(?![{([]])/,/(?:\/[^\s'"`\\$]*)?/]),/[-\/][^\s'"`\\$={><]*/]))])]),n=[ft([ye([/@\[[^\s"'`]+\](\/[^\s"'`]+)?/,e]),ye([/([^\s"'`\[\\]+-)?\[[^\s"'`]+\]\/[\w_-]+/,e]),ye([/([^\s"'`\[\\]+-)?\[[^\s"'`]+\]/,e]),ye([/[^\s"'`\[\\]+/,e])]),ft([ye([/([^\s"'`\[\\]+-)?\[[^\s`]+\]\/[\w_-]+/,e]),ye([/([^\s"'`\[\\]+-)?\[[^\s`]+\]/,e]),ye([/[^\s`\[\\]+/,e])])];for(let a of n)yield ye(["((?=((",a,")+))\\2)?",/!?/,t,r]);yield/[^<>"'`\s.(){}[\]#=%$]*[^<>"'`\s.(){}[\]#=%:$]/g}function YC(i){if(!i.includes("-["))return i;let e=0,t=[],r=i.matchAll(GC);r=Array.from(r).flatMap(n=>{let[,...a]=n;return a.map((s,o)=>Object.assign([],n,{index:n.index+o,0:s}))});for(let n of r){let a=n[0],s=t[t.length-1];if(a===s?t.pop():(a==="'"||a==='"'||a==="`")&&t.push(a),!s){if(a==="["){e++;continue}else if(a==="]"){e--;continue}if(e<0)return i.substring(0,n.index-1);if(e===0&&!HC.test(a))return i.substring(0,n.index)}}return i}var GC,HC,xh=C(()=>{l();bh();GC=/([\[\]'"`])([^\[\]'"`])?/g,HC=/[^"'`\s<>\]]+/});function QC(i,e){let t=i.tailwindConfig.content.extract;return t[e]||t.DEFAULT||Sh[e]||Sh.DEFAULT(i)}function JC(i,e){let t=i.content.transform;return t[e]||t.DEFAULT||Ch[e]||Ch.DEFAULT}function XC(i,e,t,r){ti.has(e)||ti.set(e,new kh.default({maxSize:25e3}));for(let n of i.split(`
`))if(n=n.trim(),!r.has(n))if(r.add(n),ti.get(e).has(n))for(let a of ti.get(e).get(n))t.add(a);else{let a=e(n).filter(o=>o!=="!*"),s=new Set(a);for(let o of s)t.add(o);ti.get(e).set(n,s)}}function KC(i,e){let t=e.offsets.sort(i),r={base:new Set,defaults:new Set,components:new Set,utilities:new Set,variants:new Set};for(let[n,a]of t)r[n.layer].add(a);return r}function Co(i){return async e=>{let t={base:null,components:null,utilities:null,variants:null};if(e.walkAtRules(y=>{y.name==="tailwind"&&Object.keys(t).includes(y.params)&&(t[y.params]=y)}),Object.values(t).every(y=>y===null))return e;let r=new Set([...i.candidates??[],He]),n=new Set;Ye.DEBUG&&console.time("Reading changed files");let a=[];for(let y of i.changedContent){let w=JC(i.tailwindConfig,y.extension),k=QC(i,y.extension);a.push([y,{transformer:w,extractor:k}])}let s=500;for(let y=0;y<a.length;y+=s){let w=a.slice(y,y+s);await Promise.all(w.map(async([{file:k,content:S},{transformer:_,extractor:E}])=>{S=k?await te.promises.readFile(k,"utf8"):S,XC(_(S),E,r,n)}))}Ye.DEBUG&&console.timeEnd("Reading changed files");let o=i.classCache.size;Ye.DEBUG&&console.time("Generate rules"),Ye.DEBUG&&console.time("Sorting candidates");let u=new Set([...r].sort((y,w)=>y===w?0:y<w?-1:1));Ye.DEBUG&&console.timeEnd("Sorting candidates"),kn(u,i),Ye.DEBUG&&console.timeEnd("Generate rules"),Ye.DEBUG&&console.time("Build stylesheet"),(i.stylesheetCache===null||i.classCache.size!==o)&&(i.stylesheetCache=KC([...i.ruleCache],i)),Ye.DEBUG&&console.timeEnd("Build stylesheet");let{defaults:c,base:f,components:d,utilities:p,variants:m}=i.stylesheetCache;t.base&&(t.base.before(xt([...f,...c],t.base.source,{layer:"base"})),t.base.remove()),t.components&&(t.components.before(xt([...d],t.components.source,{layer:"components"})),t.components.remove()),t.utilities&&(t.utilities.before(xt([...p],t.utilities.source,{layer:"utilities"})),t.utilities.remove());let b=Array.from(m).filter(y=>{let w=y.raws.tailwind?.parentLayer;return w==="components"?t.components!==null:w==="utilities"?t.utilities!==null:!0});t.variants?(t.variants.before(xt(b,t.variants.source,{layer:"variants"})),t.variants.remove()):b.length>0&&e.append(xt(b,e.source,{layer:"variants"})),e.source.end=e.source.end??e.source.start;let x=b.some(y=>y.raws.tailwind?.parentLayer==="utilities");t.utilities&&p.size===0&&!x&&F.warn("content-problems",["No utility classes were detected in your source files. If this is unexpected, double-check the `content` option in your Tailwind CSS configuration.","https://tailwindcss.com/docs/content-configuration"]),Ye.DEBUG&&(console.log("Potential classes: ",r.size),console.log("Active contexts: ",gn.size)),i.changedContent=[],e.walkAtRules("layer",y=>{Object.keys(t).includes(y.params)&&y.remove()})}}var kh,Ye,Sh,Ch,ti,Ah=C(()=>{l();je();kh=X(Qn());lt();Sn();Oe();gh();xh();Ye=Pe,Sh={DEFAULT:vh},Ch={DEFAULT:i=>i,svelte:i=>i.replace(/(?:^|\s)class:/g," ")};ti=new WeakMap});function Pn(i){let e=new Map;z.root({nodes:[i.clone()]}).walkRules(a=>{(0,Tn.default)(s=>{s.walkClasses(o=>{let u=o.parent.toString(),c=e.get(u);c||e.set(u,c=new Set),c.add(o.value)})}).processSync(a.selector)});let r=Array.from(e.values(),a=>Array.from(a)),n=r.flat();return Object.assign(n,{groups:r})}function Ao(i){return ZC.astSync(i)}function _h(i,e){let t=new Set;for(let r of i)t.add(r.split(e).pop());return Array.from(t)}function Oh(i,e){let t=i.tailwindConfig.prefix;return typeof t=="function"?t(e):t+e}function*Eh(i){for(yield i;i.parent;)yield i.parent,i=i.parent}function e2(i,e={}){let t=i.nodes;i.nodes=[];let r=i.clone(e);return i.nodes=t,r}function t2(i){for(let e of Eh(i))if(i!==e){if(e.type==="root")break;i=e2(e,{nodes:[i]})}return i}function r2(i,e){let t=new Map;return i.walkRules(r=>{for(let s of Eh(r))if(s.raws.tailwind?.layer!==void 0)return;let n=t2(r),a=e.offsets.create("user");for(let s of Pn(r)){let o=t.get(s)||[];t.set(s,o),o.push([{layer:"user",sort:a,important:!1},n])}}),t}function i2(i,e){for(let t of i){if(e.notClassCache.has(t)||e.applyClassCache.has(t))continue;if(e.classCache.has(t)){e.applyClassCache.set(t,e.classCache.get(t).map(([n,a])=>[n,a.clone()]));continue}let r=Array.from(io(t,e));if(r.length===0){e.notClassCache.add(t);continue}e.applyClassCache.set(t,r)}return e.applyClassCache}function n2(i){let e=null;return{get:t=>(e=e||i(),e.get(t)),has:t=>(e=e||i(),e.has(t))}}function s2(i){return{get:e=>i.flatMap(t=>t.get(e)||[]),has:e=>i.some(t=>t.has(e))}}function Th(i){let e=i.split(/[\s\t\n]+/g);return e[e.length-1]==="!important"?[e.slice(0,-1),!0]:[e,!1]}function Ph(i,e,t){let r=new Set,n=[];if(i.walkAtRules("apply",u=>{let[c]=Th(u.params);for(let f of c)r.add(f);n.push(u)}),n.length===0)return;let a=s2([t,i2(r,e)]);function s(u,c,f){let d=Ao(u),p=Ao(c),b=Ao(`.${ce(f)}`).nodes[0].nodes[0];return d.each(x=>{let y=new Set;p.each(w=>{let k=!1;w=w.clone(),w.walkClasses(S=>{S.value===b.value&&(k||(S.replaceWith(...x.nodes.map(_=>_.clone())),y.add(w),k=!0))})});for(let w of y){let k=[[]];for(let S of w.nodes)S.type==="combinator"?(k.push(S),k.push([])):k[k.length-1].push(S);w.nodes=[];for(let S of k)Array.isArray(S)&&S.sort((_,E)=>_.type==="tag"&&E.type==="class"?-1:_.type==="class"&&E.type==="tag"?1:_.type==="class"&&E.type==="pseudo"&&E.value.startsWith("::")?-1:_.type==="pseudo"&&_.value.startsWith("::")&&E.type==="class"?1:0),w.nodes=w.nodes.concat(S)}x.replaceWith(...y)}),d.toString()}let o=new Map;for(let u of n){let[c]=o.get(u.parent)||[[],u.source];o.set(u.parent,[c,u.source]);let[f,d]=Th(u.params);if(u.parent.type==="atrule"){if(u.parent.name==="screen"){let p=u.parent.params;throw u.error(`@apply is not supported within nested at-rules like @screen. We suggest you write this as @apply ${f.map(m=>`${p}:${m}`).join(" ")} instead.`)}throw u.error(`@apply is not supported within nested at-rules like @${u.parent.name}. You can fix this by un-nesting @${u.parent.name}.`)}for(let p of f){if([Oh(e,"group"),Oh(e,"peer")].includes(p))throw u.error(`@apply should not be used with the '${p}' utility`);if(!a.has(p))throw u.error(`The \`${p}\` class does not exist. If \`${p}\` is a custom class, make sure it is defined within a \`@layer\` directive.`);let m=a.get(p);for(let[,b]of m)b.type!=="atrule"&&b.walkRules(()=>{throw u.error([`The \`${p}\` class cannot be used with \`@apply\` because \`@apply\` does not currently support nested CSS.`,"Rewrite the selector without nesting or configure the `tailwindcss/nesting` plugin:","https://tailwindcss.com/docs/using-with-preprocessors#nesting"].join(`
`))});c.push([p,d,m])}}for(let[u,[c,f]]of o){let d=[];for(let[m,b,x]of c){let y=[m,..._h([m],e.tailwindConfig.separator)];for(let[w,k]of x){let S=Pn(u),_=Pn(k);if(_=_.groups.filter(R=>R.some(J=>y.includes(J))).flat(),_=_.concat(_h(_,e.tailwindConfig.separator)),S.some(R=>_.includes(R)))throw k.error(`You cannot \`@apply\` the \`${m}\` utility here because it creates a circular dependency.`);let I=z.root({nodes:[k.clone()]});I.walk(R=>{R.source=f}),(k.type!=="atrule"||k.type==="atrule"&&k.name!=="keyframes")&&I.walkRules(R=>{if(!Pn(R).some(ee=>ee===m)){R.remove();return}let J=typeof e.tailwindConfig.important=="string"?e.tailwindConfig.important:null,de=u.raws.tailwind!==void 0&&J&&u.selector.indexOf(J)===0?u.selector.slice(J.length):u.selector;de===""&&(de=u.selector),R.selector=s(de,R.selector,m),J&&de!==u.selector&&(R.selector=bn(R.selector,J)),R.walkDecls(ee=>{ee.important=w.important||b});let De=(0,Tn.default)().astSync(R.selector);De.each(ee=>$t(ee)),R.selector=De.toString()}),!!I.nodes[0]&&d.push([w.sort,I.nodes[0]])}}let p=e.offsets.sort(d).map(m=>m[1]);u.after(p)}for(let u of n)u.parent.nodes.length>1?u.remove():u.parent.remove();Ph(i,e,t)}function _o(i){return e=>{let t=n2(()=>r2(e,i));Ph(e,i,t)}}var Tn,ZC,Dh=C(()=>{l();nt();Tn=X(Me());Sn();Nt();eo();yn();ZC=(0,Tn.default)()});var Ih=v((lD,Dn)=>{l();(function(){"use strict";function i(r,n,a){if(!r)return null;i.caseSensitive||(r=r.toLowerCase());var s=i.threshold===null?null:i.threshold*r.length,o=i.thresholdAbsolute,u;s!==null&&o!==null?u=Math.min(s,o):s!==null?u=s:o!==null?u=o:u=null;var c,f,d,p,m,b=n.length;for(m=0;m<b;m++)if(f=n[m],a&&(f=f[a]),!!f&&(i.caseSensitive?d=f:d=f.toLowerCase(),p=t(r,d,u),(u===null||p<u)&&(u=p,a&&i.returnWinningObject?c=n[m]:c=f,i.returnFirstMatch)))return c;return c||i.nullResultValue}i.threshold=.4,i.thresholdAbsolute=20,i.caseSensitive=!1,i.nullResultValue=null,i.returnWinningObject=null,i.returnFirstMatch=!1,typeof Dn!="undefined"&&Dn.exports?Dn.exports=i:window.didYouMean=i;var e=Math.pow(2,32)-1;function t(r,n,a){a=a||a===0?a:e;var s=r.length,o=n.length;if(s===0)return Math.min(a+1,o);if(o===0)return Math.min(a+1,s);if(Math.abs(s-o)>a)return a+1;var u=[],c,f,d,p,m;for(c=0;c<=o;c++)u[c]=[c];for(f=0;f<=s;f++)u[0][f]=f;for(c=1;c<=o;c++){for(d=e,p=1,c>a&&(p=c-a),m=o+1,m>a+c&&(m=a+c),f=1;f<=s;f++)f<p||f>m?u[c][f]=a+1:n.charAt(c-1)===r.charAt(f-1)?u[c][f]=u[c-1][f-1]:u[c][f]=Math.min(u[c-1][f-1]+1,Math.min(u[c][f-1]+1,u[c-1][f]+1)),u[c][f]<d&&(d=u[c][f]);if(d>a)return a+1}return u[o][s]}})()});var Rh=v((uD,qh)=>{l();var Oo="(".charCodeAt(0),Eo=")".charCodeAt(0),In="'".charCodeAt(0),To='"'.charCodeAt(0),Po="\\".charCodeAt(0),Vt="/".charCodeAt(0),Do=",".charCodeAt(0),Io=":".charCodeAt(0),qn="*".charCodeAt(0),a2="u".charCodeAt(0),o2="U".charCodeAt(0),l2="+".charCodeAt(0),u2=/^[a-f0-9?-]+$/i;qh.exports=function(i){for(var e=[],t=i,r,n,a,s,o,u,c,f,d=0,p=t.charCodeAt(d),m=t.length,b=[{nodes:e}],x=0,y,w="",k="",S="";d<m;)if(p<=32){r=d;do r+=1,p=t.charCodeAt(r);while(p<=32);s=t.slice(d,r),a=e[e.length-1],p===Eo&&x?S=s:a&&a.type==="div"?(a.after=s,a.sourceEndIndex+=s.length):p===Do||p===Io||p===Vt&&t.charCodeAt(r+1)!==qn&&(!y||y&&y.type==="function"&&!1)?k=s:e.push({type:"space",sourceIndex:d,sourceEndIndex:r,value:s}),d=r}else if(p===In||p===To){r=d,n=p===In?"'":'"',s={type:"string",sourceIndex:d,quote:n};do if(o=!1,r=t.indexOf(n,r+1),~r)for(u=r;t.charCodeAt(u-1)===Po;)u-=1,o=!o;else t+=n,r=t.length-1,s.unclosed=!0;while(o);s.value=t.slice(d+1,r),s.sourceEndIndex=s.unclosed?r:r+1,e.push(s),d=r+1,p=t.charCodeAt(d)}else if(p===Vt&&t.charCodeAt(d+1)===qn)r=t.indexOf("*/",d),s={type:"comment",sourceIndex:d,sourceEndIndex:r+2},r===-1&&(s.unclosed=!0,r=t.length,s.sourceEndIndex=r),s.value=t.slice(d+2,r),e.push(s),d=r+2,p=t.charCodeAt(d);else if((p===Vt||p===qn)&&y&&y.type==="function")s=t[d],e.push({type:"word",sourceIndex:d-k.length,sourceEndIndex:d+s.length,value:s}),d+=1,p=t.charCodeAt(d);else if(p===Vt||p===Do||p===Io)s=t[d],e.push({type:"div",sourceIndex:d-k.length,sourceEndIndex:d+s.length,value:s,before:k,after:""}),k="",d+=1,p=t.charCodeAt(d);else if(Oo===p){r=d;do r+=1,p=t.charCodeAt(r);while(p<=32);if(f=d,s={type:"function",sourceIndex:d-w.length,value:w,before:t.slice(f+1,r)},d=r,w==="url"&&p!==In&&p!==To){r-=1;do if(o=!1,r=t.indexOf(")",r+1),~r)for(u=r;t.charCodeAt(u-1)===Po;)u-=1,o=!o;else t+=")",r=t.length-1,s.unclosed=!0;while(o);c=r;do c-=1,p=t.charCodeAt(c);while(p<=32);f<c?(d!==c+1?s.nodes=[{type:"word",sourceIndex:d,sourceEndIndex:c+1,value:t.slice(d,c+1)}]:s.nodes=[],s.unclosed&&c+1!==r?(s.after="",s.nodes.push({type:"space",sourceIndex:c+1,sourceEndIndex:r,value:t.slice(c+1,r)})):(s.after=t.slice(c+1,r),s.sourceEndIndex=r)):(s.after="",s.nodes=[]),d=r+1,s.sourceEndIndex=s.unclosed?r:d,p=t.charCodeAt(d),e.push(s)}else x+=1,s.after="",s.sourceEndIndex=d+1,e.push(s),b.push(s),e=s.nodes=[],y=s;w=""}else if(Eo===p&&x)d+=1,p=t.charCodeAt(d),y.after=S,y.sourceEndIndex+=S.length,S="",x-=1,b[b.length-1].sourceEndIndex=d,b.pop(),y=b[x],e=y.nodes;else{r=d;do p===Po&&(r+=1),r+=1,p=t.charCodeAt(r);while(r<m&&!(p<=32||p===In||p===To||p===Do||p===Io||p===Vt||p===Oo||p===qn&&y&&y.type==="function"&&!0||p===Vt&&y.type==="function"&&!0||p===Eo&&x));s=t.slice(d,r),Oo===p?w=s:(a2===s.charCodeAt(0)||o2===s.charCodeAt(0))&&l2===s.charCodeAt(1)&&u2.test(s.slice(2))?e.push({type:"unicode-range",sourceIndex:d,sourceEndIndex:r,value:s}):e.push({type:"word",sourceIndex:d,sourceEndIndex:r,value:s}),d=r}for(d=b.length-1;d;d-=1)b[d].unclosed=!0,b[d].sourceEndIndex=t.length;return b[0].nodes}});var Bh=v((fD,Mh)=>{l();Mh.exports=function i(e,t,r){var n,a,s,o;for(n=0,a=e.length;n<a;n+=1)s=e[n],r||(o=t(s,n,e)),o!==!1&&s.type==="function"&&Array.isArray(s.nodes)&&i(s.nodes,t,r),r&&t(s,n,e)}});var $h=v((cD,Lh)=>{l();function Fh(i,e){var t=i.type,r=i.value,n,a;return e&&(a=e(i))!==void 0?a:t==="word"||t==="space"?r:t==="string"?(n=i.quote||"",n+r+(i.unclosed?"":n)):t==="comment"?"/*"+r+(i.unclosed?"":"*/"):t==="div"?(i.before||"")+r+(i.after||""):Array.isArray(i.nodes)?(n=Nh(i.nodes,e),t!=="function"?n:r+"("+(i.before||"")+n+(i.after||"")+(i.unclosed?"":")")):r}function Nh(i,e){var t,r;if(Array.isArray(i)){for(t="",r=i.length-1;~r;r-=1)t=Fh(i[r],e)+t;return t}return Fh(i,e)}Lh.exports=Nh});var zh=v((pD,jh)=>{l();var Rn="-".charCodeAt(0),Mn="+".charCodeAt(0),qo=".".charCodeAt(0),f2="e".charCodeAt(0),c2="E".charCodeAt(0);function p2(i){var e=i.charCodeAt(0),t;if(e===Mn||e===Rn){if(t=i.charCodeAt(1),t>=48&&t<=57)return!0;var r=i.charCodeAt(2);return t===qo&&r>=48&&r<=57}return e===qo?(t=i.charCodeAt(1),t>=48&&t<=57):e>=48&&e<=57}jh.exports=function(i){var e=0,t=i.length,r,n,a;if(t===0||!p2(i))return!1;for(r=i.charCodeAt(e),(r===Mn||r===Rn)&&e++;e<t&&(r=i.charCodeAt(e),!(r<48||r>57));)e+=1;if(r=i.charCodeAt(e),n=i.charCodeAt(e+1),r===qo&&n>=48&&n<=57)for(e+=2;e<t&&(r=i.charCodeAt(e),!(r<48||r>57));)e+=1;if(r=i.charCodeAt(e),n=i.charCodeAt(e+1),a=i.charCodeAt(e+2),(r===f2||r===c2)&&(n>=48&&n<=57||(n===Mn||n===Rn)&&a>=48&&a<=57))for(e+=n===Mn||n===Rn?3:2;e<t&&(r=i.charCodeAt(e),!(r<48||r>57));)e+=1;return{number:i.slice(0,e),unit:i.slice(e)}}});var Gh=v((dD,Wh)=>{l();var d2=Rh(),Vh=Bh(),Uh=$h();function ct(i){return this instanceof ct?(this.nodes=d2(i),this):new ct(i)}ct.prototype.toString=function(){return Array.isArray(this.nodes)?Uh(this.nodes):""};ct.prototype.walk=function(i,e){return Vh(this.nodes,i,e),this};ct.unit=zh();ct.walk=Vh;ct.stringify=Uh;Wh.exports=ct});function Mo(i){return typeof i=="object"&&i!==null}function h2(i,e){let t=Ke(e);do if(t.pop(),(0,ri.default)(i,t)!==void 0)break;while(t.length);return t.length?t:void 0}function Ut(i){return typeof i=="string"?i:i.reduce((e,t,r)=>t.includes(".")?`${e}[${t}]`:r===0?t:`${e}.${t}`,"")}function Yh(i){return i.map(e=>`'${e}'`).join(", ")}function Qh(i){return Yh(Object.keys(i))}function Bo(i,e,t,r={}){let n=Array.isArray(e)?Ut(e):e.replace(/^['"]+|['"]+$/g,""),a=Array.isArray(e)?e:Ke(n),s=(0,ri.default)(i.theme,a,t);if(s===void 0){let u=`'${n}' does not exist in your theme config.`,c=a.slice(0,-1),f=(0,ri.default)(i.theme,c);if(Mo(f)){let d=Object.keys(f).filter(m=>Bo(i,[...c,m]).isValid),p=(0,Hh.default)(a[a.length-1],d);p?u+=` Did you mean '${Ut([...c,p])}'?`:d.length>0&&(u+=` '${Ut(c)}' has the following valid keys: ${Yh(d)}`)}else{let d=h2(i.theme,n);if(d){let p=(0,ri.default)(i.theme,d);Mo(p)?u+=` '${Ut(d)}' has the following keys: ${Qh(p)}`:u+=` '${Ut(d)}' is not an object.`}else u+=` Your theme has the following top-level keys: ${Qh(i.theme)}`}return{isValid:!1,error:u}}if(!(typeof s=="string"||typeof s=="number"||typeof s=="function"||s instanceof String||s instanceof Number||Array.isArray(s))){let u=`'${n}' was found but does not resolve to a string.`;if(Mo(s)){let c=Object.keys(s).filter(f=>Bo(i,[...a,f]).isValid);c.length&&(u+=` Did you mean something like '${Ut([...a,c[0]])}'?`)}return{isValid:!1,error:u}}let[o]=a;return{isValid:!0,value:Ge(o)(s,r)}}function m2(i,e,t){e=e.map(n=>Jh(i,n,t));let r=[""];for(let n of e)n.type==="div"&&n.value===","?r.push(""):r[r.length-1]+=Ro.default.stringify(n);return r}function Jh(i,e,t){if(e.type==="function"&&t[e.value]!==void 0){let r=m2(i,e.nodes,t);e.type="word",e.value=t[e.value](i,...r)}return e}function g2(i,e,t){return Object.keys(t).some(n=>e.includes(`${n}(`))?(0,Ro.default)(e).walk(n=>{Jh(i,n,t)}).toString():e}function*w2(i){i=i.replace(/^['"]+|['"]+$/g,"");let e=i.match(/^([^\s]+)(?![^\[]*\])(?:\s*\/\s*([^\/\s]+))$/),t;yield[i,void 0],e&&(i=e[1],t=e[2],yield[i,t])}function b2(i,e,t){let r=Array.from(w2(e)).map(([n,a])=>Object.assign(Bo(i,n,t,{opacityValue:a}),{resolvedPath:n,alpha:a}));return r.find(n=>n.isValid)??r[0]}function Xh(i){let e=i.tailwindConfig,t={theme:(r,n,...a)=>{let{isValid:s,value:o,error:u,alpha:c}=b2(e,n,a.length?a:void 0);if(!s){let p=r.parent,m=p?.raws.tailwind?.candidate;if(p&&m!==void 0){i.markInvalidUtilityNode(p),p.remove(),F.warn("invalid-theme-key-in-class",[`The utility \`${m}\` contains an invalid theme value and was not generated.`]);return}throw r.error(u)}let f=At(o),d=f!==void 0&&typeof f=="function";return(c!==void 0||d)&&(c===void 0&&(c=1),o=Ie(f,c,f)),o},screen:(r,n)=>{n=n.replace(/^['"]+/g,"").replace(/['"]+$/g,"");let s=at(e.theme.screens).find(({name:o})=>o===n);if(!s)throw r.error(`The '${n}' screen does not exist in your theme.`);return st(s)}};return r=>{r.walk(n=>{let a=y2[n.type];a!==void 0&&(n[a]=g2(n,n[a],t))})}}var ri,Hh,Ro,y2,Kh=C(()=>{l();ri=X(Ls()),Hh=X(Ih());Yr();Ro=X(Gh());hn();cn();pi();lr();pr();Oe();y2={atrule:"params",decl:"value"}});function Zh({tailwindConfig:{theme:i}}){return function(e){e.walkAtRules("screen",t=>{let r=t.params,a=at(i.screens).find(({name:s})=>s===r);if(!a)throw t.error(`No \`${r}\` screen found.`);t.name="media",t.params=st(a)})}}var em=C(()=>{l();hn();cn()});function v2(i){let e=i.filter(o=>o.type!=="pseudo"||o.nodes.length>0?!0:o.value.startsWith("::")||[":before",":after",":first-line",":first-letter"].includes(o.value)).reverse(),t=new Set(["tag","class","id","attribute"]),r=e.findIndex(o=>t.has(o.type));if(r===-1)return e.reverse().join("").trim();let n=e[r],a=tm[n.type]?tm[n.type](n):n;e=e.slice(0,r);let s=e.findIndex(o=>o.type==="combinator"&&o.value===">");return s!==-1&&(e.splice(0,s),e.unshift(Bn.default.universal())),[a,...e.reverse()].join("").trim()}function k2(i){return Fo.has(i)||Fo.set(i,x2.transformSync(i)),Fo.get(i)}function No({tailwindConfig:i}){return e=>{let t=new Map,r=new Set;if(e.walkAtRules("defaults",n=>{if(n.nodes&&n.nodes.length>0){r.add(n);return}let a=n.params;t.has(a)||t.set(a,new Set),t.get(a).add(n.parent),n.remove()}),K(i,"optimizeUniversalDefaults"))for(let n of r){let a=new Map,s=t.get(n.params)??[];for(let o of s)for(let u of k2(o.selector)){let c=u.includes(":-")||u.includes("::-")||u.includes(":has")?u:"__DEFAULT__",f=a.get(c)??new Set;a.set(c,f),f.add(u)}if(K(i,"optimizeUniversalDefaults")){if(a.size===0){n.remove();continue}for(let[,o]of a){let u=z.rule({source:n.source});u.selectors=[...o],u.append(n.nodes.map(c=>c.clone())),n.before(u)}}n.remove()}else if(r.size){let n=z.rule({selectors:["*","::before","::after"]});for(let s of r)n.append(s.nodes),n.parent||s.before(n),n.source||(n.source=s.source),s.remove();let a=n.clone({selectors:["::backdrop"]});n.after(a)}}}var Bn,tm,x2,Fo,rm=C(()=>{l();nt();Bn=X(Me());ze();tm={id(i){return Bn.default.attribute({attribute:"id",operator:"=",value:i.value,quoteMark:'"'})}};x2=(0,Bn.default)(i=>i.map(e=>{let t=e.split(r=>r.type==="combinator"&&r.value===" ").pop();return v2(t)})),Fo=new Map});function Lo(){function i(e){let t=null;e.each(r=>{if(!S2.has(r.type)){t=null;return}if(t===null){t=r;return}let n=im[r.type];r.type==="atrule"&&r.name==="font-face"?t=r:n.every(a=>(r[a]??"").replace(/\s+/g," ")===(t[a]??"").replace(/\s+/g," "))?(r.nodes&&t.append(r.nodes),r.remove()):t=r}),e.each(r=>{r.type==="atrule"&&i(r)})}return e=>{i(e)}}var im,S2,nm=C(()=>{l();im={atrule:["name","params"],rule:["selector"]},S2=new Set(Object.keys(im))});function $o(){return i=>{i.walkRules(e=>{let t=new Map,r=new Set([]),n=new Map;e.walkDecls(a=>{if(a.parent===e){if(t.has(a.prop)){if(t.get(a.prop).value===a.value){r.add(t.get(a.prop)),t.set(a.prop,a);return}n.has(a.prop)||n.set(a.prop,new Set),n.get(a.prop).add(t.get(a.prop)),n.get(a.prop).add(a)}t.set(a.prop,a)}});for(let a of r)a.remove();for(let a of n.values()){let s=new Map;for(let o of a){let u=A2(o.value);u!==null&&(s.has(u)||s.set(u,new Set),s.get(u).add(o))}for(let o of s.values()){let u=Array.from(o).slice(0,-1);for(let c of u)c.remove()}}})}}function A2(i){let e=/^-?\d*.?\d+([\w%]+)?$/g.exec(i);return e?e[1]??C2:null}var C2,sm=C(()=>{l();C2=Symbol("unitless-number")});function _2(i){if(!i.walkAtRules)return;let e=new Set;if(i.walkAtRules("apply",t=>{e.add(t.parent)}),e.size!==0)for(let t of e){let r=[],n=[];for(let a of t.nodes)a.type==="atrule"&&a.name==="apply"?(n.length>0&&(r.push(n),n=[]),r.push([a])):n.push(a);if(n.length>0&&r.push(n),r.length!==1){for(let a of[...r].reverse()){let s=t.clone({nodes:[]});s.append(a),t.after(s)}t.remove()}}}function Fn(){return i=>{_2(i)}}var am=C(()=>{l()});function Nn(i){return async function(e,t){let{tailwindDirectives:r,applyDirectives:n}=xo(e);Fn()(e,t);let a=i({tailwindDirectives:r,applyDirectives:n,registerDependency(s){t.messages.push({plugin:"tailwindcss",parent:t.opts.from,...s})},createContext(s,o){return co(s,o,e)}})(e,t);if(a.tailwindConfig.separator==="-")throw new Error("The '-' character cannot be used as a custom separator in JIT mode due to parsing ambiguity. Please use another character like '_' instead.");Su(a.tailwindConfig),await Co(a)(e,t),Fn()(e,t),_o(a)(e,t),Xh(a)(e,t),Zh(a)(e,t),No(a)(e,t),Lo(a)(e,t),$o(a)(e,t)}}var om=C(()=>{l();hh();Ah();Dh();Kh();em();rm();nm();sm();am();Xr();ze()});function lm(i,e){let t=null,r=null;return i.walkAtRules("config",n=>{if(r=n.source?.input.file??e.opts.from??null,r===null)throw n.error("The `@config` directive cannot be used without setting `from` in your PostCSS config.");if(t)throw n.error("Only one `@config` directive is allowed per file.");let a=n.params.match(/(['"])(.*?)\1/);if(!a)throw n.error("A path is required when using the `@config` directive.");let s=a[2];if(Z.isAbsolute(s))throw n.error("The `@config` directive cannot be used with an absolute path.");if(t=Z.resolve(Z.dirname(r),s),!te.existsSync(t))throw n.error(`The config file at "${s}" does not exist. Make sure the path is correct and the file exists.`);n.remove()}),t||null}var um=C(()=>{l();je();yt()});var fm=v((JD,jo)=>{l();dh();om();lt();um();jo.exports=function(e){return{postcssPlugin:"tailwindcss",plugins:[Pe.DEBUG&&function(t){return console.log(`
`),console.time("JIT TOTAL"),t},async function(t,r){e=lm(t,r)??e;let n=vo(e);if(t.type==="document"){let a=t.nodes.filter(s=>s.type==="root");for(let s of a)s.type==="root"&&await Nn(n)(s,r);return}await Nn(n)(t,r)},Pe.DEBUG&&function(t){return console.timeEnd("JIT TOTAL"),console.log(`
`),t}].filter(Boolean)}};jo.exports.postcss=!0});var pm=v((XD,cm)=>{l();cm.exports=fm()});var zo=v((KD,dm)=>{l();dm.exports=()=>["and_chr 114","and_uc 15.5","chrome 114","chrome 113","chrome 109","edge 114","firefox 114","ios_saf 16.5","ios_saf 16.4","ios_saf 16.3","ios_saf 16.1","opera 99","safari 16.5","samsung 21"]});var Ln={};Ae(Ln,{agents:()=>O2,feature:()=>E2});function E2(){return{status:"cr",title:"CSS Feature Queries",stats:{ie:{"6":"n","7":"n","8":"n","9":"n","10":"n","11":"n","5.5":"n"},edge:{"12":"y","13":"y","14":"y","15":"y","16":"y","17":"y","18":"y","79":"y","80":"y","81":"y","83":"y","84":"y","85":"y","86":"y","87":"y","88":"y","89":"y","90":"y","91":"y","92":"y","93":"y","94":"y","95":"y","96":"y","97":"y","98":"y","99":"y","100":"y","101":"y","102":"y","103":"y","104":"y","105":"y","106":"y","107":"y","108":"y","109":"y","110":"y","111":"y","112":"y","113":"y","114":"y"},firefox:{"2":"n","3":"n","4":"n","5":"n","6":"n","7":"n","8":"n","9":"n","10":"n","11":"n","12":"n","13":"n","14":"n","15":"n","16":"n","17":"n","18":"n","19":"n","20":"n","21":"n","22":"y","23":"y","24":"y","25":"y","26":"y","27":"y","28":"y","29":"y","30":"y","31":"y","32":"y","33":"y","34":"y","35":"y","36":"y","37":"y","38":"y","39":"y","40":"y","41":"y","42":"y","43":"y","44":"y","45":"y","46":"y","47":"y","48":"y","49":"y","50":"y","51":"y","52":"y","53":"y","54":"y","55":"y","56":"y","57":"y","58":"y","59":"y","60":"y","61":"y","62":"y","63":"y","64":"y","65":"y","66":"y","67":"y","68":"y","69":"y","70":"y","71":"y","72":"y","73":"y","74":"y","75":"y","76":"y","77":"y","78":"y","79":"y","80":"y","81":"y","82":"y","83":"y","84":"y","85":"y","86":"y","87":"y","88":"y","89":"y","90":"y","91":"y","92":"y","93":"y","94":"y","95":"y","96":"y","97":"y","98":"y","99":"y","100":"y","101":"y","102":"y","103":"y","104":"y","105":"y","106":"y","107":"y","108":"y","109":"y","110":"y","111":"y","112":"y","113":"y","114":"y","115":"y","116":"y","117":"y","3.5":"n","3.6":"n"},chrome:{"4":"n","5":"n","6":"n","7":"n","8":"n","9":"n","10":"n","11":"n","12":"n","13":"n","14":"n","15":"n","16":"n","17":"n","18":"n","19":"n","20":"n","21":"n","22":"n","23":"n","24":"n","25":"n","26":"n","27":"n","28":"y","29":"y","30":"y","31":"y","32":"y","33":"y","34":"y","35":"y","36":"y","37":"y","38":"y","39":"y","40":"y","41":"y","42":"y","43":"y","44":"y","45":"y","46":"y","47":"y","48":"y","49":"y","50":"y","51":"y","52":"y","53":"y","54":"y","55":"y","56":"y","57":"y","58":"y","59":"y","60":"y","61":"y","62":"y","63":"y","64":"y","65":"y","66":"y","67":"y","68":"y","69":"y","70":"y","71":"y","72":"y","73":"y","74":"y","75":"y","76":"y","77":"y","78":"y","79":"y","80":"y","81":"y","83":"y","84":"y","85":"y","86":"y","87":"y","88":"y","89":"y","90":"y","91":"y","92":"y","93":"y","94":"y","95":"y","96":"y","97":"y","98":"y","99":"y","100":"y","101":"y","102":"y","103":"y","104":"y","105":"y","106":"y","107":"y","108":"y","109":"y","110":"y","111":"y","112":"y","113":"y","114":"y","115":"y","116":"y","117":"y"},safari:{"4":"n","5":"n","6":"n","7":"n","8":"n","9":"y","10":"y","11":"y","12":"y","13":"y","14":"y","15":"y","17":"y","9.1":"y","10.1":"y","11.1":"y","12.1":"y","13.1":"y","14.1":"y","15.1":"y","15.2-15.3":"y","15.4":"y","15.5":"y","15.6":"y","16.0":"y","16.1":"y","16.2":"y","16.3":"y","16.4":"y","16.5":"y","16.6":"y",TP:"y","3.1":"n","3.2":"n","5.1":"n","6.1":"n","7.1":"n"},opera:{"9":"n","11":"n","12":"n","15":"y","16":"y","17":"y","18":"y","19":"y","20":"y","21":"y","22":"y","23":"y","24":"y","25":"y","26":"y","27":"y","28":"y","29":"y","30":"y","31":"y","32":"y","33":"y","34":"y","35":"y","36":"y","37":"y","38":"y","39":"y","40":"y","41":"y","42":"y","43":"y","44":"y","45":"y","46":"y","47":"y","48":"y","49":"y","50":"y","51":"y","52":"y","53":"y","54":"y","55":"y","56":"y","57":"y","58":"y","60":"y","62":"y","63":"y","64":"y","65":"y","66":"y","67":"y","68":"y","69":"y","70":"y","71":"y","72":"y","73":"y","74":"y","75":"y","76":"y","77":"y","78":"y","79":"y","80":"y","81":"y","82":"y","83":"y","84":"y","85":"y","86":"y","87":"y","88":"y","89":"y","90":"y","91":"y","92":"y","93":"y","94":"y","95":"y","96":"y","97":"y","98":"y","99":"y","100":"y","12.1":"y","9.5-9.6":"n","10.0-10.1":"n","10.5":"n","10.6":"n","11.1":"n","11.5":"n","11.6":"n"},ios_saf:{"8":"n","17":"y","9.0-9.2":"y","9.3":"y","10.0-10.2":"y","10.3":"y","11.0-11.2":"y","11.3-11.4":"y","12.0-12.1":"y","12.2-12.5":"y","13.0-13.1":"y","13.2":"y","13.3":"y","13.4-13.7":"y","14.0-14.4":"y","14.5-14.8":"y","15.0-15.1":"y","15.2-15.3":"y","15.4":"y","15.5":"y","15.6":"y","16.0":"y","16.1":"y","16.2":"y","16.3":"y","16.4":"y","16.5":"y","16.6":"y","3.2":"n","4.0-4.1":"n","4.2-4.3":"n","5.0-5.1":"n","6.0-6.1":"n","7.0-7.1":"n","8.1-8.4":"n"},op_mini:{all:"y"},android:{"3":"n","4":"n","114":"y","4.4":"y","4.4.3-4.4.4":"y","2.1":"n","2.2":"n","2.3":"n","4.1":"n","4.2-4.3":"n"},bb:{"7":"n","10":"n"},op_mob:{"10":"n","11":"n","12":"n","73":"y","11.1":"n","11.5":"n","12.1":"n"},and_chr:{"114":"y"},and_ff:{"115":"y"},ie_mob:{"10":"n","11":"n"},and_uc:{"15.5":"y"},samsung:{"4":"y","20":"y","21":"y","5.0-5.4":"y","6.2-6.4":"y","7.2-7.4":"y","8.2":"y","9.2":"y","10.1":"y","11.1-11.2":"y","12.0":"y","13.0":"y","14.0":"y","15.0":"y","16.0":"y","17.0":"y","18.0":"y","19.0":"y"},and_qq:{"13.1":"y"},baidu:{"13.18":"y"},kaios:{"2.5":"y","3.0-3.1":"y"}}}}var O2,$n=C(()=>{l();O2={ie:{prefix:"ms"},edge:{prefix:"webkit",prefix_exceptions:{"12":"ms","13":"ms","14":"ms","15":"ms","16":"ms","17":"ms","18":"ms"}},firefox:{prefix:"moz"},chrome:{prefix:"webkit"},safari:{prefix:"webkit"},opera:{prefix:"webkit",prefix_exceptions:{"9":"o","11":"o","12":"o","9.5-9.6":"o","10.0-10.1":"o","10.5":"o","10.6":"o","11.1":"o","11.5":"o","11.6":"o","12.1":"o"}},ios_saf:{prefix:"webkit"},op_mini:{prefix:"o"},android:{prefix:"webkit"},bb:{prefix:"webkit"},op_mob:{prefix:"o",prefix_exceptions:{"73":"webkit"}},and_chr:{prefix:"webkit"},and_ff:{prefix:"moz"},ie_mob:{prefix:"ms"},and_uc:{prefix:"webkit",prefix_exceptions:{"15.5":"webkit"}},samsung:{prefix:"webkit"},and_qq:{prefix:"webkit"},baidu:{prefix:"webkit"},kaios:{prefix:"moz"}}});var hm=v(()=>{l()});var le=v((t4,pt)=>{l();var{list:Vo}=ge();pt.exports.error=function(i){let e=new Error(i);throw e.autoprefixer=!0,e};pt.exports.uniq=function(i){return[...new Set(i)]};pt.exports.removeNote=function(i){return i.includes(" ")?i.split(" ")[0]:i};pt.exports.escapeRegexp=function(i){return i.replace(/[$()*+-.?[\\\]^{|}]/g,"\\$&")};pt.exports.regexp=function(i,e=!0){return e&&(i=this.escapeRegexp(i)),new RegExp(`(^|[\\s,(])(${i}($|[\\s(,]))`,"gi")};pt.exports.editList=function(i,e){let t=Vo.comma(i),r=e(t,[]);if(t===r)return i;let n=i.match(/,\s*/);return n=n?n[0]:", ",r.join(n)};pt.exports.splitSelector=function(i){return Vo.comma(i).map(e=>Vo.space(e).map(t=>t.split(/(?=\.|#)/g)))}});var dt=v((r4,ym)=>{l();var T2=zo(),mm=($n(),Ln).agents,P2=le(),gm=class{static prefixes(){if(this.prefixesCache)return this.prefixesCache;this.prefixesCache=[];for(let e in mm)this.prefixesCache.push(`-${mm[e].prefix}-`);return this.prefixesCache=P2.uniq(this.prefixesCache).sort((e,t)=>t.length-e.length),this.prefixesCache}static withPrefix(e){return this.prefixesRegexp||(this.prefixesRegexp=new RegExp(this.prefixes().join("|"))),this.prefixesRegexp.test(e)}constructor(e,t,r,n){this.data=e,this.options=r||{},this.browserslistOpts=n||{},this.selected=this.parse(t)}parse(e){let t={};for(let r in this.browserslistOpts)t[r]=this.browserslistOpts[r];return t.path=this.options.from,T2(e,t)}prefix(e){let[t,r]=e.split(" "),n=this.data[t],a=n.prefix_exceptions&&n.prefix_exceptions[r];return a||(a=n.prefix),`-${a}-`}isSelected(e){return this.selected.includes(e)}};ym.exports=gm});var ii=v((i4,wm)=>{l();wm.exports={prefix(i){let e=i.match(/^(-\w+-)/);return e?e[0]:""},unprefixed(i){return i.replace(/^-\w+-/,"")}}});var Wt=v((n4,vm)=>{l();var D2=dt(),bm=ii(),I2=le();function Uo(i,e){let t=new i.constructor;for(let r of Object.keys(i||{})){let n=i[r];r==="parent"&&typeof n=="object"?e&&(t[r]=e):r==="source"||r===null?t[r]=n:Array.isArray(n)?t[r]=n.map(a=>Uo(a,t)):r!=="_autoprefixerPrefix"&&r!=="_autoprefixerValues"&&r!=="proxyCache"&&(typeof n=="object"&&n!==null&&(n=Uo(n,t)),t[r]=n)}return t}var jn=class{static hack(e){return this.hacks||(this.hacks={}),e.names.map(t=>(this.hacks[t]=e,this.hacks[t]))}static load(e,t,r){let n=this.hacks&&this.hacks[e];return n?new n(e,t,r):new this(e,t,r)}static clone(e,t){let r=Uo(e);for(let n in t)r[n]=t[n];return r}constructor(e,t,r){this.prefixes=t,this.name=e,this.all=r}parentPrefix(e){let t;return typeof e._autoprefixerPrefix!="undefined"?t=e._autoprefixerPrefix:e.type==="decl"&&e.prop[0]==="-"?t=bm.prefix(e.prop):e.type==="root"?t=!1:e.type==="rule"&&e.selector.includes(":-")&&/:(-\w+-)/.test(e.selector)?t=e.selector.match(/:(-\w+-)/)[1]:e.type==="atrule"&&e.name[0]==="-"?t=bm.prefix(e.name):t=this.parentPrefix(e.parent),D2.prefixes().includes(t)||(t=!1),e._autoprefixerPrefix=t,e._autoprefixerPrefix}process(e,t){if(!this.check(e))return;let r=this.parentPrefix(e),n=this.prefixes.filter(s=>!r||r===I2.removeNote(s)),a=[];for(let s of n)this.add(e,s,a.concat([s]),t)&&a.push(s);return a}clone(e,t){return jn.clone(e,t)}};vm.exports=jn});var M=v((s4,Sm)=>{l();var q2=Wt(),R2=dt(),xm=le(),km=class extends q2{check(){return!0}prefixed(e,t){return t+e}normalize(e){return e}otherPrefixes(e,t){for(let r of R2.prefixes())if(r!==t&&e.includes(r))return!0;return!1}set(e,t){return e.prop=this.prefixed(e.prop,t),e}needCascade(e){return e._autoprefixerCascade||(e._autoprefixerCascade=this.all.options.cascade!==!1&&e.raw("before").includes(`
`)),e._autoprefixerCascade}maxPrefixed(e,t){if(t._autoprefixerMax)return t._autoprefixerMax;let r=0;for(let n of e)n=xm.removeNote(n),n.length>r&&(r=n.length);return t._autoprefixerMax=r,t._autoprefixerMax}calcBefore(e,t,r=""){let a=this.maxPrefixed(e,t)-xm.removeNote(r).length,s=t.raw("before");return a>0&&(s+=Array(a).fill(" ").join("")),s}restoreBefore(e){let t=e.raw("before").split(`
`),r=t[t.length-1];this.all.group(e).up(n=>{let a=n.raw("before").split(`
`),s=a[a.length-1];s.length<r.length&&(r=s)}),t[t.length-1]=r,e.raws.before=t.join(`
`)}insert(e,t,r){let n=this.set(this.clone(e),t);if(!(!n||e.parent.some(s=>s.prop===n.prop&&s.value===n.value)))return this.needCascade(e)&&(n.raws.before=this.calcBefore(r,e,t)),e.parent.insertBefore(e,n)}isAlready(e,t){let r=this.all.group(e).up(n=>n.prop===t);return r||(r=this.all.group(e).down(n=>n.prop===t)),r}add(e,t,r,n){let a=this.prefixed(e.prop,t);if(!(this.isAlready(e,a)||this.otherPrefixes(e.value,t)))return this.insert(e,t,r,n)}process(e,t){if(!this.needCascade(e)){super.process(e,t);return}let r=super.process(e,t);!r||!r.length||(this.restoreBefore(e),e.raws.before=this.calcBefore(r,e))}old(e,t){return[this.prefixed(e,t)]}};Sm.exports=km});var Am=v((a4,Cm)=>{l();Cm.exports=function i(e){return{mul:t=>new i(e*t),div:t=>new i(e/t),simplify:()=>new i(e),toString:()=>e.toString()}}});var Em=v((o4,Om)=>{l();var M2=Am(),B2=Wt(),Wo=le(),F2=/(min|max)-resolution\s*:\s*\d*\.?\d+(dppx|dpcm|dpi|x)/gi,N2=/(min|max)-resolution(\s*:\s*)(\d*\.?\d+)(dppx|dpcm|dpi|x)/i,_m=class extends B2{prefixName(e,t){return e==="-moz-"?t+"--moz-device-pixel-ratio":e+t+"-device-pixel-ratio"}prefixQuery(e,t,r,n,a){return n=new M2(n),a==="dpi"?n=n.div(96):a==="dpcm"&&(n=n.mul(2.54).div(96)),n=n.simplify(),e==="-o-"&&(n=n.n+"/"+n.d),this.prefixName(e,t)+r+n}clean(e){if(!this.bad){this.bad=[];for(let t of this.prefixes)this.bad.push(this.prefixName(t,"min")),this.bad.push(this.prefixName(t,"max"))}e.params=Wo.editList(e.params,t=>t.filter(r=>this.bad.every(n=>!r.includes(n))))}process(e){let t=this.parentPrefix(e),r=t?[t]:this.prefixes;e.params=Wo.editList(e.params,(n,a)=>{for(let s of n){if(!s.includes("min-resolution")&&!s.includes("max-resolution")){a.push(s);continue}for(let o of r){let u=s.replace(F2,c=>{let f=c.match(N2);return this.prefixQuery(o,f[1],f[2],f[3],f[4])});a.push(u)}a.push(s)}return Wo.uniq(a)})}};Om.exports=_m});var Pm=v((l4,Tm)=>{l();var Go="(".charCodeAt(0),Ho=")".charCodeAt(0),zn="'".charCodeAt(0),Yo='"'.charCodeAt(0),Qo="\\".charCodeAt(0),Gt="/".charCodeAt(0),Jo=",".charCodeAt(0),Xo=":".charCodeAt(0),Vn="*".charCodeAt(0),L2="u".charCodeAt(0),$2="U".charCodeAt(0),j2="+".charCodeAt(0),z2=/^[a-f0-9?-]+$/i;Tm.exports=function(i){for(var e=[],t=i,r,n,a,s,o,u,c,f,d=0,p=t.charCodeAt(d),m=t.length,b=[{nodes:e}],x=0,y,w="",k="",S="";d<m;)if(p<=32){r=d;do r+=1,p=t.charCodeAt(r);while(p<=32);s=t.slice(d,r),a=e[e.length-1],p===Ho&&x?S=s:a&&a.type==="div"?(a.after=s,a.sourceEndIndex+=s.length):p===Jo||p===Xo||p===Gt&&t.charCodeAt(r+1)!==Vn&&(!y||y&&y.type==="function"&&y.value!=="calc")?k=s:e.push({type:"space",sourceIndex:d,sourceEndIndex:r,value:s}),d=r}else if(p===zn||p===Yo){r=d,n=p===zn?"'":'"',s={type:"string",sourceIndex:d,quote:n};do if(o=!1,r=t.indexOf(n,r+1),~r)for(u=r;t.charCodeAt(u-1)===Qo;)u-=1,o=!o;else t+=n,r=t.length-1,s.unclosed=!0;while(o);s.value=t.slice(d+1,r),s.sourceEndIndex=s.unclosed?r:r+1,e.push(s),d=r+1,p=t.charCodeAt(d)}else if(p===Gt&&t.charCodeAt(d+1)===Vn)r=t.indexOf("*/",d),s={type:"comment",sourceIndex:d,sourceEndIndex:r+2},r===-1&&(s.unclosed=!0,r=t.length,s.sourceEndIndex=r),s.value=t.slice(d+2,r),e.push(s),d=r+2,p=t.charCodeAt(d);else if((p===Gt||p===Vn)&&y&&y.type==="function"&&y.value==="calc")s=t[d],e.push({type:"word",sourceIndex:d-k.length,sourceEndIndex:d+s.length,value:s}),d+=1,p=t.charCodeAt(d);else if(p===Gt||p===Jo||p===Xo)s=t[d],e.push({type:"div",sourceIndex:d-k.length,sourceEndIndex:d+s.length,value:s,before:k,after:""}),k="",d+=1,p=t.charCodeAt(d);else if(Go===p){r=d;do r+=1,p=t.charCodeAt(r);while(p<=32);if(f=d,s={type:"function",sourceIndex:d-w.length,value:w,before:t.slice(f+1,r)},d=r,w==="url"&&p!==zn&&p!==Yo){r-=1;do if(o=!1,r=t.indexOf(")",r+1),~r)for(u=r;t.charCodeAt(u-1)===Qo;)u-=1,o=!o;else t+=")",r=t.length-1,s.unclosed=!0;while(o);c=r;do c-=1,p=t.charCodeAt(c);while(p<=32);f<c?(d!==c+1?s.nodes=[{type:"word",sourceIndex:d,sourceEndIndex:c+1,value:t.slice(d,c+1)}]:s.nodes=[],s.unclosed&&c+1!==r?(s.after="",s.nodes.push({type:"space",sourceIndex:c+1,sourceEndIndex:r,value:t.slice(c+1,r)})):(s.after=t.slice(c+1,r),s.sourceEndIndex=r)):(s.after="",s.nodes=[]),d=r+1,s.sourceEndIndex=s.unclosed?r:d,p=t.charCodeAt(d),e.push(s)}else x+=1,s.after="",s.sourceEndIndex=d+1,e.push(s),b.push(s),e=s.nodes=[],y=s;w=""}else if(Ho===p&&x)d+=1,p=t.charCodeAt(d),y.after=S,y.sourceEndIndex+=S.length,S="",x-=1,b[b.length-1].sourceEndIndex=d,b.pop(),y=b[x],e=y.nodes;else{r=d;do p===Qo&&(r+=1),r+=1,p=t.charCodeAt(r);while(r<m&&!(p<=32||p===zn||p===Yo||p===Jo||p===Xo||p===Gt||p===Go||p===Vn&&y&&y.type==="function"&&y.value==="calc"||p===Gt&&y.type==="function"&&y.value==="calc"||p===Ho&&x));s=t.slice(d,r),Go===p?w=s:(L2===s.charCodeAt(0)||$2===s.charCodeAt(0))&&j2===s.charCodeAt(1)&&z2.test(s.slice(2))?e.push({type:"unicode-range",sourceIndex:d,sourceEndIndex:r,value:s}):e.push({type:"word",sourceIndex:d,sourceEndIndex:r,value:s}),d=r}for(d=b.length-1;d;d-=1)b[d].unclosed=!0,b[d].sourceEndIndex=t.length;return b[0].nodes}});var Im=v((u4,Dm)=>{l();Dm.exports=function i(e,t,r){var n,a,s,o;for(n=0,a=e.length;n<a;n+=1)s=e[n],r||(o=t(s,n,e)),o!==!1&&s.type==="function"&&Array.isArray(s.nodes)&&i(s.nodes,t,r),r&&t(s,n,e)}});var Bm=v((f4,Mm)=>{l();function qm(i,e){var t=i.type,r=i.value,n,a;return e&&(a=e(i))!==void 0?a:t==="word"||t==="space"?r:t==="string"?(n=i.quote||"",n+r+(i.unclosed?"":n)):t==="comment"?"/*"+r+(i.unclosed?"":"*/"):t==="div"?(i.before||"")+r+(i.after||""):Array.isArray(i.nodes)?(n=Rm(i.nodes,e),t!=="function"?n:r+"("+(i.before||"")+n+(i.after||"")+(i.unclosed?"":")")):r}function Rm(i,e){var t,r;if(Array.isArray(i)){for(t="",r=i.length-1;~r;r-=1)t=qm(i[r],e)+t;return t}return qm(i,e)}Mm.exports=Rm});var Nm=v((c4,Fm)=>{l();var Un="-".charCodeAt(0),Wn="+".charCodeAt(0),Ko=".".charCodeAt(0),V2="e".charCodeAt(0),U2="E".charCodeAt(0);function W2(i){var e=i.charCodeAt(0),t;if(e===Wn||e===Un){if(t=i.charCodeAt(1),t>=48&&t<=57)return!0;var r=i.charCodeAt(2);return t===Ko&&r>=48&&r<=57}return e===Ko?(t=i.charCodeAt(1),t>=48&&t<=57):e>=48&&e<=57}Fm.exports=function(i){var e=0,t=i.length,r,n,a;if(t===0||!W2(i))return!1;for(r=i.charCodeAt(e),(r===Wn||r===Un)&&e++;e<t&&(r=i.charCodeAt(e),!(r<48||r>57));)e+=1;if(r=i.charCodeAt(e),n=i.charCodeAt(e+1),r===Ko&&n>=48&&n<=57)for(e+=2;e<t&&(r=i.charCodeAt(e),!(r<48||r>57));)e+=1;if(r=i.charCodeAt(e),n=i.charCodeAt(e+1),a=i.charCodeAt(e+2),(r===V2||r===U2)&&(n>=48&&n<=57||(n===Wn||n===Un)&&a>=48&&a<=57))for(e+=n===Wn||n===Un?3:2;e<t&&(r=i.charCodeAt(e),!(r<48||r>57));)e+=1;return{number:i.slice(0,e),unit:i.slice(e)}}});var Gn=v((p4,jm)=>{l();var G2=Pm(),Lm=Im(),$m=Bm();function ht(i){return this instanceof ht?(this.nodes=G2(i),this):new ht(i)}ht.prototype.toString=function(){return Array.isArray(this.nodes)?$m(this.nodes):""};ht.prototype.walk=function(i,e){return Lm(this.nodes,i,e),this};ht.unit=Nm();ht.walk=Lm;ht.stringify=$m;jm.exports=ht});var Gm=v((d4,Wm)=>{l();var{list:H2}=ge(),zm=Gn(),Y2=dt(),Vm=ii(),Um=class{constructor(e){this.props=["transition","transition-property"],this.prefixes=e}add(e,t){let r,n,a=this.prefixes.add[e.prop],s=this.ruleVendorPrefixes(e),o=s||a&&a.prefixes||[],u=this.parse(e.value),c=u.map(m=>this.findProp(m)),f=[];if(c.some(m=>m[0]==="-"))return;for(let m of u){if(n=this.findProp(m),n[0]==="-")continue;let b=this.prefixes.add[n];if(!(!b||!b.prefixes))for(r of b.prefixes){if(s&&!s.some(y=>r.includes(y)))continue;let x=this.prefixes.prefixed(n,r);x!=="-ms-transform"&&!c.includes(x)&&(this.disabled(n,r)||f.push(this.clone(n,x,m)))}}u=u.concat(f);let d=this.stringify(u),p=this.stringify(this.cleanFromUnprefixed(u,"-webkit-"));if(o.includes("-webkit-")&&this.cloneBefore(e,`-webkit-${e.prop}`,p),this.cloneBefore(e,e.prop,p),o.includes("-o-")){let m=this.stringify(this.cleanFromUnprefixed(u,"-o-"));this.cloneBefore(e,`-o-${e.prop}`,m)}for(r of o)if(r!=="-webkit-"&&r!=="-o-"){let m=this.stringify(this.cleanOtherPrefixes(u,r));this.cloneBefore(e,r+e.prop,m)}d!==e.value&&!this.already(e,e.prop,d)&&(this.checkForWarning(t,e),e.cloneBefore(),e.value=d)}findProp(e){let t=e[0].value;if(/^\d/.test(t)){for(let[r,n]of e.entries())if(r!==0&&n.type==="word")return n.value}return t}already(e,t,r){return e.parent.some(n=>n.prop===t&&n.value===r)}cloneBefore(e,t,r){this.already(e,t,r)||e.cloneBefore({prop:t,value:r})}checkForWarning(e,t){if(t.prop!=="transition-property")return;let r=!1,n=!1;t.parent.each(a=>{if(a.type!=="decl"||a.prop.indexOf("transition-")!==0)return;let s=H2.comma(a.value);if(a.prop==="transition-property"){s.forEach(o=>{let u=this.prefixes.add[o];u&&u.prefixes&&u.prefixes.length>0&&(r=!0)});return}return n=n||s.length>1,!1}),r&&n&&t.warn(e,"Replace transition-property to transition, because Autoprefixer could not support any cases of transition-property and other transition-*")}remove(e){let t=this.parse(e.value);t=t.filter(s=>{let o=this.prefixes.remove[this.findProp(s)];return!o||!o.remove});let r=this.stringify(t);if(e.value===r)return;if(t.length===0){e.remove();return}let n=e.parent.some(s=>s.prop===e.prop&&s.value===r),a=e.parent.some(s=>s!==e&&s.prop===e.prop&&s.value.length>r.length);if(n||a){e.remove();return}e.value=r}parse(e){let t=zm(e),r=[],n=[];for(let a of t.nodes)n.push(a),a.type==="div"&&a.value===","&&(r.push(n),n=[]);return r.push(n),r.filter(a=>a.length>0)}stringify(e){if(e.length===0)return"";let t=[];for(let r of e)r[r.length-1].type!=="div"&&r.push(this.div(e)),t=t.concat(r);return t[0].type==="div"&&(t=t.slice(1)),t[t.length-1].type==="div"&&(t=t.slice(0,-2+1||void 0)),zm.stringify({nodes:t})}clone(e,t,r){let n=[],a=!1;for(let s of r)!a&&s.type==="word"&&s.value===e?(n.push({type:"word",value:t}),a=!0):n.push(s);return n}div(e){for(let t of e)for(let r of t)if(r.type==="div"&&r.value===",")return r;return{type:"div",value:",",after:" "}}cleanOtherPrefixes(e,t){return e.filter(r=>{let n=Vm.prefix(this.findProp(r));return n===""||n===t})}cleanFromUnprefixed(e,t){let r=e.map(a=>this.findProp(a)).filter(a=>a.slice(0,t.length)===t).map(a=>this.prefixes.unprefixed(a)),n=[];for(let a of e){let s=this.findProp(a),o=Vm.prefix(s);!r.includes(s)&&(o===t||o==="")&&n.push(a)}return n}disabled(e,t){let r=["order","justify-content","align-self","align-content"];if(e.includes("flex")||r.includes(e)){if(this.prefixes.options.flexbox===!1)return!0;if(this.prefixes.options.flexbox==="no-2009")return t.includes("2009")}}ruleVendorPrefixes(e){let{parent:t}=e;if(t.type!=="rule")return!1;if(!t.selector.includes(":-"))return!1;let r=Y2.prefixes().filter(n=>t.selector.includes(":"+n));return r.length>0?r:!1}};Wm.exports=Um});var Ht=v((h4,Ym)=>{l();var Q2=le(),Hm=class{constructor(e,t,r,n){this.unprefixed=e,this.prefixed=t,this.string=r||t,this.regexp=n||Q2.regexp(t)}check(e){return e.includes(this.string)?!!e.match(this.regexp):!1}};Ym.exports=Hm});var ke=v((m4,Jm)=>{l();var J2=Wt(),X2=Ht(),K2=ii(),Z2=le(),Qm=class extends J2{static save(e,t){let r=t.prop,n=[];for(let a in t._autoprefixerValues){let s=t._autoprefixerValues[a];if(s===t.value)continue;let o,u=K2.prefix(r);if(u==="-pie-")continue;if(u===a){o=t.value=s,n.push(o);continue}let c=e.prefixed(r,a),f=t.parent;if(!f.every(b=>b.prop!==c)){n.push(o);continue}let d=s.replace(/\s+/," ");if(f.some(b=>b.prop===t.prop&&b.value.replace(/\s+/," ")===d)){n.push(o);continue}let m=this.clone(t,{value:s});o=t.parent.insertBefore(t,m),n.push(o)}return n}check(e){let t=e.value;return t.includes(this.name)?!!t.match(this.regexp()):!1}regexp(){return this.regexpCache||(this.regexpCache=Z2.regexp(this.name))}replace(e,t){return e.replace(this.regexp(),`$1${t}$2`)}value(e){return e.raws.value&&e.raws.value.value===e.value?e.raws.value.raw:e.value}add(e,t){e._autoprefixerValues||(e._autoprefixerValues={});let r=e._autoprefixerValues[t]||this.value(e),n;do if(n=r,r=this.replace(r,t),r===!1)return;while(r!==n);e._autoprefixerValues[t]=r}old(e){return new X2(this.name,e+this.name)}};Jm.exports=Qm});var mt=v((g4,Xm)=>{l();Xm.exports={}});var el=v((y4,eg)=>{l();var Km=Gn(),eA=ke(),tA=mt().insertAreas,rA=/(^|[^-])linear-gradient\(\s*(top|left|right|bottom)/i,iA=/(^|[^-])radial-gradient\(\s*\d+(\w*|%)\s+\d+(\w*|%)\s*,/i,nA=/(!\s*)?autoprefixer:\s*ignore\s+next/i,sA=/(!\s*)?autoprefixer\s*grid:\s*(on|off|(no-)?autoplace)/i,aA=["width","height","min-width","max-width","min-height","max-height","inline-size","min-inline-size","max-inline-size","block-size","min-block-size","max-block-size"];function Zo(i){return i.parent.some(e=>e.prop==="grid-template"||e.prop==="grid-template-areas")}function oA(i){let e=i.parent.some(r=>r.prop==="grid-template-rows"),t=i.parent.some(r=>r.prop==="grid-template-columns");return e&&t}var Zm=class{constructor(e){this.prefixes=e}add(e,t){let r=this.prefixes.add["@resolution"],n=this.prefixes.add["@keyframes"],a=this.prefixes.add["@viewport"],s=this.prefixes.add["@supports"];e.walkAtRules(f=>{if(f.name==="keyframes"){if(!this.disabled(f,t))return n&&n.process(f)}else if(f.name==="viewport"){if(!this.disabled(f,t))return a&&a.process(f)}else if(f.name==="supports"){if(this.prefixes.options.supports!==!1&&!this.disabled(f,t))return s.process(f)}else if(f.name==="media"&&f.params.includes("-resolution")&&!this.disabled(f,t))return r&&r.process(f)}),e.walkRules(f=>{if(!this.disabled(f,t))return this.prefixes.add.selectors.map(d=>d.process(f,t))});function o(f){return f.parent.nodes.some(d=>{if(d.type!=="decl")return!1;let p=d.prop==="display"&&/(inline-)?grid/.test(d.value),m=d.prop.startsWith("grid-template"),b=/^grid-([A-z]+-)?gap/.test(d.prop);return p||m||b})}function u(f){return f.parent.some(d=>d.prop==="display"&&/(inline-)?flex/.test(d.value))}let c=this.gridStatus(e,t)&&this.prefixes.add["grid-area"]&&this.prefixes.add["grid-area"].prefixes;return e.walkDecls(f=>{if(this.disabledDecl(f,t))return;let d=f.parent,p=f.prop,m=f.value;if(p==="grid-row-span"){t.warn("grid-row-span is not part of final Grid Layout. Use grid-row.",{node:f});return}else if(p==="grid-column-span"){t.warn("grid-column-span is not part of final Grid Layout. Use grid-column.",{node:f});return}else if(p==="display"&&m==="box"){t.warn("You should write display: flex by final spec instead of display: box",{node:f});return}else if(p==="text-emphasis-position")(m==="under"||m==="over")&&t.warn("You should use 2 values for text-emphasis-position For example, `under left` instead of just `under`.",{node:f});else if(/^(align|justify|place)-(items|content)$/.test(p)&&u(f))(m==="start"||m==="end")&&t.warn(`${m} value has mixed support, consider using flex-${m} instead`,{node:f});else if(p==="text-decoration-skip"&&m==="ink")t.warn("Replace text-decoration-skip: ink to text-decoration-skip-ink: auto, because spec had been changed",{node:f});else{if(c&&this.gridStatus(f,t))if(f.value==="subgrid"&&t.warn("IE does not support subgrid",{node:f}),/^(align|justify|place)-items$/.test(p)&&o(f)){let x=p.replace("-items","-self");t.warn(`IE does not support ${p} on grid containers. Try using ${x} on child elements instead: ${f.parent.selector} > * { ${x}: ${f.value} }`,{node:f})}else if(/^(align|justify|place)-content$/.test(p)&&o(f))t.warn(`IE does not support ${f.prop} on grid containers`,{node:f});else if(p==="display"&&f.value==="contents"){t.warn("Please do not use display: contents; if you have grid setting enabled",{node:f});return}else if(f.prop==="grid-gap"){let x=this.gridStatus(f,t);x==="autoplace"&&!oA(f)&&!Zo(f)?t.warn("grid-gap only works if grid-template(-areas) is being used or both rows and columns have been declared and cells have not been manually placed inside the explicit grid",{node:f}):(x===!0||x==="no-autoplace")&&!Zo(f)&&t.warn("grid-gap only works if grid-template(-areas) is being used",{node:f})}else if(p==="grid-auto-columns"){t.warn("grid-auto-columns is not supported by IE",{node:f});return}else if(p==="grid-auto-rows"){t.warn("grid-auto-rows is not supported by IE",{node:f});return}else if(p==="grid-auto-flow"){let x=d.some(w=>w.prop==="grid-template-rows"),y=d.some(w=>w.prop==="grid-template-columns");Zo(f)?t.warn("grid-auto-flow is not supported by IE",{node:f}):m.includes("dense")?t.warn("grid-auto-flow: dense is not supported by IE",{node:f}):!x&&!y&&t.warn("grid-auto-flow works only if grid-template-rows and grid-template-columns are present in the same rule",{node:f});return}else if(m.includes("auto-fit")){t.warn("auto-fit value is not supported by IE",{node:f,word:"auto-fit"});return}else if(m.includes("auto-fill")){t.warn("auto-fill value is not supported by IE",{node:f,word:"auto-fill"});return}else p.startsWith("grid-template")&&m.includes("[")&&t.warn("Autoprefixer currently does not support line names. Try using grid-template-areas instead.",{node:f,word:"["});if(m.includes("radial-gradient"))if(iA.test(f.value))t.warn("Gradient has outdated direction syntax. New syntax is like `closest-side at 0 0` instead of `0 0, closest-side`.",{node:f});else{let x=Km(m);for(let y of x.nodes)if(y.type==="function"&&y.value==="radial-gradient")for(let w of y.nodes)w.type==="word"&&(w.value==="cover"?t.warn("Gradient has outdated direction syntax. Replace `cover` to `farthest-corner`.",{node:f}):w.value==="contain"&&t.warn("Gradient has outdated direction syntax. Replace `contain` to `closest-side`.",{node:f}))}m.includes("linear-gradient")&&rA.test(m)&&t.warn("Gradient has outdated direction syntax. New syntax is like `to left` instead of `right`.",{node:f})}aA.includes(f.prop)&&(f.value.includes("-fill-available")||(f.value.includes("fill-available")?t.warn("Replace fill-available to stretch, because spec had been changed",{node:f}):f.value.includes("fill")&&Km(m).nodes.some(y=>y.type==="word"&&y.value==="fill")&&t.warn("Replace fill to stretch, because spec had been changed",{node:f})));let b;if(f.prop==="transition"||f.prop==="transition-property")return this.prefixes.transition.add(f,t);if(f.prop==="align-self"){if(this.displayType(f)!=="grid"&&this.prefixes.options.flexbox!==!1&&(b=this.prefixes.add["align-self"],b&&b.prefixes&&b.process(f)),this.gridStatus(f,t)!==!1&&(b=this.prefixes.add["grid-row-align"],b&&b.prefixes))return b.process(f,t)}else if(f.prop==="justify-self"){if(this.gridStatus(f,t)!==!1&&(b=this.prefixes.add["grid-column-align"],b&&b.prefixes))return b.process(f,t)}else if(f.prop==="place-self"){if(b=this.prefixes.add["place-self"],b&&b.prefixes&&this.gridStatus(f,t)!==!1)return b.process(f,t)}else if(b=this.prefixes.add[f.prop],b&&b.prefixes)return b.process(f,t)}),this.gridStatus(e,t)&&tA(e,this.disabled),e.walkDecls(f=>{if(this.disabledValue(f,t))return;let d=this.prefixes.unprefixed(f.prop),p=this.prefixes.values("add",d);if(Array.isArray(p))for(let m of p)m.process&&m.process(f,t);eA.save(this.prefixes,f)})}remove(e,t){let r=this.prefixes.remove["@resolution"];e.walkAtRules((n,a)=>{this.prefixes.remove[`@${n.name}`]?this.disabled(n,t)||n.parent.removeChild(a):n.name==="media"&&n.params.includes("-resolution")&&r&&r.clean(n)});for(let n of this.prefixes.remove.selectors)e.walkRules((a,s)=>{n.check(a)&&(this.disabled(a,t)||a.parent.removeChild(s))});return e.walkDecls((n,a)=>{if(this.disabled(n,t))return;let s=n.parent,o=this.prefixes.unprefixed(n.prop);if((n.prop==="transition"||n.prop==="transition-property")&&this.prefixes.transition.remove(n),this.prefixes.remove[n.prop]&&this.prefixes.remove[n.prop].remove){let u=this.prefixes.group(n).down(c=>this.prefixes.normalize(c.prop)===o);if(o==="flex-flow"&&(u=!0),n.prop==="-webkit-box-orient"){let c={"flex-direction":!0,"flex-flow":!0};if(!n.parent.some(f=>c[f.prop]))return}if(u&&!this.withHackValue(n)){n.raw("before").includes(`
`)&&this.reduceSpaces(n),s.removeChild(a);return}}for(let u of this.prefixes.values("remove",o)){if(!u.check||!u.check(n.value))continue;if(o=u.unprefixed,this.prefixes.group(n).down(f=>f.value.includes(o))){s.removeChild(a);return}}})}withHackValue(e){return e.prop==="-webkit-background-clip"&&e.value==="text"}disabledValue(e,t){return this.gridStatus(e,t)===!1&&e.type==="decl"&&e.prop==="display"&&e.value.includes("grid")||this.prefixes.options.flexbox===!1&&e.type==="decl"&&e.prop==="display"&&e.value.includes("flex")||e.type==="decl"&&e.prop==="content"?!0:this.disabled(e,t)}disabledDecl(e,t){if(this.gridStatus(e,t)===!1&&e.type==="decl"&&(e.prop.includes("grid")||e.prop==="justify-items"))return!0;if(this.prefixes.options.flexbox===!1&&e.type==="decl"){let r=["order","justify-content","align-items","align-content"];if(e.prop.includes("flex")||r.includes(e.prop))return!0}return this.disabled(e,t)}disabled(e,t){if(!e)return!1;if(e._autoprefixerDisabled!==void 0)return e._autoprefixerDisabled;if(e.parent){let n=e.prev();if(n&&n.type==="comment"&&nA.test(n.text))return e._autoprefixerDisabled=!0,e._autoprefixerSelfDisabled=!0,!0}let r=null;if(e.nodes){let n;e.each(a=>{a.type==="comment"&&/(!\s*)?autoprefixer:\s*(off|on)/i.test(a.text)&&(typeof n!="undefined"?t.warn("Second Autoprefixer control comment was ignored. Autoprefixer applies control comment to whole block, not to next rules.",{node:a}):n=/on/i.test(a.text))}),n!==void 0&&(r=!n)}if(!e.nodes||r===null)if(e.parent){let n=this.disabled(e.parent,t);e.parent._autoprefixerSelfDisabled===!0?r=!1:r=n}else r=!1;return e._autoprefixerDisabled=r,r}reduceSpaces(e){let t=!1;if(this.prefixes.group(e).up(()=>(t=!0,!0)),t)return;let r=e.raw("before").split(`
`),n=r[r.length-1].length,a=!1;this.prefixes.group(e).down(s=>{r=s.raw("before").split(`
`);let o=r.length-1;r[o].length>n&&(a===!1&&(a=r[o].length-n),r[o]=r[o].slice(0,-a),s.raws.before=r.join(`
`))})}displayType(e){for(let t of e.parent.nodes)if(t.prop==="display"){if(t.value.includes("flex"))return"flex";if(t.value.includes("grid"))return"grid"}return!1}gridStatus(e,t){if(!e)return!1;if(e._autoprefixerGridStatus!==void 0)return e._autoprefixerGridStatus;let r=null;if(e.nodes){let n;e.each(a=>{if(a.type==="comment"&&sA.test(a.text)){let s=/:\s*autoplace/i.test(a.text),o=/no-autoplace/i.test(a.text);typeof n!="undefined"?t.warn("Second Autoprefixer grid control comment was ignored. Autoprefixer applies control comments to the whole block, not to the next rules.",{node:a}):s?n="autoplace":o?n=!0:n=/on/i.test(a.text)}}),n!==void 0&&(r=n)}if(e.type==="atrule"&&e.name==="supports"){let n=e.params;n.includes("grid")&&n.includes("auto")&&(r=!1)}if(!e.nodes||r===null)if(e.parent){let n=this.gridStatus(e.parent,t);e.parent._autoprefixerSelfDisabled===!0?r=!1:r=n}else typeof this.prefixes.options.grid!="undefined"?r=this.prefixes.options.grid:typeof h.env.AUTOPREFIXER_GRID!="undefined"?h.env.AUTOPREFIXER_GRID==="autoplace"?r="autoplace":r=!0:r=!1;return e._autoprefixerGridStatus=r,r}};eg.exports=Zm});var rg=v((w4,tg)=>{l();tg.exports={A:{A:{"2":"K E F G A B JC"},B:{"1":"C L M H N D O P Q R S T U V W X Y Z a b c d e f g h i j n o p q r s t u v w x y z I"},C:{"1":"2 3 4 5 6 7 8 9 AB BB CB DB EB FB GB HB IB JB KB LB MB NB OB PB QB RB SB TB UB VB WB XB YB ZB aB bB cB 0B dB 1B eB fB gB hB iB jB kB lB mB nB oB m pB qB rB sB tB P Q R 2B S T U V W X Y Z a b c d e f g h i j n o p q r s t u v w x y z I uB 3B 4B","2":"0 1 KC zB J K E F G A B C L M H N D O k l LC MC"},D:{"1":"8 9 AB BB CB DB EB FB GB HB IB JB KB LB MB NB OB PB QB RB SB TB UB VB WB XB YB ZB aB bB cB 0B dB 1B eB fB gB hB iB jB kB lB mB nB oB m pB qB rB sB tB P Q R S T U V W X Y Z a b c d e f g h i j n o p q r s t u v w x y z I uB 3B 4B","2":"0 1 2 3 4 5 6 7 J K E F G A B C L M H N D O k l"},E:{"1":"G A B C L M H D RC 6B vB wB 7B SC TC 8B 9B xB AC yB BC CC DC EC FC GC UC","2":"0 J K E F NC 5B OC PC QC"},F:{"1":"1 2 3 4 5 6 7 8 9 H N D O k l AB BB CB DB EB FB GB HB IB JB KB LB MB NB OB PB QB RB SB TB UB VB WB XB YB ZB aB bB cB dB eB fB gB hB iB jB kB lB mB nB oB m pB qB rB sB tB P Q R 2B S T U V W X Y Z a b c d e f g h i j wB","2":"G B C VC WC XC YC vB HC ZC"},G:{"1":"D fC gC hC iC jC kC lC mC nC oC pC qC rC sC tC 8B 9B xB AC yB BC CC DC EC FC GC","2":"F 5B aC IC bC cC dC eC"},H:{"1":"uC"},I:{"1":"I zC 0C","2":"zB J vC wC xC yC IC"},J:{"2":"E A"},K:{"1":"m","2":"A B C vB HC wB"},L:{"1":"I"},M:{"1":"uB"},N:{"2":"A B"},O:{"1":"xB"},P:{"1":"J k l 1C 2C 3C 4C 5C 6B 6C 7C 8C 9C AD yB BD CD DD"},Q:{"1":"7B"},R:{"1":"ED"},S:{"1":"FD GD"}},B:4,C:"CSS Feature Queries"}});var ag=v((b4,sg)=>{l();function ig(i){return i[i.length-1]}var ng={parse(i){let e=[""],t=[e];for(let r of i){if(r==="("){e=[""],ig(t).push(e),t.push(e);continue}if(r===")"){t.pop(),e=ig(t),e.push("");continue}e[e.length-1]+=r}return t[0]},stringify(i){let e="";for(let t of i){if(typeof t=="object"){e+=`(${ng.stringify(t)})`;continue}e+=t}return e}};sg.exports=ng});var cg=v((v4,fg)=>{l();var lA=rg(),{feature:uA}=($n(),Ln),{parse:fA}=ge(),cA=dt(),tl=ag(),pA=ke(),dA=le(),og=uA(lA),lg=[];for(let i in og.stats){let e=og.stats[i];for(let t in e){let r=e[t];/y/.test(r)&&lg.push(i+" "+t)}}var ug=class{constructor(e,t){this.Prefixes=e,this.all=t}prefixer(){if(this.prefixerCache)return this.prefixerCache;let e=this.all.browsers.selected.filter(r=>lg.includes(r)),t=new cA(this.all.browsers.data,e,this.all.options);return this.prefixerCache=new this.Prefixes(this.all.data,t,this.all.options),this.prefixerCache}parse(e){let t=e.split(":"),r=t[0],n=t[1];return n||(n=""),[r.trim(),n.trim()]}virtual(e){let[t,r]=this.parse(e),n=fA("a{}").first;return n.append({prop:t,value:r,raws:{before:""}}),n}prefixed(e){let t=this.virtual(e);if(this.disabled(t.first))return t.nodes;let r={warn:()=>null},n=this.prefixer().add[t.first.prop];n&&n.process&&n.process(t.first,r);for(let a of t.nodes){for(let s of this.prefixer().values("add",t.first.prop))s.process(a);pA.save(this.all,a)}return t.nodes}isNot(e){return typeof e=="string"&&/not\s*/i.test(e)}isOr(e){return typeof e=="string"&&/\s*or\s*/i.test(e)}isProp(e){return typeof e=="object"&&e.length===1&&typeof e[0]=="string"}isHack(e,t){return!new RegExp(`(\\(|\\s)${dA.escapeRegexp(t)}:`).test(e)}toRemove(e,t){let[r,n]=this.parse(e),a=this.all.unprefixed(r),s=this.all.cleaner();if(s.remove[r]&&s.remove[r].remove&&!this.isHack(t,a))return!0;for(let o of s.values("remove",a))if(o.check(n))return!0;return!1}remove(e,t){let r=0;for(;r<e.length;){if(!this.isNot(e[r-1])&&this.isProp(e[r])&&this.isOr(e[r+1])){if(this.toRemove(e[r][0],t)){e.splice(r,2);continue}r+=2;continue}typeof e[r]=="object"&&(e[r]=this.remove(e[r],t)),r+=1}return e}cleanBrackets(e){return e.map(t=>typeof t!="object"?t:t.length===1&&typeof t[0]=="object"?this.cleanBrackets(t[0]):this.cleanBrackets(t))}convert(e){let t=[""];for(let r of e)t.push([`${r.prop}: ${r.value}`]),t.push(" or ");return t[t.length-1]="",t}normalize(e){if(typeof e!="object")return e;if(e=e.filter(t=>t!==""),typeof e[0]=="string"){let t=e[0].trim();if(t.includes(":")||t==="selector"||t==="not selector")return[tl.stringify(e)]}return e.map(t=>this.normalize(t))}add(e,t){return e.map(r=>{if(this.isProp(r)){let n=this.prefixed(r[0]);return n.length>1?this.convert(n):r}return typeof r=="object"?this.add(r,t):r})}process(e){let t=tl.parse(e.params);t=this.normalize(t),t=this.remove(t,e.params),t=this.add(t,e.params),t=this.cleanBrackets(t),e.params=tl.stringify(t)}disabled(e){if(!this.all.options.grid&&(e.prop==="display"&&e.value.includes("grid")||e.prop.includes("grid")||e.prop==="justify-items"))return!0;if(this.all.options.flexbox===!1){if(e.prop==="display"&&e.value.includes("flex"))return!0;let t=["order","justify-content","align-items","align-content"];if(e.prop.includes("flex")||t.includes(e.prop))return!0}return!1}};fg.exports=ug});var hg=v((x4,dg)=>{l();var pg=class{constructor(e,t){this.prefix=t,this.prefixed=e.prefixed(this.prefix),this.regexp=e.regexp(this.prefix),this.prefixeds=e.possible().map(r=>[e.prefixed(r),e.regexp(r)]),this.unprefixed=e.name,this.nameRegexp=e.regexp()}isHack(e){let t=e.parent.index(e)+1,r=e.parent.nodes;for(;t<r.length;){let n=r[t].selector;if(!n)return!0;if(n.includes(this.unprefixed)&&n.match(this.nameRegexp))return!1;let a=!1;for(let[s,o]of this.prefixeds)if(n.includes(s)&&n.match(o)){a=!0;break}if(!a)return!0;t+=1}return!0}check(e){return!(!e.selector.includes(this.prefixed)||!e.selector.match(this.regexp)||this.isHack(e))}};dg.exports=pg});var Yt=v((k4,gg)=>{l();var{list:hA}=ge(),mA=hg(),gA=Wt(),yA=dt(),wA=le(),mg=class extends gA{constructor(e,t,r){super(e,t,r);this.regexpCache=new Map}check(e){return e.selector.includes(this.name)?!!e.selector.match(this.regexp()):!1}prefixed(e){return this.name.replace(/^(\W*)/,`$1${e}`)}regexp(e){if(!this.regexpCache.has(e)){let t=e?this.prefixed(e):this.name;this.regexpCache.set(e,new RegExp(`(^|[^:"'=])${wA.escapeRegexp(t)}`,"gi"))}return this.regexpCache.get(e)}possible(){return yA.prefixes()}prefixeds(e){if(e._autoprefixerPrefixeds){if(e._autoprefixerPrefixeds[this.name])return e._autoprefixerPrefixeds}else e._autoprefixerPrefixeds={};let t={};if(e.selector.includes(",")){let n=hA.comma(e.selector).filter(a=>a.includes(this.name));for(let a of this.possible())t[a]=n.map(s=>this.replace(s,a)).join(", ")}else for(let r of this.possible())t[r]=this.replace(e.selector,r);return e._autoprefixerPrefixeds[this.name]=t,e._autoprefixerPrefixeds}already(e,t,r){let n=e.parent.index(e)-1;for(;n>=0;){let a=e.parent.nodes[n];if(a.type!=="rule")return!1;let s=!1;for(let o in t[this.name]){let u=t[this.name][o];if(a.selector===u){if(r===o)return!0;s=!0;break}}if(!s)return!1;n-=1}return!1}replace(e,t){return e.replace(this.regexp(),`$1${this.prefixed(t)}`)}add(e,t){let r=this.prefixeds(e);if(this.already(e,r,t))return;let n=this.clone(e,{selector:r[this.name][t]});e.parent.insertBefore(e,n)}old(e){return new mA(this,e)}};gg.exports=mg});var bg=v((S4,wg)=>{l();var bA=Wt(),yg=class extends bA{add(e,t){let r=t+e.name;if(e.parent.some(s=>s.name===r&&s.params===e.params))return;let a=this.clone(e,{name:r});return e.parent.insertBefore(e,a)}process(e){let t=this.parentPrefix(e);for(let r of this.prefixes)(!t||t===r)&&this.add(e,r)}};wg.exports=yg});var xg=v((C4,vg)=>{l();var vA=Yt(),rl=class extends vA{prefixed(e){return e==="-webkit-"?":-webkit-full-screen":e==="-moz-"?":-moz-full-screen":`:${e}fullscreen`}};rl.names=[":fullscreen"];vg.exports=rl});var Sg=v((A4,kg)=>{l();var xA=Yt(),il=class extends xA{possible(){return super.possible().concat(["-moz- old","-ms- old"])}prefixed(e){return e==="-webkit-"?"::-webkit-input-placeholder":e==="-ms-"?"::-ms-input-placeholder":e==="-ms- old"?":-ms-input-placeholder":e==="-moz- old"?":-moz-placeholder":`::${e}placeholder`}};il.names=["::placeholder"];kg.exports=il});var Ag=v((_4,Cg)=>{l();var kA=Yt(),nl=class extends kA{prefixed(e){return e==="-ms-"?":-ms-input-placeholder":`:${e}placeholder-shown`}};nl.names=[":placeholder-shown"];Cg.exports=nl});var Og=v((O4,_g)=>{l();var SA=Yt(),CA=le(),sl=class extends SA{constructor(e,t,r){super(e,t,r);this.prefixes&&(this.prefixes=CA.uniq(this.prefixes.map(n=>"-webkit-")))}prefixed(e){return e==="-webkit-"?"::-webkit-file-upload-button":`::${e}file-selector-button`}};sl.names=["::file-selector-button"];_g.exports=sl});var pe=v((E4,Eg)=>{l();Eg.exports=function(i){let e;return i==="-webkit- 2009"||i==="-moz-"?e=2009:i==="-ms-"?e=2012:i==="-webkit-"&&(e="final"),i==="-webkit- 2009"&&(i="-webkit-"),[e,i]}});var Ig=v((T4,Dg)=>{l();var Tg=ge().list,Pg=pe(),AA=M(),Qt=class extends AA{prefixed(e,t){let r;return[r,t]=Pg(t),r===2009?t+"box-flex":super.prefixed(e,t)}normalize(){return"flex"}set(e,t){let r=Pg(t)[0];if(r===2009)return e.value=Tg.space(e.value)[0],e.value=Qt.oldValues[e.value]||e.value,super.set(e,t);if(r===2012){let n=Tg.space(e.value);n.length===3&&n[2]==="0"&&(e.value=n.slice(0,2).concat("0px").join(" "))}return super.set(e,t)}};Qt.names=["flex","box-flex"];Qt.oldValues={auto:"1",none:"0"};Dg.exports=Qt});var Mg=v((P4,Rg)=>{l();var qg=pe(),_A=M(),al=class extends _A{prefixed(e,t){let r;return[r,t]=qg(t),r===2009?t+"box-ordinal-group":r===2012?t+"flex-order":super.prefixed(e,t)}normalize(){return"order"}set(e,t){return qg(t)[0]===2009&&/\d/.test(e.value)?(e.value=(parseInt(e.value)+1).toString(),super.set(e,t)):super.set(e,t)}};al.names=["order","flex-order","box-ordinal-group"];Rg.exports=al});var Fg=v((D4,Bg)=>{l();var OA=M(),ol=class extends OA{check(e){let t=e.value;return!t.toLowerCase().includes("alpha(")&&!t.includes("DXImageTransform.Microsoft")&&!t.includes("data:image/svg+xml")}};ol.names=["filter"];Bg.exports=ol});var Lg=v((I4,Ng)=>{l();var EA=M(),ll=class extends EA{insert(e,t,r,n){if(t!=="-ms-")return super.insert(e,t,r);let a=this.clone(e),s=e.prop.replace(/end$/,"start"),o=t+e.prop.replace(/end$/,"span");if(!e.parent.some(u=>u.prop===o)){if(a.prop=o,e.value.includes("span"))a.value=e.value.replace(/span\s/i,"");else{let u;if(e.parent.walkDecls(s,c=>{u=c}),u){let c=Number(e.value)-Number(u.value)+"";a.value=c}else e.warn(n,`Can not prefix ${e.prop} (${s} is not found)`)}e.cloneBefore(a)}}};ll.names=["grid-row-end","grid-column-end"];Ng.exports=ll});var jg=v((q4,$g)=>{l();var TA=M(),ul=class extends TA{check(e){return!e.value.split(/\s+/).some(t=>{let r=t.toLowerCase();return r==="reverse"||r==="alternate-reverse"})}};ul.names=["animation","animation-direction"];$g.exports=ul});var Vg=v((R4,zg)=>{l();var PA=pe(),DA=M(),fl=class extends DA{insert(e,t,r){let n;if([n,t]=PA(t),n!==2009)return super.insert(e,t,r);let a=e.value.split(/\s+/).filter(d=>d!=="wrap"&&d!=="nowrap"&&"wrap-reverse");if(a.length===0||e.parent.some(d=>d.prop===t+"box-orient"||d.prop===t+"box-direction"))return;let o=a[0],u=o.includes("row")?"horizontal":"vertical",c=o.includes("reverse")?"reverse":"normal",f=this.clone(e);return f.prop=t+"box-orient",f.value=u,this.needCascade(e)&&(f.raws.before=this.calcBefore(r,e,t)),e.parent.insertBefore(e,f),f=this.clone(e),f.prop=t+"box-direction",f.value=c,this.needCascade(e)&&(f.raws.before=this.calcBefore(r,e,t)),e.parent.insertBefore(e,f)}};fl.names=["flex-flow","box-direction","box-orient"];zg.exports=fl});var Wg=v((M4,Ug)=>{l();var IA=pe(),qA=M(),cl=class extends qA{normalize(){return"flex"}prefixed(e,t){let r;return[r,t]=IA(t),r===2009?t+"box-flex":r===2012?t+"flex-positive":super.prefixed(e,t)}};cl.names=["flex-grow","flex-positive"];Ug.exports=cl});var Hg=v((B4,Gg)=>{l();var RA=pe(),MA=M(),pl=class extends MA{set(e,t){if(RA(t)[0]!==2009)return super.set(e,t)}};pl.names=["flex-wrap"];Gg.exports=pl});var Qg=v((F4,Yg)=>{l();var BA=M(),Jt=mt(),dl=class extends BA{insert(e,t,r,n){if(t!=="-ms-")return super.insert(e,t,r);let a=Jt.parse(e),[s,o]=Jt.translate(a,0,2),[u,c]=Jt.translate(a,1,3);[["grid-row",s],["grid-row-span",o],["grid-column",u],["grid-column-span",c]].forEach(([f,d])=>{Jt.insertDecl(e,f,d)}),Jt.warnTemplateSelectorNotFound(e,n),Jt.warnIfGridRowColumnExists(e,n)}};dl.names=["grid-area"];Yg.exports=dl});var Xg=v((N4,Jg)=>{l();var FA=M(),ni=mt(),hl=class extends FA{insert(e,t,r){if(t!=="-ms-")return super.insert(e,t,r);if(e.parent.some(s=>s.prop==="-ms-grid-row-align"))return;let[[n,a]]=ni.parse(e);a?(ni.insertDecl(e,"grid-row-align",n),ni.insertDecl(e,"grid-column-align",a)):(ni.insertDecl(e,"grid-row-align",n),ni.insertDecl(e,"grid-column-align",n))}};hl.names=["place-self"];Jg.exports=hl});var Zg=v((L4,Kg)=>{l();var NA=M(),ml=class extends NA{check(e){let t=e.value;return!t.includes("/")||t.includes("span")}normalize(e){return e.replace("-start","")}prefixed(e,t){let r=super.prefixed(e,t);return t==="-ms-"&&(r=r.replace("-start","")),r}};ml.names=["grid-row-start","grid-column-start"];Kg.exports=ml});var ry=v(($4,ty)=>{l();var ey=pe(),LA=M(),Xt=class extends LA{check(e){return e.parent&&!e.parent.some(t=>t.prop&&t.prop.startsWith("grid-"))}prefixed(e,t){let r;return[r,t]=ey(t),r===2012?t+"flex-item-align":super.prefixed(e,t)}normalize(){return"align-self"}set(e,t){let r=ey(t)[0];if(r===2012)return e.value=Xt.oldValues[e.value]||e.value,super.set(e,t);if(r==="final")return super.set(e,t)}};Xt.names=["align-self","flex-item-align"];Xt.oldValues={"flex-end":"end","flex-start":"start"};ty.exports=Xt});var ny=v((j4,iy)=>{l();var $A=M(),jA=le(),gl=class extends $A{constructor(e,t,r){super(e,t,r);this.prefixes&&(this.prefixes=jA.uniq(this.prefixes.map(n=>n==="-ms-"?"-webkit-":n)))}};gl.names=["appearance"];iy.exports=gl});var oy=v((z4,ay)=>{l();var sy=pe(),zA=M(),yl=class extends zA{normalize(){return"flex-basis"}prefixed(e,t){let r;return[r,t]=sy(t),r===2012?t+"flex-preferred-size":super.prefixed(e,t)}set(e,t){let r;if([r,t]=sy(t),r===2012||r==="final")return super.set(e,t)}};yl.names=["flex-basis","flex-preferred-size"];ay.exports=yl});var uy=v((V4,ly)=>{l();var VA=M(),wl=class extends VA{normalize(){return this.name.replace("box-image","border")}prefixed(e,t){let r=super.prefixed(e,t);return t==="-webkit-"&&(r=r.replace("border","box-image")),r}};wl.names=["mask-border","mask-border-source","mask-border-slice","mask-border-width","mask-border-outset","mask-border-repeat","mask-box-image","mask-box-image-source","mask-box-image-slice","mask-box-image-width","mask-box-image-outset","mask-box-image-repeat"];ly.exports=wl});var cy=v((U4,fy)=>{l();var UA=M(),Le=class extends UA{insert(e,t,r){let n=e.prop==="mask-composite",a;n?a=e.value.split(","):a=e.value.match(Le.regexp)||[],a=a.map(c=>c.trim()).filter(c=>c);let s=a.length,o;if(s&&(o=this.clone(e),o.value=a.map(c=>Le.oldValues[c]||c).join(", "),a.includes("intersect")&&(o.value+=", xor"),o.prop=t+"mask-composite"),n)return s?(this.needCascade(e)&&(o.raws.before=this.calcBefore(r,e,t)),e.parent.insertBefore(e,o)):void 0;let u=this.clone(e);return u.prop=t+u.prop,s&&(u.value=u.value.replace(Le.regexp,"")),this.needCascade(e)&&(u.raws.before=this.calcBefore(r,e,t)),e.parent.insertBefore(e,u),s?(this.needCascade(e)&&(o.raws.before=this.calcBefore(r,e,t)),e.parent.insertBefore(e,o)):e}};Le.names=["mask","mask-composite"];Le.oldValues={add:"source-over",subtract:"source-out",intersect:"source-in",exclude:"xor"};Le.regexp=new RegExp(`\\s+(${Object.keys(Le.oldValues).join("|")})\\b(?!\\))\\s*(?=[,])`,"ig");fy.exports=Le});var hy=v((W4,dy)=>{l();var py=pe(),WA=M(),Kt=class extends WA{prefixed(e,t){let r;return[r,t]=py(t),r===2009?t+"box-align":r===2012?t+"flex-align":super.prefixed(e,t)}normalize(){return"align-items"}set(e,t){let r=py(t)[0];return(r===2009||r===2012)&&(e.value=Kt.oldValues[e.value]||e.value),super.set(e,t)}};Kt.names=["align-items","flex-align","box-align"];Kt.oldValues={"flex-end":"end","flex-start":"start"};dy.exports=Kt});var gy=v((G4,my)=>{l();var GA=M(),bl=class extends GA{set(e,t){return t==="-ms-"&&e.value==="contain"&&(e.value="element"),super.set(e,t)}insert(e,t,r){if(!(e.value==="all"&&t==="-ms-"))return super.insert(e,t,r)}};bl.names=["user-select"];my.exports=bl});var by=v((H4,wy)=>{l();var yy=pe(),HA=M(),vl=class extends HA{normalize(){return"flex-shrink"}prefixed(e,t){let r;return[r,t]=yy(t),r===2012?t+"flex-negative":super.prefixed(e,t)}set(e,t){let r;if([r,t]=yy(t),r===2012||r==="final")return super.set(e,t)}};vl.names=["flex-shrink","flex-negative"];wy.exports=vl});var xy=v((Y4,vy)=>{l();var YA=M(),xl=class extends YA{prefixed(e,t){return`${t}column-${e}`}normalize(e){return e.includes("inside")?"break-inside":e.includes("before")?"break-before":"break-after"}set(e,t){return(e.prop==="break-inside"&&e.value==="avoid-column"||e.value==="avoid-page")&&(e.value="avoid"),super.set(e,t)}insert(e,t,r){if(e.prop!=="break-inside")return super.insert(e,t,r);if(!(/region/i.test(e.value)||/page/i.test(e.value)))return super.insert(e,t,r)}};xl.names=["break-inside","page-break-inside","column-break-inside","break-before","page-break-before","column-break-before","break-after","page-break-after","column-break-after"];vy.exports=xl});var Sy=v((Q4,ky)=>{l();var QA=M(),kl=class extends QA{prefixed(e,t){return t+"print-color-adjust"}normalize(){return"color-adjust"}};kl.names=["color-adjust","print-color-adjust"];ky.exports=kl});var Ay=v((J4,Cy)=>{l();var JA=M(),Zt=class extends JA{insert(e,t,r){if(t==="-ms-"){let n=this.set(this.clone(e),t);this.needCascade(e)&&(n.raws.before=this.calcBefore(r,e,t));let a="ltr";return e.parent.nodes.forEach(s=>{s.prop==="direction"&&(s.value==="rtl"||s.value==="ltr")&&(a=s.value)}),n.value=Zt.msValues[a][e.value]||e.value,e.parent.insertBefore(e,n)}return super.insert(e,t,r)}};Zt.names=["writing-mode"];Zt.msValues={ltr:{"horizontal-tb":"lr-tb","vertical-rl":"tb-rl","vertical-lr":"tb-lr"},rtl:{"horizontal-tb":"rl-tb","vertical-rl":"bt-rl","vertical-lr":"bt-lr"}};Cy.exports=Zt});var Oy=v((X4,_y)=>{l();var XA=M(),Sl=class extends XA{set(e,t){return e.value=e.value.replace(/\s+fill(\s)/,"$1"),super.set(e,t)}};Sl.names=["border-image"];_y.exports=Sl});var Py=v((K4,Ty)=>{l();var Ey=pe(),KA=M(),er=class extends KA{prefixed(e,t){let r;return[r,t]=Ey(t),r===2012?t+"flex-line-pack":super.prefixed(e,t)}normalize(){return"align-content"}set(e,t){let r=Ey(t)[0];if(r===2012)return e.value=er.oldValues[e.value]||e.value,super.set(e,t);if(r==="final")return super.set(e,t)}};er.names=["align-content","flex-line-pack"];er.oldValues={"flex-end":"end","flex-start":"start","space-between":"justify","space-around":"distribute"};Ty.exports=er});var Iy=v((Z4,Dy)=>{l();var ZA=M(),Se=class extends ZA{prefixed(e,t){return t==="-moz-"?t+(Se.toMozilla[e]||e):super.prefixed(e,t)}normalize(e){return Se.toNormal[e]||e}};Se.names=["border-radius"];Se.toMozilla={};Se.toNormal={};for(let i of["top","bottom"])for(let e of["left","right"]){let t=`border-${i}-${e}-radius`,r=`border-radius-${i}${e}`;Se.names.push(t),Se.names.push(r),Se.toMozilla[t]=r,Se.toNormal[r]=t}Dy.exports=Se});var Ry=v((eI,qy)=>{l();var e_=M(),Cl=class extends e_{prefixed(e,t){return e.includes("-start")?t+e.replace("-block-start","-before"):t+e.replace("-block-end","-after")}normalize(e){return e.includes("-before")?e.replace("-before","-block-start"):e.replace("-after","-block-end")}};Cl.names=["border-block-start","border-block-end","margin-block-start","margin-block-end","padding-block-start","padding-block-end","border-before","border-after","margin-before","margin-after","padding-before","padding-after"];qy.exports=Cl});var By=v((tI,My)=>{l();var t_=M(),{parseTemplate:r_,warnMissedAreas:i_,getGridGap:n_,warnGridGap:s_,inheritGridGap:a_}=mt(),Al=class extends t_{insert(e,t,r,n){if(t!=="-ms-")return super.insert(e,t,r);if(e.parent.some(m=>m.prop==="-ms-grid-rows"))return;let a=n_(e),s=a_(e,a),{rows:o,columns:u,areas:c}=r_({decl:e,gap:s||a}),f=Object.keys(c).length>0,d=Boolean(o),p=Boolean(u);return s_({gap:a,hasColumns:p,decl:e,result:n}),i_(c,e,n),(d&&p||f)&&e.cloneBefore({prop:"-ms-grid-rows",value:o,raws:{}}),p&&e.cloneBefore({prop:"-ms-grid-columns",value:u,raws:{}}),e}};Al.names=["grid-template"];My.exports=Al});var Ny=v((rI,Fy)=>{l();var o_=M(),_l=class extends o_{prefixed(e,t){return t+e.replace("-inline","")}normalize(e){return e.replace(/(margin|padding|border)-(start|end)/,"$1-inline-$2")}};_l.names=["border-inline-start","border-inline-end","margin-inline-start","margin-inline-end","padding-inline-start","padding-inline-end","border-start","border-end","margin-start","margin-end","padding-start","padding-end"];Fy.exports=_l});var $y=v((iI,Ly)=>{l();var l_=M(),Ol=class extends l_{check(e){return!e.value.includes("flex-")&&e.value!=="baseline"}prefixed(e,t){return t+"grid-row-align"}normalize(){return"align-self"}};Ol.names=["grid-row-align"];Ly.exports=Ol});var zy=v((nI,jy)=>{l();var u_=M(),tr=class extends u_{keyframeParents(e){let{parent:t}=e;for(;t;){if(t.type==="atrule"&&t.name==="keyframes")return!0;({parent:t}=t)}return!1}contain3d(e){if(e.prop==="transform-origin")return!1;for(let t of tr.functions3d)if(e.value.includes(`${t}(`))return!0;return!1}set(e,t){return e=super.set(e,t),t==="-ms-"&&(e.value=e.value.replace(/rotatez/gi,"rotate")),e}insert(e,t,r){if(t==="-ms-"){if(!this.contain3d(e)&&!this.keyframeParents(e))return super.insert(e,t,r)}else if(t==="-o-"){if(!this.contain3d(e))return super.insert(e,t,r)}else return super.insert(e,t,r)}};tr.names=["transform","transform-origin"];tr.functions3d=["matrix3d","translate3d","translateZ","scale3d","scaleZ","rotate3d","rotateX","rotateY","perspective"];jy.exports=tr});var Wy=v((sI,Uy)=>{l();var Vy=pe(),f_=M(),El=class extends f_{normalize(){return"flex-direction"}insert(e,t,r){let n;if([n,t]=Vy(t),n!==2009)return super.insert(e,t,r);if(e.parent.some(f=>f.prop===t+"box-orient"||f.prop===t+"box-direction"))return;let s=e.value,o,u;s==="inherit"||s==="initial"||s==="unset"?(o=s,u=s):(o=s.includes("row")?"horizontal":"vertical",u=s.includes("reverse")?"reverse":"normal");let c=this.clone(e);return c.prop=t+"box-orient",c.value=o,this.needCascade(e)&&(c.raws.before=this.calcBefore(r,e,t)),e.parent.insertBefore(e,c),c=this.clone(e),c.prop=t+"box-direction",c.value=u,this.needCascade(e)&&(c.raws.before=this.calcBefore(r,e,t)),e.parent.insertBefore(e,c)}old(e,t){let r;return[r,t]=Vy(t),r===2009?[t+"box-orient",t+"box-direction"]:super.old(e,t)}};El.names=["flex-direction","box-direction","box-orient"];Uy.exports=El});var Hy=v((aI,Gy)=>{l();var c_=M(),Tl=class extends c_{check(e){return e.value==="pixelated"}prefixed(e,t){return t==="-ms-"?"-ms-interpolation-mode":super.prefixed(e,t)}set(e,t){return t!=="-ms-"?super.set(e,t):(e.prop="-ms-interpolation-mode",e.value="nearest-neighbor",e)}normalize(){return"image-rendering"}process(e,t){return super.process(e,t)}};Tl.names=["image-rendering","interpolation-mode"];Gy.exports=Tl});var Qy=v((oI,Yy)=>{l();var p_=M(),d_=le(),Pl=class extends p_{constructor(e,t,r){super(e,t,r);this.prefixes&&(this.prefixes=d_.uniq(this.prefixes.map(n=>n==="-ms-"?"-webkit-":n)))}};Pl.names=["backdrop-filter"];Yy.exports=Pl});var Xy=v((lI,Jy)=>{l();var h_=M(),m_=le(),Dl=class extends h_{constructor(e,t,r){super(e,t,r);this.prefixes&&(this.prefixes=m_.uniq(this.prefixes.map(n=>n==="-ms-"?"-webkit-":n)))}check(e){return e.value.toLowerCase()==="text"}};Dl.names=["background-clip"];Jy.exports=Dl});var Zy=v((uI,Ky)=>{l();var g_=M(),y_=["none","underline","overline","line-through","blink","inherit","initial","unset"],Il=class extends g_{check(e){return e.value.split(/\s+/).some(t=>!y_.includes(t))}};Il.names=["text-decoration"];Ky.exports=Il});var rw=v((fI,tw)=>{l();var ew=pe(),w_=M(),rr=class extends w_{prefixed(e,t){let r;return[r,t]=ew(t),r===2009?t+"box-pack":r===2012?t+"flex-pack":super.prefixed(e,t)}normalize(){return"justify-content"}set(e,t){let r=ew(t)[0];if(r===2009||r===2012){let n=rr.oldValues[e.value]||e.value;if(e.value=n,r!==2009||n!=="distribute")return super.set(e,t)}else if(r==="final")return super.set(e,t)}};rr.names=["justify-content","flex-pack","box-pack"];rr.oldValues={"flex-end":"end","flex-start":"start","space-between":"justify","space-around":"distribute"};tw.exports=rr});var nw=v((cI,iw)=>{l();var b_=M(),ql=class extends b_{set(e,t){let r=e.value.toLowerCase();return t==="-webkit-"&&!r.includes(" ")&&r!=="contain"&&r!=="cover"&&(e.value=e.value+" "+e.value),super.set(e,t)}};ql.names=["background-size"];iw.exports=ql});var aw=v((pI,sw)=>{l();var v_=M(),Rl=mt(),Ml=class extends v_{insert(e,t,r){if(t!=="-ms-")return super.insert(e,t,r);let n=Rl.parse(e),[a,s]=Rl.translate(n,0,1);n[0]&&n[0].includes("span")&&(s=n[0].join("").replace(/\D/g,"")),[[e.prop,a],[`${e.prop}-span`,s]].forEach(([u,c])=>{Rl.insertDecl(e,u,c)})}};Ml.names=["grid-row","grid-column"];sw.exports=Ml});var uw=v((dI,lw)=>{l();var x_=M(),{prefixTrackProp:ow,prefixTrackValue:k_,autoplaceGridItems:S_,getGridGap:C_,inheritGridGap:A_}=mt(),__=el(),Bl=class extends x_{prefixed(e,t){return t==="-ms-"?ow({prop:e,prefix:t}):super.prefixed(e,t)}normalize(e){return e.replace(/^grid-(rows|columns)/,"grid-template-$1")}insert(e,t,r,n){if(t!=="-ms-")return super.insert(e,t,r);let{parent:a,prop:s,value:o}=e,u=s.includes("rows"),c=s.includes("columns"),f=a.some(k=>k.prop==="grid-template"||k.prop==="grid-template-areas");if(f&&u)return!1;let d=new __({options:{}}),p=d.gridStatus(a,n),m=C_(e);m=A_(e,m)||m;let b=u?m.row:m.column;(p==="no-autoplace"||p===!0)&&!f&&(b=null);let x=k_({value:o,gap:b});e.cloneBefore({prop:ow({prop:s,prefix:t}),value:x});let y=a.nodes.find(k=>k.prop==="grid-auto-flow"),w="row";if(y&&!d.disabled(y,n)&&(w=y.value.trim()),p==="autoplace"){let k=a.nodes.find(_=>_.prop==="grid-template-rows");if(!k&&f)return;if(!k&&!f){e.warn(n,"Autoplacement does not work without grid-template-rows property");return}!a.nodes.find(_=>_.prop==="grid-template-columns")&&!f&&e.warn(n,"Autoplacement does not work without grid-template-columns property"),c&&!f&&S_(e,n,m,w)}}};Bl.names=["grid-template-rows","grid-template-columns","grid-rows","grid-columns"];lw.exports=Bl});var cw=v((hI,fw)=>{l();var O_=M(),Fl=class extends O_{check(e){return!e.value.includes("flex-")&&e.value!=="baseline"}prefixed(e,t){return t+"grid-column-align"}normalize(){return"justify-self"}};Fl.names=["grid-column-align"];fw.exports=Fl});var dw=v((mI,pw)=>{l();var E_=M(),Nl=class extends E_{prefixed(e,t){return t+"scroll-chaining"}normalize(){return"overscroll-behavior"}set(e,t){return e.value==="auto"?e.value="chained":(e.value==="none"||e.value==="contain")&&(e.value="none"),super.set(e,t)}};Nl.names=["overscroll-behavior","scroll-chaining"];pw.exports=Nl});var gw=v((gI,mw)=>{l();var T_=M(),{parseGridAreas:P_,warnMissedAreas:D_,prefixTrackProp:I_,prefixTrackValue:hw,getGridGap:q_,warnGridGap:R_,inheritGridGap:M_}=mt();function B_(i){return i.trim().slice(1,-1).split(/["']\s*["']?/g)}var Ll=class extends T_{insert(e,t,r,n){if(t!=="-ms-")return super.insert(e,t,r);let a=!1,s=!1,o=e.parent,u=q_(e);u=M_(e,u)||u,o.walkDecls(/-ms-grid-rows/,d=>d.remove()),o.walkDecls(/grid-template-(rows|columns)/,d=>{if(d.prop==="grid-template-rows"){s=!0;let{prop:p,value:m}=d;d.cloneBefore({prop:I_({prop:p,prefix:t}),value:hw({value:m,gap:u.row})})}else a=!0});let c=B_(e.value);a&&!s&&u.row&&c.length>1&&e.cloneBefore({prop:"-ms-grid-rows",value:hw({value:`repeat(${c.length}, auto)`,gap:u.row}),raws:{}}),R_({gap:u,hasColumns:a,decl:e,result:n});let f=P_({rows:c,gap:u});return D_(f,e,n),e}};Ll.names=["grid-template-areas"];mw.exports=Ll});var ww=v((yI,yw)=>{l();var F_=M(),$l=class extends F_{set(e,t){return t==="-webkit-"&&(e.value=e.value.replace(/\s*(right|left)\s*/i,"")),super.set(e,t)}};$l.names=["text-emphasis-position"];yw.exports=$l});var vw=v((wI,bw)=>{l();var N_=M(),jl=class extends N_{set(e,t){return e.prop==="text-decoration-skip-ink"&&e.value==="auto"?(e.prop=t+"text-decoration-skip",e.value="ink",e):super.set(e,t)}};jl.names=["text-decoration-skip-ink","text-decoration-skip"];bw.exports=jl});var _w=v((bI,Aw)=>{l();"use strict";Aw.exports={wrap:xw,limit:kw,validate:Sw,test:zl,curry:L_,name:Cw};function xw(i,e,t){var r=e-i;return((t-i)%r+r)%r+i}function kw(i,e,t){return Math.max(i,Math.min(e,t))}function Sw(i,e,t,r,n){if(!zl(i,e,t,r,n))throw new Error(t+" is outside of range ["+i+","+e+")");return t}function zl(i,e,t,r,n){return!(t<i||t>e||n&&t===e||r&&t===i)}function Cw(i,e,t,r){return(t?"(":"[")+i+","+e+(r?")":"]")}function L_(i,e,t,r){var n=Cw.bind(null,i,e,t,r);return{wrap:xw.bind(null,i,e),limit:kw.bind(null,i,e),validate:function(a){return Sw(i,e,a,t,r)},test:function(a){return zl(i,e,a,t,r)},toString:n,name:n}}});var Tw=v((vI,Ew)=>{l();var Vl=Gn(),$_=_w(),j_=Ht(),z_=ke(),V_=le(),Ow=/top|left|right|bottom/gi,Qe=class extends z_{replace(e,t){let r=Vl(e);for(let n of r.nodes)if(n.type==="function"&&n.value===this.name)if(n.nodes=this.newDirection(n.nodes),n.nodes=this.normalize(n.nodes),t==="-webkit- old"){if(!this.oldWebkit(n))return!1}else n.nodes=this.convertDirection(n.nodes),n.value=t+n.value;return r.toString()}replaceFirst(e,...t){return t.map(n=>n===" "?{type:"space",value:n}:{type:"word",value:n}).concat(e.slice(1))}normalizeUnit(e,t){return`${parseFloat(e)/t*360}deg`}normalize(e){if(!e[0])return e;if(/-?\d+(.\d+)?grad/.test(e[0].value))e[0].value=this.normalizeUnit(e[0].value,400);else if(/-?\d+(.\d+)?rad/.test(e[0].value))e[0].value=this.normalizeUnit(e[0].value,2*Math.PI);else if(/-?\d+(.\d+)?turn/.test(e[0].value))e[0].value=this.normalizeUnit(e[0].value,1);else if(e[0].value.includes("deg")){let t=parseFloat(e[0].value);t=$_.wrap(0,360,t),e[0].value=`${t}deg`}return e[0].value==="0deg"?e=this.replaceFirst(e,"to"," ","top"):e[0].value==="90deg"?e=this.replaceFirst(e,"to"," ","right"):e[0].value==="180deg"?e=this.replaceFirst(e,"to"," ","bottom"):e[0].value==="270deg"&&(e=this.replaceFirst(e,"to"," ","left")),e}newDirection(e){if(e[0].value==="to"||(Ow.lastIndex=0,!Ow.test(e[0].value)))return e;e.unshift({type:"word",value:"to"},{type:"space",value:" "});for(let t=2;t<e.length&&e[t].type!=="div";t++)e[t].type==="word"&&(e[t].value=this.revertDirection(e[t].value));return e}isRadial(e){let t="before";for(let r of e)if(t==="before"&&r.type==="space")t="at";else if(t==="at"&&r.value==="at")t="after";else{if(t==="after"&&r.type==="space")return!0;if(r.type==="div")break;t="before"}return!1}convertDirection(e){return e.length>0&&(e[0].value==="to"?this.fixDirection(e):e[0].value.includes("deg")?this.fixAngle(e):this.isRadial(e)&&this.fixRadial(e)),e}fixDirection(e){e.splice(0,2);for(let t of e){if(t.type==="div")break;t.type==="word"&&(t.value=this.revertDirection(t.value))}}fixAngle(e){let t=e[0].value;t=parseFloat(t),t=Math.abs(450-t)%360,t=this.roundFloat(t,3),e[0].value=`${t}deg`}fixRadial(e){let t=[],r=[],n,a,s,o,u;for(o=0;o<e.length-2;o++)if(n=e[o],a=e[o+1],s=e[o+2],n.type==="space"&&a.value==="at"&&s.type==="space"){u=o+3;break}else t.push(n);let c;for(o=u;o<e.length;o++)if(e[o].type==="div"){c=e[o];break}else r.push(e[o]);e.splice(0,o,...r,c,...t)}revertDirection(e){return Qe.directions[e.toLowerCase()]||e}roundFloat(e,t){return parseFloat(e.toFixed(t))}oldWebkit(e){let{nodes:t}=e,r=Vl.stringify(e.nodes);if(this.name!=="linear-gradient"||t[0]&&t[0].value.includes("deg")||r.includes("px")||r.includes("-corner")||r.includes("-side"))return!1;let n=[[]];for(let a of t)n[n.length-1].push(a),a.type==="div"&&a.value===","&&n.push([]);this.oldDirection(n),this.colorStops(n),e.nodes=[];for(let a of n)e.nodes=e.nodes.concat(a);return e.nodes.unshift({type:"word",value:"linear"},this.cloneDiv(e.nodes)),e.value="-webkit-gradient",!0}oldDirection(e){let t=this.cloneDiv(e[0]);if(e[0][0].value!=="to")return e.unshift([{type:"word",value:Qe.oldDirections.bottom},t]);{let r=[];for(let a of e[0].slice(2))a.type==="word"&&r.push(a.value.toLowerCase());r=r.join(" ");let n=Qe.oldDirections[r]||r;return e[0]=[{type:"word",value:n},t],e[0]}}cloneDiv(e){for(let t of e)if(t.type==="div"&&t.value===",")return t;return{type:"div",value:",",after:" "}}colorStops(e){let t=[];for(let r=0;r<e.length;r++){let n,a=e[r],s;if(r===0)continue;let o=Vl.stringify(a[0]);a[1]&&a[1].type==="word"?n=a[1].value:a[2]&&a[2].type==="word"&&(n=a[2].value);let u;r===1&&(!n||n==="0%")?u=`from(${o})`:r===e.length-1&&(!n||n==="100%")?u=`to(${o})`:n?u=`color-stop(${n}, ${o})`:u=`color-stop(${o})`;let c=a[a.length-1];e[r]=[{type:"word",value:u}],c.type==="div"&&c.value===","&&(s=e[r].push(c)),t.push(s)}return t}old(e){if(e==="-webkit-"){let t=this.name==="linear-gradient"?"linear":"radial",r="-gradient",n=V_.regexp(`-webkit-(${t}-gradient|gradient\\(\\s*${t})`,!1);return new j_(this.name,e+this.name,r,n)}else return super.old(e)}add(e,t){let r=e.prop;if(r.includes("mask")){if(t==="-webkit-"||t==="-webkit- old")return super.add(e,t)}else if(r==="list-style"||r==="list-style-image"||r==="content"){if(t==="-webkit-"||t==="-webkit- old")return super.add(e,t)}else return super.add(e,t)}};Qe.names=["linear-gradient","repeating-linear-gradient","radial-gradient","repeating-radial-gradient"];Qe.directions={top:"bottom",left:"right",bottom:"top",right:"left"};Qe.oldDirections={top:"left bottom, left top",left:"right top, left top",bottom:"left top, left bottom",right:"left top, right top","top right":"left bottom, right top","top left":"right bottom, left top","right top":"left bottom, right top","right bottom":"left top, right bottom","bottom right":"left top, right bottom","bottom left":"right top, left bottom","left top":"right bottom, left top","left bottom":"right top, left bottom"};Ew.exports=Qe});var Iw=v((xI,Dw)=>{l();var U_=Ht(),W_=ke();function Pw(i){return new RegExp(`(^|[\\s,(])(${i}($|[\\s),]))`,"gi")}var Ul=class extends W_{regexp(){return this.regexpCache||(this.regexpCache=Pw(this.name)),this.regexpCache}isStretch(){return this.name==="stretch"||this.name==="fill"||this.name==="fill-available"}replace(e,t){return t==="-moz-"&&this.isStretch()?e.replace(this.regexp(),"$1-moz-available$3"):t==="-webkit-"&&this.isStretch()?e.replace(this.regexp(),"$1-webkit-fill-available$3"):super.replace(e,t)}old(e){let t=e+this.name;return this.isStretch()&&(e==="-moz-"?t="-moz-available":e==="-webkit-"&&(t="-webkit-fill-available")),new U_(this.name,t,t,Pw(t))}add(e,t){if(!(e.prop.includes("grid")&&t!=="-webkit-"))return super.add(e,t)}};Ul.names=["max-content","min-content","fit-content","fill","fill-available","stretch"];Dw.exports=Ul});var Mw=v((kI,Rw)=>{l();var qw=Ht(),G_=ke(),Wl=class extends G_{replace(e,t){return t==="-webkit-"?e.replace(this.regexp(),"$1-webkit-optimize-contrast"):t==="-moz-"?e.replace(this.regexp(),"$1-moz-crisp-edges"):super.replace(e,t)}old(e){return e==="-webkit-"?new qw(this.name,"-webkit-optimize-contrast"):e==="-moz-"?new qw(this.name,"-moz-crisp-edges"):super.old(e)}};Wl.names=["pixelated"];Rw.exports=Wl});var Fw=v((SI,Bw)=>{l();var H_=ke(),Gl=class extends H_{replace(e,t){let r=super.replace(e,t);return t==="-webkit-"&&(r=r.replace(/("[^"]+"|'[^']+')(\s+\d+\w)/gi,"url($1)$2")),r}};Gl.names=["image-set"];Bw.exports=Gl});var Lw=v((CI,Nw)=>{l();var Y_=ge().list,Q_=ke(),Hl=class extends Q_{replace(e,t){return Y_.space(e).map(r=>{if(r.slice(0,+this.name.length+1)!==this.name+"(")return r;let n=r.lastIndexOf(")"),a=r.slice(n+1),s=r.slice(this.name.length+1,n);if(t==="-webkit-"){let o=s.match(/\d*.?\d+%?/);o?(s=s.slice(o[0].length).trim(),s+=`, ${o[0]}`):s+=", 0.5"}return t+this.name+"("+s+")"+a}).join(" ")}};Hl.names=["cross-fade"];Nw.exports=Hl});var jw=v((AI,$w)=>{l();var J_=pe(),X_=Ht(),K_=ke(),Yl=class extends K_{constructor(e,t){super(e,t);e==="display-flex"&&(this.name="flex")}check(e){return e.prop==="display"&&e.value===this.name}prefixed(e){let t,r;return[t,e]=J_(e),t===2009?this.name==="flex"?r="box":r="inline-box":t===2012?this.name==="flex"?r="flexbox":r="inline-flexbox":t==="final"&&(r=this.name),e+r}replace(e,t){return this.prefixed(t)}old(e){let t=this.prefixed(e);if(!!t)return new X_(this.name,t)}};Yl.names=["display-flex","inline-flex"];$w.exports=Yl});var Vw=v((_I,zw)=>{l();var Z_=ke(),Ql=class extends Z_{constructor(e,t){super(e,t);e==="display-grid"&&(this.name="grid")}check(e){return e.prop==="display"&&e.value===this.name}};Ql.names=["display-grid","inline-grid"];zw.exports=Ql});var Ww=v((OI,Uw)=>{l();var e5=ke(),Jl=class extends e5{constructor(e,t){super(e,t);e==="filter-function"&&(this.name="filter")}};Jl.names=["filter","filter-function"];Uw.exports=Jl});var Qw=v((EI,Yw)=>{l();var Gw=ii(),B=M(),Hw=Em(),t5=Gm(),r5=el(),i5=cg(),Xl=dt(),ir=Yt(),n5=bg(),$e=ke(),nr=le(),s5=xg(),a5=Sg(),o5=Ag(),l5=Og(),u5=Ig(),f5=Mg(),c5=Fg(),p5=Lg(),d5=jg(),h5=Vg(),m5=Wg(),g5=Hg(),y5=Qg(),w5=Xg(),b5=Zg(),v5=ry(),x5=ny(),k5=oy(),S5=uy(),C5=cy(),A5=hy(),_5=gy(),O5=by(),E5=xy(),T5=Sy(),P5=Ay(),D5=Oy(),I5=Py(),q5=Iy(),R5=Ry(),M5=By(),B5=Ny(),F5=$y(),N5=zy(),L5=Wy(),$5=Hy(),j5=Qy(),z5=Xy(),V5=Zy(),U5=rw(),W5=nw(),G5=aw(),H5=uw(),Y5=cw(),Q5=dw(),J5=gw(),X5=ww(),K5=vw(),Z5=Tw(),eO=Iw(),tO=Mw(),rO=Fw(),iO=Lw(),nO=jw(),sO=Vw(),aO=Ww();ir.hack(s5);ir.hack(a5);ir.hack(o5);ir.hack(l5);B.hack(u5);B.hack(f5);B.hack(c5);B.hack(p5);B.hack(d5);B.hack(h5);B.hack(m5);B.hack(g5);B.hack(y5);B.hack(w5);B.hack(b5);B.hack(v5);B.hack(x5);B.hack(k5);B.hack(S5);B.hack(C5);B.hack(A5);B.hack(_5);B.hack(O5);B.hack(E5);B.hack(T5);B.hack(P5);B.hack(D5);B.hack(I5);B.hack(q5);B.hack(R5);B.hack(M5);B.hack(B5);B.hack(F5);B.hack(N5);B.hack(L5);B.hack($5);B.hack(j5);B.hack(z5);B.hack(V5);B.hack(U5);B.hack(W5);B.hack(G5);B.hack(H5);B.hack(Y5);B.hack(Q5);B.hack(J5);B.hack(X5);B.hack(K5);$e.hack(Z5);$e.hack(eO);$e.hack(tO);$e.hack(rO);$e.hack(iO);$e.hack(nO);$e.hack(sO);$e.hack(aO);var Kl=new Map,si=class{constructor(e,t,r={}){this.data=e,this.browsers=t,this.options=r,[this.add,this.remove]=this.preprocess(this.select(this.data)),this.transition=new t5(this),this.processor=new r5(this)}cleaner(){if(this.cleanerCache)return this.cleanerCache;if(this.browsers.selected.length){let e=new Xl(this.browsers.data,[]);this.cleanerCache=new si(this.data,e,this.options)}else return this;return this.cleanerCache}select(e){let t={add:{},remove:{}};for(let r in e){let n=e[r],a=n.browsers.map(u=>{let c=u.split(" ");return{browser:`${c[0]} ${c[1]}`,note:c[2]}}),s=a.filter(u=>u.note).map(u=>`${this.browsers.prefix(u.browser)} ${u.note}`);s=nr.uniq(s),a=a.filter(u=>this.browsers.isSelected(u.browser)).map(u=>{let c=this.browsers.prefix(u.browser);return u.note?`${c} ${u.note}`:c}),a=this.sort(nr.uniq(a)),this.options.flexbox==="no-2009"&&(a=a.filter(u=>!u.includes("2009")));let o=n.browsers.map(u=>this.browsers.prefix(u));n.mistakes&&(o=o.concat(n.mistakes)),o=o.concat(s),o=nr.uniq(o),a.length?(t.add[r]=a,a.length<o.length&&(t.remove[r]=o.filter(u=>!a.includes(u)))):t.remove[r]=o}return t}sort(e){return e.sort((t,r)=>{let n=nr.removeNote(t).length,a=nr.removeNote(r).length;return n===a?r.length-t.length:a-n})}preprocess(e){let t={selectors:[],"@supports":new i5(si,this)};for(let n in e.add){let a=e.add[n];if(n==="@keyframes"||n==="@viewport")t[n]=new n5(n,a,this);else if(n==="@resolution")t[n]=new Hw(n,a,this);else if(this.data[n].selector)t.selectors.push(ir.load(n,a,this));else{let s=this.data[n].props;if(s){let o=$e.load(n,a,this);for(let u of s)t[u]||(t[u]={values:[]}),t[u].values.push(o)}else{let o=t[n]&&t[n].values||[];t[n]=B.load(n,a,this),t[n].values=o}}}let r={selectors:[]};for(let n in e.remove){let a=e.remove[n];if(this.data[n].selector){let s=ir.load(n,a);for(let o of a)r.selectors.push(s.old(o))}else if(n==="@keyframes"||n==="@viewport")for(let s of a){let o=`@${s}${n.slice(1)}`;r[o]={remove:!0}}else if(n==="@resolution")r[n]=new Hw(n,a,this);else{let s=this.data[n].props;if(s){let o=$e.load(n,[],this);for(let u of a){let c=o.old(u);if(c)for(let f of s)r[f]||(r[f]={}),r[f].values||(r[f].values=[]),r[f].values.push(c)}}else for(let o of a){let u=this.decl(n).old(n,o);if(n==="align-self"){let c=t[n]&&t[n].prefixes;if(c){if(o==="-webkit- 2009"&&c.includes("-webkit-"))continue;if(o==="-webkit-"&&c.includes("-webkit- 2009"))continue}}for(let c of u)r[c]||(r[c]={}),r[c].remove=!0}}}return[t,r]}decl(e){return Kl.has(e)||Kl.set(e,B.load(e)),Kl.get(e)}unprefixed(e){let t=this.normalize(Gw.unprefixed(e));return t==="flex-direction"&&(t="flex-flow"),t}normalize(e){return this.decl(e).normalize(e)}prefixed(e,t){return e=Gw.unprefixed(e),this.decl(e).prefixed(e,t)}values(e,t){let r=this[e],n=r["*"]&&r["*"].values,a=r[t]&&r[t].values;return n&&a?nr.uniq(n.concat(a)):n||a||[]}group(e){let t=e.parent,r=t.index(e),{length:n}=t.nodes,a=this.unprefixed(e.prop),s=(o,u)=>{for(r+=o;r>=0&&r<n;){let c=t.nodes[r];if(c.type==="decl"){if(o===-1&&c.prop===a&&!Xl.withPrefix(c.value)||this.unprefixed(c.prop)!==a)break;if(u(c)===!0)return!0;if(o===1&&c.prop===a&&!Xl.withPrefix(c.value))break}r+=o}return!1};return{up(o){return s(-1,o)},down(o){return s(1,o)}}}};Yw.exports=si});var Xw=v((TI,Jw)=>{l();Jw.exports={"backdrop-filter":{feature:"css-backdrop-filter",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5","safari 16.5"]},element:{props:["background","background-image","border-image","mask","list-style","list-style-image","content","mask-image"],feature:"css-element-function",browsers:["firefox 114"]},"user-select":{mistakes:["-khtml-"],feature:"user-select-none",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5","safari 16.5"]},"background-clip":{feature:"background-clip-text",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},hyphens:{feature:"css-hyphens",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5","safari 16.5"]},fill:{props:["width","min-width","max-width","height","min-height","max-height","inline-size","min-inline-size","max-inline-size","block-size","min-block-size","max-block-size","grid","grid-template","grid-template-rows","grid-template-columns","grid-auto-columns","grid-auto-rows"],feature:"intrinsic-width",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"fill-available":{props:["width","min-width","max-width","height","min-height","max-height","inline-size","min-inline-size","max-inline-size","block-size","min-block-size","max-block-size","grid","grid-template","grid-template-rows","grid-template-columns","grid-auto-columns","grid-auto-rows"],feature:"intrinsic-width",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},stretch:{props:["width","min-width","max-width","height","min-height","max-height","inline-size","min-inline-size","max-inline-size","block-size","min-block-size","max-block-size","grid","grid-template","grid-template-rows","grid-template-columns","grid-auto-columns","grid-auto-rows"],feature:"intrinsic-width",browsers:["firefox 114"]},"fit-content":{props:["width","min-width","max-width","height","min-height","max-height","inline-size","min-inline-size","max-inline-size","block-size","min-block-size","max-block-size","grid","grid-template","grid-template-rows","grid-template-columns","grid-auto-columns","grid-auto-rows"],feature:"intrinsic-width",browsers:["firefox 114"]},"text-decoration-style":{feature:"text-decoration",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5"]},"text-decoration-color":{feature:"text-decoration",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5"]},"text-decoration-line":{feature:"text-decoration",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5"]},"text-decoration":{feature:"text-decoration",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5"]},"text-decoration-skip":{feature:"text-decoration",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5"]},"text-decoration-skip-ink":{feature:"text-decoration",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5"]},"text-size-adjust":{feature:"text-size-adjust",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5"]},"mask-clip":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-composite":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-image":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-origin":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-repeat":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-border-repeat":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-border-source":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},mask:{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-position":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-size":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-border":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-border-outset":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-border-width":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"mask-border-slice":{feature:"css-masks",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},"clip-path":{feature:"css-clip-path",browsers:["samsung 21"]},"box-decoration-break":{feature:"css-boxdecorationbreak",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5","opera 99","safari 16.5","samsung 21"]},appearance:{feature:"css-appearance",browsers:["samsung 21"]},"image-set":{props:["background","background-image","border-image","cursor","mask","mask-image","list-style","list-style-image","content"],feature:"css-image-set",browsers:["and_uc 15.5","chrome 109","samsung 21"]},"cross-fade":{props:["background","background-image","border-image","mask","list-style","list-style-image","content","mask-image"],feature:"css-cross-fade",browsers:["and_chr 114","and_uc 15.5","chrome 109","chrome 113","chrome 114","edge 114","opera 99","samsung 21"]},isolate:{props:["unicode-bidi"],feature:"css-unicode-bidi",browsers:["ios_saf 16.1","ios_saf 16.3","ios_saf 16.4","ios_saf 16.5","safari 16.5"]},"color-adjust":{feature:"css-color-adjust",browsers:["chrome 109","chrome 113","chrome 114","edge 114","opera 99"]}}});var Zw=v((PI,Kw)=>{l();Kw.exports={}});var ib=v((DI,rb)=>{l();var oO=zo(),{agents:lO}=($n(),Ln),Zl=hm(),uO=dt(),fO=Qw(),cO=Xw(),pO=Zw(),eb={browsers:lO,prefixes:cO},tb=`
  Replace Autoprefixer \`browsers\` option to Browserslist config.
  Use \`browserslist\` key in \`package.json\` or \`.browserslistrc\` file.

  Using \`browsers\` option can cause errors. Browserslist config can
  be used for Babel, Autoprefixer, postcss-normalize and other tools.

  If you really need to use option, rename it to \`overrideBrowserslist\`.

  Learn more at:
  https://github.com/browserslist/browserslist#readme
  https://twitter.com/browserslist

`;function dO(i){return Object.prototype.toString.apply(i)==="[object Object]"}var eu=new Map;function hO(i,e){e.browsers.selected.length!==0&&(e.add.selectors.length>0||Object.keys(e.add).length>2||i.warn(`Autoprefixer target browsers do not need any prefixes.You do not need Autoprefixer anymore.
Check your Browserslist config to be sure that your targets are set up correctly.

  Learn more at:
  https://github.com/postcss/autoprefixer#readme
  https://github.com/browserslist/browserslist#readme

`))}rb.exports=sr;function sr(...i){let e;if(i.length===1&&dO(i[0])?(e=i[0],i=void 0):i.length===0||i.length===1&&!i[0]?i=void 0:i.length<=2&&(Array.isArray(i[0])||!i[0])?(e=i[1],i=i[0]):typeof i[i.length-1]=="object"&&(e=i.pop()),e||(e={}),e.browser)throw new Error("Change `browser` option to `overrideBrowserslist` in Autoprefixer");if(e.browserslist)throw new Error("Change `browserslist` option to `overrideBrowserslist` in Autoprefixer");e.overrideBrowserslist?i=e.overrideBrowserslist:e.browsers&&(typeof console!="undefined"&&console.warn&&(Zl.red?console.warn(Zl.red(tb.replace(/`[^`]+`/g,n=>Zl.yellow(n.slice(1,-1))))):console.warn(tb)),i=e.browsers);let t={ignoreUnknownVersions:e.ignoreUnknownVersions,stats:e.stats,env:e.env};function r(n){let a=eb,s=new uO(a.browsers,i,n,t),o=s.selected.join(", ")+JSON.stringify(e);return eu.has(o)||eu.set(o,new fO(a.prefixes,s,e)),eu.get(o)}return{postcssPlugin:"autoprefixer",prepare(n){let a=r({from:n.opts.from,env:e.env});return{OnceExit(s){hO(n,a),e.remove!==!1&&a.processor.remove(s,n),e.add!==!1&&a.processor.add(s,n)}}},info(n){return n=n||{},n.from=n.from||h.cwd(),pO(r(n))},options:e,browsers:i}}sr.postcss=!0;sr.data=eb;sr.defaults=oO.defaults;sr.info=()=>sr().info()});var nb={};Ae(nb,{default:()=>mO});var mO,sb=C(()=>{l();mO=[]});var ob={};Ae(ob,{default:()=>gO});var ab,gO,lb=C(()=>{l();hi();ab=X(bi()),gO=Ze(ab.default.theme)});var fb={};Ae(fb,{default:()=>yO});var ub,yO,cb=C(()=>{l();hi();ub=X(bi()),yO=Ze(ub.default)});l();"use strict";var wO=Je(pm()),bO=Je(ge()),vO=Je(ib()),xO=Je((sb(),nb)),kO=Je((lb(),ob)),SO=Je((cb(),fb)),CO=Je((Zn(),bu)),AO=Je((mo(),ho)),_O=Je((hs(),Ku));function Je(i){return i&&i.__esModule?i:{default:i}}console.warn("cdn.tailwindcss.com should not be used in production. To use Tailwind CSS in production, install it as a PostCSS plugin or use the Tailwind CLI: https://tailwindcss.com/docs/installation");var Hn="tailwind",tu="text/tailwindcss",pb="/template.html",kt,db=!0,hb=0,ru=new Set,iu,mb="",gb=(i=!1)=>({get(e,t){return(!i||t==="config")&&typeof e[t]=="object"&&e[t]!==null?new Proxy(e[t],gb()):e[t]},set(e,t,r){return e[t]=r,(!i||t==="config")&&nu(!0),!0}});window[Hn]=new Proxy({config:{},defaultTheme:kO.default,defaultConfig:SO.default,colors:CO.default,plugin:AO.default,resolveConfig:_O.default},gb(!0));function yb(i){iu.observe(i,{attributes:!0,attributeFilter:["type"],characterData:!0,subtree:!0,childList:!0})}new MutationObserver(async i=>{let e=!1;if(!iu){iu=new MutationObserver(async()=>await nu(!0));for(let t of document.querySelectorAll(`style[type="${tu}"]`))yb(t)}for(let t of i)for(let r of t.addedNodes)r.nodeType===1&&r.tagName==="STYLE"&&r.getAttribute("type")===tu&&(yb(r),e=!0);await nu(e)}).observe(document.documentElement,{attributes:!0,attributeFilter:["class"],childList:!0,subtree:!0});async function nu(i=!1){i&&(hb++,ru.clear());let e="";for(let r of document.querySelectorAll(`style[type="${tu}"]`))e+=r.textContent;let t=new Set;for(let r of document.querySelectorAll("[class]"))for(let n of r.classList)ru.has(n)||t.add(n);if(document.body&&(db||t.size>0||e!==mb||!kt||!kt.isConnected)){for(let n of t)ru.add(n);db=!1,mb=e,self[pb]=Array.from(t).join(" ");let{css:r}=await(0,bO.default)([(0,wO.default)({...window[Hn].config,_hash:hb,content:{files:[pb],extract:{html:n=>n.split(" ")}},plugins:[...xO.default,...Array.isArray(window[Hn].config.plugins)?window[Hn].config.plugins:[]]}),(0,vO.default)({remove:!1})]).process(`@tailwind base;@tailwind components;@tailwind utilities;${e}`);(!kt||!kt.isConnected)&&(kt=document.createElement("style"),document.head.append(kt)),kt.textContent=r}}})();
/*! https://mths.be/cssesc v3.0.0 by @mathias */
```
### adm/_z01/js/tms_datepicker.js
```js
$(function(){
    $('.from_date').addClass('tms_dt').datepicker({
        showButtonPanel: true,
        closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
        onClose:function(dateText, inst){
            let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
                $('.to_date').val('');
            }
        },
		onSelect:function(selectedDate){
			$('.to_date').datepicker('option','minDate',selectedDate);
			$('.to_date').val('');
		}
	});
	
    $('.to_date').addClass('tms_dt').datepicker({
        showButtonPanel: true,
		closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
        //minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
        onClose:function(dateText, inst){
			let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
            }
		},
		onSelect:function(selectedDate){
			if($('.from_date').val() == ''){
				$(this).val('');
			}
		}
	});
	
    $('.tms_date').addClass('tms_dt').datepicker({
        showButtonPanel: true,
		closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
		//minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
		onClose:function(dateText, inst){
			let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
            }
		}
	});
	
    $('.required_date').addClass('tms_dt').datepicker({
        showButtonPanel: true,
		closeText:'닫기',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
		//minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
	});
});

// 시작날짜/종료날짜함수 인수로 ID값으로 넘기자 '#from_date', '#to_date'
if (typeof (range_date) != 'function') { 
function range_date(f_id, t_id) {
    $(f_id).datepicker({
        showButtonPanel: true,
        closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
        yearRange: '1910:2100',
        onClose:function(dateText, inst){
            let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
                $(t_id).val('');
            }
		},
		onSelect:function(selectedDate){
			$(t_id).datepicker('option','minDate',selectedDate);
			$(t_id).val('');
		}
    });

    $(t_id).datepicker({
        showButtonPanel: true,
		closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
        //minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
        onClose:function(dateText, inst){
			let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
            }
		},
		onSelect:function(selectedDate){
			if($(f_id).val() == ''){
				$(this).val('');
			}
		}
	});
}
}

// 날짜선택함수 인수로 ID값으로 넘기자 '#tms_date'
if (typeof (single_date) != 'function') {
function single_date(tms_id) {
    $(tms_id).datepicker({
        showButtonPanel: true,
        closeText:'취소',
        prevText:'이전달',
        nextText:'다음달',
        currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
        dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1940:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
        //minDate: '-1D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
        // 아래 함수의 기능은 
        onClose: function (dateText, inst) {
            let closeBtn = $(inst.dpDiv).find('.ui-datepicker-close');
            if (closeBtn.is(':focus')) {
                $(this).val('');
            }
        }
	});
}
}
//날짜형식에 맞는지 날짜유효성 검사함수
if(typeof(tms_dt_valid) != 'function'){
function tms_dt_valid(dt){
	var dt_ptrn = /[0-9]{4}-[0-9]{2}-[0-9]{2}/;
	var y = 0;
	var m = 0;
	var d = 0;
	var m_day = [31,28,31,30,31,30,31,31,30,31,30,31];
	//일자척으로 10자리가 아니면 날짜 형식 아니므로 실패
	if(dt.length != 10)
		return false;
	//일차적으로 10자리의 날짜 형식이 아니면 실패
	if(!dt_ptrn.test(dt))
		return false;
	
	var dt_arr = dt.split("-");
	y = parseInt(dt_arr[0],10);
	m = parseInt(dt_arr[1],10);
	d = parseInt(dt_arr[2],10);
	
	//1910년도 보다 작으면 실패
	if(y < 1910)
		return false;
	
	//월이 0이하 이거나 12보다 크면 실패
	if(m <= 0 || m > 12)
		return false;
	
	//일이 0이하 이거나 31보다 크면 실패
	if(d <= 0 || d > 31)
		return false;
	
	//윤년일때
	if(tms_is_leaf(y)){
		//윤달일때
		if(m == 2){
			if(d > m_day[m - 1] + 1)//29일보다 크면 실패
				return false;
		}else{
			if(d > m_day[m - 1]){
				return false;
			}
		}
	}else{
		if(d > m_day[m - 1]){
			return false;
		}
	}
	
	return true;
}
}
//윤년 여부 검사함수
if(typeof(tms_is_leaf) != 'function'){
function tms_is_leaf(year){
	var leaf = false;
	if(year % 4 == 0){
		leaf = true;
		
		if(year % 100 == 0){
			leaf = false;
		}
		
		if(year % 400 == 0){
			leaf = false;
		}
	}
	
	return leaf;
}
}
```
### adm/_z01/js/tms_timepicker.js
```js
var tms_timeArr = [
	['00:00','00:00','오전']
	,['00:30','00:30','오전']
	,['01:00','01:00','오전']
	,['01:30','01:30','오전']
	,['02:00','02:00','오전']
	,['02:30','02:30','오전']
	,['03:00','03:00','오전']
	,['03:30','03:30','오전']
	,['04:00','04:00','오전']
	,['04:30','04:30','오전']
	,['05:00','05:00','오전']
	,['05:30','05:30','오전']
	,['06:00','06:00','오전']
	,['06:30','06:30','오전']
	,['07:00','07:00','오전']
	,['07:30','07:30','오전']
	,['08:00','08:00','오전']
	,['08:30','08:30','오전']
	,['09:00','09:00','오전']
	,['09:30','09:30','오전']
	,['10:00','10:00','오전']
	,['10:30','10:30','오전']
	,['11:00','11:00','오전']
	,['11:30','11:30','오전']
	,['12:00','12:00','오후']
	,['12:30','12:30','오후']
	,['13:00','01:00','오후']
	,['13:30','01:30','오후']
	,['14:00','02:00','오후']
	,['14:30','02:30','오후']
	,['15:00','03:00','오후']
	,['15:30','03:30','오후']
	,['16:00','04:00','오후']
	,['16:30','04:30','오후']
	,['17:00','05:00','오후']
	,['17:30','05:30','오후']
	,['18:00','06:00','오후']
	,['18:30','06:30','오후']
	,['19:00','07:00','오후']
	,['19:30','07:30','오후']
	,['20:00','08:00','오후']
	,['20:30','08:30','오후']
	,['21:00','09:00','오후']
	,['21:30','09:30','오후']
	,['22:00','10:00','오후']
	,['22:30','10:30','오후']
	,['23:00','11:00','오후']
	,['23:30','11:30','오후']
];
/*
인수설명
1번 인수 : 타겟 select객체
2번 인수 : 24시간 타입인가? 12시간 타입인가?[기본 24]
3번 인수 : 시간범위에서 시작시간 (0~23) [기본 0]
4번 인수 : 시간범위에서 종료시간 (0~23) [기본 23]
*/
function timePicker(object,v_type,f_time,t_time){
	var obj = object;
	var val = obj.attr('val');
	var tp = 0;
	var apm = 2;
	var opts = '';
	var relArr = new Array();
	var indexFrom = 0;
	var indexTo = 0;
	var view_type = (v_type == 12) ? v_type : 24;
	var from_time = (f_time > 0) ? f_time : 0;
	var to_time = (t_time < 23) ? t_time : 23;
	
	if(from_time > to_time){
		alert('시작시간이 종료시간보다 클수는 없습니다.');
		return false;
	}
	
	if(view_type == '12'){
		tp = 1;
	}else{
		tp = 0;
	}
	
	indexFrom = fromTimeIndex(from_time);
	indexTo = toTimeIndex(to_time);
	for(var j = indexFrom; j<=indexTo; j++){
		//relArr.push(tms_timeArr[j]);
		//console.log(tms_timeArr[j]);
		opts += "<option value='"+tms_timeArr[j][0]+"'"+((val == tms_timeArr[j][0])?' selected="selected"':'')+">"+tms_timeArr[j][tp]+((tp) ? "("+tms_timeArr[j][2]+")" : "")+"</option>";
	}
	
	obj.html(opts).addClass('bwg_time');
}

function fromTimeIndex(f){
	var idx = 0;
	for(var i in tms_timeArr){
		if(Number(tms_timeArr[i][0].substring(0,2)) == f){
			idx = i;
			break;
		}
	}
	
	return idx;
}

function toTimeIndex(t){
	var idx = tms_timeArr.length - 1;
	for(var i=idx; i>=0; i--){
		if(Number(tms_timeArr[i][0].substring(0,2)) == t){
			idx = i;
			break;
		}
	}
	
	return idx;
}
```
### adm/_z01/js/widget_form.js.php
```php
<script>
const w = '<?=$w?>';
const bwgs_idx = '<?=$wgt_idx?>';
let skin = '<?=$wgt_skin?>';

timePicker($('.wgt_start_time'),12);
timePicker($('.wgt_end_time'),12);

$(function(){
	$('.dt_cancel').on('click',function(){
		$(this).siblings('input').val('0000-00-00');
		$(this).siblings('select').val('00:00');
	});
	
	// eventOn();
	$(document).on('change','.wgt_start_date, .wgt_start_time, .wgt_end_date, .wgt_end_time',function(e){
		e.stopPropagation();
		e.preventDefault();
		var inputdate = null;
		var inputtime = null;
		var inputdatetime = $(this).parent().siblings('.datetime');
		if($(this).hasClass('bwg_dt')){//현재 날짜입력일때
			inputdate = $(this);
			inputtime = $(this).siblings('.bwg_time');
			if(inputdate.val()){
				if(tms_dt_valid(inputdate.val())){//날짜 입력값이 올바르면
					inputdatetime_val = inputdate.val()+' '+inputtime.val()+':00';
				}else{ //날짜입력이잘못되었으면
					alert('올바른 날짜입력이 아닙니다.');
					inputdatetime_val = '';
					inputtime.find('option').attr('selected',false);
					inputtime.find('option[value="00:00"]').attr('selected',true);
					inputdate.val('').focus();
				}
			}else{
				inputdatetime_val = '';
				inputtime.find('option').attr('selected',false);
				inputtime.find('option[value="00:00"]').attr('selected',true);
			}
			inputdatetime.val(inputdatetime_val);
		}else if($(this).hasClass('bwg_time')){ //현재 시간입력일때
			inputdate = $(this).siblings('.bwg_dt');
			inputtime = $(this);
			//만약 날짜에 입력이 없으면 날짜부터 입력하라는 경고창을 표시한다.
			if(!inputdate.val()){//시간 앞 날짜입력에 값이 없으면
				alert('날짜부터 입력하세요');
				inputtime.find('option').attr('selected',false);
				inputtime.find('option[value="00:00"]').attr('selected',true);
				inputdate.focus();
				return false;
			}else{ //시간 앞 날짜입력에 값이 있으면
				inputdatetime.val(inputdate.val()+' '+inputtime.val()+':00');
			}
		}
	});
	if(w == 'u'){
		widget_skin_select(device,skin,bwgs_idx);
	}else{
		widget_skin_select(device);
	}
});

//위젯 위치코드 입력시 중복여부를 체크하는 함수
function widget_code_repetition_check(wgt_code){
	if(wgt_code == ''){
		$('#wgt_code_chk').text('');
		return false;
	}
	$.ajax({
		type:"POST",
		url:"<?=G5_Z_URL?>/ajax/widget_code_repetition_check.php",
		dataType:"text",
		data:{'wgt_code':wgt_code},
		success:function(response){
			if(response){
				response = Number(response);
				if(response != 0){
					$('#wgt_code_chk').text('입력불가').attr('state',0).css('color','red');
				}
				else
					$('#wgt_code_chk').text('입력가능').attr('state',1).css('color','blue');
			}
		},
		error:function(e){
			alert(e.responseText);
		}
	});
}


//위젯을 표시하는 스킨 선택박스를 호출하는 함수
function widget_skin_select(checkskin,wgt_idx){
	var category = $('#wgt_category').val();
	
	$.ajax({
		type:"POST",
		url:"<?=G5_Z_URL?>/ajax/widget_skin_select.php",
		dataType:"html",
		data:{'w':w, 'category':category, 'skin':checkskin, 'wgt_idx':wgt_idx},
		success:function(response){
			$('#td_widget_skin').html(response);
			//console.log($('select[name=bwgs_skin]').val() != null);
			if(w != '') call_skin_config(devc,checkskin,w,bwgs_idx);

			//안에 스킨 선택 요소가 존재하면 스킨선택 모달창에 목록을 셋팅한다.
			if($('#td_widget_skin').find('select').find('option').length > 0){
				skin_select_modal_setting($('#td_widget_skin').find('select'));
			}
			// eventOn();
			//alert($('select[name=bwgs_skin]').length);
		},
		error:function(e){
			alert(e.responseText);
		}
	});
}

//모달창에 스킨선탠 목록을 셋팅하는 함수 
function skin_select_modal_setting(selectObj){
	$('#skin_select_title').text('');
	$('#skin_select_con').empty();
	$('#skin_select_title').text($('#wgt_skin').attr('device').toUpperCase()+'위젯 스킨선택');
	selectObj.find('option').each(function(){
		var selected = ($(this).is(':selected')) ? ' selected' : '';
		$('<div class="skin_lst'+selected+'" value="'+($(this).text() != '사용안함' ? $(this).text() : '')+'"><img src="'+$(this).attr('thumb')+'"><div class="skin_name">'+$(this).text()+'</div></div>').appendTo('#skin_select_con');
	});
}
</script>
```
### adm/_z01/lib/_common.php
```php
<?php
include_once ('../../../common.php');

//-- REQUEST 변수 재정의 (변수명이 너무 길어~) --//
while( list($key, $val) = each($_REQUEST) ) {
	${$key} = $_REQUEST[$key];
//	echo $_REQUEST[$key].'<br>';
}
```
### adm/_z01/lib/download.php
```php
<?php
include_once('./_common.php');

// clean the output buffer
ob_end_clean();

//-- 파일경로 및 다운로드할 파일명 두개 변수 필요함
//-- http://file1.ddmc.kr/download.php?file_fullpath=/data/pgroup/3531011152_vXxCy4WP_0124_05.JPG&file_name_orig=0124_05.JPG
if(!$file_fullpath)
	alert('파일 경로가 없습니다.');
        
if(!$file_name_orig)
	alert('파일 이름이 없습니다.');

$filepath = $file_fullpath;
$filepath = preg_replace("/\s+/", "+", $filepath); // 파일명에 공백이 들어가는 경우가 있어서 공백=>+기호로 강제치환함
$filepath = addslashes($filepath);
if (!is_file($filepath) || !file_exists($filepath))
    alert('파일이 존재하지 않습니다.');

//$original = urlencode($file['bf_source']);
$original = iconv('utf-8', 'euc-kr', $file_name_orig); // SIR 잉끼님 제안코드
if(!$original)
	$original = $file_name_orig;

//if(preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) && preg_match("/5\.5/", $_SERVER['HTTP_USER_AGENT'])) {
//	header("Cache-control: private");  //<---- 이부분 추가 
//    header("content-type: doesn/matter");
//    header("content-length: ".filesize("$filepath"));
//    header("content-disposition: attachment; filename=\"$original\"");
//    header("content-transfer-encoding: binary");
//} else {
//    header("content-type: file/unknown");
//    header("content-length: ".filesize("$filepath"));
//    header("content-disposition: attachment; filename=\"$original\"");
//    header("content-description: php generated data");
//}
//header("pragma: no-cache");
//header("expires: 0");
//flush();


// Must be fresh start 
if( headers_sent() ) 
  die('Headers Already Sent'); 

// Required for some browsers 
if(ini_get('zlib.output_compression')) 
  ini_set('zlib.output_compression', 'Off'); 

// Parse Info / Get Extension 
$fsize = filesize($filepath); 
$path_parts = pathinfo($filepath); 
$ext = strtolower($path_parts["extension"]); 

// Determine Content Type 
switch ($ext) 
{ 
  case "pdf": $ctype="application/pdf"; break; 
  case "exe": $ctype="application/octet-stream"; break; 
  case "zip": $ctype="application/zip"; break; 
  case "doc": $ctype="application/msword"; break; 
  case "xls": $ctype="application/vnd.ms-excel"; break; 
  case "ppt": $ctype="application/vnd.ms-powerpoint"; break; 
  case "gif": $ctype="image/gif"; break; 
  case "png": $ctype="image/png"; break; 
  case "jpeg": 
  case "jpg": $ctype="image/jpg"; break; 
  default: $ctype="application/force-download"; 
} 

header("Pragma: public"); // required 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("Cache-Control: private",false); // required for certain browsers 
header("Content-Type: $ctype"); 
header("Content-Disposition: attachment; filename=\"".$original."\";" ); 
header("Content-Transfer-Encoding: binary"); 
header("Content-Length: ".$fsize); 
ob_clean(); 
flush(); 



$fp = fopen($filepath, 'rb');

$download_rate = 10;

while(!feof($fp)) {
    print fread($fp, round($download_rate * 1024));
    flush();
    usleep(1000);
}
fclose ($fp);
flush();
?>
```
### adm/_z01/modal/widget_modal.php
```php
<!--스킨 선택 모달창-->
<div id="skin_select_modal">
	<div id="skin_select_tbl">
		<div id="skin_select_td">
			<div id="skin_select_bg"></div>
			<div id="skin_select_box">
				<h3 id="skin_select_title"></h3>
				<img id="skin_select_modal_close" src="<?=G5_Z_URL?>/img/close.png">
				<div id="skin_select_con"></div>
			</div>
		</div>
	</div>
</div>
<!-- 콘텐츠 개별 이미지 등록 모달창-->
<div id="confile_reg_modal">
	<div id="confile_reg_tbl">
		<div id="confile_reg_td">
			<div id="confile_reg_bg"></div>
			<div id="confile_reg_box">
				<h3 id="confile_reg_title">콘텐츠 개별 이미지 등록</h3>
				<img id="confile_reg_modal_close" src="<?=G5_Z_URL?>/img/close.png">
				<div id="confile_reg_con">
					<form name="confileregform" id="confileregform" action="<?=G5_Z_URL?>/widget_content_file_register_update.php" onsubmit="return confilereg_check(this);" method="post" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="bwgs_idx" value="">
						<input type="hidden" name="bwgc_idx" value="">
						<input type="hidden" name="bwga_type" value="content">
						<div id="cur_img">
							<div id="file_box">
								<input type="file" name="bwcfile" id="bwcfile" multiple class="with-preview" maxlength="1" accept="png|jpg|gif|svg" data-maxfile="<?=$g5['bpwidget']['bwgf_filesize']?>">
							</div>
							<table class="ftbl">
								<tbody>
									<tr>
										<th>제목</th>
										<td colspan="5"><input type="text" name="bwga_title" class="ftxt" value=""></td>
									</tr>
									<tr>
										<th>랭크</th>
										<td><input type="text" name="bwga_rank" class="ftxt" value=""></td>
										<th>순서</th>
										<td><input type="text" name="bwga_sort" class="ftxt" value=""></td>
										<th>상태</th>
										<td>
											<select name="bwga_status" class="fselect">
												<option value="ok">사용</option>
												<option value="pending">대기</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>내용</th>
										<td colspan="5">
											<textarea name="bwga_content" class="ftxtarea"></textarea>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="modal_btn">
							<input type="submit" class="btn_submit btn" value="확인">
							<input type="button" class="confile_reg_close btn_close btn" value="창닫기">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!--개별 이미지 변경 모달창-->
<div id="img_change_modal">
	<div id="img_change_tbl">
		<div id="img_change_td">
			<div id="img_change_bg"></div>
			<div id="img_change_box">
				<h3 id="img_change_title">개별 이미지 변경</h3>
				<img id="img_change_modal_close" src="<?=G5_Z_URL?>/img/close.png">
				<div id="img_change_con">
					<form name="filechangeform" id="filechangeform" action="<?=G5_Z_URL?>/widget_file_change_update.php" onsubmit="return filechange_check(this);" method="post" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="bwgs_idx" value="">
						<input type="hidden" name="bwgc_idx" value="">
						<input type="hidden" name="bwga_idx" value="">
						<div id="cur_img">
							<div class="cur_img_box"></div>
							<table class="ftbl">
								<tbody>
									<tr>
										<th>제목</th>
										<td colspan="5"><input type="text" name="bwga_title" class="ftxt" value=""></td>
									</tr>
									<tr>
										<th>랭크</th>
										<td><input type="text" name="bwga_rank" class="ftxt" value=""></td>
										<th>순서</th>
										<td><input type="text" name="bwga_sort" class="ftxt" value=""></td>
										<th>상태</th>
										<td>
											<select name="bwga_status" class="fselect">
												<option value="ok">사용</option>
												<option value="pending">대기</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>내용</th>
										<td colspan="5">
											<textarea name="bwga_content" class="ftxtarea"></textarea>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div id="file_box">
							<input type="file" name="filechange" id="filechange" multiple class="with-preview" maxlength="1" accept="png|jpg|gif|svg" data-maxfile="<?=$g5['bpwidget']['bwgf_filesize']?>">
						</div>
						<div class="modal_btn">
							<input type="submit" class="btn_submit btn" value="확인">
							<input type="button" class="img_change_close btn_close btn" value="창닫기">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
```