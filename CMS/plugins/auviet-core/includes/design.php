<?php
/**
 * LP-03 Dịch vụ thiết kế: trang /thiet-ke/ và các trang con theo loại (/thiet-ke/catalogue/, /thiet-ke/bao-bi/…).
 *
 * Quản trị (Biên tập viên và Quản trị viên):
 * - Âu Việt › Thiết kế: phí thiết kế, số lần chỉnh sửa, thời gian, tư liệu cần chuẩn bị (hiện ở mọi trang thiết kế).
 * - Trang › Dịch vụ thiết kế và các trang con: chữ, ảnh, thứ tự khối. Thêm một loại thiết kế = thêm trang con
 *   của "Dịch vụ thiết kế" với mẫu "Trang loại thiết kế"; lưới loại thiết kế tự cập nhật.
 * - Yêu cầu gửi từ các trang này vào hộp thư với nhu cầu "Thiết kế" (lọc được theo nhu cầu).
 */

defined( 'ABSPATH' ) || exit;

const AV_DESIGN_TEMPLATE = 'page-design-service';

// Trang có đoạn mô tả ngắn (dùng cho thẻ loại thiết kế).
add_action( 'init', static fn() => add_post_type_support( 'page', 'excerpt' ) );

function av_design_page_id(): int {
	$id = (int) get_option( 'av_design_page' );
	return ( $id && 'page' === get_post_type( $id ) ) ? $id : 0;
}

/**
 * Các trang loại thiết kế (trang con đã xuất bản của Dịch vụ thiết kế).
 *
 * @return WP_Post[]
 */
function av_design_services( int $exclude = 0 ): array {
	$parent = av_design_page_id();
	if ( ! $parent ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'   => 'page',
			'post_parent' => $parent,
			'numberposts' => -1,
			'exclude'     => $exclude ? array( $exclude ) : array(),
			'orderby'     => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);
}

/* ------------------------------------------------------------------ */
/* Âu Việt › Thiết kế: điều khoản dịch vụ                              */
/* ------------------------------------------------------------------ */

function av_design_defaults(): array {
	return array(
		'fee'       => 'Miễn phí thiết kế card visit, tờ rơi, túi giấy khi in trọn gói tại Âu Việt. Ấn phẩm khác báo phí theo yêu cầu.',
		'revisions' => 'Chỉnh sửa đến khi Quý khách ưng ý; không giới hạn số lần khi dùng gói thiết kế và in trọn gói.',
		'time'      => '',
		'files'     => "Logo, nội dung chữ, ảnh sản phẩm (nếu có)\nMẫu tham khảo Quý khách thích\nẢnh định dạng JPG, PNG, PDF hoặc EPS, càng nét càng tốt\nĐã có file thiết kế: gửi file Illustrator (AI), InDesign hoặc PDF để Âu Việt kiểm tra trước khi in",
	);
}

function av_design(): array {
	return wp_parse_args( (array) get_option( 'av_design', array() ), av_design_defaults() );
}

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'auviet', 'Thiết kế', 'Thiết kế', 'av_manage_site_content', 'auviet-design', 'av_render_design_settings' );
	},
	16
);

add_action(
	'admin_init',
	static function () {
		register_setting(
			'av_design_group',
			'av_design',
			array(
				'sanitize_callback' => static function ( $in ) {
					$in = (array) $in;
					av_log( 'Sửa điều khoản dịch vụ thiết kế' );
					return array(
						'fee'       => sanitize_textarea_field( $in['fee'] ?? '' ),
						'revisions' => sanitize_textarea_field( $in['revisions'] ?? '' ),
						'time'      => sanitize_textarea_field( $in['time'] ?? '' ),
						'files'     => sanitize_textarea_field( $in['files'] ?? '' ),
					);
				},
			)
		);
	}
);
add_filter( 'option_page_capability_av_design_group', static fn() => 'av_manage_site_content' );

