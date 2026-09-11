<?php
/**
 * Course CPT and Taxonomies Registration.
 *
 * @package PracticeProblems
 */

namespace PracticeProblems;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Course_CPT
 */
class Course_CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_cpt' ], 5 );
		add_action( 'init', [ $this, 'register_taxonomies' ], 5 );
		add_action( 'init', [ $this, 'seed_default_terms' ], 15 );
		add_action( 'init', [ $this, 'maybe_flush_rewrite_rules' ], 99 );

		// Custom admin columns for Courses.
		add_filter( 'manage_course_posts_columns', [ $this, 'set_custom_columns' ] );
		add_action( 'manage_course_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );

		// Taxonomy term meta fields for course_level.
		add_action( 'course_level_add_form_fields', [ $this, 'add_course_level_fields' ] );
		add_action( 'course_level_edit_form_fields', [ $this, 'edit_course_level_fields' ], 10, 2 );
		add_action( 'created_course_level', [ $this, 'save_course_level_fields' ] );
		add_action( 'edited_course_level', [ $this, 'save_course_level_fields' ] );

		// Taxonomy admin columns for course_level.
		add_filter( 'manage_edit-course_level_columns', [ $this, 'set_level_columns' ] );
		add_action( 'manage_course_level_custom_column', [ $this, 'render_level_columns' ], 10, 3 );
	}

	/**
	 * Register 'course' Custom Post Type.
	 */
	public function register_cpt() {
		$labels = [
			'name'                  => esc_html_x( 'Courses', 'Post Type General Name', 'practice-problems-el' ),
			'singular_name'         => esc_html_x( 'Course', 'Post Type Singular Name', 'practice-problems-el' ),
			'menu_name'             => esc_html__( 'Courses', 'practice-problems-el' ),
			'name_admin_bar'        => esc_html__( 'Course', 'practice-problems-el' ),
			'archives'              => esc_html__( 'Course Archives', 'practice-problems-el' ),
			'attributes'            => esc_html__( 'Course Attributes', 'practice-problems-el' ),
			'parent_item_colon'     => esc_html__( 'Parent Course:', 'practice-problems-el' ),
			'all_items'             => esc_html__( 'All Courses', 'practice-problems-el' ),
			'add_new_item'          => esc_html__( 'Add New Course', 'practice-problems-el' ),
			'add_new'               => esc_html__( 'Add New', 'practice-problems-el' ),
			'new_item'              => esc_html__( 'New Course', 'practice-problems-el' ),
			'edit_item'             => esc_html__( 'Edit Course', 'practice-problems-el' ),
			'update_item'           => esc_html__( 'Update Course', 'practice-problems-el' ),
			'view_item'             => esc_html__( 'View Course', 'practice-problems-el' ),
			'view_items'            => esc_html__( 'View Courses', 'practice-problems-el' ),
			'search_items'          => esc_html__( 'Search Courses', 'practice-problems-el' ),
			'not_found'             => esc_html__( 'No courses found', 'practice-problems-el' ),
			'not_found_in_trash'    => esc_html__( 'No courses found in Trash', 'practice-problems-el' ),
		];

		$args = [
			'label'                 => esc_html__( 'Course', 'practice-problems-el' ),
			'description'           => esc_html__( 'Interactive courses with lessons, chapters, and practice problems.', 'practice-problems-el' ),
			'labels'                => $labels,
			'supports'              => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ],
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 24,
			'menu_icon'             => 'dashicons-welcome-learn-more',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => 'courses',
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rewrite'               => [ 'slug' => 'course', 'with_front' => false ],
		];

		register_post_type( 'course', $args );
	}

	/**
	 * Register Course Categories and Course Levels Taxonomies.
	 */
	public function register_taxonomies() {
		// 1. Course Level Taxonomy (e.g. Beginner, Intermediate, Advanced)
		$level_labels = [
			'name'              => esc_html_x( 'Course Levels', 'taxonomy general name', 'practice-problems-el' ),
			'singular_name'     => esc_html_x( 'Course Level', 'taxonomy singular name', 'practice-problems-el' ),
			'search_items'      => esc_html__( 'Search Course Levels', 'practice-problems-el' ),
			'all_items'         => esc_html__( 'All Course Levels', 'practice-problems-el' ),
			'parent_item'       => esc_html__( 'Parent Level', 'practice-problems-el' ),
			'parent_item_colon' => esc_html__( 'Parent Level:', 'practice-problems-el' ),
			'edit_item'         => esc_html__( 'Edit Course Level', 'practice-problems-el' ),
			'update_item'       => esc_html__( 'Update Course Level', 'practice-problems-el' ),
			'add_new_item'      => esc_html__( 'Add New Course Level', 'practice-problems-el' ),
			'new_item_name'     => esc_html__( 'New Course Level Name', 'practice-problems-el' ),
			'menu_name'         => esc_html__( 'Course Levels', 'practice-problems-el' ),
		];

		register_taxonomy( 'course_level', [ 'course' ], [
			'hierarchical'      => true,
			'labels'            => $level_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'course-level', 'with_front' => false ],
		] );

		// 2. Course Category Taxonomy (e.g. Calculus, Algebra, Geometry, Physics)
		$category_labels = [
			'name'              => esc_html_x( 'Course Categories', 'taxonomy general name', 'practice-problems-el' ),
			'singular_name'     => esc_html_x( 'Course Category', 'taxonomy singular name', 'practice-problems-el' ),
			'search_items'      => esc_html__( 'Search Course Categories', 'practice-problems-el' ),
			'all_items'         => esc_html__( 'All Course Categories', 'practice-problems-el' ),
			'parent_item'       => esc_html__( 'Parent Category', 'practice-problems-el' ),
			'parent_item_colon' => esc_html__( 'Parent Category:', 'practice-problems-el' ),
			'edit_item'         => esc_html__( 'Edit Course Category', 'practice-problems-el' ),
			'update_item'       => esc_html__( 'Update Course Category', 'practice-problems-el' ),
			'add_new_item'      => esc_html__( 'Add New Course Category', 'practice-problems-el' ),
			'new_item_name'     => esc_html__( 'New Course Category Name', 'practice-problems-el' ),
			'menu_name'         => esc_html__( 'Course Categories', 'practice-problems-el' ),
		];

		register_taxonomy( 'course_category', [ 'course' ], [
			'hierarchical'      => true,
			'labels'            => $category_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'course-category', 'with_front' => false ],
		] );
	}

	/**
	 * Seed default level terms on first initialization.
	 */
	public function seed_default_terms() {
		if ( get_option( 'cl_default_levels_seeded' ) ) {
			return;
		}

		$defaults = [
			'beginner' => [
				'name'        => 'Beginner',
				'badge_color' => '#10b981',
				'bg_color'    => '#ecfdf5',
				'border_color'=> '#a7f3d0',
				'icon'        => 'dashicons-star-filled',
				'sort_order'  => 1,
			],
			'intermediate' => [
				'name'        => 'Intermediate',
				'badge_color' => '#3b82f6',
				'bg_color'    => '#eff6ff',
				'border_color'=> '#bfdbfe',
				'icon'        => 'dashicons-star-half',
				'sort_order'  => 2,
			],
			'advanced' => [
				'name'        => 'Advanced',
				'badge_color' => '#8b5cf6',
				'bg_color'    => '#f5f3ff',
				'border_color'=> '#ddd6fe',
				'icon'        => 'dashicons-awards',
				'sort_order'  => 3,
			],
		];

		foreach ( $defaults as $slug => $data ) {
			if ( ! term_exists( $slug, 'course_level' ) ) {
				$res = wp_insert_term( $data['name'], 'course_level', [ 'slug' => $slug ] );
				if ( ! is_wp_error( $res ) && isset( $res['term_id'] ) ) {
					$term_id = $res['term_id'];
					update_term_meta( $term_id, 'level_badge_color', $data['badge_color'] );
					update_term_meta( $term_id, 'level_bg_color', $data['bg_color'] );
					update_term_meta( $term_id, 'level_border_color', $data['border_color'] );
					update_term_meta( $term_id, 'level_icon', $data['icon'] );
					update_term_meta( $term_id, 'level_sort_order', $data['sort_order'] );
				}
			}
		}

		update_option( 'cl_default_levels_seeded', 1 );
	}

	/**
	 * Add custom meta fields to 'course_level' Add Term screen.
	 */
	public function add_course_level_fields() {
		?>
		<div class="form-field term-group">
			<label for="level_badge_color"><?php esc_html_e( 'Badge Text Color', 'practice-problems-el' ); ?></label>
			<input type="color" id="level_badge_color" name="level_badge_color" value="#10b981" style="width:60px;height:36px;padding:2px;cursor:pointer;">
			<p class="description"><?php esc_html_e( 'Primary text/accent color used for badges and pill active state.', 'practice-problems-el' ); ?></p>
		</div>

		<div class="form-field term-group">
			<label for="level_bg_color"><?php esc_html_e( 'Badge Background Color', 'practice-problems-el' ); ?></label>
			<input type="color" id="level_bg_color" name="level_bg_color" value="#ecfdf5" style="width:60px;height:36px;padding:2px;cursor:pointer;">
			<p class="description"><?php esc_html_e( 'Background tint color for level badge pill.', 'practice-problems-el' ); ?></p>
		</div>

		<div class="form-field term-group">
			<label for="level_border_color"><?php esc_html_e( 'Badge Border Color', 'practice-problems-el' ); ?></label>
			<input type="color" id="level_border_color" name="level_border_color" value="#a7f3d0" style="width:60px;height:36px;padding:2px;cursor:pointer;">
			<p class="description"><?php esc_html_e( 'Border outline color for badge.', 'practice-problems-el' ); ?></p>
		</div>

		<div class="form-field term-group">
			<label for="level_icon"><?php esc_html_e( 'Icon (Dashicon / Class)', 'practice-problems-el' ); ?></label>
			<input type="text" id="level_icon" name="level_icon" value="dashicons-star-filled" placeholder="e.g. dashicons-star-filled">
			<p class="description"><?php esc_html_e( 'Dashicon class or CSS icon class for this level.', 'practice-problems-el' ); ?></p>
		</div>

		<div class="form-field term-group">
			<label for="level_sort_order"><?php esc_html_e( 'Sort Order', 'practice-problems-el' ); ?></label>
			<input type="number" id="level_sort_order" name="level_sort_order" value="0" min="0" step="1">
			<p class="description"><?php esc_html_e( 'Order of appearance in the filter bar (lowest first).', 'practice-problems-el' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Edit custom meta fields on 'course_level' Edit Term screen.
	 */
	public function edit_course_level_fields( $term ) {
		$badge_color  = get_term_meta( $term->term_id, 'level_badge_color', true ) ?: '#10b981';
		$bg_color     = get_term_meta( $term->term_id, 'level_bg_color', true ) ?: '#ecfdf5';
		$border_color = get_term_meta( $term->term_id, 'level_border_color', true ) ?: '#a7f3d0';
		$icon         = get_term_meta( $term->term_id, 'level_icon', true ) ?: 'dashicons-star-filled';
		$sort_order   = get_term_meta( $term->term_id, 'level_sort_order', true ) ?: '0';
		?>
		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="level_badge_color"><?php esc_html_e( 'Badge Text Color', 'practice-problems-el' ); ?></label></th>
			<td>
				<input type="color" id="level_badge_color" name="level_badge_color" value="<?php echo esc_attr( $badge_color ); ?>" style="width:60px;height:36px;padding:2px;cursor:pointer;">
				<p class="description"><?php esc_html_e( 'Primary text/accent color used for badges and pill active state.', 'practice-problems-el' ); ?></p>
			</td>
		</tr>

		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="level_bg_color"><?php esc_html_e( 'Badge Background Color', 'practice-problems-el' ); ?></label></th>
			<td>
				<input type="color" id="level_bg_color" name="level_bg_color" value="<?php echo esc_attr( $bg_color ); ?>" style="width:60px;height:36px;padding:2px;cursor:pointer;">
				<p class="description"><?php esc_html_e( 'Background tint color for level badge pill.', 'practice-problems-el' ); ?></p>
			</td>
		</tr>

		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="level_border_color"><?php esc_html_e( 'Badge Border Color', 'practice-problems-el' ); ?></label></th>
			<td>
				<input type="color" id="level_border_color" name="level_border_color" value="<?php echo esc_attr( $border_color ); ?>" style="width:60px;height:36px;padding:2px;cursor:pointer;">
				<p class="description"><?php esc_html_e( 'Border outline color for badge.', 'practice-problems-el' ); ?></p>
			</td>
		</tr>

		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="level_icon"><?php esc_html_e( 'Icon (Dashicon / Class)', 'practice-problems-el' ); ?></label></th>
			<td>
				<input type="text" id="level_icon" name="level_icon" value="<?php echo esc_attr( $icon ); ?>" placeholder="e.g. dashicons-star-filled">
				<p class="description"><?php esc_html_e( 'Dashicon class or CSS icon class for this level.', 'practice-problems-el' ); ?></p>
			</td>
		</tr>

		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="level_sort_order"><?php esc_html_e( 'Sort Order', 'practice-problems-el' ); ?></label></th>
			<td>
				<input type="number" id="level_sort_order" name="level_sort_order" value="<?php echo esc_attr( $sort_order ); ?>" min="0" step="1">
				<p class="description"><?php esc_html_e( 'Order of appearance in the filter bar (lowest first).', 'practice-problems-el' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save custom term meta for 'course_level'.
	 */
	public function save_course_level_fields( $term_id ) {
		if ( isset( $_POST['level_badge_color'] ) ) {
			update_term_meta( $term_id, 'level_badge_color', sanitize_hex_color( $_POST['level_badge_color'] ) );
		}
		if ( isset( $_POST['level_bg_color'] ) ) {
			update_term_meta( $term_id, 'level_bg_color', sanitize_hex_color( $_POST['level_bg_color'] ) );
		}
		if ( isset( $_POST['level_border_color'] ) ) {
			update_term_meta( $term_id, 'level_border_color', sanitize_hex_color( $_POST['level_border_color'] ) );
		}
		if ( isset( $_POST['level_icon'] ) ) {
			update_term_meta( $term_id, 'level_icon', sanitize_text_field( wp_unslash( $_POST['level_icon'] ) ) );
		}
		if ( isset( $_POST['level_sort_order'] ) ) {
			update_term_meta( $term_id, 'level_sort_order', intval( $_POST['level_sort_order'] ) );
		}
	}

	/**
	 * Set custom columns in 'course_level' admin table.
	 */
	public function set_level_columns( $columns ) {
		$new_cols = [];
		$new_cols['cb']    = $columns['cb'];
		$new_cols['name']  = $columns['name'];
		$new_cols['badge'] = esc_html__( 'Badge Preview', 'practice-problems-el' );
		$new_cols['order'] = esc_html__( 'Sort Order', 'practice-problems-el' );
		$new_cols['slug']  = $columns['slug'];
		$new_cols['posts'] = $columns['posts'];
		return $new_cols;
	}

	/**
	 * Render custom columns in 'course_level' admin table.
	 */
	public function render_level_columns( $content, $column_name, $term_id ) {
		if ( 'badge' === $column_name ) {
			$badge_color  = get_term_meta( $term_id, 'level_badge_color', true ) ?: '#10b981';
			$bg_color     = get_term_meta( $term_id, 'level_bg_color', true ) ?: '#ecfdf5';
			$border_color = get_term_meta( $term_id, 'level_border_color', true ) ?: '#a7f3d0';
			$term         = get_term( $term_id );
			$name         = $term ? $term->name : '';

			return sprintf(
				'<span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;color:%s;background:%s;border:1px solid %s;">%s</span>',
				esc_attr( $badge_color ),
				esc_attr( $bg_color ),
				esc_attr( $border_color ),
				esc_html( $name )
			);
		} elseif ( 'order' === $column_name ) {
			$order = get_term_meta( $term_id, 'level_sort_order', true );
			return esc_html( '' !== $order ? $order : '0' );
		}
		return $content;
	}

	/**
	 * Set custom columns in 'course' admin table.
	 */
	public function set_custom_columns( $columns ) {
		$new_columns = [];
		$new_columns['cb']                 = $columns['cb'];
		$new_columns['thumbnail']          = esc_html__( 'Thumb', 'practice-problems-el' );
		$new_columns['title']              = esc_html__( 'Course Title', 'practice-problems-el' );
		$new_columns['taxonomy-course_level']    = esc_html__( 'Level', 'practice-problems-el' );
		$new_columns['taxonomy-course_category'] = esc_html__( 'Category', 'practice-problems-el' );
		$new_columns['stats']              = esc_html__( 'Stats', 'practice-problems-el' );
		$new_columns['instructor']         = esc_html__( 'Instructor', 'practice-problems-el' );
		$new_columns['order']              = esc_html__( 'Order', 'practice-problems-el' );
		$new_columns['date']               = $columns['date'];
		return $new_columns;
	}

	/**
	 * Render custom columns in 'course' admin table.
	 */
	public function render_custom_columns( $column, $post_id ) {
		if ( 'thumbnail' === $column ) {
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, [ 48, 48 ], [ 'style' => 'border-radius:6px;object-fit:cover;' ] );
			} else {
				$icon = get_post_meta( $post_id, '_course_icon', true ) ?: 'dashicons-welcome-learn-more';
				echo '<span class="dashicons ' . esc_attr( $icon ) . '" style="font-size:28px;width:28px;height:28px;color:#94a3b8;"></span>';
			}
		} elseif ( 'stats' === $column ) {
			$stats = self::get_course_statistics( $post_id );
			echo '<div style="font-size:12px;line-height:1.4;">';
			echo '<strong>' . esc_html( $stats['chapters'] ) . '</strong> ' . esc_html__( 'Chapters', 'practice-problems-el' ) . '<br>';
			echo '<strong>' . esc_html( $stats['problems'] ) . '</strong> ' . esc_html__( 'Problems', 'practice-problems-el' );
			if ( ! empty( $stats['duration'] ) ) {
				echo '<br><span style="color:#64748b;">⏱ ' . esc_html( $stats['duration'] ) . '</span>';
			}
			echo '</div>';
		} elseif ( 'instructor' === $column ) {
			$inst = get_post_meta( $post_id, '_course_instructor', true );
			echo esc_html( $inst ? $inst : '—' );
		} elseif ( 'order' === $column ) {
			$order = get_post_meta( $post_id, '_course_display_order', true );
			echo esc_html( '' !== $order ? $order : '0' );
		}
	}

	/**
	 * Helper: Retrieve course statistics (automatic calculation with manual override fallback).
	 *
	 * @param int $course_id Post ID.
	 * @return array
	 */
	public static function get_course_statistics( $course_id ) {
		$mode = get_post_meta( $course_id, '_course_stats_mode', true ) ?: 'auto';

		$duration = get_post_meta( $course_id, '_course_duration', true );
		$lessons  = get_post_meta( $course_id, '_course_lesson_count', true );

		if ( 'manual' === $mode ) {
			$chapters = get_post_meta( $course_id, '_course_chapter_count', true );
			$problems = get_post_meta( $course_id, '_course_problem_count', true );
			return [
				'chapters' => ( '' !== $chapters && null !== $chapters ) ? intval( $chapters ) : 0,
				'problems' => ( '' !== $problems && null !== $problems ) ? intval( $problems ) : 0,
				'lessons'  => ( '' !== $lessons && null !== $lessons ) ? intval( $lessons ) : 0,
				'duration' => $duration ?: '',
			];
		}

		// Automatic calculation mode:
		// Check if there is a matching math_course term or linked taxonomy
		$course_post = get_post( $course_id );
		$slug = $course_post ? $course_post->post_name : '';
		$title = $course_post ? $course_post->post_title : '';

		// 1. Calculate chapters
		$manual_chapters = get_post_meta( $course_id, '_course_chapter_count', true );
		$calculated_chapters = 0;

		// Check math_chapter taxonomy or math_course terms in math_note
		$course_term = get_term_by( 'slug', $slug, 'math_course' );
		if ( ! $course_term && $title ) {
			$course_term = get_term_by( 'name', $title, 'math_course' );
		}

		if ( $course_term ) {
			// Count distinct math_chapters used in this course's notes
			$notes_query = new \WP_Query( [
				'post_type'      => 'math_note',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => [
					[
						'taxonomy' => 'math_course',
						'field'    => 'term_id',
						'terms'    => $course_term->term_id,
					],
				],
			] );

			if ( ! empty( $notes_query->posts ) ) {
				$chapter_terms = wp_get_object_terms( $notes_query->posts, 'math_chapter', [ 'fields' => 'ids' ] );
				if ( ! is_wp_error( $chapter_terms ) && ! empty( $chapter_terms ) ) {
					$calculated_chapters = count( array_unique( $chapter_terms ) );
				} else {
					$calculated_chapters = count( $notes_query->posts );
				}
			}
		}

		$final_chapters = $calculated_chapters > 0 ? $calculated_chapters : ( '' !== $manual_chapters ? intval( $manual_chapters ) : 0 );

		// 2. Calculate practice problems
		$manual_problems = get_post_meta( $course_id, '_course_problem_count', true );
		$calculated_problems = 0;

		// If course topic matches problem_topic
		$topic_term = get_term_by( 'slug', $slug, 'problem_topic' );
		if ( ! $topic_term && $title ) {
			$topic_term = get_term_by( 'name', $title, 'problem_topic' );
		}

		if ( $topic_term ) {
			$problems_query = new \WP_Query( [
				'post_type'      => 'practice_problem',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'tax_query'      => [
					[
						'taxonomy' => 'problem_topic',
						'field'    => 'term_id',
						'terms'    => $topic_term->term_id,
					],
				],
			] );
			$calculated_problems = $problems_query->found_posts;
		}

		$final_problems = $calculated_problems > 0 ? $calculated_problems : ( '' !== $manual_problems ? intval( $manual_problems ) : 0 );

		return [
			'chapters' => $final_chapters,
			'problems' => $final_problems,
			'lessons'  => ( '' !== $lessons && null !== $lessons ) ? intval( $lessons ) : 0,
			'duration' => $duration ?: '',
		];
	}

	/**
	 * Automatically flush rewrite rules if needed.
	 */
	public function maybe_flush_rewrite_rules() {
		$rules = get_option( 'rewrite_rules' );
		if ( ! is_array( $rules ) || ! isset( $rules['course/([^/]+)/?$'] ) ) {
			flush_rewrite_rules( false );
		}
	}
}
