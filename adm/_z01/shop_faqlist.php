<?php
$sub_menu = '960200';
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'r');

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

        if ($mb_1_value !== '' && $mb_1_value !== '0') {
            $shop_id_check = (int) $mb_1_value;
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
                $shop_id = (int) $shop_row['shop_id'];
                $shop_info = $shop_row;
            }
        }
    }
}

if (!$has_access || !$shop_id) {
    $g5['title'] = 'FAQ 상세관리';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

// 파라미터
$fm_id = isset($_GET['fm_id']) ? (int) $_GET['fm_id'] : 0;
$fm_subject = isset($_GET['fm_subject']) ? clean_xss_tags($_GET['fm_subject'], 1, 1, 255) : '';

// 해당 가맹점의 마스터인지 확인
$fm_sql = " SELECT fm_id, shop_id, fm_subject
            FROM faq_master
            WHERE fm_id = {$fm_id}
              AND shop_id = {$shop_id} ";
$fm = sql_fetch_pg($fm_sql);

if (!$fm || !$fm['fm_id']) {
    alert('등록된 FAQ 마스터가 없거나, 다른 가맹점의 데이터입니다.', './shop_faqmasterlist.php');
    exit;
}

// 제목은 DB 기준으로 사용
if ($fm['fm_subject']) {
    $fm_subject = $fm['fm_subject'];
}

$g5['title'] = 'FAQ 상세관리 : '.get_text($fm_subject);

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// FAQ 목록
$sql_common = " FROM faq WHERE fm_id = {$fm_id} ";

$cnt_sql = " SELECT COUNT(*) AS cnt ".$sql_common;
$row = sql_fetch_pg($cnt_sql);
$total_count = isset($row['cnt']) ? (int) $row['cnt'] : 0;

$list_sql = " SELECT fa_id, fm_id, fa_question, fa_answer, fa_order
              ".$sql_common."
              ORDER BY fa_order, fa_id ";
$result = sql_query_pg($list_sql);
?>

<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt"> 등록된 FAQ 상세내용</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
</div>

<div class="local_desc01 local_desc">
    <ol>
        <li>FAQ는 무제한으로 등록할 수 있습니다.</li>
        <li><strong>FAQ 상세내용 추가</strong>를 눌러 자주하는 질문과 답변을 입력합니다.</li>
    </ol>
</div>

<div class="btn_fixed_top">
    <a href="./shop_faqmasterlist.php" class="btn btn_02">FAQ 마스터 관리</a>
    <a href="./shop_faqform.php?fm_id=<?php echo (int) $fm['fm_id']; ?>" class="btn btn_01">FAQ 상세내용 추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr>
                <th scope="col">번호</th>
                <th scope="col">질문</th>
                <th scope="col">순서</th>
                <th scope="col">관리</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && is_object($result) && isset($result->result)) {
            for ($i = 0; $row = sql_fetch_array_pg($result->result); $i++) {
                $num = $i + 1;
                $bg = 'bg'.($i % 2);

                // 질문 내용은 에디터 HTML이므로 간단히 텍스트만 추출/축약
                $question = trim(strip_tags($row['fa_question']));
                if (mb_strlen($question) > 80) {
                    $question = mb_substr($question, 0, 80).'...';
                }
        ?>
            <tr class="<?php echo $bg; ?>">
                <td class="td_num"><?php echo $num; ?></td>
                <td class="td_left"><?php echo get_text($question); ?></td>
                <td class="td_num"><?php echo (int) $row['fa_order']; ?></td>
                <td class="td_mng td_mng_m">
                    <a href="./shop_faqform.php?w=u&amp;fm_id=<?php echo (int) $row['fm_id']; ?>&amp;fa_id=<?php echo (int) $row['fa_id']; ?>" class="btn btn_03">수정</a>
                    <a href="./shop_faqformupdate.php?w=d&amp;fm_id=<?php echo (int) $row['fm_id']; ?>&amp;fa_id=<?php echo (int) $row['fa_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02">삭제</a>
                </td>
            </tr>
        <?php
            }
        }

        if (!isset($i) || $i == 0) {
            echo '<tr><td colspan="4" class="empty_table">자료가 없습니다.</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
