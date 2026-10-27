<?php
/**
 * Trang sản phẩm phía khách: danh mục (SP-01), chi tiết (SP-02), thư viện chất liệu (SP-05).
 * Các khối động dưới đây được ghép trong template của theme (single-av_product, taxonomy-av_group…).
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'av_block_definitions',
	static function ( array $defs ): array {
		return $defs + array(
			'auviet/breadcrumb'        => array( 'title' => 'Đường dẫn trang (breadcrumb)', 'icon' => 'arrow-right-alt2', 'render' => 'av_render_breadcrumb', 'description' => 'Trang chủ › Sản phẩm › Nhóm › Trang hiện tại.' ),
			'auviet/catalog-header'    => array( 'title' => 'Đầu trang danh mục', 'icon' => 'category', 'render' => 'av_render_catalog_header', 'description' => 'Tên nhóm, đoạn giới thiệu, nhóm con.' ),
			'auviet/catalog-filters'   => array( 'title' => 'Bộ lọc sản phẩm', 'icon' => 'filter', 'render' => 'av_render_catalog_filters', 'description' => 'Lọc theo nhóm, chất liệu, ngành; sắp xếp.' ),
			'auviet/catalog-faq'       => array( 'title' => 'Hỏi đáp của nhóm', 'icon' => 'editor-help', 'render' => 'av_render_catalog_faq', 'description' => 'Câu hỏi thường gặp chọn trong Nhóm sản phẩm.' ),
			'auviet/price-badge'       => array( 'title' => 'Giá trên thẻ sản phẩm', 'icon' => 'tag', 'render' => 'av_render_price_badge', 'description' => '"Giá từ …" hoặc "Liên hệ báo giá".', 'context' => array( 'postId', 'postType' ) ),
			'auviet/product-gallery'   => array( 'title' => 'Ảnh sản phẩm', 'icon' => 'format-gallery', 'render' => 'av_render_product_gallery', 'description' => 'Ảnh lớn, ảnh nhỏ, bấm để phóng to.' ),
			'auviet/product-summary'   => array( 'title' => 'Tóm tắt sản phẩm', 'icon' => 'info', 'render' => 'av_render_product_summary', 'description' => 'Mô tả ngắn, số lượng tối thiểu, thời gian, giá từ, nút liên hệ.' ),
			'auviet/product-specs'     => array( 'title' => 'Bảng quy cách', 'icon' => 'editor-table', 'render' => 'av_render_product_specs', 'description' => 'Khổ, giấy, định lượng, số màu, số mặt, gia công.' ),
			'auviet/product-prices'    => array( 'title' => 'Bảng giá tham khảo', 'icon' => 'money-alt', 'render' => 'av_render_product_prices', 'description' => 'Giá theo bậc số lượng, hoặc "Liên hệ báo giá".' ),
			'auviet/product-quote'     => array( 'title' => 'Liên hệ báo giá sản phẩm', 'icon' => 'feedback', 'render' => 'av_render_product_quote', 'description' => 'Bốn nút liên hệ và form chọn sẵn sản phẩm.' ),
			'auviet/product-faq'       => array( 'title' => 'Hỏi đáp của sản phẩm', 'icon' => 'editor-help', 'render' => 'av_render_product_faq', 'description' => 'Câu hỏi thường gặp chọn trong sản phẩm.' ),
			'auviet/related-products'  => array( 'title' => 'Sản phẩm liên quan', 'icon' => 'screenoptions', 'render' => 'av_render_related_products', 'description' => 'Chọn tay hoặc tự lấy cùng nhóm.' ),
			'auviet/related-posts'     => array( 'title' => 'Bài viết liên quan', 'icon' => 'media-text', 'render' => 'av_render_related_posts', 'description' => 'Bài trong Kiến thức in ấn.' ),
			'auviet/material-index'    => array( 'title' => 'Danh sách chất liệu', 'icon' => 'art', 'render' => 'av_render_material_index', 'description' => 'Giấy và gia công, chia theo loại.' ),
			'auviet/material-products' => array( 'title' => 'Sản phẩm dùng chất liệu', 'icon' => 'products', 'render' => 'av_render_material_products', 'description' => 'Sản phẩm có gắn chất liệu đang xem.' ),
		);
	}
);

/* ------------------------------------------------------------------ */
/* Truy vấn danh mục: lọc, sắp xếp, phân trang (URL chia sẻ được)      */
/* ------------------------------------------------------------------ */

