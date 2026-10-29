<?php
/**
 * QT-12 Nhật ký hoạt động: dùng plugin Simple History (tự ghi đăng nhập, nội dung, người dùng, plugin, cài đặt)
 * cộng thêm sự kiện riêng của Âu Việt (giá, báo giá, khoá tài khoản, nhóm quyền, cấu hình liên hệ, bảo mật).
 * Chỉ người có quyền av_view_activity_log (Quản trị viên) xem được. Mỗi ngày tự xuất các dòng mới ra file CSV
 * theo tháng (thư mục không công khai) để lưu cùng bản sao lưu, vì Simple History chỉ giữ dữ liệu có hạn.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ghi một sự kiện vào nhật ký. $message dùng {khoá} lấy từ $context.
 */
function av_log( string $message, array $context = array(), string $level = 'info' ): void {
	if ( ! function_exists( 'SimpleLogger' ) ) {
		return;
	}
	$level = in_array( $level, array( 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency', 'debug' ), true ) ? $level : 'info';
	$context['av_role'] = av_current_role_label();
	SimpleLogger()->$level( $message, $context );
}

function av_current_role_label(): string {
	$u = wp_get_current_user();
	if ( ! $u || ! $u->exists() ) {
		return 'Khách';
	}
	return implode( ', ', array_map( 'av_role_label', (array) $u->roles ) );
}

// Ai được xem nhật ký: quyền riêng (mặc định chỉ Quản trị viên; nhóm tự tạo có thể được cấp).
add_filter( 'simple_history/view_history_capability', static fn() => 'av_view_activity_log' );
add_filter( 'simple_history/view_settings_capability', static fn() => 'manage_options' );

// Ghi đủ địa chỉ IP (mặc định Simple History che phần cuối), theo yêu cầu QT-12. Nhật ký chỉ Quản trị viên xem.
add_filter( 'simple_history/privacy/anonymize_ip_address', '__return_false' );

// Thêm vai trò người làm vào mọi dòng nhật ký (kể cả sự kiện do Simple History tự ghi).
add_filter(
	'simple_history/log_argument/context',
	static function ( $context ) {
		if ( is_array( $context ) && ! isset( $context['av_role'] ) && is_user_logged_in() ) {
			$context['av_role'] = av_current_role_label();
		}
		return $context;
	}
);

/* ------------------------------------------------------------------ */
/* Sự kiện riêng của Âu Việt                                           */
/* ------------------------------------------------------------------ */

add_action(
	'update_option_av_contact',
	static function ( $old, $new ) {
		$changed = array();
		foreach ( (array) $new as $k => $v ) {
			if ( wp_json_encode( $v ) !== wp_json_encode( ( (array) $old )[ $k ] ?? null ) ) {
				$changed[] = $k;
			}
		}
		if ( $changed ) {
			av_log( 'Đổi cấu hình liên hệ và công ty: {fields}', array( 'fields' => implode( ', ', $changed ) ), 'notice' );
		}
	},
	10,
	2
);

add_action(
	'update_option_av_campaign',
	static function ( $old, $new ) {
		av_log( 'Sửa banner chiến dịch: "{title}" ({state})', array( 'title' => $new['title'] ?? '', 'state' => empty( $new['enabled'] ) ? 'tắt' : 'bật' ) );
	},
	10,
	2
);

add_action(
	'wp_trash_post',
	static function ( int $post_id ) {
		if ( 'av_quote' === get_post_type( $post_id ) ) {
			av_log( 'Bỏ yêu cầu báo giá "{title}" vào thùng rác', array( 'title' => get_the_title( $post_id ), 'quote_id' => $post_id ), 'warning' );
		}
	}
);

add_action(
	'before_delete_post',
	static function ( int $post_id ) {
		if ( 'av_quote' === get_post_type( $post_id ) ) {
			av_log( 'Xoá hẳn yêu cầu báo giá "{title}"', array( 'title' => get_the_title( $post_id ), 'quote_id' => $post_id ), 'warning' );
		}
	}
);

/* ------------------------------------------------------------------ */
/* Xuất CSV hằng ngày, gom theo tháng                                  */
/* ------------------------------------------------------------------ */

function av_log_dir(): string {
	$dir = wp_get_upload_dir()['basedir'] . '/av-private/nhat-ky';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	$base = dirname( $dir );
	if ( ! file_exists( $base . '/.htaccess' ) ) {
		file_put_contents( $base . '/.htaccess', "RewriteEngine On\nRewriteRule .* - [F,L]\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	foreach ( array( $base, $dir ) as $d ) {
		if ( ! file_exists( $d . '/index.php' ) ) {
			file_put_contents( $d . '/index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
	}
	return $dir;
}

/**
 * Ghi các dòng nhật ký mới (chưa xuất) vào file CSV của tháng tương ứng. Trả về số dòng đã ghi.
 */
function av_log_export(): int {
	global $wpdb;
	$table = $wpdb->prefix . 'simple_history';
	$ctx_t = $wpdb->prefix . 'simple_history_contexts';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return 0;
	}
	$last  = (int) get_option( 'av_log_export_last_id', 0 );
	$total = 0;
	$dir   = av_log_dir();
	do {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT id, date, logger, level, message FROM {$table} WHERE id > %d ORDER BY id ASC LIMIT 1000", $last ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $rows ) {
			break;
		}
		$ids = implode( ',', array_map( static fn( $r ) => (int) $r->id, $rows ) );
		$ctx = array();
		foreach ( $wpdb->get_results( "SELECT history_id, `key`, value FROM {$ctx_t} WHERE history_id IN ({$ids})" ) as $c ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$ctx[ (int) $c->history_id ][ $c->key ] = $c->value;
		}
		$files = array();
		foreach ( $rows as $r ) {
			$c     = $ctx[ (int) $r->id ] ?? array();
			$local = get_date_from_gmt( $r->date, 'Y-m-d H:i:s' );
			$month = substr( $local, 0, 7 );
			$msg   = preg_replace_callback( '/\{([a-zA-Z0-9_]+)\}/', static fn( $m ) => $c[ $m[1] ] ?? $m[0], (string) $r->message );
			$role  = $c['av_role'] ?? '';
			if ( '' === $role && ! empty( $c['_user_id'] ) ) {
				$u    = get_userdata( (int) $c['_user_id'] );
				$role = $u ? implode( ', ', array_map( 'av_role_label', (array) $u->roles ) ) : '';
			}
			$files[ $month ][] = array(
				$local,
				$c['_user_login'] ?? ( $c['_user_email'] ?? 'Hệ thống' ),
				$role,
				$r->logger . ( isset( $c['_message_key'] ) ? ':' . $c['_message_key'] : '' ),
				$r->level,
				$msg,
				$c['_server_remote_addr'] ?? '',
			);
			$last = (int) $r->id;
		}
		foreach ( $files as $month => $lines ) {
			$path  = $dir . '/nhat-ky-' . $month . '.csv';
			$isnew = ! file_exists( $path );
			$fh    = fopen( $path, 'ab' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( ! $fh ) {
				continue;
			}
			if ( $isnew ) {
				fwrite( $fh, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- BOM để Excel đọc đúng tiếng Việt.
				fputcsv( $fh, array( 'Thời điểm', 'Người làm', 'Vai trò', 'Loại hành động', 'Mức', 'Hành động, đối tượng', 'IP' ), ',', '"', '' );
			}
			foreach ( $lines as $line ) {
				fputcsv( $fh, $line, ',', '"', '' );
			}
			fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$total += count( $lines );
		}
		update_option( 'av_log_export_last_id', $last, false );
	} while ( count( $rows ) === 1000 );
	update_option( 'av_log_export_last_run', time(), false );
	return $total;
}

add_action( 'av_log_export_daily', 'av_log_export' );
add_action(
	'init',
	static function () {
		if ( ! wp_next_scheduled( 'av_log_export_daily' ) ) {
			wp_schedule_event( strtotime( 'tomorrow 01:30' ) - (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ), 'daily', 'av_log_export_daily' );
		}
	}
);

add_action(
	'admin_post_av_log_export_now',
	static function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'av_log_export_now' ) ) {
			wp_die( 'Không có quyền.' );
		}
		$n = av_log_export();
		wp_safe_redirect( add_query_arg( 'av_exported', $n, admin_url( 'admin.php?page=auviet-security' ) ) );
		exit;
	}
);

add_action(
	'admin_post_av_log_download',
	static function () {
		$file = sanitize_file_name( wp_unslash( $_GET['file'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'av_log_download_' . $file ) || ! preg_match( '/^nhat-ky-\d{4}-\d{2}\.csv$/', $file ) ) {
			wp_die( 'Không có quyền.' );
		}
		$path = av_log_dir() . '/' . $file;
		if ( ! is_readable( $path ) ) {
			wp_die( 'Không tìm thấy file.' );
		}
		av_log( 'Tải file nhật ký {file}', array( 'file' => $file ) );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $file . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

function av_render_log_archive_section(): void {
	$files = glob( av_log_dir() . '/nhat-ky-*.csv' ) ?: array();
	rsort( $files );
	$last  = (int) get_option( 'av_log_export_last_run', 0 );
	$next  = wp_next_scheduled( 'av_log_export_daily' );
	echo '<h2>Nhật ký hoạt động</h2>';
	if ( isset( $_GET['av_exported'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		printf( '<div class="notice notice-success inline"><p>Đã xuất thêm %d dòng nhật ký.</p></div>', absint( $_GET['av_exported'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	printf(
		'<p>Xem, lọc theo người dùng, loại hành động, thời gian và xuất CSV/JSON ở <a href="%1$s">Nhật ký hoạt động</a> (Simple History). Mỗi ngày website tự chép các dòng mới ra file CSV theo tháng dưới đây, đặt trong thư mục không truy cập được từ web, để lưu cùng bản sao lưu.</p><p>Lần xuất gần nhất: %2$s. Lần tự xuất tiếp theo: %3$s.</p>',
		esc_url( admin_url( 'admin.php?page=simple_history_admin_menu_page' ) ),
		esc_html( $last ? wp_date( 'd/m/Y H:i', $last ) : 'chưa có' ),
		esc_html( $next ? wp_date( 'd/m/Y H:i', $next ) : 'chưa lên lịch' )
	);
	if ( $files ) {
		echo '<table class="widefat striped" style="max-width:640px"><thead><tr><th>Tháng</th><th>Dung lượng</th><th></th></tr></thead><tbody>';
		foreach ( $files as $f ) {
			$name = basename( $f );
			printf(
				'<tr><td>%s</td><td>%s</td><td><a class="button" href="%s">Tải CSV</a></td></tr>',
				esc_html( substr( $name, 8, 7 ) ),
				esc_html( size_format( (int) filesize( $f ) ) ),
				esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=av_log_download&file=' . $name ), 'av_log_download_' . $name ) )
			);
		}
		echo '</tbody></table>';
	}
	printf(
		'<form method="post" action="%s" style="margin-top:12px"><input type="hidden" name="action" value="av_log_export_now">%s<button class="button">Xuất các dòng mới ngay</button></form>',
		esc_url( admin_url( 'admin-post.php' ) ),
		wp_nonce_field( 'av_log_export_now', '_wpnonce', true, false )
	);
}
