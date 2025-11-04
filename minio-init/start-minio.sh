#!/bin/sh
set -e

# Start MinIO server in the background
minio server /data --console-address ":9001" &

# Wait for MinIO to be ready
echo "Waiting for MinIO to start..."
until curl -s http://127.0.0.1:9000/minio/health/live; do
    sleep 2
done

echo "MinIO is up. Creating bucket..."

# Configure mc
mc alias set myminio http://127.0.0.1:9000 $MINIO_ROOT_USER $MINIO_ROOT_PASSWORD

# Create bucket if it doesn't exist
mc mb myminio/app-files 2>/dev/null || echo "Bucket already exists"

# Set bucket policy to public
mc policy set download myminio/app-files

echo "Bucket 'app-files' created and policy set to public."

# Wait indefinitely to keep MinIO running in the foreground
wait
