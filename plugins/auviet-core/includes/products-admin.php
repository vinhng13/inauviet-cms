<?php
/**
 * QT-03 Quản lý sản phẩm và QT-04 Bảng giá (giai đoạn 1) trong trang quản trị.
 * Biên tập viên sửa nội dung sản phẩm; chỉ người có quyền av_manage_prices (Quản trị viên) thấy và sửa giá.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Tài nguyên trang quản trị                                           */
/* ------------------------------------------------------------------ */

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		$is_product = in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && 'av_product' === $screen->post_type;
		$is_group   = in_array( $screen->base, array( 'edit-tags', 'term' ), true ) && 'av_group' === $screen->taxonomy;
		wp_enqueue_style( 'auviet-admin', plugins_url( 'assets/css/admin.css', AV_CORE_FILE ), array(), (string) filemtime( AV_CORE_DIR . 'assets/css/admin.css' ) );
		if ( ! $is_product && ! $is_group ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'auviet-product-admin', plugins_url( 'assets/js/product-admin.js', AV_CORE_FILE ), array( 'jquery', 'jquery-ui-sortable' ), (string) filemtime( AV_CORE_DIR . 'assets/js/product-admin.js' ), true );
	}
);

/* ------------------------------------------------------------------ */
/* Hộp nhập liệu trong trang sửa sản phẩm                              */
/* ------------------------------------------------------------------ */

add_action(
	'add_meta_boxes_av_product',
	static function () {
		add_meta_box( 'av_product_info', 'Quy cách và chất liệu', 'av_box_product_info', 'av_product', 'normal', 'high' );
		add_meta_box( 'av_product_gallery', 'Thư viện ảnh sản phẩm', 'av_box_product_gallery', 'av_product', 'normal', 'high' );
		if ( current_user_can( 'av_manage_prices' ) ) {
			add_meta_box( 'av_product_prices', 'Bảng giá tham khảo (chỉ Quản trị viên)', 'av_box_product_prices', 'av_product', 'normal', 'default' );
		}
		add_meta_box( 'av_product_related', 'Liên quan và hỏi đáp', 'av_box_product_related', 'av_product', 'normal', 'low' );
	}
);

