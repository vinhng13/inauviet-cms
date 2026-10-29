<?php
/**
 * LP-06 Trang giới thiệu /gioi-thieu/: năm thành lập, số liệu, xưởng và máy móc, đội ngũ, khách hàng tiêu biểu.
 *
 * Quản trị (Biên tập viên và Quản trị viên):
 * - Âu Việt › Giới thiệu: năm thành lập, 4 số liệu nổi bật.
 * - Âu Việt › Máy móc, thiết bị: mỗi máy một mục (ảnh, loại, thông số, mô tả).
 * - Âu Việt › Khách hàng tiêu biểu: logo, ngành, website; chỉ hiện khi đánh dấu "được phép nêu tên".
 * - Chữ và thứ tự các khối của trang: Trang › Giới thiệu.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Loại nội dung: máy móc, khách hàng                                  */
/* ------------------------------------------------------------------ */

add_action(
	'init',
	static function () {
		$common = array(
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'auviet',
			'show_in_rest'    => true,
			'capability_type' => 'page',
			'map_meta_cap'    => true,
		);
		register_post_type(
			'av_machine',
			$common + array(
				'labels'   => array( 'all_items' => 'Máy móc, thiết bị' ) + av_cpt_labels( 'Máy móc, thiết bị', 'Máy' ),
				'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			)
		);
		register_post_type(
			'av_client',
			$common + array(
				'labels'   => array( 'all_items' => 'Khách hàng tiêu biểu' ) + av_cpt_labels( 'Khách hàng tiêu biểu', 'Khách hàng' ),
				'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		);
	}
);

function av_machine_kinds(): array {
	return array(
		'offset'   => 'In offset',
		'digital'  => 'In kỹ thuật số',
		'kho-lon'  => 'In khổ lớn',
		'gia-cong' => 'Gia công thành phẩm',
		'khac'     => 'Khác',
	);
}

// Bỏ trình soạn khối cho hai loại này: form ngắn, dễ nhập.
add_filter( 'use_block_editor_for_post_type', static fn( $use, $type ) => in_array( $type, array( 'av_machine', 'av_client' ), true ) ? false : $use, 10, 2 );

add_filter(
	'enter_title_here',
	static function ( $text, $post ) {
		if ( 'av_machine' === $post->post_type ) {
			return 'Tên máy, ví dụ: Máy in kỹ thuật số Konica Minolta';
		}
		if ( 'av_client' === $post->post_type ) {
			return 'Tên khách hàng (doanh nghiệp, trường, cơ quan)';
		}
		return $text;
	},
	10,
	2
);

add_action(
	'add_meta_boxes',
	static function () {
		add_meta_box(
			'av_machine_info',
			'Thông tin máy',
			static function ( WP_Post $post ) {
				wp_nonce_field( 'av_about_meta', 'av_about_nonce' );
				$kind = (string) get_post_meta( $post->ID, '_av_kind', true );
				echo '<p><label for="av-kind"><strong>Loại</strong></label><br><select id="av-kind" name="av_kind">';
				foreach ( av_machine_kinds() as $k => $label ) {
					printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $kind, $k, false ), esc_html( $label ) );
				}
				echo '</select></p>';
				printf( '<p><label for="av-spec"><strong>Thông số ngắn</strong></label><br><input type="text" class="large-text" id="av-spec" name="av_spec" value="%s" placeholder="Ví dụ: khổ in tối đa 33 × 48 cm, 60 trang/phút"></p>', esc_attr( (string) get_post_meta( $post->ID, '_av_spec', true ) ) );
				echo '<p class="description">Mô tả máy dùng để in gì, ưu điểm (ô nội dung phía trên). Ảnh thật của máy đặt ở Ảnh đại diện. Ô "Thứ tự" nhỏ hơn thì hiện trước.</p>';
			},
			'av_machine',
			'normal',
			'high'
		);
		add_meta_box(
			'av_client_info',
			'Thông tin khách hàng',
			static function ( WP_Post $post ) {
				wp_nonce_field( 'av_about_meta', 'av_about_nonce' );
				printf( '<p><label for="av-industry"><strong>Ngành</strong></label><br><input type="text" class="regular-text" id="av-industry" name="av_industry_text" value="%s" placeholder="Ví dụ: Bất động sản, Giáo dục"></p>', esc_attr( (string) get_post_meta( $post->ID, '_av_industry', true ) ) );
				printf( '<p><label for="av-site"><strong>Website</strong> (tuỳ chọn)</label><br><input type="url" class="regular-text" id="av-site" name="av_site" value="%s"></p>', esc_attr( (string) get_post_meta( $post->ID, '_av_site', true ) ) );
				printf( '<p><label><input type="checkbox" name="av_consent" value="1"%s> <strong>Khách hàng đã đồng ý cho nêu tên và logo</strong></label><br><span class="description">Chưa đánh dấu thì khách hàng này không hiện ở bất kỳ đâu trên website.</span></p>', checked( (bool) get_post_meta( $post->ID, '_av_consent', true ), true, false ) );
				printf( '<p><label><input type="checkbox" name="av_home" value="1"%s> Hiện ở trang chủ (mục "Khách hàng đã tin chọn")</label></p>', checked( (bool) get_post_meta( $post->ID, '_av_home', true ), true, false ) );
				echo '<p class="description">Logo đặt ở Ảnh đại diện, nên dùng ảnh nền trong suốt (PNG, SVG chuyển sang PNG). Ô "Thứ tự" nhỏ hơn thì hiện trước.</p>';
			},
			'av_client',
			'normal',
			'high'
		);
	}
);

add_action(
	'save_post',
	static function ( int $post_id, WP_Post $post ) {
		if ( ! in_array( $post->post_type, array( 'av_machine', 'av_client' ), true ) || ! isset( $_POST['av_about_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_about_nonce'] ), 'av_about_meta' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( 'av_machine' === $post->post_type ) {
			$kind = sanitize_text_field( wp_unslash( $_POST['av_kind'] ?? 'khac' ) );
			update_post_meta( $post_id, '_av_kind', isset( av_machine_kinds()[ $kind ] ) ? $kind : 'khac' );
			update_post_meta( $post_id, '_av_spec', sanitize_text_field( wp_unslash( $_POST['av_spec'] ?? '' ) ) );
		} else {
			update_post_meta( $post_id, '_av_industry', sanitize_text_field( wp_unslash( $_POST['av_industry_text'] ?? '' ) ) );
			update_post_meta( $post_id, '_av_site', esc_url_raw( wp_unslash( $_POST['av_site'] ?? '' ) ) );
			update_post_meta( $post_id, '_av_consent', empty( $_POST['av_consent'] ) ? 0 : 1 );
			update_post_meta( $post_id, '_av_home', empty( $_POST['av_home'] ) ? 0 : 1 );
		}
		// phpcs:enable
	},
	10,
	2
);

// Cột danh sách.
add_filter(
	'manage_av_client_posts_columns',
	static fn( $cols ) => array(
		'cb'         => $cols['cb'],
		'av_logo'    => 'Logo',
		'title'      => 'Khách hàng',
		'av_consent' => 'Được nêu tên',
		'av_home'    => 'Trang chủ',
		'menu_order' => 'Thứ tự',
	)
);
add_filter(
	'manage_av_machine_posts_columns',
	static fn( $cols ) => array(
		'cb'         => $cols['cb'],
		'av_logo'    => 'Ảnh',
		'title'      => 'Máy',
		'av_kind'    => 'Loại',
		'menu_order' => 'Thứ tự',
	)
);
$av_about_col = static function ( string $col, int $id ) {
	switch ( $col ) {
		case 'av_logo':
			echo get_the_post_thumbnail( $id, array( 64, 64 ), array( 'style' => 'width:64px;height:auto;max-height:48px;object-fit:contain' ) ) ?: '<span class="av-warn">Chưa có ảnh</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			break;
		case 'av_consent':
			echo get_post_meta( $id, '_av_consent', true ) ? '<span class="av-ok">Đã đồng ý</span>' : '<span class="av-warn">Chưa (không hiện)</span>';
			break;
		case 'av_home':
			echo get_post_meta( $id, '_av_home', true ) ? 'Có' : '—';
			break;
		case 'av_kind':
			echo esc_html( av_machine_kinds()[ (string) get_post_meta( $id, '_av_kind', true ) ] ?? '—' );
			break;
		case 'menu_order':
			echo (int) get_post_field( 'menu_order', $id );
			break;
	}
};
add_action( 'manage_av_client_posts_custom_column', $av_about_col, 10, 2 );
add_action( 'manage_av_machine_posts_custom_column', $av_about_col, 10, 2 );

// Danh sách trong quản trị sắp theo thứ tự.
add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() && $q->is_main_query() && in_array( $q->get( 'post_type' ), array( 'av_machine', 'av_client' ), true ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		}
	}
);

