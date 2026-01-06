<?php
$sub_menu = "960300";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

auth_check_menu($auth, $sub_menu, "r");

// qna_id 검증
$qna_id = isset($_GET['qna_id']) ? (int)$_GET['qna_id'] : 0;
if ($qna_id <= 0 || $qna_id > 2147483647) {
    alert('잘못된 문의번호입니다.', './shop_customer_qa_list.php');
    exit;
}

if ($qna_id > 0) {
    // 최초 질문 조회 (해당 가맹점의 문의만)
    $sql = " SELECT q.*, c.user_id, c.name, c.nickname, c.phone, c.email
             FROM shop_qna q
             LEFT JOIN customers c ON q.customer_id = c.customer_id
             WHERE q.qna_id = {$qna_id}
             AND q.shop_id = {$shop_id}
             AND q.qna_parent_id IS NULL ";

    $qna = sql_fetch_pg($sql);

    if (!isset($qna['qna_id']) || !$qna['qna_id']) {
        alert("문의자료가 없습니다.", './shop_customer_qa_list.php');
        exit;
    }

    // 답변 목록 조회 (최신순) - PostgreSQL에서만 조회
    $sql_replies = " SELECT qr.*, c.user_id, c.name, c.nickname
                     FROM shop_qna qr
                     LEFT JOIN customers c ON qr.customer_id = c.customer_id
                     WHERE qr.qna_parent_id = {$qna_id}
                     ORDER BY qr.qna_created_at DESC ";
    $result_replies = sql_query_pg($sql_replies);
    
    $replies = array();
    $admin_ids = array(); // 관리자 ID 수집용
    
    if ($result_replies && isset($result_replies->result)) {
        while ($reply = sql_fetch_array_pg($result_replies->result)) {
            // 관리자 ID 수집 (MySQL에서 조회하기 위해)
            if (!empty($reply['reply_mb_id'])) {
                $admin_ids[] = sql_escape_string($reply['reply_mb_id']);
            }
            $replies[] = $reply;
        }
    }

    // MySQL에서 관리자 정보 조회
    $admin_info = array();
    if (!empty($admin_ids)) {
        $admin_ids = array_unique($admin_ids);
        $admin_ids_str = "'" . implode("','", $admin_ids) . "'";
        $sql_admin = " SELECT mb_id, mb_name FROM {$g5['member_table']} WHERE mb_id IN ({$admin_ids_str}) ";
        $result_admin = sql_query($sql_admin);
        
        if ($result_admin) {
            while ($admin = sql_fetch_array($result_admin)) {
                $admin_info[$admin['mb_id']] = $admin['mb_name'];
            }
        }
    }
    
    // 답변 배열에 관리자 이름 추가
    foreach ($replies as $key => $reply) {
        if (!empty($reply['reply_mb_id']) && isset($admin_info[$reply['reply_mb_id']])) {
            $replies[$key]['admin_name'] = $admin_info[$reply['reply_mb_id']];
        }
    }
    
    $html_title = "고객문의 - 보기";
} else {
    alert("문의번호가 없습니다.");
}

$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$qstr = '';
if (isset($_GET['qna_id'])) {
    $qstr = 'qna_id='.$_GET['qna_id'];
}

$pg_anchor ='<ul class="anchor">
<li><a href="#anc_qa_question">고객 문의</a></li>
<li><a href="#anc_qa_reply_form">답변 작성</a></li>
<li><a href="#anc_qa_replies">답변 목록</a></li>
</ul>';
?>

<div class="local_desc01 local_desc">
    <p>
        <?php echo get_shop_display_name($shop_info, $shop_id); ?>
    </p>
</div>

<form name="fcustomerqaform" id="fcustomerqaform" action="./shop_customer_qa_form_update.php" method="post" enctype="multipart/form-data" onsubmit="return fcustomerqaform_submit(this);">
<input type="hidden" name="qna_id" value="<?php echo $qna_id; ?>">
<input type="hidden" name="w" value="r">
<input type="hidden" name="token" value="<?php echo get_token(); ?>">
<input type="hidden" name="original_secret_yn" id="original_secret_yn" value="<?php echo $qna['qna_secret_yn']; ?>">

