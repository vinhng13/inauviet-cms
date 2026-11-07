<?php
/**
 * Khối động của Âu Việt: nội dung lấy từ cấu hình chung, sửa một nơi hiện khắp site.
 */

defined( 'ABSPATH' ) || exit;

function av_block_definitions(): array {
	return apply_filters( 'av_block_definitions', array(
		'auviet/topbar'          => array( 'title' => 'Thanh liên hệ trên cùng', 'icon' => 'phone', 'render' => 'av_render_topbar', 'description' => 'Hotline, email, địa chỉ văn phòng (lấy từ Âu Việt › Liên hệ & công ty).' ),
		'auviet/campaign-banner' => array( 'title' => 'Banner chiến dịch', 'icon' => 'megaphone', 'render' => 'av_render_campaign_banner', 'description' => 'Dải banner mùa (lấy từ Âu Việt › Banner chiến dịch).' ),
		'auviet/contact-tiles'   => array( 'title' => 'Bốn nút liên hệ', 'icon' => 'share', 'render' => 'av_render_contact_tiles', 'description' => 'Zalo, Gọi, Facebook, Email.' ),
		'auviet/departments'     => array( 'title' => 'Đội tư vấn theo bộ phận', 'icon' => 'groups', 'render' => 'av_render_departments', 'description' => 'Danh sách bộ phận và số điện thoại.' ),
		'auviet/product-groups'  => array( 'title' => 'Nhóm sản phẩm', 'icon' => 'screenoptions', 'render' => 'av_render_product_groups', 'description' => 'Các nhóm sản phẩm cấp 1 kèm mô tả.' ),
		'auviet/quote-form'      => array( 'title' => 'Form liên hệ báo giá', 'icon' => 'feedback', 'render' => 'av_render_quote_form', 'description' => 'Form một bước, lưu vào hộp thư Yêu cầu báo giá.', 'attributes' => array( 'need' => array( 'type' => 'string', 'default' => '' ), 'type' => array( 'type' => 'string', 'default' => 'bao_gia' ) ) ),
		'auviet/company-info'    => array( 'title' => 'Chân trang công ty', 'icon' => 'building', 'render' => 'av_render_company_info', 'description' => 'Thông tin công ty, liên kết, chính sách.' ),
	) );
}

add_filter(
	'block_categories_all',
	static function ( array $cats ): array {
		array_unshift( $cats, array( 'slug' => 'auviet', 'title' => 'Âu Việt' ) );
		return $cats;
	}
);

add_action(
	'init',
	static function () {
		wp_register_script(
			'auviet-blocks-editor',
			plugins_url( 'assets/js/blocks-editor.js', AV_CORE_FILE ),
			array( 'wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-block-editor' ),
			AV_CORE_VERSION,
			true
		);
		$defs = array();
		foreach ( av_block_definitions() as $name => $def ) {
			register_block_type(
				$name,
				array(
					'api_version'           => 3,
					'title'                 => $def['title'],
					'category'              => 'auviet',
					'attributes'            => $def['attributes'] ?? array(),
					'render_callback'       => $def['render'],
					'uses_context'          => $def['context'] ?? array(),
					'editor_script_handles' => array( 'auviet-blocks-editor' ),
					'supports'              => array( 'html' => false ),
				)
			);
			$defs[] = array(
				'name'        => $name,
				'title'       => $def['title'],
				'icon'        => $def['icon'],
				'description' => $def['description'],
				'attributes'  => $def['attributes'] ?? (object) array(),
			);
		}
		wp_add_inline_script( 'auviet-blocks-editor', 'window.avBlocks = ' . wp_json_encode( $defs ) . ';', 'before' );
	}
);

function av_render_topbar(): string {
	$c = av_contact();
	return sprintf(
		'<div class="av-topbar"><div class="av-wrap"><a href="%1$s" data-av-event="bam_goi"><strong>Hotline %2$s</strong></a><a href="mailto:%3$s" data-av-event="bam_email">%3$s</a><span class="av-topbar__addr">%4$s</span></div></div>',
		esc_url( av_tel_href( $c['hotline'] ) ),
		esc_html( $c['hotline'] ),
		esc_html( $c['email'] ),
		esc_html( $c['office_address'] )
	);
}

function av_render_campaign_banner(): string {
	if ( ! av_campaign_is_live() ) {
		return '';
	}
	$c = av_campaign();
	return sprintf(
		'<a class="av-campaign" href="%1$s"><span class="av-wrap"><strong>%2$s</strong><span>%3$s</span><span class="av-campaign__link">%4$s</span></span></a>',
		esc_url( $c['url'] ?: home_url( '/' ) ),
		esc_html( $c['title'] ),
		esc_html( $c['text'] ),
		esc_html( $c['link_text'] )
	);
}

