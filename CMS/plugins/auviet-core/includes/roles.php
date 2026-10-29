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
 * Các cột hành động trong ma trận quyền.
 */
function av_permission_columns(): array {
	return array(
		'xem'     => 'Xem',
		'them'    => 'Thêm',
		'sua'     => 'Sửa',
		'xuatban' => 'Xuất bản',
		'xoa'     => 'Xoá',
		'khac'    => 'Khác',
	);
}

/**
 * Bộ quyền cho từng hành động của một loại nội dung (capability_type số nhiều, ví dụ "av_products", "posts").
 * Xem: danh sách trong trang quản trị và bản nháp, bản riêng tư. Thêm: tạo mới (create_*, cần Xem).
 * Sửa: sửa nội dung người khác và nội dung đã xuất bản. Xuất bản: đăng, hẹn giờ đăng. Xoá: bỏ vào thùng rác, xoá hẳn.
 */
function av_content_actions( string $plural ): array {
	return array(
		'xem'     => array( 'Xem', array( "edit_{$plural}", "read_private_{$plural}" ) ),
		'them'    => array( 'Thêm', array( "create_{$plural}" ) ),
		'sua'     => array( 'Sửa', array( "edit_others_{$plural}", "edit_published_{$plural}", "edit_private_{$plural}" ) ),
		'xuatban' => array( 'Xuất bản', array( "publish_{$plural}" ) ),
		'xoa'     => array( 'Xoá', array( "delete_{$plural}", "delete_others_{$plural}", "delete_published_{$plural}", "delete_private_{$plural}" ) ),
	);
}

/**
 * Ma trận quyền: mã module => [tên module, [mục => [tên mục, [hành động => [nhãn, capability[]]], ghi chú]]].
 * Mỗi ô là một quyền độc lập. QT-11 (người dùng, plugin, cập nhật) chỉ dành cho Quản trị viên nên không có ở đây.
 */
function av_permission_matrix(): array {
	return array(
		'QT-02' => array( 'Trang và landing', array( 'pages' => array( 'Trang', av_content_actions( 'pages' ) ) ) ),
		'QT-03' => array(
			'Sản phẩm, chất liệu',
			array(
				'products'  => array( 'Sản phẩm', av_content_actions( 'av_products' ), 'Nhóm sản phẩm: sửa theo quyền Sửa, xoá theo quyền Xoá sản phẩm.' ),
				'materials' => array( 'Chất liệu', av_content_actions( 'av_materials' ) ),
			),
		),
		'QT-04' => array(
			'Bảng giá, tính giá',
			array(
				'prices' => array(
					'Bảng giá tham khảo',
					array(
						'xem' => array( 'Xem', array( 'av_view_prices' ) ),
						'sua' => array( 'Sửa', array( 'av_manage_prices' ) ),
					),
					'Sửa giá cần có thêm quyền Sửa sản phẩm.',
				),
			),
		),
		'QT-05' => array(
			'Bài viết, chủ đề, hỏi đáp',
			array(
				'posts'      => array( 'Bài viết', av_content_actions( 'posts' ) ),
				'post_terms' => array(
					'Danh mục, chủ đề bài viết',
					array(
						'sua' => array( 'Thêm, sửa', array( 'av_edit_post_terms' ) ),
						'xoa' => array( 'Xoá', array( 'av_delete_post_terms' ) ),
					),
				),
				'faqs'       => array( 'Hỏi đáp', av_content_actions( 'av_faqs' ) ),
			),
		),
		'QT-06' => array( 'Mẫu đã làm', array( 'projects' => array( 'Mẫu đã làm', av_content_actions( 'av_projects' ), 'Ngành: sửa theo quyền Sửa, xoá theo quyền Xoá mẫu.' ) ) ),
		'QT-07' => array(
			'Yêu cầu báo giá',
			array(
				'quotes' => array(
					'Yêu cầu báo giá',
					array(
						'xem'  => array( 'Xem', array( 'edit_av_quotes', 'read_private_av_quotes' ) ),
						'sua'  => array( 'Xử lý', array( 'edit_others_av_quotes', 'edit_published_av_quotes', 'edit_private_av_quotes', 'publish_av_quotes' ) ),
						'xoa'  => array( 'Xoá', array( 'delete_av_quotes', 'delete_others_av_quotes', 'delete_published_av_quotes', 'delete_private_av_quotes' ) ),
						'khac' => array( 'Xuất Excel', array( 'av_export_quotes' ) ),
					),
					'Xử lý: mở yêu cầu, đổi trạng thái, người phụ trách, ghi chú.',
				),
			),
		),
		'QT-08' => array(
			'SEO và chuyển hướng',
			array(
				'seo_meta'  => array( 'Title, meta, ảnh chia sẻ', array( 'sua' => array( 'Sửa', array( 'rank_math_onpage_general', 'rank_math_onpage_advanced', 'rank_math_onpage_snippet', 'rank_math_onpage_social' ) ) ) ),
				'redirects' => array( 'Chuyển hướng 301, lỗi 404', array( 'sua' => array( 'Sửa', array( 'av_manage_seo', 'rank_math_redirections', 'rank_math_404_monitor' ) ) ) ),
			),
		),
		'QT-09' => array( 'Thư viện media', array( 'media' => array( 'Ảnh, file', array( 'them' => array( 'Tải lên', array( 'upload_files' ) ) ), 'Sửa, xoá ảnh của người khác đi theo quyền Sửa, Xoá bài viết.' ) ) ),
		'QT-10' => array(
			'Cấu hình chung',
			array(
				'site_content' => array( 'Nội dung: banner, giới thiệu, thiết kế', array( 'sua' => array( 'Sửa', array( 'av_manage_site_content' ) ) ) ),
				'site_system'  => array( 'Hệ thống: công ty, liên hệ, email', array( 'sua' => array( 'Sửa', array( 'av_manage_site_system' ) ) ) ),
			),
		),
		'QT-12' => array( 'Nhật ký hoạt động', array( 'log' => array( 'Nhật ký', array( 'xem' => array( 'Xem, lọc, xuất', array( 'av_view_activity_log' ) ) ) ) ) ),
	);
}

