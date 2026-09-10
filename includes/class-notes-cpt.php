<?php
/**
 * Math Notes CPT and Taxonomies Registration.
 *
 * @package PracticeProblems
 */

namespace PracticeProblems;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Notes_CPT
 */
class Notes_CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_cpt' ], 5 );
		add_action( 'init', [ $this, 'register_taxonomies' ], 5 );
		add_action( 'init', [ $this, 'maybe_flush_rewrite_rules' ], 99 );
		add_filter( 'manage_math_note_posts_columns', [ $this, 'set_custom_columns' ] );
		add_action( 'manage_math_note_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
		add_filter( 'the_content', [ $this, 'filter_single_note_content' ] );
		add_filter( 'hello_elementor_page_title', [ $this, 'suppress_hello_elementor_title' ] );
		add_action( 'template_redirect', [ $this, 'redirect_single_note_to_elementor_page' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_single_note_assets' ] );
	}

	/**
	 * Register 'math_note' Custom Post Type.
	 */
	public function register_cpt() {
		$labels = [
			'name'                  => esc_html_x( 'Math Notes', 'Post Type General Name', 'practice-problems-el' ),
			'singular_name'         => esc_html_x( 'Math Note', 'Post Type Singular Name', 'practice-problems-el' ),
			'menu_name'             => esc_html__( 'Math Notes', 'practice-problems-el' ),
			'name_admin_bar'        => esc_html__( 'Math Note', 'practice-problems-el' ),
			'archives'              => esc_html__( 'Note Archives', 'practice-problems-el' ),
			'attributes'            => esc_html__( 'Note Attributes', 'practice-problems-el' ),
			'parent_item_colon'     => esc_html__( 'Parent Note:', 'practice-problems-el' ),
			'all_items'             => esc_html__( 'All Notes', 'practice-problems-el' ),
			'add_new_item'          => esc_html__( 'Add New Note', 'practice-problems-el' ),
			'add_new'               => esc_html__( 'Add New', 'practice-problems-el' ),
			'new_item'              => esc_html__( 'New Note', 'practice-problems-el' ),
			'edit_item'             => esc_html__( 'Edit Note', 'practice-problems-el' ),
			'update_item'           => esc_html__( 'Update Note', 'practice-problems-el' ),
			'view_item'             => esc_html__( 'View Note', 'practice-problems-el' ),
			'view_items'            => esc_html__( 'View Notes', 'practice-problems-el' ),
			'search_items'          => esc_html__( 'Search Notes', 'practice-problems-el' ),
			'not_found'             => esc_html__( 'No notes found', 'practice-problems-el' ),
			'not_found_in_trash'    => esc_html__( 'No notes found in Trash', 'practice-problems-el' ),
		];

		$args = [
			'label'                 => esc_html__( 'Math Note', 'practice-problems-el' ),
			'description'           => esc_html__( 'Topic notes and study guides for math courses.', 'practice-problems-el' ),
			'labels'                => $labels,
			'supports'              => [ 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ],
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 26,
			'menu_icon'             => 'dashicons-welcome-learn-more',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rewrite'               => [ 'slug' => 'notes', 'with_front' => false ],
		];

		register_post_type( 'math_note', $args );
	}

	/**
	 * Register Course and Chapter Taxonomies.
	 */
	public function register_taxonomies() {
		// 1. Course Taxonomy (e.g. Calculus I, Calculus II, Trigonometry)
		$course_labels = [
			'name'              => esc_html_x( 'Courses', 'taxonomy general name', 'practice-problems-el' ),
			'singular_name'     => esc_html_x( 'Course', 'taxonomy singular name', 'practice-problems-el' ),
			'search_items'      => esc_html__( 'Search Courses', 'practice-problems-el' ),
			'all_items'         => esc_html__( 'All Courses', 'practice-problems-el' ),
			'parent_item'       => esc_html__( 'Parent Course', 'practice-problems-el' ),
			'parent_item_colon' => esc_html__( 'Parent Course:', 'practice-problems-el' ),
			'edit_item'         => esc_html__( 'Edit Course', 'practice-problems-el' ),
			'update_item'       => esc_html__( 'Update Course', 'practice-problems-el' ),
			'add_new_item'      => esc_html__( 'Add New Course', 'practice-problems-el' ),
			'new_item_name'     => esc_html__( 'New Course Name', 'practice-problems-el' ),
			'menu_name'         => esc_html__( 'Courses', 'practice-problems-el' ),
		];

		register_taxonomy( 'math_course', [ 'math_note' ], [
			'hierarchical'      => true,
			'labels'            => $course_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'courses', 'with_front' => false ],
		] );

		// 2. Chapter Taxonomy (e.g. Limits, Derivatives, Integration)
		$chapter_labels = [
			'name'              => esc_html_x( 'Chapters', 'taxonomy general name', 'practice-problems-el' ),
			'singular_name'     => esc_html_x( 'Chapter', 'taxonomy singular name', 'practice-problems-el' ),
			'search_items'      => esc_html__( 'Search Chapters', 'practice-problems-el' ),
			'all_items'         => esc_html__( 'All Chapters', 'practice-problems-el' ),
			'parent_item'       => esc_html__( 'Parent Chapter', 'practice-problems-el' ),
			'parent_item_colon' => esc_html__( 'Parent Chapter:', 'practice-problems-el' ),
			'edit_item'         => esc_html__( 'Edit Chapter', 'practice-problems-el' ),
			'update_item'       => esc_html__( 'Update Chapter', 'practice-problems-el' ),
			'add_new_item'      => esc_html__( 'Add New Chapter', 'practice-problems-el' ),
			'new_item_name'     => esc_html__( 'New Chapter Name', 'practice-problems-el' ),
			'menu_name'         => esc_html__( 'Chapters', 'practice-problems-el' ),
		];

		register_taxonomy( 'math_chapter', [ 'math_note' ], [
			'hierarchical'      => true,
			'labels'            => $chapter_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'chapters', 'with_front' => false ],
		] );
	}

	/**
	 * Set custom admin table columns.
	 */
	public function set_custom_columns( $columns ) {
		$new_columns = [];
		$new_columns['cb']            = $columns['cb'];
		$new_columns['title']         = esc_html__( 'Note Title', 'practice-problems-el' );
		$new_columns['taxonomy-math_course']  = esc_html__( 'Course', 'practice-problems-el' );
		$new_columns['taxonomy-math_chapter'] = esc_html__( 'Chapter', 'practice-problems-el' );
		$new_columns['mn_sections']   = esc_html__( 'Sections', 'practice-problems-el' );
		$new_columns['mn_order']      = esc_html__( 'Order', 'practice-problems-el' );
		$new_columns['mn_pdf']        = esc_html__( 'PDF', 'practice-problems-el' );
		$new_columns['date']          = $columns['date'];
		return $new_columns;
	}

	/**
	 * Render custom admin table column values.
	 */
	public function render_custom_columns( $column, $post_id ) {
		if ( 'mn_sections' === $column ) {
			$sections = get_post_meta( $post_id, '_mn_sections', true );
			$count = is_array( $sections ) ? count( $sections ) : 0;
			echo '<span class="badge" style="background:#f1f5f9;padding:3px 8px;border-radius:4px;font-weight:600;">' . esc_html( $count ) . ' sections</span>';
		} elseif ( 'mn_order' === $column ) {
			$order = get_post_meta( $post_id, '_mn_display_order', true );
			echo esc_html( '' !== $order ? $order : '0' );
		} elseif ( 'mn_pdf' === $column ) {
			$pdf = get_post_meta( $post_id, '_mn_pdf_file', true );
			if ( ! empty( $pdf ) ) {
				echo '<a href="' . esc_url( $pdf ) . '" target="_blank" style="color:#059669;font-weight:bold;">PDF</a>';
			} else {
				echo '<span style="color:#94a3b8;">—</span>';
			}
		}
	}

	/**
	 * Automatically flush rewrite rules if needed (e.g. archive rule removed or single rule missing).
	 */
	public function maybe_flush_rewrite_rules() {
		$rules = get_option( 'rewrite_rules' );
		$needs_flush = false;

		// If legacy CPT archive rule for 'notes/?$' still exists, flush it so Page 'notes' takes precedence
		if ( is_array( $rules ) && isset( $rules['notes/?$'] ) && false !== strpos( $rules['notes/?$'], 'post_type=math_note' ) ) {
			$needs_flush = true;
		}

		if ( ! is_array( $rules ) || ! isset( $rules['notes/([^/]+)/?$'] ) ) {
			$needs_flush = true;
		}

		if ( $needs_flush ) {
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Auto-render the full Topic Notes layout on single math_note post view.
	 * Allows standard WordPress single view to display the complete Topic Notes layout
	 * even without manual Elementor theme builder single post setup.
	 */
	public function filter_single_note_content( $content ) {
		// 1. NEVER filter inside admin, REST, AJAX
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $content;
		}

		// 2. NEVER filter inside Elementor editor or preview mode
		if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ) {
			return $content;
		}

		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return $content;
			}

			$post_id = get_the_ID();
			if ( $post_id ) {
				$doc = \Elementor\Plugin::$instance->documents->get( $post_id );
				if ( $doc && $doc->is_built_with_elementor() ) {
					return $content;
				}
			}
		}

		// 3. Only filter on frontend singular math_note
		if ( is_singular( 'math_note' ) && in_the_loop() && is_main_query() ) {
			// Enqueue CSS and JS assets
			wp_enqueue_style( 'topic-notes-frontend' );
			wp_enqueue_script( 'topic-notes-frontend' );

			// Check if Topic_Notes_Widget class is available
			if ( class_exists( '\PracticeProblems\Widgets\Topic_Notes_Widget' ) ) {
				ob_start();
				\PracticeProblems\Widgets\Topic_Notes_Widget::render_note_markup( get_the_ID() );
				return ob_get_clean();
			}
		}

		return $content;
	}

	/**
	 * Suppress Hello Elementor page header on single math_note view.
	 */
	public function suppress_hello_elementor_title( $show ) {
		if ( is_singular( 'math_note' ) ) {
			return false;
		}
		return $show;
	}

	/**
	 * Enqueue assets on frontend single math_note view if loaded directly.
	 */
	public function enqueue_single_note_assets() {
		if ( is_singular( 'math_note' ) && ! is_admin() ) {
			wp_enqueue_style( 'topic-notes-frontend' );
			wp_enqueue_script( 'topic-notes-frontend' );
		}
	}

	/**
	 * Automatically redirect frontend single math_note requests to the dedicated
	 * Elementor Single Topic Note page (/topic-note/?note=slug).
	 * Ensures visitors see the exact customized Elementor layout designed by the user.
	 */
	public function redirect_single_note_to_elementor_page() {
		if ( is_singular( 'math_note' ) && ! is_admin() ) {
			// Do not redirect inside Elementor editor or preview
			if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ) {
				return;
			}
			if ( class_exists( '\Elementor\Plugin' ) ) {
				if ( \Elementor\Plugin::$instance->preview->is_preview_mode() || \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					return;
				}
			}

			$post = get_queried_object();
			if ( $post && ! empty( $post->post_name ) ) {
				$topic_note_page = get_page_by_path( 'topic-note' );
				$base_url = ( $topic_note_page && 'publish' === $topic_note_page->post_status )
					? get_permalink( $topic_note_page->ID )
					: home_url( '/topic-note/' );

				$target_url = add_query_arg( 'note', $post->post_name, $base_url );
				wp_safe_redirect( $target_url, 301 );
				exit;
			}
		}
	}
}
