<?php
/**
 * Dựng 2 landing từ 2 site vệ tinh của Âu Việt (làm bằng LadiPage):
 *   - inlichdep.vn       → /in-lich-tet/   In lịch Tết theo yêu cầu
 *   - intuigiaygiare.com → /in-tui-giay/   Xưởng in túi giấy
 *
 * Âu Việt › Landing theo mùa › "Tạo 2 landing từ trang cũ": máy chủ tự tải ảnh gốc (w.ladicdn.com) về thư viện Media
 * theo từng đợt nhỏ, rồi dựng trang bằng khối chuẩn của WordPress (sửa được từng chữ, từng ảnh trong trình soạn khối).
 * Chạy lại được: ảnh đã tải không tải lại; trang đã có thì giữ nguyên, trừ khi chọn "Dựng lại nội dung".
 */

defined( 'ABSPATH' ) || exit;

const AV_LS_CDN = 'https://w.ladicdn.com/';
const AV_LS_A   = '68b11041589e01001237939d/';
const AV_LS_B   = '697a1fbe787d41001277b0f3/';

/* ------------------------------------------------------------------ */
/* Ảnh: đường dẫn gốc => mô tả ảnh (alt)                               */
/* ------------------------------------------------------------------ */

function av_ls_images(): array {
	$a = AV_LS_A;
	$b = AV_LS_B;
	return array(
		// Bộ sưu tập lịch Tết (inlichdep.vn).
		'lich_hero'   => array( $a . 'lich-de-ban-chu-m-20260930021347-ya_4j.jpg', 'Lịch để bàn chữ M in logo doanh nghiệp' ),
		'lich_g1'     => array( $a . '3-20261019033728-xexub.png', 'Mẫu lịch Tết 2027 Âu Việt in' ),
		'lich_g2'     => array( $b . '3-20260928073139-_fznb.png', 'Mẫu lịch Tết doanh nghiệp' ),
		'lich_g3'     => array( $a . '5-20261019033728-jjwjy.png', 'Mẫu lịch để bàn Tết 2027' ),
		'lich_g4'     => array( $a . '1-20261019033728-j7l5k.png', 'Mẫu lịch Tết in theo yêu cầu' ),
		'lich_g5'     => array( $a . 'khanh-lich-kovitech-40-x-60_chinh-1-20260930021346-fbdht.jpg', 'Khánh lịch treo tường 40 × 60 cm' ),
		'lich_g6'     => array( $b . '6-20260928073139-lor1r.png', 'Mẫu lịch Tết quà tặng doanh nghiệp' ),
		'lich_g7'     => array( $a . '4-20261019033728-023kz.png', 'Mẫu lịch Tết in logo thương hiệu' ),
		'lich_g8'     => array( $a . '6b241d68ea70e1e9b09961878d2f97b1-20260929090026-blguz.jpg', 'Lịch treo tường 5 tờ, 7 tờ' ),
		'lich_g9'     => array( $a . '1-20260929082232-5aoj4.png', 'Mẫu lịch Tết Âu Việt' ),
		'lich_g10'    => array( $a . '55cba4a2416614a57c3c115dab4c87a1-20260929090026-dmncp.jpg', 'Lịch treo tường 1 tờ' ),
		// Sản phẩm lịch đã thực hiện.
		'lich_13to'   => array( $a . 'lich-treo-tuong-13-to-2022-mau-2-20260930031649-xhrvn.jpg', 'Lịch treo tường 13 tờ' ),
		'lich_a_done' => array( $a . '6-20260929082204-_sbma.png', 'Lịch để bàn chữ A' ),
		'lich_m_done' => array( $a . 'lich-de-ban-chu-m-co-kich-thuoc-tuong-tu-nhu-loai-lich-chu-a-1200x900-1-20260930021347-efkhi.jpg', 'Lịch để bàn chữ M' ),
		'lich_bloc1'  => array( $a . '1-20261019034024-haymk.png', 'Lịch bloc treo tường' ),
		'lich_bloc2'  => array( $a . '2-20261019034024-ezwqy.png', 'Lịch bloc Tết' ),
		'lich_bloc3'  => array( $a . '3-20261019034024-r80po.png', 'Lịch bloc in logo doanh nghiệp' ),
		'lich_bloc4'  => array( $a . '4-20261019034024-0digm.png', 'Lịch bloc quà tặng' ),
		'lich_bloc5'  => array( $a . '1-20260929082204-fvzfa.png', 'Lịch bloc khánh gỗ' ),
		// Các dòng lịch thịnh hành.
		'lich_a'      => array( $b . '4-20260928072942-lmnvn.png', 'Lịch để bàn chữ A in logo' ),
		'lich_57'     => array( $a . '6b241d68ea70e1e9b09961878d2f97b1-1-20260930021346-lxjif.jpg', 'Lịch treo tường 5 tờ – 7 tờ' ),
		'lich_bloc'   => array( $a . '24f4d440c1f42c09b2a7b5af1a122107-20260929090026-iofj-.jpg', 'Lịch bloc treo tường' ),
		'lich_m'      => array( $a . 'lich-de-ban-chu-m-co-kich-thuoc-tuong-tu-nhu-loai-lich-chu-a-1200x900-1-20260930021347-efkhi.jpg', 'Lịch để bàn chữ M' ),
		// Xưởng in (dùng chung).
		'xuong1'      => array( $a . '1790051701226_2209232202152786361_8125104089795116499_788a171f79947b1729f379b020073bba-copy-20260923090802-rgt2p.jpg', 'Xưởng in Âu Việt' ),
		'xuong2'      => array( $a . '3-20260924065723-388im.png', 'Máy in offset tại xưởng Âu Việt' ),
		'xuong3'      => array( $a . '8-20260924065723-zsha6.png', 'Dây chuyền gia công tại xưởng Âu Việt' ),
		'xuong4'      => array( $a . '1790051701141_2209232202152786361_8125104089795116499_f9f9af7fe064abba55350d5c5261db14-copy-20260923091328-wywiu.jpg', 'Khu vực sản xuất xưởng Âu Việt' ),
		'xuong5'      => array( $a . '1790051701267_2209232202152786361_8125104089795116499_9fba7588e299e65ecaaa39f2ef6ab464-copy-20260923091514-3gdsi.jpg', 'Thành phẩm chờ giao tại xưởng Âu Việt' ),
		'xuong6'      => array( $a . '7-20260924065723-ncp8v.png', 'Máy móc xưởng in Âu Việt' ),
		'xuong7'      => array( $a . '9-20260924065723-9zbqu.png', 'Gia công thành phẩm tại xưởng Âu Việt' ),
		'xuong8'      => array( $a . '6-20260924065723-p61lg.png', 'Xưởng sản xuất Âu Việt' ),
		// Lý do chọn Âu Việt (dùng chung).
		'ly_giao'     => array( $a . 'giao-hang--20260923073317-uhpqx.png', 'Giao hàng miễn phí' ),
		'ly_tk'       => array( $a . 'thiet-ke-mien-phi-20260923073317-qusay.jpg', 'Thiết kế miễn phí' ),
		'ly_test'     => array( $a . 'in-test-mau-20260923073316-84irs.jpg', 'In test màu miễn phí' ),
		'ly_duyet'    => array( $a . 'tai-xuong-20260923073316-sqvxq.jpg', 'Duyệt màu tại xưởng' ),
		'ly_tiendo'   => array( $a . 'pngtree-minimal-calendar-icon-vector-png-image_21233134-20260923073552--52ne.png', 'Đúng tiến độ' ),
		'ly_camket'   => array( $b . 'cam-ket-chat-luong-20200727032326-20260922084023-umuwz.png', 'Cam kết chất lượng' ),
		// Đối tác (dùng chung).
		'dt_vietin'   => array( $a . 'logo-vietinbank-vecto-01-20260924025202-loq8x.jpg', 'VietinBank' ),
		'dt_vivo'     => array( $a . 'vivo-logo-20260924025202-5kr4g.png', 'vivo' ),
		'dt_tcb'      => array( $a . 'logo-techcombank-20260924025202-bxonk.jpg', 'Techcombank' ),
		'dt_mb'       => array( $a . 'mbbank-logo-4-20260924084411-tgvhx.jpg', 'MB Bank' ),
		'dt_tanlong'  => array( $a . 'logo-tan-long-20260924025202-eb7ri.png', 'Tân Long' ),
		'dt_oppo'     => array( $a . 'oppo-logo-20260924025202--auxv.webp', 'OPPO' ),
		'dt_6'        => array( $a . '1664340606461-logo-ma-mau-moi-01-20260924030954-qgg1y.png', 'Logo đối tác' ),
		'dt_utc'      => array( $a . 'logoutc-20260924025202-gmfjz.jpg', 'Đại học Giao thông Vận tải' ),
		'dt_bv'       => array( $a . 'logo-benh-vien-20260924084411-ylyo_.jpg', 'Logo bệnh viện' ),
		'dt_viettel'  => array( $a . 'viettel--20260924084411-vswzy.jpg', 'Viettel' ),
		// Phản hồi khách hàng.
		'nx_tem'      => array( $a . 'tem-nhan-1-20261001071034-rlx1w.jpg', 'Phản hồi của khách hàng in tem nhãn' ),
		'nx_tui3'     => array( $a . 'tui-3-20261001071001-pgcyh.jpg', 'Phản hồi của khách hàng in túi giấy' ),
		'nx_hop'      => array( $a . 'hop-2-20261001071025-2nmb0.jpg', 'Phản hồi của khách hàng in hộp giấy' ),
		'nx_1'        => array( $a . '1-20261001070952-5hthz.jpg', 'Phản hồi của khách hàng Âu Việt' ),
		'nx_cata'     => array( $a . 'catalogue-2-20261001071000-ecrmw.jpg', 'Phản hồi của khách hàng in catalogue' ),
		'nx_tui2'     => array( $a . 'tui-2-20261001071025-tmlvd.jpg', 'Phản hồi của khách hàng in túi giấy' ),
		'nx_3'        => array( $a . '3-20261001070952--kozi.jpg', 'Phản hồi của khách hàng Âu Việt' ),
		'nx_tuiso'    => array( $a . 'tui-so--20261001071025-4vbs3.jpg', 'Phản hồi của khách hàng in túi giấy số lượng lớn' ),
		'nx_khen'     => array( $a . 'khen--20261001071034-jn55x.jpg', 'Khách hàng khen chất lượng in' ),
		'nx_bat'      => array( $a . 'bat-de-can-1-20261001071034-gbgeu.jpg', 'Phản hồi của khách hàng in bạt, decal' ),
		'nx_khen2'    => array( $a . 'khen-2-20261001071000-btsan.jpg', 'Khách hàng khen sản phẩm Âu Việt' ),
		// Túi giấy (intuigiaygiare.com).
		'tui_hero'    => array( $a . '489b8e01-fdcb-443f-8ff8-b2bfab14461a-20260923095534-tqf_c.png', 'Xưởng in túi giấy Âu Việt' ),
		'tui_caocap'  => array( $a . '20-20260924081556-tq68w.png', 'Túi giấy cao cấp họa tiết, quai ruy băng' ),
		'tui_dt'      => array( $a . '15-20260924081543-6r7m0.png', 'Túi giấy đựng điện thoại' ),
		'tui_traicay' => array( $a . '7-20260924081543-wkbml.png', 'Túi giấy đựng trái cây nhập khẩu' ),
		'tui_cosan'   => array( $a . '12-20260924081543-xvtgo.png', 'Túi giấy có sẵn' ),
		'tui_mypham'  => array( $a . '11-20260924081543-lv092.png', 'Túi giấy mỹ phẩm cao cấp, quai lụa' ),
		'tui_thoitr'  => array( $a . '4-20260924081526-tmbac.png', 'Túi giấy kraft in logo' ),
		'tui_hoatiet' => array( $a . '21-20260924081556-mfyou.png', 'Túi giấy họa tiết' ),
		'tui_thammy'  => array( $a . '1-20260924081526-nni_o.png', 'Túi giấy thẩm mỹ viện' ),
		'tui_hoaqua'  => array( $a . '14-20260924081543-acgvj.png', 'Túi giấy đựng hoa quả' ),
		'tui_kraft'   => array( $a . '5-20260924081526-sugnl.png', 'Túi giấy thời trang' ),
		'tui_thietke' => array( $a . '24-20260924081525-8rbwh.png', 'Túi giấy thiết kế riêng' ),
		'tui_quatang' => array( $a . '6-20260924081543-n03zy.png', 'Túi giấy quà tặng doanh nghiệp' ),
		'cl_ivory'    => array( $a . 'gay-ivory-20260923094700-v36qx.webp', 'Giấy Ivory' ),
		'cl_couche'   => array( $a . 'giay-couche-20260923094700-cgrw7.jpg', 'Giấy Couche' ),
		'cl_kraft'    => array( $a . 'giay-kraft-20260923094700-ranxv.webp', 'Giấy Kraft' ),
		'cl_mythuat'  => array( $a . 'giay-my-thuat-20260923094700-2st27.webp', 'Giấy mỹ thuật' ),
		'cl_c250'     => array( $a . 'giay-c250-20260923094700-codm3.jpg', 'Giấy C250' ),
		'cl_ford'     => array( $a . 'giay_ford_-20260923094659-0hqwp.png', 'Giấy Ford' ),
	);
}

