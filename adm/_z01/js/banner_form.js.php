<script>
document.addEventListener('DOMContentLoaded', function(){
    const copyIcons = document.querySelectorAll(".copy_url");
    
    copyIcons.forEach(icon => {
        icon.addEventListener("click", function () {
            const targetSpan = this.nextElementSibling;
            if (targetSpan && targetSpan.classList.contains("copied_url")) {
                const text = targetSpan.textContent;
                navigator.clipboard.writeText(text)
                    .then(() => alert("텍스트가 복사되었습니다!"))
                    .catch(err => {
                        alert("복사에 실패했습니다.");
                        console.error("Clipboard copy failed:", err);
                    });
            }
        });
    });

    // 배너그룹 코드 실시간 중복 체크
    const bngCodeInput = document.getElementById('bng_code');
    if (bngCodeInput && !bngCodeInput.readOnly) {
        let checkTimeout;
        bngCodeInput.addEventListener('input', function() {
            const code = this.value.trim();
            const checkResult = document.getElementById('bng_code_check_result');
            
            // 입력이 없으면 결과 숨김
            if (!code) {
                checkResult.textContent = '';
                checkResult.className = '';
                return;
            }
            
            // 형식 검증 (영문 시작, 영문/숫자/_만)
            const codePattern = /^[a-zA-Z][a-zA-Z0-9_]*$/;
            if (!codePattern.test(code)) {
                checkResult.textContent = '영문으로 시작하고 영문, 숫자, 언더스코어(_)만 사용 가능합니다.';
                checkResult.style.color = '#ff0000';
                checkResult.className = 'error';
                return;
            }
            
            // 디바운싱: 입력이 멈춘 후 500ms 뒤에 체크
            clearTimeout(checkTimeout);
            checkResult.textContent = '확인 중...';
            checkResult.style.color = '#666666';
            checkResult.className = 'checking';
            
            checkTimeout = setTimeout(function() {
                checkBannerCode(code);
            }, 500);
        });
    }

    // 배너그룹 이미지 MultiFile (PC용)
    $('#banner_group_img').MultiFile({
        max:1,
        accept:'jpg|jpeg|png|gif|svg',
    });

    // 배너그룹 이미지 MultiFile (모바일용)
    $('#banner_group_mo_img').MultiFile({
        max:1,
        accept:'jpg|jpeg|png|gif|svg',
    });

    // 배너 이미지 MultiFile (PC용)
    $('#banner_img').MultiFile({
        max:1,
        accept:'jpg|jpeg|png|gif|svg',
    });

    // 배너 이미지 MultiFile (모바일용)
    $('#banner_mo_img').MultiFile({
        max:1,
        accept:'jpg|jpeg|png|gif|svg',
    });

    // 배너 목록 Sortable 초기화
    const bannerListEl = document.getElementById('banner_list');
    if (bannerListEl) {
        // helper 함수: 드래그 중인 요소의 너비 및 배경색 유지
        var fixHelperModified = function(e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            
            // 각 셀의 너비와 배경색 복사
            $helper.children().each(function(index) {
                var $originalCell = $originals.eq(index);
                var $helperCell = $(this);
                
                // 너비 복사
                $helperCell.width($originalCell.width());
                
                // 배경색 복사 (인라인 스타일 또는 계산된 스타일)
                var bgColor = $originalCell.css('background-color');
                if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
                    $helperCell.css('background-color', bgColor);
                }
            });
            
            // 행의 배경색도 복사
            var rowBgColor = $(tr).css('background-color');
            if (rowBgColor && rowBgColor !== 'rgba(0, 0, 0, 0)' && rowBgColor !== 'transparent') {
                $helper.css('background-color', rowBgColor);
            }
            
            $helper.css({
                'display': 'table-row',
                'opacity': '1'
            });
            
            return $helper;
        };
        
        $(bannerListEl).sortable({
            items: 'tr:not(.no-data)',
            placeholder: 'tr-placeholder',
            cancel: 'input, textarea, a, button',
            cursor: 'move',
            helper: fixHelperModified,
            start: function(event, ui) {
                // 드래그 시작 시 각 셀의 너비를 저장하고 고정
                ui.item.children().each(function() {
                    var $cell = $(this);
                    var cellWidth = $cell.width();
                    $cell.css('width', cellWidth + 'px');
                });
                
                // placeholder의 너비도 동일하게 설정
                ui.placeholder.children().each(function(index) {
                    var cellWidth = ui.item.children().eq(index).width();
                    $(this).css('width', cellWidth + 'px');
                });
                ui.placeholder.css('height', ui.item.height() + 'px');
                
                // 드래그 중인 요소의 opacity와 배경색 유지
                ui.item.css('opacity', '1');
            },
            stop: function(event, ui) {
                // 드래그 종료 시 너비 제약 해제
                ui.item.children().each(function() {
                    $(this).css('width', '');
                });
                setTimeout(function() {
                    ui.item.removeAttr('style');
                }, 10);
                updateBannerSort();
            }
        });
    }

    // 배너 추가/수정 버튼
    const btnBannerAdd = document.getElementById('btn_banner_add');
    if (btnBannerAdd) {
        btnBannerAdd.addEventListener('click', function() {
            addBanner();
        });
    }

    // 배너 수정 취소 버튼
    const btnBannerCancel = document.getElementById('btn_banner_cancel');
    if (btnBannerCancel) {
        btnBannerCancel.addEventListener('click', function() {
            cancelEditBanner();
        });
    }

    // 배너 삭제 버튼 이벤트 위임
    $(document).on('click', '.btn_banner_del', function() {
        const bnrId = $(this).data('bnr-id');
        if (confirm('이 배너를 삭제하시겠습니까?')) {
            deleteBanner(bnrId);
        }
    });

    // 배너 수정 버튼 이벤트 위임
    $(document).on('click', '.btn_banner_edit', function() {
        const bnrId = $(this).data('bnr-id');
        editBanner(bnrId);
    });
});

