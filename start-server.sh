#!/bin/bash

# Sendana PHP Server Startup Script

echo "Starting Sendana Backend Server..."
echo ""
echo "Server will be available at: http://localhost:8000"
echo "Frontend will be accessible at: http://localhost:8000/frontend/pages/"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server
php -S localhost:8000 -t . router.php