function av_catalog_sorts(): array {
	return array(
		'noi-bat'  => 'Nổi bật',
		'moi-nhat' => 'Mới nhất',
		'ten'      => 'Tên A–Z',
	);
}

/**
 * Giá trị lọc hiện tại, đã kiểm tra.
 */
function av_catalog_state(): array {
	// phpcs:disable WordPress.Security.NonceVerification -- bộ lọc công khai, chỉ đọc.
	$mat  = isset( $_GET['chat-lieu'] ) ? sanitize_title( wp_unslash( $_GET['chat-lieu'] ) ) : '';
	$ind  = isset( $_GET['nganh'] ) ? sanitize_title( wp_unslash( $_GET['nganh'] ) ) : '';
	$sort = isset( $_GET['sap-xep'] ) ? sanitize_key( wp_unslash( $_GET['sap-xep'] ) ) : 'noi-bat';
	// phpcs:enable
	$material = $mat ? get_page_by_path( $mat, OBJECT, 'av_material' ) : null;
	$industry = $ind ? get_term_by( 'slug', $ind, 'av_industry' ) : false;
	return array(
		'material' => $material instanceof WP_Post ? $material : null,
		'industry' => $industry instanceof WP_Term ? $industry : null,
		'sort'     => isset( av_catalog_sorts()[ $sort ] ) ? $sort : 'noi-bat',
	);
}

function av_is_catalog(): bool {
	return is_post_type_archive( 'av_product' ) || is_tax( 'av_group' );
}

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( $q->is_post_type_archive( 'av_material' ) ) {
			$q->set( 'posts_per_page', -1 );
			return;
		}
		if ( ! ( $q->is_post_type_archive( 'av_product' ) || $q->is_tax( 'av_group' ) ) ) {
			return;
		}
		$q->set( 'posts_per_page', 12 );
		$state = av_catalog_state();
		if ( $state['material'] ) {
			$q->set( 'meta_query', array( array( 'key' => '_av_material', 'value' => (string) $state['material']->ID ) ) );
		}
		if ( $state['industry'] ) {
			$tax   = (array) $q->get( 'tax_query' );
			$tax[] = array(
				'taxonomy' => 'av_industry',
				'terms'    => $state['industry']->term_id,
			);
			$q->set( 'tax_query', $tax );
		}
		switch ( $state['sort'] ) {
			case 'moi-nhat':
				$q->set( 'orderby', 'date' );
				$q->set( 'order', 'DESC' );
				break;
			case 'ten':
				$q->set( 'orderby', 'title' );
				$q->set( 'order', 'ASC' );
				break;
			default:
				$q->set(
					'orderby',
					array(
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					)
				);
		}
	}
);

/* ------------------------------------------------------------------ */
/* Tài nguyên phía khách                                               */
/* ------------------------------------------------------------------ */

add_action(
	'enqueue_block_assets',
	static function () {
		wp_enqueue_style( 'auviet-products', plugins_url( 'assets/css/products.css', AV_CORE_FILE ), array(), (string) filemtime( AV_CORE_DIR . 'assets/css/products.css' ) );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_singular( 'av_product' ) || av_is_catalog() ) {
			wp_enqueue_script( 'auviet-product', plugins_url( 'assets/js/product.js', AV_CORE_FILE ), array(), (string) filemtime( AV_CORE_DIR . 'assets/js/product.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}
	}
);

/* ------------------------------------------------------------------ */
/* Breadcrumb                                                          */
/* ------------------------------------------------------------------ */

function av_term_trail( WP_Term $term ): array {
	$items = array();
	foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $aid ) {
		$a = get_term( $aid, $term->taxonomy );
		if ( $a instanceof WP_Term ) {
			$items[] = array( $a->name, get_term_link( $a ) );
		}
	}
	return $items;
}

function av_render_breadcrumb(): string {
	$items    = array( array( 'Trang chủ', home_url( '/' ) ) );
	$products = array( 'Sản phẩm', get_post_type_archive_link( 'av_product' ) );
	$current  = '';
	if ( is_singular( 'av_product' ) ) {
		$items[] = $products;
		$group   = av_product_primary_group( get_queried_object_id() );
		if ( $group ) {
			$items   = array_merge( $items, av_term_trail( $group ) );
			$items[] = array( $group->name, get_term_link( $group ) );
		}
		$current = get_the_title( get_queried_object_id() );
	} elseif ( is_tax( 'av_group' ) ) {
		$term    = get_queried_object();
		$items[] = $products;
		$items   = array_merge( $items, av_term_trail( $term ) );
		$current = $term->name;
	} elseif ( is_post_type_archive( 'av_product' ) ) {
		$current = 'Sản phẩm';
	} elseif ( is_singular( 'av_material' ) ) {
		$items[] = array( 'Thư viện chất liệu', get_post_type_archive_link( 'av_material' ) );
		$current = get_the_title( get_queried_object_id() );
	} elseif ( is_post_type_archive( 'av_material' ) ) {
		$current = 'Thư viện chất liệu';
	} else {
		return '';
	}
	$out = '<nav class="av-crumbs" aria-label="Đường dẫn"><ol>';
	foreach ( $items as $it ) {
		$out .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $it[1] ), esc_html( $it[0] ) );
	}
	$out .= sprintf( '<li aria-current="page">%s</li>', esc_html( $current ) );
	return $out . '</ol></nav>';
}

