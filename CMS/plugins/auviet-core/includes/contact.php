<?php
/**
 * LP-06 Trang liên hệ /lien-he/: khối thông tin liên hệ, bản đồ văn phòng (tải khi cuộn tới),
 * dữ liệu có cấu trúc LocalBusiness. Mọi thông tin đọc từ Âu Việt › Liên hệ & công ty (QT-10) nên giống hệt
 * ở mọi nơi trên site. Form trên trang tạo yêu cầu loại "Liên hệ" trong hộp thư QT-07.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loại yêu cầu trong hộp thư QT-07.
 */
function av_quote_types(): array {
	return array(
		'bao_gia' => 'Báo giá',
		'lien_he' => 'Liên hệ',
	);
}

add_filter(
	'av_block_definitions',
	static function ( array $defs ): array {
		return $defs + array(
			'auviet/contact-info' => array( 'title' => 'Thông tin liên hệ', 'icon' => 'location', 'render' => 'av_render_contact_info', 'description' => 'Văn phòng, xưởng, điện thoại, email, giờ làm việc (lấy từ Âu Việt › Liên hệ & công ty).' ),
			'auviet/contact-map'  => array( 'title' => 'Bản đồ văn phòng', 'icon' => 'location-alt', 'render' => 'av_render_contact_map', 'description' => 'Bản đồ Google văn phòng giao dịch, tải khi khách cuộn tới, kèm nút chỉ đường.' ),
		);
	}
);

/**
 * Chuỗi tìm kiếm trên bản đồ: toạ độ hoặc tên địa điểm nhập trong cấu hình, để trống thì dùng địa chỉ.
 */
function av_map_query( string $which ): string {
	$c = av_contact();
	$q = trim( (string) ( $c[ $which . '_map' ] ?? '' ) );
	return $q ?: (string) $c[ $which . '_address' ];
}

function av_map_embed_url( string $query ): string {
	return 'https://maps.google.com/maps?' . http_build_query(
		array(
			'q'      => $query,
			'hl'     => 'vi',
			'z'      => 16,
			'output' => 'embed',
		)
	);
}

function av_map_directions_url( string $query ): string {
	return 'https://www.google.com/maps/dir/?' . http_build_query(
		array(
			'api'         => 1,
			'destination' => $query,
		)
	);
}

function av_render_contact_info(): string {
	$c    = av_contact();
	$rows = array(
		array( 'Văn phòng giao dịch', esc_html( $c['office_address'] ) ),
		array( 'Trụ sở', esc_html( $c['workshop_address'] ) ),
		array( 'Hotline', sprintf( '<a href="%s" data-av-event="bam_goi">%s</a>', esc_url( av_tel_href( $c['hotline'] ) ), esc_html( $c['hotline'] ) ) ),
		array( 'Điện thoại bàn', $c['landline'] ? sprintf( '<a href="%s" data-av-event="bam_goi">%s</a>', esc_url( av_tel_href( $c['landline'] ) ), esc_html( $c['landline'] ) ) : '' ),
		array( 'Email', sprintf( '<a href="mailto:%1$s" data-av-event="bam_email">%1$s</a>', esc_html( $c['email'] ) ) ),
		array( 'Giờ làm việc', esc_html( $c['hours'] ) ),
		array( 'Mã số thuế', esc_html( $c['tax_code'] ) ),
	);
	$out = '<div class="av-cinfo"><strong class="av-cinfo__name">' . esc_html( $c['company_name'] ) . '</strong><dl>';
	foreach ( $rows as $r ) {
		if ( '' !== trim( wp_strip_all_tags( $r[1] ) ) ) {
			$out .= '<div><dt>' . esc_html( $r[0] ) . '</dt><dd>' . $r[1] . '</dd></div>'; // Giá trị đã escape ở trên.
		}
	}
	$out .= '</dl>';
	if ( $c['gbp_url'] ) {
		$out .= sprintf( '<p class="av-cinfo__gbp"><a href="%s" target="_blank" rel="noopener">Xem Âu Việt trên Google Maps, đánh giá của khách</a></p>', esc_url( $c['gbp_url'] ) );
	}
	return $out . '</div>';
}

