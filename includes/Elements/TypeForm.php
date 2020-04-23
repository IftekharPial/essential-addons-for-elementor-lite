<?php

namespace Essential_Addons_Elementor\Elements;

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

use \Elementor\Controls_Manager as Controls_Manager;
use \Elementor\Group_Control_Border as Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow as Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography as Group_Control_Typography;
use \Elementor\Scheme_Typography as Scheme_Typography;
use \Elementor\Widget_Base as Widget_Base;
use \Elementor\Group_Control_Background as Group_Control_Background;
use \Elementor\Scheme_Color;

class TypeForm extends Widget_Base {

    private $form_list = [];

    public function __construct ($data = [], $args = null) {
        parent::__construct($data, $args);
    }

    public function get_name () {
        return 'eael-typeform';
    }

    public function get_title () {
        return __('TypeForm', 'essential-addons-for-elementor-lite');
    }

    public function get_categories () {
        return ['essential-addons-elementor'];
    }

    public function get_icon () {
        return 'eaicon-fluent-forms';
    }

    public function get_keywords () {
        return [
            'ea contact form',
            'ea typeform',
            'ea type form',
            'ea type forms',
            'contact form',
            'form styler',
            'elementor form',
            'feedback',
            'typeform',
            'ea',
            'essential addons'
        ];
    }

    public function get_custom_help_url () {
        return 'https://essential-addons.com/elementor/docs/type-form/';
    }

    private function get_personal_token () {
        return get_option('eael_save_typeform_personal_token');
    }

    public function get_form_list () {

        $token = $this->get_personal_token();
        $key = 'eael_type_form_data';
        $form_arr = get_transient($key);
        if (empty($form_arr)) {
            $response = wp_remote_get(
                'https://api.typeform.com/forms',
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                    ]
                ]
            );

            if (isset($response['response']['code']) && $response['response']['code'] == 200) {
                $data = json_decode(wp_remote_retrieve_body($response));
                if (isset($data->items)) {
                    $form_arr = $data->items;
                    set_transient($key, $form_arr, 1 * HOUR_IN_SECONDS);
                }
            }
        }
        $this->form_list[''] = __('Select Form', 'essential-addons-for-elementor-lite');
        foreach ($form_arr as $item) {
            $this->form_list[$item->_links->display] = $item->title;
        }
        return $this->form_list;
    }

    private function no_token_set () {
        $this->start_controls_section(
            'eael_global_warning',
            [
                'label' => __('Warning!', 'essential-addons-for-elementor-lite'),
            ]
        );

        $this->add_control(
            'eael_global_warning_text',
            [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => __('Whoops! It\' seems like you didn\'t set TypeForm personal token. You can set from 
                                    Essential Addons &gt; Elements &gt; TypeForm (Settings)',
                    'essential-addons-for-elementor-lite'),
                'content_classes' => 'eael-warning',
            ]
        );

        $this->end_controls_section();
    }

    protected function _register_controls () {

        if ($this->get_personal_token() == '') {
            $this->no_token_set();
            return;
        }

        $this->start_controls_section(
            'section_info_box',
            [
                'label' => __('TypeForm', 'essential-addons-for-elementor-lite'),
            ]
        );
        $this->add_control(
            'eael_typeform_list',
            [
                'label'   => __('TypeForm', 'essential-addons-for-elementor-lite'),
                'type'    => Controls_Manager::SELECT,
                'default' => '',
                'label_block' => true,
                'options' => $this->get_form_list()
            ]
        );
        $this->add_control(
            'eael_typeform_hideheaders',
            [
                'label'        => __('Hide Headers', 'essential-addons-for-elementor-lite'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'no',
                'return_value' => 'yes',
            ]
        );
        $this->add_control(
            'eael_typeform_hidefooter',
            [
                'label'        => __('Hide Footer', 'essential-addons-for-elementor-lite'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'no',
                'return_value' => 'yes',
            ]
        );
        $this->add_control(
            'eael_typeform_opacity',
            [
                'label'      => __('Opacity', 'essential-addons-for-elementor-lite'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 100
                    ]
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 0,
                ],
            ]
        );
        $this->end_controls_section();

        /*-----------------------------------------------------------------------------------*/
        /*    Style Tab
        /*-----------------------------------------------------------------------------------*/

        /**
         * Style Tab: Form Container
         * -------------------------------------------------
         */
        $this->start_controls_section(
            'section_container_style',
            [
                'label' => __('Form Container', 'essential-addons-for-elementor-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'eael_typeform_background',
            [
                'label' => esc_html__('Form Background Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eael-typeform' => 'background: {{VALUE}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'eael_typeform_alignment',
            [
                'label' => esc_html__('Form Alignment', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::CHOOSE,
                'label_block' => true,
                'options' => [
                    'default' => [
                        'title' => __('Default', 'essential-addons-for-elementor-lite'),
                        'icon' => 'fa fa-ban',
                    ],
                    'left' => [
                        'title' => esc_html__('Left', 'essential-addons-for-elementor-lite'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'essential-addons-for-elementor-lite'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'essential-addons-for-elementor-lite'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'default',
            ]
        );

        $this->add_responsive_control(
            'eael_typeform_max_width',
            [
                'label' => esc_html__('Form Max Width', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 1500,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-typeform' => 'width: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'eael_typeform_margin',
            [
                'label' => esc_html__('Form Margin', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eael-typeform' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'eael_typeform_padding',
            [
                'label' => esc_html__('Form Padding', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eael-typeform' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'eael_type_border_radius',
            [
                'label' => esc_html__('Border Radius', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eael-typeform' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'eael_type_border',
                'selector' => '{{WRAPPER}} .eael-typeform',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'eael_typeform_box_shadow',
                'selector' => '{{WRAPPER}} .eael-typeform',
            ]
        );

        $this->end_controls_section();

        /**
         * Style Tab: Labels
         * -------------------------------------------------
         */
        $this->start_controls_section(
            'section_label_style',
            [
                'label' => __('Labels', 'essential-addons-for-elementor-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'eael_typeform_text_color_lable',
            [
                'label' => __('Text Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eael-contact-form.eael-fluent-form-wrapper .ff-el-group label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'eael_typeform_typography_label',
                'label' => __('Typography', 'essential-addons-for-elementor-lite'),
                'selector' => '{{WRAPPER}} .eael-contact-form.eael-fluent-form-wrapper .ff-el-group label',
            ]
        );

        $this->end_controls_section();
    }

    protected function render () {

        $settings = $this->get_settings_for_display();
        if ($settings['eael_typeform_list'] == '') {
            return;
        }
        $id = 'eael-type-form-'.$this->get_id();
        $this->add_render_attribute(
            'eael_typeform_wrapper',
            [
                'id'    => $id,
                'class' => [
                    'eael-typeform',
                    'clearfix',
                    'fs_wp_sidebar',
                    'fsBody',
                    'eael-contact-form'
                ]
            ]
        );
        $alignment = $settings['eael_typeform_alignment'];
        $this->add_render_attribute('eael_typeform_wrapper', 'class', 'eael-typeform-align-'.$alignment);
        $data = [
            'url'         => esc_url($settings['eael_typeform_list']),
            'hideFooter'  => ($settings['eael_typeform_hidefooter'] == 'yes'),
            'hideHeaders' => ($settings['eael_typeform_hideheaders'] == 'yes'),
            'opacity'     => $settings['eael_typeform_opacity']['size']
        ];
        echo '<div data-typeform="'.htmlspecialchars(json_encode($data), ENT_QUOTES,
                'UTF-8').'" '.$this->get_render_attribute_string('eael_typeform_wrapper').'></div>';
    }

}
