/* Màn hình soạn Sản phẩm, Mẫu đã làm: thanh trên cùng, chia mục, danh sách kiểm tra cập nhật khi sửa. */
( function ( $ ) {
	'use strict';

	var cfg = window.avShell || {};
	var $body = $( '#post-body' );
	if ( ! $body.length ) {
		return;
	}
	var isProduct = 'av_product' === cfg.type;

	/* ---------- Thẻ "Nội dung": tên, mô tả ngắn, nội dung chi tiết ---------- */
	// Không di chuyển trình soạn (khung TinyMCE nạp lại khi bị di chuyển): dùng chính #post-body-content làm thẻ.
	var $content = $( '#post-body-content' ).addClass( 'av-card av-card--content' );
	$content.prepend(
		$( '<div class="av-card__head">' )
			.append( $( '<h2>' ).text( cfg.contentLabel || 'Nội dung' ) )
			.append( $( '<p>' ).text( cfg.contentHelp || '' ) )
	);
	$( '#titlediv' ).before( '<label class="av-card__label" for="title">' + ( isProduct ? 'Tên sản phẩm' : 'Tên dự án' ) + '</label>' );
	var $excerpt = $( '#postexcerpt' );
	if ( $excerpt.length ) {
		$excerpt.addClass( 'av-flat' ).insertAfter( '#titlediv' );
		$excerpt.find( '.inside > p' ).last().text( isProduct ? 'Hiện ngay dưới tên sản phẩm và trên thẻ sản phẩm ở trang danh mục.' : 'Hiện dưới tên dự án và trên thẻ ở trang Mẫu đã làm.' );
	}
	$( '#postdivrich' ).before( $( '<span class="av-card__label">' ).text( cfg.editorLabel || 'Thông tin chi tiết' ) );

	function resolve( sel ) {
		return '#av-card-content' === sel ? '#post-body-content' : sel;
	}

	/* ---------- Bố cục 3 cột ---------- */
	var $nav = $( '<nav class="av-shell-nav" aria-label="Các phần">' );
	$body.addClass( 'av-grid' ).prepend( $nav );

	var claimed = [ '#post-body-content', '#slugdiv' ];
	var tabs = cfg.tabs || [];
	var sections = cfg.sections || [];

	if ( tabs.length ) {
		/* Sản phẩm: mỗi mục một tab */
		tabs.forEach( function ( t ) {
			t.$els = $( t.sel.map( resolve ).join( ',' ) );
			t.sel.forEach( function ( s ) {
				claimed.push( resolve( s ) );
			} );
		} );
		var $extra = $( '#postbox-container-2 .postbox' ).filter( function () {
			var el = this;
			return ! claimed.some( function ( s ) {
				return $( s ).is( el );
			} );
		} );
		if ( $extra.length ) {
			tabs.push( { id: 'khac', label: 'Khác', $els: $extra } );
		}
		tabs = tabs.filter( function ( t ) {
			return t.$els.length;
		} );
		tabs.forEach( function ( t ) {
			t.$els.attr( 'data-av-tab', t.id );
			t.$btn = $( '<button type="button" class="av-shell-nav__item">' )
				.attr( 'data-tab', t.id )
				.append( $( '<span>' ).text( t.label ) )
				.append( '<span class="av-shell-nav__badge" hidden></span>' )
				.on( 'click', function () {
					show( t.id, true );
				} );
			$nav.append( t.$btn );
		} );
		// Ẩn nút thu gọn, kéo thả của các hộp (đã chia mục, không cần).
		$( '#postbox-container-2 .postbox' ).addClass( 'av-flat-box' );
	} else if ( sections.length ) {
		/* Mẫu đã làm: một trang, đánh số bước */
		sections.forEach( function ( s ) {
			var $el = $( resolve( s.sel ) );
			if ( ! $el.length ) {
				return;
			}
			if ( '#post-body-content' !== resolve( s.sel ) ) {
				$el.find( '.hndle' ).first().text( s.label );
				$el.addClass( 'av-flat-box' );
			}
			$nav.append(
				$( '<button type="button" class="av-shell-nav__item">' )
					.attr( 'data-target', resolve( s.sel ) )
					.append( $( '<span>' ).text( s.label ) )
					.append( '<span class="av-shell-nav__badge" hidden></span>' )
					.on( 'click', function () {
						goTo( resolve( s.sel ) );
					} )
			);
		} );
	}

	function storeKey() {
		// Nhớ mục đang mở theo từng sản phẩm (tải lại sau khi lưu vẫn ở đúng mục).
		return 'avShellTab:' + cfg.type + ':' + cfg.postId;
	}

	function show( id, remember ) {
		tabs.forEach( function ( t ) {
			var on = t.id === id;
			t.$els.toggleClass( 'av-tab-hidden', ! on );
			t.$btn.toggleClass( 'is-active', on ).attr( 'aria-current', on ? 'true' : null );
		} );
		if ( remember ) {
			try {
				sessionStorage.setItem( storeKey(), id );
			} catch ( e ) {}
			if ( window.history.replaceState ) {
				history.replaceState( null, '', '#muc-' + id );
			}
		}
		// Rank Math, TinyMCE tính lại kích thước khi hiện ra.
		$( window ).trigger( 'resize' );
	}

	if ( tabs.length ) {
		var start = ( location.hash.match( /^#muc-([a-z]+)/ ) || [] )[ 1 ];
		if ( ! start ) {
			try {
				start = sessionStorage.getItem( storeKey() );
			} catch ( e ) {}
		}
		if ( ! tabs.some( function ( t ) {
			return t.id === start;
		} ) ) {
			start = tabs[ 0 ].id;
		}
		show( start, false );
	}

	function goTo( sel ) {
		var $t = $( sel );
		if ( ! $t.length ) {
			return;
		}
		var tab = $t.closest( '[data-av-tab]' ).attr( 'data-av-tab' );
		if ( tab ) {
			show( tab, true );
		}
		$t.removeClass( 'closed' );
		$( 'html, body' ).animate( { scrollTop: Math.max( 0, $t.offset().top - 120 ) }, 200 );
		var $f = $t.find( 'input:not([type=hidden]), textarea, select' ).filter( ':visible' ).first();
		setTimeout( function () {
			$f.trigger( 'focus' );
		}, 220 );
	}

	/* ---------- Thanh trên cùng: nút gọi nút thật của WordPress ---------- */
	$( '[data-av-proxy]' ).each( function () {
		var $b = $( this );
		var $target = $( $b.data( 'avProxy' ) );
		if ( ! $target.length ) {
			$b.remove();
			return;
		}
		$b.on( 'click', function () {
			if ( $b.prop( 'disabled' ) ) {
				return;
			}
			$target.trigger( 'click' );
		} );
	} );
	var $primary = $( '[data-av-primary]' );

	/* ---------- Tên: thanh trên cùng, thẻ xem trước, kiểm tra trùng ---------- */
	var $title = $( '#title' );
	var $tbName = $( '[data-av-title]' );
	var dupTimer;
	$title.on( 'input', function () {
		var v = $.trim( this.value );
		$tbName.text( v || $tbName.data( 'empty' ) );
		$( '[data-av-card-title]' ).text( v || 'Tên dự án' );
		clearTimeout( dupTimer );
		dupTimer = setTimeout( function () {
			checkDup( v );
		}, 700 );
		update();
	} );

	function checkDup( v ) {
		if ( v.length < 4 || ! window.wp || ! wp.apiFetch ) {
			return;
		}
		wp.apiFetch( { path: wp.url.addQueryArgs( '/auviet/v1/title-check', { title: v, id: cfg.postId } ) } ).then( function ( list ) {
			var d = Array.isArray( list ) && list.length ? list[ 0 ] : null;
			set( 'dup', ! d, d ? 'Tên trùng ' + d.type.toLowerCase() + ' khác' : 'Tên không trùng', d ? '"' + d.title + '"' : 'Không trùng sản phẩm, bài viết, trang khác' );
			refresh();
		} ).catch( function () {} );
	}

	/* ---------- Nhóm chính: ngôi sao cạnh nhóm ---------- */
	var $primarySel = $( '#av-primary-group' );
	if ( $primarySel.length ) {
		$( '#av_groupchecklist li' ).each( function () {
			var $li = $( this );
			var $cb = $li.children( 'label' ).find( 'input[type=checkbox]' );
			var id = $cb.val();
			var name = $.trim( $li.children( 'label' ).text() );
			$( '<button type="button" class="av-star">' )
				.attr( 'data-term', id )
				.attr( 'aria-label', 'Đặt ' + name + ' làm nhóm chính' )
				.html( '★' )
				.insertAfter( $li.children( 'label' ) )
				.on( 'click', function () {
					$primarySel.val( id );
					$cb.prop( 'checked', true );
					paintStars();
					update();
				} );
		} );
		var paintStars = function () {
			var cur = $primarySel.val();
			$( '#av_groupchecklist .av-star' ).each( function () {
				var on = $( this ).attr( 'data-term' ) === cur;
				$( this ).toggleClass( 'is-on', on ).attr( 'aria-pressed', on ? 'true' : 'false' );
			} );
		};
		paintStars();
		$( '#av_groupdiv .inside' ).prepend( '<p class="av-note">Đánh dấu các nhóm. Bấm ★ để chọn nhóm chính (dùng cho đường dẫn và form báo giá).</p>' );
	}

	/* ---------- Danh sách kiểm tra ---------- */
	var $checks = $( '[data-av-checks] .av-check' );

	function item( key ) {
		return $checks.filter( '[data-key="' + key + '"]' );
	}

	function set( key, ok, title, sub ) {
		var $c = item( key );
		if ( ! $c.length ) {
			return;
		}
		$c.toggleClass( 'is-ok', !! ok ).toggleClass( 'is-bad', ! ok );
		$c.find( 'strong' ).text( title );
		$c.find( '.av-check__text > span' ).text( sub || '' );
		$c.find( '.screen-reader-text' ).text( ok ? 'Ổn' : 'Cần sửa' );
	}

	function galleryItems() {
		return $( '[data-av-gallery] > li' );
	}

	function editorText() {
		var ed = window.tinymce && tinymce.get( 'content' );
		if ( ed && ! ed.isHidden() ) {
			return ed.getContent( { format: 'text' } );
		}
		return $( '#content' ).val() || '';
	}

	function norm( s ) {
		return ( s || '' ).toString().toLowerCase().normalize( 'NFC' );
	}

	function update() {
		var $g = galleryItems();
		var noAlt = $g.filter( function () {
			return ! $.trim( $( this ).find( '[data-av-alt]' ).val() );
		} );
		set( 'thumb', $g.length > 0, $g.length ? 'Có ảnh đại diện' : 'Chưa có ảnh', $g.length ? $g.length + ' ảnh' : ( isProduct ? 'Thêm ít nhất 1 ảnh thật' : 'Thêm ảnh thật của đơn hàng' ) );
		var missing = noAlt.map( function () {
			return 'ảnh ' + ( $g.index( this ) + 1 );
		} ).get();
		set( 'alts', ! missing.length, missing.length ? missing.length + ' ảnh thiếu mô tả' : 'Mọi ảnh có mô tả', missing.length ? 'Thiếu ở ' + missing.join( ', ' ) : '' );

		var ex = $.trim( $( '#excerpt' ).val() || '' );
		set( 'excerpt', ex.length > 0, ex ? 'Có mô tả ngắn' : 'Chưa có mô tả ngắn', ex ? ex.length + ' ký tự' : 'Hiện dưới tên sản phẩm và trên thẻ sản phẩm' );

		var $spec = $( 'input[name="av_spec_checked"]' );
		if ( $spec.length ) {
			set( 'spec', $spec.is( ':checked' ), $spec.is( ':checked' ) ? 'Quy cách đã kiểm tra' : 'Quy cách chưa kiểm tra', $spec.is( ':checked' ) ? 'Bấm Lưu để ghi nhận' : 'Tự điền từ web cũ, cần đối chiếu với xưởng' );
		}

		var groups = $( '#av_groupchecklist input:checked' ).length;
		set( 'groups', groups > 0, groups ? 'Đã chọn nhóm sản phẩm' : 'Chưa chọn nhóm sản phẩm', 'Khung "Nhóm sản phẩm" bên phải' );

		var prods = $( 'input[name="av_products[]"]:checked' ).length;
		set( 'products', prods > 0, prods ? 'Gắn ' + prods + ' sản phẩm' : 'Chưa gắn sản phẩm', prods ? '' : 'Bắt buộc, chọn ở bước 3' );

		var ind = $( 'input[name="tax_input[av_industry][]"]:checked' ).filter( function () {
			return '0' !== this.value;
		} ).length;
		set( 'industry', ind > 0, ind ? 'Đã chọn ngành' : 'Chưa chọn ngành', 'Để khách lọc theo ngành' );

		// Tên khách khi khách chưa đồng ý nêu tên.
		var $name = $( '#av-client-name' );
		if ( $name.length ) {
			var name = norm( $.trim( $name.val() ) );
			var keep = '1' !== $( 'input[name="av_client_ok"]:checked' ).val();
			var where = [];
			if ( keep && name.length >= 2 ) {
				if ( norm( $title.val() ).indexOf( name ) > -1 ) {
					where.push( 'tên dự án' );
				}
				if ( norm( $( '#excerpt' ).val() ).indexOf( name ) > -1 ) {
					where.push( 'mô tả ngắn' );
				}
				if ( norm( editorText() ).indexOf( name ) > -1 ) {
					where.push( 'câu chuyện dự án' );
				}
				$g.each( function ( i ) {
					if ( norm( $( this ).find( '[data-av-alt]' ).val() ).indexOf( name ) > -1 ) {
						where.push( 'mô tả ảnh ' + ( i + 1 ) );
					}
				} );
			}
			set( 'client', ! where.length, where.length ? 'Tên khách đang lộ ra web' : 'Tên khách không lộ ra web', where.length ? 'Có trong ' + where.join( ', ' ) : ( keep ? 'Khách chưa đồng ý nêu tên: chỉ lưu nội bộ' : 'Khách đã đồng ý nêu tên' ) );
			$( '[data-av-leak]' ).prop( 'hidden', ! where.length ).text( where.length ? 'Tên khách đang có trong ' + where.join( ', ' ) + '. Bỏ tên khách ở đó, hoặc chọn "Khách đã đồng ý" nếu khách cho phép.' : '' );
			$( '.av-seg label' ).each( function () {
				$( this ).toggleClass( 'is-on', $( this ).find( 'input' ).is( ':checked' ) );
			} );
		}

		// Thẻ xem trước (mẫu đã làm): ảnh đầu tiên.
		var $img = $( '[data-av-card-img]' );
		if ( $img.length ) {
			var src = $g.first().find( 'img' ).attr( 'src' );
			$img.html( src ? $( '<img alt="">' ).attr( 'src', src ) : '<span>[Chưa có ảnh]</span>' );
		}
		refresh();
	}

	function refresh() {
		var ok = $checks.filter( '.is-ok' ).length;
		$( '[data-av-checks-sum]' ).text( ok + ' / ' + $checks.length + ' mục ổn.' + ( ok < $checks.length ? ' Bấm mục chưa ổn để sửa.' : '' ) );
		var blocked = $checks.filter( '.is-bad[data-block="1"]' );
		// Huy hiệu trên từng mục.
		$nav.children().each( function () {
			var $b = $( this );
			var n = $checks.filter( '.is-bad' ).filter( function () {
				var tab = $( this ).attr( 'data-tab' );
				return tab && ( tab === $b.attr( 'data-tab' ) || '#' + tab === $b.attr( 'data-target' ) );
			} ).length;
			$b.find( '.av-shell-nav__badge' ).text( n ).prop( 'hidden', ! n );
		} );
		var stop = blocked.length > 0;
		$( '#publish' ).prop( 'disabled', stop );
		$primary.prop( 'disabled', stop ).attr( 'title', stop ? 'Sửa mục "' + blocked.first().find( 'strong' ).text() + '" trước khi đăng' : null );
		$( '#av_checks .hndle' ).first().text( stop ? 'Chưa đăng được' : ( isProduct ? 'Sẵn sàng hiển thị' : 'Trước khi đăng' ) );
	}

	$checks.on( 'click', function () {
		var tab = $( this ).attr( 'data-tab' );
		var key = $( this ).attr( 'data-key' );
		var targets = {
			groups: '#av_groupdiv',
			excerpt: '#postexcerpt',
			dup: '#titlediv',
			client: '#av_project_client',
			alts: '[data-av-gallery]',
			thumb: '[data-av-gallery]'
		};
		if ( targets[ key ] && $( targets[ key ] ).length ) {
			goTo( targets[ key ] );
		} else if ( tab && $( '#' + tab ).length ) {
			goTo( '#' + tab );
		} else if ( tab ) {
			show( tab, true );
		}
	} );

	$( document ).on( 'av:gallery', update );
	$( document ).on( 'change input', '#excerpt, #av-client-name, input[name="av_client_ok"], input[name="av_spec_checked"], #av_groupchecklist input, input[name="av_products[]"], input[name="tax_input[av_industry][]"]', update );
	$( document ).on( 'tinymce-editor-init', function ( e, ed ) {
		if ( ed && 'content' === ed.id ) {
			var edTimer;
			ed.on( 'keyup change', function () {
				clearTimeout( edTimer );
				edTimer = setTimeout( update, 400 );
			} );
		}
	} );
	update();
} )( jQuery );
