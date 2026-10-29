<?php
/**
 * Mục 7 Tin tức (Kiến thức in ấn): TT-01 danh sách, chuyên mục; TT-02 chi tiết bài; TT-03 chủ đề; QT-05 quản trị bài.
 *
 * URL (mục 3 Tài liệu mô tả chức năng):
 * - /kien-thuc/                 danh sách bài (trang "Kiến thức in ấn", đặt làm trang bài viết)
 * - /kien-thuc/<chuyen-muc>/    chuyên mục
 * - /kien-thuc/<ten-bai>/       chi tiết bài
 * - /chu-de/<ten-chu-de>/       chủ đề (tag), chỉ hiện khi có từ 2 bài
 *
 * Chuyên mục và bài cùng nằm dưới /kien-thuc/: WordPress hiểu /kien-thuc/x/ là chuyên mục trước, nên bộ lọc
 * "request" bên dưới chuyển sang bài viết khi x không phải chuyên mục. Đường dẫn bài không được trùng chuyên mục.
 */

defined( 'ABSPATH' ) || exit;

const AV_BLOG_BASE = 'kien-thuc';

function av_blog_categories(): array {
	return array(
		'kien-thuc-in-an'    => array( 'Kiến thức in ấn', 'Chất liệu giấy, kỹ thuật in, gia công và kinh nghiệm đặt in để Quý khách chọn đúng ấn phẩm, đúng ngân sách.' ),
		'huong-dan-thiet-ke' => array( 'Hướng dẫn thiết kế', 'Cách chuẩn bị file, chọn bố cục, màu sắc và mẫu thiết kế đẹp cho bao bì, ấn phẩm.' ),
		'du-an'              => array( 'Dự án', 'Cận cảnh các đơn hàng Âu Việt đã thực hiện.' ),
		'tin-cong-ty'        => array( 'Tin công ty', 'Tin tức, ưu đãi và hoạt động của Âu Việt.' ),
	);
}

function av_blog_page_id(): int {
	$id = (int) get_option( 'page_for_posts' );
	return ( $id && 'page' === get_post_type( $id ) ) ? $id : 0;
}

function av_blog_url(): string {
	$id = av_blog_page_id();
	return $id ? (string) get_permalink( $id ) : home_url( '/' . AV_BLOG_BASE . '/' );
}

/* ------------------------------------------------------------------ */
/* Đường dẫn                                                           */
/* ------------------------------------------------------------------ */

add_action(
	'init',
	static function () {
		add_rewrite_rule( '^' . AV_BLOG_BASE . '/?$', 'index.php?pagename=' . AV_BLOG_BASE, 'top' );
		add_rewrite_rule( '^' . AV_BLOG_BASE . '/page/([0-9]{1,})/?$', 'index.php?pagename=' . AV_BLOG_BASE . '&paged=$matches[1]', 'top' );
	}
);

// /kien-thuc/x/ khớp quy tắc chuyên mục trước: không có chuyên mục x mà có bài x thì mở bài.
add_filter(
	'request',
	static function ( array $vars ): array {
		if ( empty( $vars['category_name'] ) || isset( $vars['name'] ) ) {
			return $vars;
		}
		$path = (string) $vars['category_name'];
		if ( str_contains( $path, '/' ) || get_category_by_path( $path, false ) ) {
			return $vars;
		}
		$post = get_page_by_path( $path, OBJECT, 'post' );
		if ( ! $post ) {
			return $vars;
		}
		unset( $vars['category_name'] );
		$vars['name'] = $path;
		unset( $vars['paged'] );
		return $vars;
	}
);

// Đường dẫn bài không trùng chuyên mục và không trùng "page" (phân trang).
add_filter(
	'wp_unique_post_slug',
	static function ( $slug, $post_id, $status, $type ) {
		if ( 'post' !== $type ) {
			return $slug;
		}
		$reserved = array_merge( array( 'page', 'feed', 'embed' ), get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'fields' => 'slugs' ) ) ?: array() );
		$base     = $slug;
		$n        = 2;
		while ( in_array( $slug, $reserved, true ) ) {
			$slug = $base . '-' . $n++;
		}
		return $slug;
	},
	10,
	4
);

// Tag hiển thị là "Chủ đề".
add_filter(
	'register_taxonomy_args',
	static function ( array $args, string $tax ): array {
		if ( 'post_tag' === $tax ) {
			$args['labels'] = array(
				'name'                       => 'Chủ đề',
				'singular_name'              => 'Chủ đề',
				'menu_name'                  => 'Chủ đề',
				'all_items'                  => 'Tất cả chủ đề',
				'edit_item'                  => 'Sửa chủ đề',
				'add_new_item'               => 'Thêm chủ đề',
				'search_items'               => 'Tìm chủ đề',
				'separate_items_with_commas' => 'Các chủ đề cách nhau bằng dấu phẩy. Chủ đề có từ 2 bài mới hiện ra ngoài.',
				'not_found'                  => 'Chưa có chủ đề',
			);
		}
		return $args;
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* Thiết lập một lần: đường dẫn bài, chuyên mục, trang Kiến thức in ấn */
/* ------------------------------------------------------------------ */

add_action(
	'init',
	static function () {
		if ( get_option( 'av_mig_blog_v1' ) ) {
			return;
		}
		update_option( 'av_mig_blog_v1', 1, false );

		if ( in_array( (string) get_option( 'permalink_structure' ), array( '', '/%postname%/' ), true ) ) {
			update_option( 'permalink_structure', '/' . AV_BLOG_BASE . '/%postname%/' );
		}
		update_option( 'category_base', AV_BLOG_BASE );
		update_option( 'tag_base', 'chu-de' );

		// Chuyên mục: đổi "Chưa phân loại" thành "Kiến thức in ấn", tạo các chuyên mục còn lại.
		$default = (int) get_option( 'default_category' );
		foreach ( av_blog_categories() as $slug => $c ) {
			$t = get_term_by( 'slug', $slug, 'category' );
			if ( $t ) {
				continue;
			}
			if ( 'kien-thuc-in-an' === $slug && $default && get_term( $default, 'category' ) ) {
				wp_update_term( $default, 'category', array( 'name' => $c[0], 'slug' => $slug, 'description' => $c[1] ) );
				continue;
			}
			$r = wp_insert_term( $c[0], 'category', array( 'slug' => $slug, 'description' => $c[1] ) );
			if ( 'kien-thuc-in-an' === $slug && ! is_wp_error( $r ) ) {
				update_option( 'default_category', (int) $r['term_id'] );
			}
		}

		// Trang danh sách bài.
		$page = get_page_by_path( AV_BLOG_BASE );
		if ( ! $page ) {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => AV_BLOG_BASE,
					'post_title'   => 'Kiến thức in ấn',
					'post_excerpt' => 'Kinh nghiệm chọn giấy, kỹ thuật in, gia công và thiết kế ấn phẩm từ xưởng in Âu Việt, giúp Quý khách đặt in đúng nhu cầu, đúng ngân sách.',
				)
			);
		} else {
			$id = $page->ID;
		}
		if ( $id && ! is_wp_error( $id ) ) {
			update_option( 'page_for_posts', (int) $id );
		}
		flush_rewrite_rules( false );
	},
	99
);

