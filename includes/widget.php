<?php

/**
 * @package notifications
 * @author Advertikon
 */

class Advertikon_Notification_Includes_Widget {
	protected $template;
	protected $defaults = array();
	protected $section_defaults = array(
		'bg_color'    => '#ff0000',
		'font_height' => '14',
		'padding'     => 10,
		'text_color'  => '#000000',
		'text'        => '',
		'align'       => 'center',
		'valign'      => 'middle',
		'height'      => 'auto',
	);

	protected $button_defaults = array(
		'bg_color'      => 'blue',
		'font_height'   => '14',
		'padding'       => 10,
		'text_color'    => 'white',
		'text'          => 'Click Me!',
		'url'           => '',
		'border_width'  => 2,
		'border_radius' => 5,
		'border_color'  => 'white',
		'name'          => 'default',
	);

	protected $storage_dir;
	protected $data = array();
	protected $is_simple = false;

	/** $var Advertikon_Notification_Includes_Shortcode */
	protected $shortcode;

	/** @var Advertikon_Notification_Includes_Filter */
	protected $filter;

	public function __construct( Advertikon_Notification_Includes_Filter $filter ) {
		$this->defaults = array(
			'name'              => 'default',
			'border_color'      => '#00ff00',
			'border_radius'     => 5,
			'border_width'      => 1,
			'shadow_color'      => '#000000',
			'shadow_dispersion' => 0,
			'shadow_horizontal' => 0,
			'shadow_vertical'   => 0,
			'template'          => 'simple',
			'width'             => 800,
			'section'           => array(
				'content' => $this->section_defaults,
			),
			'filter'            => null,
		);

		$this->storage_dir  = plugin_dir_path( __DIR__ ) . 'storage/widgets/';
		$this->template_dir = plugin_dir_path( __DIR__ ) . 'storage/templates/';
		$this->button_dir   = plugin_dir_path( __DIR__ ) . 'storage/buttons/';

		if ( !is_a( $this, 'Advertikon_Notification_Includes_Widget_Extended' ) ) {
			$this->is_simple = true;

			try{
				$this->data = $this->load( 'default' );

			} catch ( Exception $e ) {}
		}

		$this->filter = $filter;
		$this->shortcode = class_exists( 'Advertikon_Notification_Includes_Shortcode_Extended' ) ?
			new Advertikon_Notification_Includes_Shortcode_Extended() : new Advertikon_Notification_Includes_Shortcode();

		// add_action( 'wp_footer', [ $this, 'render' ] );
		add_action( 'get_template_part_template-parts/header/header', array( $this, 'render' ) );
	}

	public function is_simple() {
		return $this->is_simple;
	}

	public function get_shortcode() {
		return $this->shortcode;
	}

	public function save( array $data ) {
		$params = array();
		$this->fill_with_defaults( $data, $params );

		if ( !$params['name'] ) {
			throw new Exception( __( 'Widget\'s name is missing', Advertikon_Notifications::LNS ) );
		}

		if ( !is_dir( $this->storage_dir ) ) {
			Advertikon::log( 'Creating widget storage dir...' );

			if( false === mkdir( $this->storage_dir, 0777, true ) ) {
				throw new Exception( __( 'Failed to create widget\'s storage folder', Advertikon_Notifications::LNS ) );
			}
		}
		
		$file_name = $this->storage_dir . $params['name'];
		$is_new = false;
		
		if ( file_exists( $file_name ) ) {
			if ( !is_writeable( $file_name ) ) {
				throw new Exception ( 'File ' . $file_name . ' is not writeable' );
			}

		} else {
			if ( !is_writeable( $this->storage_dir ) ) {
				throw new Exception ( 'Directory ' . $this->storage_dir . ' is not writeable' );
			}
			
			$is_new = true;
		}

		if ( false === file_put_contents( $file_name, wp_json_encode( $params ) ) ) {
			throw new Exception( __( 'Failed to save widget', Advertikon_Notifications::LNS ) );
		}
		
		if ( $is_new ) {
			chmod( $file_name, 0777 );
		}
	}

	protected function fill_with_defaults( $data, &$out, $def = null ) {
		$defaults = is_null( $def ) ? $this->defaults : $def;

		foreach( $defaults as $k => $v ) {
			if ( isset( $data[ $k ] ) ) {
				if ( is_array( $v ) ) {
					$out[ $k ] = array();
					$this->fill_with_defaults( $data[ $k ], $out[ $k ], $v );

				} else {
					$out[ $k ] = $data[ $k ];
				}

			} else {
				$out[ $k ] = $v;
			}
		}
	}

