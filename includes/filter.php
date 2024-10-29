<?php

/**
 * @package notifications
 * @author Advertikon
 */

class Advertikon_Notification_Includes_Filter {
	const RESTRICT_EQUAL     = 1;
	const RESTRICT_NOT_EQUAL = 2;
	const RESTRICT_LESSER    = 3;
	const RESTRICT_GREATER   = 4;

	const MATCH_AND = 1;
	const MATCH_OR  = 2;
	
	/** @var $free_shipping WC_Shipping_Free_Shipping */
	protected $free_shipping;

	protected $match_logic = self::MATCH_AND;

	public function get_controls() {
		return array(
			array(
				'type' => 'title',
				'title' => __( 'Filters', Advertikon_Notifications::LNS ),
				'sort'  => 0,
			),
			array(
				'id'			=> 'add-filter',
				'type' 			=> 'adk_button',
				'title' 		=> __( 'Add template', Advertikon_Notifications::LNS ),
				'caption'		=> __( 'Add', Advertikon_Notifications::LNS ),
				'class'         => 'button-secondary',
			),
			array(
				'type'			=> 'adk_pass',
				'content'       => $this->render_template(),
				'hidden'        => true,
			),
			array(
				'type' => 'sectionend',
				'sort' => 1000,
			),
		);
	}

	public function filter( array $data, $w_name = '' ) {
		if ( !$data ) {
			return true;
		}

		foreach ( $data as $name => $set ) {
			$match = false;

			switch( $name ) {
				case 'page':
					if ( $this->filter_page( $set ) ) {
						Advertikon::log( '[' . $w_name . '] Filtered in for page' );
						$match =  true;
					}
				break;
				case 'customer':
					if ( $this->filter_customer( $set ) ) {
						Advertikon::log( '[' . $w_name . '] Filtered in for customer' );
						$match = true;
					}
				break;
				case 'free_shipping':
					if ( $this->filter_free_shipping( $set ) ) {
						Advertikon::log( '[' . $w_name . '] Filtered in for shipping' );
						$match = true;
					}
				break;
			}

			$step_result = $this->check_step( $match );
			
			if ( !is_null( $step_result ) ) {
				return $step_result;
			}
		}

		return $this->check_overall( $step_result );
	}

	////////////////////////////////////////////////////////////////////////////////////////////////

	protected function check_step( $match, $logic = null ) {
		if ( is_null( $logic ) ) {
			$logic = $this->match_logic;
		}

		if ( $logic == self::MATCH_AND ) {
			if ( !$match ) {
				return false;
			}

		} else if ( $logic == self::MATCH_OR ) {
			if ( $match ) {
				return true;
			}

		} else {
			Advertikon::error( new Exception( 'invalid match logic condition: ' . $logic ) );
			return false;
		}
	}

	protected function check_overall( $match, $logic = null ) {
		if ( is_null( $logic ) ) {
			$logic = $this->match_logic;
		}

		if ( !is_null( $match ) ) {
			Advertikon::log( new Exception( 'Match value supposed to be NULL' ) );
			return false;
		}

		if ( $logic == self::MATCH_AND ) {
			return true;

		} else {
			return false;
		}
	}

	protected function render_template() {
		$data = array(
			'title'        => '',
			'tooltip_html' => '',
			'description'  => '',
			'id'           => '',
			'type'         => '',
		);

		$elemetn = array();

		$element[] = '<div class="filter-line template">';

		$element[] = '<select class="filter-name">';

		foreach( $this->get_available_filters() as $k => $v ) {
			$element[] = sprintf(
				'<option data-input="%s" data-restrict="%s" value="%s">%s</option>',
				esc_attr( $v['input'] ),
				esc_attr( wp_json_encode( $v['restrict'] ) ),
				$k,
				$v['name']
			);
		}

		$element[] = '</select>';

		$element[] = '<select class="filter-restrict">';

		foreach( array() as $k => $v ) {
			$element[] = '<option>' . $k . '</option>';
		}

		$element[] = '</select>';

		$element[] = '<div class="filter-value-wrapper">';
		$element[] = '<input class="filter-value" placeholder="' . __( 'Filter value', Advertikon_Notifications::LNS ) . '">';
		$element[] = '</div>';

		$element[] = Advertikon_Library_Renderer_Admin::button( array(
			'type'       => 'adk_button',
			'class'      => 'filter-delete',
			'caption'    => __( 'Delete', Advertikon_Notifications::LNS ),
			'standalone' => true,
		) );

		$element[] = '</div>';

		return implode( PHP_EOL, $element );
	}