/* ------------------------------------------------------------------ */
/* Truy vấn danh sách                                                  */
/* ------------------------------------------------------------------ */

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( $q->is_home() || $q->is_category() || $q->is_tag() ) {
			$q->set( 'posts_per_page', 9 );
			$q->set( 'ignore_sticky_posts', true );
			// Bài nổi bật đã có khu riêng đầu trang danh sách.
			if ( $q->is_home() ) {
				$sticky = array_map( 'intval', (array) get_option( 'sticky_posts', array() ) );
				if ( $sticky ) {
					$q->set( 'post__not_in', $sticky );
				}
			}
		}
	}
);

/**
 * Bài nổi bật (ghim), tối đa 3.
 *
 * @return WP_Post[]
 */
function av_featured_posts(): array {
	$ids = array_map( 'intval', (array) get_option( 'sticky_posts', array() ) );
	if ( ! $ids ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'           => 'post',
			'post__in'            => $ids,
			'numberposts'         => 3,
			'ignore_sticky_posts' => true,
		)
	);
}

/**
 * Chủ đề có từ 2 bài trở lên (TT-03).
 *
 * @return WP_Term[]
 */
function av_visible_topics( int $post_id = 0 ): array {
	$terms = $post_id ? get_the_terms( $post_id, 'post_tag' ) : get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => true ) );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}
	return array_values( array_filter( $terms, static fn( $t ) => (int) $t->count >= 2 ) );
}

// Chủ đề dưới 2 bài: không hiện ra ngoài (chuyển về danh sách), không vào sitemap.
add_action(
	'template_redirect',
	static function () {
		if ( is_tag() ) {
			$t = get_queried_object();
			if ( $t instanceof WP_Term && (int) $t->count < 2 ) {
				wp_safe_redirect( av_blog_url(), 302 );
				exit;
			}
		}
	}
);
add_filter(
	'rank_math/sitemap/entry',
	static function ( $url, $type, $obj ) {
		if ( 'term' === $type && $obj instanceof WP_Term && 'post_tag' === $obj->taxonomy && (int) $obj->count < 2 ) {
			return false;
		}
		return $url;
	},
	10,
	3
);
add_filter(
	'wp_sitemaps_taxonomies_query_args',
	static function ( array $args, string $tax ): array {
		if ( 'post_tag' === $tax ) {
			$ids = array();
			foreach ( get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false ) ) as $t ) {
				if ( (int) $t->count < 2 ) {
					$ids[] = (int) $t->term_id;
				}
			}
			if ( $ids ) {
				$args['exclude'] = $ids;
			}
		}
		return $args;
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* Mục lục, ID tiêu đề, kêu gọi báo giá giữa bài                       */
/* ------------------------------------------------------------------ */

/**
 * Gắn id cho h2, h3 (để mục lục nhảy tới) và trả về danh sách mục lục.
 *
 * @param array $toc Nhận [ [level, id, text], ... ].
 */
function av_heading_ids( string $html, array &$toc = array() ): string {
	$used = array();
	return (string) preg_replace_callback(
		'#<h([23])([^>]*)>(.*?)</h\1>#is',
		static function ( $m ) use ( &$toc, &$used ) {
			$text = trim( html_entity_decode( wp_strip_all_tags( $m[3] ), ENT_QUOTES, 'UTF-8' ) );
			if ( '' === $text ) {
				return $m[0];
			}
			if ( preg_match( '/\sid="([^"]+)"/', $m[2], $idm ) ) {
				$id = $idm[1];
			} else {
				$id = sanitize_title( remove_accents( $text ) ) ?: 'muc';
				$base = $id;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
			}
			$used[ $id ] = true;
			// Dòng liên hệ cuối bài (hotline, địa chỉ) không đưa vào mục lục.
			if ( ! preg_match( '/hotline|e-?mail|địa chỉ|\d{3,4}[ .]?\d{3}[ .]?\d{3}/iu', $text ) ) {
				$toc[] = array( (int) $m[1], $id, $text );
			}
			return str_contains( $m[2], ' id=' ) ? $m[0] : '<h' . $m[1] . ' id="' . esc_attr( $id ) . '"' . $m[2] . '>' . $m[3] . '</h' . $m[1] . '>';
		},
		$html
	);
}

function av_current_article_id(): int {
	$id = get_queried_object_id();
	return ( $id && is_singular( 'post' ) ) ? $id : 0;
}

/**
 * Sản phẩm gắn với bài (chọn tay).
 *
 * @return WP_Post[]
 */
function av_post_products( int $id ): array {
	$ids = array_filter( array_map( 'intval', (array) get_post_meta( $id, '_av_post_product' ) ) );
	if ( ! $ids ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'   => 'av_product',
			'post__in'    => $ids,
			'orderby'     => 'post__in',
			'numberposts' => 4,
		)
	);
}

