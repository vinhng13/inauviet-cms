#!/usr/bin/env bash
# Cài và cấu hình WordPress cho môi trường LOCAL. Chạy lại nhiều lần vẫn an toàn.
# Dùng: docker compose exec web bash /opt/auviet/setup-wp.sh
set -euo pipefail

DOCROOT=/var/www/vhosts/localhost/html
WP="wp --allow-root --path=${DOCROOT}"
OWNER="1000:1000"

say(){ printf '\n\033[1m== %s\033[0m\n' "$*"; }

say "Kiểm tra phiên bản PHP"
php -v | head -1
if php -r 'exit(version_compare(PHP_VERSION, "8.4.27", "<") ? 1 : 0);'; then
  echo "OK: PHP đã có bản vá PHP2Shell (>= 8.4.27)."
else
  echo "CẢNH BÁO: PHP thấp hơn 8.4.27, chưa vá PHP2Shell."
  echo "Chạy: docker compose build --no-cache web && docker compose up -d"
fi

say "Chờ cơ sở dữ liệu"
for i in $(seq 1 60); do
  if mariadb -h db -u"${DB_USER}" -p"${DB_PASSWORD}" -e 'SELECT 1' "${DB_NAME}" >/dev/null 2>&1; then echo "Đã kết nối."; break; fi
  [ "$i" = 60 ] && { echo "Không kết nối được cơ sở dữ liệu."; exit 1; }
  sleep 2
done

mkdir -p "${DOCROOT}/wp-content/plugins" "${DOCROOT}/wp-content/themes" "${DOCROOT}/wp-content/uploads" "${DOCROOT}/wp-content/languages"

if [ ! -f "${DOCROOT}/wp-load.php" ]; then
  say "Tải WordPress (tiếng Việt)"
  ${WP} core download --locale=vi --skip-content
fi

if [ ! -f "${DOCROOT}/wp-config.php" ]; then
  say "Tạo wp-config.php"
  ${WP} config create --dbname="${DB_NAME}" --dbuser="${DB_USER}" --dbpass="${DB_PASSWORD}" --dbhost=db --locale=vi --skip-check --extra-php <<'PHP'
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'DISALLOW_FILE_EDIT', true );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_POST_REVISIONS', 20 );
PHP
fi

if ! ${WP} core is-installed >/dev/null 2>&1; then
  say "Cài WordPress"
  ${WP} core install --url="${WP_URL}" --title="${WP_TITLE}" \
    --admin_user="${WP_ADMIN_USER}" --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL}" --skip-email
  ${WP} post delete 1 2 --force >/dev/null 2>&1 || true
fi

say "Cài đặt chung"
${WP} option update blog_public 0
${WP} option update timezone_string "Asia/Ho_Chi_Minh"
${WP} option update date_format "d/m/Y"
${WP} option update time_format "H:i"
${WP} option update start_of_week 1
${WP} option update users_can_register 0
${WP} option update default_comment_status closed
${WP} rewrite structure '/%postname%/'

cat > "${DOCROOT}/.htaccess" <<'HT'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
HT

say "Plugin miễn phí"
for p in secure-custom-fields seo-by-rank-math redirection simple-history litespeed-cache; do
  ${WP} plugin is-installed "$p" || ${WP} plugin install "$p"
  ${WP} plugin activate "$p" || echo "Không kích hoạt được $p"
done

# Môi trường local: tắt cache trang để thấy ngay thay đổi khi phát triển.
${WP} plugin deactivate litespeed-cache >/dev/null 2>&1 || true

say "Theme và plugin Âu Việt"
${WP} plugin activate auviet-core
${WP} theme activate auviet
${WP} rewrite flush

say "Tài khoản Biên tập viên"
if ! ${WP} user get "${WP_EDITOR_USER}" >/dev/null 2>&1; then
  ${WP} user create "${WP_EDITOR_USER}" "${WP_EDITOR_EMAIL}" --role=editor --user_pass="${WP_EDITOR_PASSWORD}"
fi

say "Ngôn ngữ"
${WP} language core install vi --activate >/dev/null 2>&1 || true
${WP} language plugin install --all vi >/dev/null 2>&1 || true

FONT_DIR="${DOCROOT}/wp-content/themes/auviet/assets/fonts"
FONT_CSS="${DOCROOT}/wp-content/themes/auviet/assets/css/fonts.css"
if [ ! -s "${FONT_CSS}" ]; then
  say "Tải font về lưu cùng site (Be Vietnam Pro, Bricolage Grotesque)"
  UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36"
  CSS_URL="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Bricolage+Grotesque:opsz,wdth,wght@12..96,75..100,500..800&display=swap"
  if curl -fsSL -A "$UA" "$CSS_URL" -o /tmp/fonts.css; then
    mkdir -p "${FONT_DIR}"
    grep -o 'https://fonts.gstatic.com/[^)]*' /tmp/fonts.css | sort -u | while read -r u; do
      f="$(echo "$u" | md5sum | cut -c1-12).woff2"
      curl -fsSL "$u" -o "${FONT_DIR}/${f}"
      sed -i "s#${u}#../fonts/${f}#g" /tmp/fonts.css
    done
    cp /tmp/fonts.css "${FONT_CSS}"
    echo "Đã lưu font vào theme."
  else
    echo "Không tải được font, theme tạm dùng font hệ thống."
  fi
fi

chown -R "${OWNER}" "${DOCROOT}" 2>/dev/null || true

say "Khởi động lại OpenLiteSpeed (nạp .htaccess cho đường dẫn đẹp)"
/usr/local/lsws/bin/lswsctrl restart >/dev/null 2>&1 && echo "Đã khởi động lại." || echo "Không khởi động lại được, chạy: docker compose restart web"

say "Xong"
echo "Website:       ${WP_URL}"
echo "Quản trị:      ${WP_URL}/wp-admin  (tài khoản trong file .env)"
echo "Hộp thư thử:   http://localhost:8025"
echo "phpMyAdmin:    http://localhost:8081"
