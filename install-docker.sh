#!/usr/bin/env bash
set -Eeuo pipefail

REPOSITORY="gdspns/yizhangshi-vpn-jc"
BRANCH="${YIZHANGSHI_BRANCH:-master}"
INSTALL_DIR="${YIZHANGSHI_INSTALL_DIR:-/opt/yizhangshi-vpn-jc}"
ADMIN_ACCOUNT="${ADMIN_ACCOUNT:-admin@demo.com}"
COMPOSE_URL="https://raw.githubusercontent.com/${REPOSITORY}/${BRANCH}/compose.sample.yaml"

log() {
  printf '\n[%s] %s\n' "yizhangshi" "$*"
}

die() {
  printf '\n[yizhangshi] ERROR: %s\n' "$*" >&2
  exit 1
}

if [[ "${EUID}" -ne 0 ]]; then
  die "Please run this installer as root (for example: curl ... | sudo bash)."
fi

if ! command -v curl >/dev/null 2>&1; then
  die "curl is required. Install curl and run this command again."
fi

if ! command -v docker >/dev/null 2>&1; then
  log "Docker was not found; installing Docker Engine"
  curl -fsSL https://get.docker.com | sh
fi

docker compose version >/dev/null 2>&1 || die "Docker Compose v2 is required."

if [[ -e "${INSTALL_DIR}/compose.yaml" ]]; then
  die "${INSTALL_DIR}/compose.yaml already exists. Refusing to overwrite an existing installation."
fi

log "Preparing ${INSTALL_DIR}"
install -d -m 0755 \
  "${INSTALL_DIR}/.docker/.data" \
  "${INSTALL_DIR}/storage/logs" \
  "${INSTALL_DIR}/storage/theme" \
  "${INSTALL_DIR}/plugins"
touch "${INSTALL_DIR}/.env"
curl -fsSL "${COMPOSE_URL}" -o "${INSTALL_DIR}/compose.yaml"

cd "${INSTALL_DIR}"

log "Pulling ghcr.io/${REPOSITORY}:latest"
docker compose pull

log "Installing Xboard"
docker compose run --rm -T \
  -e ENABLE_SQLITE=true \
  -e ENABLE_REDIS=true \
  -e ADMIN_ACCOUNT="${ADMIN_ACCOUNT}" \
  xboard php artisan xboard:install

log "Starting services"
docker compose up -d

cat <<EOF

Installation completed.
URL: http://YOUR_SERVER_IP:7001
Directory: ${INSTALL_DIR}

Save the administrator URL and password printed by the installer above.
To update later:
  cd ${INSTALL_DIR} && docker compose pull && docker compose up -d
EOF
