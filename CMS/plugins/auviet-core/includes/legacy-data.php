<?php
/**
 * Dữ liệu chuyển từ web cũ inquangcaoauviet.com sang cấu trúc mới (mục 3 Tài liệu mô tả chức năng).
 * Mỗi sản phẩm mới gom một hoặc nhiều trang cũ: trang đầu lấy mô tả và nội dung, các trang sau lấy thêm ảnh
 * và chuyển hướng 301 về sản phẩm mới. Quy cách ở đây là bản nháp, Âu Việt cần kiểm tra lại (đánh dấu trong quản trị).
 */

defined( 'ABSPATH' ) || exit;

function av_legacy_base(): string {
	return 'https://inquangcaoauviet.com';
}

/**
 * Sản phẩm mới. Khoá: slug mới. sources: đường dẫn trang chi tiết cũ (/pddetail/...).
 * order: thứ tự nổi bật (1–8 hiện ở trang chủ).
 */
function av_legacy_products(): array {
	return array(
		// Ấn phẩm văn phòng.
		'in-card-visit'      => array(
			'title'   => 'In card visit',
			'groups'  => array( 'an-pham-van-phong' ),
			'order'   => 8,
			'sources' => array( '/pddetail/in-card-visit-509-3184.html' ),
			'spec'    => array( 'size' => '90 × 55 mm, 90 × 50 mm hoặc theo yêu cầu', 'paper' => 'Couche, Ivory, giấy mỹ thuật', 'gsm' => '250–300 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '1 hoặc 2 mặt', 'finishing' => 'Cán màng bóng hoặc mờ, bo góc, ép kim, phủ UV định hình' ),
			'mats'    => array( 'couche', 'ivory', 'giay-my-thuat', 'can-mang', 'bo-goc', 'ep-kim', 'phu-uv' ),
		),
		'in-phong-bi'        => array(
			'title'   => 'In phong bì',
			'groups'  => array( 'an-pham-van-phong' ),
			'sources' => array( '/pddetail/in-phong-bi-509-3191.html', '/pddetail/phong-bi-a4-a5-12x22-511-3216.html', '/pddetail/in-phong-bi-qua-tang-doanh-nghiep-theo-yeu-cau-tai-ha-noi-511-3243.html', '/pddetail/5-loi-ich-chien-luoc-khi-in-phong-bi-cho-doanh-nghiep-511-3231.html' ),
			'spec'    => array( 'size' => 'A4 (25 × 35 cm), A5 (17 × 24 cm), 12 × 22 cm hoặc theo yêu cầu', 'paper' => 'Fort, Couche, Kraft', 'gsm' => '100–150 gsm', 'colors' => '1–4 màu', 'sides' => '1 mặt', 'finishing' => 'Bế, dán thành phẩm' ),
			'mats'    => array( 'fort', 'couche', 'kraft', 'be' ),
		),
		'in-kep-file'        => array(
			'title'   => 'In kẹp file',
			'groups'  => array( 'an-pham-van-phong' ),
			'sources' => array( '/pddetail/in-kep-file-509-3189.html' ),
			'spec'    => array( 'size' => 'Đựng vừa giấy A4 hoặc theo yêu cầu', 'paper' => 'Couche, Ivory', 'gsm' => '250–300 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '1 hoặc 2 mặt', 'finishing' => 'Cán màng, bế, dán tai' ),
			'mats'    => array( 'couche', 'ivory', 'can-mang', 'be' ),
		),
		'in-hoa-don'         => array(
			'title'   => 'In hoá đơn, phiếu thu chi',
			'groups'  => array( 'an-pham-van-phong' ),
			'sources' => array( '/pddetail/in-hoa-don-509-3185.html' ),
			'spec'    => array( 'size' => 'A4, A5 hoặc theo mẫu', 'paper' => 'Fort, giấy carbonless nhiều liên', 'colors' => '1–4 màu', 'sides' => '1 mặt', 'finishing' => 'Đánh số nhảy, đóng quyển, răng cưa xé' ),
			'mats'    => array( 'fort', 'dong-quyen' ),
		),
		'in-phieu-bao-hanh'  => array(
			'title'   => 'In phiếu bảo hành, voucher',
			'groups'  => array( 'an-pham-van-phong' ),
			'sources' => array( '/pddetail/in-phieu-bao-hanh-509-3186.html' ),
			'spec'    => array( 'size' => 'Theo yêu cầu', 'paper' => 'Couche, Ivory, Fort', 'gsm' => '150–300 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '1 hoặc 2 mặt', 'finishing' => 'Cán màng, đánh số, răng cưa xé' ),
			'mats'    => array( 'couche', 'ivory', 'fort', 'can-mang' ),
		),
		'in-ve'              => array(
			'title'   => 'In vé',
			'groups'  => array( 'an-pham-van-phong' ),
			'sources' => array( '/pddetail/in-ve-509-3188.html' ),
			'spec'    => array( 'size' => 'Theo yêu cầu', 'paper' => 'Couche, Ivory, Fort', 'colors' => '4 màu (CMYK)', 'sides' => '1 hoặc 2 mặt', 'finishing' => 'Đánh số nhảy, răng cưa xé, ép kim' ),
			'mats'    => array( 'couche', 'ivory', 'fort', 'ep-kim' ),
		),
		'so-bia-da'          => array(
			'title'   => 'Sổ bìa da in logo',
			'groups'  => array( 'an-pham-van-phong' ),
			'sources' => array( '/pddetail/so-bia-da-509-3229.html' ),
			'spec'    => array( 'size' => 'A5, B5, A4', 'paper' => 'Bìa da; ruột Fort hoặc giấy ngà', 'finishing' => 'Ép kim hoặc dập logo lên bìa, đóng gáy' ),
			'mats'    => array( 'fort', 'ep-kim', 'dap-noi', 'dong-quyen' ),
		),

		// Ấn phẩm marketing.
		'in-to-roi'          => array(
			'title'   => 'In tờ rơi',
			'groups'  => array( 'an-pham-marketing' ),
			'order'   => 5,
			'sources' => array( '/pddetail/in-to-roi-509-3202.html' ),
			'spec'    => array( 'size' => 'A4, A5, A6 hoặc theo yêu cầu', 'paper' => 'Couche', 'gsm' => '120–200 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '1 hoặc 2 mặt', 'finishing' => 'Cán màng (tuỳ chọn)' ),
			'mats'    => array( 'couche', 'can-mang' ),
		),
		'in-to-gap'          => array(
			'title'   => 'In tờ gấp, brochure',
			'groups'  => array( 'an-pham-marketing' ),
			'sources' => array( '/pddetail/in-to-gap-509-3208.html' ),
			'spec'    => array( 'size' => 'A4 gấp 2, gấp 3 hoặc theo yêu cầu', 'paper' => 'Couche', 'gsm' => '150–250 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '2 mặt', 'finishing' => 'Cấn gấp, cán màng' ),
			'mats'    => array( 'couche', 'can-mang' ),
		),
		'in-catalogue'       => array(
			'title'   => 'In catalogue',
			'groups'  => array( 'an-pham-marketing' ),
			'order'   => 1,
			'sources' => array( '/pddetail/in-catalogue-509-3199.html' ),
			'spec'    => array( 'size' => 'A4, A5 hoặc theo yêu cầu', 'paper' => 'Ruột Couche; bìa Couche hoặc Ivory', 'gsm' => 'Ruột 150–200 gsm, bìa 250–300 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '2 mặt', 'finishing' => 'Cán màng bìa, đóng ghim hoặc keo gáy, ép kim, phủ UV' ),
			'mats'    => array( 'couche', 'ivory', 'can-mang', 'dong-quyen', 'ep-kim', 'phu-uv' ),
		),
		'in-sach-ky-yeu'     => array(
			'title'   => 'In sách, kỷ yếu, tài liệu',
			'groups'  => array( 'an-pham-marketing' ),
			'sources' => array( '/pddetail/in-sach-ky-yeu-tai-lieu-509-3215.html' ),
			'spec'    => array( 'size' => 'A4, B5, A5 hoặc theo yêu cầu', 'paper' => 'Ruột Fort hoặc Couche; bìa Couche, Ivory', 'colors' => '1–4 màu', 'sides' => '2 mặt', 'finishing' => 'Đóng keo gáy, may chỉ, bìa cứng, cán màng bìa' ),
			'mats'    => array( 'fort', 'couche', 'ivory', 'dong-quyen', 'can-mang' ),
		),
		'in-tem-nhan'        => array(
			'title'   => 'In tem nhãn',
			'groups'  => array( 'an-pham-marketing' ),
			'order'   => 4,
			'sources' => array( '/pddetail/in-tem-nhan-509-3187.html' ),
			'spec'    => array( 'size' => 'Theo yêu cầu', 'paper' => 'Decal giấy, decal nhựa (PP, trong, bạc)', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Bế theo hình, cán màng' ),
			'mats'    => array( 'decal-giay', 'decal-nhua', 'be', 'can-mang' ),
		),
		'in-decal'           => array(
			'title'   => 'In decal',
			'groups'  => array( 'an-pham-marketing', 'in-kho-lon' ),
			'sources' => array( '/pddetail/in-decal-509-3190.html', '/pddetail/de-can-pp-515-3244.html', '/pddetail/de-can-mo-515-3227.html' ),
			'spec'    => array( 'size' => 'Khổ nhỏ theo tờ hoặc khổ lớn theo cuộn', 'paper' => 'Decal giấy, decal PP, decal mờ dán kính', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Bế, cắt theo hình, cán màng' ),
			'mats'    => array( 'decal-giay', 'decal-nhua', 'be', 'can-mang' ),
		),
		'in-hangtag'         => array(
			'title'   => 'In hangtag, thẻ treo',
			'groups'  => array( 'an-pham-marketing' ),
			'sources' => array( '/pddetail/hangtag-509-3207.html' ),
			'spec'    => array( 'size' => 'Theo yêu cầu', 'paper' => 'Ivory, Kraft, giấy mỹ thuật', 'gsm' => '300–400 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '1 hoặc 2 mặt', 'finishing' => 'Bế theo hình, đục lỗ, xâu dây, ép kim' ),
			'mats'    => array( 'ivory', 'kraft', 'giay-my-thuat', 'be', 'ep-kim' ),
		),

		// Bao bì.
		'in-tui-giay'        => array(
			'title'   => 'In túi giấy',
			'groups'  => array( 'bao-bi' ),
			'sources' => array( '/pddetail/in-tui-giay-509-3198.html', '/pddetail/tui-giay-dung-qua-tang-514-3245.html', '/pddetail/tui-giay-day-lua-tui-giay-dung-hop-qua-514-3220.html', '/pddetail/tui-giay-doanh-nghiep-su-kien-514-3219.html', '/pddetail/xuong-in-tui-giay-my-pham-theo-yeu-cau-514-3236.html', '/pddetail/in-tui-giay-dung-dien-thoai-theo-yeu-cau-ha-noi-514-3235.html', '/pddetail/tui-giay-trung-thu-cao-cap-giup-nang-tam-mon-qua-514-3238.html', '/pddetail/nhung-mau-tui-giay-dung-qua-trung-thu-dep-va-sang-trong-514-3234.html', '/pddetail/cac-mau-tui-giay-dung-trai-cay-duoc-khach-hang-ua-chuong-514-3239.html', '/pddetail/in-tui-giay-ivory-dung-trai-cay-gia-re-ha-noi-514-3232.html', '/pddetail/tui-giay-dung-hoa-qua-514-3217.html', '/pddetail/tui-giay-lam-sao-de-in-tui-giay-gia-re-514-3212.html' ),
			'spec'    => array( 'size' => 'Theo kích thước sản phẩm', 'paper' => 'Ivory, Couche, Kraft', 'gsm' => '210–300 gsm', 'colors' => '4 màu (CMYK)', 'sides' => 'Mặt ngoài túi', 'finishing' => 'Cán màng, ép kim, quai dây dù, dây lụa hoặc giấy xoắn' ),
			'mats'    => array( 'ivory', 'couche', 'kraft', 'can-mang', 'ep-kim' ),
		),
		'in-tui-giay-kraft'  => array(
			'title'   => 'In túi giấy kraft',
			'groups'  => array( 'bao-bi' ),
			'order'   => 2,
			'sources' => array( '/pddetail/in-tui-giay-kraft-co-logo-%E2%80%93-bao-bi-dep-chi-phi-hop-ly-514-3247.html', '/pddetail/tui-giay-krap-514-3218.html' ),
			'spec'    => array( 'size' => 'Theo yêu cầu', 'paper' => 'Kraft nâu, kraft trắng', 'gsm' => '120–250 gsm', 'colors' => '1–4 màu', 'sides' => 'Mặt ngoài túi', 'finishing' => 'Quai giấy xoắn hoặc dây dù' ),
			'mats'    => array( 'kraft' ),
		),
		'in-hop-giay'        => array(
			'title'   => 'In hộp giấy',
			'groups'  => array( 'bao-bi' ),
			'order'   => 3,
			'sources' => array( '/pddetail/in-hop-giay-509-3200.html', '/pddetail/xuong-in-hop-giay-ha-noi-%E2%80%93-in-theo-kich-thuoc-thiet-ke-rieng-513-3246.html', '/pddetail/nhung-mau-hop-giay-dep-in-hop-giay-theo-yeu-cau-513-3240.html', '/pddetail/in-hop-giay-my-pham-gia-re-thiet-ke-chuyen-nghiep-513-3237.html', '/pddetail/hop-giay-duoc-pham-my-pham-513-3221.html', '/pddetail/xuong-in-hop-thuoc-ha-noi-%E2%80%93-da-dang-kich-thuoc-chat-lieu-513-3248.html', '/pddetail/in-hop-cung-trung-thu-cao-cap-thiet-ke-theo-yeu-cau-513-3233.html', '/pddetail/thiet-ke-hop-qua-tang-theo-nhan-dien-thuong-hieu-513-3249.html', '/pddetail/in-hop-dung-trai-cay-theo-kich-thuoc-rieng-toi-uu-chi-phi-513-3250.html' ),
			'spec'    => array( 'size' => 'Theo kích thước sản phẩm', 'paper' => 'Ivory, Duplex, Couche; hộp cứng bồi carton', 'gsm' => '250–350 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Cán màng, ép kim, phủ UV định hình, bế' ),
			'mats'    => array( 'ivory', 'duplex', 'couche', 'can-mang', 'ep-kim', 'phu-uv', 'be' ),
		),
		'in-thung-carton'    => array(
			'title'   => 'In thùng carton',
			'groups'  => array( 'bao-bi' ),
			'sources' => array( '/pddetail/in-hop-giay-in-thung-catton-509-3204.html', '/pddetail/san-xuat-bao-bi-giay-hop-giay-krap-hop-catton-song-513-3222.html' ),
			'spec'    => array( 'size' => 'Theo kích thước hàng', 'paper' => 'Carton sóng 3 lớp, 5 lớp', 'colors' => '1–4 màu', 'finishing' => 'Bế, dán hoặc ghim thùng' ),
			'mats'    => array( 'carton-song', 'be' ),
		),

		// Quà tặng theo mùa.
		'in-lich-treo-tuong' => array(
			'title'   => 'In lịch treo tường',
			'groups'  => array( 'qua-tang-theo-mua' ),
			'order'   => 6,
			'sources' => array( '/pddetail/mau-lich-treo-tuong-lo-xo-7-to-510-3194.html', '/pddetail/mau-lich-treo-tuong-nep-thiec-5-to-510-3195.html', '/pddetail/mau-lich-1-to-nep-thiec-510-3193.html', '/pddetail/lich-tet-lich-doc-quyen-510-3209.html', '/pddetail/lich-doc-quyen-510-3230.html' ),
			'spec'    => array( 'size' => 'Theo mẫu: 1 tờ, 5 tờ, 7 tờ, 13 tờ', 'paper' => 'Couche', 'gsm' => '200–300 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Nẹp thiếc hoặc lò xo, ép kim' ),
			'mats'    => array( 'couche', 'ep-kim', 'dong-quyen' ),
		),
		'in-lich-de-ban'     => array(
			'title'   => 'In lịch để bàn',
			'groups'  => array( 'qua-tang-theo-mua' ),
			'sources' => array( '/pddetail/lich-de-ban-lich-doc-quyen-lich-doanh-nghiep-510-3210.html', '/pddetail/in-lich-tet-509-3213.html' ),
			'spec'    => array( 'size' => 'Theo mẫu (ngang, đứng, chữ A)', 'paper' => 'Couche, Ivory; chân đế bồi carton', 'colors' => '4 màu (CMYK)', 'sides' => '2 mặt', 'finishing' => 'Đóng lò xo, cán màng chân đế' ),
			'mats'    => array( 'couche', 'ivory', 'dong-quyen', 'can-mang' ),
		),
		'in-lich-bloc'       => array(
			'title'   => 'In lịch bloc',
			'groups'  => array( 'qua-tang-theo-mua' ),
			'sources' => array( '/pddetail/mau-lich-bloc-52-tuan-510-3192.html', '/pddetail/mau-bia-lich-gan-bloc-roi-510-3196.html' ),
			'spec'    => array( 'size' => 'Bìa lịch theo mẫu, bloc theo khổ', 'paper' => 'Bìa Couche bồi carton; bloc Fort', 'colors' => '4 màu (CMYK)', 'finishing' => 'Ép kim, cán màng, bồi bìa, gắn bloc' ),
			'mats'    => array( 'couche', 'fort', 'ep-kim', 'can-mang' ),
		),
		'in-bao-li-xi'       => array(
			'title'   => 'In bao lì xì',
			'groups'  => array( 'qua-tang-theo-mua' ),
			'sources' => array( '/pddetail/in-bao-li-xi-tet-512-3197.html' ),
			'spec'    => array( 'size' => '8 × 16 cm hoặc theo yêu cầu', 'paper' => 'Couche, Ivory, giấy mỹ thuật', 'gsm' => '150–250 gsm', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Ép kim, dập nổi, bế, dán' ),
			'mats'    => array( 'couche', 'ivory', 'giay-my-thuat', 'ep-kim', 'dap-noi', 'be' ),
		),
		'in-quat-nhua'       => array(
			'title'   => 'In quạt nhựa quảng cáo',
			'groups'  => array( 'qua-tang-theo-mua' ),
			'sources' => array( '/pddetail/quat-nhua-509-3214.html' ),
			'spec'    => array( 'size' => 'Theo mẫu quạt', 'paper' => 'Nhựa PP', 'colors' => '4 màu (CMYK)', 'sides' => '1 hoặc 2 mặt', 'finishing' => 'Bế theo khuôn quạt, gắn tay cầm' ),
			'mats'    => array( 'be' ),
		),

		// In khổ lớn, sự kiện.
		'in-backdrop'        => array(
			'title'   => 'In backdrop',
			'groups'  => array( 'in-kho-lon' ),
			'order'   => 7,
			'sources' => array( '/pddetail/backdrop-xuong-in-phong-bat-515-3225.html', '/pddetail/poster-backdrop-509-3206.html' ),
			'spec'    => array( 'size' => 'Theo kích thước sân khấu, khu vực sự kiện', 'paper' => 'Bạt hiflex, vải', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Đóng khung, lắp đặt tại sự kiện' ),
			'mats'    => array( 'bat-hiflex' ),
		),
		'in-standee'         => array(
			'title'   => 'In standee',
			'groups'  => array( 'in-kho-lon' ),
			'sources' => array( '/pddetail/standee-515-3242.html', '/pddetail/standee-509-3201.html' ),
			'spec'    => array( 'size' => '60 × 160 cm, 80 × 180 cm hoặc theo yêu cầu', 'paper' => 'Decal PP, bạt', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Cán màng, kèm chân standee' ),
			'mats'    => array( 'decal-nhua', 'bat-hiflex', 'can-mang' ),
		),
		'in-poster'          => array(
			'title'   => 'In poster',
			'groups'  => array( 'in-kho-lon' ),
			'sources' => array( '/pddetail/poster-515-3228.html' ),
			'spec'    => array( 'size' => 'A3, A2, A1, A0 hoặc theo yêu cầu', 'paper' => 'Couche, decal PP', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Cán màng (tuỳ chọn)' ),
			'mats'    => array( 'couche', 'decal-nhua', 'can-mang' ),
		),
		'in-photobooth'      => array(
			'title'   => 'In photobooth',
			'groups'  => array( 'in-kho-lon' ),
			'sources' => array( '/pddetail/photobooth-515-3241.html' ),
			'spec'    => array( 'size' => 'Theo thiết kế', 'paper' => 'Decal PP bồi tấm nhựa, bạt', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Cắt theo hình, đóng khung' ),
			'mats'    => array( 'decal-nhua', 'bat-hiflex' ),
		),
		'in-bat-hiflex'      => array(
			'title'   => 'In bạt hiflex khổ lớn',
			'groups'  => array( 'in-kho-lon' ),
			'sources' => array( '/pddetail/in-bat-kho-lon-509-3183.html' ),
			'spec'    => array( 'size' => 'Khổ lớn, nối theo kích thước cần', 'paper' => 'Bạt hiflex', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Đóng khoen, may viền' ),
			'mats'    => array( 'bat-hiflex' ),
		),
		'bien-den-quang-cao' => array(
			'title'   => 'Biển đèn quảng cáo',
			'groups'  => array( 'in-kho-lon' ),
			'sources' => array( '/pddetail/bien-den-quang-cao-515-3226.html' ),
			'spec'    => array( 'size' => 'Theo vị trí lắp đặt', 'paper' => 'Bạt xuyên đèn, mica', 'finishing' => 'Khung, đèn LED, lắp đặt' ),
			'mats'    => array( 'bat-hiflex' ),
		),
		'in-tranh-canvas'    => array(
			'title'   => 'In tranh canvas',
			'groups'  => array( 'in-kho-lon' ),
			'sources' => array( '/pddetail/in-tranh-canvas-509-3203.html' ),
			'spec'    => array( 'size' => 'Theo yêu cầu', 'paper' => 'Vải canvas', 'colors' => '4 màu (CMYK)', 'sides' => '1 mặt', 'finishing' => 'Căng khung gỗ' ),
			'mats'    => array( 'vai-canvas' ),
		),
		'in-lua-vai'         => array(
			'title'   => 'In lụa, in vải',
			'groups'  => array( 'in-kho-lon' ),
			'sources' => array( '/pddetail/in-lua-in-vai-silk-509-3223.html' ),
			'spec'    => array( 'size' => 'Theo yêu cầu', 'paper' => 'Vải, lụa', 'colors' => 'Theo số màu thiết kế', 'finishing' => 'May viền, gắn khoen' ),
			'mats'    => array(),
		),
	);
}

/**
 * Trang danh mục cũ (/pd/...-<id>.html) chuyển về trang mới.
 */
function av_legacy_category_map(): array {
	return array(
		'509' => '/san-pham/',
		'508' => '/thiet-ke/',
		'514' => '/san-pham/in-tui-giay/',
		'513' => '/san-pham/in-hop-giay/',
		'511' => '/san-pham/in-phong-bi/',
		'510' => '/danh-muc/qua-tang-theo-mua/',
		'512' => '/san-pham/in-bao-li-xi/',
		'515' => '/danh-muc/in-kho-lon/',
	);
}

/**
 * Ảnh trang chủ (ô ảnh lớn và hai ô nhỏ ở phần mở đầu): lấy từ ảnh đại diện sản phẩm.
 */
function av_legacy_home_images(): array {
	return array(
		array( 'product' => 'in-catalogue', 'alt' => 'Catalogue in tại Âu Việt', 'class' => 'av-collage__main', 'placeholder' => '[Ảnh thật: catalogue Âu Việt đã in]' ),
		array( 'product' => 'in-hop-giay', 'alt' => 'Hộp giấy in theo yêu cầu', 'class' => '', 'placeholder' => '[Ảnh thật: hộp giấy]' ),
		array( 'product' => 'in-tui-giay-kraft', 'alt' => 'Túi giấy kraft in logo', 'class' => '', 'placeholder' => '[Ảnh thật: túi giấy kraft]' ),
	);
}

/**
 * Thư viện chất liệu (SP-05): mô tả chung, Âu Việt bổ sung ảnh thật và thông tin riêng.
 */
function av_legacy_materials(): array {
	return array(
		'couche'        => array( 'giay', 'Giấy Couche', 'Giấy tráng phủ hai mặt, bề mặt láng (couche bóng) hoặc mịn (couche mờ), cho màu in sắc nét và tươi.', 'Định lượng phổ biến từ 90 đến 300 gsm. Dùng nhiều cho tờ rơi, tờ gấp, catalogue, poster, lịch và card visit. Couche mờ ít loá sáng, dễ đọc chữ; couche bóng cho ảnh rực màu hơn.' ),
		'ivory'         => array( 'giay', 'Giấy Ivory', 'Giấy bìa trắng, cứng và dày, một mặt láng để in, một mặt hơi nhám.', 'Định lượng phổ biến từ 210 đến 400 gsm. Dùng cho hộp giấy, túi giấy, hangtag, bìa sách, bìa catalogue và card visit.' ),
		'kraft'         => array( 'giay', 'Giấy Kraft', 'Giấy làm từ bột gỗ ít tẩy trắng, màu nâu tự nhiên (có loại kraft trắng), dai và chịu lực tốt.', 'Hợp với bao bì mang phong cách mộc, thân thiện môi trường: túi giấy, hangtag, phong bì, hộp nhỏ. Màu in trên kraft nâu trầm hơn so với giấy trắng.' ),
		'duplex'        => array( 'giay', 'Giấy Duplex', 'Giấy bìa một mặt trắng tráng phủ để in, mặt sau màu xám.', 'Giá thành thấp hơn Ivory nên hay dùng cho hộp giấy, vỏ hộp sản phẩm số lượng lớn, nơi mặt trong hộp ít được nhìn thấy.' ),
		'fort'          => array( 'giay', 'Giấy Fort (Ford)', 'Giấy không tráng phủ, bề mặt hơi nhám, viết bút lên không bị nhoè mực.', 'Định lượng phổ biến từ 70 đến 120 gsm. Dùng cho phong bì, hoá đơn, giấy tiêu đề, ruột sổ và ruột sách.' ),
		'giay-my-thuat' => array( 'giay', 'Giấy mỹ thuật', 'Các dòng giấy có vân, ánh nhũ hoặc màu sẵn, tạo cảm giác sang khi cầm.', 'Dùng cho card visit, thiệp, bao lì xì, hangtag và hộp quà cao cấp. Mỗi loại giấy mỹ thuật có màu và vân riêng, nên xem mẫu giấy thật trước khi chọn.' ),
		'decal-giay'    => array( 'giay', 'Decal giấy', 'Giấy có sẵn lớp keo và đế bóc, in được 4 màu.', 'Dùng cho tem nhãn sản phẩm dùng trong nhà, nhãn hộp, tem niêm phong. Không hợp nơi ẩm ướt vì giấy dễ thấm nước.' ),
		'decal-nhua'    => array( 'giay', 'Decal nhựa (PP, trong, bạc)', 'Decal bằng màng nhựa, chịu nước và bền màu hơn decal giấy.', 'Có loại trắng sữa, trong suốt và ánh bạc. Dùng cho nhãn mỹ phẩm, nhãn chai lọ, tem chống nước, standee và decal dán kính.' ),
		'carton-song'   => array( 'giay', 'Carton sóng', 'Tấm giấy nhiều lớp có lớp sóng ở giữa (3 lớp, 5 lớp), chịu lực và chống va đập.', 'Dùng cho thùng đựng hàng, hộp vận chuyển, hộp trái cây. Số lớp và loại sóng chọn theo trọng lượng hàng bên trong.' ),
		'bat-hiflex'    => array( 'giay', 'Bạt hiflex', 'Vật liệu nhựa PVC có lưới sợi bên trong, chịu nắng mưa, in được khổ rất lớn.', 'Dùng cho backdrop, băng rôn, biển quảng cáo ngoài trời. Loại bạt xuyên đèn cho ánh sáng đi qua, dùng làm mặt biển hộp đèn.' ),
		'vai-canvas'    => array( 'giay', 'Vải canvas', 'Vải dệt dày, bề mặt có vân, in bằng mực bền màu, thường căng trên khung gỗ.', 'Dùng cho tranh treo tường, tranh quà tặng, tranh trang trí văn phòng.' ),
		'can-mang'      => array( 'gia-cong', 'Cán màng bóng, mờ', 'Phủ một lớp màng nhựa mỏng lên mặt in để chống ẩm, chống trầy và tăng độ bền.', 'Màng bóng làm màu rực hơn; màng mờ cho cảm giác sang, ít bám vân tay. Dùng cho card visit, bìa catalogue, hộp, túi giấy, lịch.' ),
		'ep-kim'        => array( 'gia-cong', 'Ép kim (ép nhũ)', 'Ép lớp nhũ vàng, bạc hoặc màu lên giấy bằng khuôn nhiệt, làm logo và chữ nổi bật.', 'Dùng cho bao lì xì, hộp quà, thiệp, bìa lịch, card visit. Mỗi mẫu ép kim cần làm khuôn riêng theo hình.' ),
		'phu-uv'        => array( 'gia-cong', 'Phủ UV định hình', 'Phủ lớp UV bóng lên một phần thiết kế (logo, hình ảnh) tạo độ bóng tương phản với nền.', 'Thường làm trên nền đã cán màng mờ để phần phủ UV nổi bật. Dùng cho card visit, bìa catalogue, hộp.' ),
		'dap-noi'       => array( 'gia-cong', 'Dập nổi, dập chìm', 'Dùng khuôn ép làm chữ hoặc logo nổi lên hay lõm xuống mặt giấy, không cần mực.', 'Hợp với giấy dày, giấy mỹ thuật. Dùng cho thiệp, bìa sổ, bao lì xì, hộp cao cấp.' ),
		'be'            => array( 'gia-cong', 'Bế (cắt theo khuôn)', 'Cắt giấy theo hình dạng bất kỳ bằng khuôn bế.', 'Bắt buộc với hộp giấy, túi giấy, tem nhãn theo hình, hangtag, bao lì xì. Mỗi hình dạng cần một khuôn bế riêng.' ),
		'bo-goc'        => array( 'gia-cong', 'Bo góc', 'Cắt bo tròn các góc thành phẩm, cầm êm tay và không bị quăn mép.', 'Dùng cho card visit, thẻ, lịch bloc, menu.' ),
		'dong-quyen'    => array( 'gia-cong', 'Đóng quyển', 'Các kiểu đóng: ghim giữa, keo gáy, may chỉ, lò xo, nẹp thiếc.', 'Ghim giữa hợp với catalogue mỏng; keo gáy và may chỉ cho sách, kỷ yếu dày; lò xo và nẹp thiếc cho lịch, sổ.' ),
	);
}

/**
 * Đoạn giới thiệu nhóm (bản nháp, đánh dấu để Âu Việt đọc lại).
 */
function av_legacy_group_intros(): array {
	return array(
		'an-pham-van-phong' => '<p>Ấn phẩm văn phòng là những thứ doanh nghiệp dùng hằng ngày và cũng là nơi khách hàng, đối tác nhìn thấy thương hiệu thường xuyên nhất: card visit trao tay trong buổi gặp, phong bì gửi hồ sơ, kẹp file đựng báo giá, hoá đơn và phiếu thu chi, phiếu bảo hành đi kèm sản phẩm, vé và sổ tay quà tặng.</p><p>Âu Việt in ấn phẩm văn phòng theo đúng bộ nhận diện của doanh nghiệp, đồng nhất màu logo giữa các sản phẩm và giữa các lần in. Có thể in số lượng nhỏ để dùng ngay hoặc in số lượng lớn cho cả năm, đánh số nhảy cho hoá đơn và vé, đóng quyển theo mẫu. Chưa có file, đội thiết kế của Âu Việt dàn trang từ logo và thông tin bạn gửi.</p><p>Chọn sản phẩm bên dưới để xem quy cách, chất liệu thường dùng và giá tham khảo, hoặc gửi yêu cầu để được tư vấn trọn bộ ấn phẩm văn phòng.</p>',
		'an-pham-marketing' => '<p>Ấn phẩm marketing giúp sản phẩm và dịch vụ của bạn đến tay khách hàng: tờ rơi phát tại sự kiện, tờ gấp và brochure giới thiệu dịch vụ, catalogue sản phẩm cho đại lý, sách và kỷ yếu, tem nhãn dán trên bao bì, decal và hangtag đi kèm hàng hoá.</p><p>Với mỗi ấn phẩm, Âu Việt tư vấn khổ, loại giấy, định lượng và kiểu gia công phù hợp mục đích và ngân sách: tờ rơi phát số lượng lớn cần giấy mỏng, giá tốt; catalogue gửi đối tác cần bìa dày, cán màng và có thể ép kim hoặc phủ UV logo. File được kiểm tra trước khi in để màu sắc và chữ không bị lỗi.</p><p>Xem từng sản phẩm để biết quy cách thường dùng và giá tham khảo; cần in gấp cho sự kiện, gọi hotline để Âu Việt xếp lịch sản xuất.</p>',
		'bao-bi'            => '<p>Bao bì là thứ khách hàng cầm trên tay trước khi mở sản phẩm. Âu Việt in túi giấy, hộp giấy và thùng carton theo đúng kích thước sản phẩm của bạn, từ hộp mỹ phẩm, hộp thuốc, hộp bánh Trung thu đến túi quà tặng doanh nghiệp và thùng đựng hàng vận chuyển.</p><p>Mỗi mẫu bao bì cần chọn chất liệu theo trọng lượng hàng và cách dùng: Ivory, Duplex cho hộp nhẹ; hộp cứng bồi carton cho quà cao cấp; Kraft cho phong cách mộc; carton sóng cho thùng chịu lực. Âu Việt dựng khuôn bế theo kích thước, làm mẫu để bạn duyệt trước khi sản xuất hàng loạt, và có thể ép kim, phủ UV, cán màng để bao bì nổi bật trên kệ.</p><p>Gửi kích thước sản phẩm và số lượng, Âu Việt tư vấn kiểu hộp, túi phù hợp và báo giá.</p>',
		'qua-tang-theo-mua' => '<p>Quà tặng theo mùa là dịp doanh nghiệp gửi lời chúc và nhắc thương hiệu tới khách hàng, đối tác: lịch Tết treo tường và để bàn, lịch bloc, bao lì xì in logo, quạt nhựa quảng cáo cho mùa hè và sự kiện.</p><p>Đây là nhóm sản phẩm có mùa cao điểm rõ rệt, nhất là lịch và lì xì trước Tết, nên đặt sớm giúp có thời gian duyệt mẫu, chỉnh sửa thiết kế và nhận hàng đúng hẹn. Âu Việt có sẵn nhiều mẫu lịch để bạn chọn và thay ảnh, logo, hoặc thiết kế mẫu riêng theo nhận diện doanh nghiệp. Bao lì xì có thể ép kim, dập nổi và bế theo hình riêng.</p><p>Xem từng sản phẩm để biết quy cách và giá tham khảo, hoặc gửi yêu cầu sớm để giữ lịch sản xuất mùa cao điểm.</p>',
		'in-kho-lon'        => '<p>In khổ lớn phục vụ sự kiện, cửa hàng và trưng bày: backdrop sân khấu, standee đặt tại quầy, poster, photobooth chụp ảnh, bạt hiflex và băng rôn, biển đèn quảng cáo, tranh canvas trang trí và decal khổ lớn dán kính.</p><p>Với hàng khổ lớn, kích thước thực tế và vị trí đặt quyết định chất liệu: bạt hiflex chịu nắng mưa cho ngoài trời, decal PP cho standee và poster trong nhà, bạt xuyên đèn cho hộp đèn. Âu Việt kiểm tra độ phân giải file trước khi in để hình không bị vỡ khi phóng lớn, và có thể nhận đóng khung, lắp đặt tại địa điểm sự kiện trong khu vực Hà Nội.</p><p>Gửi kích thước, ngày sự kiện và địa điểm, Âu Việt tư vấn chất liệu và báo giá trọn gói.</p>',
	);
}