	protected function get_available_filters() {
		return array(
			'page' => array(
				'name'       => __( 'Page', Advertikon_Notifications::LNS ),
				'input'      => $this->get_page_input(),
				'standalone' => true,
				'restrict'   => array(
					self::RESTRICT_EQUAL    => __( 'is', Advertikon_Notifications::LNS ),
					self::RESTRICT_NOT_EQUAL => __( 'not is', Advertikon_Notifications::LNS ),
				),
			),
			'customer' => array(
				'name'       => __( 'Customer', Advertikon_Notifications::LNS ),
				'input'      => $this->get_customer_input(),
				'standalone' => true,
				'restrict'   => array(
					self::RESTRICT_EQUAL    => __( 'is', Advertikon_Notifications::LNS ),
					self::RESTRICT_NOT_EQUAL => __( 'not is', Advertikon_Notifications::LNS ),
				),
			),
			
			'free_shipping' => array(
				'name'       => __( 'Insufficient amount for free shipping', Advertikon_Notifications::LNS ),
				'input'      => $this->get_shipping_input(),
				'standalone' => true,
				'restrict'   => array(
					self::RESTRICT_EQUAL    => __( 'Yes', Advertikon_Notifications::LNS ),
					self::RESTRICT_NOT_EQUAL => __( 'No', Advertikon_Notifications::LNS ),
				),
			),
		);
	}

	protected function get_page_input() {
		return Advertikon_Library_Renderer_Admin::select( array(
			'standalone' => true,
			'options'    => array(
				'cart'     => __( 'Cart', Advertikon_Notifications::LNS ),
				'checkout' => __( 'Checkout', Advertikon_Notifications::LNS ),
			),
		) );
	}

	protected function get_customer_input() {
		return Advertikon_Library_Renderer_Admin::select( array(
			'standalone' => true,
			'options'    => array(
				'loggedin' => __( 'Logged in', Advertikon_Notifications::LNS ),
				'guest'    => __( 'Guest', Advertikon_Notifications::LNS ),
			),
		) );
	}
	
	protected function get_shipping_input() {
		return Advertikon_Library_Renderer_Admin::input( array(
			'standalone'        => true,
			'custom_attributes' => array( 'disabled' => 'disabled' ),
			'value'             => ' ',
		) );
	}

	protected function filter_page( array $data ) {
		foreach( $data as $restrict => $values ) {
			foreach( $values as $value ) {
				$match = false;

				switch( $value ) {
					case 'shop':
						$match = is_shop();
						break;
					case 'product':
						$match = is_product();
						break;
					case 'cart':
						$match = is_cart();
						break;
					case 'account':
						$match = is_account_page();
						break;
					case 'category':
						$match = is_product_category();
						break;
					case 'checkout':
						$match = is_checkout();
						break;
					case 'payment_page':
						$match = is_checkout_pay_page();
						break;
					case 'view_order_page':
						$match = is_view_order_page();
						break;
					case 'edit_account':
						$match = is_edit_account_page();
						break;
				}

				if ( $restrict == self::RESTRICT_EQUAL ) {
					// do nothing

				} else if ( $restrict == self::RESTRICT_NOT_EQUAL ) {
					$match = !$match;

				} else {
					Advertikon::error( 'Page filter: unsupported restriction: ' . $restrict );
				}

				$step_result = $this->check_step( $match, self::MATCH_OR );

				if ( !is_null( $step_result ) ) {
					break;
				}
			}

			$result = $this->check_step( $step_result );

			if ( !is_null( $result ) ) {
				return $result;
			}
		}

		return $this->check_overall( $result );
	}
	
