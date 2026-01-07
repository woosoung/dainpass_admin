<?php
$sub_menu = '960200';
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'r');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

// 파라미터 검증
$fm_id = isset($_GET['fm_id']) ? (int)$_GET['fm_id'] : 0;
$fm_id = ($fm_id > 0 && $fm_id <= 2147483647) ? $fm_id : 0;

$fm_subject = isset($_GET['fm_subject']) ? clean_xss_tags($_GET['fm_subject']) : '';
$fm_subject = substr($fm_subject, 0, 100); // 최대 길이 제한

// fm_id 필수 체크
if (!$fm_id) {
    alert('잘못된 접근입니다.', './shop_faqmasterlist.php');
    exit;
}

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

$g5['title'] = 'FAQ 상세관리 : '.htmlspecialchars($fm_subject, ENT_QUOTES, 'UTF-8');

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
                $fa_id = (int)$row['fa_id'];
                $row_fm_id = (int)$row['fm_id'];
                $fa_order = (int)$row['fa_order'];

                // ID 범위 검증
                if ($fa_id <= 0 || $fa_id > 2147483647 || $row_fm_id <= 0 || $row_fm_id > 2147483647) {
                    continue;
                }

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
                <td class="td_left"><?php echo htmlspecialchars($question, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="td_num"><?php echo $fa_order; ?></td>
                <td class="td_mng td_mng_m">
                    <a href="./shop_faqform.php?w=u&amp;fm_id=<?php echo $row_fm_id; ?>&amp;fa_id=<?php echo $fa_id; ?>" class="btn btn_03">수정</a>
                    <a href="./shop_faqformupdate.php?w=d&amp;fm_id=<?php echo $row_fm_id; ?>&amp;fa_id=<?php echo $fa_id; ?>&amp;token=<?php echo get_admin_token(); ?>" onclick="return delete_confirm(this);" class="btn btn_02">삭제</a>
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
