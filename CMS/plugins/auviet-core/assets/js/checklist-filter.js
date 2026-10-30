/* Danh sách chọn (sản phẩm, bài viết): ô tìm phía trên, gõ không dấu vẫn ra ("tui giay" ra "Túi giấy"). */
( function () {
	'use strict';

	function plain( s ) {
		return ( s || '' ).toString().toLowerCase().normalize( 'NFD' ).replace( /[̀-ͯ]/g, '' ).replace( /đ/g, 'd' );
	}

	function enhance( list ) {
		if ( list.dataset.avFiltered ) {
			return;
		}
		list.dataset.avFiltered = '1';
		var input = document.createElement( 'input' );
		input.type = 'search';
		input.className = 'av-checklist__search';
		input.placeholder = list.dataset.avFilter || 'Tìm…';
		input.setAttribute( 'aria-label', input.placeholder );
		var count = document.createElement( 'p' );
		count.className = 'av-checklist__count';
		list.parentNode.insertBefore( input, list );
		list.parentNode.insertBefore( count, list );

		function paintCount() {
			var n = list.querySelectorAll( 'input[type=checkbox]:checked' ).length;
			count.textContent = n ? 'Đã chọn ' + n : 'Chưa chọn';
		}

		input.addEventListener( 'input', function () {
			var q = plain( input.value.trim() );
			var labels = list.querySelectorAll( 'label' );
			labels.forEach( function ( l ) {
				l.hidden = !! q && plain( l.textContent ).indexOf( q ) === -1;
			} );
			// Ẩn tên nhóm không còn mục nào hiện.
			list.querySelectorAll( '.av-checklist__group' ).forEach( function ( g ) {
				var el = g.nextElementSibling;
				var any = false;
				while ( el && ! el.classList.contains( 'av-checklist__group' ) ) {
					if ( 'LABEL' === el.tagName && ! el.hidden ) {
						any = true;
					}
					el = el.nextElementSibling;
				}
				g.hidden = ! any;
			} );
		} );
		// Enter trong ô tìm không gửi form.
		input.addEventListener( 'keydown', function ( e ) {
			if ( 'Enter' === e.key ) {
				e.preventDefault();
			}
		} );
		list.addEventListener( 'change', paintCount );
		paintCount();
	}

	function run() {
		document.querySelectorAll( '.av-checklist' ).forEach( enhance );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', run );
	} else {
		run();
	}
	// Trình soạn khối nạp hộp nhập liệu sau: thử lại vài lần.
	var tries = 0;
	var t = setInterval( function () {
		run();
		if ( ++tries > 10 ) {
			clearInterval( t );
		}
	}, 1000 );
} )();