// 가맹점 선택 팝업 열기
function open_shop_popup() {
    const winShop = window.open(
        './_win_shop_select.php',
        'winShop',
        'width=900, height=700, left=100, top=100, scrollbars=yes'
    );
    if (winShop) {
        winShop.focus();
    }
}

// 팝업에서 가맹점 선택 시 호출될 함수
function set_selected_shop(shop_id, shop_name) {
    document.getElementById('bnr_shop_id').value = shop_id || '0';
    document.getElementById('bnr_shop_name').value = shop_name || '';
}

// 가맹점 선택 초기화
function clear_shop_selection() {
    document.getElementById('bnr_shop_id').value = '0';
    document.getElementById('bnr_shop_name').value = '';
}

// 배너 정렬 업데이트
async function updateBannerSort() {
    const bannerListEl = document.getElementById('banner_list');
    if (!bannerListEl) return;
    
    const bannerItems = bannerListEl.querySelectorAll('tr[data-bnr-id]');
    const bnrIds = Array.from(bannerItems).map((item) => {
        return item.getAttribute('data-bnr-id');
    });
    
    if (bnrIds.length === 0) return;
    
    const bngId = <?=isset($bng_id) ? $bng_id : 0?>;
    if (!bngId) return;
    
    const url = '<?=G5_Z_URL?>/ajax/banner_sort_update.php';
    try {
        const data = {
            bng_id: bngId,
            bnr_ids: bnrIds.join(',')
        };
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });
        
        const result = await res.text();
        
        if (!res.ok || result !== 'ok') {
            throw new Error('순서 업데이트 실패');
        }
        
        // 순서 번호 업데이트 (아이콘 유지)
        bannerItems.forEach((item, index) => {
            const sortCell = item.querySelector('.td_sort');
            if (sortCell) {
                const newSortNumber = index + 1;
                // 기존 아이콘 찾기
                const existingIcon = sortCell.querySelector('.fa-arrows');
                
                if (existingIcon) {
                    // 아이콘이 있으면 순서 번호만 업데이트 (첫 번째 텍스트 노드 찾기)
                    let textNode = null;
                    for (let i = 0; i < sortCell.childNodes.length; i++) {
                        if (sortCell.childNodes[i].nodeType === Node.TEXT_NODE) {
                            textNode = sortCell.childNodes[i];
                            break;
                        }
                    }
                    if (textNode) {
                        textNode.textContent = newSortNumber;
                    } else {
                        // 텍스트 노드가 없으면 순서 번호를 아이콘 앞에 추가
                        sortCell.insertBefore(document.createTextNode(newSortNumber), existingIcon);
                    }
                } else {
                    // 아이콘이 없으면 순서 번호와 아이콘 모두 추가
                    sortCell.innerHTML = newSortNumber + '<i class="fa fa-arrows" style="position:absolute;top:4px;right:7px;color:#999;font-size:15px;cursor:move;" title="드래그하여 순서 변경"></i>';
                }
            }
        });
    } catch (error) {
        console.error('Error:', error);
        alert('순서 업데이트에 실패했습니다.');
        location.reload(); // 실패 시 새로고침
    }
}

