#!/usr/bin/env bash
set -Eeuo pipefail

if [[ "${EUID}" -ne 0 ]]; then
    echo "Run this script as root (for example: sudo ...)." >&2
    exit 1
fi

if [[ -z "${SSH_ALLOWED_CIDR:-}" ]]; then
    echo "Set SSH_ALLOWED_CIDR to the administrator IP/CIDR before continuing." >&2
    exit 1
fi

if [[ "${CONFIRM_UFW_RESET:-}" != "YES" ]]; then
    echo "This replaces all existing UFW rules. Set CONFIRM_UFW_RESET=YES after reviewing them." >&2
    exit 1
fi

if ! command -v apt-get >/dev/null 2>&1 || ! command -v systemctl >/dev/null 2>&1; then
    echo "This script supports systemd-based Ubuntu/Debian servers only." >&2
    exit 1
fi

if ! systemctl is-active --quiet ssh && ! systemctl is-active --quiet sshd; then
    echo "The SSH service is not active; refusing to change firewall rules." >&2
    exit 1
fi

if ! ss -H -ltn 'sport = :22' | grep LISTEN >/dev/null; then
    echo "Nothing is listening on TCP port 22; refusing to enable this firewall." >&2
    exit 1
fi

python3 - "${SSH_ALLOWED_CIDR}" "${SSH_CLIENT_IP:-}" <<'PY'
import ipaddress
import sys

try:
    allowed_network = ipaddress.ip_network(sys.argv[1], strict=False)
    client_address = sys.argv[2]
    if client_address and ipaddress.ip_address(client_address) not in allowed_network:
        raise ValueError("SSH_CLIENT_IP is outside SSH_ALLOWED_CIDR")
except ValueError as error:
    print(f"Invalid SSH firewall configuration: {error}", file=sys.stderr)
    sys.exit(1)
PY

apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y ufw fail2ban

install -d -m 0755 /etc/fail2ban/jail.d
install -m 0644 "$(dirname "$0")/fail2ban/yelenscol-sshd.local" \
    /etc/fail2ban/jail.d/yelenscol-sshd.local
systemctl enable --now fail2ban
systemctl restart fail2ban

ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow from "${SSH_ALLOWED_CIDR}" to any port 22 proto tcp comment 'YELENSCOL SSH'
ufw allow 80/tcp comment 'HTTP'
ufw allow 443/tcp comment 'HTTPS'
ufw logging low
ufw --force enable

echo "Host firewall and SSH brute-force protection are enabled."
echo "Allowed inbound ports: TCP 22 (restricted to ${SSH_ALLOWED_CIDR}), 80, 443."
echo "Apply the same inbound allow-list in your cloud/provider firewall."
