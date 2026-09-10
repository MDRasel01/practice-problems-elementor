<?php
/**
 * Topic Notes Grid Elementor Widget.
 *
 * Displays a responsive grid of topic note cards (Popular Topics)
 * with Course · Chapter eyebrow, title, description, and "Read notes →" links.
 *
 * @package PracticeProblems
 */

namespace PracticeProblems\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Topic_Notes_Grid_Widget
 */
class Topic_Notes_Grid_Widget extends Widget_Base {

	public function get_name() {
		return 'topic_notes_grid';
	}

	public function get_title() {
		return esc_html__( 'Topic Notes Grid', 'practice-problems-el' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_keywords() {
		return [ 'topic', 'notes', 'grid', 'cards', 'popular', 'topics', 'math', 'archive', 'recent' ];
	}

	public function get_style_depends() {
		return [ 'topic-notes-frontend' ];
	}

	/**
	 * Helper: Fetch course terms for filter dropdown.
	 */
	private function get_course_options() {
		$options = [ '' => esc_html__( '— All Courses —', 'practice-problems-el' ) ];
		$terms = get_terms( [ 'taxonomy' => 'math_course', 'hide_empty' => false ] );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	/**
	 * Helper: Fetch chapter terms for filter dropdown.
	 */
	private function get_chapter_options() {
		$options = [ '' => esc_html__( '— All Chapters —', 'practice-problems-el' ) ];
		$terms = get_terms( [ 'taxonomy' => 'math_chapter', 'hide_empty' => false ] );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	protected function register_controls() {

		/* =========================================================
		   CONTENT TAB — Section Header
		========================================================= */
		$this->start_controls_section(
			'section_header_settings',
			[
				'label' => esc_html__( 'Section Header', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_header',
			[
				'label'        => esc_html__( 'Show Header', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'header_title',
			[
				'label'       => esc_html__( 'Section Title', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Popular topics', 'practice-problems-el' ),
				'condition'   => [ 'show_header' => 'yes' ],
				'label_block' => true,
			]
		);

		$this->add_control(
			'header_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'practice-problems-el' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h2',
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'p'    => 'p',
					'div'  => 'div',
				],
				'condition' => [ 'show_header' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'header_align',
			[
				'label'     => esc_html__( 'Alignment', 'practice-problems-el' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'practice-problems-el' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'practice-problems-el' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'practice-problems-el' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .tn-grid-section-title' => 'text-align: {{VALUE}};',
				],
				'condition' => [ 'show_header' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Query & Filters
		========================================================= */
		$this->start_controls_section(
			'section_query_settings',
			[
				'label' => esc_html__( 'Query & Filters', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'limit_by',
			[
				'label'       => esc_html__( 'Number of Notes By', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'rows',
				'render_type' => 'template',
				'options'     => [
					'rows'   => esc_html__( 'Columns × Rows', 'practice-problems-el' ),
					'custom' => esc_html__( 'Custom Count', 'practice-problems-el' ),
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'       => esc_html__( 'Custom Number of Notes', 'practice-problems-el' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 6,
				'min'         => 1,
				'max'         => 100,
				'step'        => 1,
				'render_type' => 'template',
				'condition'   => [ 'limit_by' => 'custom' ],
			]
		);

		$this->add_control(
			'filter_course',
			[
				'label'       => esc_html__( 'Filter by Course', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->get_course_options(),
				'description' => esc_html__( 'Show notes belonging to a specific course, or select all.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'filter_chapter',
			[
				'label'       => esc_html__( 'Filter by Chapter', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->get_chapter_options(),
				'description' => esc_html__( 'Show notes belonging to a specific chapter, or select all.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'   => esc_html__( 'Order By', 'practice-problems-el' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'menu_order',
				'options' => [
					'menu_order' => esc_html__( 'Display Order / Menu Order', 'practice-problems-el' ),
					'date'       => esc_html__( 'Published Date', 'practice-problems-el' ),
					'title'      => esc_html__( 'Title', 'practice-problems-el' ),
					'rand'       => esc_html__( 'Random', 'practice-problems-el' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => esc_html__( 'Order', 'practice-problems-el' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => [
					'ASC'  => esc_html__( 'Ascending (ASC)', 'practice-problems-el' ),
					'DESC' => esc_html__( 'Descending (DESC)', 'practice-problems-el' ),
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Card Content Options
		========================================================= */
		$this->start_controls_section(
			'section_card_options',
			[
				'label' => esc_html__( 'Card Content & Options', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_eyebrow',
			[
				'label'        => esc_html__( 'Show Eyebrow (Course · Chapter)', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'eyebrow_separator',
			[
				'label'     => esc_html__( 'Eyebrow Separator', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => ' · ',
				'condition' => [ 'show_eyebrow' => 'yes' ],
			]
		);

		$this->add_control(
			'show_description',
			[
				'label'        => esc_html__( 'Show Summary Description', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'desc_max_words',
			[
				'label'       => esc_html__( 'Description Max Words', 'practice-problems-el' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 25,
				'min'         => 5,
				'max'         => 100,
				'condition'   => [ 'show_description' => 'yes' ],
				'description' => esc_html__( 'Truncate description after this number of words (0 for full text).', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'show_link',
			[
				'label'        => esc_html__( 'Show "Read notes" Link', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'link_text',
			[
				'label'     => esc_html__( 'Link Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read notes', 'practice-problems-el' ),
				'condition' => [ 'show_link' => 'yes' ],
			]
		);

		$this->add_control(
			'card_clickable',
			[
				'label'        => esc_html__( 'Make Entire Card Clickable', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Allows users to click anywhere on the card to open the note page.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'card_link_target',
			[
				'label'       => esc_html__( 'Card Link Destination', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'topic_note',
				'options'     => [
					'topic_note' => esc_html__( 'Elementor Single Page (/topic-note/?note=slug)', 'practice-problems-el' ),
					'cpt'        => esc_html__( 'Default Note URL (/notes/topic/)', 'practice-problems-el' ),
				],
				'description' => esc_html__( 'Choose whether cards open the Elementor Single Topic Note page or standard post URL.', 'practice-problems-el' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Layout & Grid Columns
		========================================================= */
		$this->start_controls_section(
			'section_layout_settings',
			[
				'label' => esc_html__( 'Grid Layout', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'grid_columns',
			[
				'label'          => esc_html__( 'Columns', 'practice-problems-el' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'render_type'    => 'template',
				'options'        => [
					'1' => '1 Column',
					'2' => '2 Columns',
					'3' => '3 Columns',
					'4' => '4 Columns',
					'5' => '5 Columns',
					'6' => '6 Columns',
				],
				'selectors'      => [
					'{{WRAPPER}} .tn-grid' => '--tn-grid-cols: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'grid_rows',
			[
				'label'       => esc_html__( 'Rows', 'practice-problems-el' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 2,
				'min'         => 1,
				'max'         => 12,
				'step'        => 1,
				'render_type' => 'template',
				'description' => esc_html__( 'Number of rows to show. (Total Notes = Columns × Rows)', 'practice-problems-el' ),
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label'      => esc_html__( 'Columns Gap', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 80 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 24 ],
				'selectors'  => [
					'{{WRAPPER}} .tn-grid' => '--tn-grid-col-gap: {{SIZE}}{{UNIT}}; column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label'      => esc_html__( 'Rows Gap', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 80 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 24 ],
				'selectors'  => [
					'{{WRAPPER}} .tn-grid' => '--tn-grid-row-gap: {{SIZE}}{{UNIT}}; row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Section Header
		========================================================= */
		$this->start_controls_section(
			'section_header_style',
			[
				'label'     => esc_html__( 'Section Header', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_header' => 'yes' ],
			]
		);

		$this->add_control(
			'header_color',
			[
				'label'     => esc_html__( 'Title Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .tn-grid-section-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'header_typography',
				'selector' => '{{WRAPPER}} .tn-grid-section-title',
			]
		);

		$this->add_responsive_control(
			'header_margin_bottom',
			[
				'label'      => esc_html__( 'Margin Bottom', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [ 'unit' => 'px', 'size' => 28 ],
				'selectors'  => [
					'{{WRAPPER}} .tn-grid-section-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Card Box Style
		========================================================= */
		$this->start_controls_section(
			'section_card_style',
			[
				'label' => esc_html__( 'Card Box', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_card_box' );

		// Normal State
		$this->start_controls_tab(
			'tab_card_normal',
			[ 'label' => esc_html__( 'Normal', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'card_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .tn-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'card_border',
				'selector'  => '{{WRAPPER}} .tn-card',
				'separator' => 'before',
				'fields_options' => [
					'border' => [ 'default' => 'solid' ],
					'width'  => [ 'default' => [ 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'unit' => 'px' ] ],
					'color'  => [ 'default' => '#e5e7eb' ],
				],
			]
		);

		$this->add_responsive_control(
			'card_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [ 'top' => '16', 'right' => '16', 'bottom' => '16', 'left' => '16', 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .tn-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .tn-card',
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_card_hover',
			[ 'label' => esc_html__( 'Hover', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'card_bg_hover',
			[
				'label'     => esc_html__( 'Hover Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tn-card:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_border_hover',
			[
				'label'     => esc_html__( 'Hover Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#d1d5db',
				'selectors' => [
					'{{WRAPPER}} .tn-card:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_hover_translate_y',
			[
				'label'      => esc_html__( 'Hover Lift (Y Translate)', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => -20, 'max' => 10 ] ],
				'default'    => [ 'unit' => 'px', 'size' => -4 ],
				'selectors'  => [
					'{{WRAPPER}} .tn-card:hover' => 'transform: translateY({{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_hover_shadow',
				'selector' => '{{WRAPPER}} .tn-card:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => '28', 'right' => '26', 'bottom' => '28', 'left' => '26', 'unit' => 'px' ],
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} .tn-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Eyebrow
		========================================================= */
		$this->start_controls_section(
			'section_eyebrow_style',
			[
				'label'     => esc_html__( 'Eyebrow (Course · Chapter)', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_eyebrow' => 'yes' ],
			]
		);

		$this->add_control(
			'eyebrow_color',
			[
				'label'     => esc_html__( 'Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#16a34a',
				'selectors' => [
					'{{WRAPPER}} .tn-card-eyebrow' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'eyebrow_typography',
				'selector' => '{{WRAPPER}} .tn-card-eyebrow',
			]
		);

		$this->add_responsive_control(
			'eyebrow_margin_bottom',
			[
				'label'      => esc_html__( 'Margin Bottom', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [ 'unit' => 'px', 'size' => 12 ],
				'selectors'  => [
					'{{WRAPPER}} .tn-card-eyebrow' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Title
		========================================================= */
		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .tn-card-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .tn-card:hover .tn-card-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .tn-card-title',
			]
		);

		$this->add_responsive_control(
			'title_margin_bottom',
			[
				'label'      => esc_html__( 'Margin Bottom', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [ 'unit' => 'px', 'size' => 12 ],
				'selectors'  => [
					'{{WRAPPER}} .tn-card-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Description
		========================================================= */
		$this->start_controls_section(
			'section_desc_style',
			[
				'label'     => esc_html__( 'Description', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_description' => 'yes' ],
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label'     => esc_html__( 'Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4b5563',
				'selectors' => [
					'{{WRAPPER}} .tn-card-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} .tn-card-desc',
			]
		);

		$this->add_responsive_control(
			'desc_margin_bottom',
			[
				'label'      => esc_html__( 'Margin Bottom', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [ 'unit' => 'px', 'size' => 18 ],
				'selectors'  => [
					'{{WRAPPER}} .tn-card-desc' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — "Read notes" Link
		========================================================= */
		$this->start_controls_section(
			'section_link_style',
			[
				'label'     => esc_html__( 'Read Notes Link', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_link' => 'yes' ],
			]
		);

		$this->add_control(
			'link_color',
			[
				'label'     => esc_html__( 'Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#16a34a',
				'selectors' => [
					'{{WRAPPER}} .tn-card-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#15803d',
				'selectors' => [
					'{{WRAPPER}} .tn-card:hover .tn-card-link, {{WRAPPER}} .tn-card-link:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'link_typography',
				'selector' => '{{WRAPPER}} .tn-card-link',
			]
		);

		$this->add_responsive_control(
			'link_arrow_gap',
			[
				'label'      => esc_html__( 'Arrow Spacing', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [ 'unit' => 'px', 'size' => 6 ],
				'selectors'  => [
					'{{WRAPPER}} .tn-card-link' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$s = $this->get_settings_for_display();

		// Determine posts per page from Rows × Columns or Custom
		$cols = ! empty( $s['grid_columns'] ) ? intval( $s['grid_columns'] ) : 3;
		$rows = ! empty( $s['grid_rows'] ) ? intval( $s['grid_rows'] ) : 2;

		if ( isset( $s['limit_by'] ) && 'custom' === $s['limit_by'] ) {
			$posts_to_show = ! empty( $s['posts_per_page'] ) ? intval( $s['posts_per_page'] ) : 6;
		} else {
			$posts_to_show = max( 1, $cols * $rows );
		}

		// Query arguments
		$query_args = [
			'post_type'      => 'math_note',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_to_show,
			'orderby'        => ! empty( $s['orderby'] ) ? $s['orderby'] : 'menu_order',
			'order'          => ! empty( $s['order'] ) ? $s['order'] : 'ASC',
		];

		// Tax query filters
		$tax_query = [];
		if ( ! empty( $s['filter_course'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'math_course',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $s['filter_course'] ),
			];
		}
		if ( ! empty( $s['filter_chapter'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'math_chapter',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $s['filter_chapter'] ),
			];
		}
		if ( ! empty( $tax_query ) ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$query_args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $query_args );
		?>

		<div class="tn-grid-container">

			<?php if ( 'yes' === $s['show_header'] && ! empty( $s['header_title'] ) ) : 
				$h_tag = ! empty( $s['header_tag'] ) ? $s['header_tag'] : 'h2';
			?>
				<<?php echo esc_attr( $h_tag ); ?> class="tn-grid-section-title">
					<?php echo esc_html( $s['header_title'] ); ?>
				</<?php echo esc_attr( $h_tag ); ?>>
			<?php endif; ?>

			<?php if ( $query->have_posts() ) : ?>
				<div class="tn-grid">
					<?php while ( $query->have_posts() ) : $query->the_post(); 
						$post_id   = get_the_ID();
						$title     = get_the_title();
						$permalink = get_permalink();
						$link_target = ! empty( $s['card_link_target'] ) ? $s['card_link_target'] : 'topic_note';
						if ( 'topic_note' === $link_target ) {
							$topic_page = get_page_by_path( 'topic-note' );
							$base_topic_url = ( $topic_page && 'publish' === $topic_page->post_status ) ? get_permalink( $topic_page->ID ) : home_url( '/topic-note/' );
							$permalink = add_query_arg( 'note', get_post_field( 'post_name', $post_id ), $base_topic_url );
						}
						$summary   = get_post_meta( $post_id, '_mn_summary', true );

						// Fallback description if summary is empty
						if ( empty( $summary ) ) {
							$summary = wp_strip_all_tags( get_the_excerpt() );
						}

						// Truncate summary if max words is set
						$max_words = ! empty( $s['desc_max_words'] ) ? intval( $s['desc_max_words'] ) : 0;
						if ( $max_words > 0 && ! empty( $summary ) ) {
							$summary = wp_trim_words( $summary, $max_words, '...' );
						}

						// Taxonomies for eyebrow
						$course_names  = wp_get_post_terms( $post_id, 'math_course', [ 'fields' => 'names' ] );
						$chapter_names = wp_get_post_terms( $post_id, 'math_chapter', [ 'fields' => 'names' ] );
						$course_str    = ( ! empty( $course_names ) && ! is_wp_error( $course_names ) ) ? $course_names[0] : '';
						$chapter_str   = ( ! empty( $chapter_names ) && ! is_wp_error( $chapter_names ) ) ? $chapter_names[0] : '';

						$sep = ! empty( $s['eyebrow_separator'] ) ? $s['eyebrow_separator'] : ' · ';
						$eyebrow_parts = [];
						if ( $course_str ) {
							$eyebrow_parts[] = $course_str;
						}
						if ( $chapter_str ) {
							$eyebrow_parts[] = $chapter_str;
						}
						$eyebrow_text = strtoupper( implode( $sep, $eyebrow_parts ) );

						$is_card_link = 'yes' === $s['card_clickable'];
						$target_attr  = ( 'yes' === $s['open_in_new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
					?>

						<<?php echo $is_card_link ? 'a href="' . esc_url( $permalink ) . '"' . $target_attr : 'div'; ?> class="tn-card<?php echo $is_card_link ? ' is-clickable' : ''; ?>">
							
							<div class="tn-card-content">
								<?php if ( 'yes' === $s['show_eyebrow'] && ! empty( $eyebrow_text ) ) : ?>
									<span class="tn-card-eyebrow"><?php echo esc_html( $eyebrow_text ); ?></span>
								<?php endif; ?>

								<h3 class="tn-card-title"><?php echo esc_html( $title ); ?></h3>

								<?php if ( 'yes' === $s['show_description'] && ! empty( $summary ) ) : ?>
									<p class="tn-card-desc"><?php echo esc_html( $summary ); ?></p>
								<?php endif; ?>
							</div>

							<?php if ( 'yes' === $s['show_link'] ) : ?>
								<div class="tn-card-link-wrapper">
									<span class="tn-card-link">
										<span><?php echo esc_html( $s['link_text'] ); ?></span>
										<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
											<line x1="5" y1="12" x2="19" y2="12"></line>
											<polyline points="12 5 19 12 12 19"></polyline>
										</svg>
									</span>
								</div>
							<?php endif; ?>

						</<?php echo $is_card_link ? 'a' : 'div'; ?>>

					<?php endwhile; wp_reset_postdata(); ?>
				</div>
			<?php else : ?>
				<p class="tn-grid-empty"><?php esc_html_e( 'No topic notes found.', 'practice-problems-el' ); ?></p>
			<?php endif; ?>

		</div>
		<?php
	}
}