// 배너 추가/수정
async function addBanner() {
    const bngId = <?=isset($bng_id) ? $bng_id : 0?>;
    if (!bngId) {
        alert('배너그룹을 먼저 저장해주세요.');
        return;
    }

    // 수정 모드 확인
    const editingBnrId = document.getElementById('editing_bnr_id').value;
    const isEditMode = editingBnrId && editingBnrId !== '';

    // 폼 데이터 수집
    const formData = new FormData();
    
    // 토큰 추가
    const tokenInput = document.querySelector('input[name="token"]');
    if (tokenInput && tokenInput.value) {
        formData.append('token', tokenInput.value);
    } else {
        // 토큰이 없으면 get_ajax_token() 함수 사용
        if (typeof get_ajax_token === 'function') {
            const token = get_ajax_token();
            if (token) {
                formData.append('token', token);
            }
        }
    }
    
    formData.append('action', isEditMode ? 'update' : 'insert');
    if (isEditMode) {
        formData.append('bnr_id', editingBnrId);
    } else {
        formData.append('bng_id', bngId);
    }
    formData.append('shop_id', document.getElementById('bnr_shop_id').value || '0');
    formData.append('bnr_name', document.getElementById('bnr_name').value || '');
    formData.append('bnr_desc', document.getElementById('bnr_desc').value || '');
    formData.append('bnr_link', document.getElementById('bnr_link').value || '');
    formData.append('bnr_mo_link', document.getElementById('bnr_mo_link').value || '');
    formData.append('bnr_target', document.getElementById('bnr_target').value || '_self');
    formData.append('bnr_youtube', document.getElementById('bnr_youtube').value || '');
    formData.append('bnr_start_dt', document.getElementById('bnr_start_dt').value || '');
    formData.append('bnr_end_dt', document.getElementById('bnr_end_dt').value || '');
    formData.append('bnr_status', document.getElementById('bnr_status').value || 'ok');
    
    // 파일 첨부 (MultiFile.js를 사용하는 경우)
    let hasFile = false;
    
    // jQuery를 사용하여 MultiFile.js가 관리하는 파일 입력 필드에서 파일 가져오기 (PC용)
    const $bannerImgInput = $('#banner_img');
    if ($bannerImgInput.length > 0) {
        const fileInput = $bannerImgInput[0];
        
        // 원본 입력 필드에서 파일 확인
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            for (let i = 0; i < fileInput.files.length; i++) {
                formData.append('banner_img[]', fileInput.files[i]);
                hasFile = true;
            }
        }
        
        // 모든 banner_img[] name 속성을 가진 입력 필드 확인
        $('input[name="banner_img[]"]').each(function() {
            const input = this;
            // 원본 입력 필드와 동일한 요소는 이미 처리했으므로 스킵
            if (input === fileInput) {
                return; // continue
            }
            if (input.files && input.files.length > 0) {
                for (let i = 0; i < input.files.length; i++) {
                    formData.append('banner_img[]', input.files[i]);
                    hasFile = true;
                }
            }
        });
    }

    // 모바일용 이미지 파일 처리
    const $bannerMoImgInput = $('#banner_mo_img');
    if ($bannerMoImgInput.length > 0) {
        const fileMoInput = $bannerMoImgInput[0];
        
        // 원본 입력 필드에서 파일 확인
        if (fileMoInput && fileMoInput.files && fileMoInput.files.length > 0) {
            for (let i = 0; i < fileMoInput.files.length; i++) {
                formData.append('banner_mo_img[]', fileMoInput.files[i]);
            }
        }
        
        // 모든 banner_mo_img[] name 속성을 가진 입력 필드 확인
        $('input[name="banner_mo_img[]"]').each(function() {
            const input = this;
            // 원본 입력 필드와 동일한 요소는 이미 처리했으므로 스킵
            if (input === fileMoInput) {
                return; // continue
            }
            if (input.files && input.files.length > 0) {
                for (let i = 0; i < input.files.length; i++) {
                    formData.append('banner_mo_img[]', input.files[i]);
                }
            }
        });
    }

    // 검증: 이미지 또는 유튜브 필수 (수정 모드에서는 기존 이미지가 있으면 생략 가능)
    if (!isEditMode) {
        // 추가 모드에서는 필수
        if (!hasFile && !document.getElementById('bnr_youtube').value) {
            alert('이미지 또는 유튜브 영상 URL 중 하나는 필수입니다.');
            return;
        }
    } else {
        // 수정 모드에서는 새 파일이나 유튜브가 없어도 기존 이미지가 있을 수 있음
        // 서버에서 검증하므로 여기서는 통과
    }

    try {
        const res = await fetch('<?=G5_Z_URL?>/ajax/banner_form_data.php', {
            method: 'POST',
            body: formData
        });
        
        // 응답 텍스트 먼저 가져오기
        const responseText = await res.text();
        const contentType = res.headers.get('content-type');
        
        console.log('=== 서버 응답 정보 ===');
        console.log('상태 코드:', res.status);
        console.log('Content-Type:', contentType);
        console.log('응답 본문 길이:', responseText.length);
        console.log('응답 본문 (처음 500자):', responseText.substring(0, 500));
        
        if (!contentType || !contentType.includes('application/json')) {
            console.error('=== 서버 응답이 JSON이 아닙니다 ===');
            console.error('전체 응답 본문:');
            console.error(responseText);
            
            // HTML 응답인 경우 에러 메시지 추출 시도
            let errorMessage = '서버 응답 오류가 발생했습니다.';
            const errorMatch = responseText.match(/<title[^>]*>([^<]+)<\/title>/i) || 
                              responseText.match(/<h1[^>]*>([^<]+)<\/h1>/i) ||
                              responseText.match(/Fatal error[^<]+/i) ||
                              responseText.match(/Parse error[^<]+/i) ||
                              responseText.match(/Warning[^<]+/i) ||
                              responseText.match(/Notice[^<]+/i);
            
            if (errorMatch) {
                errorMessage += '\n\n오류 내용: ' + errorMatch[1].substring(0, 200);
                console.error('추출된 에러 메시지:', errorMatch[1]);
            }
            
            alert(errorMessage + ' (상태 코드: ' + res.status + ')\n\n전체 응답은 콘솔을 확인해주세요.');
            return;
        }
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON 파싱 오류:', parseError);
            console.error('서버 응답:', responseText);
            alert('서버 응답을 파싱할 수 없습니다.\n\n콘솔을 확인해주세요.');
            return;
        }
        
        // 상세한 에러 정보를 콘솔에 출력
        if (!result.success) {
            console.error('=== 배너 등록 실패 ===');
            console.error('메시지:', result.message);
            
            if (result.error_details) {
                console.error('에러 상세:', result.error_details);
                if (result.error_details.trace) {
                    console.error('스택 트레이스:');
                    result.error_details.trace.forEach(line => console.error('  ' + line));
                }
            }
            
            if (result.php_errors) {
                console.error('PHP 에러:');
                if (result.php_errors.errors && result.php_errors.errors.length > 0) {
                    console.error('  치명적 에러:');
                    result.php_errors.errors.forEach(err => {
                        console.error('    - ' + err.message + ' (파일: ' + err.file + ', 라인: ' + err.line + ')');
                    });
                }
                if (result.php_errors.warnings && result.php_errors.warnings.length > 0) {
                    console.error('  경고:');
                    result.php_errors.warnings.forEach(warn => {
                        console.error('    - ' + warn.message + ' (파일: ' + warn.file + ', 라인: ' + warn.line + ')');
                    });
                }
                if (result.php_errors.notices && result.php_errors.notices.length > 0) {
                    console.error('  알림:');
                    result.php_errors.notices.forEach(notice => {
                        console.error('    - ' + notice.message + ' (파일: ' + notice.file + ', 라인: ' + notice.line + ')');
                    });
                }
            }
            
            if (result.output_buffer) {
                console.error('출력 버퍼 내용:', result.output_buffer);
            }
            
            if (result.last_error) {
                console.error('마지막 에러:', result.last_error);
            }
            
            // 사용자에게는 간단한 메시지 표시, 상세 정보는 콘솔에
            let errorMsg = result.message || '배너 등록에 실패했습니다.';
            if (result.error_details || result.php_errors || result.last_error) {
                errorMsg += '\n\n자세한 오류 정보는 브라우저 개발자 도구(F12)의 콘솔을 확인해주세요.';
            }
            alert(errorMsg);
        } else {
            alert(result.message);
            
            // 수정 모드였으면 확인 없이 폼 초기화
            if (isEditMode) {
                resetBannerForm();
            }
            
            location.reload(); // 페이지 새로고침하여 목록 갱신
        }
    } catch (error) {
        console.error('=== 네트워크/기타 오류 ===');
        console.error('Error:', error);
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        alert('배너 등록 중 오류가 발생했습니다: ' + (error.message || '알 수 없는 오류') + '\n\n콘솔을 확인해주세요.');
    }
}

