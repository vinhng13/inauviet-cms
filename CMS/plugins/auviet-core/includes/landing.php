<?php
/**
 * LP-02 Landing theo mùa (lịch Tết, lì xì, Trung thu, quà tặng doanh nghiệp): mỗi chiến dịch một trang trên web chính,
 * ví dụ /lich-tet/. Trang gồm: đầu trang, hạn chót đặt hàng, sản phẩm, bảng giá tham khảo, mẫu tiêu biểu, quy trình,
 * hỏi đáp, form báo giá chọn sẵn sản phẩm (yêu cầu ghi trang nguồn = landing).
 *
 * - Hẹn giờ: "Hiện từ" / "Ẩn sau". Ngoài khoảng đó khách vào URL được chuyển tạm (302) về nhóm sản phẩm tương ứng,
 *   không báo lỗi 404; không vào sitemap. Người có quyền sửa vẫn xem được, kèm dòng báo trạng thái.
 * - Mùa sau: "Nhân bản cho mùa sau" tạo bản nháp giống hệt. Khi đăng bản mới, bản cũ được cất (riêng tư, đường dẫn
 *   thêm năm) và bản mới giữ đúng URL cũ, nên link đã in, đã quảng cáo vẫn dùng được qua các năm.
 * - Quản trị: Âu Việt › Landing theo mùa (Biên tập viên, Quản trị viên).
 */

defined( 'ABSPATH' ) || exit;

const AV_LANDING_TEMPLATE = 'page-landing';

/* ------------------------------------------------------------------ */
/* Dữ liệu                                                             */
/* ------------------------------------------------------------------ */

function av_is_landing( int $id ): bool {
	return $id && 'page' === get_post_type( $id ) && (bool) get_post_meta( $id, '_av_landing', true );
}

function av_landing( int $id ): array {
	return array(
		'season'   => (string) get_post_meta( $id, '_av_land_season', true ),
		'from'     => (string) get_post_meta( $id, '_av_land_from', true ),
		'to'       => (string) get_post_meta( $id, '_av_land_to', true ),
		'deadline' => (string) get_post_meta( $id, '_av_land_deadline', true ),
		'products' => array_values( array_filter( array_map( 'intval', (array) get_post_meta( $id, '_av_land_products', true ) ) ) ),
		'group'    => (int) get_post_meta( $id, '_av_land_group', true ),
		'faqs'     => array_values( array_filter( array_map( 'intval', (array) get_post_meta( $id, '_av_land_faqs', true ) ) ) ),
		'replaces' => (int) get_post_meta( $id, '_av_land_replaces', true ),
	);
}

/**
 * Chuỗi ngày giờ "Y-m-d H:i" (giờ Việt Nam) → timestamp, rỗng → 0.
 */
function av_land_ts( string $local ): int {
	if ( '' === trim( $local ) ) {
		return 0;
	}
	$d = date_create_immutable( $local, wp_timezone() );
	return $d ? $d->getTimestamp() : 0;
}

/**
 * Trạng thái hiển thị với khách: live | before | after | draft.
 */
function av_landing_state( int $id ): string {
	if ( 'publish' !== get_post_status( $id ) ) {
		return 'draft';
	}
	$l    = av_landing( $id );
	$now  = time();
	$from = av_land_ts( $l['from'] );
	$to   = av_land_ts( $l['to'] );
	if ( $from && $now < $from ) {
		return 'before';
	}
	if ( $to && $now >= $to ) {
		return 'after';
	}
	return 'live';
}

function av_landing_is_live( int $id ): bool {
	return 'live' === av_landing_state( $id );
}

/**
 * @return WP_Post[]
 */
function av_landing_products( int $id ): array {
	$ids = av_landing( $id )['products'];
	return $ids ? get_posts( array( 'post_type' => 'av_product', 'post__in' => $ids, 'orderby' => 'post__in', 'numberposts' => -1 ) ) : array();
}

/**
 * Nhóm sản phẩm chuyển tới khi landing ẩn: chọn tay, không có thì nhóm cấp 1 của sản phẩm đầu tiên.
 */
function av_landing_fallback( int $id ): string {
	$l = av_landing( $id );
	$t = $l['group'] ? get_term( $l['group'], 'av_group' ) : null;
	if ( ! $t instanceof WP_Term ) {
		$ps = av_landing_products( $id );
		$g  = $ps ? av_product_primary_group( $ps[0]->ID ) : null;
		$t  = $g ? av_group_root( $g ) : null;
	}
	$link = $t instanceof WP_Term ? get_term_link( $t ) : '';
	return is_string( $link ) && $link ? $link : (string) get_post_type_archive_link( 'av_product' );
}

