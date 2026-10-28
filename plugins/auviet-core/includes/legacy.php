<?php
/**
 * Chuyển dữ liệu từ web cũ inquangcaoauviet.com và chuyển hướng 301 đường dẫn cũ (mục 3 Tài liệu mô tả chức năng).
 *
 * - Công cụ › Nhập từ web cũ (chỉ Quản trị viên, không chạy ở production): website tự tải trang cũ, lấy mô tả,
 *   nội dung và ảnh, tạo sản phẩm mới, thư viện chất liệu, đoạn giới thiệu nhóm và ảnh trang chủ.
 * - Đường dẫn cũ (/pddetail/...-509-3184.html, /pd/...-509.html) chuyển 301 về trang mới tương ứng.
 */

defined( 'ABSPATH' ) || exit;

require_once AV_CORE_DIR . 'includes/legacy-data.php';

/* ------------------------------------------------------------------ */
/* Chuyển hướng 301 đường dẫn cũ                                       */
/* ------------------------------------------------------------------ */

add_action(
	'template_redirect',
	static function () {
		if ( ! is_404() ) {
			return;
		}
		$path = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$path = rawurldecode( $path );
		$to   = '';
		if ( preg_match( '#/pddetail/.*-(\d{3})-(\d{3,5})\.html$#u', $path, $m ) ) {
			$map = (array) get_option( 'av_legacy_map', array() );
			$pid = (int) ( $map[ $m[2] ] ?? 0 );
			if ( $pid && 'publish' === get_post_status( $pid ) ) {
				$to = get_permalink( $pid );
			} else {
				$to = home_url( av_legacy_category_map()[ $m[1] ] ?? '/san-pham/' );
			}
		} elseif ( preg_match( '#/pd/.*-(\d{3})\.html$#u', $path, $m ) ) {
			$to = home_url( av_legacy_category_map()[ $m[1] ] ?? '/san-pham/' );
		} elseif ( preg_match( '#^/contact-us\.html$#', $path ) ) {
			$to = home_url( '/lien-he/' );
		}
		if ( $to ) {
			wp_safe_redirect( $to, 301, 'Au Viet legacy' );
			exit;
		}
	},
	1
);

/* ------------------------------------------------------------------ */
/* Trang công cụ                                                       */
/* ------------------------------------------------------------------ */

function av_legacy_enabled(): bool {
	return ! av_is_production() && current_user_can( 'manage_options' );
}

add_action(
	'admin_menu',
	static function () {
		if ( ! av_legacy_enabled() ) {
			return;
		}
		add_management_page( 'Nhập từ web cũ', 'Nhập từ web cũ', 'manage_options', 'av-legacy-import', 'av_legacy_page' );
	}
);

