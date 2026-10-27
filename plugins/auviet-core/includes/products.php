<?php
/**
 * Dữ liệu sản phẩm (SP-01, SP-02, SP-05, QT-03, QT-04): đọc quy cách, ảnh, chất liệu, bảng giá.
 * Mọi phần hiển thị và quản trị đều đọc qua các hàm ở đây.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Các dòng quy cách cố định của bảng quy cách (SP-02).
 */
function av_spec_fields(): array {
	return array(
		'size'      => 'Khổ, kích thước',
		'paper'     => 'Chất liệu giấy',
		'gsm'       => 'Định lượng',
		'colors'    => 'Số màu in',
		'sides'     => 'Số mặt in',
		'finishing' => 'Gia công',
	);
}

/**
 * Quy cách đã điền: [nhãn => giá trị]. Dòng "Thông tin thêm" dạng "Nhãn: giá trị" mỗi dòng.
 */
function av_product_specs( int $post_id ): array {
	$out = array();
	foreach ( av_spec_fields() as $key => $label ) {
		$v = trim( (string) get_post_meta( $post_id, '_av_spec_' . $key, true ) );
		if ( '' !== $v ) {
			$out[ $label ] = $v;
		}
	}
	$extra = (string) get_post_meta( $post_id, '_av_spec_extra', true );
	foreach ( preg_split( '/\R/', $extra ) as $line ) {
		if ( str_contains( $line, ':' ) ) {
			[ $k, $v ] = array_map( 'trim', explode( ':', $line, 2 ) );
			if ( '' !== $k && '' !== $v ) {
				$out[ $k ] = $v;
			}
		}
	}
	return $out;
}

/**
 * Ảnh sản phẩm: ảnh đại diện đứng đầu, sau đó thư viện ảnh.
 *
 * @return int[]
 */
function av_product_gallery( int $post_id ): array {
	$ids   = array();
	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		$ids[] = $thumb;
	}
	foreach ( (array) get_post_meta( $post_id, '_av_gallery', true ) as $id ) {
		$id = (int) $id;
		if ( $id && ! in_array( $id, $ids, true ) && wp_attachment_is_image( $id ) ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * Nhóm chính (dùng cho breadcrumb, nhu cầu chọn sẵn trong form). Không đặt thì lấy nhóm đầu tiên.
 */
function av_product_primary_group( int $post_id ): ?WP_Term {
	$terms = get_the_terms( $post_id, 'av_group' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	$primary = (int) get_post_meta( $post_id, '_av_primary_group', true );
	foreach ( $terms as $t ) {
		if ( $t->term_id === $primary ) {
			return $t;
		}
	}
	return $terms[0];
}

/**
 * Nhóm cấp 1 chứa một nhóm (để chọn sẵn "Sản phẩm quan tâm" trong form).
 */
function av_group_root( WP_Term $term ): WP_Term {
	$ancestors = get_ancestors( $term->term_id, 'av_group', 'taxonomy' );
	if ( $ancestors ) {
		$root = get_term( end( $ancestors ), 'av_group' );
		if ( $root instanceof WP_Term ) {
			return $root;
		}
	}
	return $term;
}

/**
 * Chất liệu, gia công gắn với sản phẩm (SP-05). Lưu mỗi chất liệu một dòng meta _av_material để lọc được.
 *
 * @return WP_Post[]
 */
function av_product_materials( int $post_id ): array {
	$ids = array_map( 'intval', (array) get_post_meta( $post_id, '_av_material' ) );
	if ( ! $ids ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'   => 'av_material',
			'post__in'    => $ids,
			'orderby'     => 'post__in',
			'numberposts' => -1,
		)
	);
}

function av_material_kinds(): array {
	return array(
		'giay'     => 'Giấy và vật liệu in',
		'gia-cong' => 'Gia công thành phẩm',
	);
}

/**
 * Bảng giá tham khảo (QT-04). Cấu trúc:
 * tiers: số lượng các bậc; rows: [label, prices[]]; basis: total|unit; unit: đơn vị tính; note: ghi chú.
 */
function av_price_defaults(): array {
	return array(
		'tiers' => array(),
		'rows'  => array(),
		'basis' => 'total',
		'unit'  => 'cái',
		'note'  => '',
	);
}

function av_product_prices( int $post_id ): array {
	$p = wp_parse_args( (array) get_post_meta( $post_id, '_av_prices', true ), av_price_defaults() );
	$p['tiers'] = array_values( array_map( 'intval', (array) $p['tiers'] ) );
	$rows       = array();
	foreach ( (array) $p['rows'] as $r ) {
		$prices = array();
		foreach ( array_keys( $p['tiers'] ) as $i ) {
			$v        = $r['prices'][ $i ] ?? null;
			$prices[] = ( null === $v || '' === $v ) ? null : (int) $v;
		}
		$rows[] = array(
			'label'  => (string) ( $r['label'] ?? '' ),
			'prices' => $prices,
		);
	}
	$p['rows'] = $rows;
	return $p;
}

/**
 * Có ít nhất một ô giá thì coi là có bảng giá; không thì hiện "Liên hệ báo giá".
 */
function av_product_has_prices( int $post_id ): bool {
	return null !== av_product_min_price( $post_id );
}

/**
 * Giá thấp nhất trong bảng (dùng cho "Giá từ" trên thẻ sản phẩm). Đơn giá: lấy đơn giá nhỏ nhất; thành tiền: giá của bậc nhỏ nhất.
 */
function av_product_min_price( int $post_id ): ?int {
	$p   = av_product_prices( $post_id );
	$min = null;
	foreach ( $p['rows'] as $r ) {
		foreach ( $r['prices'] as $v ) {
			if ( null !== $v && $v > 0 && ( null === $min || $v < $min ) ) {
				$min = $v;
				if ( 'total' === $p['basis'] ) {
					break; // Bậc nhỏ nhất của dòng là giá vào cửa của dòng đó.
				}
			}
		}
	}
	return $min;
}

function av_format_vnd( int $amount ): string {
	return number_format( $amount, 0, ',', '.' ) . ' đ';
}

function av_product_moq( int $post_id ): string {
	return trim( (string) get_post_meta( $post_id, '_av_moq', true ) );
}

function av_product_lead_time( int $post_id ): string {
	return trim( (string) get_post_meta( $post_id, '_av_lead_time', true ) );
}

/**
 * Sản phẩm liên quan: chọn tay trước, thiếu thì lấy cùng nhóm chính. Không bao giờ chứa chính sản phẩm đó.
 *
 * @return WP_Post[]
 */
function av_product_related( int $post_id, int $limit = 4 ): array {
	$ids = array_values(
		array_filter(
			array_map( 'intval', (array) get_post_meta( $post_id, '_av_related', true ) ),
			static fn( $id ) => $id && $id !== $post_id
		)
	);
	$posts = $ids ? get_posts(
		array(
			'post_type'   => 'av_product',
			'post__in'    => $ids,
			'orderby'     => 'post__in',
			'numberposts' => $limit,
		)
	) : array();
	if ( count( $posts ) < $limit ) {
		$group = av_product_primary_group( $post_id );
		if ( $group ) {
			$more  = get_posts(
				array(
					'post_type'    => 'av_product',
					'numberposts'  => $limit - count( $posts ),
					'post__not_in' => array_merge( array( $post_id ), wp_list_pluck( $posts, 'ID' ) ),
					'orderby'      => array(
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					),
					'tax_query'    => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'taxonomy' => 'av_group',
							'terms'    => $group->term_id,
						),
					),
				)
			);
			$posts = array_merge( $posts, $more );
		}
	}
	return $posts;
}

