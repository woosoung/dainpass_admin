아래는 **커서(Cursor) 환경에서 다인패스 관리자사이트(본사/가맹점 통합 관리자)의 개발을 완전하게 실수 없이 수행할 수 있도록 정리한 “명확하고 개발자 지향적인 지침서 구조”**입니다.

즉, 이 지침은 *커서의 자동 코드 생성·리팩토링* 특성을 고려하여, 커서가 맥락을 잃지 않고 정확한 백엔드/프론트엔드/DB 연동 규칙을 따르도록 만드는 데 최적화되어 있습니다.

---

# 다인패스 관리자사이트 개발을 위한 **Cursor 개발 지침서 구성안(제공용 문서 Blueprint)**

※ 커서에 직접 입력할 "지침서"로 작성해도 되는 형태입니다.

---

## 1. 프로젝트 목적 및 전체 구조

### **프로젝트 목적**

다인패스 플랫폼은
**AI 기반 다중선택 통합 예약·결제 플랫폼**이며,
관리자사이트는 다음 2가지 역할을 통합합니다:

1. **본사 관리자(Admin)**

* 전체 가맹점 관리
* 예약 현황/매출/정산 관리
* 사용자 관리
* 플랫폼 내 정적/동적 콘텐트 관리

2. **가맹점 관리자(Shop Owner)**

* 자기 매장 정보 관리
* 서비스/직원/영업시간 관리
* 예약 접수/변경/취소 처리
* 고객 쿠폰/포인트/이벤트 관리

→ **즉, 그누보드의 관리자 UI/UX를 최대한 유지하면서 PostgreSQL 기반의 예약·경로 최적화 플랫폼을 관리할 수 있는 통합 어드민을 구축하는 것이 최종 목표**.

---

## 2. 개발 환경 핵심 원칙

### 서버 & 시스템

* NCP Cloud Server
* 관리자는 **Docker Ubuntu 22.04** + **PHP 8.3 + 그누보드(YC5)**
* PostgreSQL 14 메인 DB
* MySQL8 — g5_member, g5_config 등 최소한만 사용

### 그누보드 구조 원칙

* **절대 core 파일 직접 수정 금지**
* 모든 확장 기능은 `extend/` 또는 `adm/_z01/` 내부에서 hook 방식으로 구현
* 기존 관리자 UI를 유지하면서 기능만 확장

---

## 3. 데이터베이스 연동 규칙

### MySQL

* 사용하는 테이블:
  `g5_member`, `g5_auth`, `g5_board`, `g5_config`, `g5_mail`

### PostgreSQL 14

* 모든 **예약/가맹점/AI 추천/업종/스케줄/쿠폰/이벤트/정산** 관련 데이터는 PostgreSQL에 저장
* 기본 문법 규칙:

  * `AUTO_INCREMENT` → `GENERATED ALWAYS AS IDENTITY`
  * `datetime` → `timestamp`
  * `tinyint` → `boolean`
  * 문자열은 기본 `text` 또는 `varchar(...)`
  * 작성 SQL은 반드시 PostgreSQL 14 기준

### PostgreSQL 연결

* `extend/z.03.pgconfig.php` + `extend/z.04.function.php` 활용
* 반드시 커서에게 다음 점 강조:

  * 모든 PG SQL은 `sql_query_pg()`로 실행
  * Insert ID는 `sql_insert_id_pg()` 사용
  * Blind SQL Injection 대응이 되어 있으니 동일 패턴 유지

---

## 4. 테이블 연동 모델 설계 원칙

### 핵심 연동 구조

```
customers (PostgreSQL)
    ↓
customer_reservations / shop_appointments / appointment_shop_detail
```

### 관리자 기능별 주요 PG 테이블

* 회원: `customers`, `customer_reservations`
* 가맹점: `shop`, `shop_categories`, `shop_services`
* 직원/시간표: `shop_staff`, `business_hour_slots`, `business_exceptions`
* 예약: `shop_appointments`, `appointment_shop_detail`, `appointment_staff_detail`
* 쿠폰/포인트: `coupons`, `customer_coupons`
* 검색/AI 추천: `keywords`, `shop_keyword`, `shop_search_refresh_queue`

