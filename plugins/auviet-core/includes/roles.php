<?php
/**
 * QT-11 Nhóm quyền: ma trận quyền theo module, hành động (mục 4 Tài liệu mô tả chức năng)
 * và màn hình Người dùng › Nhóm quyền để Quản trị viên tạo nhóm mới bằng cách chọn từng quyền.
 *
 * Hai vai trò cố định (administrator, editor) chỉ xem, không sửa ở đây. Nhóm tự tạo có tiền tố av_role_.
 */

defined( 'ABSPATH' ) || exit;

const AV_ROLE_PREFIX = 'av_role_';

/**
 * Ma trận quyền: mã module => [tên, [khoá hành động => [nhãn, capability[]]]].
 * QT-11 (người dùng, plugin, cập nhật) chỉ dành cho Quản trị viên nên không có trong ma trận.
 */
function av_permission_matrix(): array {
	$page_caps = array( 'edit_pages', 'edit_others_pages', 'edit_published_pages', 'edit_private_pages', 'publish_pages', 'read_private_pages', 'delete_pages', 'delete_others_pages', 'delete_published_pages', 'delete_private_pages' );
	$post_caps = array( 'edit_posts', 'edit_others_posts', 'edit_published_posts', 'edit_private_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_others_posts', 'delete_published_posts', 'delete_private_posts', 'manage_categories' );
	$quote_rw  = array( 'edit_av_quotes', 'edit_others_av_quotes', 'edit_published_av_quotes', 'edit_private_av_quotes', 'publish_av_quotes', 'read_private_av_quotes' );
	$quote_del = array( 'delete_av_quotes', 'delete_others_av_quotes', 'delete_published_av_quotes', 'delete_private_av_quotes' );
	return array(
		'QT-02' => array( 'Trang và landing', array( 'manage' => array( 'Xem, thêm, sửa, xuất bản, xoá', $page_caps ) ) ),
		'QT-03' => array(
			'Sản phẩm, chất liệu',
			array(
				'products'  => array( 'Sản phẩm: xem, thêm, sửa, xuất bản, xoá', av_post_type_caps( 'av_products' ) ),
				'materials' => array( 'Chất liệu: xem, thêm, sửa, xuất bản, xoá', av_post_type_caps( 'av_materials' ) ),
			),
		),
		'QT-04' => array( 'Bảng giá, tính giá', array( 'prices' => array( 'Xem, sửa giá, nhập/xuất', array( 'av_manage_prices' ) ) ) ),
		'QT-05' => array(
			'Bài viết, tag, hỏi đáp',
			array(
				'posts' => array( 'Bài viết, chuyên mục, tag', $post_caps ),
				'faqs'  => array( 'Hỏi đáp', av_post_type_caps( 'av_faqs' ) ),
			),
		),
		'QT-06' => array( 'Mẫu đã làm', array( 'projects' => array( 'Xem, thêm, sửa, xuất bản, xoá', av_post_type_caps( 'av_projects' ) ) ) ),
		'QT-07' => array(
			'Yêu cầu báo giá',
			array(
				'quotes'        => array( 'Xem, đổi trạng thái, ghi chú', $quote_rw ),
				'quotes_delete' => array( 'Xoá yêu cầu', $quote_del ),
				'quotes_export' => array( 'Xuất Excel', array( 'av_export_quotes' ) ),
			),
		),
		'QT-08' => array( 'SEO và chuyển hướng', array( 'seo' => array( 'Sửa meta, thêm chuyển hướng 301', array( 'av_manage_seo', 'rank_math_onpage_general', 'rank_math_onpage_advanced', 'rank_math_onpage_snippet', 'rank_math_onpage_social', 'rank_math_redirections', 'rank_math_404_monitor' ) ) ) ),
		'QT-09' => array( 'Thư viện media', array( 'media' => array( 'Tải lên, sửa, xoá ảnh', array( 'upload_files' ) ) ) ),
		'QT-10' => array(
			'Cấu hình chung',
			array(
				'site_content' => array( 'Nội dung: banner, menu, nút nổi', array( 'av_manage_site_content' ) ),
				'site_system'  => array( 'Hệ thống: thông tin công ty, liên hệ, email', array( 'av_manage_site_system' ) ),
			),
		),
		'QT-12' => array( 'Nhật ký hoạt động', array( 'log' => array( 'Xem, lọc, xuất nhật ký', array( 'av_view_activity_log' ) ) ) ),
	);
}

