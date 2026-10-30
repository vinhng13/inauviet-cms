<?php
/**
 * Màn hình soạn Sản phẩm và Mẫu đã làm theo prototype "Màn hình soạn nội dung":
 * thanh trên cùng luôn có nút lưu; sản phẩm chia mục (Nội dung, Ảnh, Quy cách, Bảng giá, Liên quan, Google);
 * mẫu đã làm là một trang 5 bước; cột phải có danh sách "Sẵn sàng hiển thị" bấm vào để tới chỗ cần sửa.
 *
 * Dựng trên màn hình sửa cổ điển của WordPress (các hộp nhập liệu giữ nguyên tên trường, lưu như cũ),
 * nên plugin khác (Rank Math, LiteSpeed) vẫn chạy; hộp lạ được gom vào mục "Khác".
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loại nội dung dùng màn hình soạn mới.
 */
function av_shell_types(): array {
	return array( 'av_product', 'av_project' );
}

function av_is_shell_screen(): bool {
	if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
		return false;
	}
	$s = get_current_screen();
	return $s && 'post' === $s->base && in_array( $s->post_type, av_shell_types(), true );
}

// Sản phẩm cũng dùng màn hình cổ điển (mẫu đã làm đã dùng từ trước).
add_filter( 'use_block_editor_for_post_type', static fn( $use, $type ) => 'av_product' === $type ? false : $use, 20, 2 );

add_filter(
	'admin_body_class',
	static function ( string $classes ): string {
		if ( av_is_shell_screen() ) {
			$classes .= ' av-shell av-shell--' . str_replace( 'av_', '', get_current_screen()->post_type );
		}
		return $classes;
	}
);

// Ảnh đại diện = ảnh đầu tiên trong hộp ảnh; ngành của mẫu chọn trong bước 3.
add_action(
	'add_meta_boxes',
	static function ( string $type ) {
		if ( ! in_array( $type, av_shell_types(), true ) ) {
			return;
		}
		remove_meta_box( 'postimagediv', $type, 'side' );
		// Hộp bộ nhớ đệm LiteSpeed theo từng trang: hiếm dùng, làm rối cột phải (vẫn chỉnh được trong LiteSpeed Cache).
		remove_meta_box( 'litespeed_meta_boxes', $type, 'side' );
		remove_meta_box( 'litespeed_meta_boxes', $type, 'normal' );
		if ( 'av_project' === $type ) {
			remove_meta_box( 'av_industrydiv', $type, 'side' );
		}
		add_meta_box( 'av_checks', 'av_project' === $type ? 'Trước khi đăng' : 'Sẵn sàng hiển thị', 'av_box_checks', $type, 'side', 'high' );
		if ( 'av_product' === $type ) {
			add_meta_box( 'av_product_seo', 'Hiện trên Google', 'av_box_seo', $type, 'normal', 'default' );
		}
		if ( 'av_project' === $type ) {
			add_meta_box( 'av_card_preview', 'Xem trước thẻ', 'av_box_card_preview', $type, 'side', 'default' );
		}
	},
	100
);

/* ------------------------------------------------------------------ */
/* Danh sách kiểm tra                                                  */
/* ------------------------------------------------------------------ */

/**
 * Ảnh của nội dung theo thứ tự hiển thị: ảnh đại diện trước, rồi thư viện ảnh.
 *
 * @return int[]
 */