function av_render_design_settings(): void {
	if ( ! current_user_can( 'av_manage_site_content' ) ) {
		return;
	}
	$d    = av_design();
	$page = av_design_page_id();
	$rows = array(
		'fee'       => array( 'Phí thiết kế', 'Ghi đúng chính sách đang áp dụng.' ),
		'revisions' => array( 'Số lần chỉnh sửa', '' ),
		'time'      => array( 'Thời gian thiết kế', 'Ví dụ: bản nháp đầu tiên sau 1–2 ngày làm việc. Để trống thì không hiện.' ),
		'files'     => array( 'Tư liệu cần chuẩn bị', 'Mỗi dòng một ý.' ),
	);
	?>
	<div class="wrap">
		<h1>Dịch vụ thiết kế</h1>
		<?php av_saved_notice(); ?>
		<p>
			Các mục dưới đây hiện ở trang <a href="<?php echo esc_url( home_url( '/thiet-ke/' ) ); ?>" target="_blank">/thiet-ke/</a> và mọi trang loại thiết kế.
			Chữ, ảnh, thứ tự khối: <?php echo $page ? '<a href="' . esc_url( get_edit_post_link( $page ) ) . '">sửa trang Dịch vụ thiết kế</a>' : 'Trang › Dịch vụ thiết kế'; ?>.
			Thêm loại thiết kế: <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>">thêm trang</a>, chọn Trang cha "Dịch vụ thiết kế" và mẫu "Trang loại thiết kế".
			Yêu cầu khách gửi từ các trang này: <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=av_quote&av_need=' . rawurlencode( 'Thiết kế' ) ) ); ?>">hộp thư, nhu cầu Thiết kế</a> (Quản trị viên).
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'av_design_group' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( $rows as $k => $r ) : ?>
					<tr>
						<th scope="row"><label for="av-design-<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $r[0] ); ?></label></th>
						<td>
							<textarea class="large-text" rows="<?php echo 'files' === $k ? 5 : 2; ?>" id="av-design-<?php echo esc_attr( $k ); ?>" name="av_design[<?php echo esc_attr( $k ); ?>]"><?php echo esc_textarea( $d[ $k ] ); ?></textarea>
							<?php if ( $r[1] ) : ?><p class="description"><?php echo esc_html( $r[1] ); ?></p><?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
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
			'auviet/design-terms'    => array( 'title' => 'Điều khoản thiết kế', 'icon' => 'clipboard', 'render' => 'av_render_design_terms', 'description' => 'Phí, số lần chỉnh sửa, thời gian, tư liệu (Âu Việt › Thiết kế).' ),
			'auviet/design-services' => array( 'title' => 'Các loại thiết kế', 'icon' => 'grid-view', 'render' => 'av_render_design_services', 'description' => 'Thẻ các trang con của Dịch vụ thiết kế.' ),
			'auviet/design-gallery'  => array( 'title' => 'Mẫu thiết kế', 'icon' => 'format-gallery', 'render' => 'av_render_design_gallery', 'description' => 'Ảnh mẫu lấy từ các trang loại thiết kế.' ),
			'auviet/page-gallery'    => array( 'title' => 'Ảnh của trang', 'icon' => 'format-image', 'render' => 'av_render_page_gallery', 'description' => 'Ảnh đại diện và thư viện ảnh của trang, bấm để phóng to.' ),
			'auviet/design-quote'    => array( 'title' => 'Gửi yêu cầu thiết kế', 'icon' => 'feedback', 'render' => 'av_render_design_quote', 'description' => 'Bốn nút liên hệ và form chọn sẵn nhu cầu Thiết kế, có tải file.' ),
		);
	}
);

function av_render_design_terms(): string {
	$d     = av_design();
	$cards = array(
		array( 'Phí thiết kế', $d['fee'] ),
		array( 'Chỉnh sửa', $d['revisions'] ),
		array( 'Thời gian', $d['time'] ),
	);
	$out = '<div class="av-terms">';
	foreach ( $cards as $c ) {
		if ( '' !== trim( $c[1] ) ) {
			$out .= sprintf( '<div class="av-term"><strong>%s</strong><p>%s</p></div>', esc_html( $c[0] ), esc_html( $c[1] ) );
		}
	}
	$lines = array_filter( array_map( 'trim', preg_split( '/\R/', (string) $d['files'] ) ) );
	if ( $lines ) {
		$out .= '<div class="av-term av-term--wide"><strong>Quý khách chuẩn bị</strong><ul>' . implode( '', array_map( static fn( $l ) => '<li>' . esc_html( $l ) . '</li>', $lines ) ) . '</ul></div>';
	}
	return $out . '</div>';
}

function av_render_design_services(): string {
	$current = is_page() ? get_queried_object_id() : 0;
	$items   = av_design_services( $current );
	if ( ! $items ) {
		return current_user_can( 'edit_pages' ) ? '<p class="av-clients-empty">Chưa có trang loại thiết kế. Chạy Công cụ › Nhập từ web cũ hoặc thêm trang con của "Dịch vụ thiết kế".</p>' : '';
	}
	$out = '<ul class="av-pcards av-dcards">';
	foreach ( $items as $p ) {
		$thumb = get_the_post_thumbnail( $p, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) );
		$out  .= sprintf(
			'<li class="av-pcard"><a href="%s">%s<strong>%s</strong>%s</a></li>',
			esc_url( get_permalink( $p ) ),
			$thumb ?: '<span class="av-ph av-pcard__ph">[Chưa có ảnh]</span>',
			esc_html( get_the_title( $p ) ),
			has_excerpt( $p ) ? '<span class="av-dcard__ex">' . esc_html( wp_trim_words( get_the_excerpt( $p ), 18, '…' ) ) . '</span>' : ''
		);
	}
	return $out . '</ul>';
}

