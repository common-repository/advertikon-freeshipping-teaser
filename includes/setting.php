<?php
/**
 * @package notifications
 * @author Advertikon
 */

class Advertikon_Notification_Includes_Setting extends WC_Settings_Page {
	/** @var Advertikon_Notifications */
	protected $notificaiton;

	public function __construct( Advertikon_Notifications $notification ) {
		$this->id    = Advertikon_Notifications::ID;
		$this->label = __( 'Notifications', Advertikon_Notifications::LNS );

		$this->notification = $notification;

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 100 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
	}

	/**
	 * Translator wrapper
	 */
	protected function __( $text ) {
		return __( $text );
	} 

	/**
	 * Get settings array.
	 * @action_callback
	 * @return array
	 */
	public function get_settings() {
		global $hide_save_button;
		$hide_save_button = true;

		$ret = array();

		$ret = array_merge( $ret, $this->notification->get_widget()->get_controls() );
		$ret = array_merge( $ret, $this->get_section_controls() );
		$ret = array_merge( $ret, $this->notification->get_filter()->get_controls() );
		$ret[] = $this->get_save_button();
		$ret = array_merge( $ret, $this->get_button_controls() );

		return $ret;

		$teaser_max_height = 100;
		//update_option( $this->id, [] );
		$notifications = get_option( $this->id, array() );
		$cur_reg_exp_str = preg_replace( '/(&.+;)*([\d.,]+)(&.+;)*/', '$1%$3', strip_tags( wc_price( 0 ) ) );

		$categories = array();
		foreach( get_terms( 'product_cat', array( 'hide_empty' => false ) ) as $cat ) {
			$categories[ $cat->slug ] = $cat->name;
		}

		$tags = array();
		foreach( get_terms( 'product_tag', array( 'hide_empty' => false ) ) as $tag ) {
			$tags[ $tag->slug ] = $tag->name;
		}

		$shipping_classes = array();
		foreach( get_terms( 'product_shipping_class', array( 'hide_empty' => false ) ) as $class ) {
			$shipping_classes[ $class->slug ] = $class->name;
		}

		$product_type = array();
		foreach( get_terms( 'product_type', array( 'hide_empty' => false ) ) as $type ) {
			$product_type[ $type->slug ] = $type->name;
		}

		$weight_unit = get_option( 'woocommerce_weight_unit' );
		$dimension_unit = get_option( 'woocommerce_dimension_unit' );
		$countries = WC()->countries->countries;

		// Available notification appearance triggers
		$triggers_template = <<<json
{
	mandatory: [ 'radio_show', 'radio_hide', 'checkbox_enable', 'value', 'priority' ],
	data: {
			allways:					{
				adk_event:			"{$this->__( 'Allways', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Notification will be displayed all the time', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			null,
				checkbox_enable:	true,
				value:				'',
				input:				null,
				priority:			100
			},
			free_shipping_enabled:		{
				adk_event:			"{$this->__( 'Free shipping method is enabled', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when free shipping is enabled in store config', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				'',
				input:				null,
				priority:			10
			},
			free_shipping_amount:		{
				adk_event:			"{$this->__( 'Insufficient amount of the order for free shipping', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when free shipping is available for the order, but order amount is less then free shipping minimum total', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				'',
				input:				null,
				priority:			10
			},
			cart_sub_total:				{
				adk_event:			"{$this->__( 'Cart sub total is >= ', 'advertikon' )} $cur_reg_exp_str <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when the cart subtotal reaches specific amount', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				0,
				input:				"number",
				priority:			10
			},
			cart_sub_total_ex_tax:		{
				adk_event:			"{$this->__( 'Cart sub total (ex. tax) is >= ', 'advertikon' )} $cur_reg_exp_str  <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when the cart subtotal (ex. tax) reaches specific amount', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				0,
				input:				"number",
				priority:			10
			},
			cart_total:					{
				adk_event:			"{$this->__( 'Cart total is >= ', 'advertikon' )} $cur_reg_exp_str <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when the cart reaches specific amount', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				0,
				input:				"number",
				priority:			10
			},
			cart_weight:				{
				adk_event:			"{$this->__( 'Cart contents are >= ', 'advertikon' )} %$weight_unit <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when the cart contents reaches specific weight', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				0,
				input:				"number",
				priority:			10
			},
			cart_product_length:		{
				adk_event:			"{$this->__( 'Product length >= ', 'advertikon' )} %$dimension_unit <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when one of the products in cart has length equal or greater than specified value', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				0,
				input:				"number",
				priority:			10	
			},
			cart_product_height:		{
				adk_event:			"{$this->__( 'Product height >= ', 'advertikon' )} %$dimension_unit <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when one of the products in cart has height equal or greater than specified value', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				0,
				input:				"number",
				priority:			10
			},
			cart_product_width:			{
				adk_event:			"{$this->__( 'Product width >= ', 'advertikon' )} %$dimension_unit <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when one of the products in cart has width equal or greater than specified value', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				0,
				input:				"number",
				priority:			10
			},
			category:					{
				adk_event:			"{$this->__( 'Categories', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when cart contains product in one of the categories', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				select2:			true,
				input:				'text',
				multiple:			true,
				data:				adk_cats,
				priority:			10
			},
			tag:						{
				adk_event:			"{$this->__( 'Tags', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when cart contains product with one of thr tags', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				select2:			true,
				input:				'text',
				multiple:			true,
				data:				adk_tags,
				priority:			10
			},
			payment_country:			{
				adk_event:			"{$this->__( 'Payment countriy', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when customer payment country is in list', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				select2:			true,
				input:				'text',
				multiple:			true,
				data:				adk_countries,
				priority:			10
			},
			shipping_country:			{
				adk_event:			"{$this->__( 'Shipping countriy', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when customer shipping country is in list', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				select2:			true,
				input:				'text',
				multiple:			true,
				data:				adk_countries,
				priority:			10
			},
			cart_product:				{
				adk_event:			"{$this->__( 'Product(s)', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when specific product is in the cart', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				input:				'text',
				ajax:				"/?wc-ajax=adk_products_list",
				select2:			true,
				multiple:			true,
				priority:			10
			},
			cart_product_related:				{
				adk_event:			"{$this->__( 'Related product(s)', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when product in the cart has specific related product(s)', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				input:				'text',
				ajax:				"/?wc-ajax=adk_products_list",
				select2:			true,
				multiple:			true,
				priority:			10
			},
			shipping_class:			{
				adk_event:			"{$this->__( 'Shipping class', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when product in the cart belongs to specific shipping class', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				select2:			true,
				input:				'text',
				multiple:			true,
				data:				adk_shipping_classes,
				priority:			10
			},
			product_type:			{
				adk_event:			"{$this->__( 'Product type', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when product in the cart belongs to specific product type', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				select2:			true,
				input:				'text',
				multiple:			true,
				data:				adk_product_type,
				priority:			10
			},
			coupon:				{
				adk_event:			"{$this->__( 'Coupon', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when specific coupon was applied', 'advertikon' )}\"></span> %",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				input:				'text',
				ajax:				"/?wc-ajax=adk_coupons_list",
				select2:			true,
				multiple:			true,
				priority:			10
			},
			product_quantities:	{
				adk_event:			"{$this->__( 'The number of products', 'advertikon' )} % <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when in the cart is a certain number of products', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				input:				'number',
				priority:			10
			},
			low_stock:			{
				adk_event:			"{$this->__( 'Low stock', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when one of the products in the cart has a low stock status', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				null,
				input:				null,
				priority:			10
			},
			out_of_stock:			{
				adk_event:			"{$this->__( 'Out of stock', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when one of the products in the cart has a out of stock status', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				null,
				input:				null,
				priority:			10
			},
			guest_session:			{
				adk_event:			"{$this->__( 'Guest session', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when customer is not logged in  or has no registered account', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				null,
				input:				null,
				priority:			10
			},
			vat_exempt:			{
				adk_event:			"{$this->__( 'VAT exempt', 'advertikon' )} <span class=\"woocommerce-help-tip\" title=\"{$this->__( 'Triggers when customer is VAT exempt', 'advertikon' )}\"></span>",
				radio_show:			true,
				radio_hide:			false,
				checkbox_enable:	false,
				value:				null,
				input:				null,
				priority:			10
			}
		}
	}
json;

		//Do not render form submit button
		$hide_save_button = true;

		return array(

			array(
					'type' 			=> 'adk-script',
					'body' 			=> 
										"var adk_countries = " . json_encode( $countries, JSON_FORCE_OBJECT ) . ";\n" .
										"var adkNotificationsList = " . json_encode( $notifications ) . ";\n" . 
										"var adkTriggers = " . json_encode( $this->fetch_triggers( $notifications ), JSON_FORCE_OBJECT ) . ";\n" .
										"var adk_cats = " . json_encode( $categories ) . ";\n" .
										"var adk_tags = " . json_encode( $tags ) . ";\n" .
										"var adk_shipping_classes = " . json_encode( $shipping_classes ) . ";\n" .
										"var adk_product_type = " . json_encode( $product_type ) . ";\n" .

										// Need to be the last one
										"var adkTriggersTemplate = $triggers_template;\n",
				),

			array(
					'id'			=> 'advertikon_notifications',
					'type' 			=> 'title',
					'title' 		=>  __( 'Notifications Options', 'advertikon' ),
					'desc' 			=> '',
				),

			array(
					'id'			=> 'advertikon_notifications_preview',
					'type' 			=> 'adk-notification-preview',
					'title' 		=> __( 'Notification live preview', 'advertikon' ),
					'desc'			=> __( 'Real width of notification will depend on a page width where it is embedded', 'advertikon' ),
					'css'			=> 'text-align: center;padding: 30px;height: 80px;overflow: hidden;resize:none',
					'custom_attributes'	=> array(
							'data-max-height'	=> $teaser_max_height,
						),
				),
			array(
					'id'			=> 'advertikon_notifications_name',
					'type' 			=> 'text',
					'title' 		=> __( 'Notification name', 'advertikon' ),
					'value' 		=> '',
					'default'		=> 'default',
					'desc'			=> __( 'May consist of alphanumeric symbols, dash and underscore', 'advertikon' ),
					'desc_tip'		=> true,
					'custom_attributes'	=> array(
							'data-callback'	=> 'setName',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_text',
					'type' 			=> 'textarea',
					'title' 		=> __( 'Notification text', 'advertikon' ),
					'default' 		=> __( 'To get free shipping spend another freeShipping()', 'advertikon' ),
					'class'			=> 'adk-textarea',
					'css'			=> 'height: 200px;',
					'custom_attributes'	=> array(
						'data-callback'		=> 'setText',
						'data-desc'			=> __( 'Supported functions:' ),
						'data-func'			=> require( dirname( dirname( __FILE__ ) ) . '/includes/functions.json.php' ),
						),
				),
			array(
					'id'			=> 'advertikon_notifications_text_color',
					'type'			=> 'adk-color',
					'title' 		=> __( 'Notification text color', 'advertikon' ),
					'class'			=> 'adk-color',
					'default'		=> '#000',
					'custom_attributes'	=> array(
						'data-callback'		=> 'setTextColor',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_bg_color',
					'type'			=> 'adk-color',
					'title' 		=> __( 'Notification background color', 'advertikon' ),
					'class'			=> 'adk-color',
					'default'		=> '#f92e2e',
					'custom_attributes'	=> array(
						'data-callback'		=> 'setBgColor',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_height',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification height', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> '40',
					'custom_attributes'	=> array(
						'data-callback'		=> 'setHeight',
						'data-unit'			=> 'px',
						'data-max'			=> $teaser_max_height,	
						),
				),
			array(
					'id'			=> 'advertikon_notifications_width',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification width', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> '100',
					'desc'			=> __( 'Teaser width - is width, in percentage, of available space at concrete page. ', 'advertikon' ),
					'custom_attributes'	=> array(
						'data-max'			=> 100,
						'data-callback'		=> 'setWidth',
						'data-unit'			=> '%',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_font_height',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification font height', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> '14',
					'custom_attributes'	=> array(
						'data-max'			=> ceil( $teaser_max_height / 2.5 ),
						'data-callback'		=> 'setFontHeight',
						'data-unit'			=> 'px',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_border_radius',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification border radius', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> 0,
					'custom_attributes'	=> array(
						'data-max'			=> ceil( $teaser_max_height / 2 ),
						'data-callback'		=> 'setBorderRadius',
						'data-unit'			=> 'px',
						),
				),
			array(
					'id'			=> 'advertikon_notification_shadow_vertical',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification vertical shadow', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> 0,
					'custom_attributes'	=> array(
						'data-max'			=> $teaser_max_height,
						'data-min'			=> $teaser_max_height * -1,
						'data-callback'		=> 'setShadowVertical',
						'data-unit'			=> 'px',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_shadow_horizontal',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification horizontal shadow', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> 0,
					'custom_attributes'	=> array(
						'data-max'			=> $teaser_max_height,
						'data-min'			=> $teaser_max_height * -1,
						'data-callback'		=> 'setShadowHorizontal',
						'data-unit'			=> 'px',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_shadow_dispersion',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification shadow dispersion', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> 0,
					'custom_attributes'	=> array(
						'data-max'			=> $teaser_max_height,
						'data-callback'		=> 'setShadowDispersion',
						'data-unit'			=> 'px',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_shadow color',
					'type'			=> 'adk-color',
					'title' 		=> __( 'Notification shadow color', 'advertikon' ),
					'class'			=> 'adk-color',
					'default'		=> '#000',
					'custom_attributes'	=> array(
						'data-callback'		=> 'setShadowColor',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_border_width',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification border width', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> 0,
					'custom_attributes'	=> array(
						'data-max'			=> 10,
						'data-callback'		=> 'setBorderWidth',
						'data-unit'			=> 'px',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_border_color',
					'type'			=> 'adk-color',
					'title' 		=> __( 'Notification border color', 'advertikon' ),
					'class'			=> 'adk-color',
					'default'		=> '#000',
					'custom_attributes'	=> array(
						'data-callback'		=> 'setBorderColor',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_border_style',
					'type'			=> 'select',
					'title' 		=> __( 'Notification border style', 'advertikon' ),
					'custom_attributes'	=> array(
						'data-callback'		=> 'setBorderStyle',
						),
					'class'			=> 'adk-select',
					'options'		=> array(
							'double'	=> __( 'Double', 'advertikon' ),
							'dashed'	=> __( 'Dashed', 'advertikon' ),
							'solid'		=> __( 'Solid', 'advertikon' ),
							'dotted'	=> __( 'Dotted', 'advertikon' ),
							'groove'	=> __( 'Groove', 'advertikon' ),
							'ridge'		=> __( 'Ridge', 'advertikon' ),
							'inset'		=> __( 'Inset', 'advertikon' ),
							'outset'	=> __( 'Outset', 'advertikon' ),
						),
				),
			array(
					'id'			=> 'advertikon_notifications_left',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification left side offset', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> 0,
					'custom_attributes'	=> array(
						'data-max'			=> 500,
						'data-min'			=> 500 * -1,
						'data-callback'		=> 'setLeft',
						'data-unit'			=> 'px',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_bottom',
					'type'			=> 'adk-slider',
					'title' 		=> __( 'Notification bottom side offset', 'advertikon' ),
					'class'			=> 'adk-slider',
					'default'		=> 0,
					'custom_attributes'	=> array(
						'data-max'			=> 500,
						'data-min'			=> 500 * -1,
						'data-callback'		=> 'setBottom',
						'data-unit'			=> 'px',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_position',
					'title' 		=> __( 'Notiication position', 'advertikon' ),
					'type' 			=> 'multiselect',
					'class'			=> 'adk-multiselect',
					'options'		=> array(
							'woocommerce_before_cart'                      => __( 'Before cart', 'advertikon' ),
							'woocommerce_before_cart_table'                => __( 'Before cart table', 'advertikon' ),
							'woocommerce_before_cart_contents'             => __( 'Before cart contents', 'advertikon' ),
							'woocommerce_after_cart_contents'              => __( 'After cart contents', 'advertikon' ),
							'woocommerce_after_cart_table'                 => __( 'After cart table', 'advertikon' ),
							'woocommerce_cart_collaterals'                 => __( 'Cart bottom', 'advertikon' ),
							'woocommerce_after_cart'                       => __( 'After cart', 'advertikon' ),
							'woocommerce_before_checkout_form'             => __( 'Before checkout form', 'advertikon' ),
							'woocommerce_checkout_before_customer_details' => __( 'Checkout. Before customer\'s details', 'advertikon' ),
							'woocommerce_checkout_after_customer_details'  => __( 'Checkout. After customer\'s details', 'advertikon' ),
							'woocommerce_checkout_before_order_review'     => __( 'Checkout. Before order review', 'advertikon' ),
							'woocommerce_checkout_order_review'            => __( 'Checkout. Order review section', 'advertikon' ),
							'woocommerce_checkout_after_order_review'      => __( 'Checkout. After order review', 'advertikon' ),
							'woocommerce_after_checkout_form'              => __( 'After checkout form', 'advertikon' ),
						),
					'default'		=> 'woocommerce_before_cart',
					'custom_attributes'	=> array(
							'data-callback'	=> 'setPosition',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_button',
					'type' 			=> 'checkbox',
					'title' 		=> __( 'Call to action', 'advertikon' ),
					'desc'			=> __( 'Whether to add call to action button to notification. Button is styled with current theme rules', 'advertikon' ),
					'desc_tip'		=> true,
					'default'		=> 'yes',
					'custom_attributes'	=> array(
							'data-callback'	=> 'setButton',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_button_text',
					'type' 			=> 'text',
					'title' 		=> __( 'Call to action button text', 'advertikon' ),
					'default' 		=> 'Click me!!',
					'desc'			=> __( 'Text to display on call to action button.', 'advertikon' ),
					'desc_tip'		=> true,
					'custom_attributes'	=> array(
							'data-callback'	=> 'setButtonText',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_button_url',
					'type' 			=> 'text',
					'title' 		=> __( 'Call to action button URL', 'advertikon' ),
					'desc'			=> __( 'Page URL which will be opened on click on the button', 'advertikon' ),
					'desc_tip'		=> true,
					'custom_attributes'	=> array(
							'data-callback'	=> 'setButtonURL',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_button_url_samepage',
					'type' 			=> 'checkbox',
					'title' 		=> __( 'In the same tab', 'advertikon' ),
					'desc'			=> __( 'Open new page in the same tab, when the call to action button was clicked', 'advertikon' ),
					'desc_tip'		=> true,
					'default'		=> 'yes',
					'custom_attributes'	=> array(
							'data-callback'	=> 'setButtonURLTarget',
						),
				),
			array(
					'id'			=> 'advertikon_notifications_save',
					'type'			=> 'adk-button',
					'title' 		=> __( 'Save notification', 'advertikon' ),
					'value'			=> __( 'Save', 'advertikon' ),
					'class'			=> 'adk-button',
					'custom_attributes'	=> array(
						'data-callback'		=> 'save',
						),
				),
			array( 'type' => 'sectionend', 'id' => 'advertikon_notofications' ),
			array(
					'id'			=> 'advertikon_notifications_triggers',
					'type'			=> 'adk-list',
					'title' 		=> __( 'Triggers', 'advertikon' ),
					'class'			=> 'adk-list widefat',
					'css'			=> 'margin-top:10px',
					'fields'		=> array(
							'adk_event'			=> __( 'Event', 'advertikon' ),
							'radio_show'		=> __( 'Show', 'advertikon' ),
							'radio_hide'		=> __( 'Hide', 'advertikon' ),
							'checkbox_enable'	=> __( 'Enable', 'advertikon' ),
						),
					'custom_attributes'	=> array(
						'data-row-name'	=> 'adk_event',
						),
					'desc'			=> __( 'A list of events', 'advertikon' ) .

						// Description separator
						'<->'

						// Detailed description
						. __( "Select the event to show and hide the notification. For example, to make the notification always displayed enable event  'Always'.  To hide the notification, if prouduct in the cart belongs to a certain category, select the category in the 'Categories' event dropdown, select event option 'Hide' and enable event. Events take priority in accordance with the position in the list, to change the position, drag event in the list", 'advertikon' ),
				),
			array(
					'id'			=> 'advertikon_notifications_list',
					'type'			=> 'adk-list',
					'title' 		=> __( 'Notifications list', 'advertikon' ),
					'class'			=> 'adk-list widefat',
					'fields'		=> array(
							'advertikon_notifications_name'		=> __( 'Name', 'advertikon' ),
							'advertikon_notifications_text'		=> __( 'Text', 'advertikon' ),
							'advertikon_notifications_triggers'	=> __( 'Trigger', 'advertikon' ),
							'advertikon_notifications_position'	=> __( 'Possition', 'advertikon' ),
							'button_remove'						=> __( 'Delete', 'advertikon' ),
						),
					'custom_attributes'	=> array(
						'data-row-name'	=> 'advertikon_notifications_name',
						),
					'desc'			=> __( 'Notifications list', 'advertikon' ),
				),
			);
	}

	/**
	* Fetch triggers settings
	*
	* @param Array $notifications Notifications setting
	* @return Array
	*/
	protected function fetch_triggers( $notifications, $template = array() ) {
		$conf = array();

		//Fetch triggers from notifications settings
		foreach( array_merge( array( 'default' => array() ), $notifications ) as $notification_name => $notification_settings ) {
			if( isset( $notification_settings['advertikon_notifications_triggers'] ) ) {
				$conf[ $notification_name ] = $notification_settings['advertikon_notifications_triggers'];
			}
			else {
				$conf[ $notification_name ] = array();
			}
		}

		return $conf;
	}

	protected function get_section_controls() {
		$ret = array();

		if ( $this->notification->get_widget()->is_simple() ) {
			$sections = array( 'content' );

		} else {
			$sections = array( 'content', 'top', 'bottom', 'left', 'right', );
		}

		foreach( $sections as $section_name ) {
			$section = 'section_' . $section_name;

			$ret = array_merge( $ret, array(
				array(
					'type'  => 'adk_title',
					'title' => __( 'Section', Advertikon_Notifications::LNS ) . ': ' . ucfirst( $section_name ),
					'id'    => $section,
				),
				array(
					'id'			=> Advertikon_Notifications::prefix( $section_name . '_text' ),
					'name'          => 'section[' . $section_name . '][' . 'text' . ']',
					'type' 			=> 'adk_textarea',
					'title' 		=> __( 'Text', Advertikon_Notifications::LNS ),
					'default' 		=>  $this->notification->get_widget()->get_default( "section/$section_name/text" ),
					'class'         => 'adk-widget-control',
					'css'			=> array( 'height' => '200px' ),
					'desc'          => __( 'Supported shortcodes', Advertikon_Notifications::LNS ) .
										'<br>' . implode( '<br>', $this->notification->get_widget()->get_shortcode()->get_list() ),
				),
				array(
					'id'			=> Advertikon_Notifications::prefix( $section_name . '_text_color' ),
					'name'          => 'section[' . $section_name . '][' . 'text_color' . ']',
					'type'			=> 'adk_color',
					'title' 		=> __( 'Text color', Advertikon_Notifications::LNS ),
					'class'         => 'adk-widget-control',
					'default'       =>  $this->notification->get_widget()->get_default( "section/$section_name/text_color" ),
				),
				array(
					'id'			=> Advertikon_Notifications::prefix( $section_name . '_align' ),
					'name'          => 'section[' . $section_name . '][' . 'align' . ']',
					'type'			=> 'adk_select',
					'title' 		=> __( 'Align', Advertikon_Notifications::LNS ),
					'class'         => 'adk-widget-control',
					'default'       =>  $this->notification->get_widget()->get_default( "section/$section_name/align" ),
					'options'       => array(
						'left'    => __( 'Left', Advertikon_Notifications::LNS ),
						'center'  => __( 'Center', Advertikon_Notifications::LNS ),
						'right'   => __( 'Right', Advertikon_Notifications::LNS ),
						'justify' => __( 'Justify', Advertikon_Notifications::LNS ),
					),
				),
				array(
					'id'			=> Advertikon_Notifications::prefix( $section_name . '_valign' ),
					'name'          => 'section[' . $section_name . '][' . 'valign' . ']',
					'type'			=> 'adk_select',
					'title' 		=> __( 'Vertical align', Advertikon_Notifications::LNS ),
					'class'         => 'adk-widget-control',
					'default'       =>  $this->notification->get_widget()->get_default( "section/$section_name/valign" ),
					'options'       => array(
						'top'    => __( 'Top', Advertikon_Notifications::LNS ),
						'middle' => __( 'Middle', Advertikon_Notifications::LNS ),
						'bottom' => __( 'Bottom', Advertikon_Notifications::LNS ),
					),
				),
				array(
					'id'			=> Advertikon_Notifications::prefix( $section_name . '_bg_color' ),
					'name'          => 'section[' . $section_name . '][' . 'bg_color' . ']',
					'type'			=> 'adk_color',
					'title' 		=> __( 'Background color', Advertikon_Notifications::LNS ),
					'class'         => 'adk-widget-control',
					'default'       =>  $this->notification->get_widget()->get_default( "section/$section_name/bg_color" ),
				),
				array(
					'id'			=> Advertikon_Notifications::prefix( $section_name . '_font_height' ),
					'name'          => 'section[' . $section_name . '][' . 'font_height' . ']',
					'type'			=> 'adk_number',
					'title' 		=> __( 'Text height', Advertikon_Notifications::LNS ),
					'class'         => 'adk-widget-control',
					'default'		=>  $this->notification->get_widget()->get_default( "section/$section_name/font_height" ),
				),
				array(
					'id'			=> Advertikon_Notifications::prefix( $section_name . '_padding' ),
					'name'          => 'section[' . $section_name . '][' . 'padding' . ']',
					'type'			=> 'adk_number',
					'title' 		=> __( 'Padding', Advertikon_Notifications::LNS ),
					'class'         => 'adk-widget-control',
					'default'		=>  $this->notification->get_widget()->get_default( "section/$section_name/padding" ),
				),
				array(
					'id'			=> Advertikon_Notifications::prefix( $section_name . '_height' ),
					'name'          => 'section[' . $section_name . '][' . 'height' . ']',
					'type'			=> 'adk_number',
					'title' 		=> __( 'Height', Advertikon_Notifications::LNS ),
					'class'         => 'adk-widget-control',
					'default'		=>  $this->notification->get_widget()->get_default( "section/$section_name/height" ),
				),
				array(
					'type' => 'sectionend',
				)
			) );

		}
		
		return $ret;
	}

	protected function get_button_controls() {
		return array(
			array(
				'type' => 'title',
				'title' => __( 'Call to action button', Advertikon_Notifications::LNS ),
				),
			array(
				'id'			=> 'button-name',
				'type' 			=> 'adk_text',
				'title' 		=> __( 'Button\'s name', Advertikon_Notifications::LNS ),
				'desc'          => __( 'Button\'s name for your reference', Advertikon_Notifications::LNS ),
				'default'		=> 'default',
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'name' ),
			),
			array(
				'id'			    => 'load-button',
				'type' 			    => 'adk_select',
				'title' 		    => __( 'Load button', Advertikon_Notifications::LNS ),
				'options'           => $this->notification->get_widget()->get_button_list(),
				'desc'			    => __( 'Select a button to modify', Advertikon_Notifications::LNS ),
				'desc_tip'		    => true,
				'custom_attributes' => array(
					'data-url' => Advertikon::ajax_url( array(
						'action' => Advertikon_Notifications::$ajax_endpoints['load_button']
					) )
				),
			),
			array(
				'id'			=> 'button-text',
				'type' 			=> 'adk_text',
				'title' 		=> __( 'Caption', Advertikon_Notifications::LNS ),
				'default' 		=> $this->notification->get_widget()->get_button_default( 'text' ),
				'desc'			=> __( 'Text to display on call to action button.', Advertikon_Notifications::LNS ),
				'desc_tip'		=> true,
				'class'         => 'adk-button-control',
			),
			array(
				'id'			=> 'button-url',
				'type' 			=> 'adk_text',
				'title' 		=> __( 'Action URL', Advertikon_Notifications::LNS ),
				'desc'			=> __( 'URL to open in response to click on CTA button', Advertikon_Notifications::LNS ),
				'desc_tip'		=> true,
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'url' ),
			),
			array(
				'id'			=> 'button-bg_color',
				'type'			=> 'adk_color',
				'title' 		=> __( 'Background color', Advertikon_Notifications::LNS ),
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'bg_color' ),
			),
			array(
				'id'			=> 'button-text_color',
				'type'			=> 'adk_color',
				'title' 		=> __( 'Text color', Advertikon_Notifications::LNS ),
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'text_color' ),
			),
			array(
				'id'			=> 'button-padding',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Padding', Advertikon_Notifications::LNS ),
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'padding' ),
			),
			array(
				'id'			=> 'button-font_height',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Text size', Advertikon_Notifications::LNS ),
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'font_height' ),
			),
			array(
				'id'			=> 'button-border_width',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Border width', Advertikon_Notifications::LNS ),
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'border_width' ),
			),
			array(
				'id'			=> 'button-border_radius',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Border radius', Advertikon_Notifications::LNS ),
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'border_radius' ),
			),
			array(
				'id'			=> 'button-border_color',
				'type'			=> 'adk_color',
				'title' 		=> __( 'Border color', Advertikon_Notifications::LNS ),
				'class'         => 'adk-button-control',
				'default' 		=> $this->notification->get_widget()->get_button_default( 'border_color' ),
			),
			array(
				'id'			    => uniqid(),
				'type'			    => 'adk_button',
				'caption' 		    => __( 'Save button', Advertikon_Notifications::LNS ),
				'class'			    => 'adk-save-button button-primary',
				'button_type'       => 'button',
				'standalone'        => false,
				'custom_attributes' => array(
					'data-url' => Advertikon::ajax_url( array(
						'action' => Advertikon_Notifications::$ajax_endpoints['save_button']
					) )
				),
			),
			array(
				'type' => 'sectionend',
			),
		);

		return $ret;
	}

	protected function get_save_button() {
		return array(
			'id'			    => uniqid(),
			'type'			    => 'adk_button',
			'caption' 		    => __( 'Save widget', Advertikon_Notifications::LNS ),
			'class'			    => 'adk-save-widget button-primary',
			'button_type'       => 'button',
			'custom_attributes' => array(
				'data-url' => Advertikon::ajax_url( array(
					'action' => Advertikon_Notifications::$ajax_endpoints['save_widget']
				) )
			),
		);
	}
}
