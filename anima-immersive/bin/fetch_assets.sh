#!/usr/bin/env bash
set -euo pipefail

ASSET_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")"/.. && pwd)/assets"
mkdir -p "$ASSET_DIR"

if [[ -z "${NEXT_PUBLIC_CDN:-}" ]]; then
  echo "[fetch_assets] NEXT_PUBLIC_CDN not set. Skipping asset download." >&2
  exit 0
fi

# Example asset list - adjust or extend as needed.
ASSETS=(
  "demo/scene-config.json"
  "demo/textures/placeholder.jpg"
)

for asset in "${ASSETS[@]}"; do
  url="${NEXT_PUBLIC_CDN%/}/$asset"
  target="$ASSET_DIR/$(basename "$asset")"
  echo "[fetch_assets] Downloading $url -> $target"
  if command -v curl >/dev/null 2>&1; then
    curl -fsSL "$url" -o "$target" || echo "[fetch_assets] Failed to download $url" >&2
  elif command -v wget >/dev/null 2>&1; then
    wget -q "$url" -O "$target" || echo "[fetch_assets] Failed to download $url" >&2
  else
    echo "[fetch_assets] Neither curl nor wget available. Skipping." >&2
    exit 0
  fi
done