/* ------------------------------------------------------------------ */
/* Danh mục                                                            */
/* ------------------------------------------------------------------ */

function av_render_catalog_header(): string {
	if ( is_tax( 'av_group' ) ) {
		$term     = get_queried_object();
		$title    = $term->name;
		$intro    = av_group_intro( $term ) ?: ( $term->description ? '<p>' . esc_html( $term->description ) . '</p>' : '' );
		$children = av_visible_groups( $term->term_id );
	} else {
		$title    = 'Sản phẩm in ấn';
		$intro    = '<p>Âu Việt nhận in ấn phẩm văn phòng, ấn phẩm marketing, bao bì, quà tặng theo mùa và in khổ lớn cho sự kiện. Chọn nhóm sản phẩm hoặc lọc theo chất liệu để xem quy cách và giá tham khảo; cần tư vấn nhanh, gọi hoặc nhắn Zalo cho Âu Việt.</p>';
		$children = array();
	}
	$out  = '<header class="av-cat-head">';
	$out .= '<h1 class="av-cat-head__title">' . esc_html( $title ) . '</h1>';
	if ( $intro ) {
		$out .= '<div class="av-cat-head__intro">' . wp_kses_post( wpautop( $intro ) ) . '</div>';
	}
	if ( $children ) {
		$out .= '<ul class="av-chips" aria-label="Nhóm con">';
		foreach ( $children as $c ) {
			$out .= sprintf( '<li><a class="av-chip" href="%s">%s <span>%d</span></a></li>', esc_url( get_term_link( $c ) ), esc_html( $c->name ), (int) $c->count );
		}
		$out .= '</ul>';
	}
	return $out . '</header>';
}

/**
 * Chất liệu được dùng bởi các sản phẩm trong phạm vi hiện tại (để bộ lọc không có lựa chọn rỗng).
 *
 * @return WP_Post[]
 */
function av_catalog_materials(): array {
	$args = array(
		'post_type'   => 'av_product',
		'numberposts' => -1,
		'fields'      => 'ids',
	);
	if ( is_tax( 'av_group' ) ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'av_group', 'terms' => get_queried_object_id() ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$ids = array();
	foreach ( get_posts( $args ) as $pid ) {
		foreach ( (array) get_post_meta( $pid, '_av_material' ) as $m ) {
			$ids[ (int) $m ] = true;
		}
	}
	if ( ! $ids ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'   => 'av_material',
			'post__in'    => array_keys( $ids ),
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
		)
	);
}