/**
 * Các quyền một vai trò đang có theo ma trận: [ 'QT-03.products' => true, ... ].
 */
function av_role_actions( WP_Role $role ): array {
	$on = array();
	foreach ( av_permission_matrix() as $code => $module ) {
		foreach ( $module[1] as $key => $action ) {
			$on[ $code . '.' . $key ] = ! array_diff( $action[1], array_keys( array_filter( $role->capabilities ) ) )
				|| ( count( $action[1] ) > 3 && $role->has_cap( $action[1][0] ) ); // Bộ quyền nội dung: có quyền sửa là tính có.
		}
	}
	return $on;
}

function av_is_custom_role( string $slug ): bool {
	return str_starts_with( $slug, AV_ROLE_PREFIX );
}

/**
 * Gán đúng tập quyền đã chọn cho nhóm tự tạo (luôn có quyền "read" để vào trang quản trị).
 */
function av_apply_role_actions( WP_Role $role, array $chosen ): void {
	$want = array( 'read' => true );
	foreach ( av_permission_matrix() as $code => $module ) {
		foreach ( $module[1] as $key => $action ) {
			if ( ! empty( $chosen[ $code . '.' . $key ] ) ) {
				foreach ( $action[1] as $cap ) {
					$want[ $cap ] = true;
				}
			}
		}
	}
	foreach ( array_keys( $role->capabilities ) as $cap ) {
		if ( ! isset( $want[ $cap ] ) ) {
			$role->remove_cap( $cap );
		}
	}
	foreach ( array_keys( $want ) as $cap ) {
		$role->add_cap( $cap );
	}
}

/* ------------------------------------------------------------------ */
/* Màn hình Người dùng › Nhóm quyền                                    */
/* ------------------------------------------------------------------ */

add_action(
	'admin_menu',
	static function () {
		add_users_page( 'Nhóm quyền', 'Nhóm quyền', 'promote_users', 'av-roles', 'av_roles_page' );
	}
);

function av_roles_page(): void {
	if ( ! current_user_can( 'promote_users' ) ) {
		wp_die( 'Bạn không có quyền xem trang này.' );
	}
	$edit = isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$msg  = isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$msgs = array(
		'saved'   => 'Đã lưu nhóm quyền.',
		'deleted' => 'Đã xoá nhóm quyền. Người trong nhóm được chuyển sang Biên tập viên nếu không còn vai trò nào khác.',
		'name'    => 'Cần nhập tên nhóm.',
		'exists'  => 'Đã có nhóm trùng tên.',
	);
	echo '<div class="wrap av-roles"><h1 class="wp-heading-inline">Nhóm quyền</h1>';
	if ( 'new' !== $edit && ! $edit ) {
		printf( ' <a class="page-title-action" href="%s">Thêm nhóm</a>', esc_url( admin_url( 'users.php?page=av-roles&role=new' ) ) );
	}
	echo '<hr class="wp-header-end">';
	if ( $msg && isset( $msgs[ $msg ] ) ) {
		printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', in_array( $msg, array( 'name', 'exists' ), true ) ? 'error' : 'success', esc_html( $msgs[ $msg ] ) );
	}
	if ( $edit ) {
		av_roles_form( $edit );
	} else {
		av_roles_list();
	}
	echo '</div>';
}

