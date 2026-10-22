#!/usr/bin/env bash
#
# install.sh — bootstrap the QA Toolkit into an existing Laravel app.
#
# Two modes:
#   1) Local dev mode — uses a local copy of this repo as a path repository.
#        cd /path/to/qa-toolkit && ./install.sh /path/to/laravel/app
#      Or run from inside your Laravel app:
#        /path/to/qa-toolkit/install.sh
#
#   2) Production mode — installs from Packagist:
#        cd /path/to/laravel/app && composer require khalequzzaman17/qa-toolkit
#
# What the local dev mode does:
#   1. Copies the package into ./packages/qa-toolkit
#   2. Adds a local path repository to composer.json
#   3. composer require khalequzzaman17/qa-toolkit:dev-main
#   4. Publishes config, stubs, and CI files
#
set -e

TOOLKIT_DIR="$( cd "$( dirname "$0" )" && pwd )"
TARGET_DIR="${1:-$(pwd)}"

if [ ! -f "$TARGET_DIR/composer.json" ] || [ ! -f "$TARGET_DIR/artisan" ]; then
  echo "❌ Not a Laravel app (no composer.json or artisan at $TARGET_DIR)."
  exit 1
fi

echo "→ Creating $TARGET_DIR/packages/qa-toolkit"
mkdir -p "$TARGET_DIR/packages"
rm -rf "$TARGET_DIR/packages/qa-toolkit"
cp -r "$TOOLKIT_DIR" "$TARGET_DIR/packages/qa-toolkit"
rm -rf "$TARGET_DIR/packages/qa-toolkit/packages" 2>/dev/null || true

echo "→ Adding local repository to composer.json"
(cd "$TARGET_DIR" && composer config repositories.toolkit '{"type":"path","url":"./packages/qa-toolkit"}' || true)

echo "→ composer require khalequzzaman17/qa-toolkit:dev-main"
(cd "$TARGET_DIR" && composer require "khalequzzaman17/qa-toolkit:dev-main" --no-interaction)

echo "→ Publishing config + stubs + CI"
(cd "$TARGET_DIR" && php artisan vendor:publish --tag=qa-toolkit-config --force)
(cd "$TARGET_DIR" && php artisan vendor:publish --tag=qa-toolkit-stubs --force)
(cd "$TARGET_DIR" && php artisan vendor:publish --tag=qa-toolkit-ci --force)

echo ""
echo "✅ Done."
echo ""
echo "Toolkit repo:  https://github.com/khalequzzaman17/qa-toolkit"
echo "Maintainer:   Khalequzzaman"
echo "License:      MIT"
echo ""
echo "Run from inside your Laravel app:"
echo "  php artisan qa:run"
echo "  php artisan qa:checks"