function av_post_images( int $post_id ): array {
	$ids   = array();
	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		$ids[] = $thumb;
	}
	foreach ( (array) get_post_meta( $post_id, '_av_gallery', true ) as $id ) {
		$id = (int) $id;
		if ( $id && ! in_array( $id, $ids, true ) && wp_attachment_is_image( $id ) ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * Các mục kiểm tra lúc mở màn hình. key: JS cập nhật trực tiếp khi sửa; tab: mục cần mở để sửa;
 * block: chưa ổn thì không cho bấm Đăng.
 *
 * @return array[]
 */
function av_shell_checks( WP_Post $post ): array {
	$images  = av_post_images( $post->ID );
	$no_alt  = count( array_filter( $images, static fn( $id ) => '' === trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) ) );
	$excerpt = trim( (string) $post->post_excerpt );
	$dups    = 'auto-draft' === $post->post_status ? array() : av_title_duplicates( $post->post_title, $post->ID );
	$checks  = array();

	if ( 'av_product' === $post->post_type ) {
		$checks[] = array( 'key' => 'excerpt', 'tab' => 'noidung', 'ok' => '' !== $excerpt, 'title' => '' !== $excerpt ? 'Có mô tả ngắn' : 'Chưa có mô tả ngắn', 'sub' => '' !== $excerpt ? mb_strlen( $excerpt ) . ' ký tự' : 'Hiện dưới tên sản phẩm và trên thẻ sản phẩm' );
		$checks[] = array( 'key' => 'thumb', 'tab' => 'anh', 'ok' => (bool) $images, 'title' => $images ? 'Có ảnh đại diện' : 'Chưa có ảnh', 'sub' => $images ? count( $images ) . ' ảnh' : 'Thêm ít nhất 1 ảnh thật' );
		$checks[] = array( 'key' => 'alts', 'tab' => 'anh', 'ok' => 0 === $no_alt, 'title' => $no_alt ? $no_alt . ' ảnh thiếu mô tả' : 'Mọi ảnh có mô tả', 'sub' => $no_alt ? 'Ghi một câu tả ảnh dưới mỗi ảnh' : '' );
		$draft    = (bool) get_post_meta( $post->ID, '_av_spec_draft', true );
		$checks[] = array( 'key' => 'spec', 'tab' => 'quycach', 'ok' => ! $draft, 'title' => $draft ? 'Quy cách chưa kiểm tra' : 'Quy cách đã kiểm tra', 'sub' => $draft ? 'Tự điền từ web cũ, cần đối chiếu với xưởng' : '' );
		if ( current_user_can( 'av_view_prices' ) || current_user_can( 'av_manage_prices' ) ) {
			$has      = av_product_has_prices( $post->ID );
			$checks[] = array( 'key' => 'prices', 'tab' => 'gia', 'ok' => $has, 'warn' => true, 'title' => $has ? 'Có bảng giá' : 'Chưa có bảng giá', 'sub' => $has ? '' : 'Trang sẽ hiện "Liên hệ báo giá"' );
		}
		$groups   = wp_get_object_terms( $post->ID, 'av_group', array( 'fields' => 'ids' ) );
		$checks[] = array( 'key' => 'groups', 'tab' => '', 'ok' => ! is_wp_error( $groups ) && $groups, 'title' => ( ! is_wp_error( $groups ) && $groups ) ? 'Đã chọn nhóm sản phẩm' : 'Chưa chọn nhóm sản phẩm', 'sub' => 'Khung "Nhóm sản phẩm" bên phải' );
	} else {
		$products = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, '_av_products', true ) ) );
		$checks[] = array( 'key' => 'products', 'tab' => 'av_project_products', 'block' => true, 'ok' => (bool) $products, 'title' => $products ? 'Gắn ' . count( $products ) . ' sản phẩm' : 'Chưa gắn sản phẩm', 'sub' => $products ? '' : 'Bắt buộc, chọn ở bước 3' );
		$checks[] = array( 'key' => 'client', 'tab' => 'av_project_client', 'block' => true, 'ok' => true, 'title' => 'Tên khách không lộ ra web', 'sub' => '' );
		$checks[] = array( 'key' => 'thumb', 'tab' => 'av_project_gallery', 'ok' => (bool) $images, 'title' => $images ? 'Có ảnh đại diện' : 'Chưa có ảnh', 'sub' => $images ? count( $images ) . ' ảnh' : 'Thêm ảnh thật của đơn hàng' );
		$checks[] = array( 'key' => 'alts', 'tab' => 'av_project_gallery', 'ok' => 0 === $no_alt, 'title' => $no_alt ? $no_alt . ' ảnh thiếu mô tả' : 'Mọi ảnh có mô tả', 'sub' => '' );
		$ind      = wp_get_object_terms( $post->ID, 'av_industry', array( 'fields' => 'ids' ) );
		$checks[] = array( 'key' => 'industry', 'tab' => 'av_project_products', 'ok' => ! is_wp_error( $ind ) && $ind, 'title' => ( ! is_wp_error( $ind ) && $ind ) ? 'Đã chọn ngành' : 'Chưa chọn ngành', 'sub' => 'Để khách lọc theo ngành' );
	}
	$checks[] = array( 'key' => 'dup', 'tab' => 'noidung', 'ok' => ! $dups, 'title' => $dups ? 'Tên trùng ' . mb_strtolower( $dups[0]['type'] ) . ' khác' : 'Tên không trùng', 'sub' => $dups ? '"' . $dups[0]['title'] . '"' : 'Không trùng sản phẩm, bài viết, trang khác' );
	return array_map( static fn( $c ) => $c + array( 'warn' => false, 'block' => false ), $checks );
}

