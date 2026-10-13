/**
 * Floor Catalog filter buttons.
 * Plain JavaScript, no jQuery. Shows only the cards whose data-materials include the chosen slug.
 */
( function () {
	'use strict';

	function initCatalog( catalog ) {
		var buttons = catalog.querySelectorAll( '.floor-catalog__filter' );
		var cards = catalog.querySelectorAll( '.floor-catalog__card' );
		var status = catalog.querySelector( '.floor-catalog__status' );

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var filter = button.getAttribute( 'data-filter' );
				var shown = 0;

				// Update which button is active, for both looks and screen readers.
				buttons.forEach( function ( b ) {
					var active = b === button;
					b.classList.toggle( 'is-active', active );
					b.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
				} );

				cards.forEach( function ( card ) {
					var materials = ( card.getAttribute( 'data-materials' ) || '' ).split( ' ' );
					var match = filter === 'all' || materials.indexOf( filter ) !== -1;
					card.hidden = ! match;
					if ( match ) {
						shown++;
					}
				} );

				// Tell screen reader users the result changed.
				if ( status ) {
					status.textContent = shown + ( shown === 1 ? ' floor shown' : ' floors shown' );
				}
			} );
		} );
	}

	document.querySelectorAll( '.floor-catalog' ).forEach( initCatalog );
} )();
