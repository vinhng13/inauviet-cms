<?php
/**
 * Hàm dùng chung: đọc cấu hình liên hệ, banner, trạng thái báo giá.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Phiên bản file CSS, JS theo nội dung (đổi nội dung là đổi phiên bản), để trình duyệt và bộ nhớ đệm
 * không giữ bản cũ. Không dùng ngày sửa file vì ổ chia sẻ Docker trên Windows có lúc báo ngày cũ.
 */
function av_ver( string $file ): string {
	static $cache = array();
	if ( ! isset( $cache[ $file ] ) ) {
		$cache[ $file ] = is_readable( $file ) ? substr( (string) md5_file( $file ), 0, 10 ) : AV_CORE_VERSION;
	}
	return $cache[ $file ];
}

/**
 * CSS, JS của plugin hoặc giao diện đổi (sau khi cập nhật code): xoá bộ nhớ đệm trang của LiteSpeed một lần,
 * để khách không nhận trang cũ trỏ tới file cũ.
 */
add_action(
	'init',
	static function () {
		$files = array_merge(
			glob( AV_CORE_DIR . 'assets/{css,js}/*.{css,js}', GLOB_BRACE ) ?: array(),
			glob( get_theme_file_path( 'assets/css' ) . '/*.css' ) ?: array(),
			glob( get_theme_file_path( 'templates' ) . '/*.html' ) ?: array(),
			glob( get_theme_file_path( 'parts' ) . '/*.html' ) ?: array()
		);
		$sig = md5( implode( ',', array_map( 'av_ver', $files ) ) );
		if ( get_option( 'av_assets_sig' ) === $sig ) {
			return;
		}
		update_option( 'av_assets_sig', $sig, true );
		do_action( 'litespeed_purge_all' );
	},
	99
);

/**
 * Nội dung hiện trên mọi trang (banner, liên hệ, giới thiệu, thiết kế...) được sửa ở trang cấu hình:
 * xoá bộ nhớ đệm trang LiteSpeed để khách thấy ngay, không phải đợi bộ nhớ đệm hết hạn (7 ngày).
 */
function av_purge_page_cache(): void {
	do_action( 'litespeed_purge_all' );
}
foreach ( array( 'av_campaign', 'av_contact', 'av_design', 'av_about', 'av_projects_intro' ) as $av_opt ) {
	add_action( "update_option_{$av_opt}", 'av_purge_page_cache' );
	add_action( "add_option_{$av_opt}", 'av_purge_page_cache' );
}
unset( $av_opt );

// Banner có ngày bắt đầu, kết thúc: trang lưu đệm hết hạn lúc nửa đêm để banner tự hiện, tự ẩn đúng ngày.
add_action(
	'wp',
	static function () {
		if ( is_admin() ) {
			return;
		}
		$c = av_campaign();
		if ( empty( $c['start'] ) && empty( $c['end'] ) ) {
			return;
		}
		$midnight = ( new DateTimeImmutable( 'tomorrow', wp_timezone() ) )->getTimestamp();
		do_action( 'litespeed_control_set_ttl', max( 60, $midnight - time() ) );
	}
);

// Lần đầu có yêu cầu sau khi qua ngày bắt đầu hoặc kết thúc: xoá bộ nhớ đệm một lần.
add_action(
	'init',
	static function () {
		$live = av_campaign_is_live() ? '1' : '0';
		if ( get_option( 'av_campaign_live_state' ) !== $live ) {
			update_option( 'av_campaign_live_state', $live, true );
			av_purge_page_cache();
		}
	},
	99
);

/**
 * Cấu hình liên hệ và thông tin công ty (QT-10 phần hệ thống).
 */
function av_contact_defaults(): array {
	return array(
		'company_name'     => 'Công ty Cổ phần In Quảng Cáo Âu Việt',
		'office_address'   => 'Số 343 nhà A1, phố Đội Cấn, phường Ngọc Hà, Hà Nội',
		'workshop_address' => 'Số 9 hẻm 209/20/45 An Dương Vương, phường Phú Thượng, Hà Nội',
		'hotline'          => '0988.706.960',
		'landline'         => '(024) 6277 9696',
		'email'            => 'inquangcaoauviet@gmail.com',
		'zalo_url'         => 'https://zalo.me/0988706960',
		'facebook_url'     => 'https://www.facebook.com/ctyinquangcaoauviet',
		'hours'            => '',
		'tax_code'         => '',
		'office_map'       => '',
		'workshop_map'     => '',
		'gbp_url'          => '',
		'notify_email'     => '',
		'gtm_id'           => '',
		'departments'      => array(
			array( 'role' => 'Kinh doanh, báo giá', 'name' => 'Ngọc Quỳnh', 'phone' => '0924 994 736' ),
			array( 'role' => 'Thiết kế, duyệt file', 'name' => 'Phòng thiết kế', 'phone' => '024 6277 9696' ),
			array( 'role' => 'Hoá đơn, thanh toán', 'name' => 'Vân Anh', 'phone' => '0983 786 665' ),
			array( 'role' => 'Giao hàng, đổi trả', 'name' => 'Bộ phận giao hàng', 'phone' => '0904 438 386' ),
		),
	);
}