function av_render_catalog_filters(): string {
	if ( ! av_is_catalog() ) {
		return '';
	}
	$state   = av_catalog_state();
	$current = is_tax( 'av_group' ) ? get_queried_object() : null;
	$base    = $current ? get_term_link( $current ) : get_post_type_archive_link( 'av_product' );
	$keep    = array_filter(
		array(
			'chat-lieu' => $state['material'] ? $state['material']->post_name : '',
			'nganh'     => $state['industry'] ? $state['industry']->slug : '',
			'sap-xep'   => 'noi-bat' !== $state['sort'] ? $state['sort'] : '',
		)
	);

	// Nhóm cấp 1: chuyển nhóm giữ nguyên cách sắp xếp, bỏ lọc chất liệu (chất liệu khác nhau giữa các nhóm).
	$root  = $current ? av_group_root( $current ) : null;
	$chips = sprintf( '<li><a class="av-chip%s" href="%s">Tất cả</a></li>', $current ? '' : ' is-active', esc_url( add_query_arg( array_intersect_key( $keep, array( 'sap-xep' => 1 ) ), get_post_type_archive_link( 'av_product' ) ) ) );
	foreach ( av_visible_groups() as $g ) {
		$chips .= sprintf(
			'<li><a class="av-chip%s" href="%s"%s>%s</a></li>',
			$root && $root->term_id === $g->term_id ? ' is-active' : '',
			esc_url( add_query_arg( array_intersect_key( $keep, array( 'sap-xep' => 1 ) ), get_term_link( $g ) ) ),
			$root && $root->term_id === $g->term_id ? ' aria-current="page"' : '',
			esc_html( $g->name )
		);
	}

	$mat_opts = '<option value="">Mọi chất liệu</option>';
	foreach ( av_catalog_materials() as $m ) {
		$mat_opts .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $m->post_name ), selected( $state['material'] && $state['material']->ID === $m->ID, true, false ), esc_html( $m->post_title ) );
	}
	$industries = get_terms( array( 'taxonomy' => 'av_industry', 'hide_empty' => true, 'object_ids' => get_posts( array( 'post_type' => 'av_product', 'numberposts' => -1, 'fields' => 'ids' ) ) ?: array( 0 ) ) );
	$ind_html   = '';
	if ( $industries && ! is_wp_error( $industries ) ) {
		$ind_opts = '<option value="">Mọi ngành</option>';
		foreach ( $industries as $t ) {
			$ind_opts .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $t->slug ), selected( $state['industry'] && $state['industry']->term_id === $t->term_id, true, false ), esc_html( $t->name ) );
		}
		$ind_html = '<label class="av-filter"><span>Ngành</span><select name="nganh" data-av-autosubmit>' . $ind_opts . '</select></label>';
	}
	$sort_opts = '';
	foreach ( av_catalog_sorts() as $k => $label ) {
		$sort_opts .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $state['sort'], $k, false ), esc_html( $label ) );
	}
	$clear = $keep ? sprintf( '<a class="av-filter__clear" href="%s">Bỏ lọc</a>', esc_url( $base ) ) : '';

	return sprintf(
		'<div class="av-filters"><ul class="av-chips" aria-label="Nhóm sản phẩm">%1$s</ul><form class="av-filters__form" method="get" action="%2$s" role="search" aria-label="Lọc sản phẩm"><label class="av-filter"><span>Chất liệu</span><select name="chat-lieu" data-av-autosubmit>%3$s</select></label>%4$s<label class="av-filter"><span>Sắp xếp</span><select name="sap-xep" data-av-autosubmit>%5$s</select></label><noscript><button class="av-btn" type="submit">Lọc</button></noscript>%6$s</form></div>',
		$chips,
		esc_url( $base ),
		$mat_opts,
		$ind_html,
		$sort_opts,
		$clear
	);
}