---

## 5. 관리자 UI/UX 구성 원칙

### 관리자 메뉴 구조 기준안

1. 회원 관리
2. 예약 관리
3. 가맹점 관리
4. 업종/서비스 관리
5. 쿠폰/포인트/이벤트
6. 리뷰 관리
7. 정산 관리
8. 통계 & 대시보드 (SQL View 또는 materialized view 활용)
9. 시스템 설정 (meta / setting)

### UI 원칙

* 기존 그누보드 관리자 UI 스타일 최대한 유지
* Tailwind Utility Class는 `adm/_z01/css/_adm_tailwind_utility_class.php`에서 로드
* 폼/리스트 페이지는 `adm/_z01`,`adm/_z01/_adm`,`adm/_z01/_adm/_shop_admin`에 동일 패턴으로 생성
* AJAX 요청은 `adm/_z01/ajax`에 배치하는 패턴 유지

---

## 6. 기능 확장 및 권한 설계


실제 권한 구조는 g5_member 의 `mb_level`, `mb_1`: shop_id, `mb_2`: shop_manager_yn, `mb_3`: shop_staff_yn 과 조합하여 처리.

---

## 7. Cursor 코드 생성 규칙(최중요)

커서에게 반드시 지켜야 할 “코드 자동생성 규칙”을 명확히 설정해야 합니다.

### ① PHP 작성 규칙

* 신규 파일은 모두 `adm/_z01` 또는 `adm/_z01/_adm` 또는 `adm/_z01/_adm/_shop_admin` 또는 `adm/_z01/ajax` 내부에 생성
* DB 쿼리는

  * MySQL → `sql_query()`
  * PostgreSQL → `sql_query_pg()`
* GET/POST 파라미터는 모두 `clean_xss_tags()` 적용
* return 시 JSON 응답 기본 템플릿 유지

### ② SQL 작성 규칙

* PostgreSQL 14 문법 준수
* identity column 사용
* timestamp without time zone 기본
* text 검색은 `tsvector` 기반
* 검색 엔진 갱신은 반드시:

  ```
  PERFORM enqueue_shop_refresh(shop_id);
  ```

### ③ 관리자 UI 생성 규칙

* UI는 그누보드 관리자 레이아웃을 그대로 상속
* 목록 페이지는 테이블 구조 고정 패턴
* 상세 페이지는 동일 폼 컴포넌트 스타일 사용
* CSS는 `adm/_z01/css/` 내 override 파일에만 추가

---

## 8. 예약 모듈 개발 규칙

### 예약정보는 반드시 PostgreSQL 기준

테이블:

* shop_appointments
* appointment_shop_detail
* appointment_staff_detail
* reservation_slots

### 예약번호 생성 규칙

(트리거로 이미 구현됨 — 수정 금지)

* AFTER INSERT 트리거에서 appointment_no 자동 생성
패턴:
`YYYYMMDD + 8자리 appointment_id 패딩`

---

## 9. 외부 API 연동

### 문자발송 — Naver SENS API

* 모든 네이버 SENS API 호출은 `./docs/naver_sens`안에 있는 파일자료의 내용을 준수
* 관리자 알림/예약 취소/지연 도착 안내 등에 활용
* PHP에서 CURL 예제 구조 동일하게 유지

### 지도/경로/ETA

* Tmap / Kakao / Naver Maps
  → 서버 내에서 비용 계산/ETA 요청할 때 사용

### AI 추천

* NCP AI Recommendation API + 내부 ML 모델
* 관리자사이트에서는 “추천결과”만 뷰로 조회

---

## 10. 개발 시 피해야 할 실수 목록(커서에게 매우 유용)

1. 그누보드 core 파일 직접 수정 금지
2. MySQL에 예약/매장/쿠폰 저장 금지
3. PostgreSQL identity 대신 serial 사용 금지
4. PHP 파일을 관리자 루트에 만들어 구조 깨지게 하지 말 것
5. SQL에서 datetime 사용 금지
6. 예약/매장 insert 시 검색 인덱스 갱신 로직 누락 금지
7. 그누보드 세션/권한 로직 우회 금지