/* Mẫu đã làm: bấm lọc hoặc chuyển trang không tải lại cả trang; URL vẫn đổi theo để chia sẻ được. */
( function () {
	'use strict';

	var filtersSel = '[data-av-filters]';
	var resultsSel = '.av-projects-results';
	if ( ! document.querySelector( resultsSel ) || ! window.fetch || ! window.history.pushState ) {
		return;
	}

	function swap( doc ) {
		[ filtersSel, resultsSel ].forEach( function ( sel ) {
			var now = document.querySelector( sel );
			var next = doc.querySelector( sel );
			if ( now && next ) {
				now.replaceWith( next );
			}
		} );
		document.title = doc.title;
	}

	function load( url, push ) {
		var results = document.querySelector( resultsSel );
		results.setAttribute( 'aria-busy', 'true' );
		results.classList.add( 'is-loading' );
		return fetch( url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } } )
			.then( function ( r ) {
				if ( ! r.ok ) {
					throw new Error( r.status );
				}
				return r.text();
			} )
			.then( function ( html ) {
				swap( new DOMParser().parseFromString( html, 'text/html' ) );
				if ( push ) {
					history.pushState( { av: 1 }, '', url );
				}
				var head = document.querySelector( filtersSel ) || document.querySelector( resultsSel );
				if ( head && head.getBoundingClientRect().top < 0 ) {
					head.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				}
			} )
			.catch( function () {
				window.location.href = url;
			} );
	}

	document.addEventListener( 'click', function ( e ) {
		var a = e.target.closest( filtersSel + ' a, ' + resultsSel + ' .wp-block-query-pagination a' );
		if ( ! a || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0 ) {
			return;
		}
		e.preventDefault();
		load( a.href, true ).then( function () {
			// Giữ chỗ bàn phím: đưa focus về chip đang chọn cùng hàng.
			if ( a.closest( filtersSel ) ) {
				var same = document.querySelector( filtersSel + ' a[href="' + a.getAttribute( 'href' ) + '"]' );
				if ( same ) {
					same.focus( { preventScroll: true } );
				}
			}
		} );
	} );

	window.addEventListener( 'popstate', function () {
		load( window.location.href, false );
	} );
} )();
