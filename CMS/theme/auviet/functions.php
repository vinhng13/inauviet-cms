<?php
/**
 * Theme Âu Việt.
 */

defined( 'ABSPATH' ) || exit;

const AUVIET_THEME_VERSION = '0.2.0';

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );
		// Chỉ dùng mẫu khối của Âu Việt, không dùng bộ mẫu mặc định của WordPress.
		remove_theme_support( 'core-block-patterns' );
	}
);

add_action(
	'init',
	static function () {
		register_block_pattern_category( 'auviet', array( 'label' => 'Âu Việt' ) );
	}
);

/**
 * CSS theme và font lưu cùng site, dùng cho cả trang và trình soạn thảo.
 */
add_action(
	'enqueue_block_assets',
	static function () {
		if ( file_exists( get_theme_file_path( 'assets/css/fonts.css' ) ) ) {
			wp_enqueue_style( 'auviet-fonts', get_theme_file_uri( 'assets/css/fonts.css' ), array(), AUVIET_THEME_VERSION );
		}
		wp_enqueue_style( 'auviet-theme', get_theme_file_uri( 'assets/css/theme.css' ), array(), ( function_exists( 'av_ver' ) ? av_ver( get_theme_file_path( 'assets/css/theme.css' ) ) : (string) filemtime( get_theme_file_path( 'assets/css/theme.css' ) ) ) );
	}
);

/**
 * Các khối của trang chủ theo thứ tự trong bản thiết kế (LP-01).
 */
function auviet_home_patterns(): array {
	return array( 'home-hero', 'home-groups', 'home-products', 'home-process', 'home-design', 'home-projects', 'home-reasons', 'home-team', 'home-contact' );
}

/**
 * Tạo trang "Trang chủ" từ các mẫu khối và đặt làm trang đầu (chạy một lần).
 * Sau đó Biên tập viên sửa trang này trong Trang › Trang chủ.
 */
add_action(
	'wp_loaded',
	static function () {
		if ( get_option( 'auviet_home_page' ) ) {
			return;
		}
		$content = array();
		foreach ( auviet_home_patterns() as $slug ) {
			$file = get_theme_file_path( 'patterns/' . $slug . '.php' );
			if ( is_readable( $file ) ) {
				ob_start();
				include $file;
				$content[] = trim( (string) ob_get_clean() );
			}
		}
		if ( ! $content ) {
			return;
		}
		// Nội dung lấy từ file mẫu của theme (tin cậy), không lọc qua kses.
		kses_remove_filters();
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Trang chủ',
				'post_name'    => 'trang-chu',
				'post_content' => implode( "\n\n", $content ),
			)
		);
		kses_init();
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_id );
			update_option( 'auviet_home_page', $page_id );
		}
	}
);

/**
 * Tạo một trang từ mẫu khối của theme (chạy một lần), lưu ID vào option.
 * Sau đó Biên tập viên sửa chữ, thứ tự khối trong Trang.
 */
/**
 * Nội dung khối của một file mẫu trong theme/patterns.
 */
function auviet_page_from_pattern( string $pattern ): string {
	$file = get_theme_file_path( 'patterns/' . $pattern . '.php' );
	if ( ! is_readable( $file ) ) {
		return '';
	}
	ob_start();
	include $file;
	return trim( (string) ob_get_clean() );
}

function auviet_ensure_page( string $slug, string $title, string $pattern, string $option ): void {
	$existing = (int) get_option( $option );
	if ( $existing && get_post( $existing ) ) {
		return;
	}
	$page = get_page_by_path( $slug );
	if ( $page ) {
		update_option( $option, (int) $page->ID );
		return;
	}
	$content = auviet_page_from_pattern( $pattern );
	if ( '' === $content ) {
		return;
	}
	// Nội dung lấy từ file mẫu của theme (tin cậy), không lọc qua kses.
	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
		)
	);
	kses_init();
	if ( $id && ! is_wp_error( $id ) ) {
		update_option( $option, (int) $id );
	}
}

// Trang Liên hệ (LP-06): thông tin liên hệ sửa ở Âu Việt › Liên hệ & công ty.
// Trang Giới thiệu (LP-06): số liệu, máy móc, khách hàng sửa ở Âu Việt › Giới thiệu, Máy móc, Khách hàng tiêu biểu.
add_action(
	'wp_loaded',
	static function () {
		auviet_ensure_page( 'lien-he', 'Liên hệ', 'contact-page', 'av_contact_page' );
		auviet_ensure_page( 'gioi-thieu', 'Giới thiệu Âu Việt', 'about-page', 'av_about_page' );
		auviet_ensure_page( 'thiet-ke', 'Dịch vụ thiết kế', 'design-page', 'av_design_page' );
	}
);