<section id="anc_qa_question">
    <h2 class="h2_frm"><?php echo $g5['title']; ?></h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>고객 문의 내용 (읽기 전용)</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">문의번호</th>
            <td><?=$qna['qna_id']?></td>
        </tr>
        <tr>
            <th scope="row">제목</th>
            <td>
                <a href="javascript:void(0);" class="text-blue-600">
                    <?php if($qna['qna_secret_yn'] == 'Y') { ?>
                        <i class="text-gray-500 fa fa-lock"></i>
                    <?php } ?>
                    <?=get_text($qna['qna_subject'])?>
                </a>
            </td>
        </tr>
        <tr>
            <th scope="row">작성자</th>
            <td>
                <div class="text-sm">
                    <strong><?=get_text($qna['name'])?></strong> (<?=get_text($qna['user_id'])?>)
                    <span class="ml-2 text-xs text-gray-500"><?=substr($qna['qna_created_at'],0,10)?></span>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">내용</th>
            <td>
                <div class="qna_content_view" style="padding: 15px; border: 1px solid #ddd; background: #f9f9f9; min-height: 100px; line-height: 1.6;">
                    <?=nl2br(get_text($qna['qna_content']))?>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">비밀글</th>
            <td>
                <?php if($qna['qna_secret_yn'] == 'Y') { ?>
                    <span class="text-red-600"><i class="fa fa-lock"></i> 비밀글</span>
                <?php } else { ?>
                    <span class="text-green-600">공개글</span>
                <?php } ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<hr style="margin: 20px 0; border: 1px solid #ddd;">

