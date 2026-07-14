#!/usr/bin/env bash
set -e
cd "$(dirname "$0")/../../.."

echo "=== Laravel Pint ==="
./vendor/bin/pint --test
echo "Selesai."
