<?php
/**
 * LP-05 Mẫu đã làm /mau-da-lam/ và QT-06 quản trị dự án.
 *
 * - Danh sách dạng lưới ảnh, lọc theo loại sản phẩm (nhóm cấp 1 của sản phẩm gắn với dự án) và ngành;
 *   bấm lọc, chuyển trang không tải lại cả trang (assets/js/projects.js), URL vẫn chia sẻ được.
 * - Chi tiết: ảnh, khách hàng (chỉ khi được phép nêu tên), ngành, quy cách, sản phẩm liên quan, form báo giá.
 * - Quản trị (Biên tập viên, Quản trị viên): Mẫu đã làm › Thêm mới. Không xuất bản được khi chưa gắn sản phẩm,
 *   hoặc khi khách không cho nêu tên mà tên khách nằm trong tiêu đề, mô tả, nội dung hay ảnh.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Dữ liệu                                                             */
/* ------------------------------------------------------------------ */

function av_project_spec_fields(): array {
	return array(
		'size'      => 'Kích thước, quy cách',
		'material'  => 'Chất liệu',
		'finishing' => 'Gia công',
		'qty'       => 'Số lượng',
		'time'      => 'Thời gian thực hiện',
		'year'      => 'Năm thực hiện',
	);
}

/**
 * Quy cách đã điền: [nhãn => giá trị].
 */
function av_project_specs( int $id ): array {
	$out = array();
	foreach ( av_project_spec_fields() as $k => $label ) {
		$v = trim( (string) get_post_meta( $id, '_av_pspec_' . $k, true ) );
		if ( '' !== $v ) {
			$out[ $label ] = $v;
		}
	}
	return $out;
}

/**
 * Sản phẩm gắn với dự án (đang hiển thị).
 *
 * @return WP_Post[]
 */
function av_project_products( int $id ): array {
	$ids = array_filter( array_map( 'intval', (array) get_post_meta( $id, '_av_products', true ) ) );
	if ( ! $ids ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'   => 'av_product',
			'post__in'    => $ids,
			'orderby'     => 'post__in',
			'numberposts' => -1,
		)
	);
}

/**
 * Tên khách hàng, chỉ trả về khi khách đã đồng ý nêu tên. Mọi chỗ hiển thị phía khách đều đi qua hàm này.
 */
function av_project_client( int $id ): string {
	if ( ! get_post_meta( $id, '_av_client_ok', true ) ) {
		return '';
	}
	return trim( (string) get_post_meta( $id, '_av_client_name', true ) );
}

/**
 * Loại sản phẩm của dự án = nhóm cấp 1 của các sản phẩm gắn kèm. Lưu sẵn vào meta _av_ptype để lọc nhanh.
 */
function av_project_sync_types( int $id ): void {
	delete_post_meta( $id, '_av_ptype' );
	$seen = array();
	foreach ( av_project_products( $id ) as $p ) {
		$g = av_product_primary_group( $p->ID );
		if ( ! $g ) {
			continue;
		}
		$root = av_group_root( $g );
		if ( ! isset( $seen[ $root->term_id ] ) ) {
			$seen[ $root->term_id ] = true;
			add_post_meta( $id, '_av_ptype', $root->term_id );
		}
	}
}

/**
 * @return WP_Term[]
 */
function av_project_types( int $id ): array {
	$out = array();
	foreach ( (array) get_post_meta( $id, '_av_ptype' ) as $tid ) {
		$t = get_term( (int) $tid, 'av_group' );
		if ( $t instanceof WP_Term ) {
			$out[] = $t;
		}
	}
	return $out;
}

/**
 * Ngành của dự án.
 *
 * @return WP_Term[]
 */