function av_render_faq_list( array $faqs, string $title = 'Câu hỏi thường gặp' ): string {
	if ( ! $faqs ) {
		return '';
	}
	$out = '<section class="av-faq" aria-labelledby="av-faq-title"><h2 id="av-faq-title">' . esc_html( $title ) . '</h2>';
	foreach ( $faqs as $f ) {
		$out .= sprintf( '<details class="av-faq__item"><summary>%s</summary><div class="av-faq__a">%s</div></details>', esc_html( $f->post_title ), wp_kses_post( apply_filters( 'the_content', $f->post_content ) ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}
	return $out . '</section>';
}

function av_render_catalog_faq(): string {
	if ( ! is_tax( 'av_group' ) ) {
		return '';
	}
	return av_render_faq_list( av_faq_posts( (array) get_term_meta( get_queried_object_id(), 'av_faqs', true ) ) );
}

function av_render_price_badge( array $attrs, string $content, WP_Block $block ): string {
	$id = (int) ( $block->context['postId'] ?? get_the_ID() );
	if ( ! $id || 'av_product' !== get_post_type( $id ) ) {
		return '';
	}
	$min   = av_product_min_price( $id );
	$group = av_product_primary_group( $id );
	return sprintf(
		'<p class="av-badge">%s<span class="av-badge__price%s">%s</span></p>',
		$group ? '<span class="av-badge__group">' . esc_html( $group->name ) . '</span>' : '',
		null === $min ? ' is-contact' : '',
		esc_html( null === $min ? 'Liên hệ báo giá' : 'Giá từ ' . av_format_vnd( $min ) )
	);
}

/* ------------------------------------------------------------------ */
/* Chi tiết sản phẩm                                                   */
/* ------------------------------------------------------------------ */

function av_current_product_id(): int {
	$id = get_queried_object_id();
	return ( $id && 'av_product' === get_post_type( $id ) ) ? $id : 0;
}

function av_render_product_gallery(): string {
	$id = av_current_product_id();
	if ( ! $id ) {
		return '';
	}
	$ids   = av_product_gallery( $id );
	$title = get_the_title( $id );
	if ( ! $ids ) {
		return '<div class="av-pg av-pg--empty"><div class="av-pg__main av-ph">[Chưa có ảnh sản phẩm]</div></div>';
	}
	$alt = static function ( int $aid, int $n ) use ( $title ): string {
		$a = trim( (string) get_post_meta( $aid, '_wp_attachment_image_alt', true ) );
		return $a ?: $title . ( $n > 1 ? ' – ảnh ' . $n : '' );
	};
	$first = $ids[0];
	$out   = '<div class="av-pg" data-av-gallery>';
	$out  .= sprintf(
		'<button type="button" class="av-pg__main" data-av-zoom aria-label="Phóng to ảnh">%s<span class="av-pg__zoom" aria-hidden="true">Phóng to</span></button>',
		wp_get_attachment_image( $first, 'large', false, array( 'alt' => $alt( $first, 1 ), 'data-av-main' => '1', 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '(max-width: 781px) 100vw, 600px' ) )
	);
	if ( count( $ids ) > 1 ) {
		$out .= '<ul class="av-pg__thumbs">';
		foreach ( $ids as $n => $aid ) {
			$full = wp_get_attachment_image_src( $aid, 'large' );
			$big  = wp_get_attachment_image_src( $aid, 'full' );
			$out .= sprintf(
				'<li><button type="button" class="av-pg__thumb%s" data-av-src="%s" data-av-srcset="%s" data-av-full="%s" data-av-alt="%s" aria-label="Xem ảnh %d"%s>%s</button></li>',
				0 === $n ? ' is-active' : '',
				esc_url( $full ? $full[0] : '' ),
				esc_attr( (string) wp_get_attachment_image_srcset( $aid, 'large' ) ),
				esc_url( $big ? $big[0] : '' ),
				esc_attr( $alt( $aid, $n + 1 ) ),
				$n + 1,
				0 === $n ? ' aria-current="true"' : '',
				wp_get_attachment_image( $aid, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) )
			);
		}
		$out .= '</ul>';
	} else {
		$big  = wp_get_attachment_image_src( $first, 'full' );
		$out .= sprintf( '<span hidden data-av-single data-av-full="%s" data-av-alt="%s"></span>', esc_url( $big ? $big[0] : '' ), esc_attr( $alt( $first, 1 ) ) );
	}
	$out .= '<dialog class="av-lightbox" aria-label="Ảnh sản phẩm"><img alt=""><button type="button" class="av-lightbox__prev" aria-label="Ảnh trước">‹</button><button type="button" class="av-lightbox__next" aria-label="Ảnh sau">›</button><button type="button" class="av-lightbox__close" aria-label="Đóng">×</button></dialog>';
	return $out . '</div>';
}

function av_render_product_summary(): string {
	$id = av_current_product_id();
	if ( ! $id ) {
		return '';
	}
	$c     = av_contact();
	$min   = av_product_min_price( $id );
	$moq   = av_product_moq( $id );
	$lead  = av_product_lead_time( $id );
	$facts = array(
		'Giá tham khảo'      => null === $min ? 'Liên hệ báo giá' : 'Từ ' . av_format_vnd( $min ),
		'Số lượng tối thiểu' => $moq ?: 'Liên hệ',
		'Thời gian sản xuất' => $lead ?: 'Liên hệ',
	);
	$out = '';
	if ( has_excerpt( $id ) ) {
		$out .= '<p class="av-ps__lead">' . esc_html( get_the_excerpt( $id ) ) . '</p>';
	}
	$out .= '<dl class="av-ps__facts">';
	foreach ( $facts as $k => $v ) {
		$out .= sprintf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html( $k ), esc_html( $v ) );
	}
	$out .= '</dl>';
	$out .= sprintf(
		'<div class="av-ps__cta"><a class="av-btn" href="#bao-gia">Nhận báo giá</a><a class="av-btn av-btn--outline" href="%1$s" data-av-event="bam_goi">Gọi %2$s</a><a class="av-btn av-btn--zalo" href="%3$s" target="_blank" rel="noopener" data-av-event="bam_zalo">Zalo</a></div>',
		esc_url( av_tel_href( $c['hotline'] ) ),
		esc_html( $c['hotline'] ),
		esc_url( $c['zalo_url'] )
	);
	return '<div class="av-ps">' . $out . '</div>';
}

