<?php
/**
	Plugin Name: Smart Notifications
	Plugin URI:
	Version: 2.0.0
	Description: Woocommerce plug-in to create a highly customizable notifications at the store side
	Author: Advertikon
	Author URI:
	Text Domain: advertikon
	Domain Path: /languages
	Network :
	License:
*/

// Make sure we don't expose any info if called directly
// if ( ! function_exists( 'add_action' ) ) {
// 	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
// 	exit;
// }

require_once( plugin_dir_path( __FILE__ ) . '../library/advertikon.php' );

//if( true || ! class_exists( 'Advertikon_Notifications' ) ) :
class Advertikon_Notifications extends Advertikon {

	protected $FILE          = __FILE__;
	protected $name          = 'Smart Notification';
	protected $class_prefix  = 'Advertikon_Notification';   // autoloader class prefix (base)
	static protected $prefix = 'advertikon_notifications_'; // module prefix

	const LNS = 'advertikon_notification';

	/**
	 * @var String ID Module name
	 */
	const ID = 'adk_notification';

	static public $ajax_endpoints = array(
		'save_widget'   => 'save_widget',
		'save_button'   => 'save_button',
		'delete_widget' => 'delete_widget',
		'load_widget'   => 'load_widget',
		'load_button'   => 'load_button',
	);

	/**
	 * @var Object $shipping_method Free shipping method instance
	 */
	protected $shipping_method = null;

	/**
	 * @var Object $message Admin area notice handler
	 */
	static $message = null;

	/**
	 * @var String $save_notification_hook AJAX request save notification hook
	 */
	protected $save_notification_hook = 'adk_save_notification';

	/**
	 * @var String $delete_notification_hook AJAX request delete notification hook
	 */
	protected $delete_notification_hook = 'adk_delete_notification';

	/**
	 * @var String $nonce_action Nonce action name for edit notification
	 */
	protected $nonce_action = 'adk_edit_notification';

	/** @var Advertikon_Notification_Includes_Widget */
	protected $widget;
	
	/** @var Advertikon_Notification_Includes_Filter */
	protected $filter;

	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct();
		load_plugin_textdomain( self::LNS, false,  dirname( __FILE__ ) . '/languages' );

		$this->init();

