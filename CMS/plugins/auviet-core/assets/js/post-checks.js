/* Bài viết: khung "Trước khi đăng" ở cột phải trình soạn khối (prototype "Sửa bài viết"). */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var useSelect = wp.data.useSelect;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var Panel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	if ( ! Panel ) {
		return;
	}

	function walk( blocks, fn ) {
		( blocks || [] ).forEach( function ( b ) {
			fn( b );
			walk( b.innerBlocks, fn );
		} );
	}

	function Item( props ) {
		return el(
			'li',
			{ className: 'av-pc__item ' + ( props.ok ? 'is-ok' : 'is-bad' ) },
			el( 'span', { className: 'av-pc__icon', 'aria-hidden': 'true' }, props.ok ? '✓' : '!' ),
			el(
				'span',
				{ className: 'av-pc__text' },
				el( 'strong', null, props.title ),
				props.sub ? el( 'span', null, props.sub ) : null,
				el( 'span', { className: 'screen-reader-text' }, props.ok ? ' (ổn)' : ' (cần sửa)' )
			)
		);
	}

	function Checks() {
		var d = useSelect( function ( select ) {
			var ed = select( 'core/editor' );
			return {
				id: ed.getCurrentPostId(),
				title: ed.getEditedPostAttribute( 'title' ) || '',
				excerpt: ed.getEditedPostAttribute( 'excerpt' ) || '',
				media: ed.getEditedPostAttribute( 'featured_media' ) || 0,
				blocks: select( 'core/block-editor' ).getBlocks()
			};
		}, [] );
		var tick = useState( 0 );

		// Lần đầu: mở sẵn khung này (sau đó theo lựa chọn đóng/mở của người dùng).
		useEffect( function () {
			var id = 'auviet-post-checks/av-post-checks';
			try {
				if ( ! window.localStorage.getItem( 'avPostChecksOpened' ) ) {
					window.localStorage.setItem( 'avPostChecksOpened', '1' );
					if ( ! wp.data.select( 'core/editor' ).isEditorPanelOpened( id ) ) {
						wp.data.dispatch( 'core/editor' ).toggleEditorPanelOpened( id );
					}
				}
			} catch ( e ) {}
		}, [] );
		var dup = useState( null );

		// Sản phẩm chọn trong hộp "Sản phẩm liên quan" (hộp cổ điển, đọc từ trang).
		useEffect( function () {
			function onChange( e ) {
				if ( e.target && 'av_post_products[]' === e.target.name ) {
					tick[ 1 ]( function ( n ) {
						return n + 1;
					} );
				}
			}
			document.addEventListener( 'change', onChange );
			return function () {
				document.removeEventListener( 'change', onChange );
			};
		}, [] );

		useEffect( function () {
			var t = d.title.trim();
			if ( t.length < 4 ) {
				dup[ 1 ]( null );
				return undefined;
			}
			var timer = setTimeout( function () {
				wp.apiFetch( { path: wp.url.addQueryArgs( '/auviet/v1/title-check', { title: t, id: d.id } ) } ).then( function ( list ) {
					dup[ 1 ]( Array.isArray( list ) && list.length ? list[ 0 ] : null );
				} ).catch( function () {} );
			}, 700 );
			return function () {
				clearTimeout( timer );
			};
		}, [ d.title, d.id ] );

		var noAlt = 0;
		var heads = 0;
		walk( d.blocks, function ( b ) {
			if ( 'core/image' === b.name && b.attributes.url && ! ( b.attributes.alt || '' ).trim() ) {
				noAlt++;
			}
			if ( 'core/heading' === b.name && ( b.attributes.level || 2 ) <= 3 ) {
				heads++;
			}
		} );
		var products = document.querySelectorAll( 'input[name="av_post_products[]"]:checked' ).length;
		var items = [
			{ ok: ! dup[ 0 ], title: dup[ 0 ] ? 'Tiêu đề trùng ' + dup[ 0 ].type.toLowerCase() + ' khác' : 'Tiêu đề không trùng', sub: dup[ 0 ] ? '"' + dup[ 0 ].title + '"' : '' },
			{ ok: d.media > 0, title: d.media ? 'Có ảnh đại diện' : 'Chưa có ảnh đại diện', sub: d.media ? 'Dùng cho danh sách và Facebook' : 'Khung "Ảnh đại diện" bên phải' },
			{ ok: 0 === noAlt, title: noAlt ? noAlt + ' ảnh trong bài thiếu mô tả' : 'Mọi ảnh có mô tả', sub: noAlt ? 'Chọn ảnh, điền "Văn bản thay thế"' : '' },
			{ ok: heads >= 2, title: heads >= 2 ? 'Có ' + heads + ' tiêu đề mục' : 'Ít tiêu đề mục', sub: heads >= 2 ? 'Mục lục tự sinh' : 'Cần từ 2 tiêu đề mục để có mục lục' },
			{ ok: products > 0, title: products ? 'Gắn ' + products + ' sản phẩm' : 'Chưa gắn sản phẩm', sub: products ? 'Hiện cuối bài, ô báo giá chọn sẵn' : 'Hộp "Sản phẩm liên quan" phía dưới' },
			{ ok: d.excerpt.trim().length > 0, title: d.excerpt.trim() ? 'Có đoạn tóm tắt' : 'Chưa có đoạn tóm tắt', sub: d.excerpt.trim() ? '' : 'Danh sách bài sẽ dùng đoạn đầu bài' }
		];
		var ok = items.filter( function ( i ) {
			return i.ok;
		} ).length;
		return el(
			Panel,
			{ name: 'av-post-checks', title: 'Trước khi đăng (' + ok + '/' + items.length + ')', className: 'av-pc', initialOpen: true },
			el( 'ul', { className: 'av-pc__list' }, items.map( function ( i, n ) {
				return el( Item, Object.assign( { key: n }, i ) );
			} ) )
		);
	}

	wp.plugins.registerPlugin( 'auviet-post-checks', { render: Checks, icon: null } );
} )( window.wp );