function av_render_product_specs(): string {
	$id = av_current_product_id();
	if ( ! $id ) {
		return '';
	}
	$specs = av_product_specs( $id );
	$mats  = av_product_materials( $id );
	if ( ! $specs && ! $mats ) {
		return '';
	}
	$out = '<section class="av-psec" aria-labelledby="av-specs-title"><h2 id="av-specs-title">Quy cách</h2><table class="av-specs"><tbody>';
	foreach ( $specs as $k => $v ) {
		$out .= sprintf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html( $k ), esc_html( $v ) );
	}
	if ( $mats ) {
		$links = array_map( static fn( $m ) => sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $m ) ), esc_html( $m->post_title ) ), $mats );
		$out  .= '<tr><th scope="row">Chất liệu, gia công</th><td>' . implode( ', ', $links ) . '</td></tr>';
	}
	return $out . '</tbody></table></section>';
}

function av_render_product_prices(): string {
	$id = av_current_product_id();
	if ( ! $id ) {
		return '';
	}
	$p    = av_product_prices( $id );
	$c    = av_contact();
	$note = $p['note'] ?: 'Giá tham khảo. Âu Việt báo giá chính xác sau khi xem file, số lượng và thời gian cần hàng.';
	$out  = '<section class="av-psec" aria-labelledby="av-prices-title"><h2 id="av-prices-title">Giá tham khảo</h2>';
	if ( ! av_product_has_prices( $id ) ) {
		$out .= sprintf(
			'<div class="av-price-contact"><p><strong>Liên hệ báo giá.</strong> Giá phụ thuộc khổ, chất liệu, số lượng và gia công. Gửi yêu cầu hoặc gọi <a href="%s" data-av-event="bam_goi">%s</a>, Âu Việt báo giá trong giờ làm việc.</p><a class="av-btn" href="#bao-gia">Gửi yêu cầu báo giá</a></div>',
			esc_url( av_tel_href( $c['hotline'] ) ),
			esc_html( $c['hotline'] )
		);
		return $out . '</section>';
	}
	$head = 'unit' === $p['basis'] ? 'Đơn giá / ' . $p['unit'] : 'Thành tiền';
	$out .= '<div class="av-prices__scroll"><table class="av-prices"><caption>' . esc_html( $head . ' theo số lượng' ) . '</caption><thead><tr><th scope="col">Quy cách</th>';
	foreach ( $p['tiers'] as $q ) {
		$out .= '<th scope="col">' . esc_html( number_format( $q, 0, ',', '.' ) . ' ' . $p['unit'] ) . '</th>';
	}
	$out .= '</tr></thead><tbody>';
	foreach ( $p['rows'] as $r ) {
		$out .= '<tr><th scope="row">' . esc_html( $r['label'] ) . '</th>';
		foreach ( $r['prices'] as $v ) {
			$out .= '<td>' . esc_html( null === $v ? 'Liên hệ' : av_format_vnd( $v ) ) . '</td>';
		}
		$out .= '</tr>';
	}
	$out .= '</tbody></table></div><p class="av-prices__note">' . esc_html( $note ) . '</p>';
	return $out . '</section>';
}

function av_render_product_quote(): string {
	$id = av_current_product_id();
	if ( ! $id ) {
		return '';
	}
	$group = av_product_primary_group( $id );
	$need  = $group ? av_group_root( $group )->name : '';
	return sprintf(
		'<section class="av-pquote" id="bao-gia" aria-labelledby="av-quote-title"><div class="av-pquote__text"><h2 id="av-quote-title">Nhận báo giá %1$s</h2><p>Để lại số điện thoại, Âu Việt gọi lại tư vấn quy cách và báo giá. Hoặc liên hệ ngay:</p>%2$s</div><div class="av-pquote__form" id="lien-he">%3$s</div></section>',
		esc_html( mb_strtolower( get_the_title( $id ) ) ),
		av_render_contact_tiles(),
		av_render_quote_form(
			array(
				'need'    => $need,
				'product' => $id,
			)
		)
	);
}