function av_render_contact_tiles(): string {
	$out = '<div class="av-tiles">';
	foreach ( av_contact_channels() as $ch ) {
		$out .= sprintf(
			'<a class="av-tile av-tile--%1$s" href="%2$s" data-av-event="%3$s"%7$s><span class="av-tile__icon">%4$s</span><span><strong>%5$s</strong><span>%6$s</span></span></a>',
			esc_attr( $ch['key'] ),
			esc_url( $ch['url'] ),
			esc_attr( $ch['event'] ),
			av_icon( $ch['icon'] ),
			esc_html( $ch['label'] ),
			esc_html( $ch['sub'] ),
			str_starts_with( $ch['url'], 'http' ) ? ' target="_blank" rel="noopener"' : ''
		);
	}
	return $out . '</div>';
}

function av_render_departments(): string {
	$deps = (array) av_contact( 'departments' );
	if ( ! $deps ) {
		return '';
	}
	$out = '<div class="av-people">';
	foreach ( $deps as $d ) {
		$out .= sprintf(
			'<div class="av-person"><span class="av-person__role">%1$s</span><strong class="av-person__name">%2$s</strong><a class="av-person__tel" href="%3$s" data-av-event="bam_goi">%4$s</a></div>',
			esc_html( $d['role'] ),
			esc_html( $d['name'] ),
			esc_url( av_tel_href( $d['phone'] ) ),
			esc_html( $d['phone'] )
		);
	}
	return $out . '</div>';
}

function av_render_product_groups(): string {
	$terms = av_visible_groups();
	if ( ! $terms ) {
		return '<p>Sản phẩm đang được cập nhật.</p>';
	}
	$out = '<div class="av-groups">';
	foreach ( $terms as $i => $t ) {
		$thumb = av_group_thumb( $t );
		$out  .= sprintf(
			'<a class="av-group av-group--%1$d%5$s" href="%2$s">%6$s<strong>%3$s</strong><span>%4$s</span></a>',
			$i % 5,
			esc_url( get_term_link( $t ) ),
			esc_html( $t->name ),
			esc_html( $t->description ),
			$thumb ? ' has-img' : '',
			$thumb ? wp_get_attachment_image( $thumb, 'medium', false, array( 'class' => 'av-group__img', 'alt' => '', 'loading' => 'lazy' ) ) : ''
		);
	}
	return $out . '</div>';
}

function av_render_company_info(): string {
	$c = av_contact();
	ob_start();
	?>
	<div class="av-footer-cols">
		<div class="av-footer-col av-footer-col--main">
			<strong class="av-footer__name"><?php echo esc_html( $c['company_name'] ); ?></strong>
			<p>Văn phòng: <?php echo esc_html( $c['office_address'] ); ?></p>
			<p>Trụ sở: <?php echo esc_html( $c['workshop_address'] ); ?></p>
			<p>Điện thoại: <a href="<?php echo esc_url( av_tel_href( $c['landline'] ) ); ?>"><?php echo esc_html( $c['landline'] ); ?></a> · Di động: <a href="<?php echo esc_url( av_tel_href( $c['hotline'] ) ); ?>"><?php echo esc_html( $c['hotline'] ); ?></a></p>
			<p>Email: <a href="mailto:<?php echo esc_attr( $c['email'] ); ?>"><?php echo esc_html( $c['email'] ); ?></a></p>
		</div>
		<nav class="av-footer-col" aria-label="Thông tin">
			<strong>Thông tin</strong>
			<a href="<?php echo esc_url( home_url( '/gioi-thieu/' ) ); ?>">Giới thiệu</a>
			<a href="<?php echo esc_url( home_url( '/mau-da-lam/' ) ); ?>">Mẫu đã làm</a>
			<a href="<?php echo esc_url( home_url( '/chat-lieu/' ) ); ?>">Thư viện chất liệu</a>
			<a href="<?php echo esc_url( home_url( '/kien-thuc/' ) ); ?>">Kiến thức in ấn</a>
			<a href="<?php echo esc_url( home_url( '/hoi-dap/' ) ); ?>">Hỏi đáp</a>
			<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>">Liên hệ</a>
		</nav>
		<nav class="av-footer-col" aria-label="Chính sách">
			<strong>Chính sách</strong>
			<a href="<?php echo esc_url( home_url( '/chinh-sach/cam-ket-chat-luong/' ) ); ?>">Cam kết chất lượng</a>
			<a href="<?php echo esc_url( home_url( '/chinh-sach/xu-ly-khieu-nai/' ) ); ?>">Chính sách xử lý khiếu nại</a>
			<a href="<?php echo esc_url( home_url( '/chinh-sach/van-chuyen/' ) ); ?>">Vận chuyển</a>
			<a href="<?php echo esc_url( home_url( '/chinh-sach/thanh-toan/' ) ); ?>">Thanh toán</a>
			<a href="<?php echo esc_url( home_url( '/chinh-sach/bao-mat-thong-tin/' ) ); ?>">Bảo mật thông tin khách hàng</a>
		</nav>
	</div>
	<p class="av-footer__copy">© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
	<?php
	return (string) ob_get_clean();
}