function av_box_checks( WP_Post $post ): void {
	$checks = av_shell_checks( $post );
	$ok     = count( array_filter( $checks, static fn( $c ) => $c['ok'] ) );
	printf( '<p class="av-checks__sum" data-av-checks-sum>%d / %d mục ổn. Bấm mục chưa ổn để sửa.</p><ul class="av-checks" data-av-checks>', (int) $ok, count( $checks ) );
	foreach ( $checks as $c ) {
		printf(
			'<li><button type="button" class="av-check %1$s" data-key="%2$s" data-tab="%3$s" data-block="%4$d" data-warn="%5$d"><span class="av-check__icon" aria-hidden="true"></span><span class="av-check__text"><strong>%6$s</strong><span>%7$s</span></span><span class="screen-reader-text">%8$s</span></button></li>',
			$c['ok'] ? 'is-ok' : 'is-bad',
			esc_attr( $c['key'] ),
			esc_attr( $c['tab'] ),
			$c['block'] ? 1 : 0,
			$c['warn'] ? 1 : 0,
			esc_html( $c['title'] ),
			esc_html( $c['sub'] ),
			$c['ok'] ? 'Ổn' : 'Cần sửa'
		);
	}
	echo '</ul>';
}

function av_box_card_preview( WP_Post $post ): void {
	$images = av_post_images( $post->ID );
	$parts  = array_merge( wp_list_pluck( av_project_types( $post->ID ), 'name' ), wp_list_pluck( av_project_industries( $post->ID ), 'name' ) );
	printf(
		'<div class="av-cardprev"><div class="av-cardprev__img" data-av-card-img>%s</div><strong data-av-card-title>%s</strong><span>%s</span></div><p class="description">Như khách thấy ở trang Mẫu đã làm và trang chủ.</p>',
		$images ? wp_get_attachment_image( $images[0], 'medium', false, array( 'alt' => '' ) ) : '<span>[Chưa có ảnh]</span>',
		esc_html( get_the_title( $post ) ?: 'Tên dự án' ),
		esc_html( implode( ' · ', array_unique( $parts ) ) ?: 'Loại sản phẩm · Ngành' )
	);
}

/* ------------------------------------------------------------------ */
/* Thanh trên cùng                                                     */
/* ------------------------------------------------------------------ */

function av_status_label( string $status ): array {
	$map = array(
		'publish'    => array( 'Đang hiển thị', 'live' ),
		'future'     => array( 'Hẹn giờ hiển thị', 'future' ),
		'pending'    => array( 'Chờ duyệt', 'draft' ),
		'private'    => array( 'Riêng tư', 'draft' ),
		'draft'      => array( 'Bản nháp', 'draft' ),
		'auto-draft' => array( 'Mới', 'draft' ),
	);
	return $map[ $status ] ?? array( $status, 'draft' );
}

