#!/bin/bash

echo "Starting Python Gateway Services..."

# python_login_check 서비스 실행
echo "Starting Login Check Gateway on port 9001..."
cd /Users/tomasjoa/Downloads/msa/python_login_check
pip install -r requirements.txt
python main.py &
LOGIN_PID=$!

# python_logout 서비스 실행  
echo "Starting Logout Gateway on port 9002..."
cd /Users/tomasjoa/Downloads/msa/python_logout
pip install -r requirements.txt
python main.py &
LOGOUT_PID=$!

echo "Gateway services started!"
echo "Login Check Gateway: http://localhost:9001"
echo "Logout Gateway: http://localhost:9002"
echo ""
echo "To stop services:"
echo "kill $LOGIN_PID $LOGOUT_PID"

# PID 파일에 저장
echo "$LOGIN_PID" > /Users/tomasjoa/Downloads/msa/login_gateway.pid
echo "$LOGOUT_PID" > /Users/tomasjoa/Downloads/msa/logout_gateway.pid

wait