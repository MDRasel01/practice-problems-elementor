<?php
/**
 * Course Meta Boxes & Admin Interface.
 *
 * @package PracticeProblems
 */

namespace PracticeProblems\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Course_Meta_Boxes
 */
class Course_Meta_Boxes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'save_post_course', [ $this, 'save_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	/**
	 * Enqueue admin scripts & styles.
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post_type;
		if ( 'course' !== $post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	/**
	 * Register Meta Boxes.
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'course_info_settings',
			esc_html__( 'Course Information & Presentation', 'practice-problems-el' ),
			[ $this, 'render_info_meta_box' ],
			'course',
			'normal',
			'high'
		);

		add_meta_box(
			'course_stats_settings',
			esc_html__( 'Course Statistics & Metrics', 'practice-problems-el' ),
			[ $this, 'render_stats_meta_box' ],
			'course',
			'side',
			'default'
		);
	}

	/**
	 * Render Course Information Meta Box.
	 */
	public function render_info_meta_box( $post ) {
		wp_nonce_field( 'course_save_meta_nonce', 'course_meta_nonce' );

		$short_desc     = get_post_meta( $post->ID, '_course_short_description', true );
		$custom_url     = get_post_meta( $post->ID, '_course_custom_url', true );
		$pdf_url        = get_post_meta( $post->ID, '_course_pdf_url', true );
		$instructor     = get_post_meta( $post->ID, '_course_instructor', true );
		$duration       = get_post_meta( $post->ID, '_course_duration', true );
		$icon           = get_post_meta( $post->ID, '_course_icon', true ) ?: 'dashicons-welcome-learn-more';
		$badge_text     = get_post_meta( $post->ID, '_course_badge_text', true );
		$order          = get_post_meta( $post->ID, '_course_display_order', true );
		?>
		<style>
			.course-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
			.course-meta-row { margin-bottom: 16px; }
			.course-meta-row label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; }
			.course-meta-row input[type="text"],
			.course-meta-row input[type="number"],
			.course-meta-row input[type="url"],
			.course-meta-row textarea,
			.course-meta-row select { width: 100%; }
			.course-meta-row .description { font-size: 12px; color: #64748b; margin-top: 4px; }
			.course-upload-wrap { display: flex; gap: 8px; align-items: center; }
		</style>

		<div class="course-meta-row">
			<label for="_course_short_description"><?php esc_html_e( 'Short Course Description / Subtitle', 'practice-problems-el' ); ?></label>
			<textarea id="_course_short_description" name="_course_short_description" rows="3" placeholder="<?php esc_attr_e( 'Comprehensive masterclass covering functions, limits, continuity, and differential calculus...', 'practice-problems-el' ); ?>"><?php echo esc_textarea( $short_desc ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Brief overview displayed on course cards and listing previews.', 'practice-problems-el' ); ?></p>
		</div>

		<div class="course-meta-grid">
			<div class="course-meta-row">
				<label for="_course_instructor"><?php esc_html_e( 'Instructor Name', 'practice-problems-el' ); ?></label>
				<input type="text" id="_course_instructor" name="_course_instructor" value="<?php echo esc_attr( $instructor ); ?>" placeholder="<?php esc_attr_e( 'e.g. Dr. Leonard Euler', 'practice-problems-el' ); ?>">
			</div>

			<div class="course-meta-row">
				<label for="_course_duration"><?php esc_html_e( 'Estimated Duration', 'practice-problems-el' ); ?></label>
				<input type="text" id="_course_duration" name="_course_duration" value="<?php echo esc_attr( $duration ); ?>" placeholder="<?php esc_attr_e( 'e.g. 14 Hours or 8 Weeks', 'practice-problems-el' ); ?>">
			</div>
		</div>

		<div class="course-meta-grid">
			<div class="course-meta-row">
				<label for="_course_icon"><?php esc_html_e( 'Course Icon Class / Dashicon', 'practice-problems-el' ); ?></label>
				<input type="text" id="_course_icon" name="_course_icon" value="<?php echo esc_attr( $icon ); ?>" placeholder="e.g. dashicons-calculator or fa-solid fa-square-root-variable">
				<p class="description"><?php esc_html_e( 'Dashicon or FontAwesome class if not using thumbnail.', 'practice-problems-el' ); ?></p>
			</div>

			<div class="course-meta-row">
				<label for="_course_badge_text"><?php esc_html_e( 'Custom Badge / Status (Optional)', 'practice-problems-el' ); ?></label>
				<input type="text" id="_course_badge_text" name="_course_badge_text" value="<?php echo esc_attr( $badge_text ); ?>" placeholder="<?php esc_attr_e( 'e.g. Popular, Featured, New', 'practice-problems-el' ); ?>">
				<p class="description"><?php esc_html_e( 'Highlights special status over the card thumbnail.', 'practice-problems-el' ); ?></p>
			</div>
		</div>

		<div class="course-meta-row">
			<label for="_course_custom_url"><?php esc_html_e( 'Custom Target URL (Optional Override)', 'practice-problems-el' ); ?></label>
			<input type="url" id="_course_custom_url" name="_course_custom_url" value="<?php echo esc_attr( $custom_url ); ?>" placeholder="https://...">
			<p class="description"><?php esc_html_e( 'Leave blank to link to standard course page/archive (/courses/calculus-i/). Set a custom link if this redirects to an external LMS or custom landing page.', 'practice-problems-el' ); ?></p>
		</div>

		<div class="course-meta-row">
			<label for="_course_pdf_url"><?php esc_html_e( 'Course PDF / Syllabus File', 'practice-problems-el' ); ?></label>
			<div class="course-upload-wrap">
				<input type="text" id="_course_pdf_url" name="_course_pdf_url" value="<?php echo esc_attr( $pdf_url ); ?>" placeholder="https://.../syllabus.pdf">
				<button type="button" class="button" id="course_pdf_upload_btn"><?php esc_html_e( 'Select PDF', 'practice-problems-el' ); ?></button>
			</div>
			<p class="description"><?php esc_html_e( 'Attach downloadable PDF notes or syllabus document.', 'practice-problems-el' ); ?></p>
		</div>

		<div class="course-meta-row">
			<label for="_course_display_order"><?php esc_html_e( 'Display Order', 'practice-problems-el' ); ?></label>
			<input type="number" id="_course_display_order" name="_course_display_order" value="<?php echo esc_attr( '' !== $order ? $order : '0' ); ?>" min="0" step="1" style="max-width:140px;">
			<p class="description"><?php esc_html_e( 'Used when sorting by "Course Order" (lower numbers appear first).', 'practice-problems-el' ); ?></p>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#course_pdf_upload_btn').on('click', function(e) {
				e.preventDefault();
				var file_frame = wp.media.frames.file_frame = wp.media({
					title: '<?php echo esc_js( __( 'Select or Upload Course PDF', 'practice-problems-el' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Use this file', 'practice-problems-el' ) ); ?>' },
					multiple: false,
					library: { type: 'application/pdf' }
				});
				file_frame.on('select', function() {
					var attachment = file_frame.state().get('selection').first().toJSON();
					$('#_course_pdf_url').val(attachment.url);
				});
				file_frame.open();
			});
		});
		</script>
		<?php
	}

	/**
	 * Render Course Statistics & Metrics Meta Box.
	 */
	public function render_stats_meta_box( $post ) {
		$mode          = get_post_meta( $post->ID, '_course_stats_mode', true ) ?: 'auto';
		$manual_chap   = get_post_meta( $post->ID, '_course_chapter_count', true );
		$manual_prob   = get_post_meta( $post->ID, '_course_problem_count', true );
		$lesson_count  = get_post_meta( $post->ID, '_course_lesson_count', true );

		$stats = \PracticeProblems\Course_CPT::get_course_statistics( $post->ID );
		?>
		<div style="margin-bottom: 12px;">
			<label style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Calculation Mode', 'practice-problems-el' ); ?></label>
			<select name="_course_stats_mode" id="_course_stats_mode" style="width:100%;">
				<option value="auto" <?php selected( $mode, 'auto' ); ?>><?php esc_html_e( 'Automatic (From Linked Chapters & Problems)', 'practice-problems-el' ); ?></option>
				<option value="manual" <?php selected( $mode, 'manual' ); ?>><?php esc_html_e( 'Manual Override', 'practice-problems-el' ); ?></option>
			</select>
		</div>

		<div style="background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:6px;margin-bottom:14px;font-size:12px;">
			<strong><?php esc_html_e( 'Current Computed Stats:', 'practice-problems-el' ); ?></strong>
			<ul style="margin:4px 0 0 16px;list-style:disc;">
				<li><?php echo esc_html( $stats['chapters'] ); ?> <?php esc_html_e( 'Chapters', 'practice-problems-el' ); ?></li>
				<li><?php echo esc_html( $stats['problems'] ); ?> <?php esc_html_e( 'Problems', 'practice-problems-el' ); ?></li>
				<?php if ( ! empty( $stats['lessons'] ) ) : ?>
					<li><?php echo esc_html( $stats['lessons'] ); ?> <?php esc_html_e( 'Lessons', 'practice-problems-el' ); ?></li>
				<?php endif; ?>
			</ul>
		</div>

		<div id="course_manual_stats_wrap" style="<?php echo 'manual' === $mode ? '' : 'display:none;'; ?>">
			<div style="margin-bottom: 10px;">
				<label for="_course_chapter_count" style="display:block;font-weight:600;margin-bottom:4px;font-size:12px;"><?php esc_html_e( 'Manual Chapter Count', 'practice-problems-el' ); ?></label>
				<input type="number" id="_course_chapter_count" name="_course_chapter_count" value="<?php echo esc_attr( $manual_chap ); ?>" min="0" style="width:100%;" placeholder="e.g. 6">
			</div>

			<div style="margin-bottom: 10px;">
				<label for="_course_problem_count" style="display:block;font-weight:600;margin-bottom:4px;font-size:12px;"><?php esc_html_e( 'Manual Problem Count', 'practice-problems-el' ); ?></label>
				<input type="number" id="_course_problem_count" name="_course_problem_count" value="<?php echo esc_attr( $manual_prob ); ?>" min="0" style="width:100%;" placeholder="e.g. 180">
			</div>
		</div>

		<div style="margin-bottom: 10px;">
			<label for="_course_lesson_count" style="display:block;font-weight:600;margin-bottom:4px;font-size:12px;"><?php esc_html_e( 'Lesson Count (Optional)', 'practice-problems-el' ); ?></label>
			<input type="number" id="_course_lesson_count" name="_course_lesson_count" value="<?php echo esc_attr( $lesson_count ); ?>" min="0" style="width:100%;" placeholder="e.g. 36">
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#_course_stats_mode').on('change', function() {
				if ($(this).val() === 'manual') {
					$('#course_manual_stats_wrap').slideDown(150);
				} else {
					$('#course_manual_stats_wrap').slideUp(150);
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Save Meta Boxes.
	 */
	public function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['course_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['course_meta_nonce'] ), 'course_save_meta_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Text fields
		$text_fields = [
			'_course_short_description' => 'sanitize_textarea_field',
			'_course_custom_url'        => 'esc_url_raw',
			'_course_pdf_url'           => 'esc_url_raw',
			'_course_instructor'        => 'sanitize_text_field',
			'_course_duration'          => 'sanitize_text_field',
			'_course_icon'              => 'sanitize_text_field',
			'_course_badge_text'        => 'sanitize_text_field',
			'_course_stats_mode'        => 'sanitize_text_field',
		];

		foreach ( $text_fields as $meta_key => $sanitizer ) {
			if ( isset( $_POST[ $meta_key ] ) ) {
				$val = call_user_func( $sanitizer, wp_unslash( $_POST[ $meta_key ] ) );
				update_post_meta( $post_id, $meta_key, $val );
			}
		}

		// Numeric fields
		$numeric_fields = [
			'_course_display_order',
			'_course_chapter_count',
			'_course_problem_count',
			'_course_lesson_count',
		];

		foreach ( $numeric_fields as $meta_key ) {
			if ( isset( $_POST[ $meta_key ] ) && '' !== trim( $_POST[ $meta_key ] ) ) {
				update_post_meta( $post_id, $meta_key, intval( $_POST[ $meta_key ] ) );
			} elseif ( isset( $_POST[ $meta_key ] ) ) {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}
}
