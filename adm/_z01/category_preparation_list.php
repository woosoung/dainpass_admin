<?php
$sub_menu = "920900";
include_once('./_common.php');
include_once(G5_ZSQL_PATH.'/shop_category.php');

// 플랫폼 관리자 권한 체크
@auth_check($auth[$sub_menu],'r');

// 검색 파라미터
$sca = isset($_GET['sca']) ? trim($_GET['sca']) : ''; // category_id

// 전체 목록 개수 조회
$where_count = [];
if ($sca !== '') {
    $sca_escaped = sql_real_escape_string($sca);
    $where_count[] = "cd.category_id = '{$sca_escaped}'";
}
$where_count_sql = !empty($where_count) ? "WHERE " . implode(" AND ", $where_count) : "";
$total_count_sql = " SELECT COUNT(*) as cnt FROM {$g5['category_default_table']} cd {$where_count_sql} ";
$total_count_row = sql_fetch_pg($total_count_sql);
$total_count = $total_count_row['cnt'] ?? 0;

// 카테고리별 예약 준비시간 목록 조회 (category_default와 shop_categories 조인)
$where = [];
if ($sca !== '') {
    $sca_escaped = sql_real_escape_string($sca);
    $where[] = "cd.category_id = '{$sca_escaped}'";
}
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$list_sql = " 
    SELECT 
        cd.category_id,
        cd.prep_period_for_reservation,
        CASE 
            WHEN cd.category_id = '0' OR cd.category_id IS NULL THEN '모든 업종'
            WHEN char_length(cd.category_id) = 2 THEN sc.name
            WHEN char_length(cd.category_id) = 4 THEN 
                COALESCE(p.name || ' > ' || sc.name, sc.name)
            ELSE COALESCE(sc.name, '모든 업종')
        END AS display_name,
        sc.name
    FROM {$g5['category_default_table']} cd
    LEFT JOIN {$g5['shop_categories_table']} sc ON cd.category_id = sc.category_id
    LEFT JOIN {$g5['shop_categories_table']} p ON char_length(cd.category_id) = 4 AND p.category_id = left(cd.category_id, 2)
    {$where_sql}
    ORDER BY 
        CASE WHEN cd.category_id = '0' OR cd.category_id IS NULL THEN 0 ELSE 1 END,
        cd.category_id
";
$list_result = sql_query_pg($list_sql);
$list = [];
if ($list_result && is_object($list_result) && isset($list_result->result)) {
    while ($row = sql_fetch_array_pg($list_result->result)) {
        $list[] = $row;
    }
}

$g5['title'] = '업종별 예약 준비시간 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/category_preparation_list.js.php');
?>

<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 15px;
}

.modal-header h2 {
    font-size: 20px;
    font-weight: bold;
    color: #333;
}

.modal-close {
    cursor: pointer;
    font-size: 28px;
    color: #999;
    border: none;
    background: none;
    padding: 0;
    line-height: 1;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

.form-group select,
.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #228be6;
}

.btn-primary {
    background: #228be6;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary:hover {
    background: #1c7ed6;
}

.btn-secondary {
    background: #868e96;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-secondary:hover {
    background: #495057;
}

</style>

<div class="local_desc01 local_desc">
    <p>업종별로 예약 준비시간을 설정합니다. 예약과 예약 사이에 필요한 준비시간(청소, 세팅 등)을 분 단위로 설정할 수 있습니다.</p>
</div>

<!-- 검색 영역 -->
<form name="fsearch" method="get" class="local_sch01 local_sch">
    <div class="sch_last">
        <label for="sca" class="sound_only">업종 선택</label>
        <select name="sca" id="sca" onchange="this.form.submit();">
            <?php 
            // 모든 업종 목록 가져오기 (계층 구조로 정렬)
            $categories = array();
            $categories[''] = '전체보기';
            $categories['0'] = '모든 업종';
            
            // 1차 분류(2자리) 가져오기
            $sql_primary = " SELECT category_id, name 
                              FROM {$g5['shop_categories_table']} 
                              WHERE use_yn = 'Y' 
                              AND char_length(category_id) = 2
                              ORDER BY category_id ASC ";
            $result_primary = sql_query_pg($sql_primary);
            
            if ($result_primary && $result_primary->result) {
                while ($row = sql_fetch_array_pg($result_primary->result)) {
                    $primary_id = isset($row['category_id']) ? $row['category_id'] : '';
                    $primary_name = isset($row['name']) ? $row['name'] : '';
                    
                    if ($primary_id) {
                        // 1차 분류 추가
                        $categories[$primary_id] = $primary_name;
                        
                        // 해당 1차 분류의 2차 분류(4자리) 가져오기
                        $primary_id_escaped = sql_real_escape_string($primary_id);
                        $sql_secondary = " SELECT category_id, name 
                                           FROM {$g5['shop_categories_table']} 
                                           WHERE use_yn = 'Y' 
                                           AND char_length(category_id) = 4
                                           AND left(category_id, 2) = '{$primary_id_escaped}'
                                           ORDER BY category_id ASC ";
                        $result_secondary = sql_query_pg($sql_secondary);
                        
                        if ($result_secondary && $result_secondary->result) {
                            while ($row_sec = sql_fetch_array_pg($result_secondary->result)) {
                                $secondary_id = isset($row_sec['category_id']) ? $row_sec['category_id'] : '';
                                $secondary_name = isset($row_sec['name']) ? $row_sec['name'] : '';
                                
                                if ($secondary_id) {
                                    // 2차 분류 추가 (부모명 포함)
                                    $categories[$secondary_id] = $primary_name . ' > ' . $secondary_name;
                                }
                            }
                        }
                    }
                }
            }
            
            // 현재 선택된 카테고리
            $sca = isset($_GET['sca']) ? trim($_GET['sca']) : '';
            
            foreach ($categories as $cat_id => $cat_name) { 
            ?>
                <option value="<?php echo $cat_id; ?>" <?php echo ($sca == $cat_id) ? 'selected' : ''; ?>>
                    <?php echo get_text($cat_name); ?>
                </option>
            <?php } ?>
        </select>
    </div>