/**
 * Ảnh trang: ảnh đại diện + thư viện _av_gallery.
 *
 * @return int[]
 */
function av_page_images( int $page_id ): array {
	$ids   = array();
	$thumb = (int) get_post_thumbnail_id( $page_id );
	if ( $thumb ) {
		$ids[] = $thumb;
	}
	foreach ( (array) get_post_meta( $page_id, '_av_gallery', true ) as $id ) {
		$id = (int) $id;
		if ( $id && ! in_array( $id, $ids, true ) && wp_attachment_is_image( $id ) ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

function av_render_design_gallery(): string {
	$ids = array();
	foreach ( av_design_services() as $p ) {
		foreach ( array_slice( av_page_images( $p->ID ), 0, 2 ) as $id ) {
			$ids[ $id ] = $p;
		}
	}
	if ( ! $ids ) {
		return '';
	}
	$out = '<ul class="av-dgallery">';
	foreach ( array_slice( $ids, 0, 12, true ) as $id => $p ) {
		$out .= sprintf(
			'<li><a href="%s">%s<span>%s</span></a></li>',
			esc_url( get_permalink( $p ) ),
			wp_get_attachment_image( $id, 'medium_large', false, array( 'alt' => get_the_title( $p ), 'loading' => 'lazy' ) ),
			esc_html( get_the_title( $p ) )
		);
	}
	return $out . '</ul>';
}

function av_render_page_gallery(): string {
	$id = is_page() ? get_queried_object_id() : 0;
	if ( ! $id ) {
		return '';
	}
	$ids   = av_page_images( $id );
	$title = get_the_title( $id );
	if ( ! $ids ) {
		return '';
	}
	$out = '<div class="av-pg" data-av-gallery><button type="button" class="av-pg__main" data-av-zoom aria-label="Phóng to ảnh">' . wp_get_attachment_image( $ids[0], 'large', false, array( 'alt' => $title, 'data-av-main' => '1', 'fetchpriority' => 'high', 'loading' => 'eager' ) ) . '<span class="av-pg__zoom" aria-hidden="true">Phóng to</span></button>';
	if ( count( $ids ) > 1 ) {
		$out .= '<ul class="av-pg__thumbs">';
		foreach ( $ids as $n => $aid ) {
			$large = wp_get_attachment_image_src( $aid, 'large' );
			$full  = wp_get_attachment_image_src( $aid, 'full' );
			$out  .= sprintf(
				'<li><button type="button" class="av-pg__thumb%1$s" data-av-src="%2$s" data-av-srcset="%3$s" data-av-full="%4$s" data-av-alt="%5$s" aria-label="Xem ảnh %6$d">%7$s</button></li>',
				0 === $n ? ' is-active' : '',
				esc_url( $large ? $large[0] : '' ),
				esc_attr( (string) wp_get_attachment_image_srcset( $aid, 'large' ) ),
				esc_url( $full ? $full[0] : '' ),
				esc_attr( $title . ' – mẫu ' . ( $n + 1 ) ),
				$n + 1,
				wp_get_attachment_image( $aid, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) )
			);
		}
		$out .= '</ul>';
	} else {
		$full = wp_get_attachment_image_src( $ids[0], 'full' );
		$out .= sprintf( '<span hidden data-av-single data-av-full="%s" data-av-alt="%s"></span>', esc_url( $full ? $full[0] : '' ), esc_attr( $title ) );
	}
	$out .= '<dialog class="av-lightbox" aria-label="Ảnh mẫu"><img alt=""><button type="button" class="av-lightbox__prev" aria-label="Ảnh trước">‹</button><button type="button" class="av-lightbox__next" aria-label="Ảnh sau">›</button><button type="button" class="av-lightbox__close" aria-label="Đóng">×</button></dialog>';
	return $out . '</div>';
}

function av_render_design_quote(): string {
	$title = is_page() && get_queried_object_id() !== av_design_page_id() ? mb_strtolower( get_the_title( get_queried_object_id() ) ) : 'thiết kế';
	return sprintf(
		'<section class="av-pquote" id="bao-gia" aria-labelledby="av-dquote-title"><div class="av-pquote__text"><h2 id="av-dquote-title">Gửi yêu cầu %1$s</h2><p>Mô tả ngắn nhu cầu, gửi kèm logo, nội dung hoặc mẫu tham khảo. Âu Việt gọi lại tư vấn hướng thiết kế, phí và thời gian.</p>%2$s</div><div class="av-pquote__form" id="lien-he">%3$s</div></section>',
		esc_html( $title ),
		av_render_contact_tiles(),
		av_render_quote_form( array( 'need' => 'Thiết kế' ) )
	);
}

// Ảnh phóng to dùng chung script với trang sản phẩm.
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_page() && ( get_queried_object_id() === av_design_page_id() || (int) wp_get_post_parent_id( get_queried_object_id() ) === av_design_page_id() ) ) {
			wp_enqueue_script( 'auviet-product', plugins_url( 'assets/js/product.js', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/js/product.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}
	}
);

/* ------------------------------------------------------------------ */
/* Thư viện ảnh cho trang loại thiết kế (quản trị)                     */
/* ------------------------------------------------------------------ */

add_action(
	'add_meta_boxes_page',
	static function ( WP_Post $post ) {
		if ( (int) $post->post_parent !== av_design_page_id() || ! av_design_page_id() ) {
			return;
		}
		add_meta_box(
			'av_page_gallery',
			'Ảnh mẫu thiết kế',
			static function ( WP_Post $p ) {
				wp_nonce_field( 'av_page_gallery', 'av_page_gallery_nonce' );
				av_box_product_gallery( $p );
			},
			'page',
			'normal',
			'high'
		);
	}
);

add_action(
	'save_post_page',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['av_page_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_page_gallery_nonce'] ), 'av_page_gallery' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$gallery = array_values( array_filter( array_map( 'absint', explode( ',', (string) wp_unslash( $_POST['av_gallery'] ?? '' ) ) ), 'wp_attachment_is_image' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		update_post_meta( $post_id, '_av_gallery', $gallery );
	}
);

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ) {
		$screen = get_current_screen();
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $screen && 'page' === $screen->post_type ) {
			wp_enqueue_media();
			wp_enqueue_script( 'auviet-product-admin', plugins_url( 'assets/js/product-admin.js', AV_CORE_FILE ), array( 'jquery', 'jquery-ui-sortable' ), av_ver( AV_CORE_DIR . 'assets/js/product-admin.js' ), true );
		}
	}
);

