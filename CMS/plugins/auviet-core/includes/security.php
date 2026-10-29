<?php
/**
 * QT-11 Người dùng và bảo mật đăng nhập (mục 8 Tài liệu mô tả công nghệ):
 * khoá tài khoản (đăng xuất ngay), lần đăng nhập gần nhất, khoá tạm khi nhập sai nhiều lần,
 * bắt buộc xác thực 2 lớp với Quản trị viên (plugin Two Factor), tắt XML-RPC, chặn dò tên người dùng.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Cấu hình bảo mật                                                    */
/* ------------------------------------------------------------------ */

function av_security_defaults(): array {
	return array(
		// Ở máy local mặc định không bắt buộc để không khoá người phát triển; staging, production bắt buộc.
		'enforce_2fa'  => 'local' === wp_get_environment_type() ? 0 : 1,
		'max_failures' => 5,
		'lock_minutes' => 15,
	);
}

function av_security( ?string $key = null ) {
	$s = wp_parse_args( (array) get_option( 'av_security', array() ), av_security_defaults() );
	return null === $key ? $s : $s[ $key ];
}

/* ------------------------------------------------------------------ */
/* Khoá tài khoản                                                      */
/* ------------------------------------------------------------------ */

function av_user_is_locked( int $user_id ): bool {
	return (bool) get_user_meta( $user_id, 'av_locked', true );
}

/**
 * Số Quản trị viên đang hoạt động (không bị khoá).
 */
function av_active_admin_count(): int {
	$n = 0;
	foreach ( get_users( array( 'role' => 'administrator', 'fields' => 'ID' ) ) as $id ) {
		if ( ! av_user_is_locked( (int) $id ) ) {
			++$n;
		}
	}
	return $n;
}

/**
 * Khoá hoặc mở khoá. Trả về thông báo lỗi, hoặc chuỗi rỗng nếu thành công.
 */
function av_set_user_lock( int $user_id, bool $lock ): string {
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return 'Không tìm thấy tài khoản.';
	}
	if ( $lock && get_current_user_id() === $user_id ) {
		return 'Không thể tự khoá tài khoản của mình.';
	}
	if ( $lock && in_array( 'administrator', (array) $user->roles, true ) && av_active_admin_count() <= 1 ) {
		return 'Không thể khoá Quản trị viên cuối cùng còn hoạt động.';
	}
	if ( $lock ) {
		update_user_meta( $user_id, 'av_locked', time() );
		WP_Session_Tokens::get_instance( $user_id )->destroy_all(); // Đăng xuất ngay mọi phiên.
		av_log( 'Khoá tài khoản {target}', array( 'target' => $user->user_login ), 'warning' );
	} else {
		delete_user_meta( $user_id, 'av_locked' );
		av_log( 'Mở khoá tài khoản {target}', array( 'target' => $user->user_login ), 'notice' );
	}
	return '';
}

// Tài khoản bị khoá không đăng nhập được.
add_filter(
	'authenticate',
	static function ( $user ) {
		if ( $user instanceof WP_User && av_user_is_locked( $user->ID ) ) {
			return new WP_Error( 'av_locked', 'Tài khoản này đã bị khoá. Liên hệ Quản trị viên nếu cần mở lại.' );
		}
		return $user;
	},
	99
);

// Phòng trường hợp còn phiên cũ: phát hiện là đăng xuất.
add_action(
	'init',
	static function () {
		$id = get_current_user_id();
		if ( $id && av_user_is_locked( $id ) ) {
			wp_logout();
			wp_safe_redirect( wp_login_url() );
			exit;
		}
	},
	1
);