function av_inline_cta( int $post_id ): string {
	$c        = av_contact();
	$products = av_post_products( $post_id );
	$name     = $products ? mb_strtolower( $products[0]->post_title ) : 'ấn phẩm';
	return sprintf(
		'<aside class="av-inline-cta" aria-label="Nhận báo giá"><p><strong>Cần %1$s theo yêu cầu?</strong> Gửi số lượng, kích thước và mẫu Quý khách thích, Âu Việt gọi lại tư vấn và báo giá trong giờ làm việc.</p><div class="av-inline-cta__btns"><a class="av-btn" href="#bao-gia">Nhận báo giá</a><a class="av-btn av-btn--zalo" href="%2$s" target="_blank" rel="noopener" data-av-event="bam_zalo">Zalo</a><a class="av-btn av-btn--outline" href="%3$s" data-av-event="bam_goi">Gọi %4$s</a></div></aside>',
		esc_html( $name ),
		esc_url( $c['zalo_url'] ),
		esc_url( av_tel_href( $c['hotline'] ) ),
		esc_html( $c['hotline'] )
	);
}

// Chỉ sửa phần nội dung bài trên trang chi tiết (không đụng đoạn trích, mô tả SEO).
add_filter(
	'render_block_core/post-content',
	static function ( string $html, array $parsed, $instance ): string {
		$id = av_current_article_id();
		if ( ! $id || ! $instance instanceof WP_Block || (int) ( $instance->context['postId'] ?? 0 ) !== $id ) {
			return $html;
		}
		$html = av_heading_ids( $html );
		// Khối kêu gọi báo giá giữa bài: trước tiêu đề h2 ở khoảng giữa bài (bài có từ 3 tiêu đề h2).
		$count = preg_match_all( '#<h2[\s>]#i', $html, $mm, PREG_OFFSET_CAPTURE );
		if ( $count >= 3 ) {
			$pos  = $mm[0][ (int) floor( $count / 2 ) ][1];
			$html = substr( $html, 0, $pos ) . av_inline_cta( $id ) . substr( $html, $pos );
		}
		return $html;
	},
	10,
	3
);

/* ------------------------------------------------------------------ */
/* Khối hiển thị                                                       */
/* ------------------------------------------------------------------ */

add_filter(
	'av_block_definitions',
	static function ( array $defs ): array {
		return $defs + array(
			'auviet/blog-header'    => array( 'title' => 'Đầu trang Kiến thức in ấn', 'icon' => 'welcome-write-blog', 'render' => 'av_render_blog_header', 'description' => 'Tiêu đề, đoạn giới thiệu, các chuyên mục.' ),
			'auviet/blog-featured'  => array( 'title' => 'Bài nổi bật', 'icon' => 'star-filled', 'render' => 'av_render_blog_featured', 'description' => 'Bài được ghim (tối đa 3), chỉ ở trang đầu danh sách.' ),
			'auviet/post-card-meta' => array( 'title' => 'Chuyên mục và ngày (thẻ bài)', 'icon' => 'calendar', 'render' => 'av_render_post_card_meta', 'description' => 'Dòng nhỏ trên thẻ bài viết.', 'context' => array( 'postId', 'postType' ) ),
			'auviet/post-byline'    => array( 'title' => 'Ngày đăng, cập nhật, tác giả', 'icon' => 'admin-users', 'render' => 'av_render_post_byline', 'description' => 'Dòng thông tin dưới tiêu đề bài.' ),
			'auviet/post-toc'       => array( 'title' => 'Mục lục bài', 'icon' => 'list-view', 'render' => 'av_render_post_toc', 'description' => 'Tự sinh từ tiêu đề con (H2, H3).' ),
			'auviet/post-aside-cta' => array( 'title' => 'Thẻ báo giá cạnh bài', 'icon' => 'phone', 'render' => 'av_render_post_aside_cta', 'description' => 'Ô nhỏ cạnh bài trên máy tính.' ),
			'auviet/post-share'     => array( 'title' => 'Chia sẻ bài', 'icon' => 'share', 'render' => 'av_render_post_share', 'description' => 'Facebook, Zalo, chép đường dẫn.' ),
			'auviet/post-topics'    => array( 'title' => 'Chủ đề của bài', 'icon' => 'tag', 'render' => 'av_render_post_topics', 'description' => 'Chủ đề có từ 2 bài.' ),
			'auviet/post-products'  => array( 'title' => 'Sản phẩm trong bài', 'icon' => 'products', 'render' => 'av_render_post_products', 'description' => 'Sản phẩm gắn với bài (chọn tay).' ),
			'auviet/post-quote'     => array( 'title' => 'Báo giá cuối bài', 'icon' => 'feedback', 'render' => 'av_render_post_quote', 'description' => 'Bốn nút liên hệ và form chọn sẵn sản phẩm của bài.' ),
			'auviet/post-related'   => array( 'title' => 'Bài liên quan', 'icon' => 'media-text', 'render' => 'av_render_post_related', 'description' => 'Bài cùng chủ đề hoặc chuyên mục.' ),
		);
	}
);

function av_render_blog_header(): string {
	$blog  = av_blog_page_id();
	$cats  = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'orderby' => 'term_id' ) );
	$cur   = is_category() ? get_queried_object_id() : 0;
	$kick  = '';
	if ( is_category() ) {
		$title = single_cat_title( '', false );
		$intro = category_description();
	} elseif ( is_tag() ) {
		$kick  = '<p class="av-kicker">Chủ đề</p>';
		$title = single_tag_title( '', false );
		$intro = tag_description() ?: '<p>Các bài viết của Âu Việt về ' . esc_html( mb_strtolower( $title ) ) . '.</p>';
	} else {
		$title = $blog ? get_the_title( $blog ) : 'Kiến thức in ấn';
		$intro = $blog && has_excerpt( $blog ) ? '<p>' . esc_html( get_the_excerpt( $blog ) ) . '</p>' : '';
	}
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	$out   = '<header class="av-cat-head">' . $kick . '<h1 class="av-cat-head__title">' . esc_html( $title ) . ( $paged > 1 ? '<span class="av-cat-head__page"> – trang ' . $paged . '</span>' : '' ) . '</h1>';
	if ( $intro ) {
		$out .= '<div class="av-cat-head__intro">' . wp_kses_post( wpautop( $intro ) ) . '</div>';
	}
	if ( $cats && ! is_wp_error( $cats ) && ! is_tag() ) {
		$out .= '<ul class="av-chips" aria-label="Chuyên mục"><li><a class="av-chip' . ( $cur ? '' : ' is-active' ) . '" href="' . esc_url( av_blog_url() ) . '"' . ( $cur ? '' : ' aria-current="page"' ) . '>Tất cả</a></li>';
		foreach ( $cats as $c ) {
			$on   = $cur === $c->term_id;
			$out .= sprintf( '<li><a class="av-chip%s" href="%s"%s>%s <span>%d</span></a></li>', $on ? ' is-active' : '', esc_url( get_term_link( $c ) ), $on ? ' aria-current="page"' : '', esc_html( $c->name ), (int) $c->count );
		}
		$out .= '</ul>';
	}
	return $out . '</header>';
}

