#!/bin/bash

set -euo pipefail

function fail() {
    echo "Error: $1" >&2
    exit 1
}

function require_cmd() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "Missing required command: $1" >&2
        case "$1" in
            node)
                echo "Install Node.js from https://nodejs.org/ and re-run this script." >&2
                ;;
            npm)
                echo "Install npm (usually bundled with Node.js) and re-run this script." >&2
                ;;
            php)
                echo "Install PHP and re-run this script." >&2
                ;;
            *)
                echo "Install $1 and re-run this script." >&2
                ;;
        esac
        exit 1
    fi
}

require_cmd node
require_cmd npm
require_cmd php
require_cmd composer

if [ ! -f package.json ]; then
    fail "package.json not found. Run this script from the repository root."
fi

if [ ! -d client_src ]; then
    fail "client_src directory not found. The Vue source should live in client_src/."
fi

if [ ! -d client_php ]; then
    fail "client_php directory not found. The PHP API should live in client_php/."
fi

if [ ! -d node_modules ]; then
    echo "node_modules is missing. Installing JS dependencies..."
    npm install
fi

if [ ! -d vendor ]; then
    echo "vendor is missing. Installing PHP dependencies..."
    composer install
fi

echo "Building frontend..."
npm run build

if [ ! -d client ]; then
    fail "Build output client/ not found. Build failed or output path changed."
fi

echo "Serving at http://localhost:8000"
echo "Press Ctrl+C to stop."
php -S localhost:8000 -t client serve_router.php
