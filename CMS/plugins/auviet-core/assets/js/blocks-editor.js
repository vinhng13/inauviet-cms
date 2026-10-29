/* Đăng ký khối động Âu Việt trong trình soạn thảo (không cần bước build). */
( function ( blocks, element, serverSideRender, blockEditor ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	( window.avBlocks || [] ).forEach( function ( def ) {
		blocks.registerBlockType( def.name, {
			apiVersion: 3,
			title: def.title,
			description: def.description,
			icon: def.icon,
			category: 'auviet',
			attributes: def.attributes || {},
			supports: { html: false },
			edit: function ( props ) {
				return el(
					'div',
					useBlockProps(),
					el( serverSideRender, { block: def.name, attributes: props.attributes } )
				);
			},
			save: function () {
				return null;
			}
		} );
	} );
} )( window.wp.blocks, window.wp.element, window.wp.serverSideRender, window.wp.blockEditor );
