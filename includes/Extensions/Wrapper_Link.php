<?php

namespace Essential_Addons_Elementor\Extensions;

use Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wrapper_Link {

	/**
	 * Initialize hooks
	 */
	public function __construct() {
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/frontend/before_render', [ $this, 'before_render' ], 1 );
		add_action( 'elementor/frontend/after_render', [ $this, 'after_render' ], 1 );
	}

	public function register_controls( $element ) {
		$element->start_controls_section(
			'eael_wrapper_link_section',
			[
				'label' => __( '<i class="eaicon-logo"></i> Wrapper Link', 'essential-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_ADVANCED
			]
		);

		$element->add_control(
			'eael_wrapper_link_switch',
			[
				'label' => __( 'Enable Wrapper Link', 'essential-addons-for-elementor-lite' ),
				'type'  => Controls_Manager::SWITCHER
			]
		);

		$element->add_control(
			'eael_wrapper_link',
			[
				'label'     => __( 'Link', 'essential-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => [
					'active' => true,
				],
				'condition' => [
					'eael_wrapper_link_switch!' => ''
				]
			]
		);

		$element->end_controls_section();
	}

	public function before_render( $element ) {
		$wrapper_link_settings = $element->get_settings_for_display( 'eael_wrapper_link' );
		
		if ( "yes" === $element->get_settings_for_display( 'eael_wrapper_link_switch' ) && ! empty( $wrapper_link_settings['url'] ) ) {
			$element->add_link_attributes( 'eael_wrapper_link', $wrapper_link_settings );
			echo "<a "; $element->print_render_attribute_string( 'eael_wrapper_link' ); echo ">";
		}
	}

	public function after_render( $element ){
		$wrapper_link_settings = $element->get_settings_for_display( 'eael_wrapper_link' );

		if ( "yes" === $element->get_settings_for_display( 'eael_wrapper_link_switch' ) && ! empty( $wrapper_link_settings['url'] ) ) {
			echo "</a>";
		}
	}

}
