<?php
namespace Essential_Addons_Elementor\Extensions;

if (!defined('ABSPATH')) {
    exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Scheme_Typography as Scheme_Typography;

class Table_of_Content
{

    public function __construct()
    {
        add_action('elementor/documents/register_controls', [$this, 'register_controls'], 10);
    }

    public function register_controls($element)
    {
        $global_settings = get_option('eael_global_settings');

        $element->start_controls_section(
            'eael_ext_table_of_content_section',
            [
                'label' => esc_html__('EA Table of Content', 'essential-addons-for-elementor-lite'),
                'tab' => Controls_Manager::TAB_SETTINGS,
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content',
            [
                'label' => __('Enable Table of Content', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => __('Yes', 'essential-addons-for-elementor-lite'),
                'label_off' => __('No', 'essential-addons-for-elementor-lite'),
                'return_value' => 'yes',
            ]
        );

        $element->add_control(
            'eael_ext_toc_has_global',
            [
                'type' => Controls_Manager::HIDDEN,
                'default' => isset($global_settings['eael_ext_table_of_content']['enabled']) ? true : false,
            ]
        );

        if (isset($global_settings['eael_ext_table_of_content']['enabled']) && ($global_settings['eael_ext_table_of_content']['enabled'] == true) && get_the_ID() != $global_settings['eael_ext_table_of_content']['post_id'] && get_post_status($global_settings['eael_ext_table_of_content']['post_id']) == 'publish') {
            $element->add_control(
                'eael_ext_toc_global_warning_text',
                [
                    'type' => Controls_Manager::RAW_HTML,
                    'raw' => __('You can modify the Global Table of content by <strong><a href="' . get_bloginfo('url') . '/wp-admin/post.php?post=' . $global_settings['eael_ext_table_of_content']['post_id'] . '&action=elementor">Clicking Here</a></strong>', 'essential-addons-for-elementor-lite'),
                    'content_classes' => 'eael-warning',
                    'condition' => [
                        'eael_ext_table_of_content' => 'yes',
                    ],
                ]
            );
        } else {
            $element->add_control(
                'eael_ext_toc_global',
                [
                    'label' => __('Enable Table of Content Globally', 'essential-addons-for-elementor-lite'),
                    'description' => __('Enabling this option will effect on entire site.', 'essential-addons-for-elementor-lite'),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => 'no',
                    'label_on' => __('Yes', 'essential-addons-for-elementor-lite'),
                    'label_off' => __('No', 'essential-addons-for-elementor-lite'),
                    'return_value' => 'yes',
                    'condition' => [
                        'eael_ext_table_of_content' => 'yes',
                    ],
                ]
            );

            $element->add_control(
                'eael_ext_toc_global_display_condition',
                [
                    'label' => __('Display On', 'essential-addons-for-elementor-lite'),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'all',
                    'options' => [
                        'posts' => __('All Posts', 'essential-addons-for-elementor-lite'),
                        'pages' => __('All Pages', 'essential-addons-for-elementor-lite'),
                        'all' => __('All Posts & Pages', 'essential-addons-for-elementor-lite'),
                    ],
                    'condition' => [
                        'eael_ext_table_of_content' => 'yes',
                        'eael_ext_toc_global' => 'yes',
                    ],
                ]
            );
        }

        $element->add_control(
            'eael_ext_toc_title',
            [
                'label' => __('Title', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Table of Content', 'essential-addons-for-elementor-lite'),
                'label_block' => false,
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_supported_heading_tag',
            [
                'label' => __('Supported Heading Tag', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'default' => [
                    'h2',
                    'h3',
                    'h4',
                    'h5',
                    'h6',
                ],
                'options' => [
                    'h1' => __('H1', 'essential-addons-for-elementor-lite'),
                    'h2' => __('H2', 'essential-addons-for-elementor-lite'),
                    'h3' => __('H3', 'essential-addons-for-elementor-lite'),
                    'h4' => __('H4', 'essential-addons-for-elementor-lite'),
                    'h5' => __('H5', 'essential-addons-for-elementor-lite'),
                    'h6' => __('H6', 'essential-addons-for-elementor-lite'),
                ],
                'render_type' => 'none',
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_collapse_sub_heading',
            [
                'label' => __('Keep Sub Heading Expended', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Yes', 'essential-addons-for-elementor-lite'),
                'label_off' => __('No', 'essential-addons-for-elementor-lite'),
                'return_value' => 'yes',
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_word_wrap',
            [
                'label' => __('Stop Word Wrap', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => __('Yes', 'essential-addons-for-elementor-lite'),
                'label_off' => __('No', 'essential-addons-for-elementor-lite'),
                'return_value' => 'yes',
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_auto_collapse',
            [
                'label' => __('TOC Auto Collapse', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Yes', 'essential-addons-for-elementor-lite'),
                'label_off' => __('No', 'essential-addons-for-elementor-lite'),
                'return_value' => 'yes',
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

	    $element->add_control(
		    'eael_ext_toc_sticky_scroll',
		    [
			    'label' => __('Sticky Scroll Effect', 'essential-addons-for-elementor-lite'),
			    'type' => Controls_Manager::SLIDER,
			    'size_units' => ['px'],
			    'range' => [
				    'px' => [
					    'min' => 5,
					    'max' => 2000,
					    'step' => 10,
				    ],
			    ],
			    'default' => [
				    'unit' => 'px',
				    'size' => 200,
			    ],
			    'condition' => [
				    'eael_ext_table_of_content' => 'yes',
			    ],
		    ]
	    );

        $element->add_control(
            'eael_ext_toc_sticky_offset',
            [
                'label' => __('Sticky Top Offset', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 5,
                        'max' => 2000,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 200,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc.eael-sticky' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

	    $element->add_control(
		    'eael_ext_toc_sticky_z_index',
		    [
			    'label' => __('Z Index', 'essential-addons-for-elementor-lite'),
			    'type' => Controls_Manager::SLIDER,
			    'size_units' => ['px'],
			    'range' => [
				    'px' => [
					    'min' => 0,
					    'max' => 9999,
					    'step' => 10,
				    ],
			    ],
			    'default' => [
				    'unit' => 'px',
				    'size' => 9999,
			    ],
			    'selectors' => [
				    '{{WRAPPER}} .eael-toc' => 'z-index: {{SIZE}}',
			    ],
			    'condition' => [
				    'eael_ext_table_of_content' => 'yes',
			    ],
		    ]
	    );

        $element->add_control(
            'eael_ext_toc_ad_warning_text',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => __('Need more information about TOC <strong><a href="https://essential-addons.com/elementor/docs/table-of-content/" class="eael-btn" target="_blank">Visit documentation</a></strong>', 'essential-addons-for-elementor-lite'),
                'content_classes' => 'eael-warning',
                'separator' => 'before',
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->end_controls_section();

        $element->start_controls_section(
            'eael_ext_toc_main',
            [
                'label' => esc_html__('EA TOC', 'essential-addons-for-elementor-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_position',
            [
                'label' => __('Position', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'label_block' => false,
                'options' => [
                    'left' => __('Left', 'essential-addons-for-elementor-lite'),
                    'right' => __('Right', 'essential-addons-for-elementor-lite'),
                ],
                'separator' => 'before',
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_list_icon',
            [
                'label' => __('List Icon', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'bullet',
                'label_block' => false,
                'options' => [
                    'bullet' => __('Bullet', 'essential-addons-for-elementor-lite'),
                    'number' => __('Number', 'essential-addons-for-elementor-lite'),
                ],
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_box_list_bullet_size',
            [
                'label' => __('Bullet Size', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body ul.eael-toc-list.eael-toc-bullet li:before' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_ext_toc_list_icon' => 'bullet',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_box_list_top_position',
            [
                'label' => __('Top Position', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => -50,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => -2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body ul.eael-toc-list.eael-toc-bullet li:before' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_ext_toc_list_icon' => 'bullet',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'eael_ext_toc_border',
                'label' => __('Border', 'essential-addons-for-elementor-lite'),
                'selector' => '{{WRAPPER}} .eael-toc,{{WRAPPER}} button.eael-toc-button',
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'eael_ext_toc_table_box_shadow',
                'label' => __('Box Shadow', 'essential-addons-for-elementor-lite'),
                'selector' => '{{WRAPPER}} .eael-toc:not(.collapsed)',
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_box_border_radius',
            [
                'label' => __('Border Radius', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc:not(.eael-toc-right)' => 'border-top-right-radius: {{SIZE}}{{UNIT}}; border-bottom-right-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eael-toc:not(.eael-toc-right) .eael-toc-header' => 'border-top-right-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eael-toc:not(.eael-toc-right) .eael-toc-body' => 'border-bottom-right-radius: {{SIZE}}{{UNIT}};',

                    '{{WRAPPER}} .eael-toc.eael-toc-right' => 'border-top-left-radius: {{SIZE}}{{UNIT}}; border-bottom-left-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eael-toc.eael-toc-right .eael-toc-header' => 'border-top-left-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eael-toc.eael-toc-right .eael-toc-body' => 'border-bottom-left-radius: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->end_controls_section();

        $element->start_controls_section(
            'eael_ext_table_of_content_header_style',
            [
                'label' => esc_html__('EA TOC Header', 'essential-addons-for-elementor-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_header_bg',
            [
                'label' => __('Background Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ff7d50',
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-header' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc.collapsed .eael-toc-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_header_text_color',
            [
                'label' => __('Text Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-header .eael-toc-title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc.collapsed .eael-toc-button' => 'color: {{VALUE}}',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'eael_ext_table_of_content_header_typography',
                'selector' => '{{WRAPPER}} .eael-toc-header .eael-toc-title,{{WRAPPER}} .eael-toc.collapsed .eael-toc-button',
                'scheme' => Scheme_Typography::TYPOGRAPHY_2,
            ]
        );

        $element->add_control(
            'eael_ext_toc_header_padding',
            [
                'label' => esc_html__('Padding', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_header_collapse_close_button',
            [
                'label' => __('Collapse', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_header_icon',
            [
                'label' => __('Icon', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::ICONS,
                'label_block' => true,
                'default' => [
                    'value' => 'fas fa-list',
                    'library' => 'fa-solid',
                ],
                'fa4compatibility' => 'icon',
            ]
        );

        $element->add_control(
            'eael_ext_toc_close_button_text_style',
            [
                'label' => __('Text Orientation', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'top_to_bottom',
                'options' => [
                    'top_to_bottom' => __('Top to Bottom', 'essential-addons-for-elementor-lite'),
                    'bottom_to_top' => __('Bottom to Top', 'essential-addons-for-elementor-lite'),
                ],
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_close_button',
            [
                'label' => __('Close Button', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_close_button_bg',
            [
                'label' => __('Background Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-close' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_close_button_text_color',
            [
                'label' => __('Close Button Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ff7d50',
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-close' => 'color: {{VALUE}}',
                ],
            ]
        );

        $element->end_controls_section();

        $element->start_controls_section(
            'eael_ext_table_of_content_list_style_section',
            [
                'label' => esc_html__('EA TOC Body', 'essential-addons-for-elementor-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_body_bg',
            [
                'label' => __('Background Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff6f3',
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body' => 'background-color: {{VALUE}}',
                ],

            ]
        );

        $element->add_control(
            'eael_ext_toc_body_padding',
            [
                'label' => esc_html__('Padding', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_list_style_separator',
            [
                'label' => __('List', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_list_style',
            [
                'label' => __('Indicator Style', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'essential-addons-for-elementor-lite'),
                    'arrow' => __('Arrow', 'essential-addons-for-elementor-lite'),
                    'bar' => __('Bar', 'essential-addons-for-elementor-lite'),
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_indicator_size',
            [
                'label' => __('Indicator Size', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-list-bar li.eael-highlight-active > a:after' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_ext_table_of_content_list_style' => 'bar',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_indicator_position',
            [
                'label' => __('Indicator Position', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => -100,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-list-arrow li.eael-highlight-active > a:before' => 'margin-top: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-list-bar li.eael-highlight-active > a:after' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_ext_table_of_content_list_style!' => 'none',
                ],
            ]
        );

        $element->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'eael_ext_table_of_content_list_typography_normal',
                'selector' => '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list',
                'scheme' => Scheme_Typography::TYPOGRAPHY_2,
            ]
        );

        $element->start_controls_tabs('ea_toc_list_style');

        $element->start_controls_tab('normal',
            [
                'label' => __('Normal', 'essential-addons-for-elementor-lite'),
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_list_text_color',
            [
                'label' => __('Text Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#707070',
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-number li:before' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-bullet li:before' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $element->end_controls_tab();

        $element->start_controls_tab('hover',
            [
                'label' => __('Hover', 'essential-addons-for-elementor-lite'),
            ]
        );

        $element->add_control(
            'eael_ext_table_of_list_hover_color',
            [
                'label' => __('Text Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ff7d50',
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-number li:hover:before' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-bullet li:hover:before' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li:hover > a' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li:hover > a:before' => 'border-bottom-color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li:hover > a:after' => 'background-color: {{VALUE}}',
                ],

            ]
        );

        $element->end_controls_tab(); // hover

        $element->start_controls_tab('active',
            [
                'label' => __('Active', 'essential-addons-for-elementor-lite'),
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_list_text_color_active',
            [
                'label' => __('Text Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ff7d50',
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li.eael-highlight-active' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-number li.eael-highlight-active:before' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-bullet li.eael-highlight-active:before' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li.eael-highlight-active > a' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li.eael-highlight-active > a:before' => 'border-bottom-color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li.eael-highlight-active > a:after' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li.eael-highlight-parent' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-number li.eael-highlight-parent:before' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list.eael-toc-bullet li.eael-highlight-parent:before' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li.eael-highlight-parent > a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $element->end_controls_tab(); // active
        $element->end_controls_tabs();

        $element->add_control(
            'eael_ext_toc_top_level_space',
            [
                'label' => __('Top Level Space', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_toc_subitem_level_space',
            [
                'label' => __('Sub Item Space', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list li ul li' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_ext_table_of_content' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_list_separator',
            [
                'label' => __('Separator', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_list_separator_style',
            [
                'label' => __('Style', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'dashed',
                'options' => [
                    'solid' => __('Solid', 'essential-addons-for-elementor-lite'),
                    'dashed' => __('Dashed', 'essential-addons-for-elementor-lite'),
                    'dotted' => __('Dotted', 'essential-addons-for-elementor-lite'),
                    'none' => __('None', 'essential-addons-for-elementor-lite'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list > li:not(:last-child)' => 'border-bottom: 0.5px {{VALUE}}',
                ],
            ]
        );

        $element->add_control(
            'eael_ext_table_of_content_list_separator_color',
            [
                'label' => __('Color', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eael-toc .eael-toc-body .eael-toc-list > li:not(:last-child)' => 'border-bottom-color: {{VALUE}}',
                ],
                'default' => '#c6c4cf',
                'condition' => [
                    'eael_ext_table_of_content_list_separator_style!' => 'none',
                ],
            ]
        );

        $element->end_controls_section();
    }
}