/**
 * ID ảnh đã tải (0 nếu chưa).
 */
function av_ls_find( string $path ): int {
	$ids = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_av_src', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $path, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * Tải một ảnh về thư viện Media. Trả về ID hoặc WP_Error.
 *
 * @return int|WP_Error
 */
function av_ls_import( string $path, string $alt ) {
	$have = av_ls_find( $path );
	if ( $have ) {
		return $have;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	// Thử bản thu nhỏ 1600px rồi bản gốc; ảnh có thể nằm ở tài khoản LadiPage còn lại.
	$other = 0 === strpos( $path, AV_LS_A ) ? AV_LS_B . substr( $path, strlen( AV_LS_A ) ) : AV_LS_A . substr( $path, strlen( AV_LS_B ) );
	$tmp   = new WP_Error( 'av_ls', 'Không tải được ảnh' );
	foreach ( array( 's1600x1600/' . $path, $path, 's1600x1600/' . $other, $other ) as $try ) {
		$tmp = download_url( AV_LS_CDN . $try, 90 );
		if ( ! is_wp_error( $tmp ) ) {
			break;
		}
	}
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}
	$ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	$name = sanitize_title( remove_accents( $alt ) ) . '.' . ( 'jpeg' === $ext ? 'jpg' : $ext );
	$id   = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return $id;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_av_src', $path );
	return (int) $id;
}

/* ------------------------------------------------------------------ */
/* Dựng khối                                                           */
/* ------------------------------------------------------------------ */

/**
 * Một khối: tên, thuộc tính, phần HTML mở, khối con, phần HTML đóng.
 */
function av_ls_block( string $name, array $attrs = array(), string $open = '', array $inner = array(), string $close = '' ): array {
	$content = array( $open );
	foreach ( $inner as $i => $b ) {
		$content[] = null;
		if ( $i < count( $inner ) - 1 ) {
			$content[] = "\n\n";
		}
	}
	$content[] = $close;
	return array(
		'blockName'    => $name,
		'attrs'        => $attrs,
		'innerBlocks'  => $inner,
		'innerHTML'    => $open . $close,
		'innerContent' => $inner ? $content : array( $open . $close ),
	);
}

function av_ls_p( string $html, string $class = '' ): array {
	return av_ls_block( 'core/paragraph', $class ? array( 'className' => $class ) : array(), '<p' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . $html . '</p>' );
}

function av_ls_h( string $text, int $level = 2, string $class = '', string $anchor = '' ): array {
	$attrs = array();
	if ( 2 !== $level ) {
		$attrs['level'] = $level;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	if ( $anchor ) {
		$attrs['anchor'] = $anchor;
	}
	return av_ls_block( 'core/heading', $attrs, sprintf( '<h%1$d class="wp-block-heading%2$s"%3$s>%4$s</h%1$d>', $level, $class ? ' ' . esc_attr( $class ) : '', $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '', esc_html( $text ) ) );
}

function av_ls_list( array $items, string $class = '' ): array {
	$li = array();
	foreach ( $items as $it ) {
		$li[] = av_ls_block( 'core/list-item', array(), '<li>' . $it . '</li>' );
	}
	return av_ls_block( 'core/list', $class ? array( 'className' => $class ) : array(), '<ul class="wp-block-list' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '">', $li, '</ul>' );
}

function av_ls_group( array $inner, string $class, array $layout = array(), string $anchor = '' ): array {
	$attrs = array( 'className' => $class );
	if ( $anchor ) {
		$attrs = array( 'anchor' => $anchor ) + $attrs;
	}
	if ( $layout ) {
		$attrs['layout'] = $layout;
	}
	return av_ls_block( 'core/group', $attrs, '<div' . ( $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . ' class="wp-block-group ' . esc_attr( $class ) . '">', $inner, '</div>' );
}

function av_ls_grid( array $inner, string $class, string $min = '240px' ): array {
	return av_ls_group( $inner, $class, array( 'type' => 'grid', 'minimumColumnWidth' => $min ) );
}

/**
 * Ảnh đơn. $size: large | medium_large | medium.
 */
function av_ls_img( int $id, string $size = 'large', string $caption = '', bool $lightbox = false ): array {
	$src   = wp_get_attachment_image_url( $id, $size ) ?: wp_get_attachment_image_url( $id, 'full' );
	$alt   = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
	$attrs = array( 'id' => $id, 'sizeSlug' => $size, 'linkDestination' => 'none' );
	if ( $lightbox ) {
		$attrs['lightbox'] = array( 'enabled' => true );
	}
	$html = sprintf(
		'<figure class="wp-block-image size-%1$s"><img src="%2$s" alt="%3$s" class="wp-image-%4$d"/>%5$s</figure>',
		esc_attr( $size ),
		esc_url( (string) $src ),
		esc_attr( $alt ),
		$id,
		$caption ? '<figcaption class="wp-element-caption">' . esc_html( $caption ) . '</figcaption>' : ''
	);
	return av_ls_block( 'core/image', $attrs, $html );
}

/**
 * Thư viện ảnh. $items: [ [id, chú thích], … ].
 */
function av_ls_gallery( array $items, int $cols, string $class, bool $crop = true, bool $lightbox = false, string $size = 'large' ): array {
	$inner = array();
	foreach ( $items as $it ) {
		$inner[] = av_ls_img( (int) $it[0], $size, (string) ( $it[1] ?? '' ), $lightbox );
	}
	$attrs = array( 'columns' => $cols, 'linkTo' => 'none', 'sizeSlug' => $size, 'className' => $class );
	if ( ! $crop ) {
		$attrs['imageCrop'] = false;
	}
	return av_ls_block(
		'core/gallery',
		$attrs,
		sprintf( '<figure class="wp-block-gallery has-nested-images columns-%d%s %s">', $cols, $crop ? ' is-cropped' : '', esc_attr( $class ) ),
		$inner,
		'</figure>'
	);
}

function av_ls_buttons( array $btns ): array {
	$inner = array();
	foreach ( $btns as $b ) {
		$outline = ! empty( $b[2] );
		$inner[] = av_ls_block(
			'core/button',
			$outline ? array( 'className' => 'is-style-outline' ) : array(),
			sprintf( '<div class="wp-block-button%s"><a class="wp-block-button__link wp-element-button" href="%s">%s</a></div>', $outline ? ' is-style-outline' : '', esc_attr( $b[1] ), esc_html( $b[0] ) )
		);
	}
	return av_ls_block( 'core/buttons', array( 'className' => 'av-hero-cta' ), '<div class="wp-block-buttons av-hero-cta">', $inner, '</div>' );
}

function av_ls_dyn( string $name, array $attrs = array() ): array {
	return av_ls_block( $name, $attrs );
}

/**
 * Đầu mỗi phần: dòng nhỏ + tiêu đề + đoạn dẫn.
 */
function av_ls_head( string $kicker, string $title, string $lead = '', string $anchor = '' ): array {
	$out = array();
	if ( $kicker ) {
		$out[] = av_ls_p( esc_html( $kicker ), 'av-lsec__kicker' );
	}
	$out[] = av_ls_h( $title, 2, 'av-lsec__title', $anchor );
	if ( $lead ) {
		$out[] = av_ls_p( esc_html( $lead ), 'av-lsec__lead' );
	}
	return $out;
}

function av_ls_section( string $class, array $blocks, string $anchor = '' ): array {
	return av_ls_group( $blocks, 'av-lsec ' . $class, array(), $anchor );
}

/* ------------------------------------------------------------------ */
/* Phần dùng chung cho cả hai landing                                  */
/* ------------------------------------------------------------------ */

function av_ls_badges( array $items ): array {
	$cards = array();
	foreach ( $items as $t ) {
		$cards[] = av_ls_p( '<strong>' . esc_html( $t ) . '</strong>', 'av-lbadge' );
	}
	return av_ls_grid( $cards, 'av-lbadges', '220px' );
}

function av_ls_factory( array $img ): array {
	$items = array();
	for ( $i = 1; $i <= 8; $i++ ) {
		$items[] = array( $img[ 'xuong' . $i ], '' );
	}
	return av_ls_gallery( $items, 4, 'av-lfactory', true, true );
}

function av_ls_reasons( array $img ): array {
	$cards = array();
	foreach (
		array(
			'ly_giao'   => array( 'Giao hàng miễn phí', 'Giao tận nơi, đóng gói cẩn thận.' ),
			'ly_tk'     => array( 'Thiết kế miễn phí', 'Đội thiết kế lên mẫu theo nhận diện thương hiệu.' ),
			'ly_test'   => array( 'In test màu miễn phí', 'Kiểm tra màu, hình ảnh trước khi in số lượng lớn.' ),
			'ly_duyet'  => array( 'Duyệt màu tại xưởng', 'Khách có thể đến xưởng duyệt màu trực tiếp.' ),
			'ly_tiendo' => array( 'Đúng tiến độ', 'Chủ động sản xuất tại xưởng, giao đúng hẹn.' ),
			'ly_camket' => array( 'Cam kết chất lượng', 'Kiểm tra thành phẩm trước khi giao.' ),
		) as $k => $t
	) {
		$cards[] = av_ls_group( array( av_ls_img( $img[ $k ], 'medium' ), av_ls_h( $t[0], 3 ), av_ls_p( esc_html( $t[1] ) ) ), 'av-lreason' );
	}
	return av_ls_section( 'av-lsec--reasons', array_merge( av_ls_head( '', 'Tại sao nên chọn Âu Việt' ), array( av_ls_grid( $cards, 'av-lreasons', '260px' ) ) ) );
}

function av_ls_partners( array $img ): array {
	$logos = array();
	foreach ( array( 'dt_vietin', 'dt_tcb', 'dt_mb', 'dt_viettel', 'dt_oppo', 'dt_vivo', 'dt_tanlong', 'dt_utc', 'dt_bv', 'dt_6' ) as $k ) {
		$logos[] = av_ls_img( $img[ $k ], 'medium' );
	}
	return av_ls_section( 'av-lsec--partners', array_merge( av_ls_head( '', 'Các đối tác của Âu Việt' ), array( av_ls_grid( $logos, 'av-llogos', '140px' ) ) ) );
}

function av_ls_reviews( array $img, array $keys ): array {
	$items = array();
	foreach ( $keys as $k ) {
		$items[] = array( $img[ $k ], '' );
	}
	return av_ls_section(
		'av-lsec--reviews',
		array_merge(
			av_ls_head( 'Phản hồi của khách', 'Nhận xét của khách hàng', 'Sự hài lòng của khách hàng là ưu tiên số 1 của Âu Việt.' ),
			array( av_ls_gallery( $items, 3, 'av-lreviews', false, true, 'medium_large' ) )
		)
	);
}

/* ------------------------------------------------------------------ */
/* Nội dung 2 landing                                                  */
/* ------------------------------------------------------------------ */

function av_ls_landings(): array {
	$out = array(
		'lich' => array(
			'slug'     => 'in-lich-tet',
			'title'    => 'In lịch Tết theo yêu cầu tại Hà Nội',
			'excerpt'  => 'Công ty Cổ phần In Quảng Cáo Âu Việt nhận in lịch Tết theo yêu cầu với đa dạng mẫu mã, kích thước và chất liệu. Hỗ trợ thiết kế, in số lượng từ ít đến lớn, dùng làm quà tặng cho doanh nghiệp, cửa hàng, tổ chức.',
			'season'   => 'Lịch doanh nghiệp · Quà tặng Tết 2027',
			'products' => array( 'in-lich-de-ban', 'in-lich-treo-tuong', 'in-lich-bloc' ),
			'group'    => 'qua-tang-theo-mua',
			'hero'     => 'lich_hero',
			'build'    => 'av_ls_build_lich',
		),
		'tui'  => array(
			'slug'     => 'in-tui-giay',
			'title'    => 'Xưởng in túi giấy theo yêu cầu, giá gốc tận xưởng',
			'excerpt'  => 'Âu Việt thiết kế và sản xuất túi giấy theo yêu cầu cho doanh nghiệp, cửa hàng và thương hiệu: Kraft, Ivory, Couche, C250, giấy mỹ thuật. Giá gốc tận xưởng, tối ưu ngân sách cho doanh nghiệp.',
			'season'   => 'Xưởng in túi giấy Âu Việt',
			'products' => array( 'in-tui-giay', 'in-tui-giay-kraft' ),
			'group'    => 'bao-bi',
			'hero'     => 'tui_hero',
			'build'    => 'av_ls_build_tui',
		),
	);
	foreach ( av_ls_product_landings() as $key => $c ) {
		$out[ $key ] = array(
			'slug'     => $c['slug'],
			'title'    => $c['title'],
			'excerpt'  => $c['excerpt'],
			'season'   => $c['season'],
			'products' => $c['products'],
			'group'    => $c['group'],
			'hero'     => '',
			'build'    => 'av_ls_build_product',
			'cfg'      => $c,
		);
	}
	return $out;
}

/**
 * Dựng khối của một landing (landing sản phẩm nhận thêm cấu hình).
 */
function av_ls_run_build( array $def, array $img ): array {
	return isset( $def['cfg'] ) ? call_user_func( $def['build'], $img, $def['cfg'] ) : call_user_func( $def['build'], $img );
}

function av_ls_build_lich( array $img ): array {
	$b = array();

	$b[] = av_ls_dyn( 'auviet/landing-hero' );
	$b[] = av_ls_grid(
		array(
			av_ls_p( '<strong>500+</strong> Doanh nghiệp tin chọn', 'av-lstat' ),
			av_ls_p( '<strong>100%</strong> Đúng tiến độ cam kết', 'av-lstat' ),
			av_ls_p( '<strong>0 đ</strong> In test mẫu thật tận nơi', 'av-lstat' ),
		),
		'av-lstats',
		'200px'
	);
	$b[] = av_ls_dyn( 'auviet/landing-deadline' );

	$gal = array();
	foreach ( array( 'lich_hero', 'lich_g1', 'lich_g2', 'lich_g3', 'lich_g4', 'lich_g5', 'lich_g6', 'lich_g7', 'lich_g8', 'lich_g9', 'lich_g10', 'lich_13to' ) as $k ) {
		$gal[] = array( $img[ $k ], '' );
	}
	$b[] = av_ls_section( 'av-lsec--collection', array_merge( av_ls_head( 'Thiết kế đẹp – In sắc nét – Giá hợp lý', 'Bộ sưu tập lịch Tết Âu Việt', '', 'bo-suu-tap' ), array( av_ls_gallery( $gal, 4, 'av-lcollection', true, true ) ) ) );

	$b[] = av_ls_section(
		'av-lsec--about',
		array_merge(
			av_ls_head( '', 'Về In Quảng Cáo Âu Việt' ),
			array(
				av_ls_badges( array( 'Thời gian nhận hàng 4–5 ngày', 'Thiết kế, tư vấn, in test màu miễn phí', 'Chuyên in số lượng lớn theo yêu cầu' ) ),
				av_ls_p( 'Công ty Cổ phần In Quảng Cáo Âu Việt được thành lập từ năm 2011, có nhiều năm kinh nghiệm trong lĩnh vực thiết kế, in ấn và sản xuất lịch Tết theo yêu cầu cho doanh nghiệp, tổ chức và thương hiệu.' ),
				av_ls_p( 'Với xưởng in và sản xuất trực tiếp, Âu Việt chủ động từ khâu tư vấn, thiết kế, in ấn đến gia công và hoàn thiện, giúp kiểm soát chất lượng, tiến độ và tối ưu chi phí cho từng đơn hàng.' ),
				av_ls_p( 'Âu Việt cung cấp đa dạng các dòng lịch Tết như lịch bloc, lịch treo tường, lịch để bàn, lịch lò xo, lịch độc quyền theo thương hiệu, với nhiều mẫu mã, kích thước và quy cách gia công.' ),
				av_ls_p( 'Không chỉ là sản phẩm sử dụng trong năm mới, lịch Tết còn là món quà tri ân và công cụ quảng bá thương hiệu hiệu quả, giúp doanh nghiệp duy trì sự hiện diện trong không gian của khách hàng suốt cả năm.' ),
				av_ls_p( '<strong>Âu Việt – Chỉn chu từng cuốn lịch, trọn vẹn dấu ấn thương hiệu.</strong>' ),
				av_ls_factory( $img ),
			)
		)
	);

	$points = array(
		array( '01', 'Thiết kế theo yêu cầu', 'Phù hợp với hình ảnh thương hiệu', 'Hỗ trợ thiết kế mẫu lịch theo nội dung, hình ảnh và thông tin của doanh nghiệp. Có thể chỉnh sửa để phù hợp với nhu cầu sử dụng thực tế.', array( 'Hỗ trợ lên ý tưởng thiết kế', 'Chỉnh sửa nội dung theo yêu cầu' ) ),
		array( '02', 'Đa dạng mẫu lịch', 'Nhiều kiểu dáng, kích thước', 'Nhiều mẫu lịch Tết với kiểu dáng và quy cách khác nhau, phù hợp dùng tại văn phòng, cửa hàng, gia đình hoặc làm quà tặng.', array( 'Nhiều mẫu lịch để lựa chọn', 'Tư vấn kích thước phù hợp' ) ),
		array( '03', 'Giá cả cạnh tranh', 'Tối ưu chi phí theo số lượng', 'Báo giá dựa trên mẫu mã, kích thước và số lượng thực tế. Đặt số lượng càng phù hợp, chi phí in trên từng sản phẩm càng được tối ưu.', array( 'Miễn phí thiết kế từ 300 cuốn', 'Báo giá rõ ràng, có ưu đãi theo số lượng' ) ),
		array( '04', 'Giao hàng đúng tiến độ', 'Giao đúng hẹn, kịp mùa Tết', 'Cam kết tiến độ, đóng màng co từng cuốn kèm thùng carton 5 lớp, giao tận nơi.', array( 'Chủ động thời gian sản xuất', 'Giao hàng toàn quốc' ) ),
	);
	$cards  = array();
	foreach ( $points as $p ) {
		$cards[] = av_ls_group( array( av_ls_p( esc_html( $p[0] ), 'av-lcard__num' ), av_ls_h( $p[1], 3 ), av_ls_p( esc_html( $p[2] ), 'av-lcard__sub' ), av_ls_p( esc_html( $p[3] ) ), av_ls_list( array_map( 'esc_html', $p[4] ), 'av-lchecks' ) ), 'av-lcard' );
	}
	$b[] = av_ls_section( 'av-lsec--points', array_merge( av_ls_head( 'Tiêu chuẩn in ấn Âu Việt', '4 điểm nổi bật khi in lịch Tết tại Âu Việt', 'In đẹp, dễ sử dụng, phù hợp nhu cầu làm lịch Tết cho doanh nghiệp, cửa hàng và khách hàng.', 'uu-diem' ), array( av_ls_grid( $cards, 'av-lcards', '240px' ) ) ) );

	$done = array();
	foreach ( array( 'lich_g10' => 'Lịch treo tường 1 tờ', 'lich_g8' => 'Lịch treo tường 5 tờ / 7 tờ', 'lich_13to' => 'Lịch treo tường 13 tờ', 'lich_a_done' => 'Lịch để bàn chữ A', 'lich_m_done' => 'Lịch để bàn chữ M', 'lich_bloc1' => 'Lịch bloc' ) as $k => $cap ) {
		$done[] = array( $img[ $k ], $cap );
	}
	$b[] = av_ls_section( 'av-lsec--done', array_merge( av_ls_head( '', 'Sản phẩm lịch Tết đã thực hiện' ), array( av_ls_gallery( $done, 3, 'av-ldone', true, true ) ) ) );

	$lines = array(
		array( 'lich_a', 'Lịch để bàn chữ A', 'Mẫu lịch nhỏ gọn, dễ đặt trên bàn làm việc, quầy lễ tân hoặc bàn tiếp khách. Có thể in logo, hình ảnh và thông tin doanh nghiệp theo yêu cầu.', '16 × 24 cm (chuẩn văn phòng)', 'Giấy Couche 300 gsm, đế carton bồi 3 lớp' ),
		array( 'lich_57', 'Lịch treo tường 5 tờ – 7 tờ', 'Lịch treo tường nhiều tờ giúp trình bày hình ảnh và thông tin theo từng tháng. Phù hợp dùng tại văn phòng, cửa hàng hoặc làm quà tặng khách hàng dịp Tết.', '40 × 60 cm hoặc 35 × 70 cm', 'Giấy Couche, in màu rõ nét' ),
		array( 'lich_bloc', 'Lịch bloc treo tường', 'Lịch bloc quen thuộc, dễ sử dụng, phù hợp treo tại gia đình, văn phòng hoặc làm quà tặng khách hàng dịp Tết.', '20 × 30 cm (khổ bloc phổ biến)', 'Giấy Couche, khánh lịch hoặc đế carton tùy mẫu' ),
		array( 'lich_m', 'Lịch để bàn chữ M', 'Thiết kế nhỏ gọn, tiện đặt trên bàn làm việc, quầy lễ tân hoặc bàn tiếp khách. Có thể in logo và thông tin doanh nghiệp theo yêu cầu.', '15 × 24 cm (khổ phổ biến)', 'Giấy Couche, đế carton bồi' ),
	);
	$cards = array();
	foreach ( $lines as $l ) {
		$cards[] = av_ls_group(
			array(
				av_ls_img( $img[ $l[0] ], 'medium_large' ),
				av_ls_p( 'Phổ biến nhất', 'av-lline__tag' ),
				av_ls_h( $l[1], 3 ),
				av_ls_p( esc_html( $l[2] ) ),
				av_ls_list( array( '<strong>Kích thước:</strong> ' . esc_html( $l[3] ), '<strong>Chất liệu:</strong> ' . esc_html( $l[4] ), '<strong>Đặt tối thiểu:</strong> 100 cuốn' ), 'av-lspec' ),
				av_ls_buttons( array( array( 'Đăng ký nhận mẫu thử', '#bao-gia' ) ) ),
			),
			'av-lline'
		);
	}
	$b[] = av_ls_section( 'av-lsec--lines', array_merge( av_ls_head( 'Bộ sưu tập quà tặng doanh nghiệp 2027', 'Các dòng lịch thịnh hành nhất hiện nay', 'Mỗi dòng sản phẩm được thiết kế riêng cho từng mục đích sử dụng và phong cách nhận diện thương hiệu.', 'mau-lich' ), array( av_ls_grid( $cards, 'av-llines', '300px' ) ) ) );

	$steps = array(
		array( 'Trong 30 phút', 'Tư vấn & báo giá', 'Bạn gửi mẫu lịch, kích thước hoặc số lượng cần in. Âu Việt tư vấn loại lịch phù hợp và báo giá theo nhu cầu.' ),
		array( '2–3 ngày', 'Duyệt thiết kế', 'Đội ngũ thiết kế lên mẫu lịch dựa trên nội dung, hình ảnh và thông tin của khách hàng. Có thể chỉnh sửa trước khi chốt mẫu.' ),
		array( '1–2 ngày', 'In mẫu thử (sample)', 'Với đơn cần in mẫu, Âu Việt in thử để khách kiểm tra màu sắc, hình ảnh và nội dung trước khi in chính thức.' ),
		array( '4–5 ngày', 'Sản xuất & giao hàng', 'Sau khi duyệt mẫu, Âu Việt in và gia công đúng yêu cầu. Sản phẩm được kiểm tra, đóng gói và giao đến khách hàng.' ),
	);
	$li = array();
	foreach ( $steps as $s ) {
		$li[] = av_ls_block( 'core/list-item', array(), '<li><strong>' . esc_html( $s[1] ) . '</strong><em>' . esc_html( $s[0] ) . '</em>' . esc_html( $s[2] ) . '</li>' );
	}
	$b[] = av_ls_section( 'av-lsec--steps', array_merge( av_ls_head( 'Quy trình 4 bước', 'Quy trình in lịch Tết tại Âu Việt', 'Đồng hành cùng bạn từ khâu tư vấn, thiết kế đến in ấn và giao hàng.', 'quy-trinh' ), array( av_ls_block( 'core/list', array( 'ordered' => true, 'className' => 'av-chain av-lsteps' ), '<ol class="wp-block-list av-chain av-lsteps">', $li, '</ol>' ) ) ) );

	$b[] = av_ls_partners( $img );
	$b[] = av_ls_reasons( $img );
	$b[] = av_ls_reviews( $img, array( 'nx_tem', 'nx_tui3', 'nx_hop', 'nx_1', 'nx_cata', 'nx_khen' ) );
	$b[] = av_ls_dyn( 'auviet/landing-faq' );
	$b[] = av_ls_dyn(
		'auviet/landing-quote',
		array(
			'title' => 'Gửi yêu cầu & nhận bộ mẫu thử miễn phí',
			'text'  => 'Nhắn Zalo hoặc gọi, chuyên viên Âu Việt gửi bảng báo giá chi tiết và hộp mẫu thử đến Quý doanh nghiệp. Phản hồi trong 15 phút giờ hành chính.',
		)
	);
	return $b;
}

function av_ls_build_tui( array $img ): array {
	$b = array();

	$b[] = av_ls_dyn( 'auviet/landing-hero' );
	$b[] = av_ls_p( '<strong>Cam kết hoàn tiền 100%</strong> nếu sản phẩm không ưng ý.', 'av-lpromise' );

	$b[] = av_ls_section(
		'av-lsec--about',
		array_merge(
			av_ls_head( '', 'Về túi giấy Âu Việt' ),
			array(
				av_ls_badges( array( 'Thời gian nhận hàng 4–5 ngày', 'Thiết kế, tư vấn, in test màu miễn phí', 'Chuyên in số lượng lớn theo yêu cầu' ) ),
				av_ls_list(
					array(
						'In Quảng Cáo Âu Việt được thành lập từ năm 2011, chuyên thiết kế và sản xuất túi giấy theo yêu cầu cho doanh nghiệp, cửa hàng và thương hiệu.',
						'Với xưởng in và sản xuất trực tiếp, Âu Việt chủ động từ thiết kế, in ấn đến gia công hoàn thiện, giúp kiểm soát chất lượng, quy cách và tối ưu chi phí cho từng đơn hàng.',
						'Túi được sản xuất trên các chất liệu Kraft, Ivory, Couche, C250, giấy mỹ thuật… kết hợp nhiều kiểu dáng, kích thước và kỹ thuật gia công, từ túi phổ thông đến túi cao cấp, góp phần nâng tầm hình ảnh thương hiệu.',
					),
					'av-lchecks'
				),
				av_ls_factory( $img ),
			)
		)
	);

	$done = array();
	foreach ( array( 'tui_dt' => 'Túi giấy điện thoại', 'tui_traicay' => 'Túi giấy trái cây', 'tui_cosan' => 'Túi giấy có sẵn', 'tui_caocap' => 'Túi giấy cao cấp', 'tui_thoitr' => 'Túi giấy thời trang', 'tui_mypham' => 'Túi giấy mỹ phẩm' ) as $k => $cap ) {
		$done[] = array( $img[ $k ], $cap );
	}
	$b[] = av_ls_section( 'av-lsec--done', array_merge( av_ls_head( '', 'Sản phẩm in túi giấy đã thực hiện', '', 'san-pham' ), array( av_ls_gallery( $done, 3, 'av-ldone', true, true ) ) ) );

	$mat = array();
	foreach ( array( 'cl_ivory' => 'Giấy Ivory', 'cl_couche' => 'Giấy Couche', 'cl_kraft' => 'Giấy Kraft', 'cl_mythuat' => 'Giấy mỹ thuật', 'cl_c250' => 'Giấy C250', 'cl_ford' => 'Giấy Ford' ) as $k => $cap ) {
		$mat[] = array( $img[ $k ], $cap );
	}
	$b[] = av_ls_section( 'av-lsec--materials', array_merge( av_ls_head( '', 'Các loại chất liệu', 'Mỗi loại giấy phù hợp một mức chi phí đầu tư khác nhau; Âu Việt tư vấn chất liệu theo mục đích dùng và ngân sách.' ), array( av_ls_gallery( $mat, 6, 'av-lmaterials', true, false, 'medium' ) ) ) );

	$all = array();
	foreach (
		array(
			'tui_caocap'  => 'In túi xách – túi giấy',
			'tui_traicay' => 'Túi giấy hoa quả',
			'tui_hoatiet' => 'Túi giấy họa tiết',
			'tui_mypham'  => 'Túi giấy mỹ phẩm',
			'tui_dt'      => 'Túi giấy điện thoại',
			'tui_thoitr'  => 'Túi giấy kraft',
			'tui_thammy'  => 'Túi giấy thẩm mỹ',
			'tui_cosan'   => 'Túi giấy có sẵn',
			'tui_hoaqua'  => 'Túi giấy trái cây',
			'tui_kraft'   => 'Túi giấy thời trang',
			'tui_thietke' => 'Túi giấy thiết kế',
			'tui_quatang' => 'Túi giấy quà tặng',
		) as $k => $cap
	) {
		$all[] = array( $img[ $k ], $cap );
	}
	$b[] = av_ls_section( 'av-lsec--range', array_merge( av_ls_head( '', 'Sản phẩm đa dạng, đáp ứng mọi nhu cầu' ), array( av_ls_gallery( $all, 4, 'av-lrange', true, true ) ) ) );

	$b[] = av_ls_partners( $img );
	$b[] = av_ls_reasons( $img );
	$b[] = av_ls_reviews( $img, array( 'nx_1', 'nx_tui3', 'nx_tui2', 'nx_3', 'nx_tuiso', 'nx_khen', 'nx_bat', 'nx_khen2' ) );

	$b[] = av_ls_section(
		'av-lsec--offer',
		array(
			av_ls_p( 'Ưu đãi đặc biệt', 'av-lsec__kicker' ),
			av_ls_h( 'Đặt túi giấy có sẵn số lượng lớn, nhận ngay quà tặng thương hiệu', 2, 'av-lsec__title' ),
			av_ls_p( '<strong>Tặng in logo + tag treo</strong> · Cam kết hoàn tiền 100% nếu sản phẩm không ưng ý.', 'av-lsec__lead' ),
			av_ls_buttons( array( array( 'Nhận tư vấn báo giá ngay', '#bao-gia' ) ) ),
		)
	);
	$b[] = av_ls_dyn( 'auviet/landing-faq' );
	$b[] = av_ls_dyn(
		'auviet/landing-quote',
		array(
			'title' => 'Báo giá nhận ưu đãi',
			'text'  => 'Nhắn Zalo hoặc gọi, chuyên viên Âu Việt gửi bảng báo giá chi tiết. Sản xuất trực tiếp, chất lượng đảm bảo, chi phí hợp lý.',
		)
	);
	return $b;
}

/* ------------------------------------------------------------------ */
/* Tạo / dựng lại trang                                                */
/* ------------------------------------------------------------------ */

/**
 * @return int|WP_Error ID trang.
 */
function av_ls_create_page( string $key, bool $rebuild = false ) {
	$def = av_ls_landings()[ $key ] ?? null;
	if ( ! $def ) {
		return new WP_Error( 'av_ls', 'Không có landing ' . $key );
	}
	$img = array();
	foreach ( av_ls_images() as $k => $v ) {
		$img[ $k ] = av_ls_find( $v[0] );
	}
	$missing = array_keys( array_filter( $img, static fn( $id ) => ! $id ) );
	$need    = av_ls_used_keys( $key );
	if ( array_intersect( $missing, $need ) ) {
		return new WP_Error( 'av_ls', 'Còn ảnh chưa tải: ' . implode( ', ', array_intersect( $missing, $need ) ) );
	}

	$existing = get_page_by_path( $def['slug'] );
	if ( $existing && ! $rebuild ) {
		return (int) $existing->ID;
	}
	$products = array();
	foreach ( $def['products'] as $slug ) {
		$p = get_page_by_path( $slug, OBJECT, 'av_product' );
		if ( $p && 'publish' === $p->post_status ) {
			$products[] = (int) $p->ID;
		}
	}
	if ( ! $def['hero'] && ! $products ) {
		return new WP_Error( 'av_ls_skip', 'Chưa có sản phẩm ' . implode( ', ', $def['products'] ) );
	}
	$content = serialize_blocks( av_ls_run_build( $def, $img ) );
	$data    = array(
		'post_type'    => 'page',
		'post_title'   => $def['title'],
		'post_excerpt' => $def['excerpt'],
		'post_content' => $content,
	);
	kses_remove_filters(); // Nội dung do code dựng (tin cậy).
	if ( $existing ) {
		$data['ID'] = $existing->ID;
		$id         = wp_update_post( wp_slash( $data ), true );
	} else {
		$data += array( 'post_status' => 'publish', 'post_name' => $def['slug'] );
		$id    = wp_insert_post( wp_slash( $data ), true );
	}
	kses_init();
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	update_post_meta( $id, '_av_landing', '1' );
	update_post_meta( $id, '_av_seed', $key );
	update_post_meta( $id, '_av_land_season', $def['season'] );
	$thumb = $def['hero'] ? $img[ $def['hero'] ] : (int) get_post_thumbnail_id( $products[0] );
	if ( $thumb ) {
		update_post_meta( $id, '_thumbnail_id', $thumb );
	}
	update_post_meta( $id, '_av_land_products', $products );
	$g = get_term_by( 'slug', $def['group'], 'av_group' );
	if ( $g ) {
		update_post_meta( $id, '_av_land_group', (int) $g->term_id );
	}
	if ( 'lich' === $key ) {
		// Banner mùa đang trỏ về nhóm quà tặng (mặc định): chuyển sang landing lịch Tết.
		$c = (array) get_option( 'av_campaign', array() );
		if ( ! isset( $c['url'] ) || '/danh-muc/qua-tang-theo-mua/' === $c['url'] ) {
			$c['url'] = '/' . $def['slug'] . '/';
			update_option( 'av_campaign', $c );
		}
	}
	if ( function_exists( 'av_purge_page_cache' ) ) {
		av_purge_page_cache();
	}
	av_log( '{action} landing "{title}" từ trang cũ', array( 'action' => $existing ? 'Dựng lại' : 'Tạo', 'title' => $def['title'] ), 'notice' );
	return (int) $id;
}

/**
 * Khoá ảnh mà một landing dùng (dựng thử với ID giả để biết).
 */
function av_ls_used_keys( string $key ): array {
	static $cache = array();
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$all  = array_keys( av_ls_images() );
	$used = array();
	$fake = array();
	foreach ( $all as $i => $k ) {
		$fake[ $k ] = 900000 + $i;
	}
	$json = wp_json_encode( av_ls_run_build( av_ls_landings()[ $key ], $fake ) );
	foreach ( $fake as $k => $n ) {
		if ( false !== strpos( (string) $json, '"id":' . $n ) || false !== strpos( (string) $json, 'wp-image-' . $n ) ) {
			$used[] = $k;
		}
	}
	if ( av_ls_landings()[ $key ]['hero'] ) {
		$used[] = av_ls_landings()[ $key ]['hero'];
	}
	$cache[ $key ] = array_values( array_unique( $used ) );
	return $cache[ $key ];
}

/* ------------------------------------------------------------------ */
/* Âu Việt › Landing theo mùa: nút "Tạo 2 landing từ trang cũ"         */
/* ------------------------------------------------------------------ */

function av_render_landing_seed_box(): void {
	if ( ! current_user_can( 'publish_pages' ) || ! current_user_can( 'upload_files' ) ) {
		return;
	}
	$pages = array();
	foreach ( av_ls_landings() as $k => $d ) {
		$p           = get_page_by_path( $d['slug'] );
		$pages[ $k ] = $p ? $p->ID : 0;
	}
	$total = count( av_ls_images() );
	$have  = 0;
	foreach ( av_ls_images() as $v ) {
		$have += av_ls_find( $v[0] ) ? 1 : 0;
	}
	?>
	<div class="card av-ls-box" style="max-width:none;margin:16px 0">
		<h2 style="margin-top:0">Tạo các landing có sẵn nội dung</h2>
		<p>Lịch Tết, túi giấy dựng lại từ <a href="https://www.inlichdep.vn" target="_blank" rel="noopener">inlichdep.vn</a>, <a href="https://www.intuigiaygiare.com" target="_blank" rel="noopener">intuigiaygiare.com</a>; sách, catalogue, hộp giấy, phong bì, lì xì, bạt dựng từ nội dung sản phẩm đã có trên web.
			Máy chủ tự tải <?php echo (int) $total; ?> ảnh gốc về thư viện Media (đã có <?php echo (int) $have; ?>), mất khoảng 1–3 phút.</p>
		<ul style="columns:2 260px;margin:0 0 12px">
			<?php foreach ( av_ls_landings() as $k => $d ) : ?>
				<li><code>/<?php echo esc_html( $d['slug'] ); ?>/</code> <?php echo esc_html( $d['title'] ); ?><?php echo $pages[ $k ] ? ' <strong>(đã có)</strong>' : ''; ?></li>
			<?php endforeach; ?>
		</ul>
		<p>
			<button type="button" class="button button-primary" data-av-ls="create"><?php echo array_filter( $pages, static fn( $v ) => ! $v ) ? 'Tạo các landing còn thiếu' : 'Tải lại ảnh còn thiếu'; ?></button>
			<?php if ( array_filter( $pages ) ) : ?>
				<label style="margin-left:12px"><input type="checkbox" data-av-ls-rebuild> Dựng lại nội dung các landing đã có (mọi chỉnh sửa tay trên các trang này sẽ mất)</label>
			<?php endif; ?>
		</p>
		<p class="av-ls-status" role="status" aria-live="polite"></p>
	</div>
	<script>
	( function () {
		var btn = document.querySelector( '[data-av-ls]' );
		var out = document.querySelector( '.av-ls-status' );
		var nonce = <?php echo wp_json_encode( wp_create_nonce( 'av_landing_seed' ) ); ?>;
		function call( step ) {
			var body = new FormData();
			body.append( 'action', 'av_landing_seed' );
			body.append( 'step', step );
			body.append( '_wpnonce', nonce );
			var rb = document.querySelector( '[data-av-ls-rebuild]' );
			body.append( 'rebuild', rb && rb.checked ? '1' : '0' );
			return fetch( ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' } ).then( function ( r ) { return r.json(); } );
		}
		function run() {
			call( 'images' ).then( function ( r ) {
				if ( ! r.success ) { throw new Error( r.data || 'Lỗi' ); }
				out.textContent = 'Đã tải ' + r.data.have + '/' + r.data.total + ' ảnh' + ( r.data.errors.length ? ' · lỗi: ' + r.data.errors.join( '; ' ) : '' );
				if ( r.data.more ) { return run(); }
				out.textContent += ' · đang dựng trang…';
				return call( 'pages' ).then( function ( p ) {
					if ( ! p.success ) { throw new Error( p.data || 'Lỗi' ); }
					out.innerHTML = 'Xong. ' + p.data.links;
					btn.disabled = false;
				} );
			} ).catch( function ( e ) { out.textContent = 'Chưa xong: ' + e.message + '. Bấm lại để tiếp tục.'; btn.disabled = false; } );
		}
		btn.addEventListener( 'click', function () { btn.disabled = true; out.textContent = 'Đang tải ảnh…'; run(); } );
	} )();
	</script>
	<?php
}

add_action(
	'wp_ajax_av_landing_seed',
	static function () {
		check_ajax_referer( 'av_landing_seed' );
		if ( ! current_user_can( 'publish_pages' ) || ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Không có quyền.' );
		}
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
		$step = sanitize_key( wp_unslash( $_POST['step'] ?? '' ) );
		if ( 'images' === $step ) {
			$errors = array();
			$done   = 0;
			$start  = time();
			$failed = (array) get_transient( 'av_ls_failed' );
			foreach ( av_ls_images() as $k => $v ) {
				if ( av_ls_find( $v[0] ) || in_array( $k, $failed, true ) ) {
					continue;
				}
				if ( $done >= 6 || time() - $start > 40 ) {
					break;
				}
				$r = av_ls_import( $v[0], $v[1] );
				if ( is_wp_error( $r ) ) {
					$failed[] = $k;
					$errors[] = $v[1] . ': ' . $r->get_error_message();
				}
				++$done;
			}
			set_transient( 'av_ls_failed', $failed, 10 * MINUTE_IN_SECONDS );
			$have  = 0;
			$left  = 0;
			foreach ( av_ls_images() as $k => $v ) {
				if ( av_ls_find( $v[0] ) ) {
					++$have;
				} elseif ( ! in_array( $k, $failed, true ) ) {
					++$left;
				}
			}
			if ( ! $left ) {
				delete_transient( 'av_ls_failed' );
			}
			wp_send_json_success( array( 'have' => $have, 'total' => count( av_ls_images() ), 'more' => $left > 0, 'errors' => $errors ) );
		}
		if ( 'pages' === $step ) {
			$rebuild = '1' === ( $_POST['rebuild'] ?? '' );
			$links   = array();
			foreach ( array_keys( av_ls_landings() ) as $k ) {
				$id = av_ls_create_page( $k, $rebuild );
				if ( is_wp_error( $id ) && 'av_ls_skip' === $id->get_error_code() ) {
					$links[] = esc_html( av_ls_landings()[ $k ]['title'] . ': bỏ qua (' . $id->get_error_message() . ')' );
					continue;
				}
				if ( is_wp_error( $id ) ) {
					wp_send_json_error( $id->get_error_message() );
				}
				$links[] = sprintf( '<a href="%s" target="_blank">%s</a> (<a href="%s">sửa</a>)', esc_url( (string) get_permalink( $id ) ), esc_html( get_the_title( $id ) ), esc_url( (string) get_edit_post_link( $id ) ) );
			}
			wp_send_json_success( array( 'links' => implode( ' · ', $links ) ) );
		}
		wp_send_json_error( 'Bước không hợp lệ.' );
	}
);