		parent::$instances['notification'] = $this;
	}

	protected function init() {
		parent::init();

		$filter_class = class_exists( 'Advertikon_Notification_Includes_Filter_Extended' ) ?
			'Advertikon_Notification_Includes_Filter_Extended' : 'Advertikon_Notification_Includes_Filter';
		
		$this->filter = new $filter_class();

		$widget_class = class_exists( 'Advertikon_Notification_Includes_Widget_Extended' ) ?
			'Advertikon_Notification_Includes_Widget_Extended' : 'Advertikon_Notification_Includes_Widget';
		
		$this->widget = new $widget_class( $this->filter );
			

		if( is_admin() ) {
			if( isset( $_GET['tab'] ) && $_GET['tab'] == Advertikon_Notifications::ID ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
			}

			add_action( 'woocommerce_get_settings_pages', function(){ new Advertikon_Notification_Includes_Setting( $this ); } );

			foreach( self::$ajax_endpoints as $a ) {
				add_action( 'wp_ajax_' . $a, [ $this, $a ] );
			}

		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
		}

		// Add ajax template redirect to products list fetcher
		add_action( 'wc_ajax_adk_products_list', array( $this, 'get_products_list' ) );

		// Add ajax template redirect to products list fetcher
		add_action( 'wc_ajax_adk_coupons_list', array( $this, 'get_coupons_list' ) );
	}

	public function get_widget() {
		return $this->widget;
	}

	public function get_filter() {
		return $this->filter;
	}

	/**
	 * Saves widget
	 * AJAX endpoint
	 */
	public function save_widget() {
		$ret = array();

		try {
			if( !current_user_can( 'manage_woocommerce' ) ) {
				throw new Exception( __( 'You must have permission to manage WooCommerce store' ) );
			}

			$this->widget->save( $_POST );
			$ret['success'] = __( 'The wigdet has been saved', self::LNS );

		} catch ( Exception $e ) {
			$ret['error'] = $e->getMessage();
			Advertikon::log( $e );
		}

		wp_send_json( $ret );
	}

	/**
	 * Loads specific widget
	 * AJAX endpoint
	 */
	public function load_widget() {
		$ret = array();

		try {
			if( !current_user_can( 'manage_woocommerce' ) ) {
				throw new Exception( __( 'You must have permission to manage WooCommerce store', self::LNS ) );
			}

			$name = Advertikon::request( 'name' );

			if ( !$name ) {
				throw new Exception( __( 'Name is missing', self::LNS ) );
			}

			$data = $this->widget->load( $name );
			$ret['success'] = true;
			$ret['data'] = $data;

		} catch ( Exception $e ) {
			$ret['error'] = $e->getMessage();
			Advertikon::log( $e );
		}

		wp_send_json( $ret );
	}

	/**
	 * Saves button
	 * AJAX endpoint
	 */
	public function save_button() {
		$ret = array();

		try {
			if( !current_user_can( 'manage_woocommerce' ) ) {
				throw new Exception( __( 'You must have permission to manage WooCommerce store' ) );
			}

			$this->widget->save_button( $_POST );
			$ret['success'] = __( 'The button has been saved', self::LNS );

		} catch ( Exception $e ) {
			$ret['error'] = $e->getMessage();
			Advertikon::log( $e );
		}

		wp_send_json( $ret );
	}

	/**
	 * Loads specific button
	 * AJAX endpoint
	 */
	public function load_button() {
		$ret = array();

		try {
			if( !current_user_can( 'manage_woocommerce' ) ) {
				throw new Exception( __( 'You must have permission to manage WooCommerce store', self::LNS ) );
			}

			$name = Advertikon::request( 'name' );

			if ( !$name ) {
				throw new Exception( __( 'Name is missing', self::LNS ) );
			}

			$data = $this->widget->load_button( $name );
			$ret['success'] = true;
			$ret['data'] = $data;

		} catch ( Exception $e ) {
			$ret['error'] = $e->getMessage();
			Advertikon::log( $e );
		}

		wp_send_json( $ret );
	}

	/**
	 * Checks whether value is true
	 *
	 * @param Mixed $val
	 * @return Boolean
	 */
