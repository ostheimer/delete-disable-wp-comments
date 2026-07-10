#!/usr/bin/env bash

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_SOURCE="wp-content/plugins/delete-disable-comments"
PACKAGE_NAME="delete-disable-comments"
ZIP_PATH="$REPO_ROOT/dist/$PACKAGE_NAME.zip"
STAGING_ROOT="$(mktemp -d)"
STAGING_PLUGIN="$STAGING_ROOT/$PACKAGE_NAME"

trap 'rm -rf "$STAGING_ROOT"' EXIT

mkdir -p "$STAGING_PLUGIN" "$REPO_ROOT/dist"
rm -f "$ZIP_PATH" "$ZIP_PATH.sha256"

while IFS= read -r -d '' source_path; do
    relative_path="${source_path#"$PLUGIN_SOURCE"/}"
    destination="$STAGING_PLUGIN/$relative_path"
    mkdir -p "$(dirname "$destination")"
    cp "$REPO_ROOT/$source_path" "$destination"
    chmod 0644 "$destination"
    touch -t 198001010000 "$destination"
done < <(cd "$REPO_ROOT" && git ls-files -z -- "$PLUGIN_SOURCE")

find "$STAGING_PLUGIN" -type d -exec chmod 0755 {} +
find "$STAGING_PLUGIN" -type d -exec touch -t 198001010000 {} +

(
    cd "$STAGING_ROOT"
    find "$PACKAGE_NAME" -type f -print \
        | LC_ALL=C sort \
        | zip -X -q "$ZIP_PATH" -@
)

echo "Built $ZIP_PATH"
