# Java Gateway Services 사용 가이드

## 생성된 서비스

### 1. java_login_check (포트: 8090)
- **목적**: 로그인 상태를 엄격하게 확인하는 Gateway
- **기능**: JWT 토큰 검증 → FastAPI 서버 프록시
- **인증**: 필수 (유효한 JWT 토큰 필요)
- **기술 스택**: Spring Boot 3.4.5, Java 21

### 2. java_logout (포트: 8091)  
- **목적**: 로그아웃 처리 및 관련 요청을 처리하는 Gateway
- **기능**: 관대한 토큰 검증 → FastAPI 서버 프록시
- **인증**: 관대함 (만료된 토큰도 허용)
- **기술 스택**: Spring Boot 3.4.5, Java 21

## 실행 방법

### 개별 실행
```bash
# Login Check Gateway
cd java_login_check
mvn spring-boot:run

# Logout Gateway  
cd java_logout
mvn spring-boot:run
```

### JAR 파일로 실행
```bash
# 빌드
cd java_login_check && mvn clean package
cd ../java_logout && mvn clean package

# 실행
java -jar java_login_check/target/java-login-check-1.0.0.jar &
java -jar java_logout/target/java-logout-1.0.0.jar &
```

## 사용 예시

### 1. 로그인 확인 Gateway (8090)

```bash
# FastAPI 서버에 인증된 요청 프록시
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"data": "분석할 데이터"}' \
     http://localhost:8090/api/analyze

# API Gateway를 통한 접근
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     http://localhost:8000/gateway/login/api/user/profile
```

### 2. 로그아웃 Gateway (8091)

```bash
# 로그아웃 요청 (토큰이 만료되었어도 허용)
curl -H "Authorization: Bearer EXPIRED_OR_INVALID_TOKEN" \
     -X POST \
     http://localhost:8091/api/logout

# 토큰 없는 요청도 허용
curl -X POST http://localhost:8091/api/session/clear

# API Gateway를 통한 접근
curl -X POST http://localhost:8000/gateway/logout/api/logout
```

# 상품 추천 (백엔드 서비스)
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     http://localhost:9001/api/recommend-service/recommendations
```

### 2. 로그아웃 Gateway (9002)

```bash
# 로그아웃 처리
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     -X POST http://localhost:9002/logout

# 로그아웃 (토큰 없이도 가능)
curl -X POST http://localhost:9002/logout

# 사용자 데이터 정리 (FastAPI)
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"action": "cleanup"}' \
     http://localhost:9002/fastapi/user-cleanup
```

## 아키텍처

```
클라이언트 앱
     ↓
┌─────────────────────┐    ┌─────────────────────┐
│  Login Check GW     │    │   Logout GW         │
│  (포트: 9001)       │    │  (포트: 9002)       │  
│  - 엄격한 JWT 검증   │    │  - 관대한 JWT 검증   │
└─────────────────────┘    └─────────────────────┘
     ↓                           ↓
┌─────────────────────────────────────────────────┐
│            백엔드 마이크로서비스                  │
│  user-service | catalog-service | order-service │
│  mobile-service | recommend-service             │
└─────────────────────────────────────────────────┘
     ↓
┌─────────────────────────────────────────────────┐
│                FastAPI 서버                     │
└─────────────────────────────────────────────────┘
```

## 특징

### Login Check Gateway
- ✅ 엄격한 인증: 유효한 JWT 토큰 필수
- ✅ 사용자 정보 헤더 추가: X-User-Subject, X-User-Customer-Id
- ✅ 모든 요청 로깅
- ✅ 백엔드 서비스 및 FastAPI 프록시

### Logout Gateway
- ✅ 관대한 인증: 만료된 토큰에서도 사용자 정보 추출
- ✅ 로그아웃 성공 우선: 인증 실패해도 로그아웃 성공 처리
- ✅ 로그아웃 로그 기록: 백엔드 user-service에 로그 전송
- ✅ 정리 작업 지원: FastAPI 서버의 사용자 데이터 정리 등

## 포트 할당

- `9001`: python_login_check (로그인 확인 Gateway)
- `9002`: python_logout (로그아웃 Gateway)
- `8000`: 기존 API Gateway (건드리지 않음)
- `8001-8005`: 기존 백엔드 서비스들 (건드리지 않음)
- `8080`: FastAPI 서버 (별도 구축 예정)