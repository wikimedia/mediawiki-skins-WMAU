$( () => {
	let originalWidth, originalHeight,
		// eslint-disable-next-line no-jquery/no-global-selector
		$searchLi = $( 'header nav .skin-wmau-search' ),
		// eslint-disable-next-line no-jquery/no-global-selector
		$searchForm = $( 'header nav #searchform' ),
		// eslint-disable-next-line no-jquery/no-global-selector
		$searchInput = $( 'header nav #searchInput' ),
		searchActive = true;

	// Don't enable toggle behaviour on mobile (where the icon is hidden).
	if ( !$searchLi.is( ':visible' ) ) { // eslint-disable-line no-jquery/no-sizzle
		return;
	}

	/**
	 * Show or hide the search form.
	 */
	function toggleSearchForm() {
		if ( searchActive ) {
			// Deactivate.
			$searchForm.removeClass( 'skin-wmau-search-active' )
				.css( {
					width: 0,
					height: 0
				} );
			$searchLi.removeClass( 'skin-wmau-search-active' );
			searchActive = false;
		} else {
			// Activate.
			$searchForm
				.addClass( 'skin-wmau-search-active' )
				.css( {
					width: originalWidth,
					height: originalHeight
				} );
			$searchLi.addClass( 'skin-wmau-search-active' );
			searchActive = true;
		}
	}

	// Show the form temporarily in order to find its size.
	$searchForm.show();

	// Calculate sizes.
	originalWidth = $searchForm.width();
	originalHeight = $searchForm.height();

	// Reposition the form to just below the search icon.
	$searchForm.css( {
		top: $searchLi.offset().top + $searchLi.height(),
		left: $searchLi.offset().left + $searchLi.width() - originalWidth
	} );

	// Show form when clicking the search icon.
	$searchLi.find( 'a' ).on( 'click', ( event ) => {
		event.preventDefault();
		toggleSearchForm();
		if ( searchActive ) {
			$searchInput.trigger( 'focus' );
		}
	} );

	// Handle accesskey focussing.
	$searchInput
		.on( 'focus', () => {
			if ( !searchActive ) {
				toggleSearchForm();
			}
		} )
		.on( 'keyup', ( event ) => {
			if ( event.which === 27 ) {
				event.preventDefault();
				$searchInput.trigger( 'blur' );
				toggleSearchForm();
			}
		} );

	// Initial toggle to hide the form.
	toggleSearchForm();

} );
