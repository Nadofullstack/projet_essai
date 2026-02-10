#!/bin/bash
# Test script to check Laravel server

echo "Testing Laravel server..."
echo "========================"

# Test 1: Check if server is running
echo "1. Testing http://localhost:8000/api/health..."
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" http://localhost:8000/api/health

# Test 2: Try test-token endpoint
echo ""
echo "2. Testing http://localhost:8000/api/auth/test-token..."
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" http://localhost:8000/api/auth/test-token

# Test 3: Check if port 8000 is open
echo ""
echo "3. Checking if port 8000 is open..."
netstat -an | grep :8000 || echo "Port 8000 not found in listening ports"

# Test 4: Try with timeout
echo ""
echo "4. Testing with curl (5s timeout)..."
curl -m 5 http://localhost:8000/api/health 2>&1 | head -5
