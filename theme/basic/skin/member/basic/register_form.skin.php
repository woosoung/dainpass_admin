<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_ZSQL_PATH . '/shop_category.php');

if (is_file(G5_Z_PATH . '/js/tailwind.min.js')) add_javascript('<script src="' . G5_Z_URL . '/js/tailwind.min.js"></script>', 0);

add_javascript('<script src="' . G5_JS_URL . '/jquery.register_form.js"></script>', 0);
add_javascript('<script src="' . G5_JS_URL . '/jquery.register_form_sub.js"></script>', 0);

// 사업자등록증 파일 업로드 설정
$business_license_max_size = 5; // MB 단위
$business_license_max_size_bytes = $business_license_max_size * 1024 * 1024; // bytes 변환
$business_license_extensions = ['gif', 'jpg', 'jpeg', 'png', 'webp', 'pdf'];
$business_license_accept = '.' . implode(',.', $business_license_extensions); // HTML accept 형식
?>

<!-- 회원정보 입력/수정 시작 -->
<div class="max-w-2xl p-4 mx-auto sm:p-6">

	<!-- 안내 메시지 -->
	<div class="p-4 mb-6 border-l-4 border-blue-500 rounded bg-blue-50">
		<p class="text-sm text-blue-700">
			가맹점 회원가입을 위해 아래 정보를 정확히 입력해주세요.
		</p>
	</div>

	<form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">

		<!-- 로딩 오버레이 -->
		<div id="loading-overlay" class="fixed inset-0 z-50 items-center justify-center hidden bg-gray-900 bg-opacity-50">
			<div class="flex flex-col items-center p-8 bg-white rounded-lg shadow-2xl">
				<div class="w-16 h-16 border-b-4 border-blue-600 rounded-full animate-spin"></div>
				<p class="mt-4 text-lg font-semibold text-gray-700">회원가입 처리중...</p>
				<p class="mt-2 text-sm text-gray-500">잠시만 기다려주세요</p>
			</div>
		</div>

		<!-- Hidden Fields -->
		<input type="hidden" name="w" value="<?php echo $w ?>">
		<input type="hidden" name="url" value="<?php echo $urlencode ?>">
		<input type="hidden" name="agree" value="<?php echo $agree ?>">
		<input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
		<input type="hidden" name="cert_type" value="">
		<input type="hidden" name="cert_no" value="">
		<input type="hidden" name="mb_nick" id="reg_mb_nick" value="">

		<section class="mb-8">
			<h2 class="pb-2 mb-4 text-xl font-bold text-gray-800 border-b-2 border-gray-200">
				기본 정보
			</h2>

			<!-- 아이디 -->
			<div class="mb-4">
				<label for="reg_mb_id" class="block mb-2 text-sm font-semibold text-gray-700">
					아이디
				</label>
				<input type="text" name="mb_id" value="<?php echo $member['mb_id'] ?>" id="reg_mb_id" <?php echo $required ?> <?php echo $readonly ?>
					class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php echo $readonly ? 'bg-gray-100 cursor-not-allowed' : ''; ?>"
					minlength="3" maxlength="20" placeholder="영문자, 숫자, _ 만 입력 (최소 3자)">
				<span id="msg_mb_id" class="block mt-1 text-sm"></span>
			</div>

			<!-- 비밀번호 -->
			<div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
				<div>
					<label for="reg_mb_password" class="block mb-2 text-sm font-semibold text-gray-700">
						비밀번호
					</label>
					<input type="password" name="mb_password" id="reg_mb_password" <?php echo $required ?>
						class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
						minlength="8" maxlength="20" placeholder="영문, 숫자, 특수문자 조합 8자 이상">
					<span id="msg_mb_password" class="block mt-1 text-sm"></span>
				</div>
				<div>
					<label for="reg_mb_password_re" class="block mb-2 text-sm font-semibold text-gray-700">
						비밀번호 확인
					</label>
					<input type="password" name="mb_password_re" id="reg_mb_password_re" <?php echo $required ?>
						class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
						minlength="8" maxlength="20" placeholder="비밀번호 확인">
					<span id="msg_mb_password_re" class="block mt-1 text-sm"></span>
				</div>
			</div>

			<!-- 이메일 -->
			<div class="mb-4">
				<label for="reg_mb_email" class="block mb-2 text-sm font-semibold text-gray-700">
					이메일
				</label>
				<input type="email" name="mb_email" id="reg_mb_email" value="<?php echo isset($member['mb_email']) ? $member['mb_email'] : ''; ?>"
					class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					placeholder="이메일" required>
				<input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
			</div>

			<!-- 휴대폰번호 -->
			<div class="mb-4">
				<label for="reg_mb_hp" class="block mb-2 text-sm font-semibold text-gray-700">
					휴대폰번호
				</label>
				<input type="text" name="mb_hp" id="reg_mb_hp" value="<?php echo get_text($member['mb_hp']) ?>"
					class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					placeholder="휴대폰번호" required>
			</div>
		</section>

		<section class="mb-8">
			<h2 class="pb-2 mb-4 text-xl font-bold text-gray-800 border-b-2 border-gray-200">
				사업자 정보
			</h2>

			<!-- 사업자등록번호 -->
			<div class="mb-4">
				<label for="reg_business_no" class="block mb-2 text-sm font-semibold text-gray-700">
					사업자등록번호
				</label>
				<div class="flex gap-2">
					<input type="text" name="business_no" id="reg_business_no"
						class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
						placeholder="'-'없이 입력'" maxlength="12" required>
					<button type="button" id="btn_business_no_check"
						class="px-6 py-3 text-white transition duration-200 bg-blue-600 rounded-lg hover:bg-blue-700 whitespace-nowrap">
						인증하기
					</button>
				</div>
				<span id="msg_business_no" class="block mt-1 text-sm"></span>
				<input type="hidden" id="business_no_verified" value="0">
			</div>

			<!-- 대표자명 -->
			<div class="mb-4">
				<label for="reg_mb_name" class="block mb-2 text-sm font-semibold text-gray-700">
					대표자명
				</label>
				<input type="text" id="reg_mb_name" name="mb_name" value="<?php echo get_text($member['mb_name']) ?>" <?php echo $required ?> <?php echo $readonly; ?>
					class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php echo $name_readonly ? 'bg-gray-100 cursor-not-allowed' : ''; ?>"
					placeholder="대표자명">
			</div>

			<!-- 업체명 -->
			<div class="mb-4">
				<label for="reg_shop_name" class="block mb-2 text-sm font-semibold text-gray-700">
					업체명
				</label>
				<input type="text" name="shop_name" id="reg_shop_name"
					class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					placeholder="업체명" maxlength="30" required>
			</div>

			<!-- 주소 -->
			<div class="mb-4">
				<label class="block mb-2 text-sm font-semibold text-gray-700">
					주소
				</label>

				<div class="flex gap-2 mb-2">
					<input type="text" name="mb_zip" id="reg_mb_zip" value="<?php echo $member['mb_zip1'] . $member['mb_zip2']; ?>"
						class="w-32 px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
						placeholder="우편번호" readonly required>
					<button type="button"
						onclick="win_zip('fregisterform', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');"
						class="px-6 py-3 text-white transition duration-200 bg-gray-600 rounded-lg hover:bg-gray-700">
						주소 검색
					</button>
				</div>

				<input type="text" name="mb_addr1" id="reg_mb_addr1" value="<?php echo get_text($member['mb_addr1']) ?>"
					class="w-full px-4 py-3 mb-2 bg-gray-100 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					placeholder="기본주소" readonly required>

				<input type="text" name="mb_addr2" id="reg_mb_addr2" value="<?php echo get_text($member['mb_addr2']) ?>"
					class="w-full px-4 py-3 mb-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					placeholder="상세주소">

				<input type="text" name="mb_addr3" id="reg_mb_addr3" value="<?php echo get_text($member['mb_addr3']) ?>"
					class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					placeholder="참고항목" readonly>

				<input type="hidden" name="mb_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']); ?>">
			</div>

			<!-- 업종 선택 -->
			<div class="mb-4">
				<label class="block mb-2 text-sm font-semibold text-gray-700">
					업종
				</label>

				<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
					<div>
						<select name="category_major" id="reg_category_major"
							class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
							required>
							<option value="">대분류 선택</option>
							<?php foreach ($cats as $k1 => $v1) { ?>
								<option value="<?= $k1 ?>"><?= $v1['name'] ?></option>
							<?php } ?>
						</select>
					</div>
					<div>
						<select name="category_id" id="reg_category_minor"
							class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed focus:ring-2 focus:ring-blue-500 focus:border-transparent"
							disabled
							required>
							<option value="">중분류 선택</option>
						</select>
					</div>
				</div>
			</div>

			<!-- 사업자등록증 -->
			<div class="mb-4">
				<label class="block mb-2 text-sm font-semibold text-gray-700">
					사업자등록증
				</label>

				<p class="mb-3 text-xs text-gray-500">
					사업자등록증을 업로드해주세요 (최대 <?php echo $business_license_max_size; ?>MB)
				</p>

				<div id="file_upload_area">
					<!-- 파일 미선택 상태 -->
					<div id="file_select_button" class="flex items-center gap-3">
						<label for="business_license_file"
							class="px-6 py-3 text-sm font-semibold text-white transition duration-200 bg-blue-600 rounded-lg cursor-pointer hover:bg-blue-700">
							파일 선택
						</label>
						<input type="file"
							id="business_license_file"
							name="business_license_file[]"
							accept="<?php echo $business_license_accept; ?>"
							class="hidden">
						<span id="file_name_display" class="text-sm text-gray-500">
							선택된 파일 없음
						</span>
					</div>

					<!-- 파일 선택 상태 -->
					<div id="file_preview" class="hidden">
						<div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-gray-50">
							<div class="flex items-center gap-3">
								<svg class="flex-shrink-0 w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
										d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
								</svg>
								<div class="flex-1 min-w-0">
									<p id="selected_file_name" class="text-sm font-medium text-gray-800 truncate"></p>
									<p id="selected_file_size" class="text-xs text-gray-500"></p>
								</div>
							</div>
							<button type="button"
								id="file_remove_btn"
								class="flex-shrink-0 px-4 py-2 text-sm font-semibold text-red-600 transition border border-red-300 rounded-lg hover:bg-red-50">
								삭제
							</button>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="mb-8">
			<!-- 자동등록방지 -->
			<div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
				<label class="block mb-3 text-sm font-semibold text-gray-700">
					자동등록방지
				</label>
				<?php echo captcha_html(); ?>
			</div>
		</section>

		<!-- 제출 버튼 -->
		<div class="flex gap-4 mt-8">
			<a href="<?php echo G5_URL ?>"
				class="flex-1 px-6 py-4 text-lg font-semibold text-center text-gray-700 transition duration-200 bg-gray-200 rounded-lg hover:bg-gray-300">
				취소
			</a>
			<button type="submit" id="btn_submit"
				class="flex-1 px-6 py-4 text-lg font-semibold text-white transition duration-200 bg-blue-600 rounded-lg hover:bg-blue-700">
				<?php echo $w == '' ? '회원가입' : '정보수정'; ?>
			</button>
		</div>

	</form>