function av_post_card_meta_text( int $id ): string {
	$cats = get_the_category( $id );
	return trim( ( $cats ? $cats[0]->name . ' · ' : '' ) . get_the_date( 'd/m/Y', $id ) );
}

function av_render_blog_featured(): string {
	if ( ! is_home() || is_paged() ) {
		return '';
	}
	$posts = av_featured_posts();
	if ( ! $posts ) {
		return '';
	}
	$out = '<section class="av-featured" aria-labelledby="av-featured-title"><h2 id="av-featured-title" class="screen-reader-text">Bài nổi bật</h2><ul class="av-featured__list">';
	foreach ( $posts as $i => $p ) {
		$out .= sprintf(
			'<li class="av-featured__item%s"><a href="%s">%s<span class="av-featured__text"><span class="av-featured__tag">Nổi bật</span><strong>%s</strong>%s<span class="av-pmeta">%s</span></span></a></li>',
			0 === $i ? ' is-main' : '',
			esc_url( get_permalink( $p ) ),
			get_the_post_thumbnail( $p, 0 === $i ? 'large' : 'medium_large', array( 'alt' => '', 'loading' => 0 === $i ? 'eager' : 'lazy' ) ) ?: '<span class="av-ph">[Chưa có ảnh]</span>',
			esc_html( get_the_title( $p ) ),
			0 === $i ? '<span class="av-featured__ex">' . esc_html( wp_trim_words( get_the_excerpt( $p ), 30, '…' ) ) . '</span>' : '',
			esc_html( av_post_card_meta_text( $p->ID ) )
		);
	}
	return $out . '</ul></section>';
}

function av_render_post_card_meta( array $attrs, string $content, WP_Block $block ): string {
	$id = (int) ( $block->context['postId'] ?? get_the_ID() );
	return ( $id && 'post' === get_post_type( $id ) ) ? '<p class="av-pmeta">' . esc_html( av_post_card_meta_text( $id ) ) . '</p>' : '';
}

function av_render_post_byline(): string {
	$id = av_current_article_id();
	if ( ! $id ) {
		return '';
	}
	$post   = get_post( $id );
	$parts  = array( sprintf( 'Đăng <time datetime="%s">%s</time>', esc_attr( get_the_date( 'c', $post ) ), esc_html( get_the_date( 'd/m/Y', $post ) ) ) );
	$mod    = strtotime( $post->post_modified_gmt );
	$pub    = strtotime( $post->post_date_gmt );
	if ( $mod - $pub > DAY_IN_SECONDS ) {
		$parts[] = sprintf( 'Cập nhật <time datetime="%s">%s</time>', esc_attr( get_the_modified_date( 'c', $post ) ), esc_html( get_the_modified_date( 'd/m/Y', $post ) ) );
	}
	$parts[] = 'Tác giả ' . esc_html( get_the_author_meta( 'display_name', (int) $post->post_author ) );
	$cats    = get_the_category( $id );
	$cat     = $cats ? sprintf( '<a class="av-byline__cat" href="%s">%s</a>', esc_url( get_category_link( $cats[0] ) ), esc_html( $cats[0]->name ) ) : '';
	return '<p class="av-byline">' . $cat . '<span>' . implode( '</span><span>', $parts ) . '</span></p>';
}

function av_render_post_toc(): string {
	$id = av_current_article_id();
	if ( ! $id ) {
		return '';
	}
	$toc = array();
	av_heading_ids( do_blocks( (string) get_post_field( 'post_content', $id ) ), $toc );
	if ( count( $toc ) < 2 ) {
		return '';
	}
	$out = '<nav class="av-toc" aria-labelledby="av-toc-title"><details data-av-toc><summary id="av-toc-title">Mục lục</summary><ol>';
	foreach ( $toc as $t ) {
		$out .= sprintf( '<li class="av-toc__l%d"><a href="#%s">%s</a></li>', (int) $t[0], esc_attr( $t[1] ), esc_html( $t[2] ) );
	}
	// Máy tính: mở sẵn mục lục; điện thoại: đóng để không đẩy nội dung xuống.
	return $out . "</ol></details></nav><script>(function(){var d=document.querySelector('[data-av-toc]');if(d&&window.matchMedia('(min-width: 1000px)').matches){d.open=true;}})();</script>";
}

function av_render_post_aside_cta(): string {
	if ( ! av_current_article_id() ) {
		return '';
	}
	$c = av_contact();
	return sprintf(
		'<div class="av-aside-cta"><strong>Cần in theo yêu cầu?</strong><p>Gọi hoặc nhắn Zalo, Âu Việt tư vấn chất liệu và báo giá nhanh.</p><a class="av-btn" href="#bao-gia">Nhận báo giá</a><a class="av-aside-cta__tel" href="%1$s" data-av-event="bam_goi">%2$s</a></div>',
		esc_url( av_tel_href( $c['hotline'] ) ),
		esc_html( $c['hotline'] )
	);
}

