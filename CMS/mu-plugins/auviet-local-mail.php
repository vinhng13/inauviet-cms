<?php
/**
 * Plugin Name: Âu Việt – Email môi trường thử
 * Description: Ở môi trường local/staging, mọi email đi vào Mailpit (http://localhost:8025) thay vì gửi thật.
 */
defined( 'ABSPATH' ) || exit;

if ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
	add_action(
		'phpmailer_init',
		static function ( $phpmailer ) {
			$phpmailer->isSMTP();
			$phpmailer->Host     = 'mailpit';
			$phpmailer->Port     = 1025;
			$phpmailer->SMTPAuth = false;
			$phpmailer->SMTPSecure = '';
			$phpmailer->SMTPAutoTLS = false;
		}
	);

	// Địa chỉ gửi mặc định "wordpress@localhost" không hợp lệ nên PHPMailer từ chối.
	add_filter( 'wp_mail_from', static fn() => 'no-reply@auviet.test' );
	add_filter( 'wp_mail_from_name', static fn() => 'In Âu Việt (local)' );
}