/**
 * Danh sách phẳng: "mục.hành động" => [tên mục, nhãn hành động, capability[]].
 */
function av_permission_flat(): array {
	$out = array();
	foreach ( av_permission_matrix() as $module ) {
		foreach ( $module[1] as $res => $r ) {
			foreach ( $r[1] as $act => $a ) {
				$out[ $res . '.' . $act ] = array( $r[0], $a[0], $a[1] );
			}
		}
	}
	return $out;
}

/**
 * Các quyền một vai trò đang có: [ 'products.xem' => true, ... ]. Một ô được tính là có khi vai trò có đủ mọi capability của ô đó.
 */
function av_role_actions( WP_Role $role ): array {
	$have = array_keys( array_filter( $role->capabilities ) );
	$on   = array();
	foreach ( av_permission_flat() as $key => $a ) {
		$on[ $key ] = ! array_diff( $a[2], $have );
	}
	return $on;
}

function av_is_custom_role( string $slug ): bool {
	return str_starts_with( $slug, AV_ROLE_PREFIX );
}

/**
 * Gán đúng tập quyền đã chọn cho nhóm tự tạo (luôn có quyền "read" để vào trang quản trị).
 * Mục nào có Thêm, Sửa, Xuất bản hoặc Xoá thì tự có Xem.
 */
