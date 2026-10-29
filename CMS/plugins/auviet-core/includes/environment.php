<?php
/**
 * Hành vi theo môi trường (WP_ENVIRONMENT_TYPE: local, staging, production).
 */

defined( 'ABSPATH' ) || exit;

function av_is_production(): bool {
	return 'production' === wp_get_environment_type();
}

// Môi trường chưa ra mắt: chặn Google index mọi trang.
if ( ! av_is_production() ) {
	add_filter( 'wp_robots', 'wp_robots_no_robots' );
	add_filter( 'pre_option_blog_public', static fn() => '0' );
}

// Nhãn môi trường trên thanh quản trị để không nhầm local/staging với site thật.
add_action(
	'admin_bar_menu',
	static function ( WP_Admin_Bar $bar ) {
		if ( av_is_production() ) {
			return;
		}
		$bar->add_node(
			array(
				'id'    => 'av-env',
				'title' => strtoupper( wp_get_environment_type() ),
				'meta'  => array( 'title' => 'Môi trường hiện tại' ),
			)
		);
	},
	1
);
