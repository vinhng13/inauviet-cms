/* QT-05: cảnh báo tiêu đề trùng với sản phẩm, bài viết hoặc trang khác, hiện ngay khi gõ và trước khi bấm Đăng. */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var useSelect = wp.data.useSelect;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var PrePublish = ( wp.editor && wp.editor.PluginPrePublishPanel ) || ( wp.editPost && wp.editPost.PluginPrePublishPanel );
	var NOTICE = 'av-duplicate-title';

	function check( title, id ) {
		return wp.apiFetch( { path: wp.url.addQueryArgs( '/auviet/v1/title-check', { title: title, id: id } ) } );
	}

	function message( list ) {
		return 'Tiêu đề trùng với: ' + list.map( function ( d ) {
			return d.type + ' "' + d.title + '"';
		} ).join( ', ' ) + '. Nên đặt tiêu đề khác để khách và Google không nhầm hai trang.';
	}

	function TitleCheck() {
		var data = useSelect( function ( select ) {
			var ed = select( 'core/editor' );
			return { title: ed.getEditedPostAttribute( 'title' ) || '', id: ed.getCurrentPostId() };
		}, [] );
		var state = useState( [] );
		var dups = state[ 0 ];
		var setDups = state[ 1 ];

		useEffect( function () {
			var title = data.title.trim();
			if ( title.length < 4 ) {
				setDups( [] );
				return undefined;
			}
			var t = setTimeout( function () {
				check( title, data.id ).then( function ( list ) {
					setDups( Array.isArray( list ) ? list : [] );
				} ).catch( function () {} );
			}, 700 );
			return function () {
				clearTimeout( t );
			};
		}, [ data.title, data.id ] );

		useEffect( function () {
			var notices = wp.data.dispatch( 'core/notices' );
			if ( dups.length ) {
				notices.createWarningNotice( message( dups ), { id: NOTICE, isDismissible: true } );
			} else {
				notices.removeNotice( NOTICE );
			}
		}, [ dups ] );

		if ( ! dups.length || ! PrePublish ) {
			return null;
		}
		return el(
			PrePublish,
			{ title: 'Tiêu đề bị trùng', initialOpen: true, className: 'av-dup-panel' },
			el( 'p', null, message( dups ) ),
			el(
				'ul',
				null,
				dups.map( function ( d, i ) {
					return el( 'li', { key: i }, d.edit ? el( 'a', { href: d.edit, target: '_blank', rel: 'noopener' }, d.type + ': ' + d.title ) : d.type + ': ' + d.title );
				} )
			)
		);
	}

	wp.plugins.registerPlugin( 'auviet-title-check', { render: TitleCheck } );
} )( window.wp );