function av_project_industries( int $id ): array {
	$terms = get_the_terms( $id, 'av_industry' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
}

function av_projects_intro(): string {
	$v = (string) get_option( 'av_projects_intro', '' );
	return '' !== trim( $v ) ? $v : 'Ảnh thật từ các đơn hàng Âu Việt đã thiết kế, in và giao cho khách: bao bì, ấn phẩm marketing, quà tặng theo mùa, in khổ lớn cho sự kiện. Lọc theo loại sản phẩm hoặc ngành để xem mẫu gần với nhu cầu của Quý khách.';
}

/* ------------------------------------------------------------------ */
/* Ngành mặc định (chỉ tạo khi chưa có ngành nào)                      */
/* ------------------------------------------------------------------ */

add_action(
	'init',
	static function () {
		if ( get_option( 'av_seed_industries' ) ) {
			return;
		}
		update_option( 'av_seed_industries', 1, false );
		$existing = get_terms( array( 'taxonomy' => 'av_industry', 'hide_empty' => false, 'fields' => 'ids' ) );
		if ( is_wp_error( $existing ) || $existing ) {
			return;
		}
		$list = array(
			'thuc-pham'   => 'Thực phẩm, đồ uống',
			'my-pham'     => 'Mỹ phẩm, làm đẹp',
			'duoc-pham'   => 'Dược phẩm, y tế',
			'giao-duc'    => 'Giáo dục, trường học',
			'doanh-nghiep' => 'Doanh nghiệp, văn phòng',
			'nha-hang'    => 'Nhà hàng, khách sạn',
			'thoi-trang'  => 'Thời trang',
			'noi-that'    => 'Nội thất, bất động sản',
			'su-kien'     => 'Sự kiện, hội nghị',
			'tai-chinh'   => 'Ngân hàng, tài chính',
		);
		foreach ( $list as $slug => $name ) {
			wp_insert_term( $name, 'av_industry', array( 'slug' => $slug ) );
		}
	},
	40
);

/* ------------------------------------------------------------------ */
/* Quản trị: form nhập dự án                                           */
/* ------------------------------------------------------------------ */

// Form ngắn, dùng trình soạn cổ điển cho dễ nhập.
add_filter( 'use_block_editor_for_post_type', static fn( $use, $type ) => 'av_project' === $type ? false : $use, 10, 2 );

add_filter(
	'enter_title_here',
	static fn( $text, $post ) => 'av_project' === $post->post_type ? 'Tên dự án, ví dụ: Túi giấy kraft cho chuỗi cửa hàng bánh' : $text,
	10,
	2
);

add_action(
	'add_meta_boxes_av_project',
	static function () {
		add_meta_box( 'av_project_client', 'Khách hàng và sản phẩm', 'av_box_project_client', 'av_project', 'normal', 'high' );
		add_meta_box( 'av_project_specs', 'Quy cách', 'av_box_project_specs', 'av_project', 'normal', 'high' );
		add_meta_box( 'av_project_gallery', 'Ảnh dự án', 'av_box_product_gallery', 'av_project', 'normal', 'high' );
		remove_meta_box( 'postexcerpt', 'av_project', 'normal' );
		add_meta_box( 'postexcerpt', 'Mô tả ngắn (hiện dưới tên dự án)', 'post_excerpt_meta_box', 'av_project', 'normal', 'high' );
	}
);

function av_box_project_client( WP_Post $post ): void {
	wp_nonce_field( 'av_project_meta', 'av_project_nonce' );
	$chosen = array_map( 'intval', (array) get_post_meta( $post->ID, '_av_products', true ) );
	$ok     = (bool) get_post_meta( $post->ID, '_av_client_ok', true );
	if ( get_post_meta( $post->ID, '_av_sample', true ) ) {
		echo '<div class="av-admin-note av-admin-note--warn"><p><strong>Đây là dự án mẫu thử</strong> (chỉ có ở môi trường thử) để xem giao diện. Sửa thành dự án thật hoặc xoá đi.</p></div>';
	}
	?>
	<p class="av-admin-row">
		<label for="av-client-name"><strong>Khách hàng</strong></label><br>
		<input type="text" class="regular-text" id="av-client-name" name="av_client_name" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, '_av_client_name', true ) ); ?>" placeholder="Tên doanh nghiệp, trường, cơ quan">
	</p>
	<p class="av-admin-row">
		<label><input type="checkbox" name="av_client_ok" value="1" <?php checked( $ok ); ?>> <strong>Khách hàng đồng ý cho nêu tên</strong></label><br>
		<span class="description">Chưa đánh dấu thì tên khách chỉ lưu nội bộ, không hiện ở bất kỳ đâu trên website. Khi đó tên khách cũng không được nằm trong tiêu đề, mô tả, nội dung hay mô tả ảnh: website sẽ không cho xuất bản.</span>
	</p>
	<fieldset class="av-admin-row">
		<legend><strong>Sản phẩm của dự án</strong> (chọn ít nhất 1; dùng để lọc "Loại sản phẩm" và hiện "Sản phẩm liên quan")</legend>
		<div class="av-checklist">
			<?php
			foreach ( av_visible_groups() as $g ) {
				$products = get_posts(
					array(
						'post_type'   => 'av_product',
						'numberposts' => -1,
						'post_status' => array( 'publish', 'draft', 'pending' ),
						'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
						'tax_query'   => array( array( 'taxonomy' => 'av_group', 'terms' => $g->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
					)
				);
				if ( ! $products ) {
					continue;
				}
				printf( '<p class="av-checklist__group">%s</p>', esc_html( $g->name ) );
				foreach ( $products as $p ) {
					printf(
						'<label><input type="checkbox" name="av_products[]" value="%d"%s> %s</label>',
						(int) $p->ID,
						checked( in_array( $p->ID, $chosen, true ), true, false ),
						esc_html( $p->post_title )
					);
				}
			}
			?>
		</div>
	</fieldset>
	<?php
}

function av_box_project_specs( WP_Post $post ): void {
	echo '<p class="description">Điền mục nào hiện mục đó trên trang dự án. Ngành chọn ở khung "Ngành" bên phải.</p><table class="form-table" role="presentation">';
	foreach ( av_project_spec_fields() as $k => $label ) {
		printf(
			'<tr><th scope="row"><label for="av-pspec-%1$s">%2$s</label></th><td><input type="text" class="large-text" id="av-pspec-%1$s" name="av_pspec[%1$s]" value="%3$s"></td></tr>',
			esc_attr( $k ),
			esc_html( $label ),
			esc_attr( (string) get_post_meta( $post->ID, '_av_pspec_' . $k, true ) )
		);
	}
	echo '</table>';
}

/**
 * Lỗi chặn xuất bản dự án, kiểm tra trên dữ liệu đang gửi lên.
 */
function av_project_publish_error( array $data ): string {
	// phpcs:disable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- đã kiểm tra nonce ở hàm gọi.
	$products = array_filter( array_map( 'absint', (array) ( $_POST['av_products'] ?? array() ) ) );
	if ( ! $products ) {
		return 'no_product';
	}
	$name = trim( sanitize_text_field( wp_unslash( $_POST['av_client_name'] ?? '' ) ) );
	if ( '' === $name || ! empty( $_POST['av_client_ok'] ) || mb_strlen( $name ) < 2 ) {
		return '';
	}
	$texts = array( $data['post_title'], $data['post_excerpt'], wp_strip_all_tags( (string) $data['post_content'] ) );
	$imgs  = array_filter( array_map( 'absint', explode( ',', (string) wp_unslash( $_POST['av_gallery'] ?? '' ) ) ) );
	$imgs[] = absint( $_POST['_thumbnail_id'] ?? 0 );
	foreach ( array_filter( $imgs ) as $aid ) {
		$texts[] = (string) get_post_meta( $aid, '_wp_attachment_image_alt', true );
		$texts[] = (string) get_post_field( 'post_excerpt', $aid );
		$texts[] = (string) get_post_field( 'post_title', $aid );
	}
	// phpcs:enable
	foreach ( $texts as $t ) {
		if ( false !== mb_stripos( wp_unslash( (string) $t ), $name ) ) {
			return 'client_leak';
		}
	}
	return '';
}

add_filter(
	'wp_insert_post_data',
	static function ( array $data ) {
		if ( 'av_project' !== $data['post_type'] || ! in_array( $data['post_status'], array( 'publish', 'future' ), true ) ) {
			return $data;
		}
		if ( ! isset( $_POST['av_project_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_project_nonce'] ), 'av_project_meta' ) ) {
			return $data;
		}
		$err = av_project_publish_error( $data );
		// Đường dẫn tạo từ tiêu đề cũ có thể còn tên khách: tạo lại từ tiêu đề hiện tại.
		$name = sanitize_title( wp_unslash( (string) ( $_POST['av_client_name'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! $err && $name && empty( $_POST['av_client_ok'] ) && str_contains( (string) $data['post_name'], $name ) ) {
			$data['post_name'] = wp_unique_post_slug( sanitize_title( wp_unslash( $data['post_title'] ) ), (int) ( $_POST['post_ID'] ?? 0 ), $data['post_status'], 'av_project', 0 ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
		if ( $err ) {
			$data['post_status'] = 'draft';
			add_filter( 'redirect_post_location', static fn( $loc ) => add_query_arg( 'av_pj_err', $err, remove_query_arg( 'message', $loc ) ) );
		}
		return $data;
	},
	20
);

add_action(
	'save_post_av_project',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['av_project_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_project_nonce'] ), 'av_project_meta' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		update_post_meta( $post_id, '_av_client_name', sanitize_text_field( wp_unslash( $_POST['av_client_name'] ?? '' ) ) );
		update_post_meta( $post_id, '_av_client_ok', empty( $_POST['av_client_ok'] ) ? 0 : 1 );
		$products = array_values( array_unique( array_filter( array_map( 'absint', (array) ( $_POST['av_products'] ?? array() ) ) ) ) );
		update_post_meta( $post_id, '_av_products', array_values( array_filter( $products, static fn( $id ) => 'av_product' === get_post_type( $id ) ) ) );
		$specs = (array) wp_unslash( $_POST['av_pspec'] ?? array() );
		foreach ( array_keys( av_project_spec_fields() ) as $k ) {
			update_post_meta( $post_id, '_av_pspec_' . $k, sanitize_text_field( (string) ( $specs[ $k ] ?? '' ) ) );
		}
		$gallery = array_values( array_filter( array_map( 'absint', explode( ',', (string) wp_unslash( $_POST['av_gallery'] ?? '' ) ) ), 'wp_attachment_is_image' ) );
		update_post_meta( $post_id, '_av_gallery', $gallery );
		// phpcs:enable
		av_project_sync_types( $post_id );
	}
);

// Nhóm chính của sản phẩm đổi thì loại sản phẩm của các dự án gắn với nó cũng đổi.
add_action(
	'save_post_av_product',
	static function ( int $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		foreach ( get_posts( array( 'post_type' => 'av_project', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $pid ) {
			if ( in_array( $post_id, array_map( 'intval', (array) get_post_meta( $pid, '_av_products', true ) ), true ) ) {
				av_project_sync_types( $pid );
			}
		}
	},
	30
);

add_action(
	'admin_notices',
	static function () {
		$err = isset( $_GET['av_pj_err'] ) ? sanitize_key( wp_unslash( $_GET['av_pj_err'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$msg = array(
			'no_product'  => 'Chưa xuất bản: dự án cần gắn ít nhất một sản phẩm (khung "Khách hàng và sản phẩm"). Đã lưu bản nháp.',
			'client_leak' => 'Chưa xuất bản: khách hàng chưa đồng ý nêu tên nhưng tên khách đang nằm trong tiêu đề, mô tả ngắn, nội dung hoặc mô tả ảnh. Bỏ tên khách ở những chỗ đó rồi xuất bản lại. Đã lưu bản nháp.',
		);
		if ( isset( $msg[ $err ] ) ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $msg[ $err ] ) );
		}
	}
);

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ) {
		$screen = get_current_screen();
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $screen && 'av_project' === $screen->post_type ) {
			wp_enqueue_media();
			wp_enqueue_script( 'auviet-product-admin', plugins_url( 'assets/js/product-admin.js', AV_CORE_FILE ), array( 'jquery', 'jquery-ui-sortable' ), (string) filemtime( AV_CORE_DIR . 'assets/js/product-admin.js' ), true );
		}
	}
);

// Cột danh sách.
add_filter(
	'manage_av_project_posts_columns',
	static function ( array $cols ): array {
		$out = array();
		foreach ( $cols as $k => $v ) {
			if ( 'title' === $k ) {
				$out['av_thumb'] = '<span class="screen-reader-text">Ảnh</span>';
			}
			$out[ $k ] = $v;
			if ( 'title' === $k ) {
				$out['av_client']   = 'Khách hàng';
				$out['av_products'] = 'Sản phẩm';
			}
		}
		return $out;
	}
);

add_action(
	'manage_av_project_posts_custom_column',
	static function ( string $col, int $id ) {
		if ( 'av_thumb' === $col ) {
			echo get_the_post_thumbnail( $id, array( 48, 48 ) ) ?: '<span class="av-col-warn">Chưa có ảnh</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		} elseif ( 'av_client' === $col ) {
			$name = (string) get_post_meta( $id, '_av_client_name', true );
			if ( '' === $name ) {
				echo '—';
			} else {
				echo esc_html( $name );
				echo get_post_meta( $id, '_av_client_ok', true ) ? '' : '<br><span class="av-col-muted">Không nêu tên trên web</span>';
			}
			if ( get_post_meta( $id, '_av_sample', true ) ) {
				echo '<br><span class="av-col-warn">Mẫu thử</span>';
			}
		} elseif ( 'av_products' === $col ) {
			$ps = av_project_products( $id );
			echo $ps ? esc_html( implode( ', ', wp_list_pluck( $ps, 'post_title' ) ) ) : '<span class="av-col-warn">Chưa gắn sản phẩm</span>';
		}
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* Quản trị: đoạn giới thiệu trang danh sách                           */
/* ------------------------------------------------------------------ */

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'edit.php?post_type=av_project', 'Giới thiệu trang Mẫu đã làm', 'Giới thiệu trang', 'edit_av_projects', 'av-projects-intro', 'av_render_projects_intro_page' );
	}
);

add_action(
	'admin_init',
	static function () {
		register_setting(
			'av_projects_group',
			'av_projects_intro',
			array(
				'sanitize_callback' => static function ( $v ) {
					av_log( 'Sửa giới thiệu trang Mẫu đã làm' );
					return sanitize_textarea_field( (string) $v );
				},
			)
		);
	}
);
add_filter( 'option_page_capability_av_projects_group', static fn() => 'edit_av_projects' );

function av_render_projects_intro_page(): void {
	?>
	<div class="wrap">
		<h1>Giới thiệu trang Mẫu đã làm</h1>
		<?php av_saved_notice(); ?>
		<p>Đoạn chữ dưới tiêu đề trang <a href="<?php echo esc_url( get_post_type_archive_link( 'av_project' ) ); ?>" target="_blank">/mau-da-lam/</a>. Để trống thì dùng đoạn mặc định.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'av_projects_group' ); ?>
			<textarea class="large-text" rows="4" name="av_projects_intro"><?php echo esc_textarea( (string) get_option( 'av_projects_intro', '' ) ); ?></textarea>
			<p class="description">Mặc định: <?php echo esc_html( av_projects_intro() ); ?></p>
			<?php submit_button( 'Lưu' ); ?>
		</form>
	</div>
	<?php
}

/* ------------------------------------------------------------------ */
/* Phía khách: truy vấn, lọc                                           */
/* ------------------------------------------------------------------ */

function av_projects_state(): array {
	// phpcs:disable WordPress.Security.NonceVerification -- bộ lọc công khai, chỉ đọc.
	$ind  = isset( $_GET['nganh'] ) ? sanitize_title( wp_unslash( $_GET['nganh'] ) ) : '';
	$type = isset( $_GET['loai'] ) ? sanitize_title( wp_unslash( $_GET['loai'] ) ) : '';
	// phpcs:enable
	$industry = $ind ? get_term_by( 'slug', $ind, 'av_industry' ) : false;
	$group    = $type ? get_term_by( 'slug', $type, 'av_group' ) : false;
	return array(
		'industry' => $industry instanceof WP_Term ? $industry : null,
		'type'     => $group instanceof WP_Term ? $group : null,
	);
}

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'av_project' ) ) {
			return;
		}
		$q->set( 'posts_per_page', 12 );
		$state = av_projects_state();
		if ( $state['industry'] ) {
			$q->set( 'tax_query', array( array( 'taxonomy' => 'av_industry', 'terms' => $state['industry']->term_id ) ) );
		}
		if ( $state['type'] ) {
			$q->set( 'meta_query', array( array( 'key' => '_av_ptype', 'value' => (string) $state['type']->term_id ) ) );
		}
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
);

