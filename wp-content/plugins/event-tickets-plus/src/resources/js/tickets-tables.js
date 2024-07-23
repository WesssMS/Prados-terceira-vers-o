// For compatibility purposes we add this
if ( 'undefined' === typeof tribe.tickets ) {
	tribe.tickets = {};
}

tribe.tickets.table = {};
( function( window, $, obj ) {
	/**
	 * Implemnts jQuery drag-n-drop for the ticket table.
	 * Stores order in the #tickets_order field.
	 *
	 * @param {jQuery} $element parent element to make sortable ( var $table above )
	 */
	obj.make_sortable = function( $element ) {
		// If we don't have at least 2 sortable items, don't sort.
		if ( 2 > $element.find( 'tr:not(.Tribe__Tickets__RSVP)' ).length ) {
			return;
		}

		const fixHelper = function( e, ui ) {
			ui.children().each( function() {
				$( this ).width( $( this ).outerWidth( true ) );
			} );

			return ui;
		};

		$element.sortable( {
			axis: 'y',
			containment: '#tribe_panel_base',
			opacity: 0.7,
			tolerance: 'intersect',
			cursor: 'move',
			items: 'tr:not(.Tribe__Tickets__RSVP)',
			forcePlaceholderSize: true,
			handle: '.tribe-handle',
			helper: fixHelper,
			update: function() {
				if ( tribe.tickets.editor ) {
					$( window ).off( 'beforeunload.tribe' );
				}

				const $tbody = $( this );
				const $items = $tbody.children( 'tr' );

				$items.each( function( k, item ) {
					const $item = $( item );
					$item.find( '.tribe-ticket-field-order' ).val( k );
				} );

				if ( tribe.tickets.editor ) {
					$( window ).on( 'beforeunload.tribe', tribe.tickets.editor.beforeUnload );
				}
			},
		} );

		$element.disableSelection();
		$element.find( '.table-header' ).disableSelection();
		$element.sortable( 'option', 'disabled', false );
	};

	obj.toggle_sortable = function() {
		const tables = document.querySelectorAll( '#tribetickets .tribe_ticket_list_table' );

		if ( tables.length === 0 ) {
			return;
		}

		Array.from( tables ).forEach( function( table ) {
			const $tableBody = $( table ).find( '.tribe-tickets-editor-table-tickets-body' );

			if ( window.matchMedia( '( min-width: 786px )' ).matches ) {
				if ( ! $tableBody.hasClass( 'ui-sortable' ) ) {
					obj.make_sortable( $tableBody );
				} else {
					$tableBody.sortable( 'enable' );
				}
			} else if ( $tableBody.hasClass( 'ui-sortable' ) ) {
				$tableBody.sortable( 'disable' );
			}
		} );
	};

	$( function() {
		// trigger once at start
		obj.toggle_sortable();

		// disable/init depending on screen size
		const maybeSortable = _.debounce( obj.toggle_sortable, 300 );
		$( window ).resize( maybeSortable );
	} );
} )( window, jQuery, tribe.tickets.table );
