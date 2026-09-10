<?php
/**
 * Main Plugin Class for Practice Problems.
 *
 * @package PracticeProblems
 */

namespace PracticeProblems;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 */
final class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Initialize CPT support.
		new CPT();
		new Notes_CPT();
		new Admin\Notes_Meta_Boxes();
		new Course_CPT();
		new Admin\Course_Meta_Boxes();

		// Elementor & Frontend asset hooks.
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_styles' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'init', [ $this, 'ensure_elementor_cpt_support' ], 20 );

		// AJAX Handler for CPT query.
		add_action( 'wp_ajax_pp_query_problems', [ $this, 'ajax_query_problems' ] );
		add_action( 'wp_ajax_nopriv_pp_query_problems', [ $this, 'ajax_query_problems' ] );

		// AJAX Handler for Course Listing query.
		add_action( 'wp_ajax_cl_query_courses', [ $this, 'ajax_query_courses' ] );
		add_action( 'wp_ajax_nopriv_cl_query_courses', [ $this, 'ajax_query_courses' ] );
	}

	/**
	 * Register custom Elementor category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements Manager.
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'practice-elements',
			[
				'title' => esc_html__( 'Practice & Learning Elements', 'practice-problems-el' ),
				'icon'  => 'fa fa-graduation-cap',
			]
		);
	}

	/**
	 * Register Elementor widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets Manager.
	 */
	public function register_widgets( $widgets_manager ) {
		require_once PRACTICE_PROBLEMS_PATH . 'includes/widgets/class-practice-problems-widget.php';
		require_once PRACTICE_PROBLEMS_PATH . 'includes/widgets/class-topic-notes-widget.php';
		require_once PRACTICE_PROBLEMS_PATH . 'includes/widgets/class-topic-notes-grid-widget.php';
		require_once PRACTICE_PROBLEMS_PATH . 'includes/widgets/class-course-listing-widget.php';

		$widgets_manager->register( new Widgets\Practice_Problems_Widget() );
		$widgets_manager->register( new Widgets\Topic_Notes_Widget() );
		$widgets_manager->register( new Widgets\Topic_Notes_Grid_Widget() );
		$widgets_manager->register( new Widgets\Course_Listing_Widget() );
	}

	/**
	 * Register frontend styles.
	 */
	public function register_styles() {
		wp_register_style(
			'practice-problems-frontend',
			PRACTICE_PROBLEMS_URL . 'assets/css/frontend.css',
			[],
			PRACTICE_PROBLEMS_VERSION
		);

		wp_register_style(
			'topic-notes-frontend',
			PRACTICE_PROBLEMS_URL . 'assets/css/topic-notes-frontend.css',
			[],
			PRACTICE_PROBLEMS_VERSION
		);

		wp_register_style(
			'course-listing-frontend',
			PRACTICE_PROBLEMS_URL . 'assets/css/course-listing-frontend.css',
			[],
			PRACTICE_PROBLEMS_VERSION
		);
	}

	/**
	 * Register frontend scripts.
	 */
	public function register_scripts() {
		wp_register_script(
			'practice-problems-frontend',
			PRACTICE_PROBLEMS_URL . 'assets/js/frontend.js',
			[ 'elementor-frontend' ],
			PRACTICE_PROBLEMS_VERSION,
			true
		);

		wp_register_script(
			'topic-notes-frontend',
			PRACTICE_PROBLEMS_URL . 'assets/js/topic-notes-frontend.js',
			[ 'elementor-frontend' ],
			PRACTICE_PROBLEMS_VERSION,
			true
		);

		wp_register_script(
			'course-listing-frontend',
			PRACTICE_PROBLEMS_URL . 'assets/js/course-listing-frontend.js',
			[ 'jquery', 'elementor-frontend' ],
			PRACTICE_PROBLEMS_VERSION,
			true
		);

		wp_localize_script(
			'practice-problems-frontend',
			'PracticeProblemsConfig',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'practice_problems_nonce' ),
			]
		);

		wp_localize_script(
			'course-listing-frontend',
			'CourseListingConfig',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'course_listing_nonce' ),
			]
		);
	}

	/**
	 * AJAX endpoint to query problems dynamically if using CPT mode.
	 */
	public function ajax_query_problems() {
		check_ajax_referer( 'practice_problems_nonce', 'nonce' );

		$topic      = isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '';
		$difficulty = isset( $_POST['difficulty'] ) ? sanitize_text_field( wp_unslash( $_POST['difficulty'] ) ) : '';
		$search     = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$page       = isset( $_POST['page'] ) ? max( 1, intval( $_POST['page'] ) ) : 1;
		$per_page   = isset( $_POST['per_page'] ) ? max( 1, intval( $_POST['per_page'] ) ) : 5;

		$args = [
			'post_type'      => 'practice_problem',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
		];

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$tax_query = [];
		if ( ! empty( $topic ) && 'all' !== $topic ) {
			$tax_query[] = [
				'taxonomy' => 'problem_topic',
				'field'    => 'slug',
				'terms'    => $topic,
			];
		}
		if ( ! empty( $difficulty ) && 'all' !== $difficulty ) {
			$tax_query[] = [
				'taxonomy' => 'problem_difficulty',
				'field'    => 'slug',
				'terms'    => $difficulty,
			];
		}
		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			$args['tax_query']     = $tax_query;
		}

		$query = new \WP_Query( $args );
		$items = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id   = get_the_ID();
				$badge_id  = get_post_meta( $post_id, '_pp_problem_id', true );
				$raw_steps = get_post_meta( $post_id, '_pp_steps', true );
				$answer    = get_post_meta( $post_id, '_pp_answer', true );

				// Determine topic and difficulty from taxonomies.
				$topics       = wp_get_post_terms( $post_id, 'problem_topic', [ 'fields' => 'names' ] );
				$difficulties = wp_get_post_terms( $post_id, 'problem_difficulty', [ 'fields' => 'slugs' ] );

				$steps_array = [];
				if ( ! empty( $raw_steps ) ) {
					$lines = explode( "\n", str_replace( "\r", '', $raw_steps ) );
					foreach ( $lines as $line ) {
						$trimmed = trim( $line );
						if ( '' !== $trimmed ) {
							$steps_array[] = $trimmed;
						}
					}
				}

				$items[] = [
					'id'         => $badge_id ? $badge_id : (string) $post_id,
					'title'      => get_the_title(),
					'topic'      => ! empty( $topics ) ? $topics[0] : '',
					'difficulty' => ! empty( $difficulties ) ? $difficulties[0] : 'medium',
					'statement'  => apply_filters( 'the_content', get_the_content() ),
					'steps'      => $steps_array,
					'answer'     => $answer,
				];
			}
			wp_reset_postdata();
		}

		wp_send_json_success( [
			'items'       => $items,
			'total'       => $query->found_posts,
			'total_pages' => $query->max_num_pages,
			'page'        => $page,
		] );
	}

	/**
	 * AJAX endpoint to query courses with combined search and level filter.
	 */
	public function ajax_query_courses() {
		check_ajax_referer( 'course_listing_nonce', 'nonce' );

		$search        = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$level         = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : '';
		$page          = isset( $_POST['page'] ) ? max( 1, intval( $_POST['page'] ) ) : 1;
		$per_page      = isset( $_POST['per_page'] ) ? max( 1, intval( $_POST['per_page'] ) ) : 6;
		$orderby       = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'course_order';
		$order         = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'ASC';
		$exclude_ids   = isset( $_POST['exclude_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['exclude_ids'] ) ) : '';
		$categories    = isset( $_POST['categories'] ) && is_array( $_POST['categories'] ) ? array_map( 'sanitize_text_field', $_POST['categories'] ) : [];
		$levels_limit  = isset( $_POST['levels'] ) && is_array( $_POST['levels'] ) ? array_map( 'sanitize_text_field', $_POST['levels'] ) : [];
		$card_settings = isset( $_POST['card_settings'] ) && is_array( $_POST['card_settings'] ) ? $_POST['card_settings'] : [];

		$args = [
			'post_type'      => 'course',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
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

		// Combined Tax Query: Level + Categories (AND logic)
		$tax_query = [];
		if ( ! empty( $level ) && 'all' !== $level ) {
			$tax_query[] = [
				'taxonomy' => 'course_level',
				'field'    => 'slug',
				'terms'    => sanitize_title( $level ),
			];
		} elseif ( ! empty( $levels_limit ) ) {
			$tax_query[] = [
				'taxonomy' => 'course_level',
				'field'    => 'slug',
				'terms'    => $levels_limit,
			];
		}

		if ( ! empty( $categories ) ) {
			$tax_query[] = [
				'taxonomy' => 'course_category',
				'field'    => 'slug',
				'terms'    => $categories,
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
			$args['s'] = $search;
		}

		// Exclude IDs
		if ( ! empty( $exclude_ids ) ) {
			$ids = array_map( 'intval', array_map( 'trim', explode( ',', $exclude_ids ) ) );
			$args['post__not_in'] = $ids;
		}

		$query = new \WP_Query( $args );
		$html  = '';

		if ( $query->have_posts() ) {
			ob_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				Widgets\Course_Listing_Widget::render_course_card( get_the_ID(), $card_settings );
			}
			$html = ob_get_clean();
			wp_reset_postdata();
		}

		wp_send_json_success( [
			'html'        => $html,
			'total'       => $query->found_posts,
			'total_pages' => $query->max_num_pages,
			'page'        => $page,
		] );
	}

	/**
	 * Ensure Elementor enables editing support for custom post types.
	 */
	public function ensure_elementor_cpt_support() {
		$cpt_support = get_option( 'elementor_cpt_support', [ 'page', 'post' ] );
		if ( ! is_array( $cpt_support ) ) {
			$cpt_support = [ 'page', 'post' ];
		}
		$updated = false;
		foreach ( [ 'math_note', 'practice_problem', 'course' ] as $type ) {
			if ( ! in_array( $type, $cpt_support, true ) ) {
				$cpt_support[] = $type;
				$updated = true;
			}
		}
		if ( $updated ) {
			update_option( 'elementor_cpt_support', $cpt_support );
		}
	}
}
