<?php
/**
 * Practice Problems Elementor Widget.
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
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Practice_Problems_Widget
 */
class Practice_Problems_Widget extends Widget_Base {

	public function get_name() {
		return 'practice_problems';
	}

	public function get_title() {
		return esc_html__( 'Practice Problems', 'practice-problems-el' );
	}

	public function get_icon() {
		return 'eicon-help-box';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_keywords() {
		return [ 'practice', 'problem', 'solution', 'quiz', 'math', 'accordion', 'cpt' ];
	}

	public function get_script_depends() {
		return [ 'practice-problems-frontend' ];
	}

	public function get_style_depends() {
		return [ 'practice-problems-frontend' ];
	}

	protected function register_controls() {

		/* =========================================================
		   CONTENT TAB — Data Source & General
		========================================================= */
		$this->start_controls_section(
			'section_data_source',
			[
				'label' => esc_html__( 'Data Source & Configuration', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'data_source',
			[
				'label'       => esc_html__( 'Problem Data Source', 'practice-problems-el' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'cpt',
				'description' => esc_html__( 'Load from WordPress "Practice Problems" Custom Post Type or enter manually.', 'practice-problems-el' ),
				'options'     => [
					'cpt'      => esc_html__( 'Practice Problems CPT (WordPress Posts)', 'practice-problems-el' ),
					'repeater' => esc_html__( 'Elementor Repeater (Manual Input)', 'practice-problems-el' ),
				],
			]
		);

		$this->add_control(
			'cpt_posts_per_page',
			[
				'label'       => esc_html__( 'Number of Problems to Query', 'practice-problems-el' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => -1,
				'description' => esc_html__( 'Enter -1 to query all available practice problems.', 'practice-problems-el' ),
				'condition'   => [ 'data_source' => 'cpt' ],
			]
		);

		$this->add_control(
			'cpt_order_by',
			[
				'label'     => esc_html__( 'Order By', 'practice-problems-el' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => [
					'date'       => esc_html__( 'Date Published', 'practice-problems-el' ),
					'title'      => esc_html__( 'Title', 'practice-problems-el' ),
					'ID'         => esc_html__( 'Problem ID', 'practice-problems-el' ),
					'rand'       => esc_html__( 'Random Order', 'practice-problems-el' ),
					'menu_order' => esc_html__( 'Menu Order', 'practice-problems-el' ),
				],
				'condition' => [ 'data_source' => 'cpt' ],
			]
		);

		$this->add_control(
			'cpt_order',
			[
				'label'     => esc_html__( 'Order', 'practice-problems-el' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => [
					'DESC' => esc_html__( 'Descending (Newest First)', 'practice-problems-el' ),
					'ASC'  => esc_html__( 'Ascending (Oldest First)', 'practice-problems-el' ),
				],
				'condition' => [ 'data_source' => 'cpt' ],
			]
		);

		$this->add_control(
			'enable_hero',
			[
				'label'        => esc_html__( 'Enable Hero Header', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'practice-problems-el' ),
				'label_off'    => esc_html__( 'No', 'practice-problems-el' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'enable_filters',
			[
				'label'        => esc_html__( 'Enable Filters', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'practice-problems-el' ),
				'label_off'    => esc_html__( 'No', 'practice-problems-el' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'enable_solutions',
			[
				'label'        => esc_html__( 'Enable Solutions', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'practice-problems-el' ),
				'label_off'    => esc_html__( 'No', 'practice-problems-el' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'enable_pagination',
			[
				'label'        => esc_html__( 'Enable Pagination', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'practice-problems-el' ),
				'label_off'    => esc_html__( 'No', 'practice-problems-el' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'problems_per_page',
			[
				'label'     => esc_html__( 'Problems Per Page', 'practice-problems-el' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 50,
				'default'   => 5,
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Hero
		========================================================= */
		$this->start_controls_section(
			'section_hero',
			[
				'label'     => esc_html__( 'Hero / Header', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'enable_hero' => 'yes' ],
			]
		);

		$this->add_control(
			'show_eyebrow',
			[
				'label'        => esc_html__( 'Show Eyebrow Text', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'eyebrow_text',
			[
				'label'     => esc_html__( 'Eyebrow Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Practice', 'practice-problems-el' ),
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'show_eyebrow' => 'yes' ],
			]
		);

		$this->add_control(
			'hero_heading',
			[
				'label'       => esc_html__( 'Main Heading', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Practice problems with full solutions', 'practice-problems-el' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'hero_heading_tag',
			[
				'label'   => esc_html__( 'Heading Tag', 'practice-problems-el' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h1',
				'options' => [
					'h1'  => 'H1',
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'div' => 'DIV',
				],
			]
		);

		$this->add_control(
			'show_hero_desc',
			[
				'label'        => esc_html__( 'Show Description', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'hero_desc',
			[
				'label'     => esc_html__( 'Description', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXTAREA,
				'default'   => esc_html__( 'Try the problem first, then open the step-by-step solution to check your work.', 'practice-problems-el' ),
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'show_hero_desc' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Problems (Repeater for manual mode)
		========================================================= */
		$this->start_controls_section(
			'section_problems_repeater',
			[
				'label'     => esc_html__( 'Manual Problems List', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'data_source' => 'repeater' ],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'problem_id',
			[
				'label'       => esc_html__( 'Problem ID Badge Text', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Optional: e.g. Problem 01 (leave blank to hide)', 'practice-problems-el' ),
				'description' => esc_html__( 'Optional badge text for this problem. If left empty, no badge will be shown.', 'practice-problems-el' ),
			]
		);

		$repeater->add_control(
			'topic',
			[
				'label'   => esc_html__( 'Topic', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Limits', 'practice-problems-el' ),
			]
		);

		$repeater->add_control(
			'difficulty',
			[
				'label'   => esc_html__( 'Difficulty', 'practice-problems-el' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'easy',
				'options' => [
					'easy'   => esc_html__( 'Easy', 'practice-problems-el' ),
					'medium' => esc_html__( 'Medium', 'practice-problems-el' ),
					'hard'   => esc_html__( 'Hard', 'practice-problems-el' ),
				],
			]
		);

		$repeater->add_control(
			'statement',
			[
				'label'       => esc_html__( 'Post Title / Problem Question (Main)', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Evaluate lim(x→3) of (x² - 9) / (x - 3).', 'practice-problems-el' ),
				'description' => esc_html__( 'Main problem title or question. LaTeX formulas supported (e.g. \\frac{a+b}{c}).', 'practice-problems-el' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'solution_btn_text',
			[
				'label'   => esc_html__( 'Show Solution Button Text', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Show solution', 'practice-problems-el' ),
			]
		);

		$repeater->add_control(
			'solution_btn_hide_text',
			[
				'label'   => esc_html__( 'Hide Solution Button Text', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Hide solution', 'practice-problems-el' ),
			]
		);

		$repeater->add_control(
			'solution_title',
			[
				'label'   => esc_html__( 'Solution Title', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Step-by-step solution', 'practice-problems-el' ),
			]
		);

		$repeater->add_control(
			'steps_content',
			[
				'label'       => esc_html__( 'Solution Steps (one per line)', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => "Direct substitution gives 0/0.\nFactor numerator: x² - 9 = (x-3)(x+3).\nCancel (x-3), evaluate limit: 3+3 = 6.",
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'answer_label',
			[
				'label'   => esc_html__( 'Answer Label', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Answer:', 'practice-problems-el' ),
			]
		);

		$repeater->add_control(
			'answer_content',
			[
				'label'   => esc_html__( 'Answer', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '6',
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'problems_list',
			[
				'label'       => esc_html__( 'Problems', 'practice-problems-el' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'problem_id'             => '1',
						'topic'                  => 'Limits',
						'difficulty'             => 'easy',
						'statement'              => 'Evaluate lim(x→3) of (x² - 9) / (x - 3).',
						'solution_btn_text'      => 'Show solution',
						'solution_btn_hide_text' => 'Hide solution',
						'solution_title'         => 'Step-by-step solution',
						'steps_content'          => "Direct substitution gives 0/0.\nFactor numerator: x² - 9 = (x-3)(x+3).\nCancel (x-3), evaluate limit: 3+3 = 6.",
						'answer_label'           => 'Answer:',
						'answer_content'         => '6',
					],
				],
				'title_field' => 'Problem #{{{ problem_id }}} — {{{ topic }}}',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Filters
		========================================================= */
		$this->start_controls_section(
			'section_filters',
			[
				'label'     => esc_html__( 'Filter Settings', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'enable_filters' => 'yes' ],
			]
		);

		$this->add_control(
			'show_topic_label',
			[
				'label'        => esc_html__( 'Show Topic Label', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'practice-problems-el' ),
				'label_off'    => esc_html__( 'Hide', 'practice-problems-el' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'all_topics_label',
			[
				'label'   => esc_html__( '"All Topics" Label', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'All topics', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'all_diff_label',
			[
				'label'   => esc_html__( '"All" Difficulty Label', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'All', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'diff_easy_label',
			[
				'label'   => esc_html__( 'Easy Label', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Easy', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'diff_medium_label',
			[
				'label'   => esc_html__( 'Medium Label', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Medium', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'diff_hard_label',
			[
				'label'   => esc_html__( 'Hard Label', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Hard', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'show_search',
			[
				'label'        => esc_html__( 'Show Instant Search Bar', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'search_placeholder',
			[
				'label'     => esc_html__( 'Search Placeholder', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Search problems, topics, questions...', 'practice-problems-el' ),
				'condition' => [ 'show_search' => 'yes' ],
			]
		);

		$this->add_control(
			'show_reset_btn',
			[
				'label'        => esc_html__( 'Show Reset Button', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'reset_btn_text',
			[
				'label'     => esc_html__( 'Reset Button Text', 'practice-problems-el' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Reset Filters', 'practice-problems-el' ),
				'condition' => [ 'show_reset_btn' => 'yes' ],
			]
		);

		$this->add_control(
			'show_result_counter',
			[
				'label'        => esc_html__( 'Show Result Counter', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Solution / Accordion Settings (Dedicated Section)
		========================================================= */
		$this->start_controls_section(
			'section_solution_settings',
			[
				'label'     => esc_html__( 'Solution / Accordion Settings', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'enable_solutions' => 'yes' ],
			]
		);

		$this->add_control(
			'show_solution_heading',
			[
				'label'        => esc_html__( 'Show Solution Heading', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'practice-problems-el' ),
				'label_off'    => esc_html__( 'Hide', 'practice-problems-el' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Controls the heading inside the solution box. If your WordPress post content already has a heading, turn this off or edit the text below.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'solution_heading_text',
			[
				'label'       => esc_html__( 'Solution Heading Text', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Step-by-step solution', 'practice-problems-el' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'show_solution_heading' => 'yes' ],
			]
		);

		$this->add_control(
			'solution_btn_show_text',
			[
				'label'       => esc_html__( 'Show Solution Button Text', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Show solution', 'practice-problems-el' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'solution_btn_hide_text',
			[
				'label'       => esc_html__( 'Hide Solution Button Text', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Hide solution', 'practice-problems-el' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'show_answer_box',
			[
				'label'        => esc_html__( 'Show Answer Box', 'practice-problems-el' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'practice-problems-el' ),
				'label_off'    => esc_html__( 'Hide', 'practice-problems-el' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'answer_label_text',
			[
				'label'       => esc_html__( 'Answer Label', 'practice-problems-el' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Answer:', 'practice-problems-el' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'show_answer_box' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Pagination
		========================================================= */
		$this->start_controls_section(
			'section_pagination',
			[
				'label'     => esc_html__( 'Pagination', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label'   => esc_html__( 'Pagination Type', 'practice-problems-el' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'numbers_and_prev_next',
				'options' => [
					'numbers'               => esc_html__( 'Numbers Only', 'practice-problems-el' ),
					'prev_next'             => esc_html__( 'Prev / Next Only', 'practice-problems-el' ),
					'numbers_and_prev_next' => esc_html__( 'Numbers + Prev / Next', 'practice-problems-el' ),
					'load_more'             => esc_html__( 'Load More Button', 'practice-problems-el' ),
				],
			]
		);

		$this->add_control(
			'prev_text',
			[
				'label'   => esc_html__( 'Previous Text', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '← Previous', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'next_text',
			[
				'label'   => esc_html__( 'Next Text', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Next →', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'load_more_text',
			[
				'label'   => esc_html__( 'Load More Text', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Load More', 'practice-problems-el' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   CONTENT TAB — Empty State
		========================================================= */
		$this->start_controls_section(
			'section_empty_state',
			[
				'label' => esc_html__( 'Empty State', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'empty_title',
			[
				'label'   => esc_html__( 'Empty State Title', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'No Problems Found', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'empty_desc',
			[
				'label'   => esc_html__( 'Empty State Description', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'No problems match your search criteria. Try a different term or clear filters.', 'practice-problems-el' ),
			]
		);

		$this->add_control(
			'empty_reset_text',
			[
				'label'   => esc_html__( 'Empty Reset Button Text', 'practice-problems-el' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Reset Filters', 'practice-problems-el' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Container
		========================================================= */
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 400, 'max' => 1920 ] ],
				'selectors'  => [
					'{{WRAPPER}} .pp-widget-container' => 'max-width: {{SIZE}}{{UNIT}}; margin-left: auto; margin-right: auto;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'container_bg',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .pp-widget-container',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-widget-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .pp-widget-container',
			]
		);

		$this->add_responsive_control(
			'container_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-widget-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Hero
		========================================================= */
		$this->start_controls_section(
			'section_style_hero',
			[
				'label'     => esc_html__( 'Hero / Header', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_hero' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'hero_bg',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .pp-hero-card',
			]
		);

		$this->add_responsive_control(
			'hero_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-hero-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'hero_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-hero-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eyebrow_color',
			[
				'label'     => esc_html__( 'Eyebrow Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-hero-eyebrow' => 'color: {{VALUE}};',
				],
				'condition' => [ 'show_eyebrow' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'hero_heading_typography',
				'label'    => esc_html__( 'Heading Typography', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .pp-hero-title',
			]
		);

		$this->add_control(
			'hero_heading_color',
			[
				'label'     => esc_html__( 'Heading Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-hero-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'hero_desc_typography',
				'label'    => esc_html__( 'Description Typography', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .pp-hero-desc',
			]
		);

		$this->add_control(
			'hero_desc_color',
			[
				'label'     => esc_html__( 'Description Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-hero-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Problem Card
		========================================================= */
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => esc_html__( 'Problem Card', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'card_bg',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .pp-problem-card',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-problem-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .pp-problem-card',
			]
		);

		$this->add_responsive_control(
			'card_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-problem-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .pp-problem-card',
			]
		);

		$this->add_control(
			'card_hover_translate',
			[
				'label'      => esc_html__( 'Hover Lift (px)', 'practice-problems-el' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => -20, 'max' => 0 ] ],
				'default'    => [ 'size' => -2, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-problem-card:hover' => 'transform: translateY({{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Badges
		========================================================= */
		$this->start_controls_section(
			'section_style_badges',
			[
				'label' => esc_html__( 'Badges', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'easy_bg',
			[
				'label'     => esc_html__( 'Easy: Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ecfdf5',
				'selectors' => [
					'{{WRAPPER}} .pp-badge-difficulty.easy' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'easy_color',
			[
				'label'     => esc_html__( 'Easy: Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#059669',
				'selectors' => [
					'{{WRAPPER}} .pp-badge-difficulty.easy' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'medium_bg',
			[
				'label'     => esc_html__( 'Medium: Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fffbeb',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .pp-badge-difficulty.medium' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'medium_color',
			[
				'label'     => esc_html__( 'Medium: Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#d97706',
				'selectors' => [
					'{{WRAPPER}} .pp-badge-difficulty.medium' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hard_bg',
			[
				'label'     => esc_html__( 'Hard: Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fef2f2',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .pp-badge-difficulty.hard' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hard_color',
			[
				'label'     => esc_html__( 'Hard: Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#dc2626',
				'selectors' => [
					'{{WRAPPER}} .pp-badge-difficulty.hard' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Statement & Title
		========================================================= */
		$this->start_controls_section(
			'section_style_statement',
			[
				'label' => esc_html__( 'Problem Title & Statement', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Problem Title Typography', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .pp-problem-title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-problem-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'statement_typography',
				'label'    => esc_html__( 'Statement Typography', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .pp-statement-content',
			]
		);

		$this->add_control(
			'statement_color',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-statement-content' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'statement_bg',
			[
				'label'     => esc_html__( 'Background Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-statement-content' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'statement_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-statement-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Solution Button
		========================================================= */
		$this->start_controls_section(
			'section_style_solution_btn',
			[
				'label' => esc_html__( 'Solution Button', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'solution_btn_typography',
				'selector' => '{{WRAPPER}} .pp-solution-toggle-btn',
			]
		);

		$this->start_controls_tabs( 'tabs_solution_btn' );

		$this->start_controls_tab(
			'tab_solution_btn_normal',
			[ 'label' => esc_html__( 'Normal', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'solution_btn_color',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-solution-toggle-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'solution_btn_bg',
			[
				'label'     => esc_html__( 'Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-solution-toggle-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'solution_btn_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-solution-toggle-btn' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_solution_btn_hover',
			[ 'label' => esc_html__( 'Hover', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'solution_btn_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-solution-toggle-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'solution_btn_hover_bg',
			[
				'label'     => esc_html__( 'Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-solution-toggle-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'solution_btn_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-solution-toggle-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'solution_btn_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-solution-toggle-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'solution_btn_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-solution-toggle-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Solution Box & Heading
		========================================================= */
		$this->start_controls_section(
			'section_style_solution_box',
			[
				'label' => esc_html__( 'Solution Box & Heading', 'practice-problems-el' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_solution_title_style',
			[
				'label'     => esc_html__( 'Solution Heading', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'solution_title_typography',
				'label'    => esc_html__( 'Heading Typography', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .pp-solution-title',
			]
		);

		$this->add_control(
			'solution_title_color',
			[
				'label'     => esc_html__( 'Heading Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-solution-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'solution_title_margin',
			[
				'label'      => esc_html__( 'Heading Margin', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-solution-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_solution_box_style',
			[
				'label'     => esc_html__( 'Solution Box', 'practice-problems-el' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'solution_box_bg',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .pp-solution-content',
			]
		);

		$this->add_control(
			'solution_accent_color',
			[
				'label'     => esc_html__( 'Left Accent Border Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-solution-content' => 'border-left-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'solution_box_padding',
			[
				'label'      => esc_html__( 'Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-solution-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'steps_typography',
				'label'    => esc_html__( 'Steps & Body Typography', 'practice-problems-el' ),
				'selector' => '{{WRAPPER}} .pp-step-item, {{WRAPPER}} .pp-solution-html-body',
			]
		);

		$this->add_control(
			'steps_color',
			[
				'label'     => esc_html__( 'Steps Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-step-item, {{WRAPPER}} .pp-solution-html-body' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'answer_bg',
			[
				'label'     => esc_html__( 'Answer Box Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .pp-answer-box' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'answer_color',
			[
				'label'     => esc_html__( 'Answer Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-answer-box' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		   STYLE TAB — Pagination
		========================================================= */
		$this->start_controls_section(
			'section_style_pagination',
			[
				'label'     => esc_html__( 'Pagination', 'practice-problems-el' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pag_typography',
				'selector' => '{{WRAPPER}} .pp-page-btn',
			]
		);

		$this->start_controls_tabs( 'tabs_pag_btn' );

		$this->start_controls_tab(
			'tab_pag_normal',
			[ 'label' => esc_html__( 'Normal', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'pag_color',
			[
				'label'     => esc_html__( 'Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-page-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pag_bg',
			[
				'label'     => esc_html__( 'Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-page-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_pag_active',
			[ 'label' => esc_html__( 'Active', 'practice-problems-el' ) ]
		);

		$this->add_control(
			'pag_active_color',
			[
				'label'     => esc_html__( 'Active Text Color', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-page-btn.active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pag_active_bg',
			[
				'label'     => esc_html__( 'Active Background', 'practice-problems-el' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pp-page-btn.active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'pag_padding',
			[
				'label'      => esc_html__( 'Button Padding', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-page-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'pag_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'practice-problems-el' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pp-page-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget HTML.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		// Determine data source (default to CPT).
		$data_source = ! empty( $s['data_source'] ) ? $s['data_source'] : 'cpt';
		$problems    = [];

		// Global solution settings from Elementor controls
		$show_solution_heading   = isset( $s['show_solution_heading'] ) ? $s['show_solution_heading'] : 'yes';
		$solution_heading_text   = ! empty( $s['solution_heading_text'] ) ? $s['solution_heading_text'] : esc_html__( 'Step-by-step solution', 'practice-problems-el' );
		$solution_btn_show_text  = ! empty( $s['solution_btn_show_text'] ) ? $s['solution_btn_show_text'] : esc_html__( 'Show solution', 'practice-problems-el' );
		$solution_btn_hide_text  = ! empty( $s['solution_btn_hide_text'] ) ? $s['solution_btn_hide_text'] : esc_html__( 'Hide solution', 'practice-problems-el' );
		$show_answer_box         = isset( $s['show_answer_box'] ) ? $s['show_answer_box'] : 'yes';
		$answer_label_text       = ! empty( $s['answer_label_text'] ) ? $s['answer_label_text'] : esc_html__( 'Answer:', 'practice-problems-el' );

		if ( 'cpt' === $data_source ) {
			$q_args = [
				'post_type'      => 'practice_problem',
				'post_status'    => 'publish',
				'posts_per_page' => ! empty( $s['cpt_posts_per_page'] ) ? (int) $s['cpt_posts_per_page'] : -1,
				'orderby'        => ! empty( $s['cpt_order_by'] ) ? $s['cpt_order_by'] : 'date',
				'order'          => ! empty( $s['cpt_order'] ) ? $s['cpt_order'] : 'DESC',
			];

			$query = new \WP_Query( $q_args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$pid       = get_the_ID();
					$badge_id  = get_post_meta( $pid, '_pp_problem_id', true );
					$raw_steps = get_post_meta( $pid, '_pp_steps', true );
					$answer    = get_post_meta( $pid, '_pp_answer', true );

					$post_topics = wp_get_post_terms( $pid, 'problem_topic', [ 'fields' => 'names' ] );
					$diffs       = wp_get_post_terms( $pid, 'problem_difficulty', [ 'fields' => 'slugs' ] );

					$steps_array = [];
					if ( ! empty( $raw_steps ) ) {
						$lines = preg_split( '/\r\n|\r|\n/', $raw_steps );
						foreach ( $lines as $line ) {
							$trimmed = trim( $line );
							if ( '' !== $trimmed ) {
								$steps_array[] = \PracticeProblems\CPT::prepare_latex( $trimmed, true );
							}
						}
					}

					$raw_content = get_the_content();

					// Strip any duplicate solution heading from the post_content (e.g. <h3>Step-by-step solution</h3>)
					// so that the heading is only controlled by the widget controls and never appears twice!
					$clean_content = preg_replace( '/<h[1-6][^>]*>\s*(Step-by-step\s+solution|Step\s+by\s+step\s+solution|Solution)\s*<\/h[1-6]>/i', '', $raw_content );

					$cpt_title = get_the_title();
					if ( empty( $cpt_title ) ) {
						$cpt_title = get_post_meta( $pid, '_pp_title', true );
					}

					$problems[] = [
						'problem_id'             => ! empty( $badge_id ) ? trim( $badge_id ) : '',
						'title'                  => \PracticeProblems\CPT::prepare_latex( $cpt_title, false ),
						'topic'                  => ! empty( $post_topics ) ? implode( ', ', $post_topics ) : '',
						'difficulty'             => ! empty( $diffs ) ? $diffs[0] : 'medium',
						'statement'              => \PracticeProblems\CPT::prepare_latex( $cpt_title, false ),
						'solution_btn_text'      => $solution_btn_show_text,
						'solution_btn_hide_text' => $solution_btn_hide_text,
						'show_solution_heading'  => $show_solution_heading,
						'solution_title'         => $solution_heading_text,
						'steps'                  => $steps_array,
						'solution_html'          => empty( $steps_array ) ? apply_filters( 'the_content', $clean_content ) : '',
						'show_answer'            => $show_answer_box,
						'answer_label'           => $answer_label_text,
						'answer_content'         => \PracticeProblems\CPT::prepare_latex( $answer, false ),
					];
				}
				wp_reset_postdata();
			}
		} else {
			// From repeater.
			$repeater_items = ! empty( $s['problems_list'] ) ? $s['problems_list'] : [];
			foreach ( $repeater_items as $item ) {
				$steps_array = [];
				if ( ! empty( $item['steps_content'] ) ) {
					$lines = preg_split( '/\r\n|\r|\n/', $item['steps_content'] );
					foreach ( $lines as $line ) {
						$trimmed = trim( $line );
						if ( '' !== $trimmed ) {
							$steps_array[] = \PracticeProblems\CPT::prepare_latex( $trimmed, true );
						}
					}
				}

				$statement = ! empty( $item['statement'] ) ? \PracticeProblems\CPT::prepare_latex( $item['statement'], false ) : '';
				$ans_raw   = ! empty( $item['answer_content'] ) ? \PracticeProblems\CPT::prepare_latex( $item['answer_content'], false ) : '';

				$problems[] = [
					'problem_id'             => ! empty( $item['problem_id'] ) ? trim( $item['problem_id'] ) : '',
					'title'                  => $statement,
					'topic'                  => ! empty( $item['topic'] ) ? $item['topic'] : '',
					'difficulty'             => ! empty( $item['difficulty'] ) ? $item['difficulty'] : 'easy',
					'statement'              => $statement,
					'solution_btn_text'      => ! empty( $item['solution_btn_text'] ) ? $item['solution_btn_text'] : $solution_btn_show_text,
					'solution_btn_hide_text' => ! empty( $item['solution_btn_hide_text'] ) ? $item['solution_btn_hide_text'] : $solution_btn_hide_text,
					'show_solution_heading'  => $show_solution_heading,
					'solution_title'         => ! empty( $item['solution_title'] ) ? $item['solution_title'] : $solution_heading_text,
					'steps'                  => $steps_array,
					'solution_html'          => '',
					'show_answer'            => $show_answer_box,
					'answer_label'           => ! empty( $item['answer_label'] ) ? $item['answer_label'] : $answer_label_text,
					'answer_content'         => $ans_raw,
				];
			}
		}

		// Config array for JS.
		$config = [
			'enablePagination' => 'yes' === $s['enable_pagination'],
			'perPage'          => ! empty( $s['problems_per_page'] ) ? (int) $s['problems_per_page'] : 5,
			'paginationType'   => ! empty( $s['pagination_type'] ) ? $s['pagination_type'] : 'numbers_and_prev_next',
			'prevText'         => ! empty( $s['prev_text'] ) ? $s['prev_text'] : '← Previous',
			'nextText'         => ! empty( $s['next_text'] ) ? $s['next_text'] : 'Next →',
			'loadMoreText'     => ! empty( $s['load_more_text'] ) ? $s['load_more_text'] : 'Load More',
			'emptyResetText'   => ! empty( $s['empty_reset_text'] ) ? $s['empty_reset_text'] : 'Reset Filters',
		];

		// Collect unique topics dynamically.
		$topics = [];
		if ( 'cpt' === $data_source ) {
			// Fetch all topics registered in WordPress dashboard CPT taxonomy.
			$cpt_terms = get_terms( [
				'taxonomy'   => 'problem_topic',
				'hide_empty' => false,
			] );
			if ( ! is_wp_error( $cpt_terms ) && ! empty( $cpt_terms ) ) {
				foreach ( $cpt_terms as $term ) {
					$topics[] = $term->name;
				}
			}
		} else {
			// From repeater items.
			foreach ( $problems as $p ) {
				if ( ! empty( $p['topic'] ) && ! in_array( $p['topic'], $topics, true ) ) {
					$topics[] = $p['topic'];
				}
			}
		}

		$widget_id = 'pp-' . $this->get_id();
		?>
		<div id="<?php echo esc_attr( $widget_id ); ?>" class="pp-widget-container" data-pp-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

			<?php // HERO. ?>
			<?php if ( 'yes' === $s['enable_hero'] ) : ?>
				<header class="pp-hero-card">
					<?php if ( 'yes' === $show_solution_heading && 'yes' === $s['show_eyebrow'] && ! empty( $s['eyebrow_text'] ) ) : ?>
						<span class="pp-hero-eyebrow"><?php echo esc_html( $s['eyebrow_text'] ); ?></span>
					<?php endif; ?>
					<?php
					$tag = ! empty( $s['hero_heading_tag'] ) ? $s['hero_heading_tag'] : 'h1';
					if ( ! empty( $s['hero_heading'] ) ) :
						?>
						<<?php echo esc_html( $tag ); ?> class="pp-hero-title"><?php echo esc_html( $s['hero_heading'] ); ?></<?php echo esc_html( $tag ); ?>>
					<?php endif; ?>
					<?php if ( 'yes' === $s['show_hero_desc'] && ! empty( $s['hero_desc'] ) ) : ?>
						<p class="pp-hero-desc"><?php echo esc_html( $s['hero_desc'] ); ?></p>
					<?php endif; ?>
				</header>
			<?php endif; ?>

			<?php // FILTER BAR. ?>
			<?php if ( 'yes' === $s['enable_filters'] ) : ?>
				<div class="pp-filters-bar">
					<div class="pp-filter-group">
						<?php if ( 'yes' === ( $s['show_topic_label'] ?? '' ) ) : ?>
							<label><?php echo esc_html__( 'Topic', 'practice-problems-el' ); ?></label>
						<?php endif; ?>
						<select class="pp-select-control pp-topic-select" aria-label="<?php esc_attr_e( 'Filter by Topic', 'practice-problems-el' ); ?>">
							<option value="all"><?php echo esc_html( $s['all_topics_label'] ); ?></option>
							<?php foreach ( $topics as $t ) : ?>
								<option value="<?php echo esc_attr( $t ); ?>"><?php echo esc_html( $t ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="pp-filter-group">
						<label><?php echo esc_html__( 'Difficulty', 'practice-problems-el' ); ?></label>
						<div class="pp-pills-group pp-difficulty-pills">
							<button type="button" class="pp-pill-btn active" data-difficulty="all"><?php echo esc_html( $s['all_diff_label'] ); ?></button>
							<button type="button" class="pp-pill-btn" data-difficulty="easy"><?php echo esc_html( $s['diff_easy_label'] ); ?></button>
							<button type="button" class="pp-pill-btn" data-difficulty="medium"><?php echo esc_html( $s['diff_medium_label'] ); ?></button>
							<button type="button" class="pp-pill-btn" data-difficulty="hard"><?php echo esc_html( $s['diff_hard_label'] ); ?></button>
						</div>
					</div>
					<?php if ( 'yes' === $s['show_search'] ) : ?>
						<div class="pp-filter-group pp-filter-search">
							<input type="search" 
								   class="pp-search-input" 
								   placeholder="<?php echo esc_attr( $s['search_placeholder'] ); ?>" 
								   aria-label="<?php esc_attr_e( 'Search problems instantly', 'practice-problems-el' ); ?>">
						</div>
					<?php endif; ?>
					<div class="pp-filter-actions">
						<?php if ( 'yes' === $s['show_reset_btn'] ) : ?>
							<button type="button" class="pp-action-btn pp-reset-btn"><?php echo esc_html( $s['reset_btn_text'] ); ?></button>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( 'yes' === $s['show_result_counter'] ) : ?>
					<div class="pp-counter-bar">
						<span class="pp-counter-text" aria-live="polite"></span>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php // PROBLEM LIST. ?>
			<div class="pp-problems-list" role="feed">
				<?php foreach ( $problems as $index => $p ) : ?>
					<?php $this->render_card( $p, $index, $s ); ?>
				<?php endforeach; ?>
			</div>

			<?php // EMPTY STATE. ?>
			<div class="pp-empty-state" style="display:none;" role="alert">
				<div class="pp-empty-icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
				</div>
				<h3 class="pp-empty-title"><?php echo esc_html( $s['empty_title'] ); ?></h3>
				<p class="pp-empty-desc"><?php echo esc_html( $s['empty_desc'] ); ?></p>
				<button type="button" class="pp-pill-btn pp-empty-reset-btn"><?php echo esc_html( $s['empty_reset_text'] ); ?></button>
			</div>

			<?php // PAGINATION. ?>
			<?php if ( 'yes' === $s['enable_pagination'] ) : ?>
				<nav class="pp-pagination-container" aria-label="<?php esc_attr_e( 'Problem pages', 'practice-problems-el' ); ?>"></nav>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Render a single problem card.
	 */
	private function render_card( $p, $index, $s ) {
		$difficulty    = ! empty( $p['difficulty'] ) ? strtolower( $p['difficulty'] ) : 'medium';
		$accordion_id  = 'pp-sol-' . $this->get_id() . '-' . $index;
		$show_solution = 'yes' === $s['enable_solutions'];

		// Difficulty label.
		$diff_labels = [
			'easy'   => ! empty( $s['diff_easy_label'] ) ? $s['diff_easy_label'] : 'Easy',
			'medium' => ! empty( $s['diff_medium_label'] ) ? $s['diff_medium_label'] : 'Medium',
			'hard'   => ! empty( $s['diff_hard_label'] ) ? $s['diff_hard_label'] : 'Hard',
		];
		$diff_label = isset( $diff_labels[ $difficulty ] ) ? $diff_labels[ $difficulty ] : ucfirst( $difficulty );
		?>
		<article class="pp-problem-card"
				 data-id="<?php echo esc_attr( $p['problem_id'] ); ?>"
				 data-title="<?php echo esc_attr( $p['title'] ); ?>"
				 data-topic="<?php echo esc_attr( $p['topic'] ); ?>"
				 data-difficulty="<?php echo esc_attr( $difficulty ); ?>">

			<header class="pp-card-header">
				<div class="pp-card-meta-left">
					<?php if ( ! empty( $p['problem_id'] ) ) : ?>
						<span class="pp-badge-id"><?php echo esc_html( $p['problem_id'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $p['topic'] ) ) : ?>
						<span class="pp-badge-topic"><?php echo esc_html( $p['topic'] ); ?></span>
					<?php endif; ?>
				</div>
				<span class="pp-badge-difficulty <?php echo esc_attr( $difficulty ); ?>"><?php echo esc_html( $diff_label ); ?></span>
			</header>

			<div class="pp-statement-content">
				<h3 class="pp-problem-title pp-math-render"><?php echo wp_kses_post( $p['title'] ); ?></h3>
			</div>

			<?php if ( $show_solution ) : ?>
				<div class="pp-solution-wrapper">
					<button type="button"
							class="pp-solution-toggle-btn"
							aria-expanded="false"
							aria-controls="<?php echo esc_attr( $accordion_id ); ?>"
							data-show-text="<?php echo esc_attr( $p['solution_btn_text'] ); ?>"
							data-hide-text="<?php echo esc_attr( $p['solution_btn_hide_text'] ); ?>">
						<span class="pp-btn-text"><?php echo esc_html( $p['solution_btn_text'] ); ?></span>
						<svg class="pp-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
					</button>

					<div id="<?php echo esc_attr( $accordion_id ); ?>"
						 class="pp-solution-content"
						 style="display:none;"
						 role="region">

						<?php if ( 'yes' === $p['show_solution_heading'] && ! empty( $p['solution_title'] ) ) : ?>
							<h4 class="pp-solution-title"><?php echo esc_html( $p['solution_title'] ); ?></h4>
						<?php endif; ?>

						<?php if ( ! empty( $p['steps'] ) && is_array( $p['steps'] ) ) : ?>
							<ol class="pp-steps-list">
								<?php foreach ( $p['steps'] as $si => $step_text ) : ?>
									<li class="pp-step-item">
										<span class="pp-step-num"><?php echo esc_html( $si + 1 ); ?></span>
										<div class="pp-step-body pp-math-render"><?php echo wp_kses_post( $step_text ); ?></div>
									</li>
								<?php endforeach; ?>
							</ol>
						<?php elseif ( ! empty( $p['solution_html'] ) ) : ?>
							<div class="pp-solution-html-body pp-math-render">
								<?php echo wp_kses_post( $p['solution_html'] ); ?>
							</div>
						<?php endif; ?>

						<?php if ( 'yes' === $p['show_answer'] && ! empty( $p['answer_content'] ) ) : ?>
							<div class="pp-answer-box">
								<strong class="pp-answer-label"><?php echo esc_html( $p['answer_label'] ); ?></strong>
								<span class="pp-answer-val pp-math-render"><?php echo wp_kses_post( $p['answer_content'] ); ?></span>
							</div>
						<?php endif; ?>

					</div>
				</div>
			<?php endif; ?>

		</article>
		<?php
	}
}
