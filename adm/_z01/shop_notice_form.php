<?php
$sub_menu = "960100";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

// shop_notice 에디터 이미지 업로드 시 S3에 저장하도록 세션 설정
if (!isset($_SESSION)) {
    @session_start();
}
$_SESSION['shop_notice_upload'] = true;

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;
$shop_info = null;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (
                    mb_level >= 6 
                    OR (mb_level < 6 AND mb_2 = 'Y')
                )
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        
        if ($mb_1_value === '0' || $mb_1_value === '') {
            $g5['title'] = '공지사항';
            include_once(G5_ADMIN_PATH.'/admin.head.php');
            echo '<div class="local_desc01 local_desc text-center py-[200px]">';
            echo '<p>업체 데이터가 없습니다.</p>';
            echo '</div>';
            include_once(G5_ADMIN_PATH.'/admin.tail.php');
            exit;
        }
        
        if (!empty($mb_1_value)) {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, shop_name, name, status 
                         FROM {$g5['shop_table']} 
                         WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending')
                    alert('아직 승인이 되지 않았습니다.');
                if ($shop_row['status'] == 'closed')
                    alert('폐업되었습니다.');
                if ($shop_row['status'] == 'shutdown')
                    alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
                $shop_info = $shop_row;
            } else {
                $g5['title'] = '공지사항';
                include_once(G5_ADMIN_PATH.'/admin.head.php');
                echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                echo '<p>업체 데이터가 없습니다.</p>';
                echo '</div>';
                include_once(G5_ADMIN_PATH.'/admin.tail.php');
                exit;
            }
        }
    }
}

if (!$has_access) {
    $g5['title'] = '공지사항';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'w');

$shopnotice_id = isset($_REQUEST['shopnotice_id']) ? (int)$_REQUEST['shopnotice_id'] : 0;
$w = isset($_REQUEST['w']) ? clean_xss_tags($_REQUEST['w']) : '';

$notice = array(
    'shopnotice_id' => 0,
    'shop_id' => $shop_id,
    'mb_id' => $member['mb_id'],
    'subject' => '',
    'content' => '',
    'status' => 'ok'
);

if ($w == 'u') {
    $html_title = '공지사항 수정';
    
    $sql = " SELECT * FROM shop_notice WHERE shopnotice_id = {$shopnotice_id} AND shop_id = {$shop_id} ";
    $notice_row = sql_fetch_pg($sql);
    
    if (!$notice_row || !$notice_row['shopnotice_id']) {
        alert('등록된 자료가 없습니다.');
    }
    
    $notice = array(
        'shopnotice_id' => $notice_row['shopnotice_id'],
        'shop_id' => $notice_row['shop_id'],
        'mb_id' => $notice_row['mb_id'],
        'subject' => $notice_row['subject'],
        'content' => $notice_row['content'],
        'status' => $notice_row['status']
    );
    
    // 수정 모드일 때 shopnotice_id를 세션에 저장 (에디터 업로드 시 사용)
    if (!isset($_SESSION)) {
        @session_start();
    }
    $_SESSION['shop_notice_id'] = $notice_row['shopnotice_id'];
} else {
    $html_title = '공지사항 등록';
    
    // 신규 등록 모드일 때는 세션에서 shopnotice_id 제거
    if (isset($_SESSION['shop_notice_id'])) {
        unset($_SESSION['shop_notice_id']);
    }
}

// 에디터 설정
$is_dhtml_editor = false;
if ($config['cf_editor'] && (!is_mobile() || (defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE))) {
    $is_dhtml_editor = true;
}