/* ------------------------------------------------------------------ */
/* Âu Việt › Giới thiệu: năm thành lập, số liệu                        */
/* ------------------------------------------------------------------ */

function av_about_defaults(): array {
	return array(
		'founded' => '',
		'stats'   => array(
			array( 'value' => '10+', 'label' => 'năm khẳng định tên tuổi trên thị trường in ấn' ),
			array( 'value' => 'Khép kín', 'label' => 'từ ý tưởng, thiết kế đến in và hoàn thiện' ),
			array( 'value' => 'Offset, kỹ thuật số', 'label' => 'màu sắc trung thực, sắc nét' ),
			array( 'value' => 'Nhà thầu', 'label' => 'in ấn, thiết kế cho cơ quan nhà nước và đại học' ),
		),
	);
}

function av_about(): array {
	return wp_parse_args( (array) get_option( 'av_about', array() ), av_about_defaults() );
}

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'auviet', 'Giới thiệu', 'Giới thiệu', 'av_manage_site_content', 'auviet-about', 'av_render_about_page' );
	},
	15
);

add_action(
	'admin_init',
	static function () {
		register_setting(
			'av_about_group',
			'av_about',
			array(
				'sanitize_callback' => static function ( $in ) {
					$in    = (array) $in;
					$year  = absint( $in['founded'] ?? 0 );
					$stats = array();
					foreach ( (array) ( $in['stats'] ?? array() ) as $s ) {
						$row = array(
							'value' => sanitize_text_field( $s['value'] ?? '' ),
							'label' => sanitize_text_field( $s['label'] ?? '' ),
						);
						if ( $row['value'] || $row['label'] ) {
							$stats[] = $row;
						}
					}
					av_log( 'Sửa số liệu trang giới thiệu' );
					return array(
						'founded' => ( $year >= 1950 && $year <= (int) wp_date( 'Y' ) ) ? $year : '',
						'stats'   => $stats,
					);
				},
			)
		);
	}
);
add_filter( 'option_page_capability_av_about_group', static fn() => 'av_manage_site_content' );

