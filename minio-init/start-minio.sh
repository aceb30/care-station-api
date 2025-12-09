#!/bin/sh
set -e

# 1. Iniciar servidor MinIO en segundo plano
minio server /data --console-address ":9001" &

# 2. Esperar a que MinIO esté listo
echo "Waiting for MinIO to start..."
until curl -s http://127.0.0.1:9000/minio/health/live; do
    sleep 2
done

echo "MinIO is up. Configuring bucket..."

# 3. Configurar el cliente 'mc' (MinIO Client)
# Usa las variables de entorno que definiste en docker-compose.yml
mc alias set myminio http://127.0.0.1:9000 "$MINIO_ROOT_USER" "$MINIO_ROOT_PASSWORD"

# 4. Crear el bucket CORRECTO (Coincide con tu AWS_BUCKET del .env)
# El '|| echo' evita que el script falle si el bucket ya existe
mc mb myminio/care-station-bucket 2>/dev/null || echo "Bucket 'care-station-bucket' already exists"

# 5. Hacer el bucket PÚBLICO (Esto soluciona la visibilidad de imágenes)
# 'set download' permite que cualquiera lea/descargue archivos (necesario para la App)
mc policy set download myminio/care-station-bucket

echo "Bucket 'care-station-bucket' configured and policy set to public."

# 6. Mantener el contenedor corriendo
wait