// /nganh/<nganh>/ → danh sách mẫu lọc theo ngành.
add_action(
	'template_redirect',
	static function () {
		if ( is_tax( 'av_industry' ) ) {
			wp_safe_redirect( add_query_arg( 'nganh', get_queried_object()->slug, get_post_type_archive_link( 'av_project' ) ), 301 );
			exit;
		}
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_post_type_archive( 'av_project' ) ) {
			wp_enqueue_script( 'auviet-projects', plugins_url( 'assets/js/projects.js', AV_CORE_FILE ), array(), (string) filemtime( AV_CORE_DIR . 'assets/js/projects.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}
		if ( is_singular( 'av_project' ) ) {
			wp_enqueue_script( 'auviet-product', plugins_url( 'assets/js/product.js', AV_CORE_FILE ), array(), (string) filemtime( AV_CORE_DIR . 'assets/js/product.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}
	}
);

/* ------------------------------------------------------------------ */
/* Khối hiển thị                                                       */
/* ------------------------------------------------------------------ */

add_filter(
	'av_block_definitions',
	static function ( array $defs ): array {
		return $defs + array(
			'auviet/projects-header'  => array( 'title' => 'Đầu trang Mẫu đã làm', 'icon' => 'format-gallery', 'render' => 'av_render_projects_header', 'description' => 'Tiêu đề, đoạn giới thiệu (Mẫu đã làm › Giới thiệu trang).' ),
			'auviet/projects-filters' => array( 'title' => 'Lọc mẫu đã làm', 'icon' => 'filter', 'render' => 'av_render_projects_filters', 'description' => 'Lọc theo loại sản phẩm và ngành, không tải lại trang.' ),
			'auviet/project-meta'     => array( 'title' => 'Ngành, loại của dự án', 'icon' => 'tag', 'render' => 'av_render_project_meta', 'description' => 'Dòng nhỏ trên thẻ dự án.', 'context' => array( 'postId', 'postType' ) ),
			'auviet/project-gallery'  => array( 'title' => 'Ảnh dự án', 'icon' => 'format-gallery', 'render' => 'av_render_project_gallery', 'description' => 'Ảnh lớn, ảnh nhỏ, bấm để phóng to.' ),
			'auviet/project-summary'  => array( 'title' => 'Thông tin dự án', 'icon' => 'info', 'render' => 'av_render_project_summary', 'description' => 'Khách hàng (nếu được phép), ngành, sản phẩm, quy cách.' ),
			'auviet/project-products' => array( 'title' => 'Sản phẩm của dự án', 'icon' => 'products', 'render' => 'av_render_project_products', 'description' => 'Thẻ các sản phẩm gắn với dự án.' ),
			'auviet/project-more'     => array( 'title' => 'Mẫu khác', 'icon' => 'grid-view', 'render' => 'av_render_project_more', 'description' => 'Dự án cùng loại sản phẩm hoặc cùng ngành.' ),
			'auviet/project-quote'    => array( 'title' => 'Báo giá sản phẩm tương tự', 'icon' => 'feedback', 'render' => 'av_render_project_quote', 'description' => 'Bốn nút liên hệ và form chọn sẵn sản phẩm của dự án.' ),
		);
	}
);

function av_render_projects_header(): string {
	$count = (int) wp_count_posts( 'av_project' )->publish;
	return sprintf(
		'<header class="av-cat-head"><h1 class="av-cat-head__title">Mẫu đã làm</h1><div class="av-cat-head__intro"><p>%s</p></div>%s</header>',
		esc_html( av_projects_intro() ),
		$count ? '<p class="av-cat-head__count">' . esc_html( $count . ' dự án' ) . '</p>' : ''
	);
}

/**
 * Các giá trị lọc có dự án (để không có lựa chọn rỗng).
 */
function av_projects_filter_terms(): array {
	$ids = get_posts( array( 'post_type' => 'av_project', 'numberposts' => -1, 'fields' => 'ids' ) );
	if ( ! $ids ) {
		return array( array(), array() );
	}
	$types = array();
	foreach ( $ids as $id ) {
		foreach ( (array) get_post_meta( $id, '_av_ptype' ) as $tid ) {
			$types[ (int) $tid ] = ( $types[ (int) $tid ] ?? 0 ) + 1;
		}
	}
	$type_terms = array();
	foreach ( av_visible_groups() as $g ) {
		if ( isset( $types[ $g->term_id ] ) ) {
			$g->av_count  = $types[ $g->term_id ];
			$type_terms[] = $g;
		}
	}
	$industries = get_terms( array( 'taxonomy' => 'av_industry', 'hide_empty' => true, 'object_ids' => $ids ) );
	return array( $type_terms, is_wp_error( $industries ) ? array() : $industries );
}

function av_render_projects_filters(): string {
	if ( ! is_post_type_archive( 'av_project' ) ) {
		return '';
	}
	[ $types, $industries ] = av_projects_filter_terms();
	if ( ! $types && ! $industries ) {
		return '';
	}
	$state = av_projects_state();
	$base  = get_post_type_archive_link( 'av_project' );
	$keep  = array_filter(
		array(
			'loai'  => $state['type'] ? $state['type']->slug : '',
			'nganh' => $state['industry'] ? $state['industry']->slug : '',
		)
	);
	$row = static function ( string $key, string $label, array $terms, ?WP_Term $active ) use ( $base, $keep ): string {
		if ( ! $terms ) {
			return '';
		}
		$out = sprintf( '<div class="av-pfilter"><span class="av-pfilter__label" id="av-pf-%1$s">%2$s</span><ul class="av-chips" aria-labelledby="av-pf-%1$s">', esc_attr( $key ), esc_html( $label ) );
		$all = array_diff_key( $keep, array( $key => 1 ) );
		$out .= sprintf( '<li><a class="av-chip%s" href="%s"%s>Tất cả</a></li>', $active ? '' : ' is-active', esc_url( add_query_arg( $all, $base ) ), $active ? '' : ' aria-current="true"' );
		foreach ( $terms as $t ) {
			$on   = $active && $active->term_id === $t->term_id;
			$out .= sprintf(
				'<li><a class="av-chip%s" href="%s"%s>%s</a></li>',
				$on ? ' is-active' : '',
				esc_url( add_query_arg( array( $key => $t->slug ) + $all, $base ) ),
				$on ? ' aria-current="true"' : '',
				esc_html( $t->name )
			);
		}
		return $out . '</ul></div>';
	};
	global $wp_query;
	$found = (int) $wp_query->found_posts;
	return sprintf(
		'<div class="av-filters av-pfilters" data-av-filters>%s%s<p class="av-pfilters__status" role="status" aria-live="polite">%s%s</p></div>',
		$row( 'loai', 'Loại sản phẩm', $types, $state['type'] ),
		$row( 'nganh', 'Ngành', $industries, $state['industry'] ),
		esc_html( $keep ? $found . ' dự án phù hợp' : '' ),
		$keep ? ' <a class="av-filter__clear" href="' . esc_url( $base ) . '">Bỏ lọc</a>' : ''
	);
}

function av_render_project_meta( array $attrs, string $content, WP_Block $block ): string {
	$id = (int) ( $block->context['postId'] ?? get_the_ID() );
	if ( ! $id || 'av_project' !== get_post_type( $id ) ) {
		return '';
	}
	$parts = array_merge( wp_list_pluck( av_project_types( $id ), 'name' ), wp_list_pluck( av_project_industries( $id ), 'name' ) );
	return $parts ? '<p class="av-pmeta">' . esc_html( implode( ' · ', array_unique( $parts ) ) ) . '</p>' : '';
}

function av_current_project_id(): int {
	$id = get_queried_object_id();
	return ( $id && 'av_project' === get_post_type( $id ) ) ? $id : 0;
}

/**
 * Ảnh dùng chung: ảnh lớn, dải ảnh nhỏ, phóng to (assets/js/product.js).
 *
 * @param int[] $ids
 */
function av_gallery_html( array $ids, string $title, string $label = 'Ảnh' ): string {
	if ( ! $ids ) {
		return '<div class="av-pg av-pg--empty"><div class="av-pg__main av-ph">[Chưa có ảnh]</div></div>';
	}
	$alt = static function ( int $aid, int $n ) use ( $title ): string {
		$a = trim( (string) get_post_meta( $aid, '_wp_attachment_image_alt', true ) );
		return $a ?: $title . ( $n > 1 ? ' – ảnh ' . $n : '' );
	};
	$out = '<div class="av-pg" data-av-gallery><button type="button" class="av-pg__main" data-av-zoom aria-label="Phóng to ảnh">' . wp_get_attachment_image( $ids[0], 'large', false, array( 'alt' => $alt( $ids[0], 1 ), 'data-av-main' => '1', 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '(max-width: 781px) 100vw, 640px' ) ) . '<span class="av-pg__zoom" aria-hidden="true">Phóng to</span></button>';
	if ( count( $ids ) > 1 ) {
		$out .= '<ul class="av-pg__thumbs">';
		foreach ( $ids as $n => $aid ) {
			$large = wp_get_attachment_image_src( $aid, 'large' );
			$full  = wp_get_attachment_image_src( $aid, 'full' );
			$out  .= sprintf(
				'<li><button type="button" class="av-pg__thumb%1$s" data-av-src="%2$s" data-av-srcset="%3$s" data-av-full="%4$s" data-av-alt="%5$s" aria-label="Xem ảnh %6$d"%8$s>%7$s</button></li>',
				0 === $n ? ' is-active' : '',
				esc_url( $large ? $large[0] : '' ),
				esc_attr( (string) wp_get_attachment_image_srcset( $aid, 'large' ) ),
				esc_url( $full ? $full[0] : '' ),
				esc_attr( $alt( $aid, $n + 1 ) ),
				$n + 1,
				wp_get_attachment_image( $aid, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ),
				0 === $n ? ' aria-current="true"' : ''
			);
		}
		$out .= '</ul>';
	} else {
		$full = wp_get_attachment_image_src( $ids[0], 'full' );
		$out .= sprintf( '<span hidden data-av-single data-av-full="%s" data-av-alt="%s"></span>', esc_url( $full ? $full[0] : '' ), esc_attr( $alt( $ids[0], 1 ) ) );
	}
	$out .= '<dialog class="av-lightbox" aria-label="' . esc_attr( $label ) . '"><img alt=""><button type="button" class="av-lightbox__prev" aria-label="Ảnh trước">‹</button><button type="button" class="av-lightbox__next" aria-label="Ảnh sau">›</button><button type="button" class="av-lightbox__close" aria-label="Đóng">×</button></dialog>';
	return $out . '</div>';
}

function av_render_project_gallery(): string {
	$id = av_current_project_id();
	return $id ? av_gallery_html( av_page_images( $id ), get_the_title( $id ), 'Ảnh dự án' ) : '';
}

function av_render_project_summary(): string {
	$id = av_current_project_id();
	if ( ! $id ) {
		return '';
	}
	$c     = av_contact();
	$rows  = array();
	$client = av_project_client( $id );
	if ( $client ) {
		$rows['Khách hàng'] = esc_html( $client );
	}
	$ind = av_project_industries( $id );
	if ( $ind ) {
		$rows['Ngành'] = implode(
			', ',
			array_map( static fn( $t ) => sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( 'nganh', $t->slug, get_post_type_archive_link( 'av_project' ) ) ), esc_html( $t->name ) ), $ind )
		);
	}
	$products = av_project_products( $id );
	if ( $products ) {
		$rows['Sản phẩm'] = implode( ', ', array_map( static fn( $p ) => sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $p ) ), esc_html( $p->post_title ) ), $products ) );
	}
	foreach ( av_project_specs( $id ) as $k => $v ) {
		$rows[ $k ] = esc_html( $v );
	}
	$out = '';
	if ( has_excerpt( $id ) ) {
		$out .= '<p class="av-ps__lead">' . esc_html( get_the_excerpt( $id ) ) . '</p>';
	}
	if ( $rows ) {
		$out .= '<table class="av-specs av-specs--compact"><tbody>';
		foreach ( $rows as $k => $v ) {
			$out .= sprintf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html( $k ), $v ); // Giá trị đã escape ở trên.
		}
		$out .= '</tbody></table>';
	}
	$out .= sprintf(
		'<div class="av-ps__cta"><a class="av-btn" href="#bao-gia">Báo giá sản phẩm tương tự</a><a class="av-btn av-btn--zalo" href="%1$s" target="_blank" rel="noopener" data-av-event="bam_zalo">Zalo</a><a class="av-btn av-btn--outline" href="%2$s" data-av-event="bam_goi">Gọi %3$s</a></div>',
		esc_url( $c['zalo_url'] ),
		esc_url( av_tel_href( $c['hotline'] ) ),
		esc_html( $c['hotline'] )
	);
	return '<div class="av-ps">' . $out . '</div>';
}