function av_contact( ?string $key = null ) {
	$data = wp_parse_args( (array) get_option( 'av_contact', array() ), av_contact_defaults() );
	return null === $key ? $data : ( $data[ $key ] ?? '' );
}

/**
 * Banner chiến dịch (QT-10 phần nội dung, LP-02).
 */
function av_campaign_defaults(): array {
	return array(
		'enabled'   => 1,
		'title'     => 'Mùa in lịch Tết 2027 đã mở',
		'text'      => 'Đặt trước [NGÀY CHỐT ĐƠN] để kịp giao trước Tết.',
		'link_text' => 'Xem mẫu lịch Tết',
		'url'       => '/danh-muc/qua-tang-theo-mua/',
		'start'     => '',
		'end'       => '',
	);
}

function av_campaign(): array {
	return wp_parse_args( (array) get_option( 'av_campaign', array() ), av_campaign_defaults() );
}

/**
 * Banner có đang hiển thị không (bật + trong khoảng ngày nếu có đặt).
 */
function av_campaign_is_live(): bool {
	$c = av_campaign();
	if ( empty( $c['enabled'] ) || '' === trim( (string) $c['title'] ) ) {
		return false;
	}
	$today = wp_date( 'Y-m-d' );
	if ( $c['start'] && $today < $c['start'] ) {
		return false;
	}
	if ( $c['end'] && $today > $c['end'] ) {
		return false;
	}
	return true;
}

function av_tel_href( string $phone ): string {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
}

function av_quote_statuses(): array {
	return array(
		'moi'         => 'Mới',
		'dang_tu_van' => 'Đang tư vấn',
		'da_bao_gia'  => 'Đã báo giá',
		'chot'        => 'Chốt',
		'huy'         => 'Huỷ',
	);
}

/**
 * Lựa chọn "Sản phẩm quan tâm" trong form: các nhóm sản phẩm + Thiết kế + Khác.
 */
function av_quote_needs(): array {
	$needs = array();
	$terms = get_terms(
		array(
			'taxonomy'   => 'av_group',
			'parent'     => 0,
			'hide_empty' => false,
			'orderby'    => 'term_id',
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$needs[] = $t->name;
		}
	}
	$needs[] = 'Thiết kế';
	$needs[] = 'Khác';
	return $needs;
}

/**
 * Biểu tượng nét đơn giản (không dùng logo thương hiệu).
 */
function av_icon( string $name, int $size = 22 ): string {
	$paths = array(
		'chat'  => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.6A8 8 0 1 1 21 12z"/>',
		'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'fb'    => '<path d="M15 3h-2a4 4 0 0 0-4 4v3H7v4h2v7h4v-7h3l1-4h-4V7a1 1 0 0 1 1-1h2z"/>',
		'mail'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
	);
	return sprintf(
		'<svg aria-hidden="true" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%2$s</svg>',
		$size,
		$paths[ $name ] ?? ''
	);
}

/**
 * Bốn kênh liên hệ (LP-08): Zalo, Gọi, Facebook, Email.
 */
function av_contact_channels(): array {
	$c = av_contact();
	return array(
		array( 'key' => 'zalo', 'label' => 'Zalo', 'sub' => preg_replace( '/[^0-9]/', '', $c['hotline'] ), 'url' => $c['zalo_url'], 'icon' => 'chat', 'event' => 'bam_zalo' ),
		array( 'key' => 'goi', 'label' => 'Gọi điện', 'sub' => $c['hotline'], 'url' => av_tel_href( $c['hotline'] ), 'icon' => 'phone', 'event' => 'bam_goi' ),
		array( 'key' => 'facebook', 'label' => 'Facebook', 'sub' => 'Nhắn fanpage', 'url' => $c['facebook_url'], 'icon' => 'fb', 'event' => 'bam_facebook' ),
		array( 'key' => 'email', 'label' => 'Email', 'sub' => 'Gửi file, yêu cầu', 'url' => 'mailto:' . $c['email'], 'icon' => 'mail', 'event' => 'bam_email' ),
	);
}
