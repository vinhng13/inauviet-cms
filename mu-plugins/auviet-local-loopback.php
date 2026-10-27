<?php
/**
 * Plugin Name: Âu Việt – Loopback trong Docker (local)
 * Description: Ở local, trình duyệt vào http://localhost:8080 nhưng bên trong container máy chủ web nghe cổng 80.
 *              WordPress tự gọi chính nó (WP-Cron, Site Health, trình soạn khối) sẽ hỏng nếu không chuyển hướng kết nối.
 */
defined( 'ABSPATH' ) || exit;

if ( 'local' === wp_get_environment_type() ) {
	add_action(
		'http_api_curl',
		static function ( $handle, $args, $url ) {
			$host = wp_parse_url( home_url(), PHP_URL_HOST );
			$port = (int) wp_parse_url( home_url(), PHP_URL_PORT );
			if ( ! $port || 80 === $port || ! defined( 'CURLOPT_CONNECT_TO' ) ) {
				return;
			}
			if ( wp_parse_url( $url, PHP_URL_HOST ) === $host && (int) wp_parse_url( $url, PHP_URL_PORT ) === $port ) {
				// Giữ nguyên Host: localhost:8080, chỉ đổi đích kết nối sang cổng 80 trong container.
				curl_setopt( $handle, CURLOPT_CONNECT_TO, array( "{$host}:{$port}:127.0.0.1:80" ) );
			}
		},
		10,
		3
	);
}
