<?php
/**
 * Phía khách: CSS, nút liên hệ nổi, thanh liên hệ cuối màn hình trên điện thoại, sự kiện GA4.
 */

defined( 'ABSPATH' ) || exit;

// CSS dùng ở cả trang và trình soạn thảo.
add_action(
	'enqueue_block_assets',
	static function () {
		wp_enqueue_style( 'auviet-core', plugins_url( 'assets/css/auviet-core.css', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/css/auviet-core.css' ) );
	}
);

add_action(
	'wp_footer',
	static function () {
		$channels = av_contact_channels();
		echo '<div class="av-fab" aria-label="Liên hệ nhanh">';
		foreach ( $channels as $ch ) {
			printf(
				'<a class="av-fab__btn av-fab__btn--%1$s" href="%2$s" aria-label="%3$s" data-av-event="%4$s"%6$s>%5$s</a>',
				esc_attr( $ch['key'] ),
				esc_url( $ch['url'] ),
				esc_attr( $ch['label'] . ' ' . $ch['sub'] ),
				esc_attr( $ch['event'] ),
				av_channel_icon( $ch, empty( $ch['img'] ) ? 22 : 52 ), // phpcs:ignore WordPress.Security.EscapeOutput -- SVG hoặc ảnh cố định.
				str_starts_with( $ch['url'], 'http' ) ? ' target="_blank" rel="noopener"' : ''
			);
		}
		echo '</div><nav class="av-mbar" aria-label="Liên hệ nhanh">';
		foreach ( $channels as $ch ) {
			printf(
				'<a class="av-mbar__btn av-mbar__btn--%1$s" href="%2$s" data-av-event="%3$s"%6$s>%4$s<span>%5$s</span></a>',
				esc_attr( $ch['key'] ),
				esc_url( $ch['url'] ),
				esc_attr( $ch['event'] ),
				av_channel_icon( $ch ), // phpcs:ignore WordPress.Security.EscapeOutput -- SVG hoặc ảnh cố định.
				esc_html( 'Gọi điện' === $ch['label'] ? 'Gọi' : $ch['label'] ),
				str_starts_with( $ch['url'], 'http' ) ? ' target="_blank" rel="noopener"' : ''
			);
		}
		echo '</nav>';
		// Gửi sự kiện bấm nút liên hệ vào dataLayer (GA4 qua GTM ở production).
		echo "<script>document.addEventListener('click',function(e){var a=e.target.closest('[data-av-event]');if(!a)return;window.dataLayer=window.dataLayer||[];window.dataLayer.push({event:a.getAttribute('data-av-event')});});</script>";
	}
);

// Mã GTM chỉ chạy ở production.
add_action(
	'wp_head',
	static function () {
		$id = (string) av_contact( 'gtm_id' );
		if ( ! av_is_production() || ! preg_match( '/^GTM-[A-Z0-9]+$/', $id ) ) {
			return;
		}
		printf( "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','%s');</script>\n", esc_js( $id ) );
	}
);

// Sự kiện gửi báo giá thành công.
add_action(
	'wp_footer',
	static function () {
		if ( isset( $_GET['bao-gia'] ) && 'ok' === $_GET['bao-gia'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo "<script>window.dataLayer=window.dataLayer||[];window.dataLayer.push({event:'gui_bao_gia'});</script>";
		}
	},
	20
);
