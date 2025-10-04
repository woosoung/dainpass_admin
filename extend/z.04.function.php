<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// AWS SDK autoloader: prefer Composer vendor (only if AWS manifest exists), fallback to legacy lib/aws (also requires manifest)
do {
    $AWS_SDK_READY = false;

    // 1) Composer 설치 사용: /vendor/autoload.php (단, aws sdk가 온전하게 포함되어 있어야 함)
    if (defined('G5_VENDOR_PATH')) {
        $composer_autoload = G5_VENDOR_PATH . '/autoload.php';
        $vendor_manifest   = G5_VENDOR_PATH . '/aws/aws-sdk-php/src/data/manifest.json';
        if (is_file($composer_autoload) && is_file($vendor_manifest)) {
            require_once $composer_autoload;
            $AWS_SDK_READY = class_exists('Aws\\S3\\S3Client');
            break;
        }
    }

    // 2) 예전 경로 사용: /lib/aws/autoload.php (프로젝트에 동봉된 레거시 SDK)
    if (defined('G5_LIB_PATH')) {
        $legacy_autoload  = G5_LIB_PATH . '/aws/autoload.php';
        // 레거시 SDK도 manifest.json이 존재해야 정상 동작함
        $legacy_manifest  = G5_LIB_PATH . '/aws/aws/aws-sdk-php/src/data/manifest.json';
        if (is_file($legacy_autoload) && is_file($legacy_manifest)) {
            require_once $legacy_autoload;
            $AWS_SDK_READY = class_exists('Aws\\S3\\S3Client');
            break;
        }
    }
} while (false);

if (!defined('AWS_SDK_READY')) {
    define('AWS_SDK_READY', !empty($AWS_SDK_READY));
}

// AWS 클래스는 FQCN으로 직접 참조하여 use 위치 제약을 피함