function av_render_post_share(): string {
	$id = av_current_article_id();
	if ( ! $id ) {
		return '';
	}
	$url = get_permalink( $id );
	return sprintf(
		'<div class="av-share" data-av-share data-url="%1$s" data-title="%2$s"><span class="av-share__label">Chia sẻ bài</span><a class="av-share__btn av-share__btn--fb" href="https://www.facebook.com/sharer/sharer.php?u=%3$s" target="_blank" rel="noopener" data-av-event="chia_se_facebook">Facebook</a><button type="button" class="av-share__btn av-share__btn--zalo" data-av-share-zalo data-av-event="chia_se_zalo">Zalo</button><button type="button" class="av-share__btn" data-av-share-copy>Chép đường dẫn</button><span class="av-share__msg" role="status" aria-live="polite"></span></div>'
		. "<script>(function(){var r=document.querySelector('[data-av-share]');if(!r)return;var u=r.dataset.url,t=r.dataset.title,m=r.querySelector('.av-share__msg');function copy(msg){var done=function(){m.textContent=msg;setTimeout(function(){m.textContent='';},4000);};if(navigator.clipboard){navigator.clipboard.writeText(u).then(done,function(){window.prompt('Chép đường dẫn:',u);});}else{window.prompt('Chép đường dẫn:',u);}}r.querySelector('[data-av-share-copy]').addEventListener('click',function(){copy('Đã chép đường dẫn.');});r.querySelector('[data-av-share-zalo]').addEventListener('click',function(){if(navigator.share){navigator.share({title:t,url:u}).catch(function(){});}else{copy('Đã chép đường dẫn, mở Zalo và dán vào cuộc trò chuyện.');}});})();</script>",
		esc_attr( $url ),
		esc_attr( get_the_title( $id ) ),
		rawurlencode( $url )
	);
}

function av_render_post_topics(): string {
	$id     = av_current_article_id();
	$topics = $id ? av_visible_topics( $id ) : array();
	if ( ! $topics ) {
		return '';
	}
	$out = '<div class="av-topics"><span class="av-topics__label">Chủ đề</span><ul class="av-chips">';
	foreach ( $topics as $t ) {
		$out .= sprintf( '<li><a class="av-chip" href="%s">%s <span>%d</span></a></li>', esc_url( get_term_link( $t ) ), esc_html( $t->name ), (int) $t->count );
	}
	return $out . '</ul></div>';
}

function av_render_post_products(): string {
	$id = av_current_article_id();
	$ps = $id ? av_post_products( $id ) : array();
	return $ps ? '<section class="av-psec" aria-labelledby="av-postp-title"><h2 id="av-postp-title">Sản phẩm trong bài</h2>' . av_product_cards( $ps ) . '</section>' : '';
}

function av_render_post_quote(): string {
	$id = av_current_article_id();
	if ( ! $id ) {
		return '';
	}
	$ps    = av_post_products( $id );
	$first = $ps ? $ps[0] : null;
	$group = $first ? av_product_primary_group( $first->ID ) : null;
	return sprintf(
		'<section class="av-pquote" id="bao-gia" aria-labelledby="av-postq-title"><div class="av-pquote__text"><h2 id="av-postq-title">%1$s</h2><p>Để lại số điện thoại, Âu Việt gọi lại tư vấn quy cách và báo giá. Hoặc liên hệ ngay:</p>%2$s</div><div class="av-pquote__form" id="lien-he">%3$s</div></section>',
		esc_html( $first ? 'Nhận báo giá ' . mb_strtolower( $first->post_title ) : 'Nhận tư vấn và báo giá' ),
		av_render_contact_tiles(),
		av_render_quote_form(
			array(
				'need'    => $group ? av_group_root( $group )->name : '',
				'product' => $first ? $first->ID : 0,
			)
		)
	);
}

/**
 * Bài liên quan: cùng chủ đề trước, sau đó cùng chuyên mục.
 *
 * @return WP_Post[]
 */
function av_related_posts( int $id, int $limit = 3 ): array {
	$posts = array();
	$tags  = wp_list_pluck( av_visible_topics( $id ), 'term_id' );
	if ( $tags ) {
		$posts = get_posts( array( 'post_type' => 'post', 'numberposts' => $limit, 'post__not_in' => array( $id ), 'tag__in' => $tags, 'ignore_sticky_posts' => true ) );
	}
	if ( count( $posts ) < $limit ) {
		$cats  = wp_get_post_categories( $id );
		$posts = array_merge(
			$posts,
			get_posts(
				array(
					'post_type'           => 'post',
					'numberposts'         => $limit - count( $posts ),
					'post__not_in'        => array_merge( array( $id ), wp_list_pluck( $posts, 'ID' ) ),
					'category__in'        => $cats,
					'ignore_sticky_posts' => true,
				)
			)
		);
	}
	return $posts;
}

/**
 * Thẻ bài viết dùng chung.
 *
 * @param WP_Post[] $posts
 */
function av_post_cards( array $posts ): string {
	$out = '<ul class="av-postcards">';
	foreach ( $posts as $p ) {
		$out .= sprintf(
			'<li class="av-postcard"><a href="%s">%s<span class="av-pmeta">%s</span><strong>%s</strong></a></li>',
			esc_url( get_permalink( $p ) ),
			get_the_post_thumbnail( $p, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) ) ?: '<span class="av-ph">[Chưa có ảnh]</span>',
			esc_html( av_post_card_meta_text( $p->ID ) ),
			esc_html( get_the_title( $p ) )
		);
	}
	return $out . '</ul>';
}

function av_render_post_related(): string {
	$id = av_current_article_id();
	$ps = $id ? av_related_posts( $id ) : array();
	if ( ! $ps ) {
		return '';
	}
	return sprintf(
		'<section class="av-psec" aria-labelledby="av-postr-title"><div class="av-psec__head"><h2 id="av-postr-title">Bài liên quan</h2><a href="%s">Xem tất cả bài</a></div>%s</section>',
		esc_url( av_blog_url() ),
		av_post_cards( $ps )
	);
}

/* ------------------------------------------------------------------ */
/* Quản trị bài viết: sản phẩm liên quan, cảnh báo trùng tiêu đề        */
/* ------------------------------------------------------------------ */