function av_render_project_products(): string {
	$id = av_current_project_id();
	$ps = $id ? av_project_products( $id ) : array();
	return $ps ? '<section class="av-psec" aria-labelledby="av-pjp-title"><h2 id="av-pjp-title">Sản phẩm trong dự án</h2>' . av_product_cards( $ps ) . '</section>' : '';
}

/**
 * Thẻ dự án dùng chung.
 *
 * @param WP_Post[] $posts
 */
function av_project_cards( array $posts ): string {
	$out = '<ul class="av-pjcards">';
	foreach ( $posts as $p ) {
		$parts = array_merge( wp_list_pluck( av_project_types( $p->ID ), 'name' ), wp_list_pluck( av_project_industries( $p->ID ), 'name' ) );
		$out  .= sprintf(
			'<li class="av-pjcard"><a href="%s">%s<strong>%s</strong>%s</a></li>',
			esc_url( get_permalink( $p ) ),
			get_the_post_thumbnail( $p, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) ) ?: '<span class="av-ph av-pjcard__ph">[Chưa có ảnh]</span>',
			esc_html( get_the_title( $p ) ),
			$parts ? '<span class="av-pmeta">' . esc_html( implode( ' · ', array_unique( $parts ) ) ) . '</span>' : ''
		);
	}
	return $out . '</ul>';
}

