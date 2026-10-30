/* Quản trị sản phẩm: thư viện ảnh, bảng giá, ảnh nhóm. Không cần bước build. */
( function ( $ ) {
	'use strict';

	/* Thư viện ảnh sản phẩm */
	var $gallery = $( '[data-av-gallery]' );
	var $galleryInput = $( '[data-av-gallery-input]' );

	var unified = $gallery.data( 'unified' ) === 1 || $gallery.data( 'unified' ) === '1';

	function syncGallery() {
		var ids = $gallery.children( 'li' ).map( function () {
			return $( this ).data( 'id' );
		} ).get();
		$galleryInput.val( ids.join( ',' ) );
		// Màn hình soạn mới: cập nhật danh sách kiểm tra, ảnh xem trước.
		$( document ).trigger( 'av:gallery' );
	}

	if ( $gallery.length ) {
		$gallery.sortable( unified ? { update: syncGallery, cancel: 'input,button,label' } : { update: syncGallery } );
		$gallery.on( 'input', '[data-av-alt]', function () {
			$( this ).closest( 'li' ).toggleClass( 'is-noalt', ! $.trim( this.value ) );
			$( document ).trigger( 'av:gallery' );
		} );
		$gallery.on( 'click', '.av-gallery__remove', function () {
			$( this ).closest( 'li' ).remove();
			syncGallery();
		} );
		var frame;
		$( '[data-av-gallery-add]' ).on( 'click', function () {
			if ( ! frame ) {
				frame = wp.media( { title: 'Chọn ảnh sản phẩm', button: { text: 'Thêm vào thư viện ảnh' }, library: { type: 'image' }, multiple: 'add' } );
				frame.on( 'select', function () {
					frame.state().get( 'selection' ).each( function ( att ) {
						var a = att.toJSON();
						if ( $gallery.children( '[data-id="' + a.id + '"]' ).length ) {
							return;
						}
						var thumb = ( a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url );
						if ( unified ) {
							var big = ( a.sizes && a.sizes.medium ? a.sizes.medium.url : thumb );
							var $u = $( '<li>' ).attr( 'data-id', a.id ).toggleClass( 'is-noalt', ! a.alt );
							var $box = $( '<div class="av-ugallery__img">' ).append( $( '<img alt="">' ).attr( 'src', big ) ).append( '<button type="button" class="av-gallery__remove" aria-label="Bỏ ảnh">×</button>' );
							var $in = $( '<input type="text" data-av-alt placeholder="VD: Túi kraft in logo 1 màu">' ).attr( 'name', 'av_alt[' + a.id + ']' ).val( a.alt || '' );
							$u.append( $box ).append( $( '<label>Mô tả ảnh</label>' ).append( $in ) );
							$gallery.append( $u );
							return;
						}
						var $li = $( '<li>' ).attr( 'data-id', a.id ).toggleClass( 'is-noalt', ! a.alt );
						$li.append( $( '<a target="_blank" title="Sửa ảnh, mô tả">' ).attr( 'href', a.editLink ).append( $( '<img alt="">' ).attr( 'src', thumb ) ) );
						$li.append( '<button type="button" class="av-gallery__remove" aria-label="Bỏ ảnh">×</button>' );
						$gallery.append( $li );
					} );
					syncGallery();
				} );
			}
			frame.open();
		} );
	}

	/* Bảng giá: thêm, xoá dòng và cột */
	var $prices = $( '[data-av-prices]' );

	function renumberRows() {
		$prices.find( 'tbody tr' ).each( function ( i ) {
			$( this ).find( 'input' ).each( function () {
				this.name = this.name.replace( /av_prices\[rows\]\[\d+\]/, 'av_prices[rows][' + i + ']' );
			} );
		} );
	}

	if ( $prices.length ) {
		$( '[data-av-add-row]' ).on( 'click', function () {
			var cols = $prices.find( 'thead th' ).length - 2;
			var i = $prices.find( 'tbody tr' ).length;
			var $tr = $( '<tr>' );
			$tr.append( '<td><input type="text" name="av_prices[rows][' + i + '][label]" placeholder="VD: A5, Couche 150, in 2 mặt"></td>' );
			for ( var c = 0; c < cols; c++ ) {
				$tr.append( '<td><input type="number" min="0" step="1" name="av_prices[rows][' + i + '][prices][]"></td>' );
			}
			$tr.append( '<td><button type="button" class="av-x" data-av-del-row aria-label="Xoá dòng">×</button></td>' );
			$prices.find( 'tbody' ).append( $tr );
			$tr.find( 'input' ).first().trigger( 'focus' );
		} );

		$prices.on( 'click', '[data-av-add-col]', function () {
			var $th = $( '<th><input type="number" min="1" name="av_prices[tiers][]" aria-label="Số lượng"><button type="button" class="av-x" data-av-del-col aria-label="Xoá cột">×</button></th>' );
			$( this ).closest( 'th' ).before( $th );
			$prices.find( 'tbody tr' ).each( function ( i ) {
				$( this ).children( 'td' ).last().before( '<td><input type="number" min="0" step="1" name="av_prices[rows][' + i + '][prices][]"></td>' );
			} );
			$th.find( 'input' ).trigger( 'focus' );
		} );

		$prices.on( 'click', '[data-av-del-col]', function () {
			var idx = $( this ).closest( 'th' ).index();
			$( this ).closest( 'th' ).remove();
			$prices.find( 'tbody tr' ).each( function () {
				$( this ).children().eq( idx ).remove();
			} );
		} );

		$prices.on( 'click', '[data-av-del-row]', function () {
			$( this ).closest( 'tr' ).remove();
			renumberRows();
		} );
	}

	/* Ảnh nhóm sản phẩm */
	var termFrame;
	$( document ).on( 'click', '[data-av-term-thumb-pick]', function () {
		if ( ! termFrame ) {
			termFrame = wp.media( { title: 'Chọn ảnh nhóm', button: { text: 'Dùng ảnh này' }, library: { type: 'image' }, multiple: false } );
			termFrame.on( 'select', function () {
				var a = termFrame.state().get( 'selection' ).first().toJSON();
				$( '[data-av-term-thumb-input]' ).val( a.id );
				$( '[data-av-term-thumb]' ).html( $( '<img alt="">' ).attr( 'src', a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url ) );
			} );
		}
		termFrame.open();
	} );
	$( document ).on( 'click', '[data-av-term-thumb-clear]', function () {
		$( '[data-av-term-thumb-input]' ).val( '' );
		$( '[data-av-term-thumb]' ).empty();
	} );
} )( jQuery );
