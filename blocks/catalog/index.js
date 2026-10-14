/**
 * Floor Catalog block: editor side.
 *
 * Plain JavaScript using the wp.* globals, so there's no build step. The block is
 * dynamic (rendered in PHP), so the editor shows a live server-rendered preview and
 * save() returns null: only the attributes are stored in the post.
 */
( function ( blocks, element, blockEditor, serverSideRender ) {
	'use strict';

	var el = element.createElement;

	blocks.registerBlockType( 'floor-catalog/catalog', {
		edit: function ( props ) {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( serverSideRender, {
					block: 'floor-catalog/catalog',
					attributes: props.attributes,
				} )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