// 배너 삭제
async function deleteBanner(bnrId) {
    try {
        const formData = new FormData();
        
        // 토큰 추가
        const tokenInput = document.querySelector('input[name="token"]');
        if (tokenInput && tokenInput.value) {
            formData.append('token', tokenInput.value);
        } else {
            // 토큰이 없으면 get_ajax_token() 함수 사용
            if (typeof get_ajax_token === 'function') {
                const token = get_ajax_token();
                if (token) {
                    formData.append('token', token);
                }
            }
        }
        
        formData.append('action', 'delete');
        formData.append('bnr_id', bnrId);
        
        const res = await fetch('<?=G5_Z_URL?>/ajax/banner_form_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await res.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || '배너 삭제에 실패했습니다.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('배너 삭제 중 오류가 발생했습니다.');
    }
}

// 배너 수정 - 상단 폼을 수정 모드로 전환
async function editBanner(bnrId) {
    try {
        const formData = new FormData();
        
        // 토큰 추가
        const tokenInput = document.querySelector('input[name="token"]');
        if (tokenInput && tokenInput.value) {
            formData.append('token', tokenInput.value);
        } else {
            // 토큰이 없으면 get_ajax_token() 함수 사용
            if (typeof get_ajax_token === 'function') {
                const token = get_ajax_token();
                if (token) {
                    formData.append('token', token);
                }
            }
        }
        
        formData.append('action', 'get');
        formData.append('bnr_id', bnrId);
        
        const res = await fetch('<?=G5_Z_URL?>/ajax/banner_form_data.php', {
            method: 'POST',
            body: formData
        });
        
        const responseText = await res.text();
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON 파싱 오류:', parseError);
            console.error('서버 응답:', responseText);
            alert('배너 데이터를 가져올 수 없습니다.');
            return;
        }
        
        if (!result.success) {
            alert(result.message || '배너 데이터를 가져올 수 없습니다.');
            return;
        }
        
        const data = result.data;
        
        // 수정 중인 배너 ID 저장
        document.getElementById('editing_bnr_id').value = bnrId;
        
        // 폼에 데이터 채우기
        document.getElementById('bnr_shop_id').value = data.shop_id || '0';
        document.getElementById('bnr_shop_name').value = data.shop_name || '';
        document.getElementById('bnr_name').value = data.bnr_name || '';
        document.getElementById('bnr_desc').value = data.bnr_desc || '';
        document.getElementById('bnr_link').value = data.bnr_link || '';
        document.getElementById('bnr_mo_link').value = data.bnr_mo_link || '';
        document.getElementById('bnr_target').value = data.bnr_target || '_self';
        document.getElementById('bnr_youtube').value = data.bnr_youtube || '';
        document.getElementById('bnr_start_dt').value = data.bnr_start_dt || '';
        document.getElementById('bnr_end_dt').value = data.bnr_end_dt || '';
        document.getElementById('bnr_status').value = data.bnr_status || 'ok';
        
        // 섬네일 표시
        const previewArea = document.getElementById('banner_preview_area');
        const imgPreview = document.getElementById('banner_img_preview');
        const imgMoPreview = document.getElementById('banner_mo_img_preview');
        const youtubePreview = document.getElementById('banner_youtube_preview');
        const noPreview = document.getElementById('banner_no_preview');
        const imgThumb = document.getElementById('banner_img_thumb');
        const imgMoThumb = document.getElementById('banner_mo_img_thumb');
        const youtubeThumb = document.getElementById('banner_youtube_thumb');
        
        // 기존 섬네일 숨기기
        imgPreview.style.display = 'none';
        imgMoPreview.style.display = 'none';
        youtubePreview.style.display = 'none';
        noPreview.style.display = 'none';
        
        // PC용 이미지 섬네일 표시
        if (data.img_url) {
            imgThumb.src = data.img_url;
            imgPreview.style.display = 'block';
            previewArea.style.display = 'block';
        }
        
        // 모바일용 이미지 섬네일 표시
        if (data.img_mo_url) {
            imgMoThumb.src = data.img_mo_url;
            imgMoPreview.style.display = 'block';
            previewArea.style.display = 'block';
        }
        
        // 유튜브 썸네일 표시
        if (data.youtube_thumb_url) {
            youtubeThumb.src = data.youtube_thumb_url;
            youtubePreview.style.display = 'block';
            previewArea.style.display = 'block';
        }
        
        // 모두 없으면 '이미지 없음' 표시
        if (!data.img_url && !data.img_mo_url && !data.youtube_thumb_url) {
            noPreview.style.display = 'block';
            previewArea.style.display = 'block';
        }
        
        // UI 모드 변경
        document.getElementById('banner_form_title').textContent = '배너 수정: ' + (data.bnr_name || '제목 없음');
        document.getElementById('btn_banner_add').textContent = '수정 완료';
        document.getElementById('btn_banner_cancel').style.display = 'inline-block';
        
        // 폼 영역으로 스크롤
        previewArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // MultiFile 초기화 (파일 입력 필드 초기화)
        $('#banner_img').MultiFile('reset');
        $('#banner_mo_img').MultiFile('reset');
        
    } catch (error) {
        console.error('Error:', error);
        alert('배너 데이터를 불러오는 중 오류가 발생했습니다.');
    }
}

