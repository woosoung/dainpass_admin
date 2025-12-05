<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (is_file(G5_Z_PATH . '/js/tailwind.min.js')) add_javascript('<script src="' . G5_Z_URL . '/js/tailwind.min.js"></script>', 0);

// dainpass_pg에서 SERVICE, PRIVACY 약관만 조회
$sql = " SELECT * FROM {$g5['service_terms_table']} WHERE st_code IN ('SERVICE', 'PRIVACY') ORDER BY st_order ASC, st_id ASC ";
$result = sql_query_pg($sql);

$terms = array();
if ($result && is_object($result) && isset($result->result)) {
    while ($row = sql_fetch_array_pg($result->result)) {
        $terms[] = $row;
    }
}

// 약관이 없으면 에러 처리
if (empty($terms)) {
    alert('등록된 서비스 약관이 없습니다. 관리자에게 문의하세요.', G5_URL);
    exit;
}

// SERVICE와 PRIVACY 약관 매핑 (agree, agree2와 매칭)
$term_map = array();
foreach ($terms as $term) {
    if ($term['st_code'] === 'SERVICE') {
        $term_map['agree'] = $term;
    } elseif ($term['st_code'] === 'PRIVACY') {
        $term_map['agree2'] = $term;
    }
}
?>

<div class="max-w-2xl p-4 mx-auto sm:p-6">

    <!-- 안내 메시지 -->
    <div class="p-4 mb-6 border-l-4 border-blue-500 rounded bg-blue-50">
        <p class="text-sm text-blue-700">
            회원가입을 위해 아래 약관에 동의해주세요.
        </p>
    </div>

    <form name="fregister" id="fregister" action="<?php echo $register_action_url ?>" onsubmit="return fregister_submit(this);" method="POST" autocomplete="off">

        <div class="p-4 mb-6 bg-gray-100 border-2 border-gray-300 rounded-lg">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox"
                    name="chk_all"
                    id="chk_all"
                    class="w-6 h-6 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-base font-bold text-gray-800">
                    모든 약관에 동의합니다
                </span>
            </label>
        </div>

        <?php if (isset($term_map['agree'])):
            $term = $term_map['agree'];
            $st_code = $term['st_code'];
            $st_title = $term['st_title'];
            $st_content = $term['st_content'];
            $st_version = $term['st_version'];
        ?>
            <section class="mb-4 term-section">
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-t-lg cursor-pointer bg-gray-50"
                    onclick="toggleTermContent('term_service')">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-semibold text-gray-800">
                            이용약관 동의
                        </h3>
                    </div>
                    <svg id="icon_service" class="w-5 h-5 text-gray-600 transition-transform transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div id="term_service" class="hidden border-b border-gray-200 border-x">
                    <div class="p-4 overflow-y-auto text-sm leading-relaxed text-gray-700 bg-white max-h-60">
                        <?php echo $st_content; ?>
                    </div>
                </div>

                <div class="p-3 bg-white border-b border-gray-200 rounded-b-lg border-x">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                            name="agree"
                            value="1"
                            id="agree11"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 agree-checkbox">
                        <span class="text-sm text-gray-700">
                            <span class="font-semibold text-red-600">[필수]</span>
                            이용약관에 동의합니다.
                        </span>
                    </label>
                </div>
            </section>
        <?php endif; ?>

        <?php if (isset($term_map['agree2'])):
            $term = $term_map['agree2'];
            $st_code = $term['st_code'];
            $st_title = $term['st_title'];
            $st_content = $term['st_content'];
        ?>
            <section class="mb-4 term-section">
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-t-lg cursor-pointer bg-gray-50"
                    onclick="toggleTermContent('term_privacy')">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-semibold text-gray-800">
                            개인정보 수집 및 이용 동의
                        </h3>
                    </div>
                    <svg id="icon_privacy" class="w-5 h-5 text-gray-600 transition-transform transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div id="term_privacy" class="hidden border-b border-gray-200 border-x">
                    <div class="p-4 overflow-y-auto text-sm leading-relaxed text-gray-700 bg-white max-h-60">
                        <?php echo $st_content; ?>
                    </div>
                </div>

                <div class="p-3 bg-white border-b border-gray-200 rounded-b-lg border-x">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                            name="agree2"
                            value="1"
                            id="agree21"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 agree-checkbox">
                        <span class="text-sm text-gray-700">
                            <span class="font-semibold text-red-600">[필수]</span>
                            개인정보 수집 및 이용에 동의합니다.
                        </span>
                    </label>
                </div>
            </section>
        <?php endif; ?>

        <div class="mt-8">
            <button type="submit" class="w-full px-6 py-4 text-lg font-semibold text-white transition duration-300 ease-in-out transform bg-blue-600 rounded-lg shadow-md hover:bg-blue-700">
                회원가입
            </button>
        </div>

    </form>
</div>

<script>
    // 아코디언 토글 함수
    function toggleTermContent(termId) {
        const content = document.getElementById(termId);
        const icon = document.getElementById('icon_' + termId.replace('term_', ''));

        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    // 폼 제출 검증
    function fregister_submit(f) {
        if (!f.agree.checked) {
            alert('이용약관에 동의하셔야 회원가입 하실 수 있습니다.');
            f.agree.focus();

            // 약관 내용 펼치기
            const termContent = document.getElementById('term_service');
            if (termContent && termContent.classList.contains('hidden')) {
                termContent.classList.remove('hidden');
                const icon = document.getElementById('icon_service');
                if (icon) icon.classList.add('rotate-180');
            }

            return false;
        }

        if (!f.agree2.checked) {
            alert('개인정보 수집 및 이용에 동의하셔야 회원가입 하실 수 있습니다.');
            f.agree2.focus();

            // 약관 내용 펼치기
            const termContent = document.getElementById('term_privacy');
            if (termContent && termContent.classList.contains('hidden')) {
                termContent.classList.remove('hidden');
                const icon = document.getElementById('icon_privacy');
                if (icon) icon.classList.add('rotate-180');
            }

            return false;
        }

        return true;
    }

    // jQuery - 전체 선택 기능
    jQuery(function($) {
        // 전체 선택 체크박스 클릭
        $("input[name=chk_all]").click(function() {
            const isChecked = $(this).prop('checked');
            $(".agree-checkbox").prop('checked', isChecked);
        });

        // 개별 체크박스 변경 시 전체 선택 상태 업데이트
        $(".agree-checkbox").change(function() {
            const totalCheckboxes = $(".agree-checkbox").length;
            const checkedCheckboxes = $(".agree-checkbox:checked").length;
            $("input[name=chk_all]").prop('checked', totalCheckboxes === checkedCheckboxes);
        });
    });
</script>