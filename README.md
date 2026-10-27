# Website In Quảng Cáo Âu Việt

Mã nguồn website mới inquangcaoauviet.com: WordPress 7.x, theme `auviet`, plugin `auviet-core`.

Tài liệu yêu cầu và kỹ thuật: xem `docs/tai-lieu.md`.

## Chạy trên máy local (Windows, Docker Desktop)

Mở PowerShell tại thư mục này (`D:\Source.Code\Inauviet`) và Docker Desktop đang chạy.

```powershell
# 1. Dựng và khởi động các container (lần đầu mất vài phút)
docker compose up -d --build

# 2. Cài WordPress, plugin, theme, tài khoản (chạy lại nhiều lần vẫn an toàn)
docker compose exec web bash /opt/auviet/setup-wp.sh
```

| Địa chỉ | Dùng để |
| --- | --- |
| http://localhost:8080 | Website |
| http://localhost:8080/wp-admin | Khu quản trị |
| http://localhost:8025 | Mailpit: xem email website gửi ra (không gửi thật) |
| http://localhost:8081 | phpMyAdmin |
| http://localhost:7080 | OpenLiteSpeed WebAdmin |

Tài khoản Quản trị viên, Biên tập viên và mật khẩu cơ sở dữ liệu nằm trong file `.env`
(sinh ngẫu nhiên, **không** đưa lên Git). Hãy chuyển chúng vào trình quản lý mật khẩu.

Mọi cổng chỉ mở cho chính máy này (127.0.0.1).

## Lệnh hay dùng

```powershell
docker compose ps                     # xem trạng thái
docker compose logs -f web            # xem log web server
docker compose stop                   # tạm dừng
docker compose start                  # chạy lại
docker compose exec web wp --allow-root --path=/var/www/vhosts/localhost/html plugin list   # WP-CLI
docker compose build --no-cache web   # dựng lại image (khi cần cập nhật PHP)
docker compose down -v                # XOÁ HẲN môi trường local, kể cả dữ liệu
```

## Cấu trúc thư mục

```
docker-compose.yml        Các dịch vụ: web (OpenLiteSpeed + PHP 8.4), db (MariaDB), mailpit, phpmyadmin
docker/web/Dockerfile     Image web: nâng lsphp84 lên bản vá mới nhất, WP-CLI, cấu hình PHP
docker/web/setup-wp.sh    Cài và cấu hình WordPress
theme/auviet/             Theme của Âu Việt (block theme) — gắn thẳng vào container, sửa là thấy ngay
plugins/auviet-core/      Plugin chức năng riêng: loại nội dung, phân quyền, bảng giá, báo giá
mu-plugins/               Plugin bắt buộc cho môi trường thử (email vào Mailpit)
.env                      Mật khẩu local (không đưa lên Git); mẫu ở .env.example
```

WordPress core, plugin cài từ kho và ảnh tải lên nằm trong volume Docker `wp_html`,
cơ sở dữ liệu trong volume `db_data` — không nằm trong thư mục này.

## Quy tắc

- Chỉ sửa code trong `theme/auviet` và `plugins/auviet-core`, rồi commit Git.
- Không sửa code trực tiếp trên máy chủ.
- Môi trường đặt bằng `WP_ENVIRONMENT_TYPE` (`local`, `staging`, `production`);
  ngoài `production`, site tự chặn Google index và email đi vào Mailpit.
- PHP phải từ 8.4.27 trở lên (bản vá PHP2Shell); `setup-wp.sh` kiểm tra và cảnh báo.