// 배너 폼 초기화 (확인 없이)
function resetBannerForm() {
    // 수정 중인 배너 ID 초기화
    document.getElementById('editing_bnr_id').value = '';
    
    // 폼 초기화
    document.getElementById('bnr_shop_id').value = '0';
    document.getElementById('bnr_shop_name').value = '';
    document.getElementById('bnr_name').value = '';
    document.getElementById('bnr_desc').value = '';
    document.getElementById('bnr_link').value = '';
    document.getElementById('bnr_mo_link').value = '';
    document.getElementById('bnr_target').value = '_self';
    document.getElementById('bnr_youtube').value = '';
    document.getElementById('bnr_start_dt').value = '';
    document.getElementById('bnr_end_dt').value = '';
    document.getElementById('bnr_status').value = 'ok';
    
    // 섬네일 영역 숨기기
    document.getElementById('banner_preview_area').style.display = 'none';
    
    // UI 모드 초기화
    document.getElementById('banner_form_title').textContent = '배너 추가';
    document.getElementById('btn_banner_add').textContent = '배너 추가';
    document.getElementById('btn_banner_cancel').style.display = 'none';
    
    // MultiFile 초기화
    $('#banner_img').MultiFile('reset');
    $('#banner_mo_img').MultiFile('reset');
}