/**
 * Các landing (mọi trạng thái trừ thùng rác).
 *
 * @return WP_Post[]
 */
function av_landings( array $status = array( 'publish', 'draft', 'pending', 'future', 'private' ) ): array {
	return get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => $status,
			'numberposts' => -1,
			'meta_key'    => '_av_landing', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'     => 'modified',
			'order'       => 'DESC',
		)
	);
}

function av_date_vn( string $local, bool $time = true ): string {
	$ts = av_land_ts( $local );
	return $ts ? wp_date( $time ? 'H:i d/m/Y' : 'd/m/Y', $ts ) : '';
}

/* ------------------------------------------------------------------ */
/* Phía khách: ẩn ngoài thời gian chạy, không 404                       */
/* ------------------------------------------------------------------ */

add_action(
	'template_redirect',
	static function () {
		if ( ! is_page() ) {
			return;
		}
		$id = get_queried_object_id();
		if ( ! av_is_landing( $id ) || av_landing_is_live( $id ) || current_user_can( 'edit_post', $id ) ) {
			return;
		}
		nocache_headers();
		do_action( 'litespeed_control_set_nocache', 'av landing hidden' );
		wp_safe_redirect( av_landing_fallback( $id ), 302, 'Au Viet landing' );
		exit;
	},
	5
);

// Người sửa xem landing đang ẩn: dòng báo ở đầu trang.
add_action(
	'wp_body_open',
	static function () {
		if ( ! is_page() || ! av_is_landing( get_queried_object_id() ) ) {
			return;
		}
		$id    = get_queried_object_id();
		$state = av_landing_state( $id );
		if ( 'live' === $state ) {
			return;
		}
		$l   = av_landing( $id );
		$msg = array(
			'before' => 'Landing chưa hiện với khách: sẽ tự hiện lúc ' . av_date_vn( $l['from'] ) . '.',
			'after'  => 'Landing đã ẩn với khách từ ' . av_date_vn( $l['to'] ) . '. Khách vào địa chỉ này được chuyển tới nhóm sản phẩm.',
			'draft'  => 'Landing đang là bản nháp, khách chưa xem được.',
		);
		printf( '<div class="av-land-notice" role="status">%s <a href="%s">Sửa landing</a></div>', esc_html( $msg[ $state ] ), esc_url( (string) get_edit_post_link( $id ) ) );
	}
);

// Template riêng (toàn chiều rộng) cho landing.
add_filter(
	'get_post_metadata',
	static function ( $value, $object_id, $meta_key ) {
		if ( '_wp_page_template' === $meta_key && av_is_landing( (int) $object_id ) ) {
			return array( AV_LANDING_TEMPLATE );
		}
		return $value;
	},
	10,
	3
);