function av_render_about_page(): void {
	if ( ! current_user_can( 'av_manage_site_content' ) ) {
		return;
	}
	$a     = av_about();
	$stats = array_values( $a['stats'] );
	$page  = (int) get_option( 'av_about_page' );
	?>
	<div class="wrap">
		<h1>Trang giới thiệu</h1>
		<?php av_saved_notice(); ?>
		<p>
			Trang <a href="<?php echo esc_url( home_url( '/gioi-thieu/' ) ); ?>" target="_blank">/gioi-thieu/</a> gồm:
			chữ và thứ tự các khối (<?php echo $page ? '<a href="' . esc_url( get_edit_post_link( $page ) ) . '">sửa trang Giới thiệu</a>' : 'Trang › Giới thiệu'; ?>),
			số liệu bên dưới,
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=av_machine' ) ); ?>">máy móc, thiết bị</a>,
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=av_client' ) ); ?>">khách hàng tiêu biểu</a>
			và đội tư vấn (Âu Việt › Liên hệ &amp; công ty).
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'av_about_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="av-founded">Năm thành lập</label></th>
					<td>
						<input type="number" id="av-founded" name="av_about[founded]" min="1950" max="<?php echo esc_attr( wp_date( 'Y' ) ); ?>" value="<?php echo esc_attr( (string) $a['founded'] ); ?>" style="width:100px">
						<p class="description">Có năm thành lập thì số liệu đầu tiên tự tính "X năm" và tự tăng mỗi năm. Để trống thì dùng số liệu nhập tay bên dưới.</p>
					</td>
				</tr>
			</table>
			<h2>Số liệu nổi bật</h2>
			<table class="widefat striped" style="max-width:760px">
				<thead><tr><th style="width:200px">Số, chữ lớn</th><th>Giải thích</th></tr></thead>
				<tbody>
				<?php for ( $i = 0; $i < 4; $i++ ) : $s = $stats[ $i ] ?? array( 'value' => '', 'label' => '' ); ?>
					<tr>
						<td><input type="text" aria-label="Số liệu <?php echo (int) $i + 1; ?>" name="av_about[stats][<?php echo (int) $i; ?>][value]" value="<?php echo esc_attr( $s['value'] ); ?>" style="width:100%"></td>
						<td><input type="text" aria-label="Giải thích <?php echo (int) $i + 1; ?>" name="av_about[stats][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $s['label'] ); ?>" style="width:100%"></td>
					</tr>
				<?php endfor; ?>
				</tbody>
			</table>
			<p class="description">Chỉ ghi số liệu có thật, kiểm chứng được (ví dụ số khách hàng, số đơn mỗi năm).</p>
			<?php submit_button( 'Lưu' ); ?>
		</form>
	</div>
	<?php
}

