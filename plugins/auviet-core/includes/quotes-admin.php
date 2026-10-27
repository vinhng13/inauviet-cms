<?php
/**
 * QT-07 Hộp thư yêu cầu báo giá trong trang quản trị.
 */

defined( 'ABSPATH' ) || exit;

// Cột danh sách.
add_filter(
	'manage_av_quote_posts_columns',
	static function (): array {
		return array(
			'cb'        => '<input type="checkbox">',
			'title'     => 'Khách',
			'av_phone'  => 'Số điện thoại',
			'av_need'   => 'Nhu cầu',
			'av_status' => 'Trạng thái',
			'av_source' => 'Trang gửi',
			'date'      => 'Thời điểm',
		);
	}
);

add_action(
	'manage_av_quote_posts_custom_column',
	static function ( string $col, int $post_id ) {
		switch ( $col ) {
			case 'av_phone':
				$p = (string) get_post_meta( $post_id, '_av_phone', true );
				printf( '<a href="%s">%s</a>', esc_url( av_tel_href( $p ) ), esc_html( $p ) );
				break;
			case 'av_need':
				echo esc_html( (string) get_post_meta( $post_id, '_av_need', true ) );
				break;
			case 'av_status':
				$s = (string) get_post_meta( $post_id, '_av_status', true );
				printf( '<span class="av-status av-status--%s">%s</span>', esc_attr( $s ), esc_html( av_quote_statuses()[ $s ] ?? $s ) );
				break;
			case 'av_source':
				$src = (string) get_post_meta( $post_id, '_av_source', true );
				echo esc_html( $src ? wp_parse_url( $src, PHP_URL_PATH ) : '—' );
				break;
		}
	},
	10,
	2
);

// Lọc theo trạng thái.
add_action(
	'restrict_manage_posts',
	static function ( string $post_type ) {
		if ( 'av_quote' !== $post_type ) {
			return;
		}
		$cur = isset( $_GET['av_status'] ) ? sanitize_key( wp_unslash( $_GET['av_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		echo '<label class="screen-reader-text" for="av-status-filter">Lọc theo trạng thái</label><select id="av-status-filter" name="av_status"><option value="">Mọi trạng thái</option>';
		foreach ( av_quote_statuses() as $k => $v ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $cur, $k, false ), esc_html( $v ) );
		}
		echo '</select>';
	}
);

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || 'av_quote' !== $q->get( 'post_type' ) ) {
			return;
		}
		$s = isset( $_GET['av_status'] ) ? sanitize_key( wp_unslash( $_GET['av_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $s && isset( av_quote_statuses()[ $s ] ) ) {
			$q->set( 'meta_query', array( array( 'key' => '_av_status', 'value' => $s ) ) );
		}
	}
);

// Hộp thông tin khách và hộp xử lý.
add_action(
	'add_meta_boxes_av_quote',
	static function () {
		add_meta_box( 'av-quote-customer', 'Thông tin khách', 'av_quote_box_customer', 'av_quote', 'normal', 'high' );
		add_meta_box( 'av-quote-handle', 'Xử lý', 'av_quote_box_handle', 'av_quote', 'side', 'high' );
		add_meta_box( 'av-quote-history', 'Lịch sử trạng thái', 'av_quote_box_history', 'av_quote', 'normal', 'default' );
	}
);