// Lần đăng nhập gần nhất.
add_action(
	'wp_login',
	static function ( string $login, WP_User $user ) {
		update_user_meta( $user->ID, 'av_last_login', time() );
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* Khoá tạm khi nhập sai nhiều lần                                     */
/* ------------------------------------------------------------------ */

function av_client_ip(): string {
	return preg_replace( '/[^0-9a-fA-F:.]/', '', (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
}

function av_lockout_key(): string {
	return 'av_login_fail_' . md5( av_client_ip() );
}

add_action(
	'wp_login_failed',
	static function ( string $username ) {
		$key  = av_lockout_key();
		$data = (array) get_transient( $key ) + array( 'n' => 0, 'until' => 0 );
		++$data['n'];
		$max = (int) av_security( 'max_failures' );
		if ( $data['n'] >= $max && ! $data['until'] ) {
			$data['until'] = time() + 60 * (int) av_security( 'lock_minutes' );
			av_log( 'Khoá tạm đăng nhập từ IP {ip} sau {n} lần nhập sai (tên đăng nhập gần nhất: {username})', array( 'ip' => av_client_ip(), 'n' => $data['n'], 'username' => sanitize_user( $username ) ), 'warning' );
		}
		set_transient( $key, $data, 60 * (int) av_security( 'lock_minutes' ) );
	}
);

add_filter(
	'authenticate',
	static function ( $user ) {
		$data = get_transient( av_lockout_key() );
		if ( is_array( $data ) && ! empty( $data['until'] ) && $data['until'] > time() ) {
			return new WP_Error( 'av_lockout', sprintf( 'Nhập sai quá nhiều lần. Thử lại sau %d phút.', (int) ceil( ( $data['until'] - time() ) / 60 ) ) );
		}
		return $user;
	},
	100 // Sau bước kiểm tra mật khẩu của WordPress để không bị ghi đè.
);

add_action(
	'wp_login',
	static function () {
		delete_transient( av_lockout_key() );
	}
);

/* ------------------------------------------------------------------ */
/* Thiết lập làm sẵn: XML-RPC, dò tên người dùng, mật khẩu ứng dụng    */
/* ------------------------------------------------------------------ */

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter(
	'wp_headers',
	static function ( array $h ): array {
		unset( $h['X-Pingback'] );
		return $h;
	}
);
add_filter( 'wp_is_application_passwords_available', '__return_false' );

// /?author=1 không lộ tên đăng nhập.
add_action(
	'template_redirect',
	static function () {
		if ( ! is_user_logged_in() && ( is_author() || isset( $_GET['author'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	0
);

// API danh sách người dùng chỉ cho người đã đăng nhập.
add_filter(
	'rest_endpoints',
	static function ( array $endpoints ): array {
		if ( ! is_user_logged_in() ) {
			foreach ( array_keys( $endpoints ) as $route ) {
				if ( str_starts_with( $route, '/wp/v2/users' ) ) {
					unset( $endpoints[ $route ] );
				}
			}
		}
		return $endpoints;
	}
);

// Thông báo lỗi đăng nhập chung chung, không cho biết tên đăng nhập có tồn tại hay không.
add_filter(
	'login_errors',
	static function ( $msg ) {
		if ( str_contains( (string) $msg, 'đã bị khoá' ) || str_contains( (string) $msg, 'quá nhiều lần' ) || str_contains( (string) $msg, 'Lỗi xác thực' ) ) {
			return $msg;
		}
		return '<strong>Lỗi:</strong> Tên đăng nhập hoặc mật khẩu không đúng.';
	}
);

/* ------------------------------------------------------------------ */
/* Xác thực 2 lớp (plugin Two Factor)                                  */
/* ------------------------------------------------------------------ */

function av_two_factor_ready(): bool {
	return class_exists( 'Two_Factor_Core' );
}

function av_user_has_2fa( int $user_id ): bool {
	return av_two_factor_ready() && Two_Factor_Core::is_user_using_two_factor( $user_id );
}

function av_user_needs_2fa( WP_User $user ): bool {
	return (bool) av_security( 'enforce_2fa' ) && in_array( 'administrator', (array) $user->roles, true );
}

// Chỉ dùng ứng dụng xác thực (TOTP) và mã dự phòng, không dùng mã qua email (theo mục 8).
add_filter(
	'two_factor_providers',
	static function ( array $providers ): array {
		unset( $providers['Two_Factor_Email'], $providers['Two_Factor_Dummy'] );
		return $providers;
	}
);

// Quản trị viên chưa bật 2 lớp: chỉ được vào trang Hồ sơ để cài đặt.
add_action(
	'admin_init',
	static function () {
		if ( wp_doing_ajax() || ! is_user_logged_in() ) {
			return;
		}
		$user = wp_get_current_user();
		if ( ! av_user_needs_2fa( $user ) || av_user_has_2fa( $user->ID ) || ! av_two_factor_ready() ) {
			return;
		}
		global $pagenow;
		if ( in_array( $pagenow, array( 'profile.php', 'admin-post.php' ), true ) ) {
			return;
		}
		wp_safe_redirect( admin_url( 'profile.php?av-2fa=1#two-factor-options' ) );
		exit;
	}
);

add_action(
	'admin_notices',
	static function () {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$user = wp_get_current_user();
		if ( ! in_array( 'administrator', (array) $user->roles, true ) ) {
			return;
		}
		if ( ! av_two_factor_ready() ) {
			echo '<div class="notice notice-warning"><p><strong>Chưa có xác thực 2 lớp.</strong> Cài plugin Two Factor (chạy lại lệnh cài đặt setup-wp.sh) để bật lớp bảo vệ này cho Quản trị viên.</p></div>';
			return;
		}
		if ( av_user_has_2fa( $user->ID ) ) {
			return;
		}
		$msg = av_user_needs_2fa( $user )
			? '<strong>Bắt buộc bật xác thực 2 lớp.</strong> Quản trị viên phải bật xác thực 2 lớp trước khi dùng trang quản trị: ở mục "Two-Factor Options" bên dưới, chọn "Authenticator App", quét mã QR bằng Google Authenticator hoặc Microsoft Authenticator, nhập mã 6 số, rồi tạo và cất giữ mã dự phòng.'
			: '<strong>Nên bật xác thực 2 lớp.</strong> Vào <a href="' . esc_url( admin_url( 'profile.php#two-factor-options' ) ) . '">Hồ sơ</a> › Two-Factor Options. Môi trường thử đang để tuỳ chọn; trên website thật sẽ bắt buộc với Quản trị viên.';
		printf( '<div class="notice notice-%s"><p>%s</p></div>', av_user_needs_2fa( $user ) ? 'error' : 'info', wp_kses_post( $msg ) );
	}
);

/* ------------------------------------------------------------------ */
/* Danh sách người dùng: trạng thái, 2 lớp, đăng nhập gần nhất, khoá   */
/* ------------------------------------------------------------------ */

add_filter(
	'manage_users_columns',
	static function ( array $cols ): array {
		unset( $cols['posts'], $cols['two-factor'] ); // Cột riêng của plugin Two Factor trùng với cột "Xác thực 2 lớp".
		$cols['av_status'] = 'Trạng thái';
		$cols['av_2fa']    = 'Xác thực 2 lớp';
		$cols['av_last']   = 'Đăng nhập gần nhất';
		return $cols;
	},
	99
);

add_filter(
	'manage_users_custom_column',
	static function ( $out, string $col, int $user_id ) {
		switch ( $col ) {
			case 'av_status':
				return av_user_is_locked( $user_id ) ? '<span class="av-warn">Đã khoá</span>' : '<span class="av-ok">Hoạt động</span>';
			case 'av_2fa':
				if ( ! av_two_factor_ready() ) {
					return '<span class="description">Chưa cài</span>';
				}
				return av_user_has_2fa( $user_id ) ? '<span class="av-ok">Đã bật</span>' : ( av_user_needs_2fa( get_userdata( $user_id ) ) ? '<span class="av-warn">Chưa bật (bắt buộc)</span>' : 'Chưa bật' );
			case 'av_last':
				$t = (int) get_user_meta( $user_id, 'av_last_login', true );
				return $t ? esc_html( wp_date( 'd/m/Y H:i', $t ) ) : '—';
		}
		return $out;
	},
	10,
	3
);

add_filter(
	'user_row_actions',
	static function ( array $actions, WP_User $user ): array {
		if ( ! current_user_can( 'edit_users' ) || get_current_user_id() === $user->ID ) {
			return $actions;
		}
		$locked = av_user_is_locked( $user->ID );
		$url    = wp_nonce_url( admin_url( 'admin-post.php?action=av_user_lock&user=' . $user->ID . '&lock=' . ( $locked ? 0 : 1 ) ), 'av_user_lock_' . $user->ID );
		$actions['av_lock'] = sprintf( '<a href="%s"%s>%s</a>', esc_url( $url ), $locked ? '' : ' style="color:#b32d2e"', $locked ? 'Mở khoá' : 'Khoá tài khoản' );
		unset( $actions['delete'] ); // Nghỉ việc thì khoá, không xoá, để giữ lịch sử ai làm gì.
		return $actions;
	},
	10,
	2
);

add_action(
	'admin_post_av_user_lock',
	static function () {
		$user_id = absint( $_GET['user'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! current_user_can( 'edit_users' ) || ! check_admin_referer( 'av_user_lock_' . $user_id ) ) {
			wp_die( 'Không có quyền.' );
		}
		$err = av_set_user_lock( $user_id, ! empty( $_GET['lock'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( add_query_arg( $err ? array( 'av_lock_err' => rawurlencode( $err ) ) : array( 'av_lock_ok' => 1 ), admin_url( 'users.php' ) ) );
		exit;
	}
);

add_action(
	'admin_notices',
	static function () {
		$screen = get_current_screen();
		if ( ! $screen || 'users' !== $screen->id ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['av_lock_err'] ) ) {
			printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( sanitize_text_field( wp_unslash( $_GET['av_lock_err'] ) ) ) );
		} elseif ( isset( $_GET['av_lock_ok'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>Đã cập nhật trạng thái tài khoản. Tài khoản bị khoá đã bị đăng xuất khỏi mọi thiết bị.</p></div>';
		}
		// phpcs:enable
		echo '<div class="notice notice-info"><p>Khi nhân viên nghỉ việc: bấm <strong>Khoá tài khoản</strong> (không xoá, để giữ lịch sử thao tác). Thêm nhóm quyền ở <a href="' . esc_url( admin_url( 'users.php?page=av-roles' ) ) . '">Người dùng › Nhóm quyền</a>.</p></div>';
	}
);

// Ô khoá trên trang sửa người dùng.
add_action(
	'edit_user_profile',
	static function ( WP_User $user ) {
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}
		$locked = av_user_is_locked( $user->ID );
		wp_nonce_field( 'av_lock_profile', 'av_lock_nonce' );
		printf(
			'<h2>Trạng thái tài khoản</h2><table class="form-table" role="presentation"><tr><th>Khoá tài khoản</th><td><label><input type="checkbox" name="av_locked" value="1"%s> Khoá (đăng xuất ngay, không đăng nhập được)</label>%s</td></tr></table>',
			checked( $locked, true, false ),
			$locked ? '<p class="description">Khoá lúc ' . esc_html( wp_date( 'd/m/Y H:i', (int) get_user_meta( $user->ID, 'av_locked', true ) ) ) . '</p>' : ''
		);
	},
	5
);

add_action(
	'edit_user_profile_update',
	static function ( int $user_id ) {
		if ( ! current_user_can( 'edit_users' ) || ! isset( $_POST['av_lock_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_lock_nonce'] ), 'av_lock_profile' ) ) {
			return;
		}
		$want = ! empty( $_POST['av_locked'] );
		if ( $want !== av_user_is_locked( $user_id ) ) {
			$err = av_set_user_lock( $user_id, $want );
			if ( $err ) {
				add_action( 'user_profile_update_errors', static fn( $errors ) => $errors->add( 'av_lock', $err ) );
			}
		}
	}
);

/* ------------------------------------------------------------------ */
/* Trang Âu Việt › Bảo mật                                             */
/* ------------------------------------------------------------------ */

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'auviet', 'Bảo mật, nhật ký', 'Bảo mật, nhật ký', 'manage_options', 'auviet-security', 'av_render_security_page' );
	},
	20
);

add_action(
	'admin_init',
	static function () {
		register_setting(
			'av_security_group',
			'av_security',
			array(
				'sanitize_callback' => static function ( $in ) {
					$in  = (array) $in;
					$out = array(
						'enforce_2fa'  => empty( $in['enforce_2fa'] ) ? 0 : 1,
						'max_failures' => max( 3, min( 20, absint( $in['max_failures'] ?? 5 ) ) ),
						'lock_minutes' => max( 5, min( 1440, absint( $in['lock_minutes'] ?? 15 ) ) ),
					);
					$old = av_security();
					if ( $old !== $out ) {
						av_log( 'Đổi cài đặt bảo mật: bắt buộc 2 lớp {enforce}, khoá sau {max} lần sai trong {min} phút', array( 'enforce' => $out['enforce_2fa'] ? 'bật' : 'tắt', 'max' => $out['max_failures'], 'min' => $out['lock_minutes'] ), 'warning' );
					}
					return $out;
				},
			)
		);
	}
);

function av_render_security_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Bạn không có quyền xem trang này.' );
	}
	$s = av_security();
	?>
	<div class="wrap">
		<h1>Bảo mật và nhật ký hoạt động</h1>
		<?php av_saved_notice(); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'av_security_group' ); ?>
			<h2>Đăng nhập</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Xác thực 2 lớp</th>
					<td>
						<label><input type="checkbox" name="av_security[enforce_2fa]" value="1"<?php checked( $s['enforce_2fa'] ); ?>> Bắt buộc với Quản trị viên</label>
						<p class="description">Quản trị viên chưa bật 2 lớp sẽ chỉ vào được trang Hồ sơ để cài đặt. Trạng thái plugin Two Factor: <?php echo av_two_factor_ready() ? '<strong class="av-ok">đã cài</strong>' : '<strong class="av-warn">chưa cài</strong> (chạy lại setup-wp.sh)'; ?>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Khoá tạm khi nhập sai</th>
					<td>
						Sau <input type="number" min="3" max="20" name="av_security[max_failures]" value="<?php echo (int) $s['max_failures']; ?>" style="width:64px"> lần sai, khoá đăng nhập từ địa chỉ IP đó trong <input type="number" min="5" max="1440" name="av_security[lock_minutes]" value="<?php echo (int) $s['lock_minutes']; ?>" style="width:72px"> phút.
					</td>
				</tr>
				<tr>
					<th scope="row">Thiết lập làm sẵn</th>
					<td>Đã tắt XML-RPC và mật khẩu ứng dụng; chặn dò tên người dùng qua ?author= và API; thông báo lỗi đăng nhập không cho biết tên đăng nhập có tồn tại; tắt sửa file trong trang quản trị.</td>
				</tr>
			</table>
			<?php submit_button( 'Lưu cài đặt bảo mật' ); ?>
		</form>
		<?php av_render_log_archive_section(); ?>
	</div>
	<?php
}
