<?php
/**
 * Trang quản trị "Âu Việt" (QT-10 Cấu hình chung).
 * - Banner chiến dịch: Biên tập viên và Quản trị viên (av_manage_site_content)
 * - Liên hệ & công ty: chỉ Quản trị viên (av_manage_site_system)
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	static function () {
		add_menu_page( 'Âu Việt', 'Âu Việt', 'av_manage_site_content', 'auviet', 'av_render_campaign_page', 'dashicons-admin-site-alt3', 3 );
		add_submenu_page( 'auviet', 'Banner chiến dịch', 'Banner chiến dịch', 'av_manage_site_content', 'auviet', 'av_render_campaign_page' );
		add_submenu_page( 'auviet', 'Liên hệ & công ty', 'Liên hệ & công ty', 'av_manage_site_system', 'auviet-contact', 'av_render_contact_page' );
	}
);

add_action(
	'admin_init',
	static function () {
		register_setting( 'av_campaign_group', 'av_campaign', array( 'sanitize_callback' => 'av_sanitize_campaign' ) );
		register_setting( 'av_contact_group', 'av_contact', array( 'sanitize_callback' => 'av_sanitize_contact' ) );
	}
);

// Quyền lưu từng nhóm cấu hình.
add_filter( 'option_page_capability_av_campaign_group', static fn() => 'av_manage_site_content' );
add_filter( 'option_page_capability_av_contact_group', static fn() => 'av_manage_site_system' );

function av_sanitize_date( $v ): string {
	$v = sanitize_text_field( (string) $v );
	return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : '';
}

function av_sanitize_campaign( $in ): array {
	$in = (array) $in;
	return array(
		'enabled'   => empty( $in['enabled'] ) ? 0 : 1,
		'title'     => sanitize_text_field( $in['title'] ?? '' ),
		'text'      => sanitize_text_field( $in['text'] ?? '' ),
		'link_text' => sanitize_text_field( $in['link_text'] ?? '' ),
		'url'       => esc_url_raw( $in['url'] ?? '' ),
		'start'     => av_sanitize_date( $in['start'] ?? '' ),
		'end'       => av_sanitize_date( $in['end'] ?? '' ),
	);
}

function av_sanitize_contact( $in ): array {
	$in  = (array) $in;
	$out = array();
	foreach ( array( 'company_name', 'office_address', 'workshop_address', 'hotline', 'landline', 'gtm_id' ) as $k ) {
		$out[ $k ] = sanitize_text_field( $in[ $k ] ?? '' );
	}
	$out['email']        = sanitize_email( $in['email'] ?? '' );
	$out['notify_email'] = sanitize_email( $in['notify_email'] ?? '' );
	$out['zalo_url']     = esc_url_raw( $in['zalo_url'] ?? '' );
	$out['facebook_url'] = esc_url_raw( $in['facebook_url'] ?? '' );
	$out['departments']  = array();
	foreach ( (array) ( $in['departments'] ?? array() ) as $d ) {
		$row = array(
			'role'  => sanitize_text_field( $d['role'] ?? '' ),
			'name'  => sanitize_text_field( $d['name'] ?? '' ),
			'phone' => sanitize_text_field( $d['phone'] ?? '' ),
		);
		if ( $row['name'] || $row['phone'] ) {
			$out['departments'][] = $row;
		}
	}
	return $out;
}

function av_field( string $option, string $key, $value, string $type = 'text', string $help = '' ): void {
	printf(
		'<input type="%1$s" class="regular-text" id="%2$s-%3$s" name="%2$s[%3$s]" value="%4$s">',
		esc_attr( $type ),
		esc_attr( $option ),
		esc_attr( $key ),
		esc_attr( (string) $value )
	);
	if ( $help ) {
		printf( '<p class="description">%s</p>', esc_html( $help ) );
	}
}

function av_render_campaign_page(): void {
	if ( ! current_user_can( 'av_manage_site_content' ) ) {
		return;
	}
	$c = av_campaign();
	?>
	<div class="wrap">
		<h1>Banner chiến dịch</h1>
		<p>Dải banner màu vàng ngay dưới đầu trang, dùng cho mùa cao điểm (lịch Tết, lì xì, Trung thu…). Đặt ngày bắt đầu, kết thúc để banner tự hiện và tự ẩn.</p>
		<p>Trạng thái hiện tại: <strong><?php echo av_campaign_is_live() ? 'Đang hiển thị' : 'Đang ẩn'; ?></strong></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'av_campaign_group' ); ?>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Hiển thị banner</th><td><label><input type="checkbox" name="av_campaign[enabled]" value="1" <?php checked( ! empty( $c['enabled'] ) ); ?>> Bật</label></td></tr>
				<tr><th scope="row"><label for="av_campaign-title">Tiêu đề</label></th><td><?php av_field( 'av_campaign', 'title', $c['title'] ); ?></td></tr>
				<tr><th scope="row"><label for="av_campaign-text">Nội dung</label></th><td><?php av_field( 'av_campaign', 'text', $c['text'] ); ?></td></tr>
				<tr><th scope="row"><label for="av_campaign-link_text">Chữ trên liên kết</label></th><td><?php av_field( 'av_campaign', 'link_text', $c['link_text'] ); ?></td></tr>
				<tr><th scope="row"><label for="av_campaign-url">Đường dẫn</label></th><td><?php av_field( 'av_campaign', 'url', $c['url'], 'text', 'Ví dụ /in-lich-tet/ hoặc địa chỉ đầy đủ.' ); ?></td></tr>
				<tr><th scope="row"><label for="av_campaign-start">Bắt đầu hiện từ</label></th><td><?php av_field( 'av_campaign', 'start', $c['start'], 'date', 'Để trống: hiện ngay khi bật.' ); ?></td></tr>
				<tr><th scope="row"><label for="av_campaign-end">Tự ẩn sau ngày</label></th><td><?php av_field( 'av_campaign', 'end', $c['end'], 'date', 'Để trống: hiện đến khi tắt.' ); ?></td></tr>
			</table>
			<?php submit_button( 'Lưu banner' ); ?>
		</form>
	</div>
	<?php
}

function av_render_contact_page(): void {
	if ( ! current_user_can( 'av_manage_site_system' ) ) {
		return;
	}
	$c     = av_contact();
	$deps  = array_values( (array) $c['departments'] );
	$total = max( 6, count( $deps ) + 1 );
	?>
	<div class="wrap">
		<h1>Liên hệ &amp; thông tin công ty</h1>
		<p>Nhập một lần ở đây, mọi vị trí trên website (đầu trang, chân trang, nút liên hệ, khối đội tư vấn, form) tự cập nhật.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'av_contact_group' ); ?>
			<h2>Công ty</h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="av_contact-company_name">Tên công ty</label></th><td><?php av_field( 'av_contact', 'company_name', $c['company_name'] ); ?></td></tr>
				<tr><th scope="row"><label for="av_contact-office_address">Văn phòng</label></th><td><?php av_field( 'av_contact', 'office_address', $c['office_address'] ); ?></td></tr>
				<tr><th scope="row"><label for="av_contact-workshop_address">Xưởng</label></th><td><?php av_field( 'av_contact', 'workshop_address', $c['workshop_address'] ); ?></td></tr>
			</table>
			<h2>Bốn nút liên hệ</h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="av_contact-hotline">Hotline (nút Gọi)</label></th><td><?php av_field( 'av_contact', 'hotline', $c['hotline'] ); ?></td></tr>
				<tr><th scope="row"><label for="av_contact-zalo_url">Đường dẫn Zalo</label></th><td><?php av_field( 'av_contact', 'zalo_url', $c['zalo_url'], 'url', 'Ví dụ https://zalo.me/0988706960' ); ?></td></tr>
				<tr><th scope="row"><label for="av_contact-facebook_url">Đường dẫn Facebook</label></th><td><?php av_field( 'av_contact', 'facebook_url', $c['facebook_url'], 'url' ); ?></td></tr>
				<tr><th scope="row"><label for="av_contact-email">Email công ty (nút Email)</label></th><td><?php av_field( 'av_contact', 'email', $c['email'], 'email' ); ?></td></tr>
				<tr><th scope="row"><label for="av_contact-landline">Điện thoại bàn</label></th><td><?php av_field( 'av_contact', 'landline', $c['landline'] ); ?></td></tr>
			</table>
			<h2>Đội tư vấn theo bộ phận</h2>
			<table class="widefat striped" style="max-width:820px">
				<thead><tr><th>Bộ phận</th><th>Tên</th><th>Số điện thoại</th></tr></thead>
				<tbody>
				<?php for ( $i = 0; $i < $total; $i++ ) : $d = $deps[ $i ] ?? array( 'role' => '', 'name' => '', 'phone' => '' ); ?>
					<tr>
						<td><input type="text" aria-label="Bộ phận <?php echo (int) $i + 1; ?>" name="av_contact[departments][<?php echo (int) $i; ?>][role]" value="<?php echo esc_attr( $d['role'] ); ?>"></td>
						<td><input type="text" aria-label="Tên <?php echo (int) $i + 1; ?>" name="av_contact[departments][<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $d['name'] ); ?>"></td>
						<td><input type="text" aria-label="Số điện thoại <?php echo (int) $i + 1; ?>" name="av_contact[departments][<?php echo (int) $i; ?>][phone]" value="<?php echo esc_attr( $d['phone'] ); ?>"></td>
					</tr>
				<?php endfor; ?>
				</tbody>
			</table>
			<p class="description">Để trống cả tên và số điện thoại để bỏ một dòng.</p>
			<h2>Hệ thống</h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="av_contact-notify_email">Email nhận yêu cầu báo giá</label></th><td><?php av_field( 'av_contact', 'notify_email', $c['notify_email'], 'email', 'Để trống: gửi về email quản trị của WordPress.' ); ?></td></tr>
				<tr><th scope="row"><label for="av_contact-gtm_id">Mã Google Tag Manager</label></th><td><?php av_field( 'av_contact', 'gtm_id', $c['gtm_id'], 'text', 'Dạng GTM-XXXXXXX. Chỉ chạy trên môi trường production.' ); ?></td></tr>
			</table>
			<?php submit_button( 'Lưu thông tin' ); ?>
		</form>
	</div>
	<?php
}