// 배너 수정 취소 (확인 필요)
function cancelEditBanner() {
    if (!confirm('수정을 취소하시겠습니까? 입력한 내용이 모두 초기화됩니다.')) {
        return;
    }
    
    resetBannerForm();
}

// 배너그룹 코드 중복 체크 함수
async function checkBannerCode(code) {
    const checkResult = document.getElementById('bng_code_check_result');
    if (!checkResult) return;
    
    const bngId = <?=isset($bng_id) ? $bng_id : 0?>;
    const w = '<?=$w?>';
    
    try {
        const formData = new FormData();
        formData.append('bng_code', code);
        formData.append('bng_id', bngId);
        formData.append('w', w);
        
        const res = await fetch('<?=G5_Z_URL?>/ajax/banner_code_check.php', {
            method: 'POST',
            body: formData
        });
        
        if (!res.ok) {
            throw new Error('서버 응답 오류: ' + res.status);
        }
        
        const result = (await res.text()).trim(); // 공백 제거
        
        // 디버깅용 (개발 환경에서만)
        if (typeof console !== 'undefined' && console.log) {
            console.log('배너 코드 체크 응답:', result, '코드:', code);
        }
        
        if (result === '1') {
            checkResult.textContent = '사용 가능한 코드입니다.';
            checkResult.style.color = '#0066cc';
            checkResult.className = 'success';
        } else if (result === '0') {
            checkResult.textContent = '이미 사용 중인 코드입니다.';
            checkResult.style.color = '#ff0000';
            checkResult.className = 'error';
        } else {
            // 예상치 못한 응답
            console.error('예상치 못한 응답:', result);
            checkResult.textContent = '확인 중 오류가 발생했습니다.';
            checkResult.style.color = '#ff0000';
            checkResult.className = 'error';
        }
    } catch (error) {
        console.error('Error:', error);
        checkResult.textContent = '확인 중 오류가 발생했습니다.';
        checkResult.style.color = '#ff0000';
        checkResult.className = 'error';
    }
}