function av_box_product_info( WP_Post $post ): void {
	wp_nonce_field( 'av_product_meta', 'av_product_nonce' );
	$groups  = get_terms( array( 'taxonomy' => 'av_group', 'hide_empty' => false ) );
	$primary = (int) get_post_meta( $post->ID, '_av_primary_group', true );
	$draft   = (bool) get_post_meta( $post->ID, '_av_spec_draft', true );
	$chosen  = array_map( 'intval', (array) get_post_meta( $post->ID, '_av_material' ) );
	?>
	<?php if ( $draft ) : ?>
		<div class="av-admin-note av-admin-note--warn">
			<p><strong>Quy cách đang là bản nháp tự điền khi chuyển từ web cũ.</strong> Hãy đối chiếu với thực tế sản xuất, sửa nếu cần rồi đánh dấu đã kiểm tra.</p>
			<label><input type="checkbox" name="av_spec_checked" value="1"> Đã kiểm tra quy cách và chất liệu</label>
		</div>
	<?php endif; ?>
	<p class="av-admin-row">
		<label for="av-primary-group"><strong>Nhóm chính</strong> (dùng cho đường dẫn "Trang chủ › Sản phẩm › Nhóm" và chọn sẵn nhu cầu trong form)</label><br>
		<select id="av-primary-group" name="av_primary_group">
			<option value="0">— Nhóm đầu tiên được chọn —</option>
			<?php
			foreach ( is_wp_error( $groups ) ? array() : $groups as $g ) {
				printf( '<option value="%d"%s>%s%s</option>', (int) $g->term_id, selected( $primary, $g->term_id, false ), $g->parent ? '— ' : '', esc_html( $g->name ) );
			}
			?>
		</select>
		<span class="description">Sản phẩm thuộc nhiều nhóm thì chọn các nhóm ở khung "Nhóm sản phẩm" bên phải; nhóm chính sẽ tự được thêm vào.</span>
	</p>
	<table class="form-table av-spec-table" role="presentation">
		<?php foreach ( av_spec_fields() as $key => $label ) : ?>
			<tr>
				<th scope="row"><label for="av-spec-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
				<td><input type="text" class="large-text" id="av-spec-<?php echo esc_attr( $key ); ?>" name="av_spec[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, '_av_spec_' . $key, true ) ); ?>"></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<th scope="row"><label for="av-spec-extra">Thông tin thêm</label></th>
			<td><textarea class="large-text" rows="3" id="av-spec-extra" name="av_spec_extra" placeholder="Mỗi dòng một mục, dạng Nhãn: giá trị. Ví dụ: Quai túi: dây dù"><?php echo esc_textarea( (string) get_post_meta( $post->ID, '_av_spec_extra', true ) ); ?></textarea>
			<p class="description">Dòng để trống thì không hiện trên trang.</p></td>
		</tr>
	</table>
	<h4>Chất liệu và gia công dùng cho sản phẩm</h4>
	<p class="description">Chọn từ <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=av_material' ) ); ?>">thư viện chất liệu</a>. Bảng quy cách trên trang sẽ dẫn tới trang của từng chất liệu.</p>
	<div class="av-check-cols">
		<?php
		foreach ( av_material_kinds() as $kind => $kind_label ) {
			$mats = get_posts(
				array(
					'post_type'   => 'av_material',
					'numberposts' => -1,
					'orderby'     => 'title',
					'order'       => 'ASC',
					'meta_key'    => '_av_kind', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'  => $kind, // phpcs:ignore WordPress.DB.SlowDBQuery
				)
			);
			echo '<fieldset><legend>' . esc_html( $kind_label ) . '</legend>';
			if ( ! $mats ) {
				echo '<p class="description">Chưa có.</p>';
			}
			foreach ( $mats as $m ) {
				printf( '<label><input type="checkbox" name="av_material[]" value="%d"%s> %s</label>', (int) $m->ID, checked( in_array( $m->ID, $chosen, true ), true, false ), esc_html( $m->post_title ) );
			}
			echo '</fieldset>';
		}
		?>
	</div>
	<?php
}

function av_box_product_gallery( WP_Post $post ): void {
	$ids = array_map( 'intval', (array) get_post_meta( $post->ID, '_av_gallery', true ) );
	?>
	<p class="description">Ảnh đại diện (khung "Ảnh đại diện" bên phải) luôn đứng đầu. Thêm các ảnh khác ở đây, kéo để đổi thứ tự. Ảnh thiếu mô tả (alt) được đánh dấu đỏ: bấm vào ảnh để thêm mô tả.</p>
	<ul class="av-gallery" data-av-gallery>
		<?php
		foreach ( $ids as $id ) {
			if ( ! wp_attachment_is_image( $id ) ) {
				continue;
			}
			$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
			printf(
				'<li data-id="%1$d" class="%3$s"><a href="%4$s" target="_blank" title="Sửa ảnh, mô tả">%2$s</a><button type="button" class="av-gallery__remove" aria-label="Bỏ ảnh">×</button></li>',
				(int) $id,
				wp_get_attachment_image( $id, 'thumbnail' ),
				'' === trim( $alt ) ? 'is-noalt' : '',
				esc_url( get_edit_post_link( $id ) )
			);
		}
		?>
	</ul>
	<input type="hidden" name="av_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-av-gallery-input>
	<p><button type="button" class="button" data-av-gallery-add>Thêm ảnh</button></p>
	<?php
}

