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
		wp_enqueue_style( 'auviet-theme', get_theme_file_uri( 'assets/css/theme.css' ), array(), (string) filemtime( get_theme_file_path( 'assets/css/theme.css' ) ) );
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
