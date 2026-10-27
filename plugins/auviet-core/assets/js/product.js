/* Trang sản phẩm: đổi ảnh, phóng to ảnh, tự lọc khi chọn. */
( function () {
	'use strict';

	document.querySelectorAll( '[data-av-autosubmit]' ).forEach( function ( sel ) {
		sel.addEventListener( 'change', function () {
			var form = sel.form;
			// Bỏ tham số rỗng để URL gọn, chia sẻ được.
			Array.prototype.forEach.call( form.elements, function ( el ) {
				if ( el.name && ! el.value ) {
					el.disabled = true;
				}
			} );
			form.submit();
		} );
	} );

	document.querySelectorAll( '[data-av-gallery]' ).forEach( function ( root ) {
		var main = root.querySelector( '[data-av-main]' );
		var thumbs = Array.prototype.slice.call( root.querySelectorAll( '.av-pg__thumb' ) );
		var single = root.querySelector( '[data-av-single]' );
		var dialog = root.querySelector( '.av-lightbox' );
		var big = dialog ? dialog.querySelector( 'img' ) : null;
		var items = thumbs.length ? thumbs.map( function ( t ) {
			return { full: t.dataset.avFull, alt: t.dataset.avAlt };
		} ) : ( single ? [ { full: single.dataset.avFull, alt: single.dataset.avAlt } ] : [] );
		var current = 0;

		function select( i ) {
			current = ( i + thumbs.length ) % thumbs.length;
			var t = thumbs[ current ];
			main.src = t.dataset.avSrc;
			main.srcset = t.dataset.avSrcset || '';
			main.alt = t.dataset.avAlt;
			thumbs.forEach( function ( x, n ) {
				x.classList.toggle( 'is-active', n === current );
				if ( n === current ) {
					x.setAttribute( 'aria-current', 'true' );
				} else {
					x.removeAttribute( 'aria-current' );
				}
			} );
		}

		function show( i ) {
			if ( ! items.length ) {
				return;
			}
			current = ( i + items.length ) % items.length;
			big.src = items[ current ].full;
			big.alt = items[ current ].alt;
		}

		thumbs.forEach( function ( t, i ) {
			t.addEventListener( 'click', function () {
				select( i );
			} );
		} );

		if ( ! dialog || ! big || typeof dialog.showModal !== 'function' ) {
			return;
		}
		dialog.classList.toggle( 'is-single', items.length < 2 );
		root.querySelector( '[data-av-zoom]' ).addEventListener( 'click', function () {
			show( current );
			dialog.showModal();
		} );
		dialog.querySelector( '.av-lightbox__close' ).addEventListener( 'click', function () {
			dialog.close();
		} );
		dialog.querySelector( '.av-lightbox__prev' ).addEventListener( 'click', function () {
			show( current - 1 );
		} );
		dialog.querySelector( '.av-lightbox__next' ).addEventListener( 'click', function () {
			show( current + 1 );
		} );
		dialog.addEventListener( 'click', function ( e ) {
			if ( e.target === dialog ) {
				dialog.close();
			}
		} );
		dialog.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowLeft' ) {
				show( current - 1 );
			} else if ( e.key === 'ArrowRight' ) {
				show( current + 1 );
			}
		} );
		dialog.addEventListener( 'close', function () {
			if ( thumbs.length ) {
				select( current );
			}
		} );
	} );
} )();