add_action(
	'edit_form_top',
	static function ( WP_Post $post ) {
		if ( ! in_array( $post->post_type, av_shell_types(), true ) ) {
			return;
		}
		$pto     = get_post_type_object( $post->post_type );
		$status  = av_status_label( $post->post_status );
		$live    = 'publish' === $post->post_status;
		$new     = 'auto-draft' === $post->post_status;
		$can_pub = current_user_can( $pto->cap->publish_posts );
		$primary = $live || 'future' === $post->post_status ? 'Lưu thay đổi' : ( $can_pub ? ( 'av_project' === $post->post_type ? 'Đăng mẫu' : 'Đăng sản phẩm' ) : 'Gửi duyệt' );
		?>
		<div class="av-topbar">
			<a class="av-topbar__back" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $post->post_type ) ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"></path></svg>
				<?php echo esc_html( $pto->labels->name ); ?>
			</a>
			<div class="av-topbar__title">
				<span class="av-topbar__name" data-av-title data-empty="<?php echo esc_attr( $new ? $pto->labels->add_new_item : '(Chưa có tên)' ); ?>"><?php echo esc_html( $post->post_title ?: ( $new ? $pto->labels->add_new_item : '(Chưa có tên)' ) ); ?></span>
				<span class="av-pill av-pill--<?php echo esc_attr( $status[1] ); ?>"><?php echo esc_html( $status[0] ); ?></span>
				<?php if ( ! $new ) : ?>
					<span class="av-topbar__saved">Đã lưu lúc <?php echo esc_html( get_post_modified_time( 'H:i, d/m', false, $post ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="av-topbar__actions">
				<?php if ( $live ) : ?>
					<a class="av-tb-btn" href="<?php echo esc_url( get_permalink( $post ) ); ?>" target="_blank" rel="noopener">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
						Xem trên web
					</a>
				<?php else : ?>
					<button type="button" class="av-tb-btn" data-av-proxy="#post-preview">Xem trước</button>
					<button type="button" class="av-tb-btn" data-av-proxy="#save-post">Lưu nháp</button>
				<?php endif; ?>
				<button type="button" class="av-tb-btn av-tb-btn--primary" data-av-proxy="#publish" data-av-primary><?php echo esc_html( $primary ); ?></button>
			</div>
		</div>
		<?php
	}
);

/* ------------------------------------------------------------------ */
/* Tài nguyên                                                          */
/* ------------------------------------------------------------------ */

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		if ( in_array( $screen->post_type, array( 'post', 'av_product', 'av_project' ), true ) ) {
			wp_enqueue_script( 'auviet-checklist-filter', plugins_url( 'assets/js/checklist-filter.js', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/js/checklist-filter.js' ), true );
		}
		if ( ! in_array( $screen->post_type, av_shell_types(), true ) ) {
			return;
		}
		global $post;
		$type = $screen->post_type;
		$tabs = 'av_product' === $type ? array(
			array( 'id' => 'noidung', 'label' => 'Nội dung', 'sel' => array( '#av-card-content' ) ),
			array( 'id' => 'anh', 'label' => 'Ảnh', 'sel' => array( '#av_product_gallery' ) ),
			array( 'id' => 'quycach', 'label' => 'Quy cách, chất liệu', 'sel' => array( '#av_product_info' ) ),
			array( 'id' => 'gia', 'label' => 'Bảng giá', 'sel' => array( '#av_product_prices' ) ),
			array( 'id' => 'lienquan', 'label' => 'Liên quan, hỏi đáp', 'sel' => array( '#av_product_related' ) ),
			array( 'id' => 'seo', 'label' => 'Hiện trên Google', 'sel' => array( '#av_product_seo', '#rank_math_metabox' ) ),
		) : array();
		wp_enqueue_style( 'auviet-editor-shell', plugins_url( 'assets/css/editor-shell.css', AV_CORE_FILE ), array(), av_ver( AV_CORE_DIR . 'assets/css/editor-shell.css' ) );
		wp_enqueue_script( 'auviet-editor-shell', plugins_url( 'assets/js/editor-shell.js', AV_CORE_FILE ), array( 'jquery', 'wp-api-fetch', 'wp-url' ), av_ver( AV_CORE_DIR . 'assets/js/editor-shell.js' ), true );
		wp_localize_script(
			'auviet-editor-shell',
			'avShell',
			array(
				'type'     => $type,
				'postId'   => $post instanceof WP_Post ? $post->ID : 0,
				'tabs'     => array_values( array_filter( $tabs, static fn( $t ) => 'gia' !== $t['id'] || current_user_can( 'av_view_prices' ) || current_user_can( 'av_manage_prices' ) ) ),
				'sections' => 'av_project' === $type ? array(
					array( 'sel' => '#av-card-content', 'label' => '1. Dự án' ),
					array( 'sel' => '#av_project_client', 'label' => '2. Khách hàng' ),
					array( 'sel' => '#av_project_products', 'label' => '3. Sản phẩm và ngành' ),
					array( 'sel' => '#av_project_specs', 'label' => '4. Quy cách' ),
					array( 'sel' => '#av_project_gallery', 'label' => '5. Ảnh thật của đơn hàng' ),
				) : array(),
				'contentLabel' => 'av_project' === $type ? '1. Dự án' : 'Nội dung',
				'contentHelp'  => 'av_project' === $type ? 'Tên dự án mô tả sản phẩm và ngành, không cần tên khách.' : 'Phần khách đọc trên trang sản phẩm.',
				'editorLabel'  => 'av_project' === $type ? 'Câu chuyện dự án (tuỳ chọn)' : 'Thông tin chi tiết',
			)
		);
	},
	20
);

/* ------------------------------------------------------------------ */
/* Ảnh: ảnh đầu tiên là ảnh đại diện, mô tả ảnh nhập ngay dưới ảnh      */
/* ------------------------------------------------------------------ */

/**
 * Lưu hộp ảnh gộp (ảnh đại diện + thư viện ảnh) và mô tả từng ảnh. Gọi từ phần lưu của sản phẩm, mẫu đã làm.
 */
function av_save_unified_gallery( int $post_id ): void {
	// phpcs:disable WordPress.Security.NonceVerification -- hàm gọi đã kiểm tra nonce của hộp nhập liệu.
	if ( empty( $_POST['av_gallery_unified'] ) ) {
		return;
	}
	$ids = array_values( array_unique( array_filter( array_map( 'absint', explode( ',', (string) wp_unslash( $_POST['av_gallery'] ?? '' ) ) ), 'wp_attachment_is_image' ) ) );
	if ( $ids ) {
		set_post_thumbnail( $post_id, $ids[0] );
	} else {
		delete_post_thumbnail( $post_id );
	}
	update_post_meta( $post_id, '_av_gallery', array_slice( $ids, 1 ) );
	foreach ( (array) wp_unslash( $_POST['av_alt'] ?? array() ) as $aid => $alt ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$aid = absint( $aid );
		if ( $aid && in_array( $aid, $ids, true ) && current_user_can( 'edit_post', $aid ) ) {
			update_post_meta( $aid, '_wp_attachment_image_alt', sanitize_text_field( (string) $alt ) );
		}
	}
	// phpcs:enable
}

/**
 * Hộp ảnh gộp: kéo đổi thứ tự, ảnh đầu là ảnh đại diện, ô mô tả dưới mỗi ảnh.
 */
function av_box_unified_gallery( WP_Post $post ): void {
	$ids = av_post_images( $post->ID );
	?>
	<p class="description">Kéo để đổi thứ tự. <strong>Ảnh đầu tiên là ảnh đại diện.</strong> Mỗi ảnh cần một câu mô tả cho Google và người khiếm thị.</p>
	<ul class="av-ugallery" data-av-gallery data-unified="1">
		<?php
		foreach ( $ids as $id ) {
			$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
			printf(
				'<li data-id="%1$d" class="%2$s"><div class="av-ugallery__img">%3$s<button type="button" class="av-gallery__remove" aria-label="Bỏ ảnh">×</button></div><label>Mô tả ảnh<input type="text" name="av_alt[%1$d]" value="%4$s" placeholder="VD: Túi kraft in logo 1 màu" data-av-alt></label></li>',
				(int) $id,
				'' === trim( $alt ) ? 'is-noalt' : '',
				wp_get_attachment_image( $id, 'medium', false, array( 'alt' => '' ) ),
				esc_attr( $alt )
			);
		}
		?>
	</ul>
	<input type="hidden" name="av_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-av-gallery-input>
	<input type="hidden" name="av_gallery_unified" value="1">
	<p><button type="button" class="button av-ugallery__add" data-av-gallery-add>+ Tải ảnh lên hoặc chọn ảnh</button></p>
	<?php
}

/* ------------------------------------------------------------------ */
/* Hiện trên Google: tiêu đề, mô tả (lưu vào trường của Rank Math)      */
/* ------------------------------------------------------------------ */

function av_box_seo( WP_Post $post ): void {
	wp_nonce_field( 'av_seo', 'av_seo_nonce' );
	$title = (string) get_post_meta( $post->ID, 'rank_math_title', true );
	$desc  = (string) get_post_meta( $post->ID, 'rank_math_description', true );
	$link  = 'auto-draft' === $post->post_status ? home_url( '/' ) : get_permalink( $post );
	$crumb = preg_replace( '#^https?://#', '', untrailingslashit( (string) $link ) );
	?>
	<p class="description">Để trống thì dùng tên và mô tả ngắn. Google thường hiện khoảng 60 ký tự tiêu đề và 160 ký tự mô tả.</p>
	<div class="av-serp" aria-label="Xem trước trên Google">
		<div class="av-serp__url"><?php echo esc_html( str_replace( '/', ' › ', $crumb ) ); ?></div>
		<div class="av-serp__title" data-av-serp-title></div>
		<div class="av-serp__desc" data-av-serp-desc></div>
	</div>
	<p class="av-field">
		<label for="av-seo-title">Tiêu đề trên Google <span class="av-count" data-av-count="av-seo-title" data-max="60"></span></label>
		<input type="text" id="av-seo-title" name="av_seo_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post ) . ' | In Âu Việt' ); ?>">
	</p>
	<p class="av-field">
		<label for="av-seo-desc">Mô tả trên Google <span class="av-count" data-av-count="av-seo-desc" data-max="160"></span></label>
		<textarea id="av-seo-desc" name="av_seo_desc" rows="3" placeholder="<?php echo esc_attr( wp_trim_words( (string) $post->post_excerpt, 30, '…' ) ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
	</p>
	<script>
	( function () {
		var t = document.getElementById( 'av-seo-title' ), d = document.getElementById( 'av-seo-desc' );
		var pt = document.querySelector( '[data-av-serp-title]' ), pd = document.querySelector( '[data-av-serp-desc]' );
		function val( el, fallback ) { return el.value.trim() || ( fallback() || '' ); }
		function paint() {
			pt.textContent = val( t, function () { var x = document.getElementById( 'title' ); return ( x ? x.value : '' ) + ' | In Âu Việt'; } );
			pd.textContent = val( d, function () { var x = document.getElementById( 'excerpt' ); return x ? x.value : ''; } );
			document.querySelectorAll( '[data-av-count]' ).forEach( function ( c ) {
				var el = document.getElementById( c.getAttribute( 'data-av-count' ) ), n = el.value.trim().length, max = +c.getAttribute( 'data-max' );
				c.textContent = n ? n + ' / ' + max : 'trống, dùng mặc định';
				c.classList.toggle( 'is-over', n > max );
			} );
		}
		[ t, d, document.getElementById( 'title' ), document.getElementById( 'excerpt' ) ].forEach( function ( el ) { if ( el ) { el.addEventListener( 'input', paint ); } } );
		paint();
	} )();
	</script>
	<?php
}

add_action(
	'save_post_av_product',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['av_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_seo_nonce'] ), 'av_seo' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		foreach ( array( 'av_seo_title' => 'rank_math_title', 'av_seo_desc' => 'rank_math_description' ) as $field => $meta ) {
			$v = 'av_seo_desc' === $field ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ?? '' ) ) : sanitize_text_field( wp_unslash( $_POST[ $field ] ?? '' ) );
			'' === $v ? delete_post_meta( $post_id, $meta ) : update_post_meta( $post_id, $meta, $v );
		}
	}
);

// Thông báo sau khi lưu theo đúng loại nội dung.
add_filter(
	'post_updated_messages',
	static function ( array $m ): array {
		foreach ( array( 'av_product' => 'sản phẩm', 'av_project' => 'mẫu' ) as $type => $name ) {
			$m[ $type ] = array(
				1  => 'Đã lưu ' . $name . '.',
				4  => 'Đã lưu ' . $name . '.',
				6  => 'Đã đăng ' . $name . ', khách đã xem được.',
				7  => 'Đã lưu ' . $name . '.',
				8  => 'Đã gửi duyệt ' . $name . '.',
				9  => 'Đã hẹn giờ hiển thị ' . $name . '.',
				10 => 'Đã lưu bản nháp ' . $name . '.',
			);
		}
		return $m;
	}
);
