#!/bin/sh
set -e

TEMPLATE="/usr/local/kong/declarative/kong.yml.template"
OUTPUT="/usr/local/kong/declarative/kong.yml"

echo "[custom-entrypoint] Rendering declarative config..."

envsubst < "$TEMPLATE" > "$OUTPUT"

echo "[custom-entrypoint] Completed:"
ls -l /usr/local/kong/declarative

exec /docker-entrypoint.sh kong docker-start