/* ------------------------------------------------------------------ */
/* Nhập trang thiết kế từ web cũ (Công cụ › Nhập từ web cũ)            */
/* ------------------------------------------------------------------ */

/**
 * Loại thiết kế mới, gom từ các trang /pddetail/...-508-... của web cũ.
 */
function av_legacy_designs(): array {
	return array(
		'catalogue'        => array( 'Thiết kế catalogue', array( '/pddetail/thiet-ke-catalogue-508-3175.html' ) ),
		'brochure-profile' => array( 'Thiết kế brochure, profile', array( '/pddetail/thiet-ke-brochure-508-3173.html' ) ),
		'to-roi-to-gap'    => array( 'Thiết kế tờ rơi, tờ gấp', array( '/pddetail/thiet-ke-to-roi-to-gap-508-3176.html' ) ),
		'bao-bi'           => array( 'Thiết kế bao bì, hộp giấy', array( '/pddetail/thiet-ke-bao-bi-508-3177.html' ) ),
		'tui-giay'         => array( 'Thiết kế túi giấy', array( '/pddetail/thiet-ke-tui-giay-508-3182.html' ) ),
		'sach-ky-yeu'      => array( 'Thiết kế sách, kỷ yếu', array( '/pddetail/thiet-ke-sach-508-3178.html' ) ),
		'tap-chi'          => array( 'Thiết kế tạp chí', array( '/pddetail/thiet-ke-tap-chi-508-3179.html' ) ),
		'so-tay'           => array( 'Thiết kế sổ tay', array( '/pddetail/thiet-ke-so-tay-508-3180.html' ) ),
		'giay-khen'        => array( 'Thiết kế giấy khen, bằng khen', array( '/pddetail/thiet-ke-giay-khen-bang-khen-508-3181.html' ) ),
		'banner-backdrop'  => array( 'Thiết kế banner, backdrop, poster', array( '/pddetail/thiet-ke-banner-backdrop-phuon-poster-508-3205.html', '/pddetail/backdrop-508-3224.html' ) ),
	);
}

