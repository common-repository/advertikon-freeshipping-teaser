<?php

/**
 * @package notifications
 * @author Advertikon
 */

class Advertikon_Notification_Includes_Template {
	
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
				'default'		=> '',
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
				'default'		=> '800',
				'sort'          => 30,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'border_width' ),
				'name'          => 'border_width',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Border width', 'advertikon' ),
				'class'			=> 'adk-slider',
				'default'		=> 1,
				'sort'          => 40,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'border_color' ),
				'name'          => 'border_color',
				'type'			=> 'adk_color',
				'title' 		=> __( 'Border color', Advertikon_Notifications::LNS ),
				'default'		=> '#000',
				'sort'          => 50,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'border_radius' ),
				'name'          => 'border_radius',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Border radius', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> 1,
				'sort'          => 60,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'shadow_vertical' ),
				'name'          => 'shadow_vertical',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Vertical shadow', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> 0,
				'sort'          => 70,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'shadow_horizontal' ),
				'name'          => 'shadow_horizontal',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Horizontal shadow', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> 0,
				'sort'          => 80,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'shadow_dispersion' ),
				'name'          => 'shadow_dispersion',
				'type'			=> 'adk_number',
				'title' 		=> __( 'Shadow dispersion', Advertikon_Notifications::LNS ),
				'class'			=> 'adk-slider',
				'default'		=> 0,
				'sort'          => 90,
				'class'         => 'adk-widget-control',
			),
			array(
				'id'			=> Advertikon_Notifications::prefix( 'shadow color' ),
				'name'          => 'shadow_color',
				'type'			=> 'adk_color',
				'title' 		=> __( 'Shadow color', Advertikon_Notifications::LNS ),
				'default'		=> '#000',
				'sort'          => 100,
				'class'         => 'adk-widget-control',
			),
			array(
				'type' => 'sectionend',
				'sort' => 1000,
			),
		);
	}

	protected function get_available_templates() {
		return array(
			'simple' => __( 'Simple', Advertikon_Notifications::LNS ),
		);
	}
}