/**
 * Bài viết liên quan (chọn tay).
 *
 * @return WP_Post[]
 */
function av_product_related_posts( int $post_id, int $limit = 3 ): array {
	$ids = array_filter( array_map( 'intval', (array) get_post_meta( $post_id, '_av_related_posts', true ) ) );
	if ( ! $ids ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'   => 'post',
			'post__in'    => $ids,
			'orderby'     => 'post__in',
			'numberposts' => $limit,
		)
	);
}

/**
 * Câu hỏi thường gặp gắn với sản phẩm hoặc nhóm.
 *
 * @param int[] $ids
 * @return WP_Post[]
 */
function av_faq_posts( array $ids ): array {
	$ids = array_filter( array_map( 'intval', $ids ) );
	if ( ! $ids ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'   => 'av_faq',
			'post__in'    => $ids,
			'orderby'     => 'post__in',
			'numberposts' => -1,
		)
	);
}

/**
 * Ảnh và đoạn giới thiệu của nhóm sản phẩm (SP-01).
 */
function av_group_thumb( WP_Term $term ): int {
	return (int) get_term_meta( $term->term_id, 'av_thumb_id', true );
}

function av_group_intro( WP_Term $term ): string {
	return (string) get_term_meta( $term->term_id, 'av_intro', true );
}

/**
 * Nhóm cấp 1 có sản phẩm đang hiển thị (nhóm rỗng không hiện trên menu, SP-01).
 *
 * @return WP_Term[]
 */
function av_visible_groups( int $parent = 0 ): array {
	$terms = get_terms(
		array(
			'taxonomy'     => 'av_group',
			'parent'       => $parent,
			'hide_empty'   => true,
			'pad_counts'   => true,
			'hierarchical' => true,
			'orderby'      => 'term_id',
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort( $terms, static fn( $a, $b ) => $a->term_id <=> $b->term_id );
	return $terms;
}
