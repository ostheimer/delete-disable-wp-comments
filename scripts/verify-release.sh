#!/usr/bin/env bash

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_FILE="$REPO_ROOT/wp-content/plugins/delete-disable-comments/delete-disable-comments.php"
README_FILE="$REPO_ROOT/wp-content/plugins/delete-disable-comments/readme.txt"
ZIP_PATH="$REPO_ROOT/dist/delete-disable-comments.zip"

sha256() {
    if command -v sha256sum >/dev/null 2>&1; then
        sha256sum "$1" | awk '{print $1}'
    else
        shasum -a 256 "$1" | awk '{print $1}'
    fi
}

PACKAGE_VERSION="$(node -p "require('$REPO_ROOT/package.json').version")"
PLUGIN_VERSION="$(
    sed -nE 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+).*/\1/p' "$PLUGIN_FILE" \
        | head -1
)"
STABLE_TAG="$(
    sed -nE 's/^Stable tag:[[:space:]]*([^[:space:]]+).*/\1/p' "$README_FILE" \
        | head -1
)"
EXPECTED_VERSION="${1:-$PACKAGE_VERSION}"

for actual_version in "$PACKAGE_VERSION" "$PLUGIN_VERSION" "$STABLE_TAG"; do
    if [[ "$actual_version" != "$EXPECTED_VERSION" ]]; then
        echo "ERROR: version metadata disagree; expected $EXPECTED_VERSION, found $actual_version." >&2
        exit 1
    fi
done

"$REPO_ROOT/scripts/build-release.sh"
FIRST_HASH="$(sha256 "$ZIP_PATH")"
"$REPO_ROOT/scripts/build-release.sh"
SECOND_HASH="$(sha256 "$ZIP_PATH")"

if [[ "$FIRST_HASH" != "$SECOND_HASH" ]]; then
    echo "ERROR: repeated builds produced different SHA-256 hashes." >&2
    exit 1
fi

unzip -tq "$ZIP_PATH"

ARCHIVE_VERSION="$(
    unzip -p "$ZIP_PATH" delete-disable-comments/delete-disable-comments.php \
        | sed -nE 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+).*/\1/p' \
        | head -1
)"

if [[ "$ARCHIVE_VERSION" != "$EXPECTED_VERSION" ]]; then
    echo "ERROR: release archive contains version $ARCHIVE_VERSION; expected $EXPECTED_VERSION." >&2
    exit 1
fi

printf '%s  delete-disable-comments.zip\n' "$SECOND_HASH" > "$ZIP_PATH.sha256"
echo "Verified reproducible release $EXPECTED_VERSION ($SECOND_HASH)"
