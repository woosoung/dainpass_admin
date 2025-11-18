# FastAPI Authentication Client
# FastAPI 서버에서 MSA 인증 시스템과 직접 연동하기 위한 클라이언트

import httpx
import jwt
import asyncio
from datetime import datetime
from typing import Optional, Dict, Any
from dataclasses import dataclass

@dataclass
class UserInfo:
    user_id: str
    username: str
    customer_id: str
    email: str
    exp: int

class MSAAuthClient:
    def __init__(self, 
                 user_service_url: str = "http://user-service:8080",
                 jwt_secret: str = "user_token_native_user_service_dev",
                 jwt_algorithm: str = "HS512"):
        self.user_service_url = user_service_url
        self.jwt_secret = jwt_secret
        self.jwt_algorithm = jwt_algorithm
        self.client = httpx.AsyncClient()

    async def verify_token_local(self, token: str) -> Optional[UserInfo]:
        """
        로컬 JWT 검증 (가장 빠름)
        Gateway 없이 바로 토큰 유효성 확인
        """
        try:
            payload = jwt.decode(token, self.jwt_secret, algorithms=[self.jwt_algorithm])
            
            # 만료 시간 확인
            if datetime.fromtimestamp(payload['exp']) < datetime.now():
                return None
                
            # 토큰 카테고리 확인
            if payload.get('category') != 'access':
                return None
                
            return UserInfo(
                user_id=payload.get("userId"),
                username=payload.get("sub"),
                customer_id=payload.get("customerId"),
                email=payload.get("email", ""),
                exp=payload.get("exp")
            )
        except jwt.InvalidTokenError:
            return None

    async def verify_token_remote(self, token: str) -> Optional[UserInfo]:
        """
        원격 토큰 검증 (데이터베이스 확인 포함)
        User Service의 introspection API 호출
        """
        try:
            response = await self.client.post(
                f"{self.user_service_url}/api/token/introspect",
                headers={"Authorization": f"Bearer {token}"}
            )
            
            if response.status_code == 200:
                data = response.json()
                if data.get("active"):
                    return UserInfo(
                        user_id=data.get("user_id"),
                        username=data.get("username"),
                        customer_id=data.get("customer_id"),
                        email=data.get("email", ""),
                        exp=data.get("exp")
                    )
            return None
        except Exception as e:
            print(f"Remote token verification failed: {e}")
            return None

    async def verify_token_fast(self, token: str) -> Optional[Dict[str, Any]]:
        """
        빠른 토큰 검증 (기본 유효성만 확인)
        """
        try:
            response = await self.client.post(
                f"{self.user_service_url}/api/token/validate",
                headers={"Authorization": f"Bearer {token}"}
            )
            
            if response.status_code == 200:
                data = response.json()
                return data if data.get("valid") else None
            return None
        except Exception:
            return None

    async def close(self):
        """클라이언트 종료"""
        await self.client.aclose()


# FastAPI에서 사용 예제
from fastapi import FastAPI, Depends, HTTPException, Header
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials

app = FastAPI()
security = HTTPBearer()
auth_client = MSAAuthClient()

async def get_current_user(credentials: HTTPAuthorizationCredentials = Depends(security)) -> UserInfo:
    """
    FastAPI Dependency: 현재 로그인한 사용자 정보 반환
    """
    token = credentials.credentials
    
    # 1차: 로컬 JWT 검증 (빠름)
    user_info = await auth_client.verify_token_local(token)
    if user_info:
        return user_info
    
    # 2차: 원격 검증 (정확함)
    user_info = await auth_client.verify_token_remote(token)
    if user_info:
        return user_info
    
    raise HTTPException(status_code=401, detail="Invalid or expired token")

async def get_current_user_optional(authorization: str = Header(None)) -> Optional[UserInfo]:
    """
    FastAPI Dependency: 선택적 인증 (로그아웃 시나리오용)
    """
    if not authorization or not authorization.startswith("Bearer "):
        return None
    
    token = authorization[7:]
    return await auth_client.verify_token_local(token)

# 사용 예제 엔드포인트
@app.get("/api/profile")
async def get_profile(current_user: UserInfo = Depends(get_current_user)):
    """인증 필요한 API"""
    return {
        "user_id": current_user.user_id,
        "username": current_user.username,
        "customer_id": current_user.customer_id
    }

@app.post("/api/logout")
async def logout(current_user: Optional[UserInfo] = Depends(get_current_user_optional)):
    """로그아웃 API (토큰이 만료되어도 허용)"""
    if current_user:
        return {"message": f"User {current_user.username} logged out"}
    else:
        return {"message": "Anonymous logout"}

@app.on_event("shutdown")
async def shutdown_event():
    await auth_client.close()