</form>

<!-- 상단 타이틀 및 버튼 -->
<div class="local_ov01 local_ov">
    <span class="btn_ov01">
        <span class="ov_txt">전체목록</span>
        <span class="ov_num">전체 <?php echo number_format($total_count); ?>건</span>
    </span>
</div>

<div class="btn_fixed_top">
    <button type="button" id="btn_new_registration" class="btn btn_02">신규등록</button>
</div>

<!-- 목록 테이블 -->
<div class="tbl_head01 tbl_wrap">
    <table id="category_prep_table">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <colgroup>
            <col style="width: 8%;">
            <col style="width: 15%;">
            <col style="width: 35%;">
            <col style="width: 20%;">
            <col style="width: 22%;">
        </colgroup>
        <thead>
            <tr>
                <th scope="col">번호</th>
                <th scope="col">카테고리ID</th>
                <th scope="col">업종명</th>
                <th scope="col">예약 준비시간(분)</th>
                <th scope="col">관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($list)) {
                echo '<tr><td colspan="5" class="empty_table text-center py-10">등록된 데이터가 없습니다.</td></tr>';
            } else {
                $num = 1;
                foreach ($list as $row) {
                    $category_id = htmlspecialchars($row['category_id']);
                    $display_name = isset($row['display_name']) && $row['display_name'] ? htmlspecialchars($row['display_name']) : htmlspecialchars($row['name'] ?? '');
                    $prep_time = (int)$row['prep_period_for_reservation'];
            ?>
            <tr>
                <td class="text-center"><?php echo $num; ?></td>
                <td class="text-center"><?php echo $category_id; ?></td>
                <td><?php echo $display_name; ?></td>
                <td class="text-center">
                    <form class="inline-form" data-category-id="<?php echo $category_id; ?>">
                        <input type="number" 
                               name="prep_period" 
                               value="<?php echo $prep_time; ?>" 
                               min="0" 
                               class="frm_input text-center" 
                               style="width: 100px;">
                    </form>
                </td>
                <td class="td_mng text-center">
                    <button type="button" class="btn btn_03 btn-save" data-category-id="<?php echo $category_id; ?>">저장</button>
                    <button type="button" class="btn btn_02 btn-delete" data-category-id="<?php echo $category_id; ?>">삭제</button>
                </td>
            </tr>
            <?php
                    $num++;
                }
            }
            ?>
        </tbody>
    </table>
</div>

<!-- 신규등록 모달 -->
<div id="registration_modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>업종별 예약 준비시간 등록</h2>
            <button class="modal-close">&times;</button>
        </div>
        <form id="registration_form">
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_category">업종(분류) 선택 <span style="color: red;">*</span></label>
                    <select id="modal_category" name="category_id" class="frm_input" required>
                        <option value="">업종을 선택하세요</option>
                        <?php 
                        // 전체보기는 제외하고 실제 카테고리만 표시 (모든 업종 포함)
                        foreach ($categories as $cat_id => $cat_name) { 
                            if ($cat_id !== '') { // 전체보기만 제외
                        ?>
                            <option value="<?php echo $cat_id; ?>"><?php echo get_text($cat_name); ?></option>
                        <?php 
                            }
                        } 
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modal_prep_period">예약 준비시간(분) <span style="color: red;">*</span></label>
                    <input type="number" 
                           id="modal_prep_period" 
                           name="prep_period_for_reservation" 
                           class="frm_input" 
                           min="0" 
                           value="0" 
                           required>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        예약과 예약 사이에 필요한 준비시간을 분 단위로 입력하세요.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary">저장</button>
                <button type="button" class="btn-secondary modal-close">취소</button>
            </div>
        </form>
    </div>
</div>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