/* ------------------------------------------------------------------ */
/* Khối hiển thị                                                       */
/* ------------------------------------------------------------------ */

add_filter(
	'av_block_definitions',
	static function ( array $defs ): array {
		return $defs + array(
			'auviet/about-stats' => array( 'title' => 'Số liệu giới thiệu', 'icon' => 'chart-bar', 'render' => 'av_render_about_stats', 'description' => 'Năm thành lập, số liệu nổi bật (Âu Việt › Giới thiệu).' ),
			'auviet/machines'    => array( 'title' => 'Máy móc, thiết bị', 'icon' => 'admin-tools', 'render' => 'av_render_machines', 'description' => 'Danh sách máy (Âu Việt › Máy móc, thiết bị).' ),
			'auviet/clients'     => array( 'title' => 'Khách hàng tiêu biểu', 'icon' => 'groups', 'render' => 'av_render_clients', 'description' => 'Logo khách hàng đã đồng ý nêu tên.', 'attributes' => array( 'homepage' => array( 'type' => 'boolean', 'default' => false ) ) ),
		);
	}
);

function av_render_about_stats(): string {
	$a     = av_about();
	$stats = array_values( $a['stats'] );
	if ( $a['founded'] ) {
		$years    = (int) wp_date( 'Y' ) - (int) $a['founded'];
		$stats[0] = array(
			'value' => $years . ' năm',
			'label' => 'khẳng định tên tuổi trên thị trường in ấn, từ năm ' . $a['founded'],
		);
	}
	if ( ! $stats ) {
		return '';
	}
	$out = '<dl class="av-stats">';
	foreach ( $stats as $s ) {
		$out .= sprintf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html( $s['value'] ), esc_html( $s['label'] ) );
	}
	return $out . '</dl>';
}

