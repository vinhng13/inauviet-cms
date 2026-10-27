<?php
/**
 * Tạo bảng riêng, dữ liệu gốc và dữ liệu mẫu (chỉ môi trường local).
 */

defined( 'ABSPATH' ) || exit;

const AV_DB_VERSION = '2';

function av_install_tables(): void {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	dbDelta(
		"CREATE TABLE {$wpdb->prefix}av_quote_log (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			quote_id bigint(20) unsigned NOT NULL,
			old_status varchar(20) NOT NULL DEFAULT '',
			new_status varchar(20) NOT NULL,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			changed_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY quote_id (quote_id)
		) {$charset};"
	);
	// QT-04: lịch sử sửa giá (người sửa, thời điểm, giá cũ, giá mới).
	dbDelta(
		"CREATE TABLE {$wpdb->prefix}av_price_log (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			product_id bigint(20) unsigned NOT NULL,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			changed_at datetime NOT NULL,
			old_value longtext NOT NULL,
			new_value longtext NOT NULL,
			PRIMARY KEY  (id),
			KEY product_id (product_id)
		) {$charset};"
	);
}

/**
 * 5 nhóm sản phẩm theo mục 3 Tài liệu mô tả chức năng (chỉ tạo khi chưa có nhóm nào).
 */
function av_seed_groups(): void {
	$existing = get_terms( array( 'taxonomy' => 'av_group', 'hide_empty' => false, 'fields' => 'ids' ) );
	if ( is_wp_error( $existing ) || $existing ) {
		return;
	}
	$groups = array(
		'an-pham-van-phong' => array( 'Ấn phẩm văn phòng', 'Card visit, phong bì, kẹp file, hoá đơn, phiếu bảo hành, sổ bìa da' ),
		'an-pham-marketing' => array( 'Ấn phẩm marketing', 'Tờ rơi, tờ gấp, catalogue, brochure, kỷ yếu, tem nhãn, decal' ),
		'bao-bi'            => array( 'Bao bì', 'Túi giấy, hộp giấy, thùng carton theo kích thước riêng' ),
		'qua-tang-theo-mua' => array( 'Quà tặng theo mùa', 'Lịch Tết, bao lì xì, quạt nhựa in logo' ),
		'in-kho-lon'        => array( 'In khổ lớn, sự kiện', 'Backdrop, standee, poster, photobooth, bạt, biển đèn, canvas' ),
	);
	foreach ( $groups as $slug => $g ) {
		wp_insert_term( $g[0], 'av_group', array( 'slug' => $slug, 'description' => $g[1] ) );
	}
}

/**
 * Dữ liệu mẫu để xem trang chủ khi chưa chuyển nội dung thật. Chỉ chạy ở local, đánh dấu _av_sample để xoá sau.
 */
function av_seed_samples(): void {
	if ( 'local' !== wp_get_environment_type() ) {
		return;
	}
	$has = get_posts( array( 'post_type' => array( 'av_product', 'av_project' ), 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( $has ) {
		return;
	}
	$products = array(
		array( 'In catalogue', 'an-pham-marketing' ),
		array( 'In túi giấy kraft có logo', 'bao-bi' ),
		array( 'In hộp giấy theo kích thước', 'bao-bi' ),
		array( 'In tem nhãn, decal', 'an-pham-marketing' ),
		array( 'In tờ rơi, tờ gấp', 'an-pham-marketing' ),
		array( 'In lịch Tết doanh nghiệp', 'qua-tang-theo-mua' ),
		array( 'In backdrop, standee', 'in-kho-lon' ),
		array( 'In card visit', 'an-pham-van-phong' ),
	);
	foreach ( array_reverse( $products ) as $p ) {
		$id = wp_insert_post( array( 'post_type' => 'av_product', 'post_status' => 'publish', 'post_title' => $p[0], 'post_excerpt' => 'Xem quy cách, giá tham khảo' ) );
		if ( $id && ! is_wp_error( $id ) ) {
			wp_set_object_terms( $id, $p[1], 'av_group' );
			update_post_meta( $id, '_av_sample', 1 );
		}
	}
	$projects = array( 'Hộp quà Trung thu, ngành thực phẩm', 'Catalogue sản phẩm, ngành nội thất', 'Backdrop hội nghị, trường đại học' );
	foreach ( array_reverse( $projects ) as $t ) {
		$id = wp_insert_post( array( 'post_type' => 'av_project', 'post_status' => 'publish', 'post_title' => $t ) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_av_sample', 1 );
		}
	}
}

/**
 * Chạy một lần mỗi khi phiên bản dữ liệu thay đổi.
 */
add_action(
	'init',
	static function () {
		if ( get_option( 'av_db_version' ) === AV_DB_VERSION ) {
			return;
		}
		av_install_tables();
		av_seed_groups();
		av_seed_samples();
		// URL theo mục 3 Tài liệu mô tả chức năng.
		update_option( 'category_base', 'kien-thuc' );
		update_option( 'tag_base', 'chu-de' );
		update_option( 'av_db_version', AV_DB_VERSION );
		flush_rewrite_rules();
	},
	30
);
