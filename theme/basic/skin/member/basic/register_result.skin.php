<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

?>

<!-- 회원가입 결과 시작 { -->
<div class="max-w-3xl px-4 py-8 mx-auto">
    <!-- 성공 헤더 -->
    <div class="mb-8 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 mb-4 bg-blue-100 rounded-full">
            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1 class="mb-2 text-3xl font-bold text-gray-900">가입 신청이 완료되었습니다</h1>
        <p class="text-lg text-gray-600">
            <strong class="text-blue-600"><?php echo get_text($mb['mb_name']); ?></strong>님, 다인패스 가맹점 회원가입을 신청해 주셔서 감사합니다.
        </p>
    </div>

    <!-- 승인 대기 안내 -->
    <div class="p-6 mb-6 border-l-4 border-yellow-500 rounded-lg bg-yellow-50">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-semibold text-yellow-800">관리자 승인 대기 중</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p class="mb-2">현재 관리자가 제출하신 정보를 검토하고 있습니다.</p>
                    <p>승인이 완료되면 등록하신 연락처로 안내 드리겠습니다.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 승인 프로세스 안내 -->
    <div class="mb-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-6 py-4 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">승인 프로세스</h3>
        </div>
        <div class="px-6 py-4">
            <div class="space-y-4">
                <!-- Step 1 -->
                <div class="flex items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-green-100 rounded-full">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">1. 가입 신청 완료</p>
                        <p class="text-sm text-gray-500">회원 정보 및 사업자등록증 제출 완료</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">2. 관리자 검토 중</p>
                        <p class="text-sm text-gray-500">제출하신 회원 정보와 사업자등록증을 검토하고 있습니다</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full">
                        <span class="text-sm font-semibold text-gray-600">3</span>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">3. 승인 완료 및 안내</p>
                        <p class="text-sm text-gray-500">승인 완료 시 이메일 또는 휴대폰번호로 안내 드립니다</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="flex items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full">
                        <span class="text-sm font-semibold text-gray-600">4</span>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">4. 서비스 이용 시작</p>
                        <p class="text-sm text-gray-500">승인 후 로그인하여 모든 서비스를 이용하실 수 있습니다</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 중요 안내사항 -->
    <div class="mb-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-6 py-4 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">중요 안내사항</h3>
        </div>
        <div class="px-6 py-4 space-y-4 text-sm text-gray-600">
            <div class="flex items-start">
                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p><strong class="text-gray-900">승인 전 서비스 이용 제한:</strong> 관리자 승인이 완료되기 전까지는 로그인 및 서비스 이용이 제한됩니다.</p>
            </div>

            <div class="flex items-start">
                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <p><strong class="text-gray-900">비밀번호 보안:</strong> 회원님의 비밀번호는 아무도 알 수 없는 암호화 코드로 안전하게 저장됩니다.</p>
            </div>

            <div class="flex items-start">
                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p><strong class="text-gray-900">승인 소요 시간:</strong> 승인 검토는 영업일 기준 1~2일 정도 소요될 수 있습니다.</p>
            </div>

            <div class="flex items-start">
                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <p><strong class="text-gray-900">문의사항:</strong> 가입 관련 문의사항이 있으시면 고객센터로 연락 주시기 바랍니다.</p>
            </div>
        </div>
    </div>

    <!-- 버튼 -->
    <div class="flex justify-center gap-4">
        <a href="<?php echo G5_URL ?>/"
            class="px-8 py-3 text-base font-semibold text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            메인으로 돌아가기
        </a>
    </div>
</div>
<!-- } 회원가입 결과 끝 -->