function av_render_project_more(): string {
	$id = av_current_project_id();
	if ( ! $id ) {
		return '';
	}
	$types = wp_list_pluck( av_project_types( $id ), 'term_id' );
	$ind   = wp_list_pluck( av_project_industries( $id ), 'term_id' );
	$args  = array(
		'post_type'    => 'av_project',
		'numberposts'  => 3,
		'post__not_in' => array( $id ),
	);
	$posts = array();
	if ( $types ) {
		$posts = get_posts( $args + array( 'meta_query' => array( array( 'key' => '_av_ptype', 'value' => array_map( 'strval', $types ), 'compare' => 'IN' ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	if ( count( $posts ) < 3 && $ind ) {
		$args['numberposts']  = 3 - count( $posts );
		$args['post__not_in'] = array_merge( array( $id ), wp_list_pluck( $posts, 'ID' ) );
		$posts                = array_merge( $posts, get_posts( $args + array( 'tax_query' => array( array( 'taxonomy' => 'av_industry', 'terms' => $ind ) ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	if ( count( $posts ) < 3 ) {
		$args['numberposts']  = 3 - count( $posts );
		$args['post__not_in'] = array_merge( array( $id ), wp_list_pluck( $posts, 'ID' ) );
		$posts                = array_merge( $posts, get_posts( $args ) );
	}
	if ( ! $posts ) {
		return '';
	}
	return sprintf(
		'<section class="av-psec" aria-labelledby="av-pjm-title"><div class="av-psec__head"><h2 id="av-pjm-title">Mẫu khác</h2><a href="%s">Xem tất cả mẫu</a></div>%s</section>',
		esc_url( get_post_type_archive_link( 'av_project' ) ),
		av_project_cards( $posts )
	);
}

function av_render_project_quote(): string {
	$id = av_current_project_id();
	if ( ! $id ) {
		return '';
	}
	$products = av_project_products( $id );
	$first    = $products ? $products[0] : null;
	$group    = $first ? av_product_primary_group( $first->ID ) : null;
	return sprintf(
		'<section class="av-pquote" id="bao-gia" aria-labelledby="av-pjq-title"><div class="av-pquote__text"><h2 id="av-pjq-title">Cần làm sản phẩm tương tự?</h2><p>Gửi số lượng, kích thước và mẫu Quý khách thích, Âu Việt gọi lại tư vấn chất liệu và báo giá. Hoặc liên hệ ngay:</p>%1$s</div><div class="av-pquote__form" id="lien-he">%2$s</div></section>',
		av_render_contact_tiles(),
		av_render_quote_form(
			array(
				'need'    => $group ? av_group_root( $group )->name : '',
				'product' => $first ? $first->ID : 0,
			)
		)
	);
}

/* ------------------------------------------------------------------ */
/* Dự án mẫu thử (chỉ môi trường local): gắn ảnh, sản phẩm, ngành       */
/* ------------------------------------------------------------------ */

add_action(
	'init',
	static function () {
		if ( 'local' !== wp_get_environment_type() || get_option( 'av_mig_projects_v1' ) ) {
			return;
		}
		// Chờ tới khi đã nhập sản phẩm từ web cũ (cần ảnh sản phẩm).
		$probe = get_page_by_path( 'in-hop-giay', OBJECT, 'av_product' );
		if ( ! $probe || ! get_post_thumbnail_id( $probe ) ) {
			return;
		}
		update_option( 'av_mig_projects_v1', 1, false );
		$samples = array(
			'Hộp quà Trung thu, ngành thực phẩm' => array( 'in-hop-giay', 'thuc-pham', array( 'qty' => '2.000 hộp', 'finishing' => 'Cán màng mờ, ép kim' ) ),
			'Catalogue sản phẩm, ngành nội thất' => array( 'in-catalogue', 'noi-that', array( 'size' => 'A4, 32 trang', 'qty' => '1.000 cuốn' ) ),
			'Backdrop hội nghị, trường đại học'  => array( 'in-backdrop', 'giao-duc', array( 'size' => '6 × 3 m', 'material' => 'Bạt hiflex' ) ),
			'Túi giấy kraft cho cửa hàng bánh'   => array( 'in-tui-giay-kraft', 'thuc-pham', array( 'qty' => '3.000 túi' ) ),
			'Tem nhãn chai cho thương hiệu mỹ phẩm' => array( 'in-tem-nhan', 'my-pham', array( 'material' => 'Decal nhựa PP' ) ),
			'Lịch Tết tặng khách hàng doanh nghiệp' => array( 'in-lich-treo-tuong', 'doanh-nghiep', array( 'qty' => '500 cuốn' ) ),
		);
		$order = 0;
		foreach ( $samples as $title => $s ) {
			$product = get_page_by_path( $s[0], OBJECT, 'av_product' );
			if ( ! $product ) {
				continue;
			}
			$found = get_posts( array( 'post_type' => 'av_project', 'title' => $title, 'post_status' => 'any', 'numberposts' => 1 ) );
			if ( $found && ! get_post_meta( $found[0]->ID, '_av_sample', true ) ) {
				continue; // Đã được sửa thành dự án thật.
			}
			$id = $found ? $found[0]->ID : (int) wp_insert_post( array( 'post_type' => 'av_project', 'post_status' => 'publish', 'post_title' => $title ) );
			if ( ! $id ) {
				continue;
			}
			wp_update_post(
				array(
					'ID'           => $id,
					'menu_order'   => $order++,
					'post_excerpt' => 'Dự án mẫu thử để xem giao diện, ảnh lấy từ trang sản phẩm. Thay bằng dự án thật trong Mẫu đã làm.',
				)
			);
			update_post_meta( $id, '_av_sample', 1 );
			update_post_meta( $id, '_av_products', array( $product->ID ) );
			$imgs = array_values( array_filter( array_map( 'intval', (array) get_post_meta( $product->ID, '_av_gallery', true ) ) ) );
			if ( ! $imgs ) {
				$imgs = array( (int) get_post_thumbnail_id( $product ) );
			}
			set_post_thumbnail( $id, $imgs[0] );
			update_post_meta( $id, '_av_gallery', array_slice( $imgs, 1, 4 ) );
			wp_set_object_terms( $id, $s[1], 'av_industry' );
			foreach ( $s[2] as $k => $v ) {
				update_post_meta( $id, '_av_pspec_' . $k, $v );
			}
			av_project_sync_types( $id );
		}
	},
	60
);