// 배너 그룹 폼 validation
function fbannerformcheck(f)
{
    if (!f.bng_code.value.trim()) {
        alert('배너그룹 코드를 입력해 주세요.');
        f.bng_code.focus();
        return false;
    }

    // 배너그룹 코드 형식 검증: 영문으로 시작하고 영문, 숫자, _만 허용
    const codePattern = /^[a-zA-Z][a-zA-Z0-9_]*$/;
    if (!codePattern.test(f.bng_code.value.trim())) {
        alert('배너그룹 코드는 영문으로 시작하고 영문, 숫자, 언더스코어(_)만 사용 가능합니다.');
        f.bng_code.focus();
        return false;
    }

    // 중복 체크 결과 확인 (에러 클래스가 있으면 중복)
    const checkResult = document.getElementById('bng_code_check_result');
    if (checkResult && checkResult.classList.contains('error') && checkResult.textContent.includes('이미 사용 중')) {
        alert('이미 사용 중인 배너그룹 코드입니다. 다른 코드를 입력해주세요.');
        f.bng_code.focus();
        return false;
    }

    if (!f.bng_name.value.trim()) {
        alert('배너그룹명을 입력해 주세요.');
        f.bng_name.focus();
        return false;
    }

    // 날짜 범위 검증
    if (f.bng_start_dt.value && f.bng_end_dt.value) {
        const startDt = new Date(f.bng_start_dt.value);
        const endDt = new Date(f.bng_end_dt.value);
        if (endDt < startDt) {
            alert('종료일시는 시작일시보다 이후여야 합니다.');
            f.bng_end_dt.focus();
            return false;
        }
    }

    return true;
}
</script>

