<?php
/**
 * Custom Post Type & Taxonomies for Practice Problems.
 *
 * @package PracticeProblems
 */

namespace PracticeProblems;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CPT
 */
class CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_practice_problem', [ $this, 'save_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	/**
	 * Register Custom Post Type.
	 */
	public function register_post_type() {
		$labels = [
			'name'               => _x( 'Practice Problems', 'post type general name', 'practice-problems-el' ),
			'singular_name'      => _x( 'Practice Problem', 'post type singular name', 'practice-problems-el' ),
			'menu_name'          => _x( 'Practice Problems', 'admin menu', 'practice-problems-el' ),
			'name_admin_bar'     => _x( 'Practice Problem', 'add new on admin bar', 'practice-problems-el' ),
			'add_new'            => _x( 'Add New', 'practice problem', 'practice-problems-el' ),
			'add_new_item'       => __( 'Add New Practice Problem', 'practice-problems-el' ),
			'new_item'           => __( 'New Practice Problem', 'practice-problems-el' ),
			'edit_item'          => __( 'Edit Practice Problem', 'practice-problems-el' ),
			'view_item'          => __( 'View Practice Problem', 'practice-problems-el' ),
			'all_items'          => __( 'All Practice Problems', 'practice-problems-el' ),
			'search_items'       => __( 'Search Practice Problems', 'practice-problems-el' ),
			'not_found'          => __( 'No practice problems found.', 'practice-problems-el' ),
			'not_found_in_trash' => __( 'No practice problems found in Trash.', 'practice-problems-el' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'practice-problem' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-welcome-learn-more',
			'supports'           => [ 'title', 'editor', 'excerpt', 'custom-fields' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'practice_problem', $args );
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies() {
		// Topic Taxonomy.
		register_taxonomy(
			'problem_topic',
			'practice_problem',
			[
				'hierarchical'      => true,
				'labels'            => [
					'name'          => __( 'Topics', 'practice-problems-el' ),
					'singular_name' => __( 'Topic', 'practice-problems-el' ),
					'search_items'  => __( 'Search Topics', 'practice-problems-el' ),
					'all_items'     => __( 'All Topics', 'practice-problems-el' ),
					'edit_item'     => __( 'Edit Topic', 'practice-problems-el' ),
					'update_item'   => __( 'Update Topic', 'practice-problems-el' ),
					'add_new_item'  => __( 'Add New Topic', 'practice-problems-el' ),
					'new_item_name' => __( 'New Topic Name', 'practice-problems-el' ),
					'menu_name'     => __( 'Topics', 'practice-problems-el' ),
				],
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'problem-topic' ],
				'show_in_rest'      => true,
			]
		);

		// Difficulty Taxonomy.
		register_taxonomy(
			'problem_difficulty',
			'practice_problem',
			[
				'hierarchical'      => true,
				'labels'            => [
					'name'          => __( 'Difficulties', 'practice-problems-el' ),
					'singular_name' => __( 'Difficulty', 'practice-problems-el' ),
					'search_items'  => __( 'Search Difficulties', 'practice-problems-el' ),
					'all_items'     => __( 'All Difficulties', 'practice-problems-el' ),
					'edit_item'     => __( 'Edit Difficulty', 'practice-problems-el' ),
					'update_item'   => __( 'Update Difficulty', 'practice-problems-el' ),
					'add_new_item'  => __( 'Add New Difficulty', 'practice-problems-el' ),
					'new_item_name' => __( 'New Difficulty Name', 'practice-problems-el' ),
					'menu_name'     => __( 'Difficulties', 'practice-problems-el' ),
				],
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'problem-difficulty' ],
				'show_in_rest'      => true,
			]
		);
	}

	/**
	 * Add Meta Boxes for Problem Details.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'pp_problem_details',
			__( 'Problem Details & Solution (with Live LaTeX Preview)', 'practice-problems-el' ),
			[ $this, 'render_meta_box' ],
			'practice_problem',
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue assets for practice_problem edit screen in WP Admin.
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'practice_problem' !== $screen->post_type ) {
			return;
		}

		// Configure MathJax before script loads
		$mathjax_config = [
			'tex' => [
				'inlineMath'          => [ [ '$', '$' ], [ '\\(', '\\)' ] ],
				'displayMath'         => [ [ '$$', '$$' ], [ '\\[', '\\]' ] ],
				'packages'            => [ 'base', 'ams', 'noundefined', 'autoload', 'physics', 'cancel', 'color', 'mathtools' ],
				'processEscapes'      => true,
				'processEnvironments' => true,
			],
			'options' => [
				'processHtmlClass' => 'pp-math-render|pp-live-preview|pp-problem-card|pp-statement-content|pp-step-body|pp-answer-val|lcs-equation|lcs-math-content',
				'ignoreHtmlClass'  => 'tex2jax_ignore',
			],
			'svg' => [
				'fontCache' => 'global',
			],
		];

		$config_script = 'window.MathJax = window.MathJax || ' . wp_json_encode( $mathjax_config ) . ';';
		wp_register_script( 'pp-mathjax-config', '', [], PRACTICE_PROBLEMS_VERSION );
		wp_enqueue_script( 'pp-mathjax-config' );
		wp_add_inline_script( 'pp-mathjax-config', $config_script, 'before' );

		// Enqueue MathJax 3 (SVG vector output)
		wp_enqueue_script(
			'pp-mathjax',
			'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js',
			[ 'pp-mathjax-config' ],
			'3.2.2',
			true
		);

		// Admin Meta Box CSS & JS
		wp_enqueue_style(
			'pp-admin-meta-box',
			PRACTICE_PROBLEMS_URL . 'assets/css/admin-meta-box.css',
			[],
			PRACTICE_PROBLEMS_VERSION
		);

		wp_enqueue_script(
			'pp-admin-meta-box',
			PRACTICE_PROBLEMS_URL . 'assets/js/admin-meta-box.js',
			[ 'pp-mathjax' ],
			PRACTICE_PROBLEMS_VERSION,
			true
		);
	}

	/**
	 * Prepare LaTeX content for rendering.
	 *
	 * Detects raw LaTeX formulas (e.g. \frac{a+b}{c} or \textbf{1}. Prove that...)
	 * and wraps them in display or inline math delimiters so MathJax renders them
	 * cleanly without showing raw code to visitors.
	 *
	 * @param string $content Raw input string.
	 * @param bool   $is_block Whether to use display/block math ($$..$$) or inline ($..$).
	 * @return string
	 */
	public static function prepare_latex( $content, $is_block = true ) {
		if ( empty( $content ) || ! is_string( $content ) ) {
			return '';
		}

		$text = trim( $content );

		// Check if already contains LaTeX math delimiters
		$has_delimiters = (
			strpos( $text, '$$' ) !== false ||
			strpos( $text, '\\[' ) !== false ||
			strpos( $text, '\\(' ) !== false ||
			preg_match( '/(?<!\\\\)\$[^$\s][^$]*?(?<!\\\\)\$/', $text )
		);

		if ( $has_delimiters ) {
			return $text;
		}

		// Replace standard LaTeX styling tags if found
		$text = preg_replace( '/\\\\textbf\{([^}]+)\}/', '<strong>$1</strong>', $text );
		$text = preg_replace( '/\\\\textit\{([^}]+)\}/', '<em>$1</em>', $text );
		$text = preg_replace( '/\\\\underline\{([^}]+)\}/', '<u>$1</u>', $text );

		// Check for common LaTeX math commands or math notation
		$has_latex_cmd = preg_match( '/\\\\(frac|sqrt|sin|cos|tan|cot|sec|csc|cosec|log|ln|lim|sum|int|prod|alpha|beta|gamma|theta|pi|infty|pm|times|cdot|le|ge|neq|approx|vec|hat|begin|end|matrix|cases|over|to|partial)\b/i', $text );
		$has_math_notation = preg_match( '/[a-zA-Z0-9]\^[0-9a-zA-Z{]|_[0-9a-zA-Z{]/', $text );

		if ( $has_latex_cmd || $has_math_notation ) {
			// Check if it's mixed with a sentence prefix (e.g. "Prove that \sin..." or "Evaluate \lim...")
			if ( preg_match( '/^(.*?\b(?:Prove\s+that|Evaluate|Find|Show\s+that|Calculate|Given\s+that|Where|If|Then)[:\s]+)(.+)$/i', $text, $matches ) ) {
				$prefix    = $matches[1];
				$math_part = trim( $matches[2] );
				return $prefix . ( $is_block ? '$$' . $math_part . '$$' : '$' . $math_part . '$' );
			} elseif ( ! preg_match( '/[a-zA-Z]{4,}\s+[a-zA-Z]{4,}/', $text ) ) {
				// Pure formula (no multiple English sentence words)
				return $is_block ? '$$' . $text . '$$' : '$' . $text . '$';
			} else {
				// Mixed English words with embedded LaTeX formulas - wrap formulas in \( ... \)
				return preg_replace_callback(
					'/(\\\\(?:frac\{[^{}]*\}\{[^{}]*\}|[a-zA-Z]+(?:\{[^{}]*\})?|[a-zA-Z0-9]+(?:\^[0-9a-zA-Z{}]|_[0-9a-zA-Z{}])+)(?:[^$\n\r<]*[=<>][^$\n\r<]*)?)/',
					function( $m ) {
						if ( 0 === strpos( $m[0], '<strong>' ) || 0 === strpos( $m[0], '<' ) ) {
							return $m[0];
						}
						return '\\(' . $m[0] . '\\)';
					},
					$text
				);
			}
		}

		return $text;
	}

	/**
	 * Render Meta Box with 2-Column Split Layout (Input on Left, Live LaTeX Preview on Right).
	 *
	 * @param \WP_Post $post Current post.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'pp_save_problem_meta', 'pp_problem_meta_nonce' );

		// Problem ID is strictly optional. Do not default to $post->ID!
		$problem_id = get_post_meta( $post->ID, '_pp_problem_id', true );
		$steps      = get_post_meta( $post->ID, '_pp_steps', true );
		$answer     = get_post_meta( $post->ID, '_pp_answer', true );
		?>
		<div class="pp-meta-box-wrap">

			<!-- Problem ID Badge Row (Optional Field) -->
			<div class="pp-meta-badge-row">
				<label for="pp_problem_id"><?php esc_html_e( 'Problem ID Badge Text', 'practice-problems-el' ); ?></label>
				<div class="pp-badge-input-wrap">
					<input type="text" 
						   id="pp_problem_id" 
						   name="pp_problem_id" 
						   value="<?php echo esc_attr( $problem_id ); ?>" 
						   class="regular-text" 
						   placeholder="<?php esc_attr_e( 'e.g. Problem 01 or EX-101 (Leave empty to hide badge)', 'practice-problems-el' ); ?>" 
						   autocomplete="off" />
				</div>
				<p class="description">
					<?php esc_html_e( 'Optional problem ID or badge (e.g. Problem 01, Ex-101). If left blank, no badge will be displayed.', 'practice-problems-el' ); ?>
				</p>
			</div>

			<!-- Split Layout: LaTeX Input on Left, Live LaTeX Preview on Right -->
			<div class="pp-meta-split-layout">

				<!-- Left Column: Inputs -->
				<div class="pp-meta-col-input">
					<div class="pp-col-header">
						<span class="dashicons dashicons-edit"></span>
						<span><?php esc_html_e( 'Problem Input & LaTeX Content', 'practice-problems-el' ); ?></span>
					</div>

					<div class="pp-form-group">
						<label for="pp_steps"><?php esc_html_e( 'Step-by-Step Solution', 'practice-problems-el' ); ?></label>
						<textarea id="pp_steps" 
								  name="pp_steps" 
								  rows="9" 
								  class="large-text code" 
								  placeholder="<?php esc_attr_e( "\\textbf{1}. Prove that \\sin^{-1}(\\csc\\theta) = \\frac{\\pi}{2} + i\\log\\cot\\frac{\\theta}{2}\n\\frac{a+b}{c}", 'practice-problems-el' ); ?>"><?php echo esc_textarea( $steps ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Enter one step per line. Full LaTeX code supported (e.g. \\frac{a+b}{c} or \\textbf{1}. Prove that...).', 'practice-problems-el' ); ?>
						</p>
					</div>

					<div class="pp-form-group">
						<label for="pp_answer"><?php esc_html_e( 'Final Answer', 'practice-problems-el' ); ?></label>
						<input type="text" 
							   id="pp_answer" 
							   name="pp_answer" 
							   value="<?php echo esc_attr( $answer ); ?>" 
							   class="large-text" 
							   placeholder="<?php esc_attr_e( 'e.g. Answer: 6 or \\frac{\\pi}{2}', 'practice-problems-el' ); ?>" />
						<p class="description">
							<?php esc_html_e( 'Final short answer, formula, or result.', 'practice-problems-el' ); ?>
						</p>
					</div>

					<div class="pp-latex-tips">
						<strong><?php esc_html_e( 'LaTeX Tips:', 'practice-problems-el' ); ?></strong>
						<?php esc_html_e( 'You can write raw LaTeX such as ', 'practice-problems-el' ); ?>
						<code>\frac{a+b}{c}</code>, <code>\sin^{-1}\theta</code>, <code>\sqrt{x^2+y^2}</code>, <?php esc_html_e( 'or standard delimiters like ', 'practice-problems-el' ); ?>
						<code>$$...$$</code>. <?php esc_html_e( 'The live preview on the right will render it automatically using MathJax.', 'practice-problems-el' ); ?>
					</div>
				</div>

				<!-- Right Column: Live LaTeX Preview -->
				<div class="pp-meta-col-preview">
					<div class="pp-preview-header">
						<div class="pp-preview-title">
							<span class="dashicons dashicons-visibility"></span>
							<span><?php esc_html_e( 'Live LaTeX Preview', 'practice-problems-el' ); ?></span>
						</div>
						<span class="pp-preview-status" id="pp-preview-status">
							<span class="pp-status-dot"></span> <?php esc_html_e( 'MathJax Ready', 'practice-problems-el' ); ?>
						</span>
					</div>

					<div class="pp-preview-card">
						<div class="pp-preview-badges-row">
							<span id="pp-preview-id-badge" 
								  class="pp-badge-id" 
								  style="<?php echo empty( $problem_id ) ? 'display:none;' : ''; ?>">
								<?php echo esc_html( $problem_id ); ?>
							</span>
							<span id="pp-preview-no-badge-notice" 
								  class="pp-preview-empty-hint" 
								  style="<?php echo ! empty( $problem_id ) ? 'display:none;' : ''; ?>">
								<em><?php esc_html_e( '(Badge hidden — Optional field is empty)', 'practice-problems-el' ); ?></em>
							</span>
						</div>

						<div class="pp-preview-section-title"><?php esc_html_e( 'Step-by-Step Solution', 'practice-problems-el' ); ?></div>
						<div id="pp-preview-steps-box" class="pp-preview-steps-box pp-math-render">
							<!-- Dynamic real-time preview populated by JS -->
						</div>

						<div class="pp-preview-section-title"><?php esc_html_e( 'Final Answer', 'practice-problems-el' ); ?></div>
						<div id="pp-preview-answer-box" class="pp-preview-answer-box pp-math-render">
							<!-- Dynamic real-time preview populated by JS -->
						</div>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Save Meta Box data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['pp_problem_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pp_problem_meta_nonce'] ) ), 'pp_save_problem_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['pp_problem_id'] ) ) {
			$badge = sanitize_text_field( wp_unslash( $_POST['pp_problem_id'] ) );
			update_post_meta( $post_id, '_pp_problem_id', trim( $badge ) );
		}

		if ( isset( $_POST['pp_steps'] ) ) {
			update_post_meta( $post_id, '_pp_steps', sanitize_textarea_field( wp_unslash( $_POST['pp_steps'] ) ) );
		}

		if ( isset( $_POST['pp_answer'] ) ) {
			update_post_meta( $post_id, '_pp_answer', wp_kses_post( wp_unslash( $_POST['pp_answer'] ) ) );
		}
	}
}
