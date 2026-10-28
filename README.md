# Website In Quảng Cáo Âu Việt

Mã nguồn website mới inquangcaoauviet.com: WordPress 7.x, theme `auviet`, plugin `auviet-core`.

Tài liệu yêu cầu và kỹ thuật: xem `docs/tai-lieu.md`.

## Chạy trên máy local (Windows, Docker Desktop)

Yêu cầu: Docker Desktop đang chạy (“Engine running”), WSL 2 đã cài (`wsl --status` hiện “Default Version: 2”).
Mở PowerShell tại thư mục này (`D:\Source.Code\Inauviet`).

```powershell
# 1. Dựng và khởi động các container (lần đầu mất vài phút)
docker compose up -d --build

# 2. Cài WordPress, plugin, theme, tài khoản (chạy lại nhiều lần vẫn an toàn)
docker compose exec web bash /opt/auviet/setup-wp.sh
```

Nếu dùng **Git Bash** thay cho PowerShell, lệnh thứ hai phải chạy như sau
(Git Bash tự đổi đường dẫn `/opt/...` và không có TTY chuẩn):

```bash
MSYS_NO_PATHCONV=1 docker compose exec -T web bash /opt/auviet/setup-wp.sh
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

## Quản trị trang chủ

| Muốn sửa | Vào đâu | Ai làm được |
|---|---|---|
| Chữ, ảnh, thứ tự các khối trên trang chủ | Trang › **Trang chủ** (trình soạn khối; khối mẫu ở nhóm "Âu Việt") | Biên tập viên, Quản trị viên |
| Banner vàng theo mùa (bật/tắt, nội dung, ngày hiện) | **Âu Việt › Banner chiến dịch** | Biên tập viên, Quản trị viên |
| Chữ, thứ tự khối trang Liên hệ (/lien-he/) | Trang › **Liên hệ** | Biên tập viên, Quản trị viên |
| Trang Giới thiệu (/gioi-thieu/): chữ, thứ tự khối | Trang › **Giới thiệu Âu Việt** | Biên tập viên, Quản trị viên |
| Năm thành lập, 4 số liệu nổi bật | **Âu Việt › Giới thiệu** | Biên tập viên, Quản trị viên |
| Trang Dịch vụ thiết kế (/thiet-ke/) và từng loại thiết kế (trang con, mẫu "Trang loại thiết kế", ảnh mẫu trong hộp "Ảnh mẫu") | Trang › **Dịch vụ thiết kế** | Biên tập viên, Quản trị viên |
| Phí thiết kế, số lần chỉnh sửa, thời gian, tư liệu cần chuẩn bị | **Âu Việt › Thiết kế** | Biên tập viên, Quản trị viên |
| Máy móc, thiết bị (ảnh, loại, thông số) | **Âu Việt › Máy móc, thiết bị** | Biên tập viên, Quản trị viên |
| Logo khách hàng (chỉ hiện khi khách đồng ý nêu tên; chọn hiện ở trang chủ) | **Âu Việt › Khách hàng tiêu biểu** | Biên tập viên, Quản trị viên |
| Hotline, Zalo, Facebook, email, địa chỉ, giờ làm việc, mã số thuế, vị trí bản đồ, đội tư vấn, email nhận báo giá | **Âu Việt › Liên hệ & công ty** | Quản trị viên |
| Sản phẩm / Mẫu đã làm hiện trên trang chủ | **Sản phẩm**, **Mẫu đã làm** (8 sản phẩm, 3 mẫu mới nhất) | Biên tập viên, Quản trị viên |
| Yêu cầu báo giá khách gửi | **Yêu cầu báo giá** (đổi trạng thái, người phụ trách, ghi chú; có lịch sử) | Quản trị viên |
| Sản phẩm: mô tả, ảnh, quy cách, chất liệu, nhóm chính, sản phẩm liên quan, hỏi đáp | **Sản phẩm** (ô "Thứ tự" nhỏ hơn thì lên trước; 1–8 hiện ở trang chủ) | Biên tập viên, Quản trị viên |
| Bảng giá tham khảo, số lượng tối thiểu, thời gian sản xuất (có lịch sử sửa giá) | **Sản phẩm** › hộp "Bảng giá tham khảo" | Quản trị viên |
| Nhóm sản phẩm: ảnh, đoạn giới thiệu, hỏi đáp | **Sản phẩm › Nhóm sản phẩm** | Biên tập viên, Quản trị viên |
| Thư viện chất liệu, gia công | **Chất liệu** | Biên tập viên, Quản trị viên |
| Mẫu đã làm (/mau-da-lam/): ảnh, khách hàng và có được nêu tên không, ngành, sản phẩm (bắt buộc ít nhất 1), quy cách. Không xuất bản được khi khách chưa đồng ý mà tên khách nằm trong tiêu đề, mô tả hoặc ảnh | **Mẫu đã làm** (ngành ở **Mẫu đã làm › Ngành**, đoạn đầu trang ở **Mẫu đã làm › Giới thiệu trang**) | Biên tập viên, Quản trị viên |
| Bài viết Kiến thức in ấn (/kien-thuc/): nội dung, chuyên mục, chủ đề, ảnh đại diện, sản phẩm liên quan (hộp bên phải); ghim "Nổi bật" để lên khu bài nổi bật; tiêu đề trùng sản phẩm/bài khác được cảnh báo trước khi Đăng | **Bài viết** (chuyên mục ở **Bài viết › Danh mục**, chủ đề ở **Bài viết › Chủ đề**: chủ đề dưới 2 bài không hiện ra ngoài) | Biên tập viên, Quản trị viên |
| Đoạn giới thiệu đầu trang Kiến thức in ấn | Trang › **Kiến thức in ấn** › Tóm tắt | Biên tập viên, Quản trị viên |
| Thêm, khoá tài khoản; vai trò; nhóm quyền bổ sung | **Người dùng**, **Người dùng › Nhóm quyền** | Quản trị viên |
| Xác thực 2 lớp, khoá tạm khi nhập sai, file nhật ký hằng tháng | **Âu Việt › Bảo mật, nhật ký**; mỗi người bật 2 lớp ở **Hồ sơ** | Quản trị viên |
| Nhật ký hoạt động (lọc, xuất CSV/JSON) | **Nhật ký hoạt động** (Simple History) | Quản trị viên |

Ở local, email thông báo không gửi ra ngoài mà vào Mailpit: http://localhost:8025

**Nhập từ web cũ** (Công cụ › Nhập từ web cũ, chỉ có ở local/staging): website tự tải sản phẩm, ảnh, nội dung từ inquangcaoauviet.com, tạo thư viện chất liệu, ảnh trang chủ và chuyển hướng 301 từ đường dẫn cũ. Chạy lại được; sản phẩm đã sửa tay được giữ nguyên trừ khi chọn ghi đè. Quy cách và giới thiệu nhóm nhập vào là bản nháp, cần kiểm tra (cột "Quy cách" trong danh sách sản phẩm).

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