function av_legacy_page(): void {
	$products = av_legacy_products();
	$sources  = array_sum( array_map( static fn( $p ) => count( $p['sources'] ), $products ) );
	$last     = get_option( 'av_legacy_last_run' );
	?>
	<div class="wrap">
		<h1>Nhập từ web cũ</h1>
		<p>Website tự tải <?php echo (int) $sources; ?> trang sản phẩm cũ trên <code><?php echo esc_html( av_legacy_base() ); ?></code>, gom thành <?php echo (int) count( $products ); ?> sản phẩm mới theo 5 nhóm, kèm ảnh, mô tả và nội dung. Đồng thời tạo thư viện chất liệu, đoạn giới thiệu nhóm, ảnh cho trang chủ và chuyển hướng 301 từ đường dẫn cũ.</p>
		<ul class="ul-disc">
			<li>Chạy lại được nhiều lần: ảnh đã tải không tải lại; sản phẩm đã được sửa tay sau lần nhập trước thì giữ nguyên nội dung, chỉ bổ sung ảnh còn thiếu.</li>
			<li>Quy cách và đoạn giới thiệu nhóm là bản nháp, được đánh dấu "Cần kiểm tra" trong danh sách sản phẩm.</li>
			<li>Giá không nhập từ web cũ: sản phẩm chưa có bảng giá sẽ hiện "Liên hệ báo giá".</li>
			<li>Công cụ này chỉ có ở môi trường thử (local, staging), không có trên website thật.</li>
		</ul>
		<?php if ( $last ) : ?>
			<p><strong>Lần chạy gần nhất:</strong> <?php echo esc_html( (string) $last ); ?></p>
		<?php endif; ?>
		<p><label><input type="checkbox" id="av-legacy-force"> Ghi đè cả sản phẩm đã sửa tay sau lần nhập trước (mất phần nội dung, quy cách đã sửa; không đụng tới bảng giá)</label></p>
		<p><button type="button" class="button button-primary button-hero" id="av-legacy-start">Bắt đầu nhập</button></p>
		<div id="av-legacy-progress" hidden>
			<progress id="av-legacy-bar" value="0" max="100" style="width:100%;max-width:640px;height:20px"></progress>
			<p id="av-legacy-status" aria-live="polite"></p>
			<ol id="av-legacy-log" style="max-height:420px;overflow:auto;background:#fff;border:1px solid #dcdcde;padding:8px 8px 8px 32px;max-width:900px"></ol>
		</div>
	</div>
	<script>
	( function () {
		var btn = document.getElementById( 'av-legacy-start' );
		var nonce = <?php echo wp_json_encode( wp_create_nonce( 'av_legacy' ) ); ?>;
		var ajax = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var log = document.getElementById( 'av-legacy-log' );
		var bar = document.getElementById( 'av-legacy-bar' );
		var status = document.getElementById( 'av-legacy-status' );
		function line( text, bad ) {
			var li = document.createElement( 'li' );
			li.textContent = text;
			if ( bad ) { li.style.color = '#b32d2e'; }
			log.appendChild( li );
			log.scrollTop = log.scrollHeight;
		}
		function call( step, data ) {
			var body = new FormData();
			body.append( 'action', 'av_legacy_' + step );
			body.append( '_ajax_nonce', nonce );
			Object.keys( data || {} ).forEach( function ( k ) { body.append( k, data[ k ] ); } );
			return fetch( ajax, { method: 'POST', body: body, credentials: 'same-origin' } ).then( function ( r ) {
				return r.json().catch( function () { return { success: false, data: 'Máy chủ trả lỗi ' + r.status }; } );
			} );
		}
		btn.addEventListener( 'click', async function () {
			btn.disabled = true;
			document.getElementById( 'av-legacy-progress' ).hidden = false;
			status.textContent = 'Đang chuẩn bị…';
			var prep = await call( 'prepare' );
			if ( ! prep.success ) { line( 'Lỗi: ' + prep.data, true ); btn.disabled = false; return; }
			line( prep.data.message );
			var jobs = prep.data.jobs;
			bar.max = jobs.length + 1;
			for ( var i = 0; i < jobs.length; i++ ) {
				status.textContent = 'Đang nhập ' + ( i + 1 ) + '/' + jobs.length + ': ' + jobs[ i ].label;
				var r = await call( 'source', { slug: jobs[ i ].slug, index: jobs[ i ].index, force: document.getElementById( 'av-legacy-force' ).checked ? 1 : 0 } );
				line( ( r.success ? '' : 'Lỗi: ' ) + ( r.data && r.data.message ? r.data.message : r.data ), ! r.success );
				bar.value = i + 1;
			}
			status.textContent = 'Đang hoàn tất (ảnh trang chủ, ảnh nhóm)…';
			var fin = await call( 'finish' );
			line( fin.success ? fin.data.message : 'Lỗi: ' + fin.data, ! fin.success );
			bar.value = bar.max;
			status.textContent = 'Xong.';
			btn.disabled = false;
		} );
	} )();
	</script>
	<?php
}

/* ------------------------------------------------------------------ */
/* Các bước nhập (admin-ajax)                                          */
/* ------------------------------------------------------------------ */