function av_quote_box_customer( WP_Post $post ): void {
	wp_nonce_field( 'av_quote_save', 'av_quote_nonce' );
	$m      = static fn( $k ) => (string) get_post_meta( $post->ID, $k, true );
	$fields = array(
		'_av_name'  => array( 'Họ tên', 'text' ),
		'_av_phone' => array( 'Số điện thoại', 'tel' ),
		'_av_email' => array( 'Email', 'email' ),
	);
	echo '<table class="form-table" role="presentation">';
	foreach ( $fields as $k => $f ) {
		printf(
			'<tr><th><label for="%1$s">%2$s</label></th><td><input class="regular-text" type="%3$s" id="%1$s" name="%1$s" value="%4$s"></td></tr>',
			esc_attr( $k ),
			esc_html( $f[0] ),
			esc_attr( $f[1] ),
			esc_attr( $m( $k ) )
		);
	}
	echo '<tr><th><label for="_av_need">Sản phẩm quan tâm</label></th><td><select id="_av_need" name="_av_need">';
	foreach ( av_quote_needs() as $n ) {
		printf( '<option%s>%s</option>', selected( $m( '_av_need' ), $n, false ), esc_html( $n ) );
	}
	echo '</select></td></tr>';
	printf( '<tr><th><label for="_av_note">Nội dung</label></th><td><textarea class="large-text" rows="4" id="_av_note" name="_av_note">%s</textarea></td></tr>', esc_textarea( $m( '_av_note' ) ) );
	if ( $m( '_av_file' ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=av_quote_file&id=' . $post->ID ), 'av_quote_file_' . $post->ID );
		printf( '<tr><th>File khách gửi</th><td><a class="button" href="%s">Tải %s</a></td></tr>', esc_url( $url ), esc_html( $m( '_av_file_name' ) ) );
	}
	if ( $m( '_av_source' ) ) {
		printf( '<tr><th>Trang gửi</th><td><a href="%1$s" target="_blank" rel="noopener">%1$s</a></td></tr>', esc_url( $m( '_av_source' ) ) );
	}
	echo '</table>';
}

function av_quote_box_handle( WP_Post $post ): void {
	$status = (string) get_post_meta( $post->ID, '_av_status', true ) ?: 'moi';
	echo '<p><label for="_av_status"><strong>Trạng thái</strong></label><br><select id="_av_status" name="_av_status" style="width:100%">';
	foreach ( av_quote_statuses() as $k => $v ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $status, $k, false ), esc_html( $v ) );
	}
	echo '</select></p>';
	printf( '<p><label for="_av_assignee"><strong>Người phụ trách</strong></label><br><input type="text" id="_av_assignee" name="_av_assignee" value="%s" style="width:100%%"></p>', esc_attr( (string) get_post_meta( $post->ID, '_av_assignee', true ) ) );
	printf( '<p><label for="_av_internal"><strong>Ghi chú nội bộ</strong></label><br><textarea id="_av_internal" name="_av_internal" rows="4" style="width:100%%">%s</textarea></p>', esc_textarea( (string) get_post_meta( $post->ID, '_av_internal', true ) ) );
}

function av_quote_box_history( WP_Post $post ): void {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}av_quote_log WHERE quote_id = %d ORDER BY id DESC", $post->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( ! $rows ) {
		echo '<p>Chưa có thay đổi.</p>';
		return;
	}
	$labels = av_quote_statuses();
	echo '<table class="widefat striped"><thead><tr><th>Thời điểm</th><th>Từ</th><th>Sang</th><th>Người đổi</th></tr></thead><tbody>';
	foreach ( $rows as $r ) {
		$user = $r->user_id ? get_userdata( (int) $r->user_id ) : null;
		printf(
			'<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
			esc_html( wp_date( 'd/m/Y H:i', strtotime( $r->changed_at . ' UTC' ) ) ),
			esc_html( $labels[ $r->old_status ] ?? '—' ),
			esc_html( $labels[ $r->new_status ] ?? $r->new_status ),
			esc_html( $user ? $user->display_name : 'Website' )
		);
	}
	echo '</tbody></table>';
}

add_action(
	'save_post_av_quote',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['av_quote_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['av_quote_nonce'] ) ), 'av_quote_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$text = array( '_av_name', '_av_phone', '_av_need', '_av_assignee' );
		foreach ( $text as $k ) {
			if ( isset( $_POST[ $k ] ) ) {
				update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
			}
		}
		if ( isset( $_POST['_av_email'] ) ) {
			update_post_meta( $post_id, '_av_email', sanitize_email( wp_unslash( $_POST['_av_email'] ) ) );
		}
		foreach ( array( '_av_note', '_av_internal' ) as $k ) {
			if ( isset( $_POST[ $k ] ) ) {
				update_post_meta( $post_id, $k, sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ) );
			}
		}
		$new = isset( $_POST['_av_status'] ) ? sanitize_key( wp_unslash( $_POST['_av_status'] ) ) : '';
		$old = (string) get_post_meta( $post_id, '_av_status', true );
		if ( $new && isset( av_quote_statuses()[ $new ] ) && $new !== $old ) {
			update_post_meta( $post_id, '_av_status', $new );
			av_quote_log( $post_id, $old, $new, get_current_user_id() );
		}
		// Tiêu đề = Họ tên – SĐT để danh sách dễ đọc.
		$title = trim( get_post_meta( $post_id, '_av_name', true ) . ' – ' . get_post_meta( $post_id, '_av_phone', true ), ' –' );
		if ( $title && get_post_field( 'post_title', $post_id ) !== $title ) {
			remove_all_actions( 'save_post_av_quote' );
			wp_update_post( array( 'ID' => $post_id, 'post_title' => $title ) );
		}
	}
);

