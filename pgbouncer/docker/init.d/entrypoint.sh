#!/bin/sh
set -e

# Render template dengan envsubst
envsubst < /etc/pgbouncer/pgbouncer.ini.template > /etc/pgbouncer/pgbouncer.ini

# Jalankan PgBouncer
exec pgbouncer /etc/pgbouncer/pgbouncer.ini
