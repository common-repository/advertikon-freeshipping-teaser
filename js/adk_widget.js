+function( $ ) {

	function hideWidget() {
		$( $( this ).attr( "data-for" ) ).fadeOut();
	}

	function showWidget() {
		$( this ).fadeIn();
	}

	$( document ).ready( function() {
		$( ".adk-widget-close" ).on( "click", hideWidget );
		$( ".adk-widget-wrapper" ).each( showWidget );
	} );
}( jQuery )