	public function save_button( array $data ) {
		$params = array();
		
		foreach( $this->button_defaults as $k => $v ) {
			if ( isset( $data[ $k ] ) ) {
				$params[ $k ] = $data[ $k ];

			} else {
				$params[ $k ] = $v;
			}
		}

		Advertikon::log( $params );

		if ( !$params['name'] ) {
			throw new Exception( __( 'Buttons\'s name is missing', Advertikon_Notifications::LNS ) );
		}

		if ( !is_dir( $this->button_dir ) ) {
			Advertikon::log( 'Creating widget storage dir...' );

			if( false === mkdir( $this->button_dir, 0777, true ) ) {
				throw new Exception( __( 'Failed to create widget\'s storage folder', Advertikon_Notifications::LNS ) );
			}
		}

		file_put_contents( $this->button_dir . $params['name'], wp_json_encode( $params ) );
	}

	public function load( $name ) {
		if ( !is_file( $this->storage_dir . $name ) ) {
			throw new Exception( __( 'Widget doesn\'t exist', Advertikon_Notifications::LNS ) );
		}

		$content = json_decode( file_get_contents( $this->storage_dir . $name ), true );

		if ( is_null( $content ) ) {
			throw new Exception( __( 'Failed to read widget\'s data', Advertikon_Notifications::LNS ) );
		}

		return $content;
	}

	public function load_button( $name ) {
		if ( !is_file( $this->button_dir . $name ) ) {
			throw new Exception( __( 'Button doesn\'t exist', Advertikon_Notifications::LNS ) . '(' . $name . ')' );
		}

		$content = json_decode( file_get_contents( $this->button_dir . $name ), true );

		if ( is_null( $content ) ) {
			throw new Exception( __( 'Failed to read button\'s data', Advertikon_Notifications::LNS ) );
		}

		return $content;
	}