<section id="anc_qa_reply_form">
    <h2 class="h2_frm">답변 작성</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>답변 작성</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="qna_reply_secret_yn">비밀글</label></th>
            <td>
                <label for="qna_reply_secret_yn_y">
                    <input type="radio" name="qna_reply_secret_yn" id="qna_reply_secret_yn_y" value="Y" <?php echo ($qna['qna_secret_yn'] == 'Y') ? 'checked' : ''; ?>>
                    비밀글
                </label>
                <label for="qna_reply_secret_yn_n" style="margin-left: 20px;">
                    <input type="radio" name="qna_reply_secret_yn" id="qna_reply_secret_yn_n" value="N" <?php echo ($qna['qna_secret_yn'] == 'N') ? 'checked' : ''; ?>>
                    공개글
                </label>
                <?php if($qna['qna_secret_yn'] == 'N') { ?>
                    <div id="secret_warning" style="display: none; margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; color: #856404; border-radius: 4px;">
                        <i class="fa fa-exclamation-triangle"></i> 댓글에 비밀글 설정을 하시면 본 대표 문의글도 비밀글로 전환됩니다.
                    </div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="qna_reply_content">답변 내용<strong class="sound_only">필수</strong></label></th>
            <td>
                <textarea name="qna_reply_content" id="qna_reply_content" rows="10" class="frm_input" style="width: 100%;" required></textarea>
                <?php echo help("고객 문의에 대한 답변을 작성해주세요."); ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top">
    <input type="submit" value="입력" class="btn_submit btn" accesskey="s">
    <a href="./shop_customer_qa_list.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
</div>
</form>

<hr style="margin: 20px 0; border: 1px solid #ddd;">

<section id="anc_qa_replies">
    <h2 class="h2_frm">답변 목록</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <?php
        if (!empty($replies) && count($replies) > 0) {
            foreach ($replies as $idx => $reply) {
                $writer_name = '';
                $is_admin = false;
                
                // 관리자/고객 구분
                if (!empty($reply['reply_mb_id'])) {
                    // 관리자 답변
                    $writer_name = !empty($reply['admin_name']) ? get_text($reply['admin_name']) : get_text($reply['reply_mb_id']);
                    $is_admin = true;
                    // 관리자 답변 배경색 (연한 파란색)
                    $bg_class = 'bg-blue-50';
                    $border_color = '#3b82f6';
                } else {
                    // 고객 추가 질문
                    $writer_name = !empty($reply['name']) ? get_text($reply['name']) . ' (' . get_text($reply['user_id']) . ')' : '고객';
                    $is_admin = false;
                    // 고객 답변 배경색 (연한 초록색)
                    $bg_class = 'bg-green-50';
                    $border_color = '#10b981';
                }
                
                // 비밀글 처리: 본 문의글이 비밀글이면 댓글도 비밀글로 표시하되 lock 아이콘 없이 내용 표시
                $show_lock = false;
                $show_content = true;
                if ($qna['qna_secret_yn'] == 'N' && !empty($reply['qna_secret_yn']) && $reply['qna_secret_yn'] == 'Y') {
                    // 본 문의글이 공개인데 댓글이 비밀글인 경우에만 lock 표시
                    $show_lock = true;
                } elseif ($qna['qna_secret_yn'] == 'Y') {
                    // 본 문의글이 비밀글이면 댓글도 비밀글로 처리하되 lock 아이콘 없이 내용 표시
                    $show_lock = false;
                    $show_content = true;
                }
        ?>
        <div class="qna_reply_item <?=$bg_class?>" style="padding: 15px; margin-bottom: 10px; border: 2px solid <?=$border_color?>; border-radius: 4px;" data-reply-id="<?=$reply['qna_id']?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div>
                    <strong><?=$writer_name?></strong>
                    <?php if($is_admin) { ?>
                        <span class="text-sm text-blue-700 font-weight-bold">(가맹점 관리자)</span>
                        <?php 
                        // 관리자 답변이고 고객이 확인하지 않은 경우 (qna_status = 'pending')
                        $can_edit = ($is_admin && !empty($reply['qna_status']) && $reply['qna_status'] == 'pending');
                        $is_confirmed = ($is_admin && !empty($reply['qna_status']) && $reply['qna_status'] == 'ok');
                        
                        // 고객 확인 여부 표시
                        if ($is_confirmed) {
                        ?>
                        <span class="ml-2 text-xs text-green-600"><i class="fa fa-check-circle"></i> 고객 확인완료</span>
                        <?php } else { ?>
                        <span class="ml-2 text-xs text-orange-600"><i class="fa fa-clock-o"></i> 고객 미확인</span>
                        <?php } ?>
                        
                        <?php if ($can_edit) { ?>
                        <span class="ml-2 text-xs text-orange-600">(수정/삭제 가능)</span>
                        <?php } ?>
                    <?php } else { ?>
                        <span class="text-sm text-green-700 font-weight-bold">(고객)</span>
                    <?php } ?>
                    <?php if($show_lock) { ?>
                        <i class="ml-2 text-gray-500 fa fa-lock"></i>
                    <?php } ?>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="text-sm text-gray-500"><?=!empty($reply['qna_created_at']) ? substr($reply['qna_created_at'],0,16) : ''?></span>
                    <?php if($is_admin && $can_edit) { ?>
                        <button type="button" class="btn_edit_reply btn_02 btn" data-reply-id="<?=$reply['qna_id']?>" style="padding: 4px 8px; font-size: 12px;">수정</button>
                        <button type="button" class="btn_delete_reply btn_02 btn" data-reply-id="<?=$reply['qna_id']?>" style="padding: 4px 8px; font-size: 12px; background: #dc3545; color: #fff;">삭제</button>
                    <?php } ?>
                </div>
            </div>
            <div class="qna_reply_content" id="reply_content_<?=$reply['qna_id']?>" style="padding: 10px; background: #fff; border: 1px solid #eee; min-height: 50px; line-height: 1.6; border-radius: 4px;">
                <?php if($show_lock && !$show_content) { ?>
                    <span class="text-gray-500">비밀글입니다.</span>
                <?php } else { ?>
                    <?=!empty($reply['qna_content']) ? nl2br(get_text($reply['qna_content'])) : ''?>
                <?php } ?>
            </div>
            <?php if($is_admin && $can_edit) { ?>
            <div id="reply_edit_form_<?=$reply['qna_id']?>" style="display: none; margin-top: 10px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                <form class="frm_edit_reply" data-reply-id="<?=$reply['qna_id']?>">
                    <input type="hidden" name="reply_id" value="<?=$reply['qna_id']?>">
                    <input type="hidden" name="qna_id" value="<?=$qna_id?>">
                    <div style="margin-bottom: 10px;">
                        <label><strong>답변 내용 수정:</strong></label>
                        <textarea name="qna_reply_content" rows="5" class="frm_input" style="width: 100%;" required><?=!empty($reply['qna_content']) ? htmlspecialchars($reply['qna_content']) : ''?></textarea>
                    </div>
                    <div style="text-align: right;">
                        <button type="button" class="btn_cancel_edit btn_02 btn" data-reply-id="<?=$reply['qna_id']?>" style="padding: 4px 8px; font-size: 12px; margin-right: 5px;">취소</button>
                        <button type="submit" class="btn_submit btn" style="padding: 4px 8px; font-size: 12px;">수정 저장</button>
                    </div>
                </form>
            </div>
            <?php } ?>
        </div>
        <?php
            }
        } else {
        ?>
        <div class="empty_table" style="padding: 20px; text-align: center; color: #999;">
            아직 답변이 없습니다.
        </div>
        <?php
        }
        ?>
    </div>
</section>

<?php include_once('./js/shop_customer_qa_form.js.php'); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