function av_render_machines(): string {
	$items = get_posts(
		array(
			'post_type'   => 'av_machine',
			'numberposts' => -1,
			'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		)
	);
	if ( ! $items ) {
		return '';
	}
	$out = '<ul class="av-machines">';
	foreach ( $items as $m ) {
		$thumb = get_the_post_thumbnail( $m, 'medium_large', array( 'alt' => $m->post_title, 'loading' => 'lazy' ) );
		$out  .= sprintf(
			'<li class="av-machine">%1$s<div class="av-machine__body"><span class="av-machine__kind">%2$s</span><strong>%3$s</strong>%4$s%5$s</div></li>',
			$thumb ?: '<span class="av-ph av-machine__ph">[Ảnh máy thật]</span>',
			esc_html( av_machine_kinds()[ (string) get_post_meta( $m->ID, '_av_kind', true ) ] ?? '' ),
			esc_html( $m->post_title ),
			get_post_meta( $m->ID, '_av_spec', true ) ? '<span class="av-machine__spec">' . esc_html( (string) get_post_meta( $m->ID, '_av_spec', true ) ) . '</span>' : '',
			$m->post_content ? '<div class="av-machine__desc">' . wp_kses_post( wpautop( $m->post_content ) ) . '</div>' : ''
		);
	}
	return $out . '</ul>';
}

function av_render_clients( array $attrs = array() ): string {
	$meta = array( array( 'key' => '_av_consent', 'value' => '1' ) );
	if ( ! empty( $attrs['homepage'] ) ) {
		$meta[] = array( 'key' => '_av_home', 'value' => '1' );
	}
	$items = get_posts(
		array(
			'post_type'   => 'av_client',
			'numberposts' => ! empty( $attrs['homepage'] ) ? 6 : -1,
			'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'meta_query'  => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( ! $items ) {
		$hint = current_user_can( 'edit_pages' ) ? sprintf( ' <a href="%s">Thêm logo khách hàng</a> (chỉ bạn thấy dòng này).', esc_url( admin_url( 'post-new.php?post_type=av_client' ) ) ) : '';
		return '<p class="av-clients-empty">Doanh nghiệp vừa và nhỏ, cơ quan nhà nước và các trường đại học tại Hà Nội.' . $hint . '</p>';
	}
	$out = '<ul class="av-clients">';
	foreach ( $items as $c ) {
		$logo = get_the_post_thumbnail( $c, 'medium', array( 'alt' => $c->post_title, 'loading' => 'lazy' ) );
		$site = (string) get_post_meta( $c->ID, '_av_site', true );
		$in   = $logo ?: '<span class="av-client__name">' . esc_html( $c->post_title ) . '</span>';
		$out .= '<li class="av-client" title="' . esc_attr( $c->post_title ) . '">' . ( $site ? '<a href="' . esc_url( $site ) . '" target="_blank" rel="noopener nofollow">' . $in . '</a>' : $in ) . '</li>';
	}
	return $out . '</ul>';
}

/* ------------------------------------------------------------------ */
/* Dữ liệu ban đầu, đường dẫn cũ                                       */
/* ------------------------------------------------------------------ */

/**
 * Hai dây chuyền in nêu trên web cũ (offset, kỹ thuật số Konica C6085). Chỉ tạo khi chưa có máy nào.
 */
add_action(
	'init',
	static function () {
		if ( get_option( 'av_about_seeded' ) ) {
			return;
		}
		update_option( 'av_about_seeded', 1, false );
		if ( get_posts( array( 'post_type' => 'av_machine', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) ) ) {
			return;
		}
		$seed = array(
			array( 'Máy in offset', 'offset', '', 'In số lượng lớn với màu ổn định giữa các lô: catalogue, tờ rơi, hộp giấy, lịch.', 1 ),
			array( 'Máy in kỹ thuật số Konica Minolta C6085', 'digital', 'In laser màu', 'In nhanh số lượng ít, lấy ngay trong ngày: card visit, tờ rơi số lượng nhỏ, bản in mẫu để duyệt màu.', 2 ),
		);
		foreach ( $seed as $s ) {
			$id = wp_insert_post( array( 'post_type' => 'av_machine', 'post_status' => 'publish', 'post_title' => $s[0], 'post_content' => $s[3], 'menu_order' => $s[4] ) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_av_kind', $s[1] );
				update_post_meta( $id, '_av_spec', $s[2] );
			}
		}
	},
	40
);

// /about/gioi-thieu-34.html và URL giới thiệu cũ khác → /gioi-thieu/.
add_action(
	'template_redirect',
	static function () {
		if ( ! is_404() ) {
			return;
		}
		$path = rawurldecode( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( preg_match( '#^/about(/.*)?$#', $path ) ) {
			wp_safe_redirect( home_url( '/gioi-thieu/' ), 301, 'Au Viet legacy' );
			exit;
		}
	},
	1
);

// Một lần: thay 6 ô logo chờ trên trang chủ bằng khối khách hàng tiêu biểu.
add_action(
	'init',
	static function () {
		if ( get_option( 'av_mig_home_clients' ) ) {
			return;
		}
		update_option( 'av_mig_home_clients', 1, false );
		$home = (int) get_option( 'page_on_front' );
		if ( ! $home ) {
			return;
		}
		$content = (string) get_post_field( 'post_content', $home );
		$new     = preg_replace( '#<!-- wp:group \{"className":"av-logos".*?<!-- /wp:group -->#s', '<!-- wp:auviet/clients {"homepage":true} /-->', $content, 1 );
		if ( $new && $new !== $content ) {
			kses_remove_filters();
			wp_update_post( array( 'ID' => $home, 'post_content' => $new ) );
			kses_init();
		}
	},
	50
);

// Một lần: trang Giới thiệu chưa ai sửa thì dựng lại theo mẫu mới (bám nội dung giới thiệu trên web cũ).
add_action(
	'wp_loaded',
	static function () {
		if ( get_option( 'av_mig_about_v3' ) || ! function_exists( 'auviet_page_from_pattern' ) ) {
			return;
		}
		update_option( 'av_mig_about_v3', 1, false );
		$id = (int) get_option( 'av_about_page' );
		$p  = $id ? get_post( $id ) : null;
		if ( ! $p || $p->post_modified_gmt !== $p->post_date_gmt ) {
			return; // Đã có người sửa: giữ nguyên.
		}
		$content = auviet_page_from_pattern( 'about-page' );
		if ( $content ) {
			kses_remove_filters();
			wp_update_post( array( 'ID' => $id, 'post_content' => $content ) );
			kses_init();
			// Giữ trạng thái "chưa sửa" để lần nâng cấp sau vẫn nhận ra.
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_modified' => $p->post_date, 'post_modified_gmt' => $p->post_date_gmt ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( $id );
		}
	},
	20
);