add_action(
	'add_meta_boxes_post',
	static function () {
		add_meta_box( 'av_post_products', 'Sản phẩm liên quan', 'av_box_post_products', 'post', 'side', 'default' );
	}
);

function av_box_post_products( WP_Post $post ): void {
	wp_nonce_field( 'av_post_products', 'av_post_products_nonce' );
	$chosen = array_map( 'intval', (array) get_post_meta( $post->ID, '_av_post_product' ) );
	echo '<p class="description">Sản phẩm chọn ở đây hiện cuối bài, và form báo giá chọn sẵn sản phẩm đầu tiên. Bài cũng tự hiện ở mục "Bài viết liên quan" của sản phẩm đó.</p><div class="av-checklist av-checklist--side">';
	foreach ( av_visible_groups() as $g ) {
		$products = get_posts( array( 'post_type' => 'av_product', 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ), 'tax_query' => array( array( 'taxonomy' => 'av_group', 'terms' => $g->term_id ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( ! $products ) {
			continue;
		}
		printf( '<p class="av-checklist__group">%s</p>', esc_html( $g->name ) );
		foreach ( $products as $p ) {
			printf( '<label><input type="checkbox" name="av_post_products[]" value="%d"%s> %s</label>', (int) $p->ID, checked( in_array( $p->ID, $chosen, true ), true, false ), esc_html( $p->post_title ) );
		}
	}
	echo '</div>';
}

add_action(
	'save_post_post',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['av_post_products_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_post_products_nonce'] ), 'av_post_products' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		delete_post_meta( $post_id, '_av_post_product' );
		foreach ( array_unique( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['av_post_products'] ?? array() ) ) ) ) as $pid ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( 'av_product' === get_post_type( $pid ) ) {
				add_post_meta( $post_id, '_av_post_product', $pid );
			}
		}
	}
);

/**
 * Nội dung trùng tiêu đề (không phân biệt hoa thường, dấu cách thừa) trong sản phẩm, bài viết, trang.
 *
 * @return array[] [ ['type' => ..., 'title' => ..., 'edit' => ...] ]
 */
function av_title_duplicates( string $title, int $exclude = 0 ): array {
	global $wpdb;
	$norm = static fn( $s ) => mb_strtolower( trim( preg_replace( '/\s+/u', ' ', html_entity_decode( (string) $s, ENT_QUOTES, 'UTF-8' ) ) ) );
	$t    = $norm( $title );
	if ( '' === $t ) {
		return array();
	}
	$labels = array( 'av_product' => 'Sản phẩm', 'post' => 'Bài viết', 'page' => 'Trang' );
	$rows   = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT ID, post_title, post_type FROM {$wpdb->posts} WHERE post_type IN ('av_product','post','page') AND post_status IN ('publish','future','draft','pending','private') AND ID <> %d AND post_title LIKE %s LIMIT 20",
			$exclude,
			$wpdb->esc_like( trim( $title ) )
		)
	);
	$out = array();
	foreach ( $rows as $r ) {
		if ( $norm( $r->post_title ) === $t ) {
			$out[] = array(
				'type'  => $labels[ $r->post_type ] ?? $r->post_type,
				'title' => $r->post_title,
				'edit'  => get_edit_post_link( (int) $r->ID, 'raw' ),
			);
		}
	}
	return $out;
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'auviet/v1',
			'/title-check',
			array(
				'methods'             => 'GET',
				'permission_callback' => static fn() => current_user_can( 'edit_posts' ) || current_user_can( 'edit_av_products' ),
				'args'                => array(
					'title' => array( 'type' => 'string', 'required' => true ),
					'id'    => array( 'type' => 'integer', 'default' => 0 ),
				),
				'callback'            => static fn( WP_REST_Request $r ) => rest_ensure_response( av_title_duplicates( (string) $r['title'], (int) $r['id'] ) ),
			)
		);
	}
);

add_action(
	'enqueue_block_editor_assets',
	static function () {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->post_type, array( 'post', 'av_product' ), true ) ) {
			return;
		}
		wp_enqueue_script( 'auviet-title-check', plugins_url( 'assets/js/title-check.js', AV_CORE_FILE ), array( 'wp-data', 'wp-api-fetch', 'wp-plugins', 'wp-element', 'wp-editor', 'wp-notices', 'wp-url' ), av_ver( AV_CORE_DIR . 'assets/js/title-check.js' ), true );
	}
);

/* ------------------------------------------------------------------ */
/* Sản phẩm: bài viết liên quan tự lấy từ bài có gắn sản phẩm          */
/* ------------------------------------------------------------------ */

add_filter(
	'av_product_related_posts',
	static function ( array $posts, int $product_id, int $limit ): array {
		if ( count( $posts ) >= $limit ) {
			return $posts;
		}
		$more = get_posts(
			array(
				'post_type'           => 'post',
				'numberposts'         => $limit - count( $posts ),
				'post__not_in'        => wp_list_pluck( $posts, 'ID' ),
				'meta_key'            => '_av_post_product', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'          => (string) $product_id, // phpcs:ignore WordPress.DB.SlowDBQuery
				'ignore_sticky_posts' => true,
			)
		);
		return array_merge( $posts, $more );
	},
	10,
	3
);

/* ------------------------------------------------------------------ */
/* Phía khách: tài nguyên                                              */
/* ------------------------------------------------------------------ */