function av_roles_list(): void {
	$counts = count_users();
	echo '<p>Hai vai trò cố định: <strong>Quản trị viên</strong> lo giá, khách hàng và hệ thống; <strong>Biên tập viên</strong> lo mọi thứ khách nhìn thấy trên web. Cần thêm nhóm (ví dụ "Kinh doanh" chỉ xử lý báo giá) thì bấm Thêm nhóm và chọn từng quyền. Một người có thể thuộc nhiều nhóm: chọn ở trang sửa người dùng.</p>';
	echo '<table class="widefat striped av-roles-table"><thead><tr><th>Nhóm</th><th>Số người</th><th>Quyền</th><th></th></tr></thead><tbody>';
	$roles = wp_roles()->roles;
	foreach ( array_unique( array_merge( array( 'administrator', 'editor' ), array_keys( $roles ) ) ) as $slug ) {
		if ( ! isset( $roles[ $slug ] ) || ( ! av_is_custom_role( $slug ) && ! in_array( $slug, array( 'administrator', 'editor' ), true ) ) ) {
			continue;
		}
		$role    = get_role( $slug );
		$actions = av_role_actions( $role );
		$labels  = array();
		foreach ( av_permission_matrix() as $code => $module ) {
			$parts = array();
			foreach ( $module[1] as $key => $action ) {
				if ( ! empty( $actions[ $code . '.' . $key ] ) ) {
					$parts[] = $action[0];
				}
			}
			if ( $parts ) {
				$labels[] = '<strong>' . esc_html( $module[0] ) . '</strong>' . ( count( $module[1] ) > 1 ? ': ' . esc_html( implode( '; ', $parts ) ) : '' );
			}
		}
		if ( 'administrator' === $slug ) {
			array_unshift( $labels, '<strong>Toàn quyền</strong>, gồm người dùng, plugin, cập nhật (QT-11)' );
		}
		printf(
			'<tr><td><strong>%1$s</strong>%2$s</td><td>%3$d</td><td>%4$s</td><td>%5$s</td></tr>',
			esc_html( translate_user_role( $roles[ $slug ]['name'] ) ),
			av_is_custom_role( $slug ) ? '' : '<br><span class="description">Cố định</span>',
			(int) ( $counts['avail_roles'][ $slug ] ?? 0 ),
			implode( '<br>', $labels ) ?: '—', // phpcs:ignore WordPress.Security.EscapeOutput -- đã escape từng phần.
			av_is_custom_role( $slug )
				? sprintf( '<a class="button" href="%s">Sửa</a>', esc_url( admin_url( 'users.php?page=av-roles&role=' . $slug ) ) )
				: ( 'editor' === $slug ? sprintf( '<a class="button-link" href="%s">Tạo nhóm mới từ đây</a>', esc_url( admin_url( 'users.php?page=av-roles&role=new&from=' . $slug ) ) ) : '' )
		);
	}
	echo '</tbody></table>';
}