// 	protected function is_true( $val ) {
// 		return $val === true || strtolower( $val ) === 'true' || strtolower( $val ) === 'yes';
// 	}

	/**
	 * Checks fired triggers
	 *
	 * @param Array $notification Notification data
	 * @return Boolean
	 */
	protected function is_triggered( $notification ) {

		// Default value
		$result = false;
		$this->use_priority = true;

		if( $this->use_priority ) {
			$triggers = $this->priority_sort( $notification['advertikon_notifications_triggers'] );
		} else {
			$triggers = $notification['advertikon_notifications_triggers'];
		}

		foreach( $triggers as $trigger ) {


			if( ! $this->is_true( $trigger['checkbox_enable'] ) ) {
				continue;
			}

			// Select2 doesn't provide default or empty value
			if ( empty( $trigger['value'] ) ){
				$trigger['value'] = '';
			}

			switch( $trigger['name'] ) {

				// Always show
				case 'allways' :
					return true;
					break;

				// Free shipping is enabled in store config
				case 'free_shipping_enabled' : 
					$set = get_option( 'woocommerce_free_shipping_settings', array() );
					if( isset( $set['enabled'] ) || $this->is_true( $set['enabled'] ) ) {
						return $this->is_true( $trigger['radio_show'] );
					}
					break;

				// Insufficient amount for free shipping
				case 'free_shipping_amount' :
					if( WC()->cart && $this->get_free_shipping() ) {
						foreach( WC()->cart->get_shipping_packages() as $package ) {
							if( $this->is_need_more_sum_free_shipping( $package ) ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Cart subtotal greater or equal to specific sum 
				case 'cart_sub_total' :
					if( WC()->cart && $trigger['value'] <= WC()->cart->subtotal ) {
						return $this->is_true( $trigger['radio_show'] );
					}
					break;

				// Cart subtotal excluding taxes greater or equal to specific sum 
				case 'cart_sub_total_ex_tax' :
					if( WC()->cart && $trigger['value'] <= WC()->cart->subtotal_ex_tax ) {
						return $this->is_true( $trigger['radio_show'] );
					}
					break;

				//Cart total greater or equal to specific sum 
				case 'cart_total' :
					if( WC()->cart && $trigger['value'] <= WC()->cart->total ) {
						return $this->is_true( $trigger['radio_show'] );
					}
					break;

				//Cart contents weight greater or equal to specific weight
				case 'cart_weight' :
					if( WC()->cart && $trigger['value'] <= WC()->cart->get_cart_contents_weight() ) {
						return $this->is_true( $trigger['radio_show'] );
					}
					break;

				// Cart product has length equal or greater then
				case 'cart_product_length' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							if( (float)$item['data']->get_length() <= (float)$trigger['value'] ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Cart product has width equal or greater then
				case 'cart_product_width' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							if( (float)$item['data']->get_width() <= (float)$trigger['value'] ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Cart product has height equal or greater then
				case 'cart_product_height' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							if( (float)$item['data']->get_height() <= (float)$trigger['value'] ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Product belongs to one of categories
				case 'category' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							foreach( get_the_terms( $item['data']->id, 'product_cat' ) as $cat ) {
								if( $this->match_select2( $cat->slug, $trigger['value'] ) ) {
									return $this->is_true( $trigger['radio_show'] );
								}
							}
						}
					}
					break;

				// Product has tag
				case 'tag' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							foreach( get_the_terms( $item['data']->id, 'product_tag' ) as $tag ) {
								if( $this->match_select2( $tag->slug, $trigger['value'] ) ) {
									return $this->is_true( $trigger['radio_show'] );
								}
							}
						}
					}
					break;

				// Customer has specific payment country
				case 'payment_country' :
					if( WC()->customer ) {
						if( $this->match_select2(
								WC()->customer->get_country(),
								$trigger['value'] )
							) {
							return $this->is_true( $trigger['radio_show'] );
						}
					}
					break;

				// Customer has specific payment country
				case 'shipping_country' :
					if( WC()->customer ) {
						if( $this->match_select2(
								WC()->customer->get_shipping_country(),
								$trigger['value']
							) ) {
							return $this->is_true( $trigger['radio_show'] );
						}
					}
					break;

				// Specific product in cart
				case 'cart_product' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							if( $this->match_select2( $item['product_id'], $trigger['value'] ) ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Related product in cart
				case 'cart_product_related' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							foreach( $item['data']->get_related() as $related ){
								if( $this->match_select2( $related, $trigger['value'] ) ) {
									return $this->is_true( $trigger['radio_show'] );
								}
							}
						}
					}
					break;

				// Product belongs to specific shipping class
				case 'shipping_class' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							$sc = $item['data']->get_shipping_class();
							if( $sc && $this->match_elect2( $sc, $trigger['value'] ) ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Product belongs to specific product type
				case 'product_type' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_cart() as $item ) {
							$type = $item['data']->get_type();
							if( $type && $this->match_select2( $type, $trigger['value'] ) ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Product belongs to specific product type
				case 'coupon' :
					if( WC()->cart ) {
						foreach( WC()->cart->get_coupons() as $coupon ) {
							if( $this->match_select2( $coupon->id, $trigger['value'] ) ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Certain product number in the cart
				case 'product_quantities' :
					if( WC()->cart ) {
						$q = 0;
						foreach( WC()->cart->get_cart_item_quantities() as $quantity ) {
							$q += $quantity;
						}
						if( $q >= $trigger['value'] ) {
							return $this->is_true( $trigger['radio_show'] );
						}
					}
					break;

				// Low stock status
				case 'low_stock' :
					if( WC()->cart ) {
						$low_threshold = get_option( 'woocommerce_notify_low_stock_amount' );
						foreach( WC()->cart->get_cart() as $item ) {
							$stock = $item['data']->get_stock_quantity();
							if( ! is_null( $stock ) && $stock <= $low_threshold ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Out of stock status
				case 'out_of_stock' :
					if( WC()->cart ) {
						$out_threshold = get_option( 'woocommerce_notify_no_stock_amount' );
						foreach( WC()->cart->get_cart() as $item ) {
							$stock = $item['data']->get_stock_quantity();
							if( ! is_null( $stock ) && $stock <= $out_threshold ) {
								return $this->is_true( $trigger['radio_show'] );
							}
						}
					}
					break;

				// Guest session
				case 'guest_session' :
					if( ! is_user_logged_in() ) {
						return $this->is_true( $trigger['radio_show'] );
					}
					break;

				// Vat_exempt
				case 'vat_exempt' :
					if( WC()->customer && WC()->customer->is_vat_exempt() ) {
						return $this->is_true( $trigger['radio_show'] );
					}
					break;
			}
		}
		return $result;
	}

	/**
	 * Checks if free shipping is available.
	 *
	 * @param array $package
	 * @return bool
	 */
	protected function is_need_more_sum_free_shipping( $package ) {
		if ( 'no' === $this->shipping_method->enabled ) {
			return false;
		}

		if ( 'specific' === $this->shipping_method->availability ) {
			$ship_to_countries = $this->shipping_method->countries;
		} else {
			$ship_to_countries = array_keys( WC()->countries->get_shipping_countries() );
		}

		if ( is_array( $ship_to_countries ) && ! in_array( $package['destination']['country'], $ship_to_countries ) ) {
			return false;
		}

		// Enabled logic
		$is_available       = false;
		$has_coupon         = false;
		$has_met_min_amount = false;

		if ( in_array( $this->shipping_method->requires, array( 'coupon', 'either', 'both' ) ) ) {

			if ( $coupons = WC()->cart->get_coupons() ) {
				foreach ( $coupons as $code => $coupon ) {
					if ( $coupon->is_valid() && $coupon->enable_free_shipping() ) {
						$has_coupon = true;
					}
				}
			}
		}

		if ( in_array( $this->shipping_method->requires, array( 'min_amount', 'either', 'both' ) ) &&
			 isset( WC()->cart->cart_contents_total ) ) {

			if ( WC()->cart->prices_include_tax ) {
				$total = WC()->cart->cart_contents_total + array_sum( WC()->cart->taxes );
			} else {
				$total = WC()->cart->cart_contents_total;
			}

			if ( $total >= $this->shipping_method->min_amount ) {
				$has_met_min_amount = true;
			}
		}

		switch ( $this->shipping_method->requires ) {
			case 'min_amount' :
				if ( ! $has_met_min_amount ) {
					$is_available = true;
				}
				break;
			case 'coupon' :

				// Do not show in this case
				break;
			case 'both' :
				if ( ! $has_met_min_amount && $has_coupon ) {
					$is_available = true;
				}
				break;
			case 'either' :
				if ( ! $has_met_min_amount && ! $has_coupon ) {
					$is_available = true;
				}
				break;
			default :
				$is_available = true;
		}
		return $is_available;
	}

	/**
	 * Add scripts to admin area
	 */
	public function add_admin_scripts() {

		// Plugin main script - at footer
		wp_enqueue_script(
			'adk_notifications',
			plugins_url( 'js/adk_notifications.js', __FILE__ ),
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-widget',
				'jquery-ui-button',
				'jquery-ui-slider',
				'jquery-ui-draggable',
				'jquery-ui-sortable',
			),
			false,
			true
		);
		
		wp_enqueue_script(
			'select2',
			plugins_url( 'assets/js/select2/select2.js', 'woocommerce/woocommerce.php' ),
			array(
				'jquery',
			),
			false,
			true
		);
		
		wp_enqueue_script(
			'spectrum',
			plugins_url( 'js/spectrum.js', __FILE__ ),
			array(
				'jquery',
				'tinycolor'
			),
			false,
			true
		);
		
		wp_enqueue_script(
			'tinycolor',
			plugins_url( 'js/tinycolor-min.js', __FILE__ ),
			array(
				'jquery',
			),
			false,
			true
		);

		wp_scripts()->add_inline_script(
			'adk_notifications',
			// Localization and code injections
			"var adkLang=" . json_encode( array(
				'show_if'                  => __( 'Show if', 'advertikon' ),
				'hide_if'                  => __( 'Hide if', 'advertikon' ),
				'disabled'                 => __( 'Disabled', 'advertikon' ),
				'saving'                   => __( 'Saving', 'advertikon' ),
				'network_error'            => __( 'Network error', 'advertikon' ),
				'search'                   => __( 'Search for item', 'advertikon' ),
				'searching'                => __( 'Searching', 'advertikon' ),
				'query_min_length'         => __( 'Query should be at least % character(s) long', 'advertikon' ),
				'query_max_length'         => __( 'Query may be maximum % character(s) long', 'advertikon' ),
				'no_matches'               => __( 'No matches found', 'advertikon' ),
				'parse_error'              => __( 'Response parsing error', 'advertikon' ),
				'ajax_delete_notification' => site_url() . '?wc-ajax=' . $this->delete_notification_hook,
				'wpnonce'                  => wp_create_nonce( $this->nonce_action ),
				'prefix'                   => Advertikon_Notifications::prefix( '' ),
				'missUrl'                  => __( 'URL is missing', self::LNS ),
			) ) . ";",
			'before'
		);

		$this->add_styles();
	}

	/**
	 * Add front end scripts
	 */
	public function add_scripts() {

		// Plugin main script - at footer
		wp_enqueue_script(
			'adk-widget',
			plugins_url( 'js/adk_widget.js', __FILE__ ),
			array( 'jquery' ),
			false,
			true
		);

		// wp_script_add_data(
		// 	'adk_function_parser',
		// 	'data',
		// 	$this->minify_script(

		// 		// Notification text and call-to-action button responsive functionality
		// 		$this->fix_banner_css_script() ) . ';' . PHP_EOL .

		// 		// Notification's text parser functions templates
		// 		// Such complication (the way to inject JavaScript to page) needed
		// 		// so to decrease code support efforts - one code for front-end and back-end.
		// 		'var adkParserData = ' . require( dirname( __FILE__ ) . '/includes/functions.json.php' )
		// 	);

		$this->add_styles();
	}

	/**
	 * Add styles
	 */
	public function add_styles() {
		wp_enqueue_style( 'adk_notifications', plugins_url( 'css/adk_notifications.css', __FILE__ ) );
		wp_enqueue_style( 'spectrum', plugins_url( 'css/spectrum.css', __FILE__ ) );
	}

	/**
	* Fetch product list based on query string
	* AJAX endpoint
	*/
	public function get_products_list() {

		global $wpdb, $table_prefix;
		$resp = array();

		try {

			if( ! current_user_can( 'read' ) ) {
				throw new ADK_Error( __( 'You must have permission to read products' ) );
			}

			$name = ! empty( $_REQUEST['q'] ) ? esc_sql( $wpdb->esc_like( $_REQUEST['q'] ) ) : '_';

			$res = array();

			if( $name ) {
				$res = $wpdb->get_results(
					"SELECT `ID` as 'id', `post_title` as 'text' FROM `{$table_prefix}posts` WHERE `post_type` = 'product' AND `post_title` LIKE '%$name%' LIMIT 20"
					);
			}

			$resp['items'] = $res;
			
		} catch( ADK_Error $e ) {
			$resp['error'] = __( 'Error', 'advertikon' ) . ':' . $e->getMessage();
		}

		echo json_encode( $resp );
	}

	/**
	* Fetch coupons list based on query string
	* AJAX endpoint
	*/
	public function get_coupons_list() {

		global $wpdb, $table_prefix;
		$resp = array();

		try {

			if( ! current_user_can( 'read_shop_coupon' ) ) {
				throw new ADK_Error( __( 'You must have permission to read shop coupons' ) );
			}

			$name = ! empty( $_REQUEST['q'] ) ? esc_sql( $wpdb->esc_like( $_REQUEST['q'] ) ) : '_';

			$res = array();

			if( $name ) {
				$res = $wpdb->get_results(
					"SELECT `ID` as 'id', `post_title` as 'text' FROM `{$table_prefix}posts` WHERE `post_type` = 'shop_coupon' AND `post_title` LIKE '%$name%' LIMIT 20"
					);
			}

			$resp['items'] = $res;
			
		} catch( ADK_Error $e ) {
			$resp['error'] = __( 'Error', 'advertikon' ) . ':' . $e->getMessage();
		}

		echo json_encode( $resp );
	}
}

$advertikon_notifications = new Advertikon_Notifications();