add_action(
	'enqueue_block_assets',
	static function () {
		wp_enqueue_style( 'auviet-content', plugins_url( 'assets/css/content.css', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/css/content.css' ) );
	}
);

/* ------------------------------------------------------------------ */
/* Nhập bài viết từ web cũ (Công cụ › Nhập từ web cũ, chỉ môi trường thử) */
/* ------------------------------------------------------------------ */

/**
 * Đọc danh sách bài trên web cũ (Tin tức, Tư vấn cùng một chuyên mục 115), lưu tạm để các bước sau dùng.
 *
 * @return array[] Việc cần làm cho trang nhập.
 */
function av_legacy_news_jobs(): array {
	$list = array();
	foreach ( array( '/news/tin-tuc-115.html', '/news/tu-van-115.html' ) as $base ) {
		for ( $page = 1; $page <= 15; $page++ ) {
			$xp = av_legacy_xpath( $base . '/p-' . $page );
			if ( is_wp_error( $xp ) ) {
				break;
			}
			$new = 0;
			foreach ( $xp->query( "//a[@id='a_tieu_de_tin']" ) as $a ) {
				$href = (string) $a->getAttribute( 'href' );
				if ( ! preg_match( '#(/newsdetail/.*-115-(\d+)\.html)$#u', rawurldecode( $href ), $m ) || isset( $list[ $m[2] ] ) ) {
					continue;
				}
				$list[ $m[2] ] = array( $m[1], trim( preg_replace( '/\s+/u', ' ', $a->textContent ) ) );
				++$new;
			}
			if ( ! $new ) {
				break;
			}
		}
	}
	ksort( $list, SORT_NUMERIC );
	update_option( 'av_legacy_news_list', $list, false );
	$jobs = array();
	foreach ( $list as $id => $item ) {
		$jobs[] = array(
			'kind'  => 'news',
			'slug'  => (string) $id,
			'index' => 0,
			'label' => 'Bài: ' . $item[1],
		);
	}
	return $jobs;
}

/**
 * Chữ không dấu, thường, để so từ khoá.
 */
function av_ascii( string $s ): string {
	return strtolower( remove_accents( $s ) );
}

/**
 * Chuyên mục theo tiêu đề bài cũ (Biên tập viên sửa lại được trong bài).
 */
function av_legacy_news_category( string $title ): string {
	$t = av_ascii( $title );
	if ( preg_match( '/can canh|thanh pham .*hoan thien/', $t ) ) {
		return 'du-an';
	}
	if ( preg_match( '/^uu dai|khuyen mai/', $t ) ) {
		return 'tin-cong-ty';
	}
	if ( preg_match( '/^thiet ke|quy trinh thiet ke|noi dung can co|mau .*(dep|ua chuong)/', $t ) ) {
		return 'huong-dan-thiet-ke';
	}
	return 'kien-thuc-in-an';
}

/**
 * Chủ đề và sản phẩm theo từ khoá trong tiêu đề: [ chủ đề => [tên, [đường dẫn sản phẩm]] ].
 */
function av_legacy_news_topics(): array {
	// [tên chủ đề, mẫu từ khoá, sản phẩm, gắn sản phẩm đầu khi tiêu đề không nhắc đúng tên sản phẩm nào].
	return array(
		'tui-giay'           => array( 'Túi giấy', '/tui giay/', array( 'in-tui-giay' ), true ),
		'tui-giay-kraft'     => array( 'Túi giấy kraft', '/tui giay kraft/', array( 'in-tui-giay-kraft' ), true ),
		'hop-giay'           => array( 'Hộp giấy', '/\bhop (giay|qua|thuoc|my pham|dung|cung|mem|nap|carton)|thung carton/', array( 'in-hop-giay', 'in-thung-carton' ), true ),
		'lich-tet'           => array( 'Lịch Tết', '/\blich\b/', array( 'in-lich-treo-tuong', 'in-lich-de-ban' ), true ),
		'bao-li-xi'          => array( 'Bao lì xì', '/li xi/', array( 'in-bao-li-xi' ), true ),
		'trung-thu'          => array( 'Quà Trung thu', '/trung thu/', array(), false ),
		'bao-bi-my-pham'     => array( 'Bao bì mỹ phẩm', '/my pham/', array(), false ),
		'catalogue-brochure' => array( 'Catalogue, brochure', '/catalogue|brochure/', array( 'in-catalogue' ), true ),
		'to-roi-to-gap'      => array( 'Tờ rơi, tờ gấp', '/to roi|to gap/', array( 'in-to-roi', 'in-to-gap' ), true ),
		'tem-nhan-decal'     => array( 'Tem nhãn, decal', '/decal|tem nhan|\btem\b/', array( 'in-tem-nhan', 'in-decal' ), false ),
		'in-kho-lon'         => array( 'Standee, banner, bạt', '/standee|banner|hiflex|backdrop/', array( 'in-standee', 'in-bat-hiflex', 'in-backdrop' ), false ),
		'an-pham-van-phong'  => array( 'Ấn phẩm văn phòng', '/card visit|phong bi|kep file|hoa don|so tay/', array( 'in-card-visit', 'in-phong-bi', 'in-kep-file', 'in-hoa-don' ), false ),
		'sach-ky-yeu'        => array( 'Sách, kỷ yếu', '/\bsach\b|ky yeu/', array( 'in-sach-ky-yeu' ), true ),
		'tranh-canvas'       => array( 'Tranh canvas', '/canvas/', array( 'in-tranh-canvas' ), true ),
		'chat-lieu-gia-cong' => array( 'Chất liệu, gia công', '/ivory|couche|loai giay|giay kraft|can mo|can bong|gia cong/', array(), false ),
	);
}

/**
 * Chủ đề và sản phẩm gợi ý theo tiêu đề bài cũ.
 *
 * @return array{0:string[],1:int[]} [tên chủ đề], [ID sản phẩm]
 */
function av_legacy_news_tags( string $title ): array {
	$t        = av_ascii( $title );
	$tags     = array();
	$products = array();
	foreach ( av_legacy_news_topics() as $topic ) {
		if ( ! preg_match( $topic[1], $t ) ) {
			continue;
		}
		$tags[]  = $topic[0];
		$matched = array();
		$all     = array();
		foreach ( $topic[2] as $slug ) {
			$p = get_page_by_path( $slug, OBJECT, 'av_product' );
			if ( ! $p ) {
				continue;
			}
			$all[] = $p->ID;
			// Tên ngắn của sản phẩm: bỏ "In", phần sau dấu phẩy, giữ 2 chữ đầu ("In bạt hiflex khổ lớn" → "bat hiflex").
			$short = implode( ' ', array_slice( explode( ' ', av_ascii( trim( preg_replace( array( '/^In /u', '/,.*$/u' ), '', $p->post_title ) ) ) ), 0, 2 ) );
			if ( '' !== $short && str_contains( $t, $short ) ) {
				$matched[] = $p->ID;
			}
		}
		$products = array_merge( $products, $matched ?: ( $topic[3] && $all ? array( $all[0] ) : array() ) );
	}
	return array( array_values( array_unique( $tags ) ), array_slice( array_values( array_unique( $products ) ), 0, 3 ) );
}

add_action(
	'wp_ajax_av_legacy_news',
	static function () {
		av_legacy_guard();
		$old  = absint( $_POST['slug'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		$list = (array) get_option( 'av_legacy_news_list', array() );
		if ( ! isset( $list[ $old ] ) ) {
			wp_send_json_error( 'Không có bài cũ này trong danh sách.' );
		}
		[ $path, $list_title ] = $list[ $old ];
		$xp = av_legacy_xpath( $path );
		if ( is_wp_error( $xp ) ) {
			wp_send_json_error( $list_title . ': không tải được (' . $xp->get_error_message() . ')' );
		}
		$name_node = $xp->query( "//*[@id='news3_chi_tiet_tin']//p[contains(concat(' ', normalize-space(@class), ' '), ' name2 ')]" )->item( 0 );
		$title     = $name_node ? trim( preg_replace( '/\s+/u', ' ', $name_node->textContent ) ) : $list_title;
		$content   = $xp->query( "//*[@id='news3_chi_tiet_tin']//div[contains(concat(' ', normalize-space(@class), ' '), ' noi_dung_chitiet ')]" )->item( 0 );
		if ( ! $content ) {
			wp_send_json_error( $title . ': không thấy nội dung bài.' );
		}

		$found = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'meta_key' => '_av_legacy_news', 'meta_value' => (string) $old ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $found ) {
			$post_id = $found[0]->ID;
		} else {
			$post_id = (int) wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => $title, 'post_author' => get_current_user_id() ) );
			if ( ! $post_id ) {
				wp_send_json_error( $title . ': không tạo được bài.' );
			}
			update_post_meta( $post_id, '_av_legacy_news', (string) $old );
		}
		$force = ! empty( $_POST['force'] ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $force && ! av_legacy_is_fresh( $post_id ) ) {
			wp_send_json_success( array( 'message' => $title . ': giữ nguyên (bài đã được sửa tay sau lần nhập trước).' ) );
		}

		$images = 0;
		$blocks = av_legacy_blocks( $content, $post_id, $title, $images );
		$urls   = av_legacy_content_images( $content );
		$thumb  = $urls ? av_legacy_sideload( $urls[0], $post_id, $title ) : 0;

		// Ngày đăng: web cũ không ghi ngày, lấy ngày tải lên của ảnh đầu bài (Last-Modified); không có thì để hôm nay.
		$ts = 0;
		if ( $urls ) {
			$head = wp_remote_head( $urls[0], array( 'timeout' => 20 ) );
			$lm   = is_wp_error( $head ) ? '' : (string) wp_remote_retrieve_header( $head, 'last-modified' );
			$ts   = $lm ? (int) strtotime( $lm ) : 0;
		}
		$ts = ( $ts > 0 && $ts < time() ) ? $ts : time();

		// Đoạn tóm tắt: đoạn văn đủ dài đầu tiên.
		$excerpt = '';
		foreach ( $content->getElementsByTagName( 'p' ) as $p ) {
			$t = trim( preg_replace( '/\s+/u', ' ', str_replace( "\xc2\xa0", ' ', $p->textContent ) ) );
			if ( mb_strlen( $t ) > 60 ) {
				$excerpt = wp_trim_words( $t, 40, '…' );
				break;
			}
		}

		$gmt = gmdate( 'Y-m-d H:i:s', $ts );
		kses_remove_filters();
		wp_update_post(
			array(
				'ID'            => $post_id,
				'post_title'    => $title,
				'post_name'     => sanitize_title( $title ),
				'post_content'  => $blocks,
				'post_excerpt'  => $excerpt,
				'post_status'   => 'publish',
				'post_date'     => get_date_from_gmt( $gmt ),
				'post_date_gmt' => $gmt,
				'edit_date'     => true,
			)
		);
		kses_init();
		// Ngày cập nhật = ngày đăng (nhập lại không tính là cập nhật bài).
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_modified' => get_date_from_gmt( $gmt ), 'post_modified_gmt' => $gmt ), array( 'ID' => $post_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		clean_post_cache( $post_id );
		update_post_meta( $post_id, '_av_legacy_time', $gmt );

		if ( $thumb ) {
			set_post_thumbnail( $post_id, $thumb );
		}
		$cat = get_term_by( 'slug', av_legacy_news_category( $title ), 'category' );
		if ( $cat ) {
			wp_set_post_categories( $post_id, array( (int) $cat->term_id ) );
		}
		[ $tags, $products ] = av_legacy_news_tags( $title );
		wp_set_post_tags( $post_id, $tags );
		delete_post_meta( $post_id, '_av_post_product' );
		foreach ( $products as $pid ) {
			add_post_meta( $post_id, '_av_post_product', $pid );
		}
		// Ba bài kiến thức chung làm bài nổi bật ban đầu.
		if ( in_array( $old, array( 502, 462, 473 ), true ) ) {
			stick_post( $post_id );
		}

		$map         = (array) get_option( 'av_legacy_news_map', array() );
		$map[ $old ] = $post_id;
		update_option( 'av_legacy_news_map', $map, false );

		$dups = av_title_duplicates( $title, $post_id );
		wp_send_json_success(
			array(
				'message' => sprintf(
					'%s ← %s: %d ảnh, ngày %s, %s%s%s',
					$title,
					$path,
					$images,
					wp_date( 'd/m/Y', $ts ),
					$cat ? $cat->name : '',
					$tags ? ', chủ đề ' . implode( ', ', $tags ) : '',
					$dups ? ' — CẢNH BÁO trùng tiêu đề với ' . $dups[0]['type'] . ' "' . $dups[0]['title'] . '"' : ''
				),
			)
		);
	}
);