function av_roles_form( string $slug ): void {
	$is_new = 'new' === $slug;
	if ( ! $is_new && ( ! av_is_custom_role( $slug ) || ! get_role( $slug ) ) ) {
		echo '<p>Không sửa được nhóm này. Hai vai trò cố định chỉ xem.</p>';
		return;
	}
	$from    = isset( $_GET['from'] ) ? sanitize_key( wp_unslash( $_GET['from'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$source  = $is_new ? ( $from && get_role( $from ) && 'administrator' !== $from ? get_role( $from ) : null ) : get_role( $slug );
	$actions = $source ? av_role_actions( $source ) : array();
	$name    = $is_new ? '' : translate_user_role( wp_roles()->roles[ $slug ]['name'] );
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="av-role-form">
		<input type="hidden" name="action" value="av_save_role">
		<input type="hidden" name="role" value="<?php echo esc_attr( $slug ); ?>">
		<?php wp_nonce_field( 'av_save_role' ); ?>
		<p><label for="av-role-name"><strong>Tên nhóm</strong></label><br>
		<input type="text" class="regular-text" id="av-role-name" name="name" value="<?php echo esc_attr( $name ); ?>" required placeholder="VD: Kinh doanh"></p>
		<table class="widefat striped av-matrix">
			<thead><tr><th>Mã</th><th>Module</th><th>Được làm</th></tr></thead>
			<tbody>
			<tr><td>QT-01</td><td>Bảng điều khiển</td><td><label><input type="checkbox" checked disabled> Xem (luôn có)</label></td></tr>
			<?php foreach ( av_permission_matrix() as $code => $module ) : ?>
				<tr>
					<td><?php echo esc_html( $code ); ?></td>
					<td><?php echo esc_html( $module[0] ); ?></td>
					<td>
						<?php foreach ( $module[1] as $key => $action ) : ?>
							<label><input type="checkbox" name="actions[<?php echo esc_attr( $code . '.' . $key ); ?>]" value="1"<?php checked( ! empty( $actions[ $code . '.' . $key ] ) ); ?>> <?php echo esc_html( $action[0] ); ?></label><br>
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<tr><td>QT-11</td><td>Người dùng, plugin, cập nhật, sao lưu</td><td><span class="description">Chỉ Quản trị viên</span></td></tr>
			</tbody>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary">Lưu nhóm quyền</button>
			<a class="button" href="<?php echo esc_url( admin_url( 'users.php?page=av-roles' ) ); ?>">Quay lại</a>
		</p>
	</form>
	<?php if ( ! $is_new ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Xoá nhóm quyền này?');">
			<input type="hidden" name="action" value="av_delete_role">
			<input type="hidden" name="role" value="<?php echo esc_attr( $slug ); ?>">
			<?php wp_nonce_field( 'av_delete_role' ); ?>
			<button type="submit" class="button-link button-link-delete">Xoá nhóm</button>
		</form>
	<?php endif; ?>
	<?php
}

add_action(
	'admin_post_av_save_role',
	static function () {
		if ( ! current_user_can( 'promote_users' ) || ! check_admin_referer( 'av_save_role' ) ) {
			wp_die( 'Không có quyền.' );
		}
		$slug    = sanitize_key( wp_unslash( $_POST['role'] ?? '' ) );
		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$chosen  = array_map( 'absint', (array) wp_unslash( $_POST['actions'] ?? array() ) );
		$back    = admin_url( 'users.php?page=av-roles' );
		if ( '' === $name ) {
			wp_safe_redirect( add_query_arg( array( 'role' => $slug, 'msg' => 'name' ), $back ) );
			exit;
		}
		if ( 'new' === $slug ) {
			$slug = AV_ROLE_PREFIX . substr( sanitize_key( str_replace( '-', '_', sanitize_title( $name ) ) ), 0, 40 );
			if ( get_role( $slug ) ) {
				wp_safe_redirect( add_query_arg( array( 'role' => 'new', 'msg' => 'exists' ), $back ) );
				exit;
			}
			add_role( $slug, $name, array( 'read' => true ) );
			$verb = 'Tạo';
		} elseif ( ! av_is_custom_role( $slug ) || ! get_role( $slug ) ) {
			wp_die( 'Không sửa được nhóm này.' );
		} else {
			// Đổi tên: WordPress lưu tên trong option user_roles.
			$roles = get_option( wp_roles()->role_key );
			$roles[ $slug ]['name'] = $name;
			update_option( wp_roles()->role_key, $roles );
			$verb = 'Sửa';
		}
		$role   = get_role( $slug );
		$before = av_role_actions( $role );
		av_apply_role_actions( $role, $chosen );
		$after = av_role_actions( get_role( $slug ) );
		av_log(
			'{verb} nhóm quyền "{role_name}": {actions}',
			array(
				'verb'      => $verb,
				'role_name' => $name,
				'actions'   => av_role_actions_text( $after ),
				'before'    => av_role_actions_text( $before ),
			),
			'notice'
		);
		wp_safe_redirect( add_query_arg( 'msg', 'saved', $back ) );
		exit;
	}
);

add_action(
	'admin_post_av_delete_role',
	static function () {
		if ( ! current_user_can( 'promote_users' ) || ! check_admin_referer( 'av_delete_role' ) ) {
			wp_die( 'Không có quyền.' );
		}
		$slug = sanitize_key( wp_unslash( $_POST['role'] ?? '' ) );
		if ( ! av_is_custom_role( $slug ) || ! get_role( $slug ) ) {
			wp_die( 'Không xoá được nhóm này.' );
		}
		$name = translate_user_role( wp_roles()->roles[ $slug ]['name'] );
		foreach ( get_users( array( 'role' => $slug ) ) as $u ) {
			$u->remove_role( $slug );
			if ( ! $u->roles ) {
				$u->add_role( 'editor' );
			}
		}
		remove_role( $slug );
		av_log( 'Xoá nhóm quyền "{role_name}"', array( 'role_name' => $name ), 'warning' );
		wp_safe_redirect( admin_url( 'users.php?page=av-roles&msg=deleted' ) );
		exit;
	}
);

/* ------------------------------------------------------------------ */
/* Vai trò hiển thị khi thêm, sửa người dùng                          */
/* ------------------------------------------------------------------ */

// Chỉ cho chọn Quản trị viên, Biên tập viên và nhóm tự tạo; bỏ Tác giả, Cộng tác viên, Thành viên.
add_filter(
	'editable_roles',
	static function ( array $roles ): array {
		foreach ( array_keys( $roles ) as $slug ) {
			if ( ! in_array( $slug, array( 'administrator', 'editor' ), true ) && ! av_is_custom_role( $slug ) ) {
				unset( $roles[ $slug ] );
			}
		}
		return $roles;
	}
);

// Người dùng mới mặc định là Biên tập viên.
add_filter( 'pre_option_default_role', static fn() => 'editor' );

/**
 * Nhóm quyền bổ sung trên trang sửa người dùng (một người thuộc nhiều nhóm).
 */
function av_extra_roles_field( WP_User $user ): void {
	if ( ! current_user_can( 'promote_users' ) ) {
		return;
	}
	$custom = array_filter( wp_roles()->roles, static fn( $r, $slug ) => av_is_custom_role( $slug ), ARRAY_FILTER_USE_BOTH );
	echo '<h2>Nhóm quyền bổ sung</h2><table class="form-table" role="presentation"><tr><th>Thuộc thêm nhóm</th><td>';
	wp_nonce_field( 'av_extra_roles', 'av_extra_roles_nonce' );
	if ( ! $custom ) {
		printf( '<p class="description">Chưa có nhóm tự tạo. <a href="%s">Tạo nhóm quyền</a>.</p>', esc_url( admin_url( 'users.php?page=av-roles' ) ) );
	}
	foreach ( $custom as $slug => $r ) {
		printf( '<label style="display:block;margin:4px 0"><input type="checkbox" name="av_extra_roles[]" value="%s"%s> %s</label>', esc_attr( $slug ), checked( in_array( $slug, (array) $user->roles, true ), true, false ), esc_html( translate_user_role( $r['name'] ) ) );
	}
	echo '<p class="description">Ngoài vai trò chính ở trên, người này có thêm quyền của các nhóm được chọn.</p></td></tr></table>';
}
add_action( 'edit_user_profile', 'av_extra_roles_field' );

add_action(
	'edit_user_profile_update',
	static function ( int $user_id ) {
		if ( ! current_user_can( 'promote_users' ) || ! isset( $_POST['av_extra_roles_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_extra_roles_nonce'] ), 'av_extra_roles' ) ) {
			return;
		}
		$wanted = array_filter( array_map( 'sanitize_key', (array) wp_unslash( $_POST['av_extra_roles'] ?? array() ) ), 'av_is_custom_role' );
		$main   = sanitize_key( wp_unslash( $_POST['role'] ?? '' ) );
		// Chạy sau khi WordPress đặt vai trò chính, để không bị ghi đè.
		add_action(
			'profile_update',
			static function ( $id ) use ( $user_id, $wanted, $main ) {
				if ( $id !== $user_id ) {
					return;
				}
				$u      = get_userdata( $user_id );
				$before = $u->roles;
				foreach ( $u->roles as $r ) {
					if ( av_is_custom_role( $r ) && ! in_array( $r, $wanted, true ) && $r !== $main ) {
						$u->remove_role( $r );
					}
				}
				foreach ( $wanted as $r ) {
					if ( get_role( $r ) && ! in_array( $r, $u->roles, true ) ) {
						$u->add_role( $r );
					}
				}
				$after = get_userdata( $user_id )->roles;
				if ( array_diff( $before, $after ) || array_diff( $after, $before ) ) {
					av_log( 'Đổi nhóm quyền của {target}: {after}', array( 'target' => $u->user_login, 'after' => implode( ', ', array_map( 'av_role_label', $after ) ), 'before' => implode( ', ', array_map( 'av_role_label', $before ) ) ), 'notice' );
				}
			},
			20
		);
	}
);

function av_role_label( string $slug ): string {
	$roles = wp_roles()->roles;
	return isset( $roles[ $slug ] ) ? translate_user_role( $roles[ $slug ]['name'] ) : $slug;
}

// Tên vai trò theo tài liệu: bản dịch tiếng Việt của WordPress gọi Administrator là "Quản lý".
add_filter(
	'gettext_with_context',
	static function ( $translation, $text, $context ) {
		if ( 'User role' === $context && 'Administrator' === $text ) {
			return 'Quản trị viên';
		}
		return $translation;
	},
	10,
	3
);

/**
 * Danh sách quyền đọc được, ví dụ "Yêu cầu báo giá: Xem, đổi trạng thái, ghi chú; Xuất Excel".
 */
function av_role_actions_text( array $on ): string {
	$parts = array();
	foreach ( av_permission_matrix() as $code => $module ) {
		$acts = array();
		foreach ( $module[1] as $key => $action ) {
			if ( ! empty( $on[ $code . '.' . $key ] ) ) {
				$acts[] = $action[0];
			}
		}
		if ( $acts ) {
			$parts[] = $module[0] . ': ' . implode( '; ', $acts );
		}
	}
	return $parts ? implode( ' | ', $parts ) : 'không có quyền nào';
}

