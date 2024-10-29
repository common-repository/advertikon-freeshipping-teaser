<?php
$freeShippingSettings = get_option( 'woocommerce_free_shipping_settings' , array() );
$curreencyFormat = strip_tags( wc_price( 100 ) );
$weightUnits = get_option( 'woocommerce_weight_unit' );

$freeShippingAmount = isset( $freeShippingSettings[ 'min_amount' ] ) ? $freeShippingSettings[ 'min_amount' ] : 0;
$cartTotal = isset( WC()->cart->total ) ? WC()->cart->total : 0;
$cartSubtotal = isset( WC()->cart->subtotal ) ? WC()->cart->subtotal : 0;
$cartSubtotalExTax = isset( WC()->cart->subtotal_ex_tax ) ? WC()->cart->subtotal_ex_tax : 0;
$cartWeight = isset( WC()->cart ) ? WC()->cart->get_cart_contents_weight() : 0;

ob_start();
?>
{
	"freeShipping": {
		"description"	: <?php echo '"' . __( 'freeShipping() - difference between cart total amount and free shipping minimum order amount' , 'advertikon' ) . '"'; ?>,
		"args"			: "0",
		"body"			: "return '<?php echo $curreencyFormat; ?>'.replace( /(&.+;)*([0-9,.]+)(&.+;)*/ , '$1' + <?php echo ( $freeShippingAmount - $cartSubtotalExTax ); ?> + '$3' )"
	},

	"totalDiff": {
		"description"	: <?php echo '"' . __( 'totalDiff(sum) - difference between cart total amount and specific \"sum\"' , 'advertikon' ) . '"'; ?>,
		"args"			: "1",
		"body"			: "A1 = parseFloat( A1 ); A1 = isNaN( A1 ) ? 0 : A1;return '<?php echo $curreencyFormat; ?>'.replace( /(&.+;)*([0-9,.]+)(&.+;)*/ , '$1' + ( A1 - <?php echo $cartTotal; ?> ) + '$3' );"
	}, 

	"subTotalDiff": {
		"description"	: <?php echo '"' . __( 'subTotalDiff(sum) - difference between cart sub total amount and specific \"sum\"' , 'advertikon' ) . '"'; ?>,
		"args"			: "1",
		"body"			: "A1 = parseFloat( A1 ); A1 = isNaN( A1 ) ? 0 : A1;return '<?php echo $curreencyFormat; ?>'.replace( /(&.+;)*([0-9,.]+)(&.+;)*/ , '$1' + ( A1 - <?php echo $cartSubtotal; ?>) + '$3' );"
	}, 

	"subTotalExTaxDiff": {
		"description"	: <?php echo '"' . __( 'subTotalExTaxDiff(sum) - difference between cart sub total (ex. tax) amount and specific \"sum\"' , 'advertikon' ) . '"'; ?>,
		"args"			: "1",
		"body"			: "A1 = parseFloat( A1 ); A1 = isNaN( A1 ) ? 0 : A1;return '<?php echo $curreencyFormat; ?>'.replace( /(&.+;)*([0-9,.]+)(&.+;)*/ , '$1' + ( A1 - <?php echo $cartSubtotalExTax; ?>) + '$3' );"
	}, 

	"url": {
		"description"	: <?php echo '"' . __( 'url( Title , URL , new_window ) - URL to be opened in current/new window, example: url( Link description , http://mysite/product ) - open in current window, url( Link description ,http://mysite/product , new ) - open in new window, ' , 'advertikon' ) . '"'; ?>,
		"args"			: "3",
		"body"			: "if( ! A1 || ! A2 )return false;return A3 ? '<a href=\"' + A2 + '\" target=\"_blank\" >' + A1 + '</a>' : '<a href=\"' + A2 + '\" target=\"_self\" >' + A1 + '</a>'"
	},

	"cartWeight": {
		"description"	: <?php echo '"' .  __( 'cartWeight(weight) - Difference between specific weight and cart weight' , 'advertikon' ) . '"'; ?>,
		"args"			: "1",
		"body"			: "A1 = parseFloat( A1 ); A1 = isNaN( A1 ) ? 0 : A1;return ( A1 - <?php echo $cartWeight; ?> ) + ' <?php echo $weightUnits; ?>'"
	},

	"curDate": {
		"description"	: <?php echo '"' .  __( 'curDate() - Shows current date' , 'advertikon' ) . '"'; ?>,
		"args"			: "1",
		"body"			: "var d = new Date();return d.toLocaleDateString();"
	},

	"curTime": {
		"description"	: <?php echo '"' .  __( 'curTime() - Shows current time' , 'advertikon' ) . '"'; ?>,
		"args"			: "1",
		"body"			: "var d = new Date();return d.toLocaleTimeString();"
	},

	"curDateTime": {
		"description"	: <?php echo '"' .  __( 'curDateTime() - Shows current date and time' , 'advertikon' ) . '"'; ?>,
		"args"			: "1",
		"body"			: "var d = new Date();return d.toLocaleString();"
	},

	"timeLeft": {
		"description"	: <?php echo '"' .  __( 'timeLeft(hh:mm) - Shows the time remaining until a specified time' , 'advertikon' ) . '"'; ?>,
		"args"			: "1",
		"body"			: "
							var parts = A1 ? A1.split( ':' ) : [],
								th = parseInt( parts[ 0 ] , 10 ) || 0,
								tm = parseInt( parts[ 1 ] , 10 ) || 0;
							th = th > 24 ? 24 : th;
							tm = tm >= 60 ? 59 : tm;

							var d = new Date(),
								curH = d.getHours(),
								curM = d.getMinutes(),
								curMin = curH * 60 + curM,
								tmin = th * 60 + tm,
								diff = 0,
								h = 0,
								m = 0;

							if( curMin > tmin ) {
								diff = 1440 - ( curMin - tmin ); 
							}
							else {
								diff = tmin - curMin;
							}
							
							if( diff < 60 ) {
								h = 0;
								m = diff;
							}
							else {
								h = Math.round( diff / 60 );
								m = diff % 60;

							}

							return ( h < 10 ? '0' : '' ) + h + ':' + ( m < 10 ? '0' : '' ) + m;"
	}
}
<?php
return preg_replace( '/\s+/' , ' ' , ob_get_clean() );