	protected function filter_customer( array $data ) {
		foreach( $data as $restrict => $values ) {
			foreach( $values as $value ) {
				$match = false;
				
				switch( $value ) {
					case 'loggedin':
						$match = is_user_logged_in();
						break;
					case 'guest':
						$match = !is_user_logged_in();
						break;
				}
				
				if ( $restrict == self::RESTRICT_EQUAL ) {
					
				} else if ( $restrict == self::RESTRICT_NOT_EQUAL ) {
					$match = !$match;
					
				} else {
					Advertikon::error( 'Customer filter: unsupported restriction: ' . $restrict );
				}
				
				$step_result = $this->check_step( $match, self::MATCH_OR );
				
				if ( !is_null( $step_result ) ) {
					break;
				}
			}
			
			$result = $this->check_step( $step_result );
			
			if ( !is_null( $result ) ) {
				return $result;
			}
		}
		
		return $this->check_overall( $result );
	}
	
	protected function filter_free_shipping( array $data ) {
		foreach( $data as $restrict => $values ) {
			foreach( $values as $value ) { // always one pass
				$match = $this->is_need_more_for_free_shipping();
				
				if ( $restrict == self::RESTRICT_EQUAL ) {
					if ( $match ) {
						return true;
					}
					
				} else if ( $restrict == self::RESTRICT_NOT_EQUAL ) {
					if ( !$match ) {
						return true;
					}
					
				} else {
					Advertikon::error( 'Customer filter: unsupported restriction: ' . $restrict );
				}
				
				return false;
			}
		}
	}

	/**
	 * Checks if free shipping is available.
	 *
	 * @return bool
	 */
	public function is_need_more_for_free_shipping() {
		foreach( ADK()->get_free_shipping() as $shipping ) {
			if ( $this->do_show_free_shipping( $shipping ) ) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Checks if to show notification for specific free shipping object
	 *
	 * @param array $free_shipping WC_Shipping_Free_Shipping
	 * @return bool
	 */
	public function do_show_free_shipping( WC_Shipping_Free_Shipping $free_shipping ) {
		$has_coupon		 = false;
		$has_met_min_amount = false;

		if ( in_array( $free_shipping->requires, array( 'coupon', 'either', 'both' ), true ) ) {
			$coupons = WC()->cart->get_coupons();

			if ( $coupons ) {
				foreach ( $coupons as $code => $coupon ) {
					if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
						$has_coupon = true;
						break;
					}
				}
			}
		}

		if ( in_array( $free_shipping->requires, array( 'min_amount', 'either', 'both' ), true ) ) {
			$total = WC()->cart->get_displayed_subtotal();
			
			if ( WC()->cart->display_prices_including_tax() ) {
				$total = round( $total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals() );
			} else {
				$total = round( $total - WC()->cart->get_discount_total(), wc_get_price_decimals() );
			}

			if ( $total >= $free_shipping->min_amount ) {
				$has_met_min_amount = true;
			}
		}
		
		switch ( $free_shipping->requires ) {
			case 'min_amount':
				$is_available = !$has_met_min_amount; // Show in case of insufficient amount
				break;
			case 'coupon':
				$is_available = false; // Skip
				break;
			case 'both':
				$is_available = !$has_met_min_amount && $has_coupon; // Insufficient amount + coupon
				break;
			case 'either':
				$is_available = !$has_met_min_amount && !$has_coupon; // Insufficient amount - coupon
				break;
			default:
				$is_available = false;
				break;
		}

		return $is_available;
	}
}