<?php
/**
 * Topic Notes (Single Topic Notes) Elementor Widget.
 *
 * @package PracticeProblems
 */

namespace PracticeProblems\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Topic_Notes_Widget
 */
class Topic_Notes_Widget extends Widget_Base {

	public function get_name() {
		return 'topic_notes';
	}

	public function get_title() {
		return esc_html__( 'Topic Notes', 'practice-problems-el' );
	}

	public function get_icon() {
		return 'eicon-document-file';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_keywords() {
		return [ 'topic', 'notes', 'math', 'single', 'formula', 'lesson', 'study', 'toc' ];
	}

	public function get_script_depends() {
		return [ 'topic-notes-frontend' ];
	}

	public function get_style_depends() {
		return [ 'topic-notes-frontend' ];
	}

	/**
	 * Helper: Get list of published math notes for manual selection.
	 */
	private function get_math_notes_options() {
		$options = [ '' => esc_html__( '— Select a Math Note —', 'practice-problems-el' ) ];
		$posts = get_posts( [
			'post_type'      => 'math_note',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $p ) {
				$options[ $p->ID ] = $p->post_title . ' (#' . $p->ID . ')';
			}
		}
		return $options;
	}

	protected function register_controls() {

		/* =========================================================
		   CONTENT TAB — Query & Data Source
		========================================================= */
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Note Query & Data Source', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'data_source',
			[
				'label'       => esc_html__( 'Source Mode', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'dynamic',
				'options'     => [
					'dynamic' => esc_html__( 'Dynamic (Current Note / Single Template)', 'practice-problems-el' ),
					'manual'  => esc_html__( 'Manual (Select Specific Note)', 'practice-problems-el' ),
				],
				'description' => esc_html__( 'Use Dynamic mode on single note templates, or Manual mode to place any specific note on a page.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'selected_note_id',
			[
				'label'       => esc_html__( 'Select Math Note', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_math_notes_options(),
				'condition'   => [ 'data_source' => 'manual' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Header & Breadcrumbs
		========================================================= */
		$this->start_controls_section(
			'section_header',
			[
				'label' => esc_html__( 'Header & Breadcrumbs', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_breadcrumb',
			[
				'label'        => esc_html__( 'Show Breadcrumb', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'breadcrumb_home_label',
			[
				'label'     => esc_html__( 'Home / Root Label', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Notes', 'practice-problems-el' ),
				'condition' => [ 'show_breadcrumb' => 'yes' ],
			]
		);

		$this->add_control(
			'breadcrumb_separator',
			[
				'label'     => esc_html__( 'Separator', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '/',
				'condition' => [ 'show_breadcrumb' => 'yes' ],
			]
		);

		$this->add_control(
			'show_chapter_eyebrow',
			[
				'label'        => esc_html__( 'Show Chapter Eyebrow', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'show_summary',
			[
				'label'        => esc_html__( 'Show Summary Subtitle', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Table of Contents & Sidebar
		========================================================= */
		$this->start_controls_section(
			'section_toc',
			[
				'label' => esc_html__( 'Table of Contents (TOC)', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_toc',
			[
				'label'        => esc_html__( 'Enable Table of Contents', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'toc_title',
			[
				'label'     => esc_html__( 'TOC Title', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'On this page', 'practice-problems-el' ),
				'condition' => [ 'show_toc' => 'yes' ],
			]
		);

		$this->add_control(
			'enable_sticky_toc',
			[
				'label'        => esc_html__( 'Sticky Sidebar TOC', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [ 'show_toc' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Reading Progress Bar
		========================================================= */
		$this->start_controls_section(
			'section_progress_bar',
			[
				'label' => esc_html__( 'Reading Progress Bar', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_progress_bar',
			[
				'label'        => esc_html__( 'Enable Progress Bar', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Navigation & PDF
		========================================================= */
		$this->start_controls_section(
			'section_nav_pdf',
			[
				'label' => esc_html__( 'Navigation & PDF Download', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_navigation',
			[
				'label'        => esc_html__( 'Show Previous / Next Topics', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'prev_topic_label',
			[
				'label'     => esc_html__( 'Previous Topic Label', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Previous topic', 'practice-problems-el' ),
				'condition' => [ 'enable_navigation' => 'yes' ],
			]
		);

		$this->add_control(
			'next_topic_label',
			[
				'label'     => esc_html__( 'Next Topic Label', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Next topic', 'practice-problems-el' ),
				'condition' => [ 'enable_navigation' => 'yes' ],
			]
		);

		$this->add_control(
			'enable_floating_pdf',
			[
				'label'        => esc_html__( 'Enable Floating PDF Button', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Container & Layout
		========================================================= */
		/* =========================================================
		   STYLE TAB — 1. Container & Layout
		========================================================= */
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container & Layout', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range'      => [ 'px' => [ 'min' => 600, 'max' => 1920 ] ],
				'selectors'  => [
					'{{WRAPPER}} .tn-widget-container' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Container Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-widget-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'sidebar_width',
			[
				'label'      => esc_html__( 'Sidebar Width', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 180, 'max' => 450 ] ],
				'default'    => [ 'size' => 260, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-layout-grid' => 'grid-template-columns: {{SIZE}}{{UNIT}} 1fr;',
				],
			]
		);

		$this->add_responsive_control(
			'grid_gap',
			[
				'label'      => esc_html__( 'Column Gap', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 10, 'max' => 100 ] ],
				'default'    => [ 'size' => 48, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-layout-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 2. Reading Progress Bar
		========================================================= */
		$this->start_controls_section(
			'section_style_progress_bar',
			[
				'label'     => esc_html__( 'Reading Progress Bar', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_progress_bar' => 'yes' ],
			]
		);

		$this->add_control(
			'progress_bar_color',
			[
				'label'     => esc_html__( 'Bar Color / Gradient', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .tn-progress-bar' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_bar_track_bg',
			[
				'label'     => esc_html__( 'Track Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tn-progress-bar-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_bar_height',
			[
				'label'      => esc_html__( 'Bar Height (px)', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 2, 'max' => 16 ] ],
				'default'    => [ 'size' => 4, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-progress-bar-wrap' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 3. Breadcrumbs
		========================================================= */
		$this->start_controls_section(
			'section_style_breadcrumbs',
			[
				'label'     => esc_html__( 'Breadcrumbs', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_breadcrumb' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'breadcrumb_typography',
				'selector' => '{{WRAPPER}} .tn-breadcrumb',
			]
		);

		$this->add_control(
			'breadcrumb_link_color',
			[
				'label'     => esc_html__( 'Link Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .tn-breadcrumb a, {{WRAPPER}} .tn-breadcrumb span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'breadcrumb_link_hover_color',
			[
				'label'     => esc_html__( 'Link Hover Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .tn-breadcrumb a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'breadcrumb_sep_color',
			[
				'label'     => esc_html__( 'Separator Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#cbd5e1',
				'selectors' => [
					'{{WRAPPER}} .tn-breadcrumb-sep' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'breadcrumb_current_color',
			[
				'label'     => esc_html__( 'Current Item Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-breadcrumb-current' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'breadcrumb_margin_bottom',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'selectors'  => [
					'{{WRAPPER}} .tn-breadcrumb' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 4. Note Header (Title & Eyebrow & Subtitle)
		========================================================= */
		$this->start_controls_section(
			'section_style_header',
			[
				'label' => esc_html__( 'Note Header & Title', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'header_eyebrow_heading',
			[
				'label' => esc_html__( 'Eyebrow / Chapter Badge', 'practice-problems-el' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'eyebrow_typography',
				'selector' => '{{WRAPPER}} .tn-eyebrow',
			]
		);

		$this->add_control(
			'eyebrow_color',
			[
				'label'     => esc_html__( 'Eyebrow Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .tn-eyebrow' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'eyebrow_bg_color',
			[
				'label'     => esc_html__( 'Eyebrow Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tn-eyebrow' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'eyebrow_padding',
			[
				'label'      => esc_html__( 'Eyebrow Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-eyebrow' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'eyebrow_border_radius',
			[
				'label'      => esc_html__( 'Eyebrow Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-eyebrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'eyebrow_margin_bottom',
			[
				'label'      => esc_html__( 'Eyebrow Spacing Bottom', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-eyebrow' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'header_title_heading',
			[
				'label'     => esc_html__( 'Main Title (H1)', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'main_title_typography',
				'selector' => '{{WRAPPER}} .tn-main-title',
			]
		);

		$this->add_control(
			'main_title_color',
			[
				'label'     => esc_html__( 'Title Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-main-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'main_title_margin_bottom',
			[
				'label'      => esc_html__( 'Title Spacing Bottom', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-main-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'header_summary_heading',
			[
				'label'     => esc_html__( 'Summary / Subtitle', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'summary_typography',
				'selector' => '{{WRAPPER}} .tn-summary',
			]
		);

		$this->add_control(
			'summary_color',
			[
				'label'     => esc_html__( 'Summary Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#475569',
				'selectors' => [
					'{{WRAPPER}} .tn-summary' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_divider_color',
			[
				'label'     => esc_html__( 'Header Bottom Divider Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f1f5f9',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .tn-note-header' => 'border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 5. Table of Contents (TOC) Sidebar
		========================================================= */
		$this->start_controls_section(
			'section_style_toc',
			[
				'label'     => esc_html__( 'Table of Contents (TOC)', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_toc' => 'yes' ],
			]
		);

		$this->add_control(
			'toc_box_heading',
			[
				'label' => esc_html__( 'TOC Card Box', 'practice-problems-el' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'toc_card_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-toc-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'toc_card_border',
				'selector' => '{{WRAPPER}} .tn-toc-card',
			]
		);

		$this->add_responsive_control(
			'toc_card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-toc-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'toc_card_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-toc-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'toc_card_shadow',
				'selector' => '{{WRAPPER}} .tn-toc-card',
			]
		);

		$this->add_responsive_control(
			'toc_sticky_top',
			[
				'label'      => esc_html__( 'Sticky Top Offset (px)', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 200 ] ],
				'selectors'  => [
					'{{WRAPPER}} .tn-sidebar' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'toc_title_heading',
			[
				'label'     => esc_html__( 'TOC Title', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'toc_title_typography',
				'selector' => '{{WRAPPER}} .tn-toc-title',
			]
		);

		$this->add_control(
			'toc_title_color',
			[
				'label'     => esc_html__( 'Title Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-toc-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toc_links_heading',
			[
				'label'     => esc_html__( 'TOC Links / Items', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'toc_links_typography',
				'selector' => '{{WRAPPER}} .tn-toc-link',
			]
		);

		$this->add_control(
			'toc_link_color',
			[
				'label'     => esc_html__( 'Default Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .tn-toc-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toc_link_hover_color',
			[
				'label'     => esc_html__( 'Hover Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .tn-toc-link:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toc_link_hover_bg',
			[
				'label'     => esc_html__( 'Hover Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tn-toc-link:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toc_active_color',
			[
				'label'     => esc_html__( 'Active Link Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .tn-toc-link.active' => 'color: {{VALUE}}; border-left-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toc_active_bg',
			[
				'label'     => esc_html__( 'Active Link Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#eff6ff',
				'selectors' => [
					'{{WRAPPER}} .tn-toc-link.active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'toc_item_padding',
			[
				'label'      => esc_html__( 'Link Item Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-toc-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'toc_item_gap',
			[
				'label'      => esc_html__( 'Items Gap', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-toc-list' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 6. Section Headings (H2)
		========================================================= */
		$this->start_controls_section(
			'section_style_headings',
			[
				'label' => esc_html__( 'Section Headings (H2)', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'section_heading_typography',
				'selector' => '{{WRAPPER}} .tn-section-heading',
			]
		);

		$this->add_control(
			'section_heading_color',
			[
				'label'     => esc_html__( 'Heading Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-section-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'section_heading_border_color',
			[
				'label'     => esc_html__( 'Bottom Border Divider Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e2e8f0',
				'selectors' => [
					'{{WRAPPER}} .tn-section-heading' => 'border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'section_heading_margin_bottom',
			[
				'label'      => esc_html__( 'Heading Bottom Margin', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-section-heading' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'section_block_spacing',
			[
				'label'      => esc_html__( 'Space Between Sections', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 10, 'max' => 100 ] ],
				'default'    => [ 'size' => 44, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-section' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 7. Overview & General Text
		========================================================= */
		$this->start_controls_section(
			'section_style_overview',
			[
				'label' => esc_html__( 'Overview / Body Text', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'overview_typography',
				'selector' => '{{WRAPPER}} .tn-section-body, {{WRAPPER}} .tn-section-body p, {{WRAPPER}} .tn-section-body li',
			]
		);

		$this->add_control(
			'overview_color',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => [
					'{{WRAPPER}} .tn-section-body, {{WRAPPER}} .tn-section-body p, {{WRAPPER}} .tn-section-body li' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 8. Definition Card
		========================================================= */
		$this->start_controls_section(
			'section_style_definition',
			[
				'label' => esc_html__( 'Definition Box', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'def_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f8fafc',
				'selectors' => [
					'{{WRAPPER}} .tn-definition-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'def_accent_color',
			[
				'label'     => esc_html__( 'Left Accent Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .tn-definition-card' => 'border-left-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'def_accent_width',
			[
				'label'      => esc_html__( 'Left Accent Border Width', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 1, 'max' => 12 ] ],
				'default'    => [ 'size' => 4, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-definition-card' => 'border-left-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'def_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e2e8f0',
				'selectors' => [
					'{{WRAPPER}} .tn-definition-card' => 'border-top-color: {{VALUE}}; border-right-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'def_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-definition-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'def_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-definition-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'def_box_shadow',
				'selector' => '{{WRAPPER}} .tn-definition-card',
			]
		);

		$this->add_control(
			'def_label_heading',
			[
				'label'     => esc_html__( 'Definition Badge', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'def_label_typography',
				'selector' => '{{WRAPPER}} .tn-definition-label',
			]
		);

		$this->add_control(
			'def_label_color',
			[
				'label'     => esc_html__( 'Badge Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .tn-definition-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'def_label_bg',
			[
				'label'     => esc_html__( 'Badge Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#eff6ff',
				'selectors' => [
					'{{WRAPPER}} .tn-definition-label' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'def_content_heading',
			[
				'label'     => esc_html__( 'Definition Content Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'def_content_typography',
				'selector' => '{{WRAPPER}} .tn-definition-content',
			]
		);

		$this->add_control(
			'def_content_color',
			[
				'label'     => esc_html__( 'Content Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e293b',
				'selectors' => [
					'{{WRAPPER}} .tn-definition-content' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 9. Key Formula Card
		========================================================= */
		$this->start_controls_section(
			'section_style_formula',
			[
				'label' => esc_html__( 'Key Formula Box', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'formula_card_bg',
			[
				'label'     => esc_html__( 'Card Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-formula-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'formula_card_border',
				'selector' => '{{WRAPPER}} .tn-formula-card',
			]
		);

		$this->add_responsive_control(
			'formula_card_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-formula-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'formula_card_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-formula-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'formula_card_shadow',
				'selector' => '{{WRAPPER}} .tn-formula-card',
			]
		);

		$this->add_control(
			'formula_display_heading',
			[
				'label'     => esc_html__( 'Formula Expression Box', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'formula_display_typography',
				'selector' => '{{WRAPPER}} .tn-formula-display',
			]
		);

		$this->add_control(
			'formula_display_color',
			[
				'label'     => esc_html__( 'Formula Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#38bdf8',
				'selectors' => [
					'{{WRAPPER}} .tn-formula-display' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'formula_display_bg',
			[
				'label'     => esc_html__( 'Formula Inner Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e293b',
				'selectors' => [
					'{{WRAPPER}} .tn-formula-display' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'formula_exp_heading',
			[
				'label'     => esc_html__( 'Formula Explanation Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'formula_exp_typography',
				'selector' => '{{WRAPPER}} .tn-formula-explanation',
			]
		);

		$this->add_control(
			'formula_exp_color',
			[
				'label'     => esc_html__( 'Explanation Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#cbd5e1',
				'selectors' => [
					'{{WRAPPER}} .tn-formula-explanation' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 10. Worked Example Card
		========================================================= */
		$this->start_controls_section(
			'section_style_example',
			[
				'label' => esc_html__( 'Worked Example Card', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'example_card_bg',
			[
				'label'     => esc_html__( 'Card Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-example-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'example_card_border',
				'selector' => '{{WRAPPER}} .tn-example-card',
			]
		);

		$this->add_responsive_control(
			'example_card_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-example-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'example_card_shadow',
				'selector' => '{{WRAPPER}} .tn-example-card',
			]
		);

		$this->add_control(
			'example_problem_heading',
			[
				'label'     => esc_html__( 'Problem Statement Area', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'example_problem_typography',
				'selector' => '{{WRAPPER}} .tn-example-problem',
			]
		);

		$this->add_control(
			'example_problem_color',
			[
				'label'     => esc_html__( 'Problem Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e293b',
				'selectors' => [
					'{{WRAPPER}} .tn-example-problem' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'example_problem_label_color',
			[
				'label'     => esc_html__( '"Problem:" Label Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .tn-example-problem strong' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'example_problem_bg',
			[
				'label'     => esc_html__( 'Problem Area Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-example-problem' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'example_steps_heading',
			[
				'label'     => esc_html__( 'Steps List & Badges', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'step_badge_color',
			[
				'label'     => esc_html__( 'Step Number Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4338ca',
				'selectors' => [
					'{{WRAPPER}} .tn-step-badge' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'step_badge_bg',
			[
				'label'     => esc_html__( 'Step Number Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e0e7ff',
				'selectors' => [
					'{{WRAPPER}} .tn-step-badge' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'step_text_typography',
				'selector' => '{{WRAPPER}} .tn-step-text',
			]
		);

		$this->add_control(
			'step_text_color',
			[
				'label'     => esc_html__( 'Step Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => [
					'{{WRAPPER}} .tn-step-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'example_sol_heading',
			[
				'label'     => esc_html__( 'Solution Box', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'example_sol_bg',
			[
				'label'     => esc_html__( 'Solution Box Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ecfdf5',
				'selectors' => [
					'{{WRAPPER}} .tn-example-solution-box' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'example_sol_border_color',
			[
				'label'     => esc_html__( 'Solution Box Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#a7f3d0',
				'selectors' => [
					'{{WRAPPER}} .tn-example-solution-box' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'example_sol_label_color',
			[
				'label'     => esc_html__( 'Solution Label Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#059669',
				'selectors' => [
					'{{WRAPPER}} .tn-example-solution-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'example_sol_value_color',
			[
				'label'     => esc_html__( 'Solution Value Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#065f46',
				'selectors' => [
					'{{WRAPPER}} .tn-example-solution-value' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 11. Common Mistake Card
		========================================================= */
		$this->start_controls_section(
			'section_style_mistake',
			[
				'label' => esc_html__( 'Common Mistake Box', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'mistake_bg',
			[
				'label'     => esc_html__( 'Card Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fffbeb',
				'selectors' => [
					'{{WRAPPER}} .tn-mistake-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mistake_accent_border',
			[
				'label'     => esc_html__( 'Left Accent Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f59e0b',
				'selectors' => [
					'{{WRAPPER}} .tn-mistake-card' => 'border-left-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'mistake_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fde68a',
				'selectors' => [
					'{{WRAPPER}} .tn-mistake-card' => 'border-top-color: {{VALUE}}; border-right-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mistake_icon_color',
			[
				'label'     => esc_html__( 'Warning Icon Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#d97706',
				'selectors' => [
					'{{WRAPPER}} .tn-mistake-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'mistake_title_typography',
				'selector' => '{{WRAPPER}} .tn-mistake-body h4',
			]
		);

		$this->add_control(
			'mistake_title_color',
			[
				'label'     => esc_html__( 'Mistake Title Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#92400e',
				'selectors' => [
					'{{WRAPPER}} .tn-mistake-body h4' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'mistake_desc_typography',
				'selector' => '{{WRAPPER}} .tn-mistake-body p',
			]
		);

		$this->add_control(
			'mistake_desc_color',
			[
				'label'     => esc_html__( 'Description Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#78350f',
				'selectors' => [
					'{{WRAPPER}} .tn-mistake-body p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 12. Next Steps Card
		========================================================= */
		$this->start_controls_section(
			'section_style_next_steps',
			[
				'label' => esc_html__( 'Next Steps Card', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'next_steps_bg',
			[
				'label'     => esc_html__( 'Card Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tn-next-steps-card' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'next_steps_border',
				'selector' => '{{WRAPPER}} .tn-next-steps-card',
			]
		);

		$this->add_responsive_control(
			'next_steps_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-next-steps-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'next_steps_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-next-steps-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'next_steps_title_typography',
				'label'    => esc_html__( 'Title Typography', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .tn-next-steps-info h3',
			]
		);

		$this->add_control(
			'next_steps_title_color',
			[
				'label'     => esc_html__( 'Title Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-next-steps-info h3' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'next_steps_desc_typography',
				'label'    => esc_html__( 'Description Typography', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .tn-next-steps-info p',
			]
		);

		$this->add_control(
			'next_steps_desc_color',
			[
				'label'     => esc_html__( 'Description Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .tn-next-steps-info p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'next_steps_btn_heading',
			[
				'label'     => esc_html__( 'Action Button ("Go to practice")', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'next_steps_btn_typography',
				'selector' => '{{WRAPPER}} .tn-btn-primary',
			]
		);

		$this->add_control(
			'next_steps_btn_text_color',
			[
				'label'     => esc_html__( 'Button Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-btn-primary' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'next_steps_btn_bg',
			[
				'label'     => esc_html__( 'Button Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-btn-primary' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'next_steps_btn_hover_text_color',
			[
				'label'     => esc_html__( 'Hover Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-btn-primary:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'next_steps_btn_hover_bg',
			[
				'label'     => esc_html__( 'Hover Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e293b',
				'selectors' => [
					'{{WRAPPER}} .tn-btn-primary:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'next_steps_btn_padding',
			[
				'label'      => esc_html__( 'Button Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-btn-primary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'next_steps_btn_radius',
			[
				'label'      => esc_html__( 'Button Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-btn-primary' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 13. Previous / Next Topic Navigation
		========================================================= */
		$this->start_controls_section(
			'section_style_navigation',
			[
				'label'     => esc_html__( 'Topic Navigation (Footer)', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_navigation' => 'yes' ],
			]
		);

		$this->add_control(
			'nav_card_bg',
			[
				'label'     => esc_html__( 'Card Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-nav-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_card_hover_bg',
			[
				'label'     => esc_html__( 'Card Hover Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tn-nav-card:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'nav_card_border',
				'selector' => '{{WRAPPER}} .tn-nav-card',
			]
		);

		$this->add_responsive_control(
			'nav_card_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-nav-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_card_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-nav-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'nav_sub_heading',
			[
				'label'     => esc_html__( 'Direction Label ("Previous / Next")', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'nav_sub_typography',
				'selector' => '{{WRAPPER}} .tn-nav-sub',
			]
		);

		$this->add_control(
			'nav_sub_color',
			[
				'label'     => esc_html__( 'Direction Label Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .tn-nav-sub' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_title_heading',
			[
				'label'     => esc_html__( 'Topic Title', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'nav_title_typography',
				'selector' => '{{WRAPPER}} .tn-nav-title',
			]
		);

		$this->add_control(
			'nav_title_color',
			[
				'label'     => esc_html__( 'Topic Title Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-nav-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_title_hover_color',
			[
				'label'     => esc_html__( 'Topic Title Hover Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .tn-nav-card:hover .tn-nav-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — 14. Floating PDF Button
		========================================================= */
		$this->start_controls_section(
			'section_style_pdf',
			[
				'label'     => esc_html__( 'Floating PDF Button', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_floating_pdf' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pdf_btn_typography',
				'selector' => '{{WRAPPER}} .tn-floating-pdf-btn',
			]
		);

		$this->add_control(
			'pdf_btn_text_color',
			[
				'label'     => esc_html__( 'Text / Icon Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-floating-pdf-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pdf_btn_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-floating-pdf-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pdf_btn_hover_text_color',
			[
				'label'     => esc_html__( 'Hover Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-floating-pdf-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pdf_btn_hover_bg',
			[
				'label'     => esc_html__( 'Hover Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e293b',
				'selectors' => [
					'{{WRAPPER}} .tn-floating-pdf-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'pdf_btn_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-floating-pdf-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'pdf_btn_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-floating-pdf-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'pdf_btn_shadow',
				'selector' => '{{WRAPPER}} .tn-floating-pdf-btn',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget HTML.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		// Determine target Note ID
		$note_id = 0;
		if ( 'manual' === $s['data_source'] && ! empty( $s['selected_note_id'] ) ) {
			$note_id = intval( $s['selected_note_id'] );
		} else {
			// 1. Check URL parameters: ?note=slug or ?id=123 or ?note_id=123
			if ( ! empty( $_GET['note'] ) ) {
				$by_slug = get_page_by_path( sanitize_title( wp_unslash( $_GET['note'] ) ), OBJECT, 'math_note' );
				if ( $by_slug ) {
					$note_id = $by_slug->ID;
				}
			} elseif ( ! empty( $_GET['id'] ) ) {
				$note_id = intval( $_GET['id'] );
			} elseif ( ! empty( $_GET['note_id'] ) ) {
				$note_id = intval( $_GET['note_id'] );
			}

			// 2. Dynamic mode: check if on single math_note post
			if ( ! $note_id ) {
				$current_id = get_the_ID();
				if ( 'math_note' === get_post_type( $current_id ) ) {
					$note_id = $current_id;
				}
			}

			// 3. Fallback to first published math_note for Elementor editor preview
			if ( ! $note_id ) {
				$fallback = get_posts( [
					'post_type'      => 'math_note',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				] );
				if ( ! empty( $fallback ) ) {
					$note_id = $fallback[0]->ID;
				}
			}
		}

		if ( ! $note_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'Please create or select a Math Note to display content.', 'practice-problems-el' ) . '</div>';
			}
			return;
		}

		self::render_note_markup( $note_id, $s );
	}

	/**
	 * Public static renderer for Note markup.
	 * Used by Topic_Notes_Widget, Elementor templates, and the single math_note post filter.
	 */
	public static function render_note_markup( $note_id, $settings = [] ) {
		$default_settings = [
			'enable_progress_bar'   => 'yes',
			'show_breadcrumb'       => 'yes',
			'breadcrumb_home_label' => 'Notes',
			'breadcrumb_separator'  => '/',
			'show_toc'              => 'yes',
			'toc_title'             => 'On this page',
			'show_chapter_eyebrow'  => 'yes',
			'show_summary'          => 'yes',
			'enable_navigation'     => 'yes',
			'prev_topic_label'      => 'Previous topic',
			'next_topic_label'      => 'Next topic',
			'enable_floating_pdf'   => 'yes',
		];
		$s = wp_parse_args( $settings, $default_settings );

		$post = get_post( $note_id );
		if ( ! $post ) {
			return;
		}

		// Note data
		$title       = get_the_title( $note_id );
		$summary     = get_post_meta( $note_id, '_mn_summary', true );
		$pdf_file    = get_post_meta( $note_id, '_mn_pdf_file', true );
		$pdf_label   = get_post_meta( $note_id, '_mn_pdf_label', true ) ?: esc_html__( 'Download PDF', 'practice-problems-el' );
		$raw_sections= get_post_meta( $note_id, '_mn_sections', true );
		$sections    = is_array( $raw_sections ) ? $raw_sections : [];

		// Taxonomies
		$courses  = wp_get_post_terms( $note_id, 'math_course', [ 'fields' => 'names' ] );
		$chapters = wp_get_post_terms( $note_id, 'math_chapter', [ 'fields' => 'names' ] );
		$course_name  = ! empty( $courses ) ? $courses[0] : '';
		$chapter_name = ! empty( $chapters ) ? $chapters[0] : '';

		// Normalize sections into chapters data
		$is_chapter_format = false;
		if ( ! empty( $sections ) && is_array( $sections ) ) {
			if ( isset( $sections[0]['subsections'] ) || isset( $sections[0]['definition'] ) ) {
				$is_chapter_format = true;
			}
		}

		$chapters_data = [];
		if ( $is_chapter_format ) {
			$chapters_data = $sections;
		} else {
			// Migrate legacy flat array format
			$single_ch = [
				'id'             => 'ch_main',
				'title'          => 'Overview',
				'subsections'    => [],
				'definition'     => [ 'enabled' => false, 'content' => '' ],
				'formula'        => [ 'enabled' => false, 'formula' => '', 'explanation' => '' ],
				'worked_example' => [ 'enabled' => false, 'problem' => '', 'solution' => '' ],
				'legacy_items'   => [],
			];

			foreach ( $sections as $s_item ) {
				$t = $s_item['type'] ?? 'overview';
				if ( 'overview' === $t || 'custom' === $t ) {
					$single_ch['subsections'][] = [
						'id'      => 'sub_' . uniqid(),
						'title'   => $s_item['title'] ?? 'Overview',
						'content' => $s_item['content'] ?? '',
					];
				} elseif ( 'definition' === $t ) {
					$single_ch['definition'] = [
						'enabled' => true,
						'content' => $s_item['content'] ?? '',
					];
				} elseif ( 'formula' === $t ) {
					$single_ch['formula'] = [
						'enabled'     => true,
						'formula'     => $s_item['formula'] ?? '',
						'explanation' => $s_item['explanation'] ?? '',
					];
				} elseif ( 'worked_example' === $t ) {
					$single_ch['worked_example'] = [
						'enabled'  => true,
						'problem'  => $s_item['problem'] ?? '',
						'solution' => $s_item['solution'] ?? '',
					];
				} else {
					$single_ch['legacy_items'][] = $s_item;
				}
			}

			if ( ! empty( $single_ch['subsections'] ) || ! empty( $single_ch['definition']['enabled'] ) || ! empty( $single_ch['legacy_items'] ) ) {
				$chapters_data[] = $single_ch;
			}
		}

		// Generate TOC items from Chapters & Sub Sections
		$toc_items = [];
		foreach ( $chapters_data as $c_idx => $ch ) {
			$ch_id    = 'tn-chapter-' . sanitize_title( $ch['title'] ?? 'chapter-' . $c_idx );
			$ch_title = $ch['title'] ?? 'Overview';

			$toc_items[] = [
				'id'     => $ch_id,
				'title'  => $ch_title,
				'is_sub' => false,
			];

			if ( ! empty( $ch['subsections'] ) && is_array( $ch['subsections'] ) ) {
				foreach ( $ch['subsections'] as $s_idx => $sub ) {
					$sub_id    = 'tn-sub-' . sanitize_title( ( $sub['title'] ?? 'sub' ) . '-' . $c_idx . '-' . $s_idx );
					$sub_title = $sub['title'] ?? 'Sub Section';
					$toc_items[] = [
						'id'     => $sub_id,
						'title'  => $sub_title,
						'is_sub' => true,
					];
				}
			}
		}

		// Calculate Prev / Next
		$prev_post = get_previous_post();
		$next_post = get_next_post();
		$prev_override = get_post_meta( $note_id, '_mn_prev_override', true );
		$next_override = get_post_meta( $note_id, '_mn_next_override', true );

		$topic_page = get_page_by_path( 'topic-note' );
		$base_topic_url = ( $topic_page && 'publish' === $topic_page->post_status ) ? get_permalink( $topic_page->ID ) : home_url( '/topic-note/' );
		?>

		<div class="tn-widget-container">

			<?php // READING PROGRESS BAR ?>
			<?php if ( 'yes' === $s['enable_progress_bar'] ) : ?>
				<div class="tn-progress-bar-wrap">
					<div class="tn-progress-bar"></div>
				</div>
			<?php endif; ?>

			<?php // BREADCRUMBS ?>
			<?php if ( 'yes' === $s['show_breadcrumb'] ) : ?>
				<nav class="tn-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'practice-problems-el' ); ?>">
					<a href="<?php echo esc_url( home_url( '/notes/' ) ); ?>"><?php echo esc_html( $s['breadcrumb_home_label'] ); ?></a>
					<?php if ( $course_name ) : ?>
						<span class="tn-breadcrumb-sep"><?php echo esc_html( $s['breadcrumb_separator'] ); ?></span>
						<span><?php echo esc_html( $course_name ); ?></span>
					<?php endif; ?>
					<?php if ( $chapter_name ) : ?>
						<span class="tn-breadcrumb-sep"><?php echo esc_html( $s['breadcrumb_separator'] ); ?></span>
						<span><?php echo esc_html( $chapter_name ); ?></span>
					<?php endif; ?>
					<span class="tn-breadcrumb-sep"><?php echo esc_html( $s['breadcrumb_separator'] ); ?></span>
					<span class="tn-breadcrumb-current"><?php echo esc_html( $title ); ?></span>
				</nav>
			<?php endif; ?>

			<div class="tn-layout-grid">

				<?php // LEFT: SIDEBAR & TABLE OF CONTENTS ?>
				<?php if ( 'yes' === $s['show_toc'] && ! empty( $toc_items ) ) : ?>
					<aside class="tn-sidebar">
						<div class="tn-toc-card">
							<h3 class="tn-toc-title"><?php echo esc_html( $s['toc_title'] ); ?></h3>
							<ul class="tn-toc-list">
								<?php foreach ( $toc_items as $item ) : ?>
									<li class="tn-toc-item <?php echo ! empty( $item['is_sub'] ) ? 'tn-toc-item-sub' : 'tn-toc-item-chapter'; ?>">
										<a href="#<?php echo esc_attr( $item['id'] ); ?>" class="tn-toc-link">
											<span class="pp-math-render"><?php echo esc_html( $item['title'] ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</aside>
				<?php endif; ?>

				<?php // RIGHT: MAIN CONTENT ?>
				<main class="tn-main-content">

					<?php // NOTE HEADER ?>
					<header class="tn-note-header">
						<?php if ( 'yes' === $s['show_chapter_eyebrow'] && $chapter_name ) : ?>
							<span class="tn-eyebrow"><?php echo esc_html( $chapter_name ); ?></span>
						<?php endif; ?>

						<h1 class="tn-main-title"><?php echo esc_html( $title ); ?></h1>

						<?php if ( 'yes' === $s['show_summary'] && ! empty( $summary ) ) : ?>
							<p class="tn-summary"><?php echo esc_html( $summary ); ?></p>
						<?php endif; ?>
					</header>

					<?php // CHAPTER BOXES & ACCORDIONS ?>
					<div class="tn-sections-body">
						<?php foreach ( $chapters_data as $c_idx => $ch ) : 
							$ch_id    = 'tn-chapter-' . sanitize_title( $ch['title'] ?? 'chapter-' . $c_idx );
							$ch_title = $ch['title'] ?? 'Overview';
							$subsections = ( isset( $ch['subsections'] ) && is_array( $ch['subsections'] ) ) ? $ch['subsections'] : [];
						?>
							<section id="<?php echo esc_attr( $ch_id ); ?>" class="tn-chapter-box">
								<!-- Chapter Header / Title -->
								<div class="tn-chapter-box-header">
									<h2 class="tn-chapter-title pp-math-render"><?php echo esc_html( $ch_title ); ?></h2>
								</div>

								<!-- Sub Sections (Accordions) -->
								<?php if ( ! empty( $subsections ) ) : ?>
									<div class="tn-subsections-accordions">
										<?php foreach ( $subsections as $s_idx => $sub ) : 
											$sub_id = 'tn-sub-' . sanitize_title( ( $sub['title'] ?? 'sub' ) . '-' . $c_idx . '-' . $s_idx );
											$sub_title = $sub['title'] ?? 'Sub Section';
										?>
											<div id="<?php echo esc_attr( $sub_id ); ?>" class="tn-accordion-item" data-sub-id="<?php echo esc_attr( $sub['id'] ?? 'sub_' . $s_idx ); ?>">
												<button class="tn-accordion-header" type="button" aria-expanded="false">
													<span class="tn-accordion-title pp-math-render"><?php echo esc_html( $sub_title ); ?></span>
													<span class="tn-accordion-icon" aria-hidden="true">+</span>
												</button>
												<div class="tn-accordion-body" style="display:none;">
													<div class="tn-accordion-content pp-math-render">
														<?php echo wp_kses_post( wpautop( $sub['content'] ?? '' ) ); ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>

								<!-- Optional: Definition Block -->
								<?php if ( ! empty( $ch['definition']['enabled'] ) && ! empty( $ch['definition']['content'] ) ) : ?>
									<div class="tn-definition-card">
										<span class="tn-definition-label"><?php esc_html_e( 'Definition', 'practice-problems-el' ); ?></span>
										<div class="tn-definition-content pp-math-render">
											<?php echo wp_kses_post( wpautop( $ch['definition']['content'] ) ); ?>
										</div>
									</div>
								<?php endif; ?>

								<!-- Optional: Key Formula Block -->
								<?php if ( ! empty( $ch['formula']['enabled'] ) && ! empty( $ch['formula']['formula'] ) ) : ?>
									<div class="tn-formula-card">
										<div class="tn-formula-header">
											<span class="tn-formula-badge"><?php esc_html_e( 'Key Formula', 'practice-problems-el' ); ?></span>
										</div>
										<div class="tn-formula-display pp-math-render">
											$$<?php echo esc_html( trim( $ch['formula']['formula'], '$' ) ); ?>$$
										</div>
										<?php if ( ! empty( $ch['formula']['explanation'] ) ) : ?>
											<p class="tn-formula-explanation pp-math-render"><?php echo wp_kses_post( $ch['formula']['explanation'] ); ?></p>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<!-- Optional: Worked Example Block -->
								<?php if ( ! empty( $ch['worked_example']['enabled'] ) && ( ! empty( $ch['worked_example']['problem'] ) || ! empty( $ch['worked_example']['solution'] ) ) ) : ?>
									<div class="tn-example-card">
										<div class="tn-example-header">
											<span class="tn-example-badge"><?php esc_html_e( 'Worked Example', 'practice-problems-el' ); ?></span>
										</div>
										<?php if ( ! empty( $ch['worked_example']['problem'] ) ) : ?>
											<div class="tn-example-problem">
												<strong><?php esc_html_e( 'Question:', 'practice-problems-el' ); ?></strong>
												<div class="tn-example-problem-text pp-math-render">
													<?php echo wp_kses_post( wpautop( $ch['worked_example']['problem'] ) ); ?>
												</div>
											</div>
										<?php endif; ?>
										<?php if ( ! empty( $ch['worked_example']['solution'] ) ) : ?>
											<div class="tn-example-solution-box">
												<span class="tn-example-solution-label"><?php esc_html_e( 'Solution / Result:', 'practice-problems-el' ); ?></span>
												<div class="tn-example-solution-value pp-math-render">
													<?php echo wp_kses_post( wpautop( $ch['worked_example']['solution'] ) ); ?>
												</div>
											</div>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<!-- Optional: Legacy items if converted from old format -->
								<?php if ( ! empty( $ch['legacy_items'] ) ) : ?>
									<?php foreach ( $ch['legacy_items'] as $l_item ) : 
										$l_type = $l_item['type'] ?? '';
									?>
										<?php if ( 'common_mistake' === $l_type ) : ?>
											<div class="tn-mistake-card <?php echo esc_attr( $l_item['severity'] ?? 'warning' ); ?>">
												<svg class="tn-mistake-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
													<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
													<line x1="12" y1="9" x2="12" y2="13"/>
													<line x1="12" y1="17" x2="12.01" y2="17"/>
												</svg>
												<div class="tn-mistake-body">
													<?php if ( ! empty( $l_item['mistake_title'] ) ) : ?>
														<h4><?php echo esc_html( $l_item['mistake_title'] ); ?></h4>
													<?php endif; ?>
													<p class="pp-math-render"><?php echo wp_kses_post( $l_item['description'] ?? '' ); ?></p>
												</div>
											</div>
										<?php elseif ( 'next_steps' === $l_type ) : ?>
											<div class="tn-next-steps-card">
												<div class="tn-next-steps-info">
													<h3><?php echo esc_html( $l_item['title'] ?? 'Next Steps' ); ?></h3>
													<p><?php echo wp_kses_post( $l_item['description'] ?? '' ); ?></p>
												</div>
												<?php if ( ! empty( $l_item['button_text'] ) ) : ?>
													<a href="<?php echo esc_url( $l_item['button_link'] ?? '#' ); ?>" class="tn-btn-primary">
														<span><?php echo esc_html( $l_item['button_text'] ); ?></span>
													</a>
												<?php endif; ?>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>

							</section>
						<?php endforeach; ?>
					</div>

					<?php // PREVIOUS / NEXT TOPIC NAVIGATION ?>
					<?php if ( 'yes' === $s['enable_navigation'] ) : ?>
						<nav class="tn-nav-container" aria-label="<?php esc_attr_e( 'Topic navigation', 'practice-problems-el' ); ?>">
							<?php if ( ! empty( $prev_override ) || ! empty( $prev_post ) ) : 
								$p_title = ! empty( $prev_override ) ? $prev_override : get_the_title( $prev_post );
								$p_link  = ! empty( $prev_post ) ? add_query_arg( 'note', $prev_post->post_name, $base_topic_url ) : '#';
							?>
								<a href="<?php echo esc_url( $p_link ); ?>" class="tn-nav-card prev">
									<span class="tn-nav-sub">← <?php echo esc_html( $s['prev_topic_label'] ); ?></span>
									<span class="tn-nav-title"><?php echo esc_html( $p_title ); ?></span>
								</a>
							<?php else : ?>
								<div></div>
							<?php endif; ?>

							<?php if ( ! empty( $next_override ) || ! empty( $next_post ) ) : 
								$n_title = ! empty( $next_override ) ? $next_override : get_the_title( $next_post );
								$n_link  = ! empty( $next_post ) ? add_query_arg( 'note', $next_post->post_name, $base_topic_url ) : '#';
							?>
								<a href="<?php echo esc_url( $n_link ); ?>" class="tn-nav-card next">
									<span class="tn-nav-sub"><?php echo esc_html( $s['next_topic_label'] ); ?> →</span>
									<span class="tn-nav-title"><?php echo esc_html( $n_title ); ?></span>
								</a>
							<?php endif; ?>
						</nav>
					<?php endif; ?>

				</main>

			</div>

			<?php // FLOATING PDF BUTTON ?>
			<?php 
			$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
			if ( 'yes' === $s['enable_floating_pdf'] && ( ! empty( $pdf_file ) || $is_editor ) ) : 
			?>
				<a href="<?php echo esc_url( ! empty( $pdf_file ) ? $pdf_file : '#' ); ?>" <?php echo ! empty( $pdf_file ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> class="tn-floating-pdf-btn">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
					<span><?php echo esc_html( ! empty( $pdf_label ) ? $pdf_label : __( 'Download Cheat Sheet (PDF)', 'practice-problems' ) ); ?></span>
				</a>
			<?php endif; ?>

		</div>
		<?php
	}
}