function av_render_contact_map(): string {
	$c     = av_contact();
	// Bản đồ chỉ cho văn phòng giao dịch (nơi khách đến làm việc).
	$items = array(
		array( 'Văn phòng giao dịch', $c['office_address'], av_map_query( 'office' ) ),
	);
	$out = '<div class="av-maps av-maps--single">';
	foreach ( $items as $it ) {
		if ( '' === trim( (string) $it[1] ) ) {
			continue;
		}
		$out .= sprintf(
			'<figure class="av-map"><div class="av-map__frame" data-av-map="%1$s"><a class="av-map__ph" href="%2$s" target="_blank" rel="noopener">Bản đồ đang tải. Bấm để mở Google Maps</a></div><figcaption><strong>%3$s</strong><span>%4$s</span><a class="av-btn av-btn--outline" href="%5$s" target="_blank" rel="noopener">Chỉ đường</a></figcaption></figure>',
			esc_url( av_map_embed_url( $it[2] ) ),
			esc_url( 'https://www.google.com/maps/search/?' . http_build_query( array( 'api' => 1, 'query' => $it[2] ) ) ),
			esc_html( $it[0] ),
			esc_html( $it[1] ),
			esc_url( av_map_directions_url( $it[2] ) )
		);
	}
	// Bản đồ chỉ tải khi khách cuộn tới (đỡ chậm trang, không gọi Google khi chưa cần).
	$out .= "<script>(function(){var els=document.querySelectorAll('[data-av-map]');function load(el){if(el.dataset.loaded)return;el.dataset.loaded=1;var f=document.createElement('iframe');f.src=el.dataset.avMap;f.title='Bản đồ';f.loading='lazy';f.referrerPolicy='no-referrer-when-downgrade';f.setAttribute('allowfullscreen','');el.appendChild(f);}if(!('IntersectionObserver' in window)){els.forEach(load);return;}var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){load(e.target);io.unobserve(e.target);}});},{rootMargin:'200px'});els.forEach(function(el){io.observe(el);});})();</script>";
	return $out . '</div>';
}

/**
 * Trang liên hệ (tạo từ mẫu khối của theme). Trả về ID hoặc 0.
 */
function av_contact_page_id(): int {
	$id = (int) get_option( 'av_contact_page' );
	return ( $id && 'page' === get_post_type( $id ) ) ? $id : 0;
}

// Dữ liệu có cấu trúc cho Google: thông tin doanh nghiệp, khớp với Google Business Profile.
add_action(
	'wp_head',
	static function () {
		$id = av_contact_page_id();
		if ( ! $id || ! is_page( $id ) ) {
			return;
		}
		$c    = av_contact();
		$data = array(
			'@context'  => 'https://schema.org',
			'@type'     => 'LocalBusiness',
			'name'      => $c['company_name'],
			'url'       => home_url( '/' ),
			'telephone' => preg_replace( '/[^0-9+]/', '', $c['hotline'] ),
			'email'     => $c['email'],
			'address'   => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $c['office_address'],
				'addressLocality' => 'Hà Nội',
				'addressCountry'  => 'VN',
			),
			'sameAs'    => array_values( array_filter( array( $c['facebook_url'], $c['gbp_url'] ) ) ),
		);
		$points = array();
		foreach ( (array) $c['departments'] as $d ) {
			if ( ! empty( $d['phone'] ) ) {
				$points[] = array(
					'@type'       => 'ContactPoint',
					'contactType' => $d['role'],
					'telephone'   => preg_replace( '/[^0-9+]/', '', $d['phone'] ),
				);
			}
		}
		if ( $points ) {
			$data['contactPoint'] = $points;
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
	}
);

// Một lần: đổi tiêu đề mục bản đồ trên trang liên hệ đã tạo trước đó (chỉ còn bản đồ văn phòng).
add_action(
	'init',
	static function () {
		if ( get_option( 'av_mig_contact_map' ) ) {
			return;
		}
		update_option( 'av_mig_contact_map', 1, false );
		$id = av_contact_page_id();
		if ( ! $id ) {
			return;
		}
		$content = (string) get_post_field( 'post_content', $id );
		$new     = str_replace( '>Văn phòng và xưởng</h2>', '>Văn phòng giao dịch</h2>', $content );
		if ( $new !== $content ) {
			kses_remove_filters();
			wp_update_post( array( 'ID' => $id, 'post_content' => $new ) );
			kses_init();
		}
	},
	50
);

