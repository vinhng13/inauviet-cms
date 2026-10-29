ARG OLS_VERSION=1.9.2
FROM litespeedtech/openlitespeed:${OLS_VERSION}-lsphp84

# 1) Nâng cấp lsphp84 lên bản mới nhất trong kho LiteSpeed (cần >= 8.4.27 để vá PHP2Shell)
# 2) Công cụ phục vụ WP-CLI
RUN apt-get update \
 && PKGS="$(dpkg -l 'lsphp84*' 2>/dev/null | awk '/^ii/{print $2}' | tr '\n' ' ')" \
 && (apt-get install -y --only-upgrade ${PKGS} || echo "CANH BAO: khong nang cap duoc lsphp84") \
 && apt-get install -y --no-install-recommends mariadb-client less unzip curl ca-certificates \
 && rm -rf /var/lib/apt/lists/*

# php và wp-cli cho dòng lệnh
RUN if [ ! -e /usr/local/bin/php ]; then ln -s /usr/local/lsws/lsphp84/bin/php /usr/local/bin/php; fi \
 && if ! command -v wp >/dev/null 2>&1; then \
      curl -fsSL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
      && chmod +x /usr/local/bin/wp; fi

# Cấu hình PHP: tải file 20 MB, tắt session.upload_progress (biện pháp bổ sung cho PHP2Shell)
RUN INI_DIR="$(php --ini | sed -n 's/^Scan for additional .ini files in: //p')" \
 && if [ -d "$INI_DIR" ]; then printf '%s\n' \
      'upload_max_filesize = 20M' \
      'post_max_size = 25M' \
      'memory_limit = 256M' \
      'max_execution_time = 120' \
      'session.upload_progress.enabled = Off' \
      'expose_php = Off' > "$INI_DIR/99-auviet.ini"; fi