$content = '';
if ($w == 'u') {
    // 에디터에 표시할 때는 HTML을 유지해야 이미지가 표시됨
    // 관리자 페이지이므로 최소한의 필터링만 적용 (S3 URL 보존)
    if ($is_dhtml_editor) {
        // 관리자 페이지이므로 html_purifier를 거치지 않고 직접 사용
        // 최소한의 XSS 방지만 적용 (script 태그 제거)
        $content = isset($notice['content']) ? $notice['content'] : '';
        
        if (!empty($content)) {
            // PostgreSQL에서 가져온 content는 이미 올바른 형식일 수 있음
            // 하지만 안전을 위해 디코딩 시도
            
            // 먼저 백슬래시 제거 (magic quotes 등으로 인한 이스케이프 제거)
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                $content = stripslashes($content);
            }
            
            // HTML 엔티티가 인코딩되어 있는지 확인
            // &quot; 또는 &amp; 같은 엔티티가 있으면 디코딩
            if (strpos($content, '&quot;') !== false || strpos($content, '&amp;') !== false || strpos($content, '&lt;') !== false || strpos($content, '&gt;') !== false) {
                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            
            // S3 URL이 제대로 있는지 확인하고, 필요시 URL 디코딩
            if (strpos($content, 'amazonaws.com') !== false) {
                // URL 인코딩된 부분이 있으면 디코딩
                $content = preg_replace_callback('/src=["\']([^"\']*amazonaws\.com[^"\']*)["\']/i', function($matches) {
                    $url = $matches[1];
                    // URL 인코딩된 부분 디코딩
                    $decoded_url = urldecode($url);
                    return 'src="' . $decoded_url . '"';
                }, $content);
            }
            
            // 위험한 스크립트 태그만 제거 (이미지는 보존)
            $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $content);
            $content = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $content); // 이벤트 핸들러 제거
            $content = preg_replace('/javascript:/i', '', $content); // javascript: 프로토콜 제거
        }
    } else {
        $content = get_text(html_purifier($notice['content']), 0);
    }
}
// exit;
$editor_html = editor_html('content', $content, $is_dhtml_editor);
$editor_js = '';
$editor_js .= get_editor_js('content', $is_dhtml_editor);
$editor_js .= chk_editor_js('content', $is_dhtml_editor);

$g5['title'] = $html_title;
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 에디터 JavaScript 변수 전달
$editor_js_content = $editor_js;

?>

<div class="local_desc01 local_desc">
    <p>
        공지사항을 <?php echo $w == 'u' ? '수정' : '등록'; ?>합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<form name="fnoticeform" action="./shop_notice_formupdate.php" method="post" onsubmit="return form_check(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="shopnotice_id" value="<?php echo $notice['shopnotice_id']; ?>">
<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
<input type="hidden" name="sst" value="<?php echo isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : ''; ?>">
<input type="hidden" name="sod" value="<?php echo isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : ''; ?>">
<input type="hidden" name="sfl" value="<?php echo isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : ''; ?>">
<input type="hidden" name="stx" value="<?php echo isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : ''; ?>">
<input type="hidden" name="sfl2" value="<?php echo isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; ?>">
<input type="hidden" name="page" value="<?php echo isset($_GET['page']) ? (int)$_GET['page'] : 1; ?>">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title'] ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="subject">제목<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="subject" value="<?php echo get_text($notice['subject']); ?>" id="subject" required class="frm_input required" size="80" maxlength="255">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="content">내용<strong class="sound_only">필수</strong></label></th>
        <td>
            <?php echo $editor_html; ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="status">상태<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="status" id="status" class="frm_input required" required>
                <option value="ok"<?php echo $notice['status'] == 'ok' ? ' selected' : '' ?>>정상</option>
                <option value="pending"<?php echo $notice['status'] == 'pending' ? ' selected' : '' ?>>대기</option>
            </select>
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="submit" class="btn_submit btn">확인</button>
    <a href="./shop_notice_list.php?<?php echo isset($_GET['sst']) ? 'sst='.urlencode($_GET['sst']) : ''; ?><?php echo isset($_GET['sod']) ? '&sod='.urlencode($_GET['sod']) : ''; ?><?php echo isset($_GET['sfl']) ? '&sfl='.urlencode($_GET['sfl']) : ''; ?><?php echo isset($_GET['stx']) ? '&stx='.urlencode($_GET['stx']) : ''; ?><?php echo isset($_GET['sfl2']) ? '&sfl2='.urlencode($_GET['sfl2']) : ''; ?><?php echo isset($_GET['page']) ? '&page='.(int)$_GET['page'] : ''; ?>" class="btn_cancel btn btn_02">취소</a>
</div>

</form>

<?php
include_once('./js/shop_notice_form.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

