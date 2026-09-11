<?php
/**
 * Course Listing & Filter Elementor Widget.
 *
 * Production-grade WordPress Elementor Widget for Course Search,
 * Dynamic Level Filter, AJAX Results, Responsive Grid, and WAI-ARIA Accessibility.
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
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Course_Listing_Widget
 */
class Course_Listing_Widget extends Widget_Base {

	public function get_name() {
		return 'course_listing';
	}

	public function get_title() {
		return esc_html__( 'Course Listing & Filter', 'practice-problems-el' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return [ 'practice-elements', 'general' ];
	}

	public function get_keywords() {
		return [ 'course', 'listing', 'filter', 'search', 'level', 'beginner', 'intermediate', 'advanced', 'ajax', 'grid' ];
	}

	public function get_style_depends() {
		return [ 'course-listing-frontend' ];
	}

	public function get_script_depends() {
		return [ 'course-listing-frontend' ];
	}

	/**
	 * Helper: Fetch Course Category options.
	 */
	private function get_category_options() {
		$options = [];
		$terms = get_terms( [ 'taxonomy' => 'course_category', 'hide_empty' => false ] );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	/**
	 * Helper: Fetch Course Level options.
	 */
	private function get_level_options() {
		$options = [];
		$terms = get_terms( [ 'taxonomy' => 'course_level', 'hide_empty' => false ] );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	/**
	 * Register Widget Controls.
	 */
	protected function register_controls() {

		/* =========================================================
		   CONTENT TAB — Grid Layout (Columns & Rows)
		========================================================= */
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Grid Layout (Columns & Rows)', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'grid_columns',
			[
				'label'          => esc_html__( 'Columns per Row', 'practice-problems-el' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'render_type'    => 'template',
				'options'        => [
					'1' => esc_html__( '1 Column', 'practice-problems-el' ),
					'2' => esc_html__( '2 Columns', 'practice-problems-el' ),
					'3' => esc_html__( '3 Columns', 'practice-problems-el' ),
					'4' => esc_html__( '4 Columns', 'practice-problems-el' ),
					'5' => esc_html__( '5 Columns', 'practice-problems-el' ),
					'6' => esc_html__( '6 Columns', 'practice-problems-el' ),
				],
				'selectors'      => [
					'{{WRAPPER}} .cl-courses-grid' => '--cl-grid-columns: {{VALUE}}; grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				],
			]
		);

		$this->add_control(
			'grid_rows',
			[
				'label'       => esc_html__( 'Number of Rows', 'practice-problems-el' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 2,
				'min'         => 1,
				'max'         => 12,
				'step'        => 1,
				'render_type' => 'template',
				'description' => esc_html__( 'Total courses shown per page = Columns × Rows (e.g. 3 columns × 2 rows = 6 courses).', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'custom_posts_count',
			[
				'label'        => esc_html__( 'Custom Course Count Override', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'render_type'  => 'template',
				'description'  => esc_html__( 'Enable to override Columns × Rows and set an exact number of courses per page.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'       => esc_html__( 'Custom Courses Count', 'practice-problems-el' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 6,
				'min'         => 1,
				'max'         => 60,
				'step'        => 1,
				'render_type' => 'template',
				'condition'   => [ 'custom_posts_count' => 'yes' ],
				'description' => esc_html__( 'Custom number of courses to display per page.', 'practice-problems-el' ),
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label'      => esc_html__( 'Columns Gap', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 60 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 24 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-courses-grid' => '--cl-col-gap: {{SIZE}}{{UNIT}}; column-gap: {{SIZE}}{{UNIT}};',
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
					'px' => [ 'min' => 0, 'max' => 60 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 24 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-courses-grid' => '--cl-row-gap: {{SIZE}}{{UNIT}}; row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Query
		========================================================= */
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Course Query', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'query_categories',
			[
				'label'       => esc_html__( 'Filter by Categories', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_category_options(),
				'multiple'    => true,
				'label_block' => true,
				'description' => esc_html__( 'Leave empty to include all course categories.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'query_levels',
			[
				'label'       => esc_html__( 'Limit Available Levels', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_level_options(),
				'multiple'    => true,
				'label_block' => true,
				'description' => esc_html__( 'Leave empty to show all levels in the filter bar.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'query_orderby',
			[
				'label'   => esc_html__( 'Order By', 'practice-problems-el' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'course_order',
				'options' => [
					'course_order' => esc_html__( 'Course Order (Custom Field)', 'practice-problems-el' ),
					'date'         => esc_html__( 'Date Published', 'practice-problems-el' ),
					'title'        => esc_html__( 'Course Title', 'practice-problems-el' ),
					'modified'     => esc_html__( 'Last Modified', 'practice-problems-el' ),
					'menu_order'   => esc_html__( 'Menu Order', 'practice-problems-el' ),
					'rand'         => esc_html__( 'Random', 'practice-problems-el' ),
				],
			]
		);

		$this->add_control(
			'query_order',
			[
				'label'   => esc_html__( 'Order Direction', 'practice-problems-el' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => [
					'ASC'  => esc_html__( 'Ascending (ASC)', 'practice-problems-el' ),
					'DESC' => esc_html__( 'Descending (DESC)', 'practice-problems-el' ),
				],
			]
		);

		$this->add_control(
			'exclude_ids',
			[
				'label'       => esc_html__( 'Exclude Course IDs', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'e.g. 12, 45, 99',
				'description' => esc_html__( 'Comma-separated course IDs to exclude.', 'practice-problems-el' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Search
		========================================================= */
		$this->start_controls_section(
			'section_search',
			[
				'label' => esc_html__( 'Search Bar', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_search',
			[
				'label'        => esc_html__( 'Enable Search Bar', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'search_placeholder',
			[
				'label'     => esc_html__( 'Placeholder Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Search courses…', 'practice-problems-el' ),
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'search_aria_label',
			[
				'label'     => esc_html__( 'Accessibility Label', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Search courses', 'practice-problems-el' ),
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'search_icon',
			[
				'label'     => esc_html__( 'Search Icon', 'practice-problems-el' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-search',
					'library' => 'fa-solid',
				],
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'search_icon_position',
			[
				'label'     => esc_html__( 'Icon Position', 'practice-problems-el' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'  => [ 'title' => esc_html__( 'Left', 'practice-problems-el' ), 'icon' => 'eicon-h-align-left' ],
					'right' => [ 'title' => esc_html__( 'Right', 'practice-problems-el' ), 'icon' => 'eicon-h-align-right' ],
				],
				'default'   => 'left',
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'search_behavior',
			[
				'label'     => esc_html__( 'Search Behavior', 'practice-problems-el' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'live_debounce',
				'options'   => [
					'live_debounce' => esc_html__( 'Live + Debounce (Recommended)', 'practice-problems-el' ),
					'live'          => esc_html__( 'Instant Live Search', 'practice-problems-el' ),
					'submit'        => esc_html__( 'Submit on Enter / Button Click', 'practice-problems-el' ),
				],
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'search_debounce_delay',
			[
				'label'     => esc_html__( 'Debounce Delay (ms)', 'practice-problems-el' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 300,
				'min'       => 100,
				'max'       => 1500,
				'step'      => 50,
				'condition' => [
					'enable_search'   => 'yes',
					'search_behavior' => 'live_debounce',
				],
			]
		);

		$this->add_control(
			'search_min_chars',
			[
				'label'     => esc_html__( 'Minimum Characters', 'practice-problems-el' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'search_fields',
			[
				'label'       => esc_html__( 'Search In Fields', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'default'     => [ 'title', 'description', 'category', 'level' ],
				'options'     => [
					'title'       => esc_html__( 'Course Title', 'practice-problems-el' ),
					'description' => esc_html__( 'Course Description', 'practice-problems-el' ),
					'slug'        => esc_html__( 'Course Slug', 'practice-problems-el' ),
					'category'    => esc_html__( 'Course Category', 'practice-problems-el' ),
					'level'       => esc_html__( 'Course Level', 'practice-problems-el' ),
					'instructor'  => esc_html__( 'Instructor', 'practice-problems-el' ),
					'id'          => esc_html__( 'Course ID', 'practice-problems-el' ),
				],
				'label_block' => true,
				'condition'   => [ 'enable_search' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Level Filter
		========================================================= */
		$this->start_controls_section(
			'section_level_filter',
			[
				'label' => esc_html__( 'Level Filter', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_level_filter',
			[
				'label'        => esc_html__( 'Enable Level Filter', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'filter_show_all',
			[
				'label'        => esc_html__( 'Show "All" Button', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => [ 'enable_level_filter' => 'yes' ],
			]
		);

		$this->add_control(
			'filter_all_label',
			[
				'label'     => esc_html__( '"All" Button Label', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'All', 'practice-problems-el' ),
				'condition' => [
					'enable_level_filter' => 'yes',
					'filter_show_all'     => 'yes',
				],
			]
		);

		$this->add_control(
			'filter_show_count',
			[
				'label'        => esc_html__( 'Show Course Counts', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'return_value' => 'yes',
				'condition'    => [ 'enable_level_filter' => 'yes' ],
				'description'  => esc_html__( 'e.g. All (24), Beginner (8), Intermediate (10)', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'filter_sort',
			[
				'label'     => esc_html__( 'Level Sort Order', 'practice-problems-el' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'meta_order',
				'options'   => [
					'meta_order' => esc_html__( 'Sort Order Term Meta (Dashboard)', 'practice-problems-el' ),
					'name_asc'   => esc_html__( 'Name (A-Z)', 'practice-problems-el' ),
					'name_desc'  => esc_html__( 'Name (Z-A)', 'practice-problems-el' ),
					'count_desc' => esc_html__( 'Highest Count First', 'practice-problems-el' ),
				],
				'condition' => [ 'enable_level_filter' => 'yes' ],
			]
		);

		$this->add_control(
			'filter_mode',
			[
				'label'       => esc_html__( 'Filter Mode', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'ajax',
				'options'     => [
					'ajax'   => esc_html__( 'AJAX (Fast & Dynamic)', 'practice-problems-el' ),
					'client' => esc_html__( 'Client-side (Small Datasets)', 'practice-problems-el' ),
				],
				'condition'   => [ 'enable_level_filter' => 'yes' ],
			]
		);

		$this->add_control(
			'sync_url_query',
			[
				'label'        => esc_html__( 'Shareable URL Sync', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Updates browser address bar (/courses/?search=...&level=...) without reloading.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'mobile_overflow',
			[
				'label'       => esc_html__( 'Mobile Filter Layout', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'scroll',
				'options'     => [
					'scroll' => esc_html__( 'Horizontal Scroll (Swipeable)', 'practice-problems-el' ),
					'wrap'   => esc_html__( 'Wrap onto Multiple Lines', 'practice-problems-el' ),
				],
				'condition'   => [ 'enable_level_filter' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Course Card Content
		========================================================= */
		$this->start_controls_section(
			'section_card_elements',
			[
				'label' => esc_html__( 'Course Card Elements', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_thumbnail',
			[
				'label'        => esc_html__( 'Show Thumbnail / Image', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_icon',
			[
				'label'        => esc_html__( 'Show Course Icon', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_level_badge',
			[
				'label'        => esc_html__( 'Show Level Badge', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_category_badge',
			[
				'label'        => esc_html__( 'Show Category Badge', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_custom_badge',
			[
				'label'        => esc_html__( 'Show Status Badge (e.g. Featured)', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'        => esc_html__( 'Show Course Title', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'     => esc_html__( 'Title HTML Tag', 'practice-problems-el' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => [
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'div'  => 'div',
					'span' => 'span',
				],
				'condition' => [ 'show_title' => 'yes' ],
			]
		);

		$this->add_control(
			'show_description',
			[
				'label'        => esc_html__( 'Show Description', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_chapters',
			[
				'label'        => esc_html__( 'Show Chapter Count', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'chapter_label',
			[
				'label'     => esc_html__( 'Chapters Label', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Chapters', 'practice-problems-el' ),
				'condition' => [ 'show_chapters' => 'yes' ],
			]
		);

		$this->add_control(
			'show_problems',
			[
				'label'        => esc_html__( 'Show Problem Count', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'problem_label',
			[
				'label'     => esc_html__( 'Problems Label', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Problems', 'practice-problems-el' ),
				'condition' => [ 'show_problems' => 'yes' ],
			]
		);

		$this->add_control(
			'show_duration',
			[
				'label'        => esc_html__( 'Show Duration', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_instructor',
			[
				'label'        => esc_html__( 'Show Instructor', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_cta',
			[
				'label'        => esc_html__( 'Show CTA Button', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'cta_text',
			[
				'label'     => esc_html__( 'CTA Button Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'View Course', 'practice-problems-el' ),
				'condition' => [ 'show_cta' => 'yes' ],
			]
		);

		$this->add_control(
			'cta_icon',
			[
				'label'     => esc_html__( 'CTA Arrow / Icon', 'practice-problems-el' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-arrow-right',
					'library' => 'fa-solid',
				],
				'condition' => [ 'show_cta' => 'yes' ],
			]
		);

		$this->add_control(
			'cta_new_tab',
			[
				'label'        => esc_html__( 'Open Link in New Tab', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'return_value' => 'yes',
				'condition'    => [ 'show_cta' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Empty & Loading States
		========================================================= */
		$this->start_controls_section(
			'section_states',
			[
				'label' => esc_html__( 'Empty & Loading States', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'empty_icon',
			[
				'label'   => esc_html__( 'Empty State Icon', 'practice-problems-el' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-search',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'empty_title',
			[
				'label'   => esc_html__( 'Empty Title', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'No courses found', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'empty_desc',
			[
				'label'   => esc_html__( 'Empty Description', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Try another search keyword or choose a different level filter.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'empty_show_reset',
			[
				'label'        => esc_html__( 'Show Reset Filters Button', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'empty_reset_label',
			[
				'label'     => esc_html__( 'Reset Button Label', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Reset Filters', 'practice-problems-el' ),
				'condition' => [ 'empty_show_reset' => 'yes' ],
			]
		);

		$this->add_control(
			'loading_type',
			[
				'label'   => esc_html__( 'Loading State Effect', 'practice-problems-el' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'skeleton',
				'options' => [
					'skeleton' => esc_html__( 'Skeleton Cards Shimmer (Recommended)', 'practice-problems-el' ),
					'spinner'  => esc_html__( 'Circular Spinner', 'practice-problems-el' ),
					'text'     => esc_html__( 'Simple Text ("Loading courses...")', 'practice-problems-el' ),
					'none'     => esc_html__( 'None', 'practice-problems-el' ),
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Pagination
		========================================================= */
		$this->start_controls_section(
			'section_pagination',
			[
				'label' => esc_html__( 'Pagination', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_pagination',
			[
				'label'        => esc_html__( 'Enable Pagination', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label'     => esc_html__( 'Pagination Type', 'practice-problems-el' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'numbers',
				'options'   => [
					'numbers'         => esc_html__( 'Page Numbers & Prev/Next', 'practice-problems-el' ),
					'load_more'       => esc_html__( 'Load More Button', 'practice-problems-el' ),
					'infinite_scroll' => esc_html__( 'Infinite Scroll', 'practice-problems-el' ),
				],
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'load_more_text',
			[
				'label'     => esc_html__( 'Load More Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Load More Courses', 'practice-problems-el' ),
				'condition' => [
					'enable_pagination' => 'yes',
					'pagination_type'   => 'load_more',
				],
			]
		);

		$this->add_control(
			'prev_text',
			[
				'label'     => esc_html__( 'Previous Button Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Previous', 'practice-problems-el' ),
				'condition' => [
					'enable_pagination' => 'yes',
					'pagination_type'   => 'numbers',
				],
			]
		);

		$this->add_control(
			'next_text',
			[
				'label'     => esc_html__( 'Next Button Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Next', 'practice-problems-el' ),
				'condition' => [
					'enable_pagination' => 'yes',
					'pagination_type'   => 'numbers',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Global Presets & CSS Variables
		========================================================= */
		$this->start_controls_section(
			'section_style_global',
			[
				'label' => esc_html__( 'Global Style & Theme Colors', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'style_primary_color',
			[
				'label'     => esc_html__( 'Primary Brand Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#238E23',
				'selectors' => [
					'{{WRAPPER}} .cl-widget-container' => '--cl-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_text_color',
			[
				'label'     => esc_html__( 'Dark Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .cl-widget-container' => '--cl-text: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_muted_color',
			[
				'label'     => esc_html__( 'Muted / Secondary Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .cl-widget-container' => '--cl-muted: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e2e8f0',
				'selectors' => [
					'{{WRAPPER}} .cl-widget-container' => '--cl-border: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_card_bg',
			[
				'label'     => esc_html__( 'Card Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .cl-widget-container' => '--cl-card-bg: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'style_border_radius',
			[
				'label'      => esc_html__( 'Global Corner Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 32 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 12 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-widget-container' => '--cl-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Search Box Style
		========================================================= */
		$this->start_controls_section(
			'section_style_search',
			[
				'label'     => esc_html__( 'Search Box Style', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'search_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [ 'min' => 200, 'max' => 1200 ],
					'%'  => [ 'min' => 20, 'max' => 100 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 480 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-search-wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'search_height',
			[
				'label'      => esc_html__( 'Input Height', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 36, 'max' => 64 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 44 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-search-input' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'search_typography',
				'selector' => '{{WRAPPER}} .cl-search-input',
			]
		);

		$this->add_control(
			'search_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f8fafc',
				'selectors' => [
					'{{WRAPPER}} .cl-search-input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'search_text_color',
			[
				'label'     => esc_html__( 'Input Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-search-input' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'search_placeholder_color',
			[
				'label'     => esc_html__( 'Placeholder Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-search-input::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'search_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-search-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'search_border',
				'selector' => '{{WRAPPER}} .cl-search-input',
			]
		);

		$this->add_control(
			'search_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 10 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-search-input' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'search_focus_heading',
			[
				'label'     => esc_html__( 'Focus State Ring', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'search_focus_border_color',
			[
				'label'     => esc_html__( 'Focus Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-search-input:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'search_focus_ring_color',
			[
				'label'     => esc_html__( 'Focus Ring Glow Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(35, 142, 35, 0.25)',
				'selectors' => [
					'{{WRAPPER}} .cl-search-input:focus' => 'box-shadow: 0 0 0 3px {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Level Filter Buttons
		========================================================= */
		$this->start_controls_section(
			'section_style_filter',
			[
				'label'     => esc_html__( 'Level Filter Buttons', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_level_filter' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'filter_alignment',
			[
				'label'     => esc_html__( 'Alignment', 'practice-problems-el' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [ 'title' => esc_html__( 'Left', 'practice-problems-el' ), 'icon' => 'eicon-h-align-left' ],
					'center'     => [ 'title' => esc_html__( 'Center', 'practice-problems-el' ), 'icon' => 'eicon-h-align-center' ],
					'flex-end'   => [ 'title' => esc_html__( 'Right', 'practice-problems-el' ), 'icon' => 'eicon-h-align-right' ],
				],
				'default'   => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-nav' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'filter_typography',
				'selector' => '{{WRAPPER}} .cl-filter-btn',
			]
		);

		$this->add_responsive_control(
			'filter_gap',
			[
				'label'      => esc_html__( 'Gap Between Buttons', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 4, 'max' => 32 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 10 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-filter-nav' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filter_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => 8,
					'right'    => 18,
					'bottom'   => 8,
					'left'     => 18,
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .cl-filter-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'filter_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 24 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-filter-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'filter_button_tabs' );

		// Normal State
		$this->start_controls_tab(
			'filter_tab_normal',
			[ 'label' => esc_html__( 'Normal', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'filter_normal_text',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#475569',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_normal_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f1f5f9',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_normal_border',
			[
				'label'     => esc_html__( 'Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'transparent',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'filter_tab_hover',
			[ 'label' => esc_html__( 'Hover', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'filter_hover_text',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_hover_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e2e8f0',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_hover_border',
			[
				'label'     => esc_html__( 'Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Active State
		$this->start_controls_tab(
			'filter_tab_active',
			[ 'label' => esc_html__( 'Active', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'filter_active_text',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn.is-active, {{WRAPPER}} .cl-filter-btn[aria-pressed="true"]' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_active_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#238E23',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn.is-active, {{WRAPPER}} .cl-filter-btn[aria-pressed="true"]' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_active_border',
			[
				'label'     => esc_html__( 'Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#238E23',
				'selectors' => [
					'{{WRAPPER}} .cl-filter-btn.is-active, {{WRAPPER}} .cl-filter-btn[aria-pressed="true"]' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Course Grid & Card
		========================================================= */
		$this->start_controls_section(
			'section_style_grid',
			[
				'label' => esc_html__( 'Course Grid Layout', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
				'options'        => [
					'1' => '1 Column',
					'2' => '2 Columns',
					'3' => '3 Columns',
					'4' => '4 Columns',
				],
				'selectors'      => [
					'{{WRAPPER}} .cl-courses-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				],
			]
		);

		$this->add_responsive_control(
			'grid_gap',
			[
				'label'      => esc_html__( 'Grid Gap', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 8, 'max' => 48 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 24 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-courses-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Course Card
		========================================================= */
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => esc_html__( 'Course Card Box', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .cl-course-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .cl-course-card',
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 14 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-course-card' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .cl-course-card',
			]
		);

		$this->add_control(
			'card_hover_heading',
			[
				'label'     => esc_html__( 'Card Hover Effects', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_hover_box_shadow',
				'label'    => esc_html__( 'Hover Shadow', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .cl-course-card:hover',
			]
		);

		$this->add_control(
			'card_hover_transform',
			[
				'label'        => esc_html__( 'Hover Lift Effect', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Card Thumbnail & Image
		========================================================= */
		$this->start_controls_section(
			'section_style_image',
			[
				'label'     => esc_html__( 'Course Thumbnail & Image', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_thumbnail' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label'      => esc_html__( 'Image Height', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 120, 'max' => 380 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 190 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-card-thumb' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Corner Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 10 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-card-thumb' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Title & Description
		========================================================= */
		$this->start_controls_section(
			'section_style_content',
			[
				'label' => esc_html__( 'Title, Description & Meta', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-card-title, {{WRAPPER}} .cl-card-title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label'     => esc_html__( 'Title Hover Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-card-title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .cl-card-title',
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label'     => esc_html__( 'Description Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .cl-card-desc' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} .cl-card-desc',
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label'     => esc_html__( 'Stats & Meta Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#475569',
				'selectors' => [
					'{{WRAPPER}} .cl-card-meta' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — CTA Button
		========================================================= */
		$this->start_controls_section(
			'section_style_cta',
			[
				'label'     => esc_html__( 'CTA Action Button', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_cta' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'cta_typography',
				'selector' => '{{WRAPPER}} .cl-card-cta-btn',
			]
		);

		$this->add_control(
			'cta_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 8 ],
				'selectors'  => [
					'{{WRAPPER}} .cl-card-cta-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'cta_style_tabs' );

		// Normal
		$this->start_controls_tab(
			'cta_tab_normal',
			[ 'label' => esc_html__( 'Normal', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'cta_btn_color',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-card-cta-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cta_btn_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-card-cta-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'cta_tab_hover',
			[ 'label' => esc_html__( 'Hover', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'cta_btn_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-card-cta-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cta_btn_hover_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cl-card-cta-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Build WP_Query args based on widget settings.
	 */
	public function get_query_args( $settings, $paged = 1, $search = '', $active_level = '' ) {
		$cols = ! empty( $settings['grid_columns'] ) ? intval( $settings['grid_columns'] ) : 3;
		$rows = ! empty( $settings['grid_rows'] ) ? intval( $settings['grid_rows'] ) : 2;

		if ( ! empty( $settings['custom_posts_count'] ) && 'yes' === $settings['custom_posts_count'] ) {
			$posts_per_page = ! empty( $settings['posts_per_page'] ) ? intval( $settings['posts_per_page'] ) : ( $cols * $rows );
		} elseif ( isset( $settings['limit_by'] ) && 'custom' === $settings['limit_by'] ) {
			$posts_per_page = ! empty( $settings['posts_per_page'] ) ? intval( $settings['posts_per_page'] ) : ( $cols * $rows );
		} else {
			$posts_per_page = max( 1, $cols * $rows );
		}
		$orderby        = ! empty( $settings['query_orderby'] ) ? sanitize_text_field( $settings['query_orderby'] ) : 'course_order';
		$order          = ! empty( $settings['query_order'] ) ? sanitize_text_field( $settings['query_order'] ) : 'ASC';

		$args = [
			'post_type'      => 'course',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'paged'          => $paged,
		];

		// Sorting
		if ( 'course_order' === $orderby ) {
			$args['meta_key'] = '_course_display_order';
			$args['orderby']  = 'meta_value_num date';
			$args['order']    = $order;
		} else {
			$args['orderby'] = $orderby;
			$args['order']   = $order;
		}

		// Tax query construction
		$tax_query = [];

		// Active level filter (AJAX or initial)
		if ( ! empty( $active_level ) && 'all' !== $active_level ) {
			$tax_query[] = [
				'taxonomy' => 'course_level',
				'field'    => 'slug',
				'terms'    => sanitize_title( $active_level ),
			];
		} elseif ( ! empty( $settings['query_levels'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'course_level',
				'field'    => 'slug',
				'terms'    => (array) $settings['query_levels'],
			];
		}

		// Categories filter
		if ( ! empty( $settings['query_categories'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'course_category',
				'field'    => 'slug',
				'terms'    => (array) $settings['query_categories'],
			];
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		// Search keywords
		if ( ! empty( $search ) ) {
			$args['s'] = sanitize_text_field( $search );
		}

		// Exclude IDs
		if ( ! empty( $settings['exclude_ids'] ) ) {
			$ids = array_map( 'intval', array_map( 'trim', explode( ',', $settings['exclude_ids'] ) ) );
			$args['post__not_in'] = $ids;
		}

		return $args;
	}

	/**
	 * Render Widget Output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$widget_id = $this->get_id();

		$enable_search = 'yes' === $settings['enable_search'];
		$enable_filter = 'yes' === $settings['enable_level_filter'];

		// Initial server-side query for instant render & SEO
		$query_args = $this->get_query_args( $settings, 1 );
		$posts_per_page = $query_args['posts_per_page'];
		$courses_query = new \WP_Query( $query_args );

		// Setup widget config payload
		$config = [
			'widgetId'        => $widget_id,
			'postsPerPage'    => $posts_per_page,
			'searchMethod'    => $settings['search_behavior'],
			'debounceDelay'   => intval( $settings['search_debounce_delay'] ),
			'minChars'        => intval( $settings['search_min_chars'] ),
			'syncUrl'         => 'yes' === $settings['sync_url_query'],
			'filterMode'      => $settings['filter_mode'],
			'paginationType'  => $settings['pagination_type'],
			'loadingType'     => $settings['loading_type'],
			'cardSettings'    => [
				'show_thumbnail'      => $settings['show_thumbnail'],
				'show_icon'           => $settings['show_icon'],
				'show_level_badge'    => $settings['show_level_badge'],
				'show_category_badge' => $settings['show_category_badge'],
				'show_custom_badge'   => $settings['show_custom_badge'],
				'show_title'          => $settings['show_title'],
				'title_tag'           => $settings['title_tag'],
				'show_description'    => $settings['show_description'],
				'show_chapters'       => $settings['show_chapters'],
				'chapter_label'       => $settings['chapter_label'],
				'show_problems'       => $settings['show_problems'],
				'problem_label'       => $settings['problem_label'],
				'show_duration'       => $settings['show_duration'],
				'show_instructor'     => $settings['show_instructor'],
				'show_cta'            => $settings['show_cta'],
				'cta_text'            => $settings['cta_text'],
				'cta_new_tab'         => $settings['cta_new_tab'],
			],
			'categories'      => ! empty( $settings['query_categories'] ) ? (array) $settings['query_categories'] : [],
			'levels'          => ! empty( $settings['query_levels'] ) ? (array) $settings['query_levels'] : [],
			'orderby'         => $settings['query_orderby'],
			'order'           => $settings['query_order'],
			'excludeIds'      => $settings['exclude_ids'],
		];
		?>
		<div class="cl-widget-container" id="cl-widget-<?php echo esc_attr( $widget_id ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

			<!-- Top Controls: Search Bar & Level Filter Bar -->
			<div class="cl-header-controls">

				<?php if ( $enable_search ) : ?>
					<div class="cl-search-wrapper cl-icon-<?php echo esc_attr( $settings['search_icon_position'] ); ?>">
						<span class="cl-search-icon" aria-hidden="true">
							<?php
							if ( ! empty( $settings['search_icon']['value'] ) ) {
								Icons_Manager::render_icon( $settings['search_icon'], [ 'aria-hidden' => 'true' ] );
							} else {
								echo '<span class="dashicons dashicons-search"></span>';
							}
							?>
						</span>
						<input
							type="search"
							class="cl-search-input"
							placeholder="<?php echo esc_attr( $settings['search_placeholder'] ); ?>"
							aria-label="<?php echo esc_attr( $settings['search_aria_label'] ); ?>"
							autocomplete="off"
							spellcheck="false"
						/>
						<button type="button" class="cl-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'practice-problems-el' ); ?>" style="display:none;">&times;</button>
					</div>
				<?php endif; ?>

				<?php if ( $enable_filter ) : ?>
					<?php
					// Fetch levels
					$terms = get_terms( [
						'taxonomy'   => 'course_level',
						'hide_empty' => false,
					] );

					// Sort terms
					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						if ( 'meta_order' === $settings['filter_sort'] ) {
							usort( $terms, function( $a, $b ) {
								$oa = intval( get_term_meta( $a->term_id, 'level_sort_order', true ) );
								$ob = intval( get_term_meta( $b->term_id, 'level_sort_order', true ) );
								return $oa <=> $ob;
							} );
						} elseif ( 'count_desc' === $settings['filter_sort'] ) {
							usort( $terms, function( $a, $b ) {
								return $b->count <=> $a->count;
							} );
						}
					}
					$mobile_overflow_class = 'cl-overflow-' . esc_attr( $settings['mobile_overflow'] );
					?>
					<nav class="cl-filter-nav <?php echo esc_attr( $mobile_overflow_class ); ?>" role="group" aria-label="<?php esc_attr_e( 'Filter courses by level', 'practice-problems-el' ); ?>">
						<?php if ( 'yes' === $settings['filter_show_all'] ) : ?>
							<button
								type="button"
								class="cl-filter-btn is-active"
								data-level="all"
								aria-pressed="true"
							>
								<?php echo esc_html( $settings['filter_all_label'] ); ?>
								<?php if ( 'yes' === $settings['filter_show_count'] ) : ?>
									<span class="cl-filter-count" aria-hidden="true"><?php echo esc_html( $courses_query->found_posts ); ?></span>
								<?php endif; ?>
							</button>
						<?php endif; ?>

						<?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) : ?>
							<?php foreach ( $terms as $term ) : ?>
								<?php
								// If allowed levels are restricted
								if ( ! empty( $settings['query_levels'] ) && ! in_array( $term->slug, (array) $settings['query_levels'], true ) ) {
									continue;
								}
								$b_color  = get_term_meta( $term->term_id, 'level_badge_color', true ) ?: '';
								$bg_color = get_term_meta( $term->term_id, 'level_bg_color', true ) ?: '';
								$style_attr = '';
								if ( $b_color ) {
									$style_attr .= '--level-accent:' . esc_attr( $b_color ) . ';';
								}
								if ( $bg_color ) {
									$style_attr .= '--level-tint:' . esc_attr( $bg_color ) . ';';
								}
								?>
								<button
									type="button"
									class="cl-filter-btn"
									data-level="<?php echo esc_attr( $term->slug ); ?>"
									aria-pressed="false"
									style="<?php echo esc_attr( $style_attr ); ?>"
								>
									<?php echo esc_html( $term->name ); ?>
									<?php if ( 'yes' === $settings['filter_show_count'] ) : ?>
										<span class="cl-filter-count" aria-hidden="true"><?php echo esc_html( $term->count ); ?></span>
									<?php endif; ?>
								</button>
							<?php endforeach; ?>
						<?php endif; ?>
					</nav>
				<?php endif; ?>

			</div>

			<!-- Live status announcement for Screen Readers -->
			<div class="cl-sr-status" role="status" aria-live="polite" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(1px, 1px, 1px, 1px);"></div>

			<!-- Skeleton Shimmer Placeholder (Hidden while content is active) -->
			<div class="cl-loading-skeleton" style="display:none;" aria-hidden="true">
				<div class="cl-courses-grid cl-skeleton-grid">
					<?php for ( $i = 0; $i < max( 3, min( 12, $posts_per_page ) ); $i++ ) : ?>
						<div class="cl-skeleton-card">
							<div class="cl-skeleton-thumb"></div>
							<div class="cl-skeleton-body">
								<div class="cl-skeleton-pill"></div>
								<div class="cl-skeleton-line cl-line-title"></div>
								<div class="cl-skeleton-line cl-line-desc"></div>
								<div class="cl-skeleton-meta"></div>
							</div>
						</div>
					<?php endfor; ?>
				</div>
			</div>

			<!-- Course Cards Grid -->
			<div class="cl-courses-grid cl-main-grid" id="cl-grid-<?php echo esc_attr( $widget_id ); ?>">
				<?php
				if ( $courses_query->have_posts() ) {
					while ( $courses_query->have_posts() ) {
						$courses_query->the_post();
						self::render_course_card( get_the_ID(), $settings );
					}
					wp_reset_postdata();
				}
				?>
			</div>

			<!-- Empty State -->
			<div class="cl-empty-state" style="<?php echo ( ! $courses_query->have_posts() ) ? '' : 'display:none;'; ?>" role="status">
				<div class="cl-empty-icon" aria-hidden="true">
					<?php
					if ( ! empty( $settings['empty_icon']['value'] ) ) {
						Icons_Manager::render_icon( $settings['empty_icon'], [ 'aria-hidden' => 'true' ] );
					} else {
						echo '<span class="dashicons dashicons-search"></span>';
					}
					?>
				</div>
				<h3 class="cl-empty-title"><?php echo esc_html( $settings['empty_title'] ); ?></h3>
				<p class="cl-empty-desc"><?php echo esc_html( $settings['empty_desc'] ); ?></p>
				<?php if ( 'yes' === $settings['empty_show_reset'] ) : ?>
					<button type="button" class="cl-empty-reset-btn"><?php echo esc_html( $settings['empty_reset_label'] ); ?></button>
				<?php endif; ?>
			</div>

			<!-- Pagination Section -->
			<?php if ( 'yes' === $settings['enable_pagination'] && $courses_query->max_num_pages > 1 ) : ?>
				<div class="cl-pagination-wrapper cl-pagination-<?php echo esc_attr( $settings['pagination_type'] ); ?>">
					<?php if ( 'numbers' === $settings['pagination_type'] ) : ?>
						<nav class="cl-pagination-nav" aria-label="<?php esc_attr_e( 'Courses pagination', 'practice-problems-el' ); ?>" data-max-pages="<?php echo esc_attr( $courses_query->max_num_pages ); ?>">
							<button type="button" class="cl-page-btn cl-page-prev" data-page="prev" disabled>
								&larr; <?php echo esc_html( $settings['prev_text'] ); ?>
							</button>
							<div class="cl-page-numbers">
								<?php for ( $p = 1; $p <= $courses_query->max_num_pages; $p++ ) : ?>
									<button type="button" class="cl-page-number <?php echo 1 === $p ? 'is-active' : ''; ?>" data-page="<?php echo esc_attr( $p ); ?>" aria-current="<?php echo 1 === $p ? 'page' : 'false'; ?>">
										<?php echo esc_html( $p ); ?>
									</button>
								<?php endfor; ?>
							</div>
							<button type="button" class="cl-page-btn cl-page-next" data-page="next">
								<?php echo esc_html( $settings['next_text'] ); ?> &rarr;
							</button>
						</nav>
					<?php elseif ( 'load_more' === $settings['pagination_type'] ) : ?>
						<button type="button" class="cl-load-more-btn" data-page="1" data-max-pages="<?php echo esc_attr( $courses_query->max_num_pages ); ?>">
							<?php echo esc_html( $settings['load_more_text'] ); ?>
						</button>
					<?php elseif ( 'infinite_scroll' === $settings['pagination_type'] ) : ?>
						<div class="cl-infinite-sentinel" data-page="1" data-max-pages="<?php echo esc_attr( $courses_query->max_num_pages ); ?>">
							<span class="cl-spinner" aria-hidden="true"></span>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Static Card Renderer used for both Initial SSR and AJAX Responses.
	 */
	public static function render_course_card( $course_id, $settings ) {
		$title       = get_the_title( $course_id );
		$short_desc  = get_post_meta( $course_id, '_course_short_description', true );
		if ( ! $short_desc ) {
			$short_desc = get_the_excerpt( $course_id );
		}

		$custom_url  = get_post_meta( $course_id, '_course_custom_url', true );
		$course_url  = ! empty( $custom_url ) ? $custom_url : get_permalink( $course_id );
		$badge_text  = get_post_meta( $course_id, '_course_badge_text', true );
		$icon_class  = get_post_meta( $course_id, '_course_icon', true ) ?: 'dashicons-welcome-learn-more';
		$instructor  = get_post_meta( $course_id, '_course_instructor', true );
		$stats       = \PracticeProblems\Course_CPT::get_course_statistics( $course_id );

		// Taxonomies
		$levels     = wp_get_post_terms( $course_id, 'course_level' );
		$categories = wp_get_post_terms( $course_id, 'course_category' );

		$level_name  = ! empty( $levels ) && ! is_wp_error( $levels ) ? $levels[0]->name : '';
		$level_slug  = ! empty( $levels ) && ! is_wp_error( $levels ) ? $levels[0]->slug : '';
		$level_color = ! empty( $levels ) && ! is_wp_error( $levels ) ? get_term_meta( $levels[0]->term_id, 'level_badge_color', true ) : '';
		$level_bg    = ! empty( $levels ) && ! is_wp_error( $levels ) ? get_term_meta( $levels[0]->term_id, 'level_bg_color', true ) : '';

		$cat_name    = ! empty( $categories ) && ! is_wp_error( $categories ) ? $categories[0]->name : '';

		$card_show_thumb = isset( $settings['show_thumbnail'] ) ? ( 'yes' === $settings['show_thumbnail'] ) : true;
		$card_show_icon  = isset( $settings['show_icon'] ) ? ( 'yes' === $settings['show_icon'] ) : true;
		$card_show_level = isset( $settings['show_level_badge'] ) ? ( 'yes' === $settings['show_level_badge'] ) : true;
		$card_show_cat   = isset( $settings['show_category_badge'] ) ? ( 'yes' === $settings['show_category_badge'] ) : true;
		$card_show_title = isset( $settings['show_title'] ) ? ( 'yes' === $settings['show_title'] ) : true;
		$title_tag       = ! empty( $settings['title_tag'] ) ? esc_html( $settings['title_tag'] ) : 'h3';
		$card_show_desc  = isset( $settings['show_description'] ) ? ( 'yes' === $settings['show_description'] ) : true;
		$card_show_chap  = isset( $settings['show_chapters'] ) ? ( 'yes' === $settings['show_chapters'] ) : true;
		$chap_label      = ! empty( $settings['chapter_label'] ) ? $settings['chapter_label'] : esc_html__( 'Chapters', 'practice-problems-el' );
		$card_show_prob  = isset( $settings['show_problems'] ) ? ( 'yes' === $settings['show_problems'] ) : true;
		$prob_label      = ! empty( $settings['problem_label'] ) ? $settings['problem_label'] : esc_html__( 'Problems', 'practice-problems-el' );
		$card_show_dur   = isset( $settings['show_duration'] ) ? ( 'yes' === $settings['show_duration'] ) : true;
		$card_show_inst  = isset( $settings['show_instructor'] ) ? ( 'yes' === $settings['show_instructor'] ) : false;
		$card_show_cta   = isset( $settings['show_cta'] ) ? ( 'yes' === $settings['show_cta'] ) : true;
		$cta_text        = ! empty( $settings['cta_text'] ) ? $settings['cta_text'] : esc_html__( 'View Course', 'practice-problems-el' );
		$cta_target      = ( isset( $settings['cta_new_tab'] ) && 'yes' === $settings['cta_new_tab'] ) ? '_blank' : '_self';
		?>
		<article class="cl-course-card" data-course-id="<?php echo esc_attr( $course_id ); ?>" data-level="<?php echo esc_attr( $level_slug ); ?>">
			<?php if ( $card_show_thumb ) : ?>
				<div class="cl-card-thumb">
					<a href="<?php echo esc_url( $course_url ); ?>" target="<?php echo esc_attr( $cta_target ); ?>" tabindex="-1" aria-hidden="true">
						<?php if ( has_post_thumbnail( $course_id ) ) : ?>
							<?php echo get_the_post_thumbnail( $course_id, 'medium_large', [ 'class' => 'cl-thumb-img', 'alt' => esc_attr( $title ) ] ); ?>
						<?php else : ?>
							<div class="cl-thumb-placeholder">
								<?php if ( $card_show_icon ) : ?>
									<span class="cl-course-icon dashicons <?php echo esc_attr( $icon_class ); ?>"></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</a>

					<!-- Badges on top of thumbnail -->
					<div class="cl-card-badges">
						<?php if ( $card_show_level && $level_name ) : ?>
							<?php
							$badge_style = '';
							if ( $level_color ) {
								$badge_style .= 'color:' . esc_attr( $level_color ) . ';';
							}
							if ( $level_bg ) {
								$badge_style .= 'background-color:' . esc_attr( $level_bg ) . ';';
							}
							?>
							<span class="cl-badge cl-level-badge" style="<?php echo esc_attr( $badge_style ); ?>">
								<?php echo esc_html( $level_name ); ?>
							</span>
						<?php endif; ?>

						<?php if ( $card_show_cat && $cat_name ) : ?>
							<span class="cl-badge cl-cat-badge"><?php echo esc_html( $cat_name ); ?></span>
						<?php endif; ?>

						<?php if ( ! empty( $badge_text ) ) : ?>
							<span class="cl-badge cl-status-badge"><?php echo esc_html( $badge_text ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="cl-card-content">
				<?php if ( ! $card_show_thumb && ( $card_show_level || $card_show_cat ) ) : ?>
					<div class="cl-card-badges cl-badges-inline">
						<?php if ( $card_show_level && $level_name ) : ?>
							<span class="cl-badge cl-level-badge"><?php echo esc_html( $level_name ); ?></span>
						<?php endif; ?>
						<?php if ( $card_show_cat && $cat_name ) : ?>
							<span class="cl-badge cl-cat-badge"><?php echo esc_html( $cat_name ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $card_show_title ) : ?>
					<<?php echo esc_html( $title_tag ); ?> class="cl-card-title">
						<a href="<?php echo esc_url( $course_url ); ?>" target="<?php echo esc_attr( $cta_target ); ?>">
							<?php echo esc_html( $title ); ?>
						</a>
					</<?php echo esc_html( $title_tag ); ?>>
				<?php endif; ?>

				<?php if ( $card_show_desc && ! empty( $short_desc ) ) : ?>
					<p class="cl-card-desc"><?php echo esc_html( wp_strip_all_tags( $short_desc ) ); ?></p>
				<?php endif; ?>

				<!-- Course Metrics / Meta Row -->
				<div class="cl-card-meta">
					<?php if ( $card_show_chap && isset( $stats['chapters'] ) ) : ?>
						<span class="cl-meta-item cl-meta-chapters" title="<?php echo esc_attr( $stats['chapters'] . ' ' . $chap_label ); ?>">
							<svg class="cl-meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
							<strong><?php echo esc_html( $stats['chapters'] ); ?></strong>&nbsp;<?php echo esc_html( $chap_label ); ?>
						</span>
					<?php endif; ?>

					<?php if ( $card_show_prob && isset( $stats['problems'] ) ) : ?>
						<span class="cl-meta-item cl-meta-problems" title="<?php echo esc_attr( $stats['problems'] . ' ' . $prob_label ); ?>">
							<svg class="cl-meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
							<strong><?php echo esc_html( $stats['problems'] ); ?></strong>&nbsp;<?php echo esc_html( $prob_label ); ?>
						</span>
					<?php endif; ?>

					<?php if ( $card_show_dur && ! empty( $stats['duration'] ) ) : ?>
						<span class="cl-meta-item cl-meta-duration">
							<svg class="cl-meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
							<?php echo esc_html( $stats['duration'] ); ?>
						</span>
					<?php endif; ?>
				</div>

				<?php if ( $card_show_inst && ! empty( $instructor ) ) : ?>
					<div class="cl-card-instructor">
						<span class="cl-inst-label"><?php esc_html_e( 'By', 'practice-problems-el' ); ?></span> <?php echo esc_html( $instructor ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $card_show_cta ) : ?>
					<div class="cl-card-footer">
						<a href="<?php echo esc_url( $course_url ); ?>" class="cl-card-cta-btn" target="<?php echo esc_attr( $cta_target ); ?>">
							<span><?php echo esc_html( $cta_text ); ?></span>
							<svg class="cl-cta-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}
}