</div>

<script>
	let cats = <?= json_encode($cats) ?>;

	$(function($) {
		let idCheckTimeout = null;

		$("#reg_mb_id").on("input", function() {
			var value = this.value;

			if (value.length === 0) {
				$("#msg_mb_id").html('');
				return;
			}

			if (value.length < 3) {
				$("#msg_mb_id").html('<span class="text-red-600">아이디를 3자 이상 입력하세요.</span>');
				return;
			}

			var pattern = /^[a-zA-Z0-9_]+$/;
			if (!pattern.test(value)) {
				$("#msg_mb_id").html('<span class="text-red-600">영문자, 숫자, _ 만 입력 가능합니다.</span>');
				return;
			}

			clearTimeout(idCheckTimeout);

			idCheckTimeout = setTimeout(function() {
				var msg = reg_mb_id_check();
				if (msg) {
					$("#msg_mb_id").html('<span class="text-red-600">' + msg + '</span>');
				} else {
					$("#msg_mb_id").html('<span class="text-green-600">✓ 사용 가능한 아이디입니다.</span>');
				}
			}, 400);
		});

		$("#reg_mb_id").on("blur", function() {
			clearTimeout(idCheckTimeout);
			var value = this.value;

			if (value.length === 0) {
				$("#msg_mb_id").html('');
				return;
			}

			if (value.length < 3) {
				$("#msg_mb_id").html('<span class="text-red-600">아이디를 3자 이상 입력하세요.</span>');
				return;
			}

			var pattern = /^[a-zA-Z0-9_]+$/;
			if (!pattern.test(value)) {
				$("#msg_mb_id").html('<span class="text-red-600">영문자, 숫자, _ 만 입력 가능합니다.</span>');
				return;
			}

			var msg = reg_mb_id_check();
			if (msg) {
				$("#msg_mb_id").html('<span class="text-red-600">' + msg + '</span>');
			} else {
				$("#msg_mb_id").html('<span class="text-green-600">✓ 사용 가능한 아이디입니다.</span>');
			}
		});

		let pwCheckTimeout = null;
		let prCheckTimeout1 = null;

		$("#reg_mb_password").on("input", function() {
			var value = this.value;
			var passwordReValue = $("#reg_mb_password_re").val();

			if (value.length === 0) {
				$("#msg_mb_password").html('');
				return;
			}

			clearTimeout(pwCheckTimeout);

			pwCheckTimeout = setTimeout(function() {
				var msg = reg_mb_password_check();
				if (msg) {
					$("#msg_mb_password").html('<span class="text-red-600">' + msg + '</span>');
					return;
				} else {
					$("#msg_mb_password").html('<span class="text-green-600">✓ 사용 가능한 비밀번호입니다.</span>');
				}
			}, 400);

			clearTimeout(prCheckTimeout1);

			prCheckTimeout1 = setTimeout(function() {
				if (passwordReValue.length > 0) {
					var msg = reg_mb_password_check();

					if (value === passwordReValue && msg === '') {
						$("#msg_mb_password_re").html('<span class="text-green-600">✓ 비밀번호가 일치합니다.</span>');
					} else {
						$("#msg_mb_password_re").html('<span class="text-red-600">비밀번호가 일치하지 않습니다.</span>');
					}
				}
			}, 400);
		});

		let prCheckTimeout2 = null;
		// 비밀번호 확인 실시간 검증
		$("#reg_mb_password_re").on("input", function() {
			var value = this.value;
			var passwordValue = $("#reg_mb_password").val();

			// 빈 값 처리
			if (value.length === 0) {
				$("#msg_mb_password_re").html('');
				return;
			}

			clearTimeout(prCheckTimeout2);

			prCheckTimeout2 = setTimeout(function() {
				var msg = reg_mb_password_check();

				if (value === passwordValue && msg === '') {
					$("#msg_mb_password_re").html('<span class="text-green-600">✓ 비밀번호가 일치합니다.</span>');
				} else {
					$("#msg_mb_password_re").html('<span class="text-red-600">비밀번호가 일치하지 않습니다.</span>');
				}
			}, 400);
		});

		// 사업자등록번호 형식 자동 완성 및 인증 상태 초기화
		$("#reg_business_no").on("input", function() {
			var value = this.value.replace(/[^0-9]/g, '');

			if (value.length <= 3) {
				this.value = value;
			} else if (value.length <= 5) {
				this.value = value.slice(0, 3) + '-' + value.slice(3);
			} else {
				this.value = value.slice(0, 3) + '-' + value.slice(3, 5) + '-' + value.slice(5, 10);
			}

			// 인증 상태 초기화
			$("#business_no_verified").val("0");
			$("#msg_business_no").html('');
		});

		// 사업자등록번호 인증 버튼 클릭
		$("#btn_business_no_check").on("click", function() {
			var businessNo = $("#reg_business_no").val();

			// 빈 값 체크
			if (!businessNo || businessNo.trim() === '') {
				$("#msg_business_no").html('<span class="text-red-600">사업자등록번호를 입력해 주십시오.</span>');
				$("#reg_business_no").focus();
				return;
			}

			// 형식 체크
			var cleanNo = businessNo.replace(/[^0-9]/g, '');
			if (cleanNo.length != 10) {
				$("#msg_business_no").html('<span class="text-red-600">사업자등록번호는 10자리 숫자여야 합니다.</span>');
				$("#reg_business_no").focus();
				return;
			}

			// AJAX 검증
			$("#msg_business_no").html('<span class="text-gray-500">확인 중...</span>');

			var msg = reg_business_no_check();
			if (msg) {
				$("#msg_business_no").html('<span class="text-red-600">' + msg + '</span>');
				$("#business_no_verified").val("0");
			} else {
				$("#msg_business_no").html('<span class="text-green-600">✓ 인증 완료</span>');
				$("#business_no_verified").val("1");
			}
		});

		const majorCateSelect = document.getElementById('reg_category_major');
		if (majorCateSelect) {
			majorCateSelect.addEventListener('change', function(e) {
				let selectedValue = e.target.value;
				let minorCateSelect = document.getElementById('reg_category_minor');

				// 옵션 초기화
				minorCateSelect.innerHTML = '<option value="">중분류 선택</option>';
				minorCateSelect.value = ''; // 선택 값 초기화

				if (selectedValue !== '') { // 대분류가 선택된 경우
					// 중분류 옵션 추가
					let hasOptions = false;
					for (let ck in cats[selectedValue]['mid']) {
						let opt = document.createElement("option");
						opt.value = ck;
						opt.textContent = cats[selectedValue]['mid'][ck];
						minorCateSelect.appendChild(opt);
						hasOptions = true;
					}

					// 중분류 옵션이 있는 경우에만 활성화
					if (hasOptions) {
						minorCateSelect.disabled = false;
						minorCateSelect.classList.remove('bg-gray-100', 'cursor-not-allowed');
					} else {
						// 중분류 옵션이 없는 경우 비활성화 유지
						minorCateSelect.disabled = true;
						minorCateSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
					}
				} else { // 대분류가 선택 해제된 경우
					// 중분류 비활성화
					minorCateSelect.disabled = true;
					minorCateSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
				}
			});
		}

		// PHP 변수를 JavaScript로 전달
		const BUSINESS_LICENSE_MAX_SIZE = <?php echo $business_license_max_size_bytes; ?>;
		const BUSINESS_LICENSE_EXTENSIONS = <?php echo json_encode($business_license_extensions); ?>;

		// 사업자등록증 파일 업로드 처리
		$('#business_license_file').on('change', function(e) {
			const file = this.files[0];

			if (!file) {
				return;
			}

			const maxSize = BUSINESS_LICENSE_MAX_SIZE;

			// 파일 확장자 검증
			const fileName = file.name.toLowerCase();
			const fileExt = fileName.substring(fileName.lastIndexOf('.') + 1);

			if (!BUSINESS_LICENSE_EXTENSIONS.includes(fileExt)) {
				alert(`허용되지 않는 파일 형식입니다.\n허용 형식: ${BUSINESS_LICENSE_EXTENSIONS.join(', ')}`);
				this.value = '';
				return;
			}

			// 파일 크기 검증
			if (file.size > maxSize) {
				alert(`${(maxSize / 1024 / 1024)}MB 미만의 파일만 업로드 가능합니다.`);
				this.value = '';
				return;
			}

			// 파일 정보 표시
			const fileSize = (file.size / 1024).toFixed(1);
			$('#selected_file_name').text(file.name);
			$('#selected_file_size').text(`${fileSize} KB`);

			// UI 전환: 선택 버튼 숨김, 미리보기 표시
			$('#file_select_button').addClass('hidden');
			$('#file_preview').removeClass('hidden');
		});

		// 파일 삭제 버튼
		$('#file_remove_btn').on('click', function() {
			// 파일 input 초기화
			$('#business_license_file').val('');

			// UI 전환: 미리보기 숨김, 선택 버튼 표시
			$('#file_preview').addClass('hidden');
			$('#file_select_button').removeClass('hidden');
		});
	});

	// Form Submit 검증
	function fregisterform_submit(f) {
		var randomNick = generateRandomNick();
		$("#reg_mb_nick").val(randomNick);

		// 아이디 검증
		if (f.w.value == "") {
			var msg = reg_mb_id_check();
			if (msg) {
				alert(msg);
				f.mb_id.select();
				return false;
			}
		}
		if (f.w.value == "") {
			var msg = reg_mb_nick_check();
			if (msg) {
				alert(msg);
				f.mb_nick.select();
				return false;
			}
		}

		// 비밀번호 검증
		if (f.w.value == "") {
			var msg = reg_mb_password_check();
			if (msg) {
				alert(msg);
				f.mb_password.focus();
				return false;
			}
		}

		if (f.mb_password.value != f.mb_password_re.value) {
			alert("비밀번호가 같지 않습니다.");
			f.mb_password_re.focus();
			return false;
		}

		if (f.mb_password.value.length > 0) {
			if (f.mb_password_re.value.length < 8) {
				alert("비밀번호를 8자 이상 입력하십시오.");
				f.mb_password_re.focus();
				return false;
			}
		}

		// 사업자등록번호 인증 확인
		if ($("#business_no_verified").val() != "1") {
			alert("사업자등록번호 인증을 완료해주세요.");
			$("#reg_business_no").focus();
			return false;
		}

		// 대표자명 검증
		if (f.w.value == "") {
			if (f.mb_name.value.length < 1) {
				alert("대표자명을 입력하십시오.");
				f.mb_name.focus();
				return false;
			}
		}

		// 업체명 검증
		if (!f.shop_name.value) {
			alert("업체명을 입력하십시오.");
			f.shop_name.focus();
			return false;
		}

		// 주소 검증
		if (!f.mb_zip.value || !f.mb_addr1.value) {
			alert("주소를 입력하십시오. 주소 검색 버튼을 이용해주세요.");
			return false;
		}

		// 업종 검증
		if (!f.category_major.value) {
			alert("업종 대분류를 선택하십시오.");
			f.category_major.focus();
			return false;
		}

		if (!f.category_id.value) {
			alert("업종 중분류를 선택하십시오.");
			f.category_id.focus();
			return false;
		}

		if (!f.category_id.value) {
			alert("업종을 올바르게 선택하십시오.");
			f.category_id.focus();
			return false;
		}

		// 이메일 검증
		if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
			var msg = reg_mb_email_check();
			if (msg) {
				alert(msg);
				f.mb_email.select();
				return false;
			}
		}

		// 휴대폰 검증
		var msg = reg_mb_hp_check();
		if (msg) {
			alert(msg);
			f.mb_hp.select();
			return false;
		}

		// 사업자등록증 파일 검증
		if (!$('#business_license_file')[0].files[0]) {
			alert("사업자등록증을 업로드해주세요.");
			return false;
		}

		// Captcha 검증
		<?php echo chk_captcha_js(); ?>

		// 로딩 오버레이 표시
		var loadingOverlay = document.getElementById("loading-overlay");
		loadingOverlay.classList.remove("hidden");
		loadingOverlay.classList.add("flex");

		// Submit 버튼 비활성화
		document.getElementById("btn_submit").disabled = true;

		return true;
	}
</script>

<!-- } 회원정보 입력/수정 끝 -->