function av_legacy_guard(): void {
	if ( ! av_legacy_enabled() || ! check_ajax_referer( 'av_legacy', false, false ) ) {
		wp_send_json_error( 'Không có quyền hoặc phiên đã hết hạn, tải lại trang.' );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
}

add_action(
	'wp_ajax_av_legacy_prepare',
	static function () {
		av_legacy_guard();
		$made = 0;
		// Thư viện chất liệu.
		foreach ( av_legacy_materials() as $slug => $m ) {
			if ( get_page_by_path( $slug, OBJECT, 'av_material' ) ) {
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'av_material',
					'post_status'  => 'publish',
					'post_name'    => $slug,
					'post_title'   => $m[1],
					'post_excerpt' => $m[2],
					'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $m[2] ) . '</p><!-- /wp:paragraph -->' . "\n\n" . '<!-- wp:paragraph --><p>' . esc_html( $m[3] ) . '</p><!-- /wp:paragraph -->',
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_av_kind', $m[0] );
				++$made;
			}
		}
		// Đoạn giới thiệu nhóm (chỉ điền khi còn trống).
		foreach ( av_legacy_group_intros() as $slug => $html ) {
			$t = get_term_by( 'slug', $slug, 'av_group' );
			if ( $t && '' === trim( av_group_intro( $t ) ) ) {
				update_term_meta( $t->term_id, 'av_intro', $html );
				update_term_meta( $t->term_id, 'av_intro_draft', 1 );
			}
		}
		// Sản phẩm mẫu: trùng đường dẫn thì dùng lại, còn lại bỏ vào thùng rác.
		$keep    = array_keys( av_legacy_products() );
		$trashed = 0;
		foreach ( get_posts( array( 'post_type' => 'av_product', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_av_sample', 'meta_value' => 1 ) ) as $p ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( in_array( $p->post_name, $keep, true ) ) {
				continue;
			}
			wp_trash_post( $p->ID );
			++$trashed;
		}
		$jobs = array();
		foreach ( av_legacy_products() as $slug => $p ) {
			foreach ( $p['sources'] as $i => $src ) {
				$jobs[] = array(
					'slug'  => $slug,
					'index' => $i,
					'label' => $p['title'] . ( $i ? ' (ảnh từ trang cũ ' . ( $i + 1 ) . ')' : '' ),
				);
			}
		}
		wp_send_json_success(
			array(
				'message' => sprintf( 'Đã tạo %d chất liệu, điền giới thiệu nhóm, bỏ %d sản phẩm mẫu vào thùng rác. Bắt đầu tải %d trang cũ.', $made, $trashed, count( $jobs ) ),
				'jobs'    => $jobs,
			)
		);
	}
);

add_action(
	'wp_ajax_av_legacy_source',
	static function () {
		av_legacy_guard();
		$slug  = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$index = absint( $_POST['index'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		$all   = av_legacy_products();
		if ( ! isset( $all[ $slug ]['sources'][ $index ] ) ) {
			wp_send_json_error( 'Không có trang cũ này trong danh sách.' );
		}
		$def  = $all[ $slug ];
		$path = $def['sources'][ $index ];

		$page = av_legacy_fetch( $path );
		if ( is_wp_error( $page ) ) {
			wp_send_json_error( $def['title'] . ': không tải được ' . $path . ' (' . $page->get_error_message() . ')' );
		}

		$post_id = av_legacy_product_id( $slug, $def );
		if ( ! $post_id ) {
			wp_send_json_error( $def['title'] . ': không tạo được sản phẩm.' );
		}
		$fresh  = ! empty( $_POST['force'] ) || av_legacy_is_fresh( $post_id ); // phpcs:ignore WordPress.Security.NonceVerification
		$images = 0;

		// Ảnh trình chiếu của trang cũ vào thư viện ảnh sản phẩm.
		$gallery = array_map( 'intval', (array) get_post_meta( $post_id, '_av_gallery', true ) );
		$slides  = array();
		foreach ( $page['slides'] as $url ) {
			$aid = av_legacy_sideload( $url, $post_id, $def['title'] );
			if ( $aid ) {
				$slides[] = $aid;
				++$images;
			}
		}

		if ( 0 === $index && $fresh ) {
			$content = $page['content'] ? av_legacy_blocks( $page['content'], $post_id, $def['title'], $images ) : '';
			kses_remove_filters();
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_title'   => $def['title'],
					'post_excerpt' => wp_trim_words( $page['intro'], 45, '…' ),
					'post_content' => $content,
					'menu_order'   => (int) ( $def['order'] ?? 0 ) ?: 10 + array_search( $slug, array_keys( $all ), true ),
				)
			);
			kses_init();
			if ( $slides ) {
				set_post_thumbnail( $post_id, $slides[0] );
			}
			av_legacy_apply_meta( $post_id, $def );
			// Ảnh trong nội dung (đã tải khi dựng khối) cũng đưa vào thư viện ảnh, tối đa 6 ảnh.
			foreach ( array_slice( av_legacy_content_images( $page['content'] ), 0, 6 ) as $url ) {
				$aid = av_legacy_sideload( $url, $post_id, $def['title'] );
				if ( $aid ) {
					$slides[] = $aid;
				}
			}
		} elseif ( $index > 0 && $page['content'] ) {
			// Trang cũ phụ: lấy thêm tối đa 4 ảnh trong nội dung làm ảnh sản phẩm.
			foreach ( array_slice( av_legacy_content_images( $page['content'] ), 0, 4 ) as $url ) {
				$aid = av_legacy_sideload( $url, $post_id, $def['title'] );
				if ( $aid ) {
					$slides[] = $aid;
					++$images;
				}
			}
		}

		$thumb = (int) get_post_thumbnail_id( $post_id );
		foreach ( $slides as $aid ) {
			if ( $aid !== $thumb && ! in_array( $aid, $gallery, true ) && count( $gallery ) < 15 ) {
				$gallery[] = $aid;
			}
		}
		update_post_meta( $post_id, '_av_gallery', $gallery );
		if ( ! $thumb && $gallery ) {
			set_post_thumbnail( $post_id, array_shift( $gallery ) );
			update_post_meta( $post_id, '_av_gallery', $gallery );
		}
		if ( 0 === $index && $fresh ) {
			update_post_meta( $post_id, '_av_legacy_time', get_post_field( 'post_modified_gmt', $post_id ) );
		}

		// Đường dẫn cũ → sản phẩm mới.
		if ( preg_match( '#-(\d{3,5})\.html$#', $path, $m ) ) {
			$map          = (array) get_option( 'av_legacy_map', array() );
			$map[ $m[1] ] = $post_id;
			update_option( 'av_legacy_map', $map, false );
		}

		wp_send_json_success(
			array(
				'message' => sprintf( '%s ← %s: %d ảnh%s', $def['title'], $path, $images, ( 0 === $index && ! $fresh ) ? ' (giữ nội dung đã sửa tay)' : '' ),
			)
		);
	}
);

add_action(
	'wp_ajax_av_legacy_finish',
	static function () {
		av_legacy_guard();
		$notes = array();

		// Ảnh nhóm: ảnh đại diện của sản phẩm nổi bật nhất trong nhóm (chỉ khi nhóm chưa có ảnh).
		foreach ( get_terms( array( 'taxonomy' => 'av_group', 'hide_empty' => false, 'parent' => 0 ) ) as $t ) {
			if ( av_group_thumb( $t ) ) {
				continue;
			}
			$top = get_posts( array( 'post_type' => 'av_product', 'numberposts' => 1, 'orderby' => array( 'menu_order' => 'ASC' ), 'tax_query' => array( array( 'taxonomy' => 'av_group', 'terms' => $t->term_id ) ), 'meta_key' => '_thumbnail_id' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( $top ) {
				update_term_meta( $t->term_id, 'av_thumb_id', (int) get_post_thumbnail_id( $top[0] ) );
			}
		}
		$notes[] = 'ảnh 5 nhóm sản phẩm';

		// Trang chủ: thay ô ảnh chờ bằng ảnh thật, sản phẩm nổi bật theo thứ tự, hiện giá thay đoạn trích.
		$home = (int) get_option( 'page_on_front' );
		if ( $home ) {
			$content = (string) get_post_field( 'post_content', $home );
			$before  = $content;
			foreach ( av_legacy_home_images() as $img ) {
				$p   = get_page_by_path( $img['product'], OBJECT, 'av_product' );
				$aid = $p ? (int) get_post_thumbnail_id( $p ) : 0;
				if ( ! $aid ) {
					continue;
				}
				$cls     = trim( 'av-ph ' . $img['class'] );
				$pattern = '#<!-- wp:paragraph \{"className":"' . preg_quote( $cls, '#' ) . '"\} -->\s*<p class="' . preg_quote( $cls, '#' ) . '">' . preg_quote( $img['placeholder'], '#' ) . '</p>\s*<!-- /wp:paragraph -->#u';
				$content = preg_replace( $pattern, av_image_block( $aid, $img['alt'], $img['class'] ), $content, 1 );
			}
			$content = str_replace( '"postType":"av_product","order":"desc","orderBy":"date"', '"postType":"av_product","order":"asc","orderBy":"menu_order"', $content );
			$content = preg_replace( '#("postType":"av_product".*?<!-- wp:post-title \{"level":3,"isLink":true\} /-->\s*)<!-- wp:post-excerpt /-->#s', '$1<!-- wp:auviet/price-badge /-->', $content, 1 );
			if ( $content !== $before ) {
				kses_remove_filters();
				wp_update_post( array( 'ID' => $home, 'post_content' => $content ) );
				kses_init();
				$notes[] = 'ảnh và sản phẩm nổi bật trên trang chủ';
			}
		}
		update_option( 'av_legacy_last_run', wp_date( 'd/m/Y H:i' ) . ' bởi ' . wp_get_current_user()->display_name, false );
		$count = wp_count_posts( 'av_product' );
		wp_send_json_success(
			array(
				'message' => sprintf( 'Hoàn tất: %d sản phẩm đang hiển thị, %d đường dẫn cũ có chuyển hướng; đã cập nhật %s.', (int) $count->publish, count( (array) get_option( 'av_legacy_map', array() ) ), implode( ', ', $notes ) ),
			)
		);
	}
);

/* ------------------------------------------------------------------ */
/* Hàm hỗ trợ                                                          */
/* ------------------------------------------------------------------ */

/**
 * URL tuyệt đối, mã hoá đúng từng đoạn (tên ảnh cũ có dấu cách, chữ có dấu).
 */
function av_legacy_url( string $src ): string {
	$src = trim( html_entity_decode( $src ) );
	if ( '' === $src || str_starts_with( $src, 'data:' ) ) {
		return '';
	}
	if ( str_starts_with( $src, '//' ) ) {
		$src = 'https:' . $src;
	} elseif ( ! preg_match( '#^https?://#i', $src ) ) {
		$src = av_legacy_base() . '/' . ltrim( $src, '/' );
	}
	$parts = wp_parse_url( $src );
	if ( empty( $parts['host'] ) ) {
		return '';
	}
	$host = strtolower( $parts['host'] );
	$path = implode( '/', array_map( static fn( $seg ) => rawurlencode( rawurldecode( $seg ) ), explode( '/', $parts['path'] ?? '/' ) ) );
	$base = ( 'inquangcaoauviet.com' === $host || 'www.inquangcaoauviet.com' === $host ) ? 'https://inquangcaoauviet.com' : ( ( $parts['scheme'] ?? 'https' ) . '://' . $host );
	return $base . $path;
}

/**
 * Tải và tách trang cũ: đoạn giới thiệu, ảnh trình chiếu, khối nội dung.
 *
 * @return array|WP_Error
 */
function av_legacy_fetch( string $path ) {
	$res = wp_remote_get(
		av_legacy_url( $path ),
		array(
			'timeout'    => 40,
			'user-agent' => 'AuViet-Migration/1.0 (+' . home_url( '/' ) . ')',
		)
	);
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	if ( 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
		return new WP_Error( 'http', 'HTTP ' . wp_remote_retrieve_response_code( $res ) );
	}
	$html = (string) wp_remote_retrieve_body( $res );
	$dom  = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING );
	libxml_clear_errors();
	$xp = new DOMXPath( $dom );

	$intro_node = $xp->query( "//div[contains(concat(' ', normalize-space(@class), ' '), ' infocongty_sp ')]" )->item( 0 );
	$intro      = $intro_node ? trim( preg_replace( '/\s+/u', ' ', $intro_node->textContent ) ) : '';
	$slides     = array();
	foreach ( $xp->query( "//div[contains(@class,'webwidget_slideshow_dot')]//img" ) as $img ) {
		$u = av_legacy_url( (string) $img->getAttribute( 'src' ) );
		if ( $u && ! in_array( $u, $slides, true ) ) {
			$slides[] = $u;
		}
	}
	return array(
		'intro'   => $intro,
		'slides'  => $slides,
		'content' => $xp->query( "//div[@id='noi_dung']" )->item( 0 ),
	);
}

/**
 * Tìm sản phẩm theo đường dẫn mới, chưa có thì tạo bản nháp.
 */
function av_legacy_product_id( string $slug, array $def ): int {
	$p = get_page_by_path( $slug, OBJECT, 'av_product' );
	if ( $p ) {
		delete_post_meta( $p->ID, '_av_sample' );
		if ( 'publish' !== $p->post_status ) {
			wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'publish' ) );
		}
		return $p->ID;
	}
	$id = wp_insert_post(
		array(
			'post_type'   => 'av_product',
			'post_status' => 'publish',
			'post_name'   => $slug,
			'post_title'  => $def['title'],
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_av_legacy', 1 );
	return (int) $id;
}

/**
 * Sản phẩm chưa bị sửa tay sau lần nhập trước (được phép ghi đè nội dung).
 */
function av_legacy_is_fresh( int $post_id ): bool {
	if ( get_post_meta( $post_id, '_av_sample', true ) ) {
		return true;
	}
	$stamp = (string) get_post_meta( $post_id, '_av_legacy_time', true );
	if ( '' === $stamp ) {
		return true;
	}
	return get_post_field( 'post_modified_gmt', $post_id ) <= $stamp;
}

function av_legacy_apply_meta( int $post_id, array $def ): void {
	// Nhóm.
	$term_ids = array();
	foreach ( $def['groups'] as $g ) {
		$t = get_term_by( 'slug', $g, 'av_group' );
		if ( $t ) {
			$term_ids[] = (int) $t->term_id;
		}
	}
	if ( $term_ids ) {
		wp_set_object_terms( $post_id, $term_ids, 'av_group' );
		update_post_meta( $post_id, '_av_primary_group', $term_ids[0] );
	}
	// Quy cách nháp.
	foreach ( array_keys( av_spec_fields() ) as $k ) {
		if ( ! empty( $def['spec'][ $k ] ) ) {
			update_post_meta( $post_id, '_av_spec_' . $k, $def['spec'][ $k ] );
		}
	}
	update_post_meta( $post_id, '_av_spec_draft', 1 );
	// Chất liệu.
	delete_post_meta( $post_id, '_av_material' );
	foreach ( $def['mats'] as $slug ) {
		$m = get_page_by_path( $slug, OBJECT, 'av_material' );
		if ( $m ) {
			add_post_meta( $post_id, '_av_material', $m->ID );
		}
	}
}

/**
 * Tải ảnh về thư viện Media (không tải lại ảnh đã có). Bỏ ảnh nhỏ hơn 150 px (biểu tượng).
 */
function av_legacy_sideload( string $url, int $post_id, string $alt ): int {
	if ( '' === $url ) {
		return 0;
	}
	$found = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_av_source_url', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $url, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( $found ) {
		return (int) $found[0];
	}
	$skip = (array) get_option( 'av_legacy_skip', array() );
	if ( isset( $skip[ md5( $url ) ] ) ) {
		return 0;
	}
	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}
	$size = @getimagesize( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( ! $size || $size[0] < 150 || $size[1] < 150 ) {
		wp_delete_file( $tmp );
		$skip[ md5( $url ) ] = 1;
		update_option( 'av_legacy_skip', $skip, false );
		return 0;
	}
	$name = sanitize_file_name( remove_accents( rawurldecode( wp_basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) ) ) );
	$id   = media_handle_sideload(
		array(
			'name'     => $name ?: 'anh-' . md5( $url ) . '.jpg',
			'tmp_name' => $tmp,
		),
		$post_id,
		$alt
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_av_source_url', $url );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

function av_image_block( int $id, string $alt, string $class = '' ): string {
	$attrs = array(
		'id'              => $id,
		'sizeSlug'        => 'large',
		'linkDestination' => 'none',
	);
	if ( $class ) {
		$attrs['className'] = $class;
	}
	return sprintf(
		'<!-- wp:image %1$s -->' . "\n" . '<figure class="wp-block-image size-large%2$s"><img src="%3$s" alt="%4$s" class="wp-image-%5$d"/></figure>' . "\n" . '<!-- /wp:image -->',
		wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
		$class ? ' ' . esc_attr( $class ) : '',
		esc_url( (string) wp_get_attachment_image_url( $id, 'large' ) ),
		esc_attr( $alt ),
		$id
	);
}

/**
 * URL ảnh trong khối nội dung cũ.
 *
 * @return string[]
 */
function av_legacy_content_images( DOMNode $root ): array {
	$out = array();
	if ( $root instanceof DOMElement ) {
		foreach ( $root->getElementsByTagName( 'img' ) as $img ) {
			$u = av_legacy_url( (string) $img->getAttribute( 'src' ) );
			if ( $u && ! in_array( $u, $out, true ) ) {
				$out[] = $u;
			}
		}
	}
	return $out;
}

/**
 * Chuyển nội dung HTML cũ thành khối: đoạn, tiêu đề, ảnh (tải về); danh sách và bảng giữ dạng Cổ điển.
 */
function av_legacy_blocks( DOMNode $root, int $post_id, string $title, int &$images ): string {
	$blocks = array();
	$n      = 0;
	av_legacy_walk( $root, $blocks, $post_id, $title, $images, $n );
	return implode( "\n\n", array_filter( $blocks ) );
}

function av_legacy_block_level( DOMElement $el ): bool {
	foreach ( $el->childNodes as $c ) {
		if ( $c instanceof DOMElement && in_array( strtolower( $c->tagName ), array( 'p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'table', 'section', 'article', 'blockquote', 'figure' ), true ) ) {
			return true;
		}
	}
	return false;
}

function av_legacy_walk( DOMNode $node, array &$blocks, int $post_id, string $title, int &$images, int &$n ): void {
	foreach ( iterator_to_array( $node->childNodes ) as $c ) {
		if ( $c instanceof DOMText ) {
			$t = trim( str_replace( "\xc2\xa0", ' ', $c->textContent ) );
			if ( '' !== $t ) {
				$blocks[] = av_para( esc_html( $t ) );
			}
			continue;
		}
		if ( ! $c instanceof DOMElement ) {
			continue;
		}
		$tag = strtolower( $c->tagName );
		if ( in_array( $tag, array( 'script', 'style', 'iframe', 'noscript', 'form', 'input', 'button', 'object', 'embed' ), true ) ) {
			continue;
		}
		if ( preg_match( '/^h[1-6]$/', $tag ) ) {
			$text = av_legacy_inline( $c );
			if ( '' !== trim( wp_strip_all_tags( $text ) ) ) {
				$level    = max( 2, min( 4, (int) $tag[1] ) );
				$blocks[] = sprintf( '<!-- wp:heading%1$s -->' . "\n" . '<h%2$d class="wp-block-heading">%3$s</h%2$d>' . "\n" . '<!-- /wp:heading -->', 2 === $level ? '' : ' {"level":' . $level . '}', $level, $text );
			}
			av_legacy_images_in( $c, $blocks, $post_id, $title, $images, $n );
			continue;
		}
		if ( 'ul' === $tag || 'ol' === $tag ) {
			$items = '';
			foreach ( $c->getElementsByTagName( 'li' ) as $li ) {
				$t = av_legacy_inline( $li );
				if ( '' !== trim( wp_strip_all_tags( $t ) ) ) {
					$items .= '<li>' . $t . '</li>';
				}
			}
			if ( $items ) {
				$blocks[] = "<{$tag}>{$items}</{$tag}>";
			}
			continue;
		}
		if ( 'table' === $tag ) {
			$rows = '';
			foreach ( $c->getElementsByTagName( 'tr' ) as $tr ) {
				$cells = '';
				foreach ( $tr->childNodes as $td ) {
					if ( $td instanceof DOMElement && in_array( strtolower( $td->tagName ), array( 'td', 'th' ), true ) ) {
						$cells .= '<' . strtolower( $td->tagName ) . '>' . av_legacy_inline( $td ) . '</' . strtolower( $td->tagName ) . '>';
					}
				}
				if ( '' !== trim( wp_strip_all_tags( $cells ) ) ) {
					$rows .= '<tr>' . $cells . '</tr>';
				}
			}
			if ( $rows ) {
				$blocks[] = '<table><tbody>' . $rows . '</tbody></table>';
			}
			av_legacy_images_in( $c, $blocks, $post_id, $title, $images, $n );
			continue;
		}
		if ( 'img' === $tag ) {
			av_legacy_image_el( $c, $blocks, $post_id, $title, $images, $n );
			continue;
		}
		if ( 'br' === $tag || 'hr' === $tag ) {
			continue;
		}
		if ( av_legacy_block_level( $c ) ) {
			av_legacy_walk( $c, $blocks, $post_id, $title, $images, $n );
			continue;
		}
		// Đoạn văn (p, div, span… không chứa khối con): chữ thành đoạn, ảnh bên trong thành khối ảnh riêng.
		$text = av_legacy_inline( $c );
		if ( '' !== trim( str_replace( "\xc2\xa0", ' ', wp_strip_all_tags( $text ) ) ) ) {
			array_push( $blocks, ...av_legacy_paras( $text ) );
		}
		av_legacy_images_in( $c, $blocks, $post_id, $title, $images, $n );
	}
}

function av_para( string $html ): string {
	return '<!-- wp:paragraph -->' . "\n" . '<p>' . trim( $html ) . '</p>' . "\n" . '<!-- /wp:paragraph -->';
}

/**
 * Đoạn văn cũ thường dùng <br><br> để ngắt đoạn và dòng chữ đậm làm tiêu đề: tách thành đoạn và tiêu đề cấp 3.
 *
 * @return string[]
 */
function av_legacy_paras( string $html ): array {
	$out = array();
	foreach ( preg_split( '#(?:\s*<br>\s*){2,}#u', $html ) as $seg ) {
		$seg = trim( preg_replace( '#^(\s*<br>\s*)+|(\s*<br>\s*)+$#u', '', $seg ) );
		if ( '' === trim( wp_strip_all_tags( $seg ) ) ) {
			continue;
		}
		// Dòng đầu chỉ là chữ đậm ngắn: tiêu đề, phần còn lại là đoạn.
		if ( preg_match( '#^<strong>([^<]{2,90})</strong>\s*(?:<br>\s*(.*))?$#us', $seg, $m ) ) {
			$out[] = '<!-- wp:heading {"level":3} -->' . "\n" . '<h3 class="wp-block-heading">' . trim( rtrim( trim( $m[1] ), ':' ) ) . '</h3>' . "\n" . '<!-- /wp:heading -->';
			if ( ! empty( $m[2] ) && '' !== trim( wp_strip_all_tags( $m[2] ) ) ) {
				$out[] = av_para( $m[2] );
			}
			continue;
		}
		$out[] = av_para( $seg );
	}
	return $out;
}

function av_legacy_images_in( DOMElement $el, array &$blocks, int $post_id, string $title, int &$images, int &$n ): void {
	foreach ( iterator_to_array( $el->getElementsByTagName( 'img' ) ) as $img ) {
		av_legacy_image_el( $img, $blocks, $post_id, $title, $images, $n );
	}
}

function av_legacy_image_el( DOMElement $img, array &$blocks, int $post_id, string $title, int &$images, int &$n ): void {
	if ( $n >= 25 ) {
		return;
	}
	$alt = trim( (string) $img->getAttribute( 'alt' ) );
	if ( '' === $alt || preg_match( '/\.(jpe?g|png|gif|webp)$/i', $alt ) ) {
		$alt = $title . ' – ảnh minh hoạ ' . ( $n + 1 );
	}
	$aid = av_legacy_sideload( av_legacy_url( (string) $img->getAttribute( 'src' ) ), $post_id, $alt );
	if ( $aid ) {
		++$n;
		++$images;
		$blocks[] = av_image_block( $aid, $alt );
	}
}

/**
 * Chữ trong một phần tử, chỉ giữ đậm, nghiêng, liên kết, xuống dòng. Liên kết nội bộ cũ đổi thành đường dẫn tương đối (sẽ được chuyển hướng).
 */
function av_legacy_inline( DOMNode $node ): string {
	$out = '';
	foreach ( $node->childNodes as $c ) {
		if ( $c instanceof DOMText ) {
			$out .= esc_html( str_replace( "\xc2\xa0", ' ', $c->textContent ) );
			continue;
		}
		if ( ! $c instanceof DOMElement ) {
			continue;
		}
		$tag   = strtolower( $c->tagName );
		$inner = av_legacy_inline( $c );
		switch ( $tag ) {
			case 'strong':
			case 'b':
				$out .= '' !== trim( $inner ) ? '<strong>' . $inner . '</strong>' : $inner;
				break;
			case 'em':
			case 'i':
				$out .= '' !== trim( $inner ) ? '<em>' . $inner . '</em>' : $inner;
				break;
			case 'br':
				$out .= '<br>';
				break;
			case 'a':
				$href = trim( (string) $c->getAttribute( 'href' ) );
				if ( preg_match( '#^(https?:)?//(www\.)?inquangcaoauviet\.com(/.*)?$#i', $href, $m ) ) {
					$href = $m[3] ?? '/';
				}
				$out .= ( '' !== trim( $inner ) && $href && ! str_starts_with( $href, 'javascript:' ) ) ? '<a href="' . esc_url( $href ) . '">' . $inner . '</a>' : $inner;
				break;
			case 'img':
			case 'script':
			case 'style':
				break;
			default:
				$out .= $inner;
		}
	}
	return preg_replace( '/\s+/u', ' ', $out );
}