add_action(
	'wp_ajax_av_legacy_design',
	static function () {
		av_legacy_guard();
		$slug  = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$index = absint( $_POST['index'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		$force = ! empty( $_POST['force'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$all   = av_legacy_designs();
		$parent = av_design_page_id();
		if ( ! isset( $all[ $slug ][1][ $index ] ) || ! $parent ) {
			wp_send_json_error( 'Chưa có trang Dịch vụ thiết kế hoặc không có trang cũ này.' );
		}
		[ $title, $sources ] = $all[ $slug ];
		$path = $sources[ $index ];
		$page = av_legacy_fetch( $path );
		if ( is_wp_error( $page ) ) {
			wp_send_json_error( $title . ': không tải được ' . $path . ' (' . $page->get_error_message() . ')' );
		}

		$existing = get_posts( array( 'post_type' => 'page', 'post_parent' => $parent, 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1 ) );
		if ( $existing ) {
			$post_id = $existing[0]->ID;
		} else {
			$post_id = (int) wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $parent, 'post_name' => $slug, 'post_title' => $title, 'menu_order' => array_search( $slug, array_keys( $all ), true ) + 1 ) );
			if ( ! $post_id ) {
				wp_send_json_error( $title . ': không tạo được trang.' );
			}
		}
		update_post_meta( $post_id, '_wp_page_template', AV_DESIGN_TEMPLATE );
		$fresh  = $force || av_legacy_is_fresh( $post_id );
		$images = 0;
		$found  = array();
		foreach ( $page['slides'] as $url ) {
			$aid = av_legacy_sideload( $url, $post_id, $title );
			if ( $aid ) {
				$found[] = $aid;
				++$images;
			}
		}
		if ( 0 === $index && $fresh ) {
			$content = $page['content'] ? av_legacy_blocks( $page['content'], $post_id, $title, $images ) : '';
			kses_remove_filters();
			wp_update_post( array( 'ID' => $post_id, 'post_title' => $title, 'post_excerpt' => wp_trim_words( $page['intro'], 45, '…' ), 'post_content' => $content ) );
			kses_init();
			if ( $found ) {
				set_post_thumbnail( $post_id, $found[0] );
			}
		}
		if ( $page['content'] ) {
			foreach ( array_slice( av_legacy_content_images( $page['content'] ), 0, 0 === $index ? 8 : 12 ) as $url ) {
				$aid = av_legacy_sideload( $url, $post_id, $title );
				if ( $aid ) {
					$found[] = $aid;
				}
			}
		}
		$gallery = array_map( 'intval', (array) get_post_meta( $post_id, '_av_gallery', true ) );
		$thumb   = (int) get_post_thumbnail_id( $post_id );
		foreach ( $found as $aid ) {
			if ( $aid !== $thumb && ! in_array( $aid, $gallery, true ) && count( $gallery ) < 15 ) {
				$gallery[] = $aid;
			}
		}
		update_post_meta( $post_id, '_av_gallery', $gallery );
		if ( ! $thumb && $gallery ) {
			set_post_thumbnail( $post_id, array_shift( $gallery ) );
			update_post_meta( $post_id, '_av_gallery', $gallery );
		}
		if ( 0 === $index && $fresh ) {
			update_post_meta( $post_id, '_av_legacy_time', get_post_field( 'post_modified_gmt', $post_id ) );
		}
		if ( preg_match( '#-(\d{3,5})\.html$#', $path, $m ) ) {
			$map          = (array) get_option( 'av_legacy_map', array() );
			$map[ $m[1] ] = $post_id;
			update_option( 'av_legacy_map', $map, false );
		}
		wp_send_json_success( array( 'message' => sprintf( '%s ← %s: %d ảnh%s', $title, $path, $images, ( 0 === $index && ! $fresh ) ? ' (giữ nội dung đã sửa tay)' : '' ) ) );
	}
);

// Máy offset theo web cũ là Roland 500: đổi tên mục máy tạo sẵn nếu chưa ai sửa.
add_action(
	'init',
	static function () {
		if ( get_option( 'av_mig_roland' ) ) {
			return;
		}
		update_option( 'av_mig_roland', 1, false );
		$m = get_posts( array( 'post_type' => 'av_machine', 'title' => 'Máy in offset', 'numberposts' => 1 ) );
		if ( $m && $m[0]->post_modified_gmt === $m[0]->post_date_gmt ) {
			wp_update_post( array( 'ID' => $m[0]->ID, 'post_title' => 'Máy in offset Roland 500', 'post_content' => 'In số lượng lớn với màu sắc đẹp, ổn định giữa các lô: tờ rơi, catalogue, túi giấy, hộp giấy, lịch.' ) );
			update_post_meta( $m[0]->ID, '_av_spec', 'In offset' );
		}
	},
	45
);