function av_render_product_faq(): string {
	$id = av_current_product_id();
	return $id ? av_render_faq_list( av_faq_posts( (array) get_post_meta( $id, '_av_faqs', true ) ) ) : '';
}

/**
 * Thẻ sản phẩm dùng chung (liên quan, chất liệu).
 *
 * @param WP_Post[] $posts
 */
function av_product_cards( array $posts ): string {
	$out = '<ul class="av-pcards">';
	foreach ( $posts as $p ) {
		$thumb = get_the_post_thumbnail( $p, 'medium', array( 'alt' => '', 'loading' => 'lazy' ) );
		$min   = av_product_min_price( $p->ID );
		$out  .= sprintf(
			'<li class="av-pcard"><a href="%s">%s<strong>%s</strong><span class="av-badge__price%s">%s</span></a></li>',
			esc_url( get_permalink( $p ) ),
			$thumb ?: '<span class="av-ph av-pcard__ph">[Chưa có ảnh]</span>',
			esc_html( get_the_title( $p ) ),
			null === $min ? ' is-contact' : '',
			esc_html( null === $min ? 'Liên hệ báo giá' : 'Giá từ ' . av_format_vnd( $min ) )
		);
	}
	return $out . '</ul>';
}

function av_render_related_products(): string {
	$id = av_current_product_id();
	if ( ! $id ) {
		return '';
	}
	$posts = av_product_related( $id );
	return $posts ? '<section class="av-psec" aria-labelledby="av-rel-title"><h2 id="av-rel-title">Sản phẩm liên quan</h2>' . av_product_cards( $posts ) . '</section>' : '';
}

function av_render_related_posts(): string {
	$id = av_current_product_id();
	if ( ! $id ) {
		return '';
	}
	$posts = av_product_related_posts( $id );
	if ( ! $posts ) {
		return '';
	}
	$out = '<section class="av-psec" aria-labelledby="av-relp-title"><h2 id="av-relp-title">Bài viết liên quan</h2><ul class="av-plinks">';
	foreach ( $posts as $p ) {
		$out .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $p ) ), esc_html( get_the_title( $p ) ) );
	}
	return $out . '</ul></section>';
}

/* ------------------------------------------------------------------ */
/* Thư viện chất liệu                                                  */
/* ------------------------------------------------------------------ */

function av_render_material_index(): string {
	$out = '';
	foreach ( av_material_kinds() as $kind => $label ) {
		$mats = get_posts(
			array(
				'post_type'   => 'av_material',
				'numberposts' => -1,
				'orderby'     => 'title',
				'order'       => 'ASC',
				'meta_key'    => '_av_kind', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'  => $kind, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( ! $mats ) {
			continue;
		}
		$out .= '<section class="av-psec"><h2>' . esc_html( $label ) . '</h2><ul class="av-mats">';
		foreach ( $mats as $m ) {
			$used  = count( get_posts( array( 'post_type' => 'av_product', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_av_material', 'meta_value' => (string) $m->ID ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$out  .= sprintf(
				'<li class="av-mat"><a href="%s"><strong>%s</strong><span>%s</span><em>%s</em></a></li>',
				esc_url( get_permalink( $m ) ),
				esc_html( $m->post_title ),
				esc_html( wp_trim_words( wp_strip_all_tags( $m->post_excerpt ?: $m->post_content ), 22 ) ),
				esc_html( $used ? $used . ' sản phẩm dùng' : 'Đang cập nhật sản phẩm' )
			);
		}
		$out .= '</ul></section>';
	}
	return $out ?: '<p>Thư viện chất liệu đang được cập nhật.</p>';
}

function av_render_material_products(): string {
	$id = get_queried_object_id();
	if ( ! $id || 'av_material' !== get_post_type( $id ) ) {
		return '';
	}
	$posts = get_posts(
		array(
			'post_type'   => 'av_product',
			'numberposts' => -1,
			'meta_key'    => '_av_material', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => (string) $id, // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'     => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);
	if ( ! $posts ) {
		return '';
	}
	return '<section class="av-psec" aria-labelledby="av-matp-title"><h2 id="av-matp-title">Sản phẩm dùng ' . esc_html( mb_strtolower( get_the_title( $id ) ) ) . '</h2>' . av_product_cards( $posts ) . '</section>';
}
