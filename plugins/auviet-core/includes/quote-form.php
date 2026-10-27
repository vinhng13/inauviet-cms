<?php
/**
 * LP-04 Form liên hệ báo giá: hiển thị, kiểm tra, lưu vào hộp thư QT-07, báo email.
 */

defined( 'ABSPATH' ) || exit;

const AV_QUOTE_MAX_FILE = 20 * MB_IN_BYTES;

function av_quote_allowed_ext(): array {
	return array( 'pdf', 'jpg', 'jpeg', 'png', 'webp', 'ai', 'psd', 'cdr', 'eps', 'svg', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' );
}

function av_quote_error_messages(): array {
	return array(
		'name'  => 'Vui lòng nhập họ tên.',
		'phone' => 'Số điện thoại chưa đúng, ví dụ 0988 706 960.',
		'email' => 'Email chưa đúng định dạng.',
		'file'  => 'File không hợp lệ: tối đa 20 MB, định dạng PDF, ảnh, AI, PSD, CDR, ZIP, Word, Excel.',
		'spam'  => 'Bạn gửi hơi nhanh, vui lòng thử lại sau ít phút hoặc gọi hotline.',
		'save'  => 'Chưa gửi được yêu cầu, vui lòng thử lại hoặc gọi hotline.',
	);
}

function av_render_quote_form( array $attrs = array() ): string {
	$status = isset( $_GET['bao-gia'] ) ? sanitize_key( wp_unslash( $_GET['bao-gia'] ) ) : '';
	$err    = isset( $_GET['loi'] ) ? sanitize_key( wp_unslash( $_GET['loi'] ) ) : '';
	$msgs   = av_quote_error_messages();
	$c      = av_contact();

	if ( 'ok' === $status ) {
		return sprintf(
			'<div class="av-form av-form--done" role="status"><strong class="av-form__done-title">Đã nhận yêu cầu</strong><p>Cảm ơn bạn. Âu Việt sẽ gọi lại số điện thoại bạn để lại để tư vấn và báo giá. Cần gấp, gọi ngay <a href="%1$s">%2$s</a>.</p><a class="av-btn av-btn--outline" href="%3$s#lien-he">Gửi yêu cầu khác</a></div>',
			esc_url( av_tel_href( $c['hotline'] ) ),
			esc_html( $c['hotline'] ),
			esc_url( remove_query_arg( array( 'bao-gia', 'loi' ) ) )
		);
	}

	$need_default = (string) ( $attrs['need'] ?? '' );
	$options      = '';
	foreach ( av_quote_needs() as $n ) {
		$options .= sprintf( '<option%2$s>%1$s</option>', esc_html( $n ), selected( $n, $need_default, false ) );
	}
	$uid     = wp_unique_id( 'avq-' );
	$product = absint( $attrs['product'] ?? 0 );
	$product = ( $product && 'av_product' === get_post_type( $product ) ) ? $product : 0;

	ob_start();
	?>
	<form class="av-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="av_quote">
		<input type="hidden" name="av_source" value="<?php echo esc_attr( remove_query_arg( array( 'bao-gia', 'loi' ) ) ); ?>">
		<input type="hidden" name="av_t" value="<?php echo esc_attr( (string) time() ); ?>">
		<?php if ( $product ) : ?>
			<input type="hidden" name="av_product" value="<?php echo (int) $product; ?>">
			<p class="av-form__product av-field--full">Sản phẩm: <strong><?php echo esc_html( get_the_title( $product ) ); ?></strong></p>
		<?php endif; ?>
		<div class="av-hp" aria-hidden="true"><label for="<?php echo esc_attr( $uid ); ?>-web">Website</label><input id="<?php echo esc_attr( $uid ); ?>-web" type="text" name="av_website" tabindex="-1" autocomplete="off"></div>
		<?php if ( $err && isset( $msgs[ $err ] ) ) : ?>
			<p class="av-form__error" role="alert"><?php echo esc_html( $msgs[ $err ] ); ?></p>
		<?php endif; ?>
		<div class="av-field"><label for="<?php echo esc_attr( $uid ); ?>-name">Họ tên *</label><input id="<?php echo esc_attr( $uid ); ?>-name" name="av_name" autocomplete="name" required maxlength="100"></div>
		<div class="av-field"><label for="<?php echo esc_attr( $uid ); ?>-phone">Số điện thoại *</label><input id="<?php echo esc_attr( $uid ); ?>-phone" name="av_phone" type="tel" autocomplete="tel" required pattern="[0-9+ .\-]{9,15}" title="Ví dụ 0988 706 960"></div>
		<div class="av-field"><label for="<?php echo esc_attr( $uid ); ?>-email">Email</label><input id="<?php echo esc_attr( $uid ); ?>-email" name="av_email" type="email" autocomplete="email"></div>
		<div class="av-field"><label for="<?php echo esc_attr( $uid ); ?>-need">Sản phẩm quan tâm</label><select id="<?php echo esc_attr( $uid ); ?>-need" name="av_need"><?php echo $options; // phpcs:ignore WordPress.Security.EscapeOutput -- đã escape từng option. ?></select></div>
		<div class="av-field av-field--full"><label for="<?php echo esc_attr( $uid ); ?>-note">Nội dung</label><textarea id="<?php echo esc_attr( $uid ); ?>-note" name="av_note" rows="4" maxlength="2000" placeholder="Kích thước, số lượng, thời gian cần hàng…"></textarea></div>
		<div class="av-field av-field--full"><label for="<?php echo esc_attr( $uid ); ?>-file">File thiết kế hoặc tham khảo (tuỳ chọn, tối đa 20 MB)</label><input id="<?php echo esc_attr( $uid ); ?>-file" name="av_file" type="file" accept=".<?php echo esc_attr( implode( ',.', av_quote_allowed_ext() ) ); ?>"></div>
		<div class="av-field av-field--full av-form__actions">
			<button type="submit" class="av-btn">Gửi yêu cầu</button>
			<span>Âu Việt gọi lại trong giờ làm việc.</span>
		</div>
	</form>
	<?php
	return (string) ob_get_clean();
}

add_action( 'admin_post_nopriv_av_quote', 'av_handle_quote' );
add_action( 'admin_post_av_quote', 'av_handle_quote' );

function av_quote_redirect( string $source, array $args ): void {
	$url = add_query_arg( $args, $source ) . '#lien-he';
	wp_safe_redirect( $url );
	exit;
}

function av_handle_quote(): void {
	// phpcs:disable WordPress.Security.NonceVerification -- form công khai, trang có cache; chống spam bằng honeypot, thời gian và giới hạn tần suất.
	$source = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['av_source'] ?? '' ) ), home_url( '/' ) );

	// Chống spam: ô ẩn phải trống, gửi sau ít nhất 3 giây, tối đa 5 lần mỗi 10 phút mỗi IP.
	$elapsed = time() - (int) ( $_POST['av_t'] ?? 0 );
	$ip_key  = 'av_q_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$count   = (int) get_transient( $ip_key );
	if ( ! empty( $_POST['av_website'] ) || $elapsed < 3 || $count >= 5 ) {
		av_quote_redirect( $source, array( 'loi' => 'spam' ) );
	}

	$name  = sanitize_text_field( wp_unslash( $_POST['av_name'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['av_phone'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['av_email'] ?? '' ) );
	$need  = sanitize_text_field( wp_unslash( $_POST['av_need'] ?? '' ) );
	$note  = sanitize_textarea_field( wp_unslash( $_POST['av_note'] ?? '' ) );
	$prod  = absint( $_POST['av_product'] ?? 0 );
	// phpcs:enable
	if ( $prod && ( 'av_product' !== get_post_type( $prod ) || 'publish' !== get_post_status( $prod ) ) ) {
		$prod = 0;
	}

	if ( '' === $name || mb_strlen( $name ) > 100 ) {
		av_quote_redirect( $source, array( 'loi' => 'name' ) );
	}
	$digits = preg_replace( '/[\s.\-]/', '', $phone );
	if ( ! preg_match( '/^(0|\+84)\d{9}$/', $digits ) ) {
		av_quote_redirect( $source, array( 'loi' => 'phone' ) );
	}
	if ( ! empty( $_POST['av_email'] ) && ! is_email( $email ) ) {
		av_quote_redirect( $source, array( 'loi' => 'email' ) );
	}
	if ( ! in_array( $need, av_quote_needs(), true ) ) {
		$need = 'Khác';
	}

	$file = av_quote_store_file();
	if ( false === $file ) {
		av_quote_redirect( $source, array( 'loi' => 'file' ) );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'av_quote',
			'post_status' => 'publish',
			'post_title'  => $name . ' – ' . $digits,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		av_quote_redirect( $source, array( 'loi' => 'save' ) );
	}

	$meta = array(
		'_av_name'   => $name,
		'_av_phone'  => $digits,
		'_av_email'  => $email,
		'_av_need'   => $need,
		'_av_note'   => $note,
		'_av_source' => $source,
		'_av_status' => 'moi',
	);
	if ( $prod ) {
		$meta['_av_product'] = $prod;
	}
	if ( $file ) {
		$meta['_av_file']      = $file['path'];
		$meta['_av_file_name'] = $file['name'];
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $post_id, $k, $v );
	}
	av_quote_log( $post_id, '', 'moi', 0 );
	set_transient( $ip_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	av_quote_notify( $post_id );
	av_quote_redirect( $source, array( 'bao-gia' => 'ok' ) );
}

/**
 * Lưu file khách tải lên vào thư mục không công khai. Trả về null nếu không có file, false nếu file không hợp lệ.
 */
function av_quote_store_file() {
	if ( empty( $_FILES['av_file'] ) || UPLOAD_ERR_NO_FILE === (int) $_FILES['av_file']['error'] ) {
		return null;
	}
	$f = $_FILES['av_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( UPLOAD_ERR_OK !== (int) $f['error'] || (int) $f['size'] > AV_QUOTE_MAX_FILE || ! is_uploaded_file( $f['tmp_name'] ) ) {
		return false;
	}
	$original = sanitize_file_name( wp_basename( (string) $f['name'] ) );
	$ext      = strtolower( pathinfo( $original, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, av_quote_allowed_ext(), true ) ) {
		return false;
	}
	$dir = av_quote_upload_dir() . '/' . wp_date( 'Y/m' );
	if ( ! wp_mkdir_p( $dir ) ) {
		return false;
	}
	$stored = bin2hex( random_bytes( 16 ) ) . '.' . $ext;
	if ( ! move_uploaded_file( $f['tmp_name'], $dir . '/' . $stored ) ) {
		return false;
	}
	return array(
		'path' => wp_date( 'Y/m' ) . '/' . $stored,
		'name' => $original,
	);
}

/**
 * Thư mục file báo giá, chặn truy cập trực tiếp từ web.
 */
function av_quote_upload_dir(): string {
	$base = wp_get_upload_dir()['basedir'] . '/av-quotes';
	if ( ! is_dir( $base ) ) {
		wp_mkdir_p( $base );
	}
	if ( ! file_exists( $base . '/.htaccess' ) ) {
		file_put_contents( $base . '/.htaccess', "RewriteEngine On\nRewriteRule .* - [F,L]\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	if ( ! file_exists( $base . '/index.php' ) ) {
		file_put_contents( $base . '/index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $base;
}

function av_quote_notify( int $post_id ): void {
	$to = av_contact( 'notify_email' ) ?: get_option( 'admin_email' );
	$m  = static fn( $k ) => (string) get_post_meta( $post_id, $k, true );
	$body = implode(
		"\n",
		array(
			'Có yêu cầu báo giá mới trên website.',
			'',
			'Họ tên: ' . $m( '_av_name' ),
			'Số điện thoại: ' . $m( '_av_phone' ),
			'Email: ' . ( $m( '_av_email' ) ?: '(không có)' ),
			'Sản phẩm quan tâm: ' . $m( '_av_need' ) . ( $m( '_av_product' ) ? ' – ' . get_the_title( (int) $m( '_av_product' ) ) : '' ),
			'Nội dung: ' . ( $m( '_av_note' ) ?: '(không có)' ),
			'File đính kèm: ' . ( $m( '_av_file_name' ) ?: '(không có)' ),
			'Trang gửi: ' . $m( '_av_source' ),
			'',
			'Xử lý tại: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
		)
	);
	$subject = $m( '_av_product' ) ? get_the_title( (int) $m( '_av_product' ) ) : $m( '_av_need' );
	wp_mail( $to, '[Báo giá] ' . $m( '_av_name' ) . ' – ' . $subject, $body );
}

// Ghi lại lỗi gửi email để Quản trị viên thấy (yêu cầu vẫn được lưu trong hộp thư).
add_action(
	'wp_mail_failed',
	static function ( WP_Error $error ) {
		set_transient( 'av_mail_error', $error->get_error_message() . ' (' . wp_date( 'd/m/Y H:i' ) . ')', WEEK_IN_SECONDS );
	}
);

add_action(
	'admin_notices',
	static function () {
		$err = get_transient( 'av_mail_error' );
		if ( ! $err || ! current_user_can( 'av_manage_site_system' ) ) {
			return;
		}
		printf( '<div class="notice notice-error"><p><strong>Website không gửi được email thông báo:</strong> %s. Yêu cầu báo giá vẫn được lưu trong hộp thư.</p></div>', esc_html( $err ) );
	}
);
