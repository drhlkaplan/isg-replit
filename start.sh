#!/bin/bash
set -e

MYSQL_DATADIR="/home/runner/mysql_data"
MYSQL_SOCKET="/home/runner/mysql_run/mysql.sock"
MYSQL_PIDFILE="/home/runner/mysql_run/mysql.pid"
MYSQL_LOGFILE="/home/runner/mysql_logs/error.log"

mkdir -p /home/runner/mysql_data /home/runner/mysql_run /home/runner/mysql_logs

# Initialize MySQL data directory if not initialized
if [ ! -f "$MYSQL_DATADIR/ibdata1" ]; then
    echo "Initializing MySQL data directory..."
    mysqld --initialize-insecure --user=runner \
        --datadir="$MYSQL_DATADIR" 2>&1
    echo "MySQL data directory initialized."
fi

# Clean up stale socket/pid files
rm -f "$MYSQL_SOCKET" "$MYSQL_SOCKET.lock" "$MYSQL_PIDFILE"

# Start MySQL
echo "Starting MySQL..."
mysqld --user=runner \
    --datadir="$MYSQL_DATADIR" \
    --socket="$MYSQL_SOCKET" \
    --pid-file="$MYSQL_PIDFILE" \
    --log-error="$MYSQL_LOGFILE" \
    --bind-address=127.0.0.1 \
    --port=3306 \
    --skip-name-resolve \
    --mysqlx=OFF &
MYSQL_PID=$!

# Wait for MySQL to be ready
echo "Waiting for MySQL to start..."
for i in $(seq 1 30); do
    if mysql -u root --socket="$MYSQL_SOCKET" -e "SELECT 1;" > /dev/null 2>&1; then
        echo "MySQL is ready!"
        break
    fi
    sleep 1
done

# Setup database if not already done
mysql -u root --socket="$MYSQL_SOCKET" -e "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='isg_lms';" 2>/dev/null | grep -q "isg_lms" || {
    echo "Creating database and importing schema..."
    mysql -u root --socket="$MYSQL_SOCKET" < /home/runner/workspace/db/schema.sql 2>&1
    echo "Database schema imported."
    
    # Run seed if available
    if [ -f "/home/runner/workspace/db/seed_courses.sql" ]; then
        mysql -u root --socket="$MYSQL_SOCKET" isg_lms < /home/runner/workspace/db/seed_courses.sql 2>&1
        echo "Seed data imported."
    fi
}

# Create upload directories
mkdir -p /home/runner/workspace/uploads/scorm \
         /home/runner/workspace/uploads/certificates \
         /home/runner/workspace/uploads/thumbnails \
         /home/runner/workspace/uploads/logos

# Update config to use socket connection
# Start PHP built-in server
echo "Starting PHP server on port 5000..."
cd /home/runner/workspace
exec php -S 0.0.0.0:5000 router.php