function av_apply_role_actions( WP_Role $role, array $chosen ): void {
	$flat = av_permission_flat();
	foreach ( array_keys( array_filter( $chosen ) ) as $key ) {
		[ $res ] = explode( '.', (string) $key );
		if ( isset( $flat[ $res . '.xem' ] ) ) {
			$chosen[ $res . '.xem' ] = 1;
		}
	}
	$want = array( 'read' => true );
	foreach ( $flat as $key => $a ) {
		if ( ! empty( $chosen[ $key ] ) ) {
			foreach ( $a[2] as $cap ) {
				$want[ $cap ] = true;
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
		$labels = av_role_actions_lines( av_role_actions( get_role( $slug ) ) );
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

/**
 * Mỗi mục một dòng: "<strong>Sản phẩm</strong>: xem, thêm, sửa".
 *
 * @return string[]
 */
function av_role_actions_lines( array $on ): array {
	$lines = array();
	foreach ( av_permission_matrix() as $module ) {
		foreach ( $module[1] as $res => $r ) {
			$acts = array();
			foreach ( $r[1] as $act => $a ) {
				if ( ! empty( $on[ $res . '.' . $act ] ) ) {
					$acts[] = mb_strtolower( $a[0] );
				}
			}
			if ( $acts ) {
				$lines[] = '<strong>' . esc_html( $r[0] ) . '</strong>: ' . esc_html( implode( ', ', $acts ) );
			}
		}
	}
	return $lines;
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
		<p class="description">Mỗi ô là một quyền riêng. Chọn Thêm, Sửa, Xuất bản hoặc Xoá thì Xem tự được chọn. Ô "—" là hành động không có ở mục đó.</p>
		<table class="widefat av-matrix">
			<thead>
				<tr>
					<th scope="col">Mã</th>
					<th scope="col">Mục</th>
					<?php foreach ( av_permission_columns() as $col => $label ) : ?>
						<th scope="col" class="av-matrix__act">
							<?php echo esc_html( $label ); ?>
							<?php if ( 'khac' !== $col ) : ?>
								<br><label class="av-matrix__all"><input type="checkbox" data-av-col="<?php echo esc_attr( $col ); ?>"> <span>cả cột</span></label>
							<?php endif; ?>
						</th>
					<?php endforeach; ?>
					<th scope="col" class="av-matrix__act">Cả dòng</th>
				</tr>
			</thead>
			<tbody>
				<tr class="av-matrix__fixed">
					<td>QT-01</td><td>Bảng điều khiển</td>
					<td class="av-matrix__act"><input type="checkbox" checked disabled aria-label="Xem bảng điều khiển (luôn có)"></td>
					<td colspan="6"><span class="description">Luôn có</span></td>
				</tr>
				<?php foreach ( av_permission_matrix() as $code => $module ) : ?>
					<?php $first = true; foreach ( $module[1] as $res => $r ) : ?>
						<tr data-av-row="<?php echo esc_attr( $res ); ?>" class="<?php echo $first ? 'av-matrix__module' : ''; ?>">
							<td><?php echo $first ? esc_html( $code ) : ''; ?></td>
							<td>
								<?php if ( $first && $module[0] !== $r[0] ) : ?><span class="av-matrix__mod"><?php echo esc_html( $module[0] ); ?></span><br><?php endif; ?>
								<strong><?php echo esc_html( $r[0] ); ?></strong>
								<?php if ( ! empty( $r[2] ) ) : ?><br><span class="description"><?php echo esc_html( $r[2] ); ?></span><?php endif; ?>
							</td>
							<?php foreach ( av_permission_columns() as $col => $label ) : ?>
								<td class="av-matrix__act">
									<?php if ( isset( $r[1][ $col ] ) ) : ?>
										<?php $k = $res . '.' . $col; ?>
										<label>
											<input type="checkbox" name="actions[<?php echo esc_attr( $k ); ?>]" value="1" data-av-act="<?php echo esc_attr( $col ); ?>"<?php checked( ! empty( $actions[ $k ] ) ); ?> aria-label="<?php echo esc_attr( $r[1][ $col ][0] . ' – ' . $r[0] ); ?>">
											<?php if ( $r[1][ $col ][0] !== $label ) : ?><span class="av-matrix__lbl"><?php echo esc_html( $r[1][ $col ][0] ); ?></span><?php endif; ?>
										</label>
									<?php else : ?>
										<span class="av-matrix__na" aria-hidden="true">—</span>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
							<td class="av-matrix__act"><input type="checkbox" data-av-rowall aria-label="<?php echo esc_attr( 'Chọn mọi quyền – ' . $r[0] ); ?>"></td>
						</tr>
					<?php $first = false; endforeach; ?>
				<?php endforeach; ?>
				<tr class="av-matrix__fixed av-matrix__module">
					<td>QT-11</td><td>Người dùng, plugin, cập nhật, sao lưu</td>
					<td colspan="7"><span class="description">Chỉ Quản trị viên</span></td>
				</tr>
			</tbody>
		</table>
		<script>
		( function () {
			var table = document.querySelector( '.av-matrix' );
			if ( ! table ) { return; }
			function rowSync( tr ) {
				var boxes = tr.querySelectorAll( '[data-av-act]' );
				var xem = tr.querySelector( '[data-av-act="xem"]' );
				var other = Array.prototype.some.call( boxes, function ( b ) { return b !== xem && b.checked; } );
				if ( xem && other ) { xem.checked = true; }
				var all = tr.querySelector( '[data-av-rowall]' );
				if ( all ) { all.checked = boxes.length && Array.prototype.every.call( boxes, function ( b ) { return b.checked; } ); }
			}
			function colSync() {
				table.querySelectorAll( '[data-av-col]' ).forEach( function ( head ) {
					var boxes = table.querySelectorAll( '[data-av-act="' + head.dataset.avCol + '"]' );
					head.checked = boxes.length && Array.prototype.every.call( boxes, function ( b ) { return b.checked; } );
				} );
			}
			table.addEventListener( 'change', function ( e ) {
				var t = e.target;
				var tr = t.closest( 'tr' );
				if ( t.dataset.avAct === 'xem' && ! t.checked ) {
					// Bỏ Xem thì bỏ luôn các quyền khác của mục đó.
					tr.querySelectorAll( '[data-av-act]' ).forEach( function ( b ) { b.checked = false; } );
				} else if ( t.hasAttribute( 'data-av-rowall' ) ) {
					tr.querySelectorAll( '[data-av-act]' ).forEach( function ( b ) { b.checked = t.checked; } );
				} else if ( t.dataset.avCol ) {
					table.querySelectorAll( '[data-av-act="' + t.dataset.avCol + '"]' ).forEach( function ( b ) {
						b.checked = t.checked;
						if ( ! t.checked && t.dataset.avCol === 'xem' ) {
							b.closest( 'tr' ).querySelectorAll( '[data-av-act]' ).forEach( function ( x ) { x.checked = false; } );
						}
					} );
				}
				table.querySelectorAll( 'tr[data-av-row]' ).forEach( rowSync );
				colSync();
			} );
			table.querySelectorAll( 'tr[data-av-row]' ).forEach( rowSync );
			colSync();
		} )();
		</script>
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
 * Danh sách quyền đọc được, ví dụ "Yêu cầu báo giá: xem, xử lý, xuất excel | Sản phẩm: xem".
 */
function av_role_actions_text( array $on ): string {
	$lines = array_map( static fn( $l ) => wp_strip_all_tags( $l ), av_role_actions_lines( $on ) );
	return $lines ? implode( ' | ', $lines ) : 'không có quyền nào';
}