function av_box_product_prices( WP_Post $post ): void {
	wp_nonce_field( 'av_product_prices', 'av_prices_nonce' );
	$p = av_product_prices( $post->ID );
	if ( ! $p['tiers'] ) {
		$p['tiers'] = array( 100, 500, 1000 );
	}
	if ( ! $p['rows'] ) {
		$p['rows'] = array( array( 'label' => '', 'prices' => array_fill( 0, count( $p['tiers'] ), null ) ) );
	}
	?>
	<p class="description">Giá hiện trên trang sản phẩm là <strong>giá tham khảo</strong>. Bảng để trống thì trang hiện "Liên hệ báo giá". Mỗi dòng là một quy cách (khổ, giấy, gia công), mỗi cột là một bậc số lượng. Nhập số, không cần dấu chấm.</p>
	<p>
		<label>Giá trong bảng là
			<select name="av_prices[basis]">
				<option value="total"<?php selected( $p['basis'], 'total' ); ?>>Thành tiền cả lô</option>
				<option value="unit"<?php selected( $p['basis'], 'unit' ); ?>>Đơn giá mỗi đơn vị</option>
			</select>
		</label>
		<label>Đơn vị tính <input type="text" name="av_prices[unit]" value="<?php echo esc_attr( $p['unit'] ); ?>" size="8" placeholder="cái"></label>
	</p>
	<div class="av-price-wrap">
		<table class="widefat av-price-table" data-av-prices>
			<thead>
				<tr>
					<th>Quy cách</th>
					<?php foreach ( $p['tiers'] as $q ) : ?>
						<th><input type="number" min="1" name="av_prices[tiers][]" value="<?php echo esc_attr( (string) $q ); ?>" aria-label="Số lượng"><button type="button" class="av-x" data-av-del-col aria-label="Xoá cột">×</button></th>
					<?php endforeach; ?>
					<th><button type="button" class="button" data-av-add-col>+ Bậc số lượng</button></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $p['rows'] as $i => $r ) : ?>
					<tr>
						<td><input type="text" name="av_prices[rows][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $r['label'] ); ?>" placeholder="VD: A5, Couche 150, in 2 mặt"></td>
						<?php foreach ( $r['prices'] as $v ) : ?>
							<td><input type="number" min="0" step="1" name="av_prices[rows][<?php echo (int) $i; ?>][prices][]" value="<?php echo esc_attr( null === $v ? '' : (string) $v ); ?>"></td>
						<?php endforeach; ?>
						<td><button type="button" class="av-x" data-av-del-row aria-label="Xoá dòng">×</button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<p><button type="button" class="button" data-av-add-row>+ Dòng quy cách</button></p>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="av-moq">Số lượng tối thiểu</label></th>
			<td><input type="text" id="av-moq" name="av_moq" value="<?php echo esc_attr( av_product_moq( $post->ID ) ); ?>" placeholder="VD: 100 cái"></td>
		</tr>
		<tr>
			<th scope="row"><label for="av-lead">Thời gian sản xuất</label></th>
			<td><input type="text" class="regular-text" id="av-lead" name="av_lead_time" value="<?php echo esc_attr( av_product_lead_time( $post->ID ) ); ?>" placeholder="VD: 3–5 ngày làm việc sau khi duyệt file"></td>
		</tr>
		<tr>
			<th scope="row"><label for="av-price-note">Ghi chú dưới bảng giá</label></th>
			<td><input type="text" class="large-text" id="av-price-note" name="av_prices[note]" value="<?php echo esc_attr( $p['note'] ); ?>" placeholder="Để trống dùng câu mặc định"></td>
		</tr>
	</table>
	<?php av_render_price_history( $post->ID ); ?>
	<?php
}

