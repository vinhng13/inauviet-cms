<?php
/**
 * Theme Âu Việt.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );
		// Không dùng bộ mẫu khối mặc định của WordPress; chỉ dùng mẫu của Âu Việt.
		remove_theme_support( 'core-block-patterns' );
	}
);

/**
 * Font lưu cùng site (assets/css/fonts.css do setup-wp.sh tạo).
 */
function auviet_font_css_url(): ?string {
	$file = get_theme_file_path( 'assets/css/fonts.css' );
	return file_exists( $file ) ? get_theme_file_uri( 'assets/css/fonts.css' ) : null;
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		$fonts = auviet_font_css_url();
		if ( $fonts ) {
			wp_enqueue_style( 'auviet-fonts', $fonts, array(), wp_get_theme()->get( 'Version' ) );
		}
	}
);

add_action(
	'after_setup_theme',
	static function () {
		$fonts = auviet_font_css_url();
		if ( $fonts ) {
			add_editor_style( 'assets/css/fonts.css' );
		}
	}
);