	public function get_controls() {
		return array(
			array(
				'type' => 'title',
				'title' => __( 'Common appearance', Advertikon_Notifications::LNS ),
				'sort'  => 0,
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'template' ),
				'name'          => 'template',
				'type' 			=> 'adk_select',
				'title' 		=> __( 'Template', Advertikon_Notifications::LNS ),
				'options'       => $this->get_available_templates(),
				'default'		=> $this->get_default( 'template' ),
				'desc'			=> __( 'Defines widget structure', Advertikon_Notifications::LNS ),
				'desc_tip'		=> true,
				'sort'          => 20,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'width' ),
				'name'          => 'width',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Width', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> $this->get_default( 'width' ),
				'sort'          => 30,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'border_width' ),
				'name'          => 'border_width',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Border width', 'advertikon' ),
				'class'			=> 'adk-slider',
				'default'		=> $this->get_default( 'border_width' ),
				'sort'          => 40,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'border_color' ),
				'name'          => 'border_color',
				'type'			=> 'adk_color',
				'title' 		=> __( 'Border color', Advertikon_Notifications::LNS ),
				'default'		=> $this->get_default( 'border_color' ),
				'sort'          => 50,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'border_radius' ),
				'name'          => 'border_radius',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Border radius', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> $this->get_default( 'border_radius' ),
				'sort'          => 60,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'shadow_vertical' ),
				'name'          => 'shadow_vertical',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Vertical shadow', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> $this->get_default( 'shadow_vertical' ),
				'sort'          => 70,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'shadow_horizontal' ),
				'name'          => 'shadow_horizontal',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Horizontal shadow', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> $this->get_default( 'shadow_horizontal' ),
				'sort'          => 80,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'shadow_dispersion' ),
				'name'          => 'shadow_dispersion',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Shadow dispersion', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> $this->get_default( 'shadow_dispersion' ),
				'sort'          => 90,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'shadow color' ),
				'name'          => 'shadow_color',
				'type'			=> 'adk_color',
				'title' 		=> __( 'Shadow color', Advertikon_Notifications::LNS ),
				'default'		=> $this->get_default( 'shadow_color' ),
				'sort'          => 100,
				'class'         => 'adk-widget-control',
			),
			array(
				'type' => 'sectionend',
				'sort' => 1000,
			),
		);
	}

	public function get_default( $name ) {
		$parts = explode( '/', $name );
		$pointer = $this->data ?: $this->defaults; // use "default" widget as defaults in simple mode 

		while( $parts ) {
			$current = array_shift( $parts );

			if ( !isset( $pointer[ $current ] ) ) {
				Advertikon::error( 'Missing default widget value: ' . $name );
				return '';
			}

			$pointer = $pointer[ $current ];
		}

		return is_array( $pointer ) ? 'Array' : $pointer;
	}

	public function get_button_default( $name ) {
		if ( !isset( $this->button_defaults[ $name ] ) ) {
				Advertikon::error( 'Missing default button value: ' . $name );
				return '';
		}

		return $this->button_defaults[ $name ];
	}

	public function render() {
		// if ( !is_woocommerce() ) {
		// 	return;
		// }

		foreach( $this->load_all() as $widget ) {
			if ( empty( $widget['filter'] ) || !is_array( $widget['filter'] ) ||
				$this->filter->filter( $widget['filter'], $widget['name'] ) ) {

				$this->render_widget( $widget );
			}
		}
	}

	public function get_button_list() {
		$ret = array();

		if ( !is_dir( $this->button_dir ) ) {
			return $ret;
		}

		foreach( scandir( $this->button_dir ) as $item ) {
			if ( '.' === $item[ 0 ] || !is_file( $this->button_dir . $item ) ) {
				continue;
			}

			$button = $this->load_button( $item );
			$ret[ $item ] = $button['name'];
		}

		if ( $ret ) {
			array_unshift( $ret, __( 'Select a button', Advertikon_Notifications::LNS ) );
		}

		return $ret;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////

	protected function render_widget( array $widget ) {
		$template_path = $this->template_dir . ( isset( $widget['template'] ) ? $widget['template'] : 'simple' ) . '.php';

		if ( !file_exists( $template_path) ) {
			Advertikon::error( 'Template ' . $template_path . ' doesn\'t exist' );
			return;
		}

		extract( $widget );
		$id = uniqid();

		foreach( $section as $section_name => $section_data ) {
			$$section_name = $this->render_section( $section_data, $section_name );
		}

		require( $template_path );
	}

	protected function render_section( array $data, $name ) {
		$height = (int)( isset( $data['height'] ) ? $data['height'] : $this->get_default( "section/$name/height" ) );

		return sprintf(
			'<td style="color: %s; font-size: %spx; background-color: %s; padding: %spx; height: %s" align="%s" valign="%s">%s</td>',
			isset( $data['text_color'] )  ? $data['text_color']  : $this->get_default( "section/$name/text_color" ),
			isset( $data['font_height'] ) ? $data['font_height'] : $this->get_default( "section/$name/font_height" ),
			isset( $data['bg_color'] )    ? $data['bg_color']    : $this->get_default( "section/$name/bg_color" ),
			isset( $data['padding'] )     ? $data['padding']     : $this->get_default( "section/$name/padding" ),
			$height > 0                   ? $height . 'px'       : 'auto',
			isset( $data['align'] )       ? $data['align']       : $this->get_default( "section/$name/align" ),
			isset( $data['valign'] )      ? $data['valign']      : $this->get_default( "section/$name/valign" ),
			isset( $data['text'] )        ? do_shortcode( $data['text'] ) : $this->get_default( "section/$name/text" )
		);
	}

	protected function get_available_templates() {
		return array(
			'simple' => __( 'Simple', Advertikon_Notifications::LNS ),
		);
	}

	protected function get_list() {
		$ret = array();

		if ( !is_dir( $this->storage_dir ) ) {
			return $ret;
		}

		foreach( scandir( $this->storage_dir ) as $item ) {
			if ( '.' === $item[ 0 ] || !is_file( $this->storage_dir . $item ) ) {
				continue;
			}

			$widget = $this->load( $item );
			$ret[ $item ] = $widget['name'];
		}

		if ( $ret ) {
			array_unshift( $ret, __( 'Select a widget', Advertikon_Notifications::LNS ) );
		}

		return $ret;
	}

	protected function sanitaze_name( $name ) {
		return strtolower( preg_replace( '/[^a-zA-Z0-9_.-]/', '', $name ) );
	}

	protected function load_all() {
		$ret = array();

		if ( !is_dir( $this->storage_dir ) ) {
			return $ret;
		}

		foreach( scandir( $this->storage_dir ) as $item ) {
			if ( '.' === $item[ 0 ] || !is_file( $this->storage_dir . $item ) ) {
				continue;
			}

			$widget = $this->load( $item );
			$ret[ $item ] = $widget;
		}

		return $ret;
	}
	
}