function av_box_product_related( WP_Post $post ): void {
	$related  = array_map( 'intval', (array) get_post_meta( $post->ID, '_av_related', true ) );
	$rel_post = array_map( 'intval', (array) get_post_meta( $post->ID, '_av_related_posts', true ) );
	$faqs     = array_map( 'intval', (array) get_post_meta( $post->ID, '_av_faqs', true ) );
	$products = get_posts( array( 'post_type' => 'av_product', 'numberposts' => -1, 'post_status' => array( 'publish', 'draft' ), 'orderby' => 'title', 'order' => 'ASC', 'exclude' => array( $post->ID ) ) );
	$articles = get_posts( array( 'post_type' => 'post', 'numberposts' => 200, 'post_status' => 'publish' ) );
	$faq_list = get_posts( array( 'post_type' => 'av_faq', 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
	?>
	<div class="av-rel-cols">
		<p>
			<label for="av-related"><strong>Sản phẩm liên quan</strong></label><br>
			<select id="av-related" name="av_related[]" multiple size="8">
				<?php foreach ( $products as $p ) : ?>
					<option value="<?php echo (int) $p->ID; ?>"<?php selected( in_array( $p->ID, $related, true ) ); ?>><?php echo esc_html( $p->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="description">Giữ Ctrl để chọn nhiều. Chọn thiếu thì trang tự lấy thêm sản phẩm cùng nhóm chính.</span>
		</p>
		<p>
			<label for="av-related-posts"><strong>Bài viết liên quan</strong></label><br>
			<select id="av-related-posts" name="av_related_posts[]" multiple size="8">
				<?php foreach ( $articles as $a ) : ?>
					<option value="<?php echo (int) $a->ID; ?>"<?php selected( in_array( $a->ID, $rel_post, true ) ); ?>><?php echo esc_html( $a->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php if ( ! $articles ) : ?><span class="description">Chưa có bài viết nào trong Kiến thức in ấn.</span><?php endif; ?>
		</p>
	</div>
	<fieldset class="av-faq-pick">
		<legend><strong>Câu hỏi thường gặp hiện cuối trang</strong></legend>
		<?php if ( ! $faq_list ) : ?>
			<p class="description">Chưa có câu hỏi. <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=av_faq' ) ); ?>">Thêm câu hỏi</a> rồi quay lại chọn.</p>
		<?php endif; ?>
		<?php foreach ( $faq_list as $f ) : ?>
			<label><input type="checkbox" name="av_faqs[]" value="<?php echo (int) $f->ID; ?>"<?php checked( in_array( $f->ID, $faqs, true ) ); ?>> <?php echo esc_html( $f->post_title ); ?></label>
		<?php endforeach; ?>
	</fieldset>
	<?php
}

/* ------------------------------------------------------------------ */
/* Lưu                                                                 */
/* ------------------------------------------------------------------ */

add_action(
	'save_post_av_product',
	static function ( int $post_id ) {
		if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- làm sạch từng trường bên dưới.
		if ( isset( $_POST['av_product_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['av_product_nonce'] ), 'av_product_meta' ) && current_user_can( 'edit_post', $post_id ) ) {
			// Quy cách.
			foreach ( array_keys( av_spec_fields() ) as $key ) {
				$v = sanitize_text_field( wp_unslash( $_POST['av_spec'][ $key ] ?? '' ) );
				'' === $v ? delete_post_meta( $post_id, '_av_spec_' . $key ) : update_post_meta( $post_id, '_av_spec_' . $key, $v );
			}
			update_post_meta( $post_id, '_av_spec_extra', sanitize_textarea_field( wp_unslash( $_POST['av_spec_extra'] ?? '' ) ) );
			if ( ! empty( $_POST['av_spec_checked'] ) ) {
				delete_post_meta( $post_id, '_av_spec_draft' );
			}

			// Nhóm chính: phải là một trong các nhóm của sản phẩm, chưa có thì thêm vào.
			$primary = absint( $_POST['av_primary_group'] ?? 0 );
			if ( $primary && term_exists( $primary, 'av_group' ) ) {
				update_post_meta( $post_id, '_av_primary_group', $primary );
				if ( ! has_term( $primary, 'av_group', $post_id ) ) {
					wp_set_object_terms( $post_id, array( $primary ), 'av_group', true );
				}
			} else {
				delete_post_meta( $post_id, '_av_primary_group' );
			}

			// Chất liệu: mỗi chất liệu một dòng meta để lọc được.
			delete_post_meta( $post_id, '_av_material' );
			foreach ( array_unique( array_map( 'absint', (array) ( $_POST['av_material'] ?? array() ) ) ) as $mid ) {
				if ( $mid && 'av_material' === get_post_type( $mid ) ) {
					add_post_meta( $post_id, '_av_material', $mid );
				}
			}

			// Thư viện ảnh.
			$gallery = array_values( array_filter( array_map( 'absint', explode( ',', (string) wp_unslash( $_POST['av_gallery'] ?? '' ) ) ), 'wp_attachment_is_image' ) );
			update_post_meta( $post_id, '_av_gallery', $gallery );

			// Liên quan, hỏi đáp.
			update_post_meta( $post_id, '_av_related', array_values( array_diff( array_map( 'absint', (array) ( $_POST['av_related'] ?? array() ) ), array( $post_id, 0 ) ) ) );
			update_post_meta( $post_id, '_av_related_posts', array_values( array_filter( array_map( 'absint', (array) ( $_POST['av_related_posts'] ?? array() ) ) ) ) );
			update_post_meta( $post_id, '_av_faqs', array_values( array_filter( array_map( 'absint', (array) ( $_POST['av_faqs'] ?? array() ) ) ) ) );
		}

		// Bảng giá: chỉ Quản trị viên, nonce riêng. Biên tập viên lưu sản phẩm không làm mất giá.
		if ( isset( $_POST['av_prices_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['av_prices_nonce'] ), 'av_product_prices' ) && current_user_can( 'av_manage_prices' ) ) {
			$raw   = (array) wp_unslash( $_POST['av_prices'] ?? array() );
			$old   = av_price_snapshot( $post_id );
			$new_p = av_sanitize_prices( $raw );
			update_post_meta( $post_id, '_av_prices', $new_p );
			update_post_meta( $post_id, '_av_moq', sanitize_text_field( wp_unslash( $_POST['av_moq'] ?? '' ) ) );
			update_post_meta( $post_id, '_av_lead_time', sanitize_text_field( wp_unslash( $_POST['av_lead_time'] ?? '' ) ) );
			$new = av_price_snapshot( $post_id );
			if ( $old !== $new ) {
				av_price_log( $post_id, $old, $new );
			}
		}
		// phpcs:enable
	}
);

/**
 * Làm sạch bảng giá gửi lên: bỏ cột không có số lượng, bỏ dòng trống hoàn toàn.
 */
function av_sanitize_prices( array $raw ): array {
	$tiers = array();
	$keep  = array();
	foreach ( (array) ( $raw['tiers'] ?? array() ) as $i => $q ) {
		$q = absint( $q );
		if ( $q > 0 ) {
			$tiers[] = $q;
			$keep[]  = $i;
		}
	}
	$rows = array();
	foreach ( (array) ( $raw['rows'] ?? array() ) as $r ) {
		$label  = sanitize_text_field( (string) ( $r['label'] ?? '' ) );
		$prices = array();
		$vals   = array_values( (array) ( $r['prices'] ?? array() ) );
		foreach ( $keep as $i ) {
			$v        = preg_replace( '/[^0-9]/', '', (string) ( $vals[ $i ] ?? '' ) );
			$prices[] = '' === $v ? null : (int) $v;
		}
		if ( '' === $label && ! array_filter( $prices, static fn( $v ) => null !== $v ) ) {
			continue;
		}
		$rows[] = array(
			'label'  => $label,
			'prices' => $prices,
		);
	}
	return array(
		'tiers' => $tiers,
		'rows'  => $rows,
		'basis' => in_array( $raw['basis'] ?? '', array( 'total', 'unit' ), true ) ? $raw['basis'] : 'total',
		'unit'  => sanitize_text_field( (string) ( $raw['unit'] ?? 'cái' ) ) ?: 'cái',
		'note'  => sanitize_text_field( (string) ( $raw['note'] ?? '' ) ),
	);
}

/* ------------------------------------------------------------------ */
/* Lịch sử giá (QT-04: người sửa, thời điểm, giá cũ, giá mới)          */
/* ------------------------------------------------------------------ */

function av_price_snapshot( int $post_id ): array {
	$p = av_product_prices( $post_id );
	if ( ! $p['rows'] ) {
		$p['tiers'] = array();
	}
	return array(
		'prices' => $p,
		'moq'    => av_product_moq( $post_id ),
		'lead'   => av_product_lead_time( $post_id ),
	);
}

function av_price_log( int $post_id, array $old, array $new ): void {
	global $wpdb;
	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prefix . 'av_price_log',
		array(
			'product_id' => $post_id,
			'user_id'    => get_current_user_id(),
			'changed_at' => current_time( 'mysql' ),
			'old_value'  => wp_json_encode( $old ),
			'new_value'  => wp_json_encode( $new ),
		)
	);
	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Sửa bảng giá sản phẩm "{title}"', array( 'title' => get_the_title( $post_id ) ) );
	}
}

/**
 * Các thay đổi giữa hai bản giá, dạng câu đọc được.
 *
 * @return string[]
 */
function av_price_diff( array $old, array $new ): array {
	$cells = static function ( array $snap ): array {
		$out = array();
		$p   = $snap['prices'] ?? av_price_defaults();
		foreach ( (array) ( $p['rows'] ?? array() ) as $r ) {
			foreach ( (array) ( $p['tiers'] ?? array() ) as $i => $q ) {
				$v = $r['prices'][ $i ] ?? null;
				if ( null !== $v ) {
					$out[ ( $r['label'] ?: '(không tên)' ) . ' · ' . number_format( (int) $q, 0, ',', '.' ) . ' ' . ( $p['unit'] ?? '' ) ] = (int) $v;
				}
			}
		}
		return $out;
	};
	$a     = $cells( $old );
	$b     = $cells( $new );
	$lines = array();
	foreach ( array_unique( array_merge( array_keys( $a ), array_keys( $b ) ) ) as $k ) {
		$va = $a[ $k ] ?? null;
		$vb = $b[ $k ] ?? null;
		if ( $va !== $vb ) {
			$lines[] = sprintf( '%s: %s → %s', $k, null === $va ? 'trống' : av_format_vnd( $va ), null === $vb ? 'trống' : av_format_vnd( $vb ) );
		}
	}
	foreach ( array( 'moq' => 'Số lượng tối thiểu', 'lead' => 'Thời gian sản xuất' ) as $k => $label ) {
		if ( ( $old[ $k ] ?? '' ) !== ( $new[ $k ] ?? '' ) ) {
			$lines[] = sprintf( '%s: %s → %s', $label, ( $old[ $k ] ?? '' ) ?: 'trống', ( $new[ $k ] ?? '' ) ?: 'trống' );
		}
	}
	foreach ( array( 'basis' => 'Cách tính', 'unit' => 'Đơn vị', 'note' => 'Ghi chú' ) as $k => $label ) {
		$x = $old['prices'][ $k ] ?? '';
		$y = $new['prices'][ $k ] ?? '';
		if ( $x !== $y ) {
			$lines[] = sprintf( '%s: %s → %s', $label, $x ?: 'trống', $y ?: 'trống' );
		}
	}
	return $lines;
}

function av_render_price_history( int $post_id ): void {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}av_price_log WHERE product_id = %d ORDER BY id DESC LIMIT 20", $post_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	echo '<h4>Lịch sử thay đổi giá</h4>';
	if ( ! $rows ) {
		echo '<p class="description">Chưa có thay đổi nào.</p>';
		return;
	}
	echo '<ul class="av-price-history">';
	foreach ( $rows as $r ) {
		$user  = get_userdata( (int) $r->user_id );
		$lines = av_price_diff( (array) json_decode( $r->old_value, true ), (array) json_decode( $r->new_value, true ) );
		printf(
			'<li><strong>%s</strong> · %s<ul>%s</ul></li>',
			esc_html( mysql2date( 'd/m/Y H:i', $r->changed_at ) ),
			esc_html( $user ? $user->display_name : 'Hệ thống' ),
			implode( '', array_map( static fn( $l ) => '<li>' . esc_html( $l ) . '</li>', $lines ?: array( 'Không đổi giá trị' ) ) )
		);
	}
	echo '</ul>';
}

/* ------------------------------------------------------------------ */
/* Danh sách sản phẩm                                                  */
/* ------------------------------------------------------------------ */

add_filter(
	'manage_av_product_posts_columns',
	static function ( array $cols ): array {
		$out = array();
		foreach ( $cols as $k => $v ) {
			if ( 'title' === $k ) {
				$out['av_thumb'] = '<span class="screen-reader-text">Ảnh</span>';
			}
			$out[ $k ] = $v;
			if ( 'title' === $k ) {
				$out['av_primary'] = 'Nhóm chính';
				$out['av_photos']  = 'Ảnh';
				if ( current_user_can( 'av_manage_prices' ) ) {
					$out['av_price'] = 'Giá tham khảo';
				}
				$out['av_check'] = 'Quy cách';
			}
		}
		return $out;
	}
);

add_action(
	'manage_av_product_posts_custom_column',
	static function ( string $col, int $post_id ) {
		switch ( $col ) {
			case 'av_thumb':
				echo get_the_post_thumbnail( $post_id, array( 48, 48 ) ) ?: '<span class="av-nothumb" title="Chưa có ảnh đại diện">—</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
				break;
			case 'av_primary':
				$g = av_product_primary_group( $post_id );
				echo $g ? esc_html( $g->name ) : '<span class="av-warn">Chưa chọn nhóm</span>';
				break;
			case 'av_photos':
				echo (int) count( av_product_gallery( $post_id ) );
				break;
			case 'av_price':
				$min = av_product_min_price( $post_id );
				echo null === $min ? '<span class="description">Liên hệ báo giá</span>' : esc_html( 'Từ ' . av_format_vnd( $min ) );
				break;
			case 'av_check':
				echo get_post_meta( $post_id, '_av_spec_draft', true ) ? '<span class="av-warn">Cần kiểm tra</span>' : '<span class="av-ok">Đã kiểm tra</span>';
				break;
		}
	},
	10,
	2
);

/* ------------------------------------------------------------------ */
/* Nhóm sản phẩm: ảnh, đoạn giới thiệu, câu hỏi thường gặp (SP-01)    */
/* ------------------------------------------------------------------ */

function av_group_fields_html( ?WP_Term $term ): void {
	$thumb = $term ? av_group_thumb( $term ) : 0;
	$intro = $term ? av_group_intro( $term ) : '';
	$faqs  = $term ? array_map( 'intval', (array) get_term_meta( $term->term_id, 'av_faqs', true ) ) : array();
	$list  = get_posts( array( 'post_type' => 'av_faq', 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
	wp_nonce_field( 'av_group_meta', 'av_group_nonce' );
	?>
	<tr class="form-field">
		<th scope="row">Ảnh nhóm</th>
		<td>
			<div class="av-term-thumb" data-av-term-thumb><?php echo $thumb ? wp_get_attachment_image( $thumb, 'thumbnail' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<input type="hidden" name="av_thumb_id" value="<?php echo (int) $thumb; ?>" data-av-term-thumb-input>
			<button type="button" class="button" data-av-term-thumb-pick>Chọn ảnh</button>
			<button type="button" class="button-link" data-av-term-thumb-clear>Bỏ ảnh</button>
			<p class="description">Hiện trên thẻ nhóm ở trang chủ và đầu trang nhóm.</p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="av-intro">Đoạn giới thiệu nhóm</label></th>
		<td>
			<?php if ( $term && get_term_meta( $term->term_id, 'av_intro_draft', true ) ) : ?>
				<p class="av-warn">Đoạn này là bản nháp, cần Âu Việt đọc lại. Lưu nhóm để bỏ đánh dấu nháp.</p>
			<?php endif; ?>
			<textarea id="av-intro" name="av_intro" rows="8"><?php echo esc_textarea( $intro ); ?></textarea>
			<p class="description">150–300 chữ, hiện đầu trang nhóm. Có thể dùng thẻ đoạn &lt;p&gt;, &lt;strong&gt;, &lt;a&gt;. Ô "Mô tả" phía trên là câu ngắn trên thẻ nhóm ở trang chủ.</p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row">Câu hỏi thường gặp</th>
		<td class="av-faq-pick">
			<?php if ( ! $list ) : ?>
				<p class="description">Chưa có câu hỏi. Thêm trong mục Hỏi đáp rồi quay lại chọn.</p>
			<?php endif; ?>
			<?php foreach ( $list as $f ) : ?>
				<label><input type="checkbox" name="av_faqs[]" value="<?php echo (int) $f->ID; ?>"<?php checked( in_array( $f->ID, $faqs, true ) ); ?>> <?php echo esc_html( $f->post_title ); ?></label>
			<?php endforeach; ?>
		</td>
	</tr>
	<?php
}

add_action(
	'av_group_edit_form_fields',
	static function ( WP_Term $term ) {
		av_group_fields_html( $term );
	}
);

add_action(
	'av_group_add_form_fields',
	static function () {
		echo '<table class="form-table" role="presentation">';
		av_group_fields_html( null );
		echo '</table>';
	}
);

function av_save_group_meta( int $term_id ): void {
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! isset( $_POST['av_group_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_group_nonce'] ), 'av_group_meta' ) || ! current_user_can( 'edit_av_products' ) ) {
		return;
	}
	$thumb = absint( $_POST['av_thumb_id'] ?? 0 );
	$thumb && wp_attachment_is_image( $thumb ) ? update_term_meta( $term_id, 'av_thumb_id', $thumb ) : delete_term_meta( $term_id, 'av_thumb_id' );
	update_term_meta( $term_id, 'av_intro', wp_kses_post( wp_unslash( $_POST['av_intro'] ?? '' ) ) );
	update_term_meta( $term_id, 'av_faqs', array_values( array_filter( array_map( 'absint', (array) ( $_POST['av_faqs'] ?? array() ) ) ) ) );
	delete_term_meta( $term_id, 'av_intro_draft' );
	// phpcs:enable
}
add_action( 'created_av_group', 'av_save_group_meta' );
add_action( 'edited_av_group', 'av_save_group_meta' );

/* ------------------------------------------------------------------ */
/* Chất liệu: loại (giấy hay gia công)                                 */
/* ------------------------------------------------------------------ */

add_action(
	'add_meta_boxes_av_material',
	static function () {
		add_meta_box(
			'av_material_kind',
			'Loại',
			static function ( WP_Post $post ) {
				wp_nonce_field( 'av_material_meta', 'av_material_nonce' );
				$kind = (string) get_post_meta( $post->ID, '_av_kind', true ) ?: 'giay';
				foreach ( av_material_kinds() as $k => $label ) {
					printf( '<label style="display:block;margin:4px 0"><input type="radio" name="av_kind" value="%s"%s> %s</label>', esc_attr( $k ), checked( $kind, $k, false ), esc_html( $label ) );
				}
				echo '<p class="description">Mô tả đặc điểm, dùng cho sản phẩm nào ở khung nội dung; ảnh thật đặt ở Ảnh đại diện. Danh sách sản phẩm dùng chất liệu này tự hiện trên trang.</p>';
			},
			'av_material',
			'side'
		);
	}
);

add_action(
	'save_post_av_material',
	static function ( int $post_id ) {
		if ( ! isset( $_POST['av_material_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['av_material_nonce'] ), 'av_material_meta' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$kind = sanitize_key( wp_unslash( $_POST['av_kind'] ?? 'giay' ) );
		update_post_meta( $post_id, '_av_kind', isset( av_material_kinds()[ $kind ] ) ? $kind : 'giay' );
	}
);