// Không đưa landing đang ẩn vào sitemap, không cho Google lập chỉ mục khi đang ẩn.
add_filter(
	'rank_math/sitemap/entry',
	static function ( $url, $type, $obj ) {
		if ( 'post' === $type && $obj instanceof WP_Post && av_is_landing( $obj->ID ) && ! av_landing_is_live( $obj->ID ) ) {
			return false;
		}
		return $url;
	},
	10,
	3
);
add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( is_page() && av_is_landing( get_queried_object_id() ) && ! av_landing_is_live( get_queried_object_id() ) ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

// Bộ nhớ đệm trang: hết hạn đúng lúc landing tự hiện / tự ẩn; xoá một lần khi trạng thái đổi.
add_action(
	'wp',
	static function () {
		if ( ! is_page() || ! av_is_landing( get_queried_object_id() ) ) {
			return;
		}
		$l    = av_landing( get_queried_object_id() );
		$next = array_filter( array( av_land_ts( $l['from'] ), av_land_ts( $l['to'] ) ), static fn( $t ) => $t > time() );
		if ( $next ) {
			do_action( 'litespeed_control_set_ttl', max( 60, min( $next ) - time() ) );
		}
	}
);
add_action(
	'init',
	static function () {
		$state = array();
		foreach ( av_landings( array( 'publish' ) ) as $p ) {
			$state[ $p->ID ] = av_landing_state( $p->ID );
		}
		if ( get_option( 'av_landing_states' ) !== $state ) {
			update_option( 'av_landing_states', $state, true );
			if ( function_exists( 'av_purge_page_cache' ) ) {
				av_purge_page_cache();
			}
		}
	},
	99
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_page() && av_is_landing( get_queried_object_id() ) ) {
			wp_enqueue_style( 'auviet-landing', plugins_url( 'assets/css/landing.css', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/css/landing.css' ) );
			wp_enqueue_script( 'auviet-product', plugins_url( 'assets/js/product.js', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/js/product.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}
	}
);
add_action(
	'enqueue_block_assets',
	static function () {
		if ( ! is_admin() ) {
			return;
		}
		wp_enqueue_style( 'auviet-landing', plugins_url( 'assets/css/landing.css', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/css/landing.css' ) );
	}
);

/* ------------------------------------------------------------------ */
/* Khối hiển thị (sắp xếp, bật/tắt trong trình soạn khối của trang)    */
/* ------------------------------------------------------------------ */

add_filter(
	'av_block_definitions',
	static function ( array $defs ): array {
		return $defs + array(
			'auviet/landing-hero'     => array( 'title' => 'Landing: đầu trang', 'icon' => 'megaphone', 'render' => 'av_render_landing_hero', 'description' => 'Tên mùa, tiêu đề, đoạn mở đầu, hạn chót, nút báo giá, ảnh.' ),
			'auviet/landing-deadline' => array( 'title' => 'Landing: hạn chót đặt hàng', 'icon' => 'clock', 'render' => 'av_render_landing_deadline', 'description' => 'Hạn chót và số ngày còn lại.' ),
			'auviet/landing-products' => array( 'title' => 'Landing: sản phẩm', 'icon' => 'products', 'render' => 'av_render_landing_products', 'description' => 'Thẻ các sản phẩm của landing.' ),
			'auviet/landing-prices'   => array( 'title' => 'Landing: bảng giá tham khảo', 'icon' => 'money-alt', 'render' => 'av_render_landing_prices', 'description' => 'Bảng giá của từng sản phẩm, chưa có giá thì "Liên hệ báo giá".' ),
			'auviet/landing-samples'  => array( 'title' => 'Landing: mẫu tiêu biểu', 'icon' => 'format-gallery', 'render' => 'av_render_landing_samples', 'description' => 'Ảnh thật lấy từ sản phẩm của landing.' ),
			'auviet/landing-faq'      => array( 'title' => 'Landing: hỏi đáp', 'icon' => 'editor-help', 'render' => 'av_render_landing_faq', 'description' => 'Câu hỏi chọn trong hộp Landing theo mùa.' ),
			'auviet/landing-quote'    => array(
				'title'       => 'Landing: form báo giá',
				'icon'        => 'feedback',
				'render'      => 'av_render_landing_quote',
				'description' => 'Bốn nút liên hệ và form chọn sẵn sản phẩm.',
				'attributes'  => array(
					'title' => array( 'type' => 'string', 'default' => '' ),
					'text'  => array( 'type' => 'string', 'default' => '' ),
				),
			),
		);
	}
);

/**
 * ID landing đang hiển thị (trang khách xem hoặc trang đang sửa trong trình soạn).
 */
function av_current_landing_id(): int {
	$id = is_page() ? get_queried_object_id() : 0;
	if ( ! $id && in_the_loop() ) {
		$id = (int) get_the_ID();
	}
	if ( ! $id && defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$id = absint( $_GET['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	if ( ! $id && defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		$id = (int) get_the_ID();
	}
	return av_is_landing( $id ) ? $id : 0;
}

function av_landing_deadline_text( array $l ): array {
	$ts = $l['deadline'] ? av_land_ts( $l['deadline'] . ' 23:59' ) : 0;
	if ( ! $ts ) {
		return array( '', '' );
	}
	$days = (int) ceil( ( $ts - time() ) / DAY_IN_SECONDS );
	$date = wp_date( 'd/m/Y', $ts );
	if ( $days < 0 ) {
		return array( 'Đã qua hạn đặt ' . $date, 'Vẫn nhận đơn gấp tuỳ lịch xưởng, gọi để được báo thời gian.' );
	}
	return array( 'Đặt trước ' . $date, 0 === $days ? 'Hôm nay là ngày cuối' : 'Còn ' . $days . ' ngày để kịp nhận hàng' );
}

function av_render_landing_hero(): string {
	$id = av_current_landing_id();
	if ( ! $id ) {
		return '';
	}
	$l     = av_landing( $id );
	$c     = av_contact();
	$ps    = av_landing_products( $id );
	$img   = (int) get_post_thumbnail_id( $id );
	if ( ! $img && $ps ) {
		$img = (int) get_post_thumbnail_id( $ps[0] );
	}
	[ $dl, $dl_sub ] = av_landing_deadline_text( $l );
	return sprintf(
		'<section class="av-lhero"><div class="av-lhero__text">%1$s<h1 class="av-lhero__title">%2$s</h1>%3$s%4$s<div class="av-lhero__cta"><a class="av-btn" href="#bao-gia">Nhận báo giá</a><a class="av-btn av-btn--zalo" href="%5$s" target="_blank" rel="noopener" data-av-event="bam_zalo">Zalo</a><a class="av-btn av-btn--outline" href="%6$s" data-av-event="bam_goi">Gọi %7$s</a></div></div><div class="av-lhero__img">%8$s</div></section>',
		$l['season'] ? '<p class="av-kicker">' . esc_html( $l['season'] ) . '</p>' : '',
		esc_html( get_the_title( $id ) ),
		has_excerpt( $id ) ? '<p class="av-lhero__lead">' . esc_html( get_the_excerpt( $id ) ) . '</p>' : '',
		$dl ? '<p class="av-lhero__deadline"><strong>' . esc_html( $dl ) . '</strong> · ' . esc_html( $dl_sub ) . '</p>' : '',
		esc_url( $c['zalo_url'] ),
		esc_url( av_tel_href( $c['hotline'] ) ),
		esc_html( $c['hotline'] ),
		$img ? wp_get_attachment_image( $img, 'large', false, array( 'alt' => get_the_title( $id ), 'fetchpriority' => 'high', 'loading' => 'eager' ) ) : '<span class="av-ph">[Ảnh đại diện landing]</span>'
	);
}

function av_render_landing_deadline(): string {
	$id = av_current_landing_id();
	if ( ! $id ) {
		return '';
	}
	[ $dl, $sub ] = av_landing_deadline_text( av_landing( $id ) );
	if ( ! $dl ) {
		return current_user_can( 'edit_post', $id ) ? '<p class="av-land-hint">[Chưa nhập hạn chót đặt hàng: hộp "Landing theo mùa" trong trang sửa]</p>' : '';
	}
	return sprintf( '<div class="av-ldeadline"><strong>%s</strong><span>%s</span><a class="av-btn" href="#bao-gia">Đặt in ngay</a></div>', esc_html( $dl ), esc_html( $sub ) );
}

function av_render_landing_products(): string {
	$id = av_current_landing_id();
	$ps = $id ? av_landing_products( $id ) : array();
	if ( ! $ps ) {
		return ( $id && current_user_can( 'edit_post', $id ) ) ? '<p class="av-land-hint">[Chưa chọn sản phẩm cho landing]</p>' : '';
	}
	return av_product_cards( $ps );
}

function av_render_landing_prices(): string {
	$id = av_current_landing_id();
	$ps = $id ? av_landing_products( $id ) : array();
	if ( ! $ps ) {
		return '';
	}
	$c   = av_contact();
	$out = '<div class="av-lprices">';
	foreach ( $ps as $p ) {
		$out .= '<section class="av-lprice"><h3><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( $p->post_title ) . '</a></h3>';
		if ( ! av_product_has_prices( $p->ID ) ) {
			$out .= sprintf( '<p class="av-lprice__contact"><strong>Liên hệ báo giá.</strong> Giá theo khổ, giấy, số tờ và số lượng. Gọi <a href="%s" data-av-event="bam_goi">%s</a> hoặc gửi form bên dưới.</p>', esc_url( av_tel_href( $c['hotline'] ) ), esc_html( $c['hotline'] ) );
		} else {
			$pr   = av_product_prices( $p->ID );
			$out .= '<div class="av-prices__scroll"><table class="av-prices"><thead><tr><th scope="col">Quy cách</th>';
			foreach ( $pr['tiers'] as $q ) {
				$out .= '<th scope="col">' . esc_html( number_format( $q, 0, ',', '.' ) . ' ' . $pr['unit'] ) . '</th>';
			}
			$out .= '</tr></thead><tbody>';
			foreach ( $pr['rows'] as $r ) {
				$out .= '<tr><th scope="row">' . esc_html( $r['label'] ) . '</th>';
				foreach ( $r['prices'] as $v ) {
					$out .= '<td>' . esc_html( null === $v ? 'Liên hệ' : av_format_vnd( $v ) ) . '</td>';
				}
				$out .= '</tr>';
			}
			$out .= '</tbody></table></div>';
		}
		$out .= '</section>';
	}
	return $out . '<p class="av-prices__note">Giá tham khảo. Âu Việt báo giá chính xác sau khi xem mẫu, số lượng và thời gian cần hàng.</p></div>';
}

function av_render_landing_samples(): string {
	$id = av_current_landing_id();
	if ( ! $id ) {
		return '';
	}
	$items = array();
	foreach ( av_landing_products( $id ) as $p ) {
		foreach ( array_slice( av_page_images( $p->ID ), 0, 4 ) as $img ) {
			$items[ $img ] = $p;
		}
	}
	if ( ! $items ) {
		return '';
	}
	$out = '<ul class="av-dgallery av-lsamples">';
	foreach ( array_slice( $items, 0, 12, true ) as $img => $p ) {
		$alt  = trim( (string) get_post_meta( $img, '_wp_attachment_image_alt', true ) ) ?: $p->post_title;
		$out .= sprintf( '<li><a href="%s">%s<span>%s</span></a></li>', esc_url( get_permalink( $p ) ), wp_get_attachment_image( $img, 'medium_large', false, array( 'alt' => $alt, 'loading' => 'lazy' ) ), esc_html( $p->post_title ) );
	}
	return $out . '</ul>';
}

function av_render_landing_faq(): string {
	$id = av_current_landing_id();
	if ( ! $id ) {
		return '';
	}
	$faqs = av_faq_posts( av_landing( $id )['faqs'] );
	if ( ! $faqs ) {
		return current_user_can( 'edit_post', $id ) ? '<p class="av-land-hint">[Chưa chọn câu hỏi thường gặp. Thêm ở Hỏi đáp, rồi chọn trong hộp "Landing theo mùa"]</p>' : '';
	}
	return av_render_faq_list( $faqs );
}

function av_render_landing_quote( array $attrs = array() ): string {
	$id = av_current_landing_id();
	if ( ! $id ) {
		return '';
	}
	$ps    = av_landing_products( $id );
	$first = $ps ? $ps[0] : null;
	$group = $first ? av_product_primary_group( $first->ID ) : null;
	return sprintf(
		'<section class="av-pquote" id="bao-gia" aria-labelledby="av-lq-title"><div class="av-pquote__text"><h2 id="av-lq-title">%1$s</h2><p>%4$s</p>%2$s</div><div class="av-pquote__form" id="lien-he">%3$s</div></section>',
		esc_html( trim( (string) ( $attrs['title'] ?? '' ) ) ?: 'Nhận báo giá ' . mb_strtolower( get_the_title( $id ) ) ),
		av_render_contact_tiles(),
		av_render_quote_form(
			array(
				'need'    => $group ? av_group_root( $group )->name : '',
				'product' => $first ? $first->ID : 0,
			)
		),
		esc_html( trim( (string) ( $attrs['text'] ?? '' ) ) ?: 'Gửi số lượng, kiểu dáng và logo, Âu Việt gọi lại tư vấn mẫu, chất liệu và báo giá. Đặt sớm để giữ lịch in, kịp ngày cần hàng.' )
	);
}

/* ------------------------------------------------------------------ */
/* Quản trị: hộp "Landing theo mùa" trong trang sửa                    */
/* ------------------------------------------------------------------ */

add_action(
	'add_meta_boxes_page',
	static function ( WP_Post $post ) {
		if ( av_is_landing( $post->ID ) ) {
			add_meta_box( 'av_landing_box', 'Landing theo mùa', 'av_box_landing', 'page', 'normal', 'high' );
		}
	}
);

function av_box_landing( WP_Post $post ): void {
	wp_nonce_field( 'av_landing', 'av_landing_nonce' );
	$l      = av_landing( $post->ID );
	$state  = av_landing_state( $post->ID );
	$labels = array( 'live' => 'Đang hiện với khách', 'before' => 'Chưa tới giờ hiện', 'after' => 'Đã hết thời gian chạy', 'draft' => 'Bản nháp' );
	$toinp  = static fn( string $v ) => $v ? str_replace( ' ', 'T', $v ) : '';
	$faqs   = get_posts( array( 'post_type' => 'av_faq', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	$groups = get_terms( array( 'taxonomy' => 'av_group', 'hide_empty' => false, 'parent' => 0 ) );
	?>
	<p class="av-land-state av-land-state--<?php echo esc_attr( $state ); ?>"><strong><?php echo esc_html( $labels[ $state ] ); ?>.</strong> Địa chỉ: <code><?php echo esc_html( (string) get_permalink( $post ) ); ?></code></p>
	<div class="av-land-grid">
		<p><label for="av-land-season"><strong>Tên mùa</strong> (dòng nhỏ trên tiêu đề)</label><input type="text" id="av-land-season" name="av_land[season]" value="<?php echo esc_attr( $l['season'] ); ?>" placeholder="VD: Mùa in lịch Tết 2027" class="widefat"></p>
		<p><label for="av-land-deadline"><strong>Hạn chót đặt hàng</strong></label><input type="date" id="av-land-deadline" name="av_land[deadline]" value="<?php echo esc_attr( $l['deadline'] ); ?>" class="widefat"></p>
		<p><label for="av-land-from"><strong>Tự hiện từ</strong> (để trống: hiện ngay khi đăng)</label><input type="datetime-local" id="av-land-from" name="av_land[from]" value="<?php echo esc_attr( $toinp( $l['from'] ) ); ?>" class="widefat"></p>
		<p><label for="av-land-to"><strong>Tự ẩn sau</strong> (để trống: không tự ẩn)</label><input type="datetime-local" id="av-land-to" name="av_land[to]" value="<?php echo esc_attr( $toinp( $l['to'] ) ); ?>" class="widefat"></p>
		<p><label for="av-land-group"><strong>Khi ẩn, chuyển khách tới nhóm</strong></label>
			<select id="av-land-group" name="av_land[group]" class="widefat">
				<option value="0">— Nhóm của sản phẩm đầu tiên —</option>
				<?php foreach ( is_wp_error( $groups ) ? array() : $groups as $g ) : ?>
					<option value="<?php echo (int) $g->term_id; ?>"<?php selected( $l['group'], $g->term_id ); ?>><?php echo esc_html( $g->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>
	<fieldset class="av-land-pick">
		<legend><strong>Sản phẩm của landing</strong> (sản phẩm đầu tiên được chọn sẵn trong form báo giá)</legend>
		<div class="av-checklist" data-av-filter="Tìm sản phẩm…">
			<?php
			$all = get_posts( array( 'post_type' => 'av_product', 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
			$all = array_merge(
				array_values( array_filter( $all, static fn( $p ) => in_array( $p->ID, $l['products'], true ) ) ),
				array_values( array_filter( $all, static fn( $p ) => ! in_array( $p->ID, $l['products'], true ) ) )
			);
			foreach ( $all as $p ) {
				printf( '<label><input type="checkbox" name="av_land[products][]" value="%d"%s> %s</label>', (int) $p->ID, checked( in_array( $p->ID, $l['products'], true ), true, false ), esc_html( $p->post_title ) );
			}
			?>
		</div>
	</fieldset>
	<fieldset class="av-land-pick">
		<legend><strong>Câu hỏi thường gặp</strong></legend>
		<?php if ( ! $faqs ) : ?>
			<p class="description">Chưa có câu hỏi. <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=av_faq' ) ); ?>" target="_blank">Thêm câu hỏi</a> rồi quay lại chọn.</p>
		<?php else : ?>
			<div class="av-checklist" data-av-filter="Tìm câu hỏi…">
				<?php foreach ( $faqs as $f ) : ?>
					<label><input type="checkbox" name="av_land[faqs][]" value="<?php echo (int) $f->ID; ?>"<?php checked( in_array( $f->ID, $l['faqs'], true ) ); ?>> <?php echo esc_html( $f->post_title ); ?></label>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</fieldset>
	<?php if ( $l['replaces'] && get_post( $l['replaces'] ) ) : ?>
		<p class="av-land-state av-land-state--before">Đây là bản cho mùa mới của <a href="<?php echo esc_url( (string) get_edit_post_link( $l['replaces'] ) ); ?>"><?php echo esc_html( get_the_title( $l['replaces'] ) ); ?></a>. Khi bấm Đăng, bản cũ được cất (riêng tư) và bản này giữ đúng địa chỉ <code><?php echo esc_html( (string) get_permalink( $l['replaces'] ) ); ?></code>.</p>
	<?php endif; ?>
	<p class="description">Sắp xếp, thêm, bỏ các phần của trang trong khung soạn phía trên (các khối "Landing: …" ở nhóm Âu Việt). Ảnh đại diện của trang là ảnh lớn ở đầu landing.</p>
	<?php
}

add_action(
	'save_post_page',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['av_landing_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_landing_nonce'] ), 'av_landing' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		$in   = (array) wp_unslash( $_POST['av_land'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$time = static function ( $v ): string {
			$v = trim( sanitize_text_field( (string) $v ) );
			return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $v ) ? substr( str_replace( 'T', ' ', $v ), 0, 16 ) : '';
		};
		update_post_meta( $post_id, '_av_land_season', sanitize_text_field( (string) ( $in['season'] ?? '' ) ) );
		update_post_meta( $post_id, '_av_land_from', $time( $in['from'] ?? '' ) );
		update_post_meta( $post_id, '_av_land_to', $time( $in['to'] ?? '' ) );
		$dl = sanitize_text_field( (string) ( $in['deadline'] ?? '' ) );
		update_post_meta( $post_id, '_av_land_deadline', preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dl ) ? $dl : '' );
		update_post_meta( $post_id, '_av_land_group', absint( $in['group'] ?? 0 ) );
		update_post_meta( $post_id, '_av_land_products', array_values( array_filter( array_map( 'absint', (array) ( $in['products'] ?? array() ) ), static fn( $id ) => 'av_product' === get_post_type( $id ) ) ) );
		update_post_meta( $post_id, '_av_land_faqs', array_values( array_filter( array_map( 'absint', (array) ( $in['faqs'] ?? array() ) ) ) ) );
		if ( function_exists( 'av_purge_page_cache' ) ) {
			av_purge_page_cache();
		}
	}
);

// Bản mùa mới được đăng: cất bản cũ, bản mới nhận đúng địa chỉ cũ.
add_action(
	'transition_post_status',
	static function ( string $new, string $old, WP_Post $post ) {
		if ( 'publish' !== $new || 'publish' === $old || 'page' !== $post->post_type || ! av_is_landing( $post->ID ) ) {
			return;
		}
		$prev = (int) get_post_meta( $post->ID, '_av_land_replaces', true );
		$p    = $prev ? get_post( $prev ) : null;
		if ( ! $p || 'page' !== $p->post_type ) {
			return;
		}
		$slug   = $p->post_name;
		$parent = (int) $p->post_parent;
		$year   = get_post_time( 'Y', false, $p );
		wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'private', 'post_name' => $slug . '-' . $year ) );
		delete_post_meta( $post->ID, '_av_land_replaces' );
		wp_update_post( array( 'ID' => $post->ID, 'post_name' => $slug, 'post_parent' => $parent ) );
		av_log( 'Landing mùa mới "{title}" thay bản cũ tại {url}', array( 'title' => $post->post_title, 'url' => '/' . $slug . '/' ), 'notice' );
	},
	10,
	3
);

/* ------------------------------------------------------------------ */
/* Âu Việt › Landing theo mùa: danh sách, tạo mới, nhân bản            */
/* ------------------------------------------------------------------ */

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'auviet', 'Landing theo mùa', 'Landing theo mùa', 'edit_pages', 'auviet-landing', 'av_render_landing_admin' );
	},
	17
);

function av_render_landing_admin(): void {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$labels = array( 'live' => array( 'Đang hiện', 'live' ), 'before' => array( 'Chờ tới giờ hiện', 'future' ), 'after' => array( 'Đã ẩn', 'draft' ), 'draft' => array( 'Bản nháp', 'draft' ) );
	$msg    = isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap av-land-admin">
		<h1 class="wp-heading-inline">Landing theo mùa</h1>
		<a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=av_landing_new' ), 'av_landing_new' ) ); ?>">Tạo landing mới</a>
		<hr class="wp-header-end">
		<?php if ( function_exists( 'av_render_landing_seed_box' ) ) { av_render_landing_seed_box(); } ?>
		<?php if ( 'copied' === $msg ) : ?>
			<div class="notice notice-success"><p>Đã nhân bản thành bản nháp cho mùa sau. Sửa nội dung, ngày, rồi bấm Đăng: bản mới sẽ giữ đúng địa chỉ cũ.</p></div>
		<?php endif; ?>
		<p>Mỗi chiến dịch (lịch Tết, lì xì, Trung thu, quà tặng doanh nghiệp) là một trang trên web chính. Đặt "Tự hiện từ", "Tự ẩn sau" trong trang sửa; ngoài thời gian đó khách được chuyển tới nhóm sản phẩm. Mùa sau bấm <strong>Nhân bản cho mùa sau</strong>.</p>
		<table class="widefat striped">
			<thead><tr><th>Landing</th><th>Địa chỉ</th><th>Trạng thái</th><th>Thời gian chạy</th><th>Hạn chót đặt</th><th></th></tr></thead>
			<tbody>
			<?php
			$list = av_landings();
			if ( ! $list ) {
				echo '<tr><td colspan="6">Chưa có landing nào.</td></tr>';
			}
			foreach ( $list as $p ) {
				$l  = av_landing( $p->ID );
				$st = 'private' === $p->post_status ? array( 'Đã cất (mùa trước)', 'draft' ) : $labels[ av_landing_state( $p->ID ) ];
				printf(
					'<tr><td><strong><a href="%1$s">%2$s</a></strong>%3$s</td><td><a href="%4$s" target="_blank">%5$s</a></td><td><span class="av-pill av-pill--%6$s">%7$s</span></td><td>%8$s</td><td>%9$s</td><td>%10$s</td></tr>',
					esc_url( (string) get_edit_post_link( $p->ID ) ),
					esc_html( $p->post_title ),
					$l['season'] ? '<br><span class="description">' . esc_html( $l['season'] ) . '</span>' : '',
					esc_url( (string) get_permalink( $p ) ),
					esc_html( wp_make_link_relative( (string) get_permalink( $p ) ) ),
					esc_attr( $st[1] ),
					esc_html( $st[0] ),
					esc_html( ( av_date_vn( $l['from'] ) ?: 'Khi đăng' ) . ' → ' . ( av_date_vn( $l['to'] ) ?: 'không tự ẩn' ) ),
					esc_html( $l['deadline'] ? av_date_vn( $l['deadline'] . ' 00:00', false ) : '—' ),
					'private' === $p->post_status ? '' : sprintf( '<a class="button" href="%s">Nhân bản cho mùa sau</a>', esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=av_landing_copy&id=' . $p->ID ), 'av_landing_copy_' . $p->ID ) ) )
				);
			}
			?>
			</tbody>
		</table>
	</div>
	<?php
}

add_action(
	'admin_post_av_landing_new',
	static function () {
		if ( ! current_user_can( 'publish_pages' ) && ! current_user_can( 'edit_pages' ) ) {
			wp_die( 'Không có quyền.' );
		}
		check_admin_referer( 'av_landing_new' );
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_title'   => 'Landing mới',
				'post_content' => function_exists( 'auviet_page_from_pattern' ) ? auviet_page_from_pattern( 'auviet/landing-page' ) : '',
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			wp_die( 'Không tạo được trang.' );
		}
		update_post_meta( $id, '_av_landing', '1' );
		av_log( 'Tạo landing mới (bản nháp)' );
		wp_safe_redirect( (string) get_edit_post_link( $id, 'raw' ) );
		exit;
	}
);

add_action(
	'admin_post_av_landing_copy',
	static function () {
		$src = absint( $_GET['id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		check_admin_referer( 'av_landing_copy_' . $src );
		if ( ! av_is_landing( $src ) || ! current_user_can( 'edit_post', $src ) ) {
			wp_die( 'Không có quyền.' );
		}
		$p  = get_post( $src );
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_title'   => $p->post_title . ' (mùa mới)',
				'post_excerpt' => $p->post_excerpt,
				'post_content' => $p->post_content,
				'post_parent'  => $p->post_parent,
				'menu_order'   => $p->menu_order,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			wp_die( 'Không nhân bản được trang.' );
		}
		foreach ( array( '_av_landing', '_av_land_season', '_av_land_products', '_av_land_group', '_av_land_faqs', '_thumbnail_id' ) as $k ) {
			$v = get_post_meta( $src, $k, true );
			if ( '' !== $v && array() !== $v ) {
				update_post_meta( $id, $k, $v );
			}
		}
		update_post_meta( $id, '_av_land_replaces', $src );
		av_log( 'Nhân bản landing "{title}" cho mùa sau', array( 'title' => $p->post_title ) );
		wp_safe_redirect( (string) get_edit_post_link( $id, 'raw' ) );
		exit;
	}
);

// Cột "Landing" trong danh sách Trang.
add_filter(
	'display_post_states',
	static function ( array $states, WP_Post $post ): array {
		if ( av_is_landing( $post->ID ) ) {
			$map               = array( 'live' => 'Landing đang hiện', 'before' => 'Landing chờ giờ hiện', 'after' => 'Landing đã ẩn', 'draft' => 'Landing' );
			$states['av_land'] = 'private' === $post->post_status ? 'Landing mùa trước' : $map[ av_landing_state( $post->ID ) ];
		}
		return $states;
	},
	10,
	2
);

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ) {
		global $post;
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $post instanceof WP_Post && av_is_landing( $post->ID ) ) {
			wp_enqueue_script( 'auviet-checklist-filter', plugins_url( 'assets/js/checklist-filter.js', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/js/checklist-filter.js' ), true );
		}
	}
);