function av_quote_log( int $quote_id, string $old, string $new, int $user_id ): void {
	global $wpdb;
	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prefix . 'av_quote_log',
		array(
			'quote_id'   => $quote_id,
			'old_status' => $old,
			'new_status' => $new,
			'user_id'    => $user_id,
			'changed_at' => current_time( 'mysql', true ),
		),
		array( '%d', '%s', '%s', '%d', '%s' )
	);
	if ( function_exists( 'SimpleLogger' ) && $user_id ) {
		SimpleLogger()->info( 'Đổi trạng thái yêu cầu báo giá #{quote_id}: {old} → {new}', array( 'quote_id' => $quote_id, 'old' => $old, 'new' => $new ) );
	}
}

// Tải file khách gửi: chỉ người có quyền sửa yêu cầu đó.
add_action(
	'admin_post_av_quote_file',
	static function () {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'av_quote_file_' . $id );
		if ( ! $id || 'av_quote' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'Bạn không có quyền tải file này.', 403 );
		}
		$rel  = (string) get_post_meta( $id, '_av_file', true );
		$path = av_quote_upload_dir() . '/' . $rel;
		$real = realpath( $path );
		if ( ! $rel || ! $real || ! str_starts_with( $real, realpath( av_quote_upload_dir() ) ) ) {
			wp_die( 'Không tìm thấy file.', 404 );
		}
		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . rawurlencode( (string) get_post_meta( $id, '_av_file_name', true ) ) . '"' );
		header( 'Content-Length: ' . filesize( $real ) );
		readfile( $real ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

// QT-01: số yêu cầu trên Bảng tin (chỉ Quản trị viên).
add_action(
	'wp_dashboard_setup',
	static function () {
		if ( ! current_user_can( 'edit_av_quotes' ) ) {
			return;
		}
		wp_add_dashboard_widget(
			'av_quotes_widget',
			'Yêu cầu báo giá',
			static function () {
				$counts = array();
				foreach ( av_quote_statuses() as $k => $label ) {
					$q            = new WP_Query(
						array(
							'post_type'      => 'av_quote',
							'post_status'    => 'publish',
							'meta_key'       => '_av_status', // phpcs:ignore WordPress.DB.SlowDBQuery
							'meta_value'     => $k, // phpcs:ignore WordPress.DB.SlowDBQuery
							'fields'         => 'ids',
							'posts_per_page' => 1,
						)
					);
					$counts[ $k ] = array( $label, (int) $q->found_posts );
				}
				echo '<ul>';
				foreach ( $counts as $k => $c ) {
					printf( '<li><a href="%s">%s: <strong>%d</strong></a></li>', esc_url( admin_url( 'edit.php?post_type=av_quote&av_status=' . $k ) ), esc_html( $c[0] ), (int) $c[1] );
				}
				echo '</ul>';
			}
		);
	}
);

// Màu trạng thái trong danh sách.
add_action(
	'admin_head-edit.php',
	static function () {
		if ( 'av_quote' !== get_current_screen()->post_type ) {
			return;
		}
		echo '<style>.av-status{display:inline-block;padding:2px 8px;border-radius:99px;background:#eef1f5;font-weight:600}.av-status--moi{background:#e1f3fc;color:#00577d}.av-status--chot{background:#e5f4e4;color:#1d5c1a}.av-status--huy{background:#f0f0f0;color:#555}</style>';
	}
);

// Thông báo sau khi lưu dùng đúng ngôn ngữ "yêu cầu" thay vì "bài viết".
add_filter(
	'post_updated_messages',
	static function ( $messages ) {
		$saved              = 'Đã lưu yêu cầu báo giá.';
		$messages['av_quote'] = array_fill( 0, 11, $saved );
		$messages['av_quote'][0] = '';
		return $messages;
	}
);