// DB 연결
if(!function_exists('sql_connect_pg')){
function sql_connect_pg($host, $user, $pass, $db=G5_PGSQL_DB)
{
    $port = 5432; // 기본 포트
    if (strpos($host, ':') !== false) {
        list($host, $port) = explode(':', $host, 2);
    }
    $pg_link = @pg_connect(" host = $host port = $port dbname = $db user = $user password = $pass ") or die('PgSQL Host, User, Password, DB 정보에 오류가 있습니다.');
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


// if(!function_exists('sql_query_pg')){
// function sql_query_pg($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null)
// {
//     global $conf_com_idx, $g5;
    
//     if(!$link)
//         $link = $g5['connect_pg'];

//     // Blind SQL Injection 취약점 해결
//     $sql = trim($sql);

//     if ($error) {
//         $result = pg_query($link, $sql) or die("<p>$sql</p> <p>error file : {$_SERVER['SCRIPT_NAME']}</p>");
//     } else {
//         try {
//             $result = @pg_query($link, $sql);
//         } catch (Exception $e) {
//             $result = null;
//         }
//     }

//     return $result;
// }
// }

// --- 1) 래퍼 클래스 정의 ---
if (!class_exists('PGSQLResultWrapper')) {
    class PGSQLResultWrapper {
        /** @var resource|\PgSql\Result 실제 pg_query 결과 */
        public $result;

        /** @var int 현재 필드 인덱스(호환용) */
        public $current_field = 0;

        /** @var int 필드 수 */
        public $field_count = 0;

        /** @var array<int,int>|null 현재 로우의 각 컬럼 길이(호환용) */
        public $lengths = null;

        /** @var int 결과 행 수 */
        public $num_rows = 0;

        /** @var int 타입(호환용, 고정 0) */
        public $type = 0;

        public function __construct($pg_result) {
            $this->result       = $pg_result;
            $this->field_count  = @pg_num_fields($pg_result) ?: 0;
            $this->num_rows     = @pg_num_rows($pg_result) ?: 0;
            // $this->lengths 는 현재 로우를 fetch한 뒤에만 의미가 있어 기본 null 유지
        }
    }
}

// --- 2) 래퍼 판별 유틸 ---
if (!function_exists('is_pg_wrapper')) {
function is_pg_wrapper($res) {
    return ($res instanceof PGSQLResultWrapper);
}
}

// --- 3) sql_query_pg : 래퍼를 반환하도록 변경 ---
if (!function_exists('sql_query_pg')) {
function sql_query_pg($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null)
{
    global $g5;

    if(!$link)
        $link = $g5['connect_pg'];

    // Blind SQL Injection 취약점 최소화
    $sql = trim($sql);

    if ($error) {
        $raw = @pg_query($link, $sql) or die("<p>$sql</p> <p>error file : {$_SERVER['SCRIPT_NAME']}</p>");
    } else {
        try {
            $raw = @pg_query($link, $sql);
        } catch (Exception $e) {
            $raw = null;
        }
    }

    // SELECT/SHOW 등 결과셋이 있는 경우만 래퍼 생성
    if ($raw) {
        return new PGSQLResultWrapper($raw);
    }

    // INSERT/UPDATE/DELETE 등 영향만 있는 경우에는 그대로 null/false 반환
    return $raw;
}
}

// --- 6) (선택) 메타만 필요할 때 꺼내보는 헬퍼 ---
if (!function_exists('sql_result_meta_pg')) {
function sql_result_meta_pg($result)
{
    if (is_pg_wrapper($result)) {
        return [
            'current_field' => $result->current_field,
            'field_count'   => $result->field_count,
            'lengths'       => $result->lengths,
            'num_rows'      => $result->num_rows,
            'type'          => $result->type,
        ];
    } elseif ($result) {
        return [
            'current_field' => 0,
            'field_count'   => @pg_num_fields($result) ?: 0,
            'lengths'       => null,
            'num_rows'      => @pg_num_rows($result) ?: 0,
            'type'          => 0,
        ];
    }
    return null;
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
    global $conf_com_idx, $g5;

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
    global $conf_com_idx, $g5;

    if(!$link)
        $link = $g5['connect_pg'];

    $columns = array();

    $sql = "SELECT column_name, data_type, character_maximum_length
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = '".$table."'
	";
    $result = sql_query_pg($sql,1);
	while($field = sql_fetch_array_pg($result->result)) {
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
    global $conf_com_idx, $g5;

    if(!$link)
        $link = $g5['connect_pg'];

    $result = sql_query_pg($sql, $error, $link);
    $row = sql_fetch_array_pg($result->result);
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
    // global $g5;// global $conf_com_idx, $db;

	if(!$db_table||!$db_field||!$db_id)
		return false;
    
    $table_name = $db_table;
    $sql = " SELECT ".$db_fields." FROM ".$table_name." WHERE ".$db_field." = '".$db_id."' LIMIT 1 ";
    $row = sql_fetch_pg($sql);
    return $row;
}
}

// 기본 디비 배열 + 확장 meta 배열
// get_table('g5_shop_item','it_id',215021535,'it_name')	// 4번째 매개변수는 테이블명과 같으면 생략할 수 있다.
if(!function_exists('get_table')){
function get_table($db_table,$db_field,$db_id,$db_fields='*')
{
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
    
    $sql = " SELECT ".$db_fields." FROM ".$table_name." WHERE ".$db_field." = '".$db_id."' LIMIT 1 ";
    //print_r3($sql);
    //echo $sql.'<br>';
    $row = sql_fetch($sql);

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
    global $conf_com_idx, $g5;

    if (!$link)
        $link = $g5['connect_pg'];

    // pg_escape_string에 연결 리소스 명시적으로 전달
    $escaped_table = pg_escape_string($link, $table);

    $sql = "
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = 'public'
          AND table_name = '$escaped_table'
    ";

    $result = pg_query($link, $sql);
    return $result && pg_num_rows($result) > 0;
}
}

/***********************************/
//--PgSQL 관련 함수 모음 : 여기까지
/***********************************/

if(!function_exists('pg_setting_check2')){
function pg_setting_check2($is_print=false){
	global $g5, $config,$default, $default2, $member;

	$msg = '';
	$pg_msg = '';

	if( $default2['de_card_test'] ){
		if( $default2['de_pg_service'] === 'kcp' && $default2['de_kcp_mid'] && $default2['de_kcp_site_key'] ){
			$pg_msg = 'NHN KCP';
		} else if ( $default2['de_pg_service'] === 'lg' && $default2['de_lg_mid'] && $default2['de_lg_mert_key'] ){
			$pg_msg = '토스페이먼츠';
		} else if ( $default2['de_pg_service'] === 'inicis' && $default2['de_inicis_mid'] && isset($default2['de_inicis_sign_key']) ){
			$pg_msg = 'KG이니시스';
		} else if ( $default2['de_pg_service'] === 'nicepay' && $default2['de_nicepay_mid'] && $default2['de_nicepay_key'] ){
			$pg_msg = 'NICEPAY';
		}
	}

    if( function_exists('is_use_easypay') && is_use_easypay('global_nhnkcp') ){
        if(!extension_loaded('soap') || !class_exists('SOAPClient')) {
            $msg .= '<script>'.PHP_EOL;
            $msg .= 'alert("PHP SOAP 확장모듈이 설치되어 있지 않습니다.\n모바일 쇼핑몰 결제 때 사용되오니 SOAP 확장 모듈을 설치하여 주십시오.\nNHN_KCP (네이버페이) 모바일결제가 되지 않습니다.");'.PHP_EOL;
            $msg .= '</script>'.PHP_EOL;
        }
    }

	if( $pg_msg ){
		$pg_test_conf_link = G5_ZSHOP_ADMIN_URL.'/configform.php#de_card_test1';
		$msg .= '<div class="admin_pg_notice od_test_caution">(주의!) '.$pg_msg.' 결제의 결제 설정이 현재 테스트결제 로 되어 있습니다.<br>테스트결제시 실제 결제가 되지 않으므로, 쇼핑몰 운영중이면 반드시 실결제로 설정하여 운영하셔야 합니다.<br>아래 링크를 클릭하여 실결제로 설정하여 운영해 주세요.<br><a href="'.$pg_test_conf_link.'" class="pg_test_conf_link">'.$pg_test_conf_link.'</a></div>';
	}
	
	if( $is_print ){
		echo $msg;
	} else{
		return $msg;
	}
}
}

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
// if(!function_exists('gmailer')){
// function gmailer($to, $subject, $content, $type=1)
// {
//     global $conf_com_idx, $config;
//     global $conf_com_idx, $g5;
//     // 메일발송 사용을 하지 않는다면
//     if (!$config['cf_email_use']) {
//         return;
//     }
//     if ($type != 1) {
//         $content = nl2br($content);
//     }
//     include_once(G5_PHPMAILER_PATH.'/PHPMailerAutoload.php');
//     $mail = new PHPMailer(); // defaults to using php "mail()"
//     if (defined('G5_SMTP') && G5_SMTP) {
//         $mail->isSMTP();
//         $mail->SMTPAuth = true;
//         $mail->SMTPSecure = "ssl";
//         $mail->Host = "smtp.gmail.com";
//         $mail->Port = 465;
//         $mail->Username = "tomasjoa21"; //사용할 지메일 계정
//         $mail->Password = "jemi0210!@#"; //구글계정 패스워드
//     }
//     $mail->CharSet = 'UTF-8';
//     $mail->From = "tomasjoa21@gmail.com"; //발송메일(=사용할 지메일 계정)
//     $mail->FromName = "다인패스"; // 메일발송자명
//     $mail->Subject = $subject;
//     $mail->AltBody = ""; // optional, comment out and test
//     $mail->msgHTML($content);
//     $mail->addAddress($to);
//     return $mail->send();
// }
// }

// 기본 디비 배열 + 확장 meta 배열
// get_table_meta('g5_shop_item','it_id',215021535,'shop_item')	// 4번째 매개변수는 테이블명과 같으면 생략할 수 있다.
if(!function_exists('get_table_meta')){
function get_table_meta($db_table,$db_field,$db_id,$db_table2=''){
    global $conf_com_idx, $g5;
    
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
if(!function_exists('get_table_meta_pg')){
function get_table_meta_pg($db_table,$db_field,$db_id,$db_table2=''){
    global $conf_com_idx, $g5;
    
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
    global $conf_com_idx, $g5;
    
    // 필수 키 보강 및 기본값 설정
    $meta_array = array_merge([
        'mta_db_tbl' => '',
        'mta_db_idx' => '',
        'mta_key'    => '',
        'mta_value'  => '',
        'mta_title'  => '',
    ], (array)$meta_array);

    if ($meta_array['mta_key'] === '')
        return 0;

    // 안전 이스케이프 헬퍼
    $pg = isset($g5['connect_pg']) && (is_object($g5['connect_pg']) || is_resource($g5['connect_pg'])) ? $g5['connect_pg'] : null;
    $esc = function($v) use ($pg) {
        $s = (string)$v;
        return $pg ? pg_escape_string($pg, $s) : $s;
    };

    // 각 필드 이스케이프
    $db_tbl = $esc($meta_array['mta_db_tbl']);
    $db_idx = $esc($meta_array['mta_db_idx']);
    $m_key  = $esc($meta_array['mta_key']);
    $m_val  = $esc($meta_array['mta_value']);
    $m_tit  = $esc($meta_array['mta_title']);

    // 존재 여부 확인
    $row1 = sql_fetch_pg("\tSELECT * FROM {$g5['meta_table']} 
                            WHERE mta_db_tbl='".$db_tbl."' 
                              AND mta_db_idx='".$db_idx."' 
                              AND mta_key='".$m_key."' ");
    $row1 = is_array($row1) ? $row1 : [];
    $mta_idx = isset($row1['mta_idx']) ? $row1['mta_idx'] : null;

    if ($mta_idx) {
        // 업데이트
        $sql = " UPDATE {$g5['meta_table']} SET 
                    mta_value='".$m_val."',
                    mta_update_dt='".G5_TIME_YMDHIS."' 
                 WHERE mta_idx='".$mta_idx."' ";
        sql_query_pg($sql);
    } else {
        // 삽입 (mta_title 기본값 허용)
        $sql = " INSERT INTO {$g5['meta_table']} (
                    mta_db_tbl,
                    mta_db_idx,
                    mta_key,
                    mta_value,
                    mta_title,
                    mta_reg_dt
                 ) VALUES (
                    '".$db_tbl."',
                    '".$db_idx."',
                    '".$m_key."',
                    '".$m_val."',
                    '".$m_tit."',
                    '".G5_TIME_YMDHIS."'
                 ) ";
        sql_query_pg($sql);
        $row1['mta_idx'] = sql_insert_id_pg($g5['meta_table']);
    }
    return $mta_idx ?: 0;
}
}

// 확장 메타값 배열로 반환하는 함수
// serialized 되었다면 각 항목별로 분리해서 배열로 만듦
if(!function_exists('get_meta')){
function get_meta($db_table,$db_id,$code64=1)
{
    global $conf_com_idx, $g5;

    if(!$db_table||!$db_id)
        return false;

    $mta2 = []; // 빈 배열로 초기화

    $sql = " SELECT mta_key, mta_value FROM {$g5['meta_table']} WHERE mta_db_tbl = '".$db_table."' AND mta_db_idx = '".$db_id."' ";
    // echo $sql.'<br>';exit;
    $rs = sql_query_pg($sql);
    
    for($i=0;$row=sql_fetch_array_pg($rs->result);$i++) {
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
    global $conf_com_idx, $g5;
    $fr = sql_fetch_pg(" SELECT GROUP_CONCAT(fle_idx) AS fle_idxs FROM {$g5['dain_file_table']} WHERE fle_db_tbl = '{$fle_db_tbl}' AND fle_db_idx = '{$fle_db_idx}' AND fle_type = '{$fle_type}' ");
    if($fr['fle_idxs']) {
        $fle_idx_array = explode(',',$fr['fle_idxs']);
        delete_idx_file($fle_idx_array);
    }
}
}


// fle_db_tbl, fle_db_idx, fle_type 으로 파일삭제하기
if(!function_exists('delete_db_s3_file')) {
function delete_db_s3_file($fle_db_tbl='',$fle_db_idx='',$fle_type=''){
    global $conf_com_idx, $g5;
    $sql = " SELECT string_agg(fle_idx::text, ',') AS fle_idxs
                FROM {$g5['dain_file_table']}
                WHERE fle_db_tbl = '{$fle_db_tbl}'
                AND fle_db_idx = '{$fle_db_idx}'
                AND fle_type = '{$fle_type}'
    ";
    $fr = sql_fetch_pg($sql);
    if($fr['fle_idxs']) {
        $fle_idx_array = explode(',',$fr['fle_idxs']);
        delete_idx_s3_file($fle_idx_array);
    }
}
}


//fle_idx로 파일삭제하기
if(!function_exists('delete_idx_file')) {
function delete_idx_file($fle_idx_array=array()) {
    global $conf_com_idx, $g5;
    //print_r2($fle_idx_array);
    foreach($fle_idx_array as $k=>$v) {
        $fr = sql_fetch_pg(" SELECT fle_path, fle_name FROM {$g5['dain_file_table']} WHERE fle_idx = '{$v}' ");
        @unlink(G5_DATA_PATH.$fr['fle_path'].'/'.$fr['fle_name']);
        delete_ndr_file_thumbnail($fr['fle_path'], $fr['fle_name']);
        sql_query_pg(" DELETE FROM {$g5['dain_file_table']} WHERE fle_idx = '{$v}' ");
    }
}
}



//fle_idx로 파일삭제하기
if (!function_exists('delete_idx_s3_file')) {
function delete_idx_s3_file($fle_idx_array = array()) {
    global $set_conf, $conf_com_idx, $g5;

    if (!AWS_SDK_READY) {
        error_log('AWS SDK not ready: skip delete_idx_s3_file');
        return false;
    }

    // AWS S3 클라이언트 초기화
    $s3 = new \Aws\S3\S3Client([
        'version' => 'latest',
        'region'  => $set_conf['set_aws_region'],
        'credentials' => [
            'key'    => $set_conf['set_s3_accesskey'],
            'secret' => $set_conf['set_s3_secretaccesskey'],
        ]
    ]);
    $bucket = $set_conf['set_aws_bucket'];

    foreach ($fle_idx_array as $v) {
        $fr = sql_fetch_pg("SELECT fle_path, fle_name FROM {$g5['dain_file_table']} WHERE fle_idx = '{$v}' ");
        if (!$fr) continue;

        $key = trim($fr['fle_path'], '/');

        // S3 원본 파일 삭제
        try {
            $s3->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $key
            ]);
        } catch (\Aws\Exception\AwsException $e) {
            error_log("S3 Delete Error (original): " . $e->getMessage());
        }


        // PostgreSQL에서 메타데이터 삭제
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
function upload_multi_file($_files=array(),$tbl='',$idx=0,$fle_dir='',$fle_type=''){
    global $conf_com_idx, $g5, $config, $member;
    // echo 'tbl='.$tbl.', $idx='.$idx.', fle_dir='.$fle_dir.', fle_type='.$fle_type;exit;
    // 해당 파일이 이미지파일이면 width, height를 구해야 한다.
    $f_flag = (!count($_files['name']) || !$_files['name'][0]) ? false : true;
    if($f_flag){
        for($i=0;$i<count($_files['name']);$i++) {
            if ($_files['name'][$i]) {
                // 해당 파일이 이미지파일이면 width, height를 구해야 한다.
                if( strpos($_files['type'][$i], 'image/') === 0) {
                    $img_info = getimagesize($_files['tmp_name'][$i]);
                    $width = $img_info[0];
                    $height = $img_info[1];
                } else {
                    $width = 0;
                    $height = 0;
                }
                $upfile_info = upload_insert_file(array(
                                    "fle_mb_id"=>$member['mb_id']
                                    ,"fle_name"=>$_files['tmp_name'][$i]
                                    ,"fle_name_orig"=>$_files['name'][$i]
                                    ,"fle_mime_type"=>$_files['type'][$i]
                                    ,"fle_width"=>$width
                                    ,"fle_height"=>$height
                                    ,"fle_size"=>$_files['size'][$i]
                                    ,"fle_desc"=>''
                                    ,"fle_path"=>''		//<---- 저장 디렉토리
                                    ,"fle_db_tbl"=>$tbl
                                    ,"fle_db_idx"=>$idx
                                    ,"fle_type"=>$fle_type
                                    ,"fle_dir"=>$fle_dir
                                    ,"fle_sort"=>$i
                ));
                //print_r2($upfile_info);
            }
        }
    }//if($f_flag)
}
}
//https://dainpass-prod-file.s3.ap-northeast-2.amazonaws.com/data/
// Post File 업로드 함수
//설정 변수: fle_mb_id, fle_name, fle_name_orig, fle_mime_type, fle_path, fle_db_tbl, fle_db_idx, fle_sort ....
if(!function_exists('upload_insert_file')){
function upload_insert_file($fle_array){
    global $conf_com_idx, $g5,$config,$member;

    //-- 원본 파일명이 없으면 리턴
    if($fle_array['fle_name_orig'] == "")
        return false;

    //-- 파일명 재설정, 한글인 경우는 변경
    $fle_array['fle_dest_file'] = preg_replace("/\s+/", "", $fle_array['fle_name_orig']);
    $fle_array['fle_dest_file'] = preg_replace("/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/", "", $fle_array['fle_dest_file']);
    $fle_array['fle_dest_file'] = preg_replace_callback(
                                    "/[ㄱ-ㅎ|ㅏ-ㅣ|가-힣]+/",
                                    function($matches) { return base64_encode($matches[0]); },
                                    $fle_array['fle_dest_file']
                                );
    $fle_array['fle_dest_file'] = preg_replace("/\+/", "", $fle_array['fle_dest_file']);	// 한글변환후 + 기호가 있으면 제거해야 함
    $fle_array['fle_dest_file'] = preg_replace("/\//", "", $fle_array['fle_dest_file']);	// 한글변환후 / 기호가 있으면 제거해야 함

    // 상태값이 있으면 업데이트 (키 존재 여부 안전 가드)
    if (isset($fle_array['fle_status']) && $fle_array['fle_status'] !== '') {
        $sql_status = $fle_array['fle_status'];
    } else {
        $sql_status = "ok";
    }

    //-- 파일 업로드 처리
    // $upload_file = upload_common_file($fle_array['fle_name'], $fle_array['fle_dest_file'], $fle_array['fle_path']);
    //-- 파일 업로드 처리 (AWS S3 사용)
    // print_r2($fle_array);
    $upload_file = upload_aws_s3_file($fle_array['fle_name'], $fle_array['fle_dest_file'], $fle_array['fle_dir']);
    if ($upload_file === false || !is_array($upload_file)) {
        // 업로드 실패 시 중단
        return false;
    }
    // print_r2($upload_file);exit;

    // 파일의 mime_type 추출
    if (empty($fle_array['fle_mime_type'])) {
        $fle_array['fle_mime_type'] = @mime_content_type($fle_array['fle_name']);
    }

    $sql = " INSERT INTO {$g5['dain_file_table']} (
            fle_mb_id,
            fle_db_tbl,
            fle_db_idx,
            fle_type,
            fle_dir,
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
            '{$fle_array['fle_dir']}',
            '{$upload_file['key']}',
            '{$upload_file['filename']}',
            '{$fle_array['fle_name_orig']}',
            '{$fle_array['fle_width']}',
            '{$fle_array['fle_height']}',
            '{$fle_array['fle_desc']}',
            '{$fle_array['fle_sort']}',
            '{$fle_array['fle_mime_type']}',
            '{$upload_file['filesize']}',
            '".G5_TIME_YMDHIS."',
            '{$sql_status}'
        )";
    // echo $sql.'<br>';
    // exit;
    sql_query_pg($sql);
    $fle_idx = sql_insert_id_pg($g5['dain_file_table']);

    //$fle_return[0] = $upload_file[0];
    //$fle_return[1] = $upload_file[1];
    //$fle_return[2] = $upload_file[2];
    //$fle_return[3] = $upload_file[3];
    //$fle_return[4] = $pfl['fle_idx'];
    //return $fle_return;
    // S3 업로드 반환 키에 맞춰 리턴 구조 조정
    return array(
        "upfile_name"      => $upload_file['filename'],
        "upfile_width"     => isset($fle_array['fle_width']) ? $fle_array['fle_width'] : 0,
        "upfile_height"    => isset($fle_array['fle_height']) ? $fle_array['fle_height'] : 0,
        "upfile_filesize"  => $upload_file['filesize'],
        "upfile_fle_idx"   => $fle_idx,
        "upfile_fle_sort"  => isset($fle_array['fle_sort']) ? $fle_array['fle_sort'] : 0,
    );
}
}




// 파일을 업로드 함
if (!function_exists('upload_aws_s3_file')) {
/**
 * S3에 파일 업로드 + 썸네일 생성 (옵션 가능)
 *
 * @param string $srcfile   임시 업로드된 원본 파일 경로
 * @param string $destfile  저장할 대상 파일명 (확장자 포함)
 * @param string $dir       S3 내 저장 디렉토리 (예: 'user/123')
 * @param array  $options   썸네일 설정: thumb_width, thumb_height, thumb_crop (bool)
 * @return array|false      [파일명, width, height, filesize, url] 또는 false
 */
function upload_aws_s3_file($srcfile, $destfile, $dir, $options = []) {
    global $set_conf;

    if (!AWS_SDK_READY) {
        error_log('AWS SDK not ready: skip upload_aws_s3_file');
        return false;
    }

    if (empty($destfile) || !is_uploaded_file($srcfile)) return false;

    // AWS SDK 초기화
    $s3 = new \Aws\S3\S3Client([
        'version' => 'latest',
        'region'  => trim($set_conf['set_aws_region']),
        'credentials' => [
            'key'    => trim($set_conf['set_s3_accesskey']),
            'secret' => trim($set_conf['set_s3_secretaccesskey']),
        ]
    ]);

    $bucket = trim($set_conf['set_aws_bucket']);

    // 저장 경로 및 파일명 구성
    $prefix = trim($dir, '/');
    $ext = pathinfo($destfile, PATHINFO_EXTENSION);
    $name = pathinfo($destfile, PATHINFO_FILENAME);
    $unique = uniqid(); // 고유값 추가
    $key = "data/{$prefix}/{$name}_{$unique}.{$ext}";
    
    // MIME 타입 확인
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $srcfile);
    finfo_close($finfo);

    // 업로드 처리
    try {
        $res = $s3->putObject([
            'Bucket'      => $bucket,
            'Key'         => $key,
            'SourceFile'  => $srcfile,
            'ContentType' => $mime_type,
        ]);
    } catch (\Exception $e) {
        error_log("S3 Upload Error: " . $e->getMessage());
        return false;
    }

    return [
        'filename'  => basename($key),
        'filesize'  => filesize($srcfile),
        'url'       => $res['ObjectURL'],
        'key'       => $key,
        'mime_type' => $mime_type,
    ];
}
}


// 파일을 업로드 함
if(!function_exists('is_s3file')){
function is_s3file($key) {
    global $set_conf;

    if (!AWS_SDK_READY) {
        return false;
    }

    $s3 = new \Aws\S3\S3Client([
        'version' => 'latest',
        'region'  => $set_conf['set_aws_region'],
        'credentials' => [
            'key'    => trim($set_conf['set_s3_accesskey']),
            'secret' => trim($set_conf['set_s3_secretaccesskey']),
        ]
    ]);

    try {
        $s3->headObject([
            'Bucket' => trim($set_conf['set_aws_bucket']),
            'Key'    => $key
        ]);
        return true; // 파일이 존재함
    } catch (\Aws\S3\Exception\S3Exception $e) {
        if ($e->getAwsErrorCode() === 'NotFound') {
            return false; // 파일 없음
        } else {
            // 기타 오류 (권한 문제 등)
            error_log("S3 check error: " . $e->getMessage());
            return false;
        }
    }
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
    global $conf_com_idx, $g5,$config;
    
    $set_key = ($set_array['set_key']) ? $set_array['set_key']:'dain';
    $set_auto_yn = (isset($set_array['set_auto_yn']) && $set_array['set_auto_yn'] == 'Y') ? 'Y':'N';
    $set_com_idx = (isset($set_array['set_com_idx']) && $set_array['set_com_idx'] != 0) ? $set_array['set_com_idx']:0;
    $set_trm_idx = (isset($set_array['set_trm_idx']) && $set_array['set_trm_idx'] != 0) ? $set_array['set_trm_idx']:0;
    

    $ssql = " SELECT * FROM {$g5['setting_table']}
                        WHERE set_name='{$set_array['set_name']}'
                            AND set_shop_id = '{$set_com_idx}'
                            AND set_key = '{$set_key}'
                            AND set_type = '{$set_array['set_type']}'
                            AND set_name = '{$set_array['set_name']}' ";
    // echo $ssql;exit;
    $row1 = sql_fetch_pg($ssql);

    if(isset($row1['set_idx'])) {
        $u_sql = " UPDATE {$g5['setting_table']} SET
                            set_value='{$set_array['set_value']}',
                            set_auto_yn='$set_auto_yn'
                        WHERE set_idx='".$row1['set_idx']."' ";
        // echo $u_sql;exit;
        sql_query_pg($u_sql);
    }
    else {
        $i_sql = " INSERT INTO {$g5['setting_table']} (
            set_shop_id,
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
        ) ";
        // echo $i_sql;exit;
        sql_query_pg($i_sql);
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
function loop_time_uniqid($n, $l=2){ //자릿수, 각루프값(주로 $i값)
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
    global $conf_com_idx, $g5;
    $iupclass = ($iup) ? "iup" : 'idown';
    $str = ($help) ? '<div class="tms_info_box"><p class="tms_info '.$iupclass.'" style="background:'.$bgcolor.';color:'.$fontcolor.';">'.str_replace("\n", "<br>", $help).'</p></div>' : '';
    return $str;
}
}

//범위(range) input form 생성 함수
if(!function_exists('tms_input_range')){
function tms_input_range($rname='',$val='1',$w='',$min='0',$max='1',$step='0.1',$width='100',$padding_right=29,$unit=''){
	global $conf_com_idx,$g5,$config,$default,$member,$is_admin;
	
	if(preg_match("/%/", $width)){
		$width = substr($width,0,-1);
		$wd_class = ' bp_wdp'.$width;
	}else{
		$wd_class = ' bp_wdx'.$width;
	}

    $rname_exists = (isset($rname) && $rname) ? 1 : 0;
	
	$output_show = '';
	if(!$padding_right || $padding_right == '0'){
		$output_show = 'display:none;';
		$padding_right_style='';
		$wd_class = '';
	}else{
		$padding_right_style = 'padding-right:'.$padding_right.'px;';
	}
	
	$rid = 'r_'.tms_uniqid();
	$rinid = 'rin_'.tms_uniqid();
	$rotid = 'rot_'.tms_uniqid();
	
	ob_start();
    include G5_Z_PATH.'/form/input_range.skin.php';
    $input_content = ob_get_contents();
    ob_end_clean();

    return $input_content;
}	
}

//색상/투명도 설정 input form 생성 함수
if(!function_exists('tms_input_color')){
function tms_input_color($name='',$value='#333333',$w='',$alpha_flag=0){
    global $conf_com_idx,$g5,$config,$default,$member,$is_admin;
    
    //if($name == '') return '컬러픽커 name값이 없습니다.';
    
    $aid = tms_get_random_string('az',4).'_'.tms_uniqid();
    $bid = tms_get_random_string('az',4).'_'.tms_uniqid();
    $cid = tms_get_random_string('az',4).'_'.tms_uniqid();
    //그외 랜덤id값
    $did = tms_get_random_string('az',4).'_'.tms_uniqid();
    $eid = tms_get_random_string('az',4).'_'.tms_uniqid();

    $name_exists = (isset($name) && $name != '') ? 'Y' : 'N';
    // echo $name_exists;
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
    $radio_tag = '';
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
    global $conf_com_idx, $w;
    
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

if(!function_exists('category_tree_array')){
function category_tree_array($cat_code){
    $cat_arr = array();
    $cnt = strlen($cat_code)/2;
    for($i=1;$i<=$cnt;$i++){
        array_push($cat_arr,substr($cat_code,0,$i*2));
    }
    return $cat_arr;
}
}

// rgbToHsv 함수는 RGB 색상 모델을 HSV 색상 모델로 변환
// r, g, b 값은 0에서 255 사이의 정수여야
if(!function_exists('rgbToHsv')){
function rgbToHsv($r, $g, $b) {
    $r /= 255;
    $g /= 255;
    $b /= 255;

    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $delta = $max - $min;

    // Hue 계산
    if ($delta == 0) {
        $h = 0;
    } elseif ($max == $r) {
        $h = 60 * fmod((($g - $b) / $delta), 6);
    } elseif ($max == $g) {
        $h = 60 * ((($b - $r) / $delta) + 2);
    } else {
        $h = 60 * ((($r - $g) / $delta) + 4);
    }
    if ($h < 0) $h += 360;

    // Saturation 계산
    $s = ($max == 0) ? 0 : ($delta / $max);

    // Value 계산
    $v = $max;

    return [$h, $s, $v];
}
}

// 아래 함수는 원하는 개수 만큼의 선명한 색상코드(HEX값)을 랜덤하게생성합니다.
if(!function_exists('generateVividColors')){
function generateVividColors($count = 100, $minDistance = 100, $minSaturation = 0.5) {
    $colors = [];

    while (count($colors) < $count) {
        // 랜덤 RGB
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);

        // HSV 변환
        list($h, $s, $v) = rgbToHsv($r, $g, $b);

        // 채도 필터링: 낮은 채도는 건너뛰기
        if ($s < $minSaturation) continue;

        $hex = sprintf("#%02X%02X%02X", $r, $g, $b);

        // 중복 체크
        if (in_array($hex, $colors)) continue;

        // 이웃 색상 대비 체크
        if (!empty($colors)) {
            $prev = $colors[count($colors) - 1];
            list($pr, $pg, $pb) = sscanf($prev, "#%02X%02X%02X");
            $distance = sqrt(($r - $pr)**2 + ($g - $pg)**2 + ($b - $pb)**2);
            if ($distance < $minDistance) continue;
        }

        $colors[] = $hex;
    }

    return $colors;
}
}

if(!function_exists('change_com_names')){
function change_com_names($shop_id,$new_shop_name){
	global $g5;

	if(!$shop_id||!$new_shop_name)
		return;

	$com = get_table_meta_pg('shop','shop_id',$shop_id);

	// I intended to change keys info of order_table. cart_table, sales.
	// But it could break data integrity and have to change all data each time changing.
	// So trying to join tables.

	// Change all board info if needed.
	echo 55;


	return true;
}
}