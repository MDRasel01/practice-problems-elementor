<?php
/**
 * Math Notes Meta Boxes and Section Builder.
 *
 * @package PracticeProblems
 */

namespace PracticeProblems\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Notes_Meta_Boxes
 */
class Notes_Meta_Boxes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'save_post_math_note', [ $this, 'save_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	/**
	 * Enqueue admin scripts & styles for the Meta Box.
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post_type;
		if ( 'math_note' !== $post_type ) {
			return;
		}

		wp_enqueue_media();
	}

	/**
	 * Register Meta Boxes.
	 */
	public function register_meta_boxes() {
		// 1. Basic Note Settings
		add_meta_box(
			'mn_note_settings',
			esc_html__( 'Note Information & PDF Settings', 'practice-problems-el' ),
			[ $this, 'render_settings_meta_box' ],
			'math_note',
			'normal',
			'high'
		);

		// 2. Structured Section Builder
		add_meta_box(
			'mn_sections_builder',
			esc_html__( 'Note Sections Builder (TOC & Content)', 'practice-problems-el' ),
			[ $this, 'render_sections_builder_meta_box' ],
			'math_note',
			'normal',
			'high'
		);
	}

	/**
	 * Render Settings Meta Box.
	 */
	public function render_settings_meta_box( $post ) {
		wp_nonce_field( 'mn_save_settings_nonce', 'mn_settings_nonce' );

		$summary       = get_post_meta( $post->ID, '_mn_summary', true );
		$order         = get_post_meta( $post->ID, '_mn_display_order', true );
		$pdf_file      = get_post_meta( $post->ID, '_mn_pdf_file', true );
		$pdf_label     = get_post_meta( $post->ID, '_mn_pdf_label', true );
		$related_prob  = get_post_meta( $post->ID, '_mn_related_problems', true );
		$prev_override = get_post_meta( $post->ID, '_mn_prev_override', true );
		$next_override = get_post_meta( $post->ID, '_mn_next_override', true );
		?>
		<style>
			.mn-meta-row { margin-bottom: 16px; }
			.mn-meta-row label { font-weight: 600; display: block; margin-bottom: 6px; }
			.mn-meta-row input[type="text"], .mn-meta-row input[type="number"], .mn-meta-row textarea { width: 100%; }
			.mn-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
		</style>
		<div class="mn-meta-row">
			<label for="mn_summary"><?php esc_html_e( 'Short Summary / Subtitle', 'practice-problems-el' ); ?></label>
			<textarea id="mn_summary" name="mn_summary" rows="2" placeholder="<?php esc_attr_e( 'e.g. Differentiate compositions of functions, layer by layer.', 'practice-problems-el' ); ?>"><?php echo esc_textarea( $summary ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Displayed directly beneath the main H1 title on the note page.', 'practice-problems-el' ); ?></p>
		</div>

		<div class="mn-meta-grid">
			<div class="mn-meta-row">
				<label for="mn_display_order"><?php esc_html_e( 'Display / Sequence Order', 'practice-problems-el' ); ?></label>
				<input type="number" id="mn_display_order" name="mn_display_order" value="<?php echo esc_attr( '' !== $order ? $order : '10' ); ?>" step="1">
				<p class="description"><?php esc_html_e( 'Used for automatic Previous / Next topic sequence (e.g. 10, 20, 30).', 'practice-problems-el' ); ?></p>
			</div>
			<div class="mn-meta-row">
				<label for="mn_pdf_label"><?php esc_html_e( 'PDF Button Label', 'practice-problems-el' ); ?></label>
				<input type="text" id="mn_pdf_label" name="mn_pdf_label" value="<?php echo esc_attr( ! empty( $pdf_label ) ? $pdf_label : __( 'Download PDF', 'practice-problems-el' ) ); ?>">
			</div>
		</div>

		<div class="mn-meta-row">
			<label for="mn_pdf_file"><?php esc_html_e( 'PDF Document URL', 'practice-problems-el' ); ?></label>
			<div style="display:flex;gap:8px;">
				<input type="text" id="mn_pdf_file" name="mn_pdf_file" value="<?php echo esc_url( $pdf_file ); ?>" placeholder="https://example.com/notes.pdf">
				<button type="button" class="button" id="mn_upload_pdf_btn"><?php esc_html_e( 'Upload / Select PDF', 'practice-problems-el' ); ?></button>
			</div>
		</div>

		<div class="mn-meta-grid">
			<div class="mn-meta-row">
				<label for="mn_prev_override"><?php esc_html_e( 'Manual Previous Topic (Optional URL or Title)', 'practice-problems-el' ); ?></label>
				<input type="text" id="mn_prev_override" name="mn_prev_override" value="<?php echo esc_attr( $prev_override ); ?>" placeholder="<?php esc_attr_e( 'Leave empty for automatic navigation', 'practice-problems-el' ); ?>">
			</div>
			<div class="mn-meta-row">
				<label for="mn_next_override"><?php esc_html_e( 'Manual Next Topic (Optional URL or Title)', 'practice-problems-el' ); ?></label>
				<input type="text" id="mn_next_override" name="mn_next_override" value="<?php echo esc_attr( $next_override ); ?>" placeholder="<?php esc_attr_e( 'Leave empty for automatic navigation', 'practice-problems-el' ); ?>">
			</div>
		</div>

		<script>
		jQuery(document).ready(function($){
			$('#mn_upload_pdf_btn').on('click', function(e){
				e.preventDefault();
				var frame = wp.media({
					title: 'Select or Upload PDF Note',
					button: { text: 'Use this PDF' },
					multiple: false,
					library: { type: 'application/pdf' }
				});
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					$('#mn_pdf_file').val(attachment.url);
				});
				frame.open();
			});
		});
		</script>
		<?php
	}

	/**
	 * Render Sections Builder Meta Box.
	 */
	public function render_sections_builder_meta_box( $post ) {
		wp_nonce_field( 'mn_save_sections_nonce', 'mn_sections_nonce' );

		$raw_sections = get_post_meta( $post->ID, '_mn_sections', true );
		$sections = is_array( $raw_sections ) ? $raw_sections : [];

		// Default initial sections if empty
		if ( empty( $sections ) ) {
			$sections = [
				[
					'type'    => 'overview',
					'title'   => 'Overview',
					'show_toc'=> 'yes',
					'content' => '',
				],
				[
					'type'         => 'definition',
					'title'        => 'Definition',
					'label'        => 'Definition',
					'accent_color' => '#3b82f6',
					'show_toc'     => 'yes',
					'content'      => '',
				],
				[
					'type'         => 'formula',
					'title'        => 'Key Formula',
					'formula'      => '',
					'formula_type' => 'latex',
					'explanation'  => '',
					'show_toc'     => 'yes',
				],
				[
					'type'           => 'worked_example',
					'title'          => 'Worked Example',
					'problem_label'  => 'Problem',
					'problem'        => '',
					'steps'          => "1. Write the expression\n2. Factor numerator and cancel common terms\n3. Substitute limit value",
					'solution_label' => 'Solution',
					'solution'       => '',
					'show_toc'       => 'yes',
				],
				[
					'type'        => 'common_mistake',
					'title'       => 'Common Mistake',
					'mistake_title' => "Don't cancel before expanding",
					'description' => 'You can only cancel factors that are multiplied across the entire numerator and denominator.',
					'severity'    => 'warning',
					'show_toc'    => 'yes',
				],
				[
					'type'        => 'next_steps',
					'title'       => 'Next Steps',
					'description' => 'Work through practice problems to build speed and confidence with this concept.',
					'button_text' => 'Go to practice problems',
					'button_link' => '#',
					'show_toc'    => 'yes',
				],
			];
		}
		?>
		<style>
			.mn-sections-wrapper { margin-top: 10px; }
			.mn-section-card {
				background: #ffffff;
				border: 1px solid #cbd5e1;
				border-radius: 8px;
				margin-bottom: 12px;
				overflow: hidden;
				box-shadow: 0 1px 3px rgba(0,0,0,0.04);
			}
			.mn-section-header {
				background: #f8fafc;
				padding: 10px 16px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				border-bottom: 1px solid #e2e8f0;
				cursor: pointer;
				user-select: none;
			}
			.mn-section-header h4 { margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
			.mn-section-type-badge {
				font-size: 11px;
				padding: 2px 8px;
				border-radius: 4px;
				background: #e0e7ff;
				color: #3730a3;
				text-transform: uppercase;
				font-weight: 700;
			}
			.mn-section-body { padding: 16px; display: block; }
			.mn-field-group { margin-bottom: 14px; }
			.mn-field-group label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
			.mn-field-group input[type="text"], .mn-field-group textarea, .mn-field-group select { width: 100%; }
			.mn-btn-remove { color: #dc2626; background: none; border: none; cursor: pointer; font-size: 13px; }
			.mn-btn-remove:hover { text-decoration: underline; }
			.mn-add-section-bar { margin-top: 16px; padding: 16px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; gap: 12px; }
		</style>

		<div class="mn-sections-wrapper" id="mn_sections_container">
			<?php foreach ( $sections as $i => $sec ) : 
				$type  = $sec['type'] ?? 'overview';
				$title = $sec['title'] ?? ucfirst( str_replace( '_', ' ', $type ) );
				$toc   = ( ! isset( $sec['show_toc'] ) || 'yes' === $sec['show_toc'] ) ? 'yes' : 'no';
			?>
				<div class="mn-section-card" data-index="<?php echo esc_attr( $i ); ?>">
					<div class="mn-section-header">
						<h4>
							<span class="dashicons dashicons-menu"></span>
							<span class="mn-sec-title-display"><?php echo esc_html( $title ); ?></span>
							<span class="mn-section-type-badge"><?php echo esc_html( $type ); ?></span>
						</h4>
						<div style="display:flex;align-items:center;gap:12px;">
							<label style="font-size:12px;cursor:pointer;">
								<input type="checkbox" name="mn_sec[<?php echo esc_attr( $i ); ?>][show_toc]" value="yes" <?php checked( $toc, 'yes' ); ?>>
								<?php esc_html_e( 'Show in TOC', 'practice-problems-el' ); ?>
							</label>
							<button type="button" class="mn-btn-remove">&times; <?php esc_html_e( 'Remove', 'practice-problems-el' ); ?></button>
						</div>
					</div>

					<div class="mn-section-body">
						<input type="hidden" name="mn_sec[<?php echo esc_attr( $i ); ?>][type]" value="<?php echo esc_attr( $type ); ?>">

						<div class="mn-field-group">
							<label><?php esc_html_e( 'Section Title (Heading)', 'practice-problems-el' ); ?></label>
							<input type="text" class="mn-sec-title-input" name="mn_sec[<?php echo esc_attr( $i ); ?>][title]" value="<?php echo esc_attr( $title ); ?>">
						</div>

						<?php if ( 'overview' === $type || 'custom' === $type ) : ?>
							<div class="mn-field-group">
								<label><?php esc_html_e( 'Content', 'practice-problems-el' ); ?></label>
								<textarea name="mn_sec[<?php echo esc_attr( $i ); ?>][content]" rows="5"><?php echo esc_textarea( $sec['content'] ?? '' ); ?></textarea>
							</div>

						<?php elseif ( 'definition' === $type ) : ?>
							<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mn-field-group">
								<div>
									<label><?php esc_html_e( 'Badge Label', 'practice-problems-el' ); ?></label>
									<input type="text" name="mn_sec[<?php echo esc_attr( $i ); ?>][label]" value="<?php echo esc_attr( $sec['label'] ?? 'Definition' ); ?>">
								</div>
								<div>
									<label><?php esc_html_e( 'Accent Border Color', 'practice-problems-el' ); ?></label>
									<input type="text" name="mn_sec[<?php echo esc_attr( $i ); ?>][accent_color]" value="<?php echo esc_attr( $sec['accent_color'] ?? '#3b82f6' ); ?>">
								</div>
							</div>
							<div class="mn-field-group">
								<label><?php esc_html_e( 'Definition Text', 'practice-problems-el' ); ?></label>
								<textarea name="mn_sec[<?php echo esc_attr( $i ); ?>][content]" rows="4"><?php echo esc_textarea( $sec['content'] ?? '' ); ?></textarea>
							</div>

						<?php elseif ( 'formula' === $type ) : ?>
							<div class="mn-field-group">
								<label><?php esc_html_e( 'Formula Content (LaTeX, Math, or Plain Text)', 'practice-problems-el' ); ?></label>
								<input type="text" name="mn_sec[<?php echo esc_attr( $i ); ?>][formula]" value="<?php echo esc_attr( $sec['formula'] ?? '' ); ?>" placeholder="e.g. f'(x) = lim h->0 [f(x+h) - f(x)] / h">
							</div>
							<div class="mn-field-group">
								<label><?php esc_html_e( 'Formula Explanation / Description', 'practice-problems-el' ); ?></label>
								<textarea name="mn_sec[<?php echo esc_attr( $i ); ?>][explanation]" rows="3"><?php echo esc_textarea( $sec['explanation'] ?? '' ); ?></textarea>
							</div>

						<?php elseif ( 'worked_example' === $type ) : ?>
							<div class="mn-field-group">
								<label><?php esc_html_e( 'Problem Statement / Question', 'practice-problems-el' ); ?></label>
								<textarea name="mn_sec[<?php echo esc_attr( $i ); ?>][problem]" rows="3"><?php echo esc_textarea( $sec['problem'] ?? '' ); ?></textarea>
							</div>
							<div class="mn-field-group">
								<label><?php esc_html_e( 'Solution Steps (One step per line)', 'practice-problems-el' ); ?></label>
								<textarea name="mn_sec[<?php echo esc_attr( $i ); ?>][steps]" rows="5" placeholder="Step 1: ...&#10;Step 2: ..."><?php echo esc_textarea( $sec['steps'] ?? '' ); ?></textarea>
							</div>
							<div style="display:grid;grid-template-columns:120px 1fr;gap:12px;" class="mn-field-group">
								<div>
									<label><?php esc_html_e( 'Solution Label', 'practice-problems-el' ); ?></label>
									<input type="text" name="mn_sec[<?php echo esc_attr( $i ); ?>][solution_label]" value="<?php echo esc_attr( $sec['solution_label'] ?? 'Solution' ); ?>">
								</div>
								<div>
									<label><?php esc_html_e( 'Final Solution / Answer', 'practice-problems-el' ); ?></label>
									<input type="text" name="mn_sec[<?php echo esc_attr( $i ); ?>][solution]" value="<?php echo esc_attr( $sec['solution'] ?? '' ); ?>">
								</div>
							</div>

						<?php elseif ( 'common_mistake' === $type ) : ?>
							<div style="display:grid;grid-template-columns:1fr 140px;gap:12px;" class="mn-field-group">
								<div>
									<label><?php esc_html_e( 'Mistake Heading / Rule', 'practice-problems-el' ); ?></label>
									<input type="text" name="mn_sec[<?php echo esc_attr( $i ); ?>][mistake_title]" value="<?php echo esc_attr( $sec['mistake_title'] ?? '' ); ?>" placeholder="e.g. Don't cancel before expanding">
								</div>
								<div>
									<label><?php esc_html_e( 'Severity', 'practice-problems-el' ); ?></label>
									<select name="mn_sec[<?php echo esc_attr( $i ); ?>][severity]">
										<option value="warning" <?php selected( $sec['severity'] ?? 'warning', 'warning' ); ?>><?php esc_html_e( 'Warning', 'practice-problems-el' ); ?></option>
										<option value="critical" <?php selected( $sec['severity'] ?? '', 'critical' ); ?>><?php esc_html_e( 'Critical', 'practice-problems-el' ); ?></option>
										<option value="info" <?php selected( $sec['severity'] ?? '', 'info' ); ?>><?php esc_html_e( 'Info / Tip', 'practice-problems-el' ); ?></option>
									</select>
								</div>
							</div>
							<div class="mn-field-group">
								<label><?php esc_html_e( 'Explanation / Advice', 'practice-problems-el' ); ?></label>
								<textarea name="mn_sec[<?php echo esc_attr( $i ); ?>][description]" rows="3"><?php echo esc_textarea( $sec['description'] ?? '' ); ?></textarea>
							</div>

						<?php elseif ( 'next_steps' === $type ) : ?>
							<div class="mn-field-group">
								<label><?php esc_html_e( 'Description / Next Recommended Action', 'practice-problems-el' ); ?></label>
								<textarea name="mn_sec[<?php echo esc_attr( $i ); ?>][description]" rows="2"><?php echo esc_textarea( $sec['description'] ?? '' ); ?></textarea>
							</div>
							<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mn-field-group">
								<div>
									<label><?php esc_html_e( 'Button Text', 'practice-problems-el' ); ?></label>
									<input type="text" name="mn_sec[<?php echo esc_attr( $i ); ?>][button_text]" value="<?php echo esc_attr( $sec['button_text'] ?? 'Go to practice problems' ); ?>">
								</div>
								<div>
									<label><?php esc_html_e( 'Button URL / Target', 'practice-problems-el' ); ?></label>
									<input type="text" name="mn_sec[<?php echo esc_attr( $i ); ?>][button_link]" value="<?php echo esc_attr( $sec['button_link'] ?? '#' ); ?>">
								</div>
							</div>
						<?php endif; ?>

					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="mn-add-section-bar">
			<strong><?php esc_html_e( 'Add New Section:', 'practice-problems-el' ); ?></strong>
			<select id="mn_new_section_type">
				<option value="overview"><?php esc_html_e( 'Overview', 'practice-problems-el' ); ?></option>
				<option value="definition"><?php esc_html_e( 'Definition', 'practice-problems-el' ); ?></option>
				<option value="formula"><?php esc_html_e( 'Key Formula', 'practice-problems-el' ); ?></option>
				<option value="worked_example"><?php esc_html_e( 'Worked Example', 'practice-problems-el' ); ?></option>
				<option value="common_mistake"><?php esc_html_e( 'Common Mistake', 'practice-problems-el' ); ?></option>
				<option value="next_steps"><?php esc_html_e( 'Next Steps', 'practice-problems-el' ); ?></option>
				<option value="custom"><?php esc_html_e( 'Custom Content', 'practice-problems-el' ); ?></option>
			</select>
			<button type="button" class="button button-primary" id="mn_add_section_btn">+ <?php esc_html_e( 'Add Section', 'practice-problems-el' ); ?></button>
		</div>

		<script>
		jQuery(document).ready(function($){
			// Live title update in header
			$(document).on('input', '.mn-sec-title-input', function(){
				$(this).closest('.mn-section-card').find('.mn-sec-title-display').text($(this).val());
			});

			// Remove section
			$(document).on('click', '.mn-btn-remove', function(e){
				e.preventDefault();
				if(confirm('<?php echo esc_js( __( 'Remove this section?', 'practice-problems-el' ) ); ?>')){
					$(this).closest('.mn-section-card').slideUp(200, function(){ $(this).remove(); });
				}
			});

			// Add Section
			$('#mn_add_section_btn').on('click', function(e){
				e.preventDefault();
				var type = $('#mn_new_section_type').val();
				var index = $('.mn-section-card').length + Date.now();
				var typeLabel = $('#mn_new_section_type option:selected').text();

				var html = '<div class="mn-section-card" data-index="' + index + '">' +
					'<div class="mn-section-header">' +
						'<h4><span class="dashicons dashicons-menu"></span> <span class="mn-sec-title-display">' + typeLabel + '</span> <span class="mn-section-type-badge">' + type + '</span></h4>' +
						'<div style="display:flex;align-items:center;gap:12px;">' +
							'<label style="font-size:12px;cursor:pointer;"><input type="checkbox" name="mn_sec[' + index + '][show_toc]" value="yes" checked> <?php echo esc_js( __( 'Show in TOC', 'practice-problems-el' ) ); ?></label>' +
							'<button type="button" class="mn-btn-remove">&times; <?php echo esc_js( __( 'Remove', 'practice-problems-el' ) ); ?></button>' +
						'</div>' +
					'</div>' +
					'<div class="mn-section-body">' +
						'<input type="hidden" name="mn_sec[' + index + '][type]" value="' + type + '">' +
						'<div class="mn-field-group">' +
							'<label><?php echo esc_js( __( 'Section Title', 'practice-problems-el' ) ); ?></label>' +
							'<input type="text" class="mn-sec-title-input" name="mn_sec[' + index + '][title]" value="' + typeLabel + '">' +
						'</div>';

				if(type === 'overview' || type === 'custom'){
					html += '<div class="mn-field-group"><label><?php echo esc_js( __( 'Content', 'practice-problems-el' ) ); ?></label><textarea name="mn_sec[' + index + '][content]" rows="5"></textarea></div>';
				} else if(type === 'definition'){
					html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mn-field-group">' +
						'<div><label><?php echo esc_js( __( 'Badge Label', 'practice-problems-el' ) ); ?></label><input type="text" name="mn_sec[' + index + '][label]" value="Definition"></div>' +
						'<div><label><?php echo esc_js( __( 'Accent Border Color', 'practice-problems-el' ) ); ?></label><input type="text" name="mn_sec[' + index + '][accent_color]" value="#3b82f6"></div>' +
					'</div>' +
					'<div class="mn-field-group"><label><?php echo esc_js( __( 'Definition Text', 'practice-problems-el' ) ); ?></label><textarea name="mn_sec[' + index + '][content]" rows="4"></textarea></div>';
				} else if(type === 'formula'){
					html += '<div class="mn-field-group"><label><?php echo esc_js( __( 'Formula (LaTeX or text)', 'practice-problems-el' ) ); ?></label><input type="text" name="mn_sec[' + index + '][formula]"></div>' +
					'<div class="mn-field-group"><label><?php echo esc_js( __( 'Explanation', 'practice-problems-el' ) ); ?></label><textarea name="mn_sec[' + index + '][explanation]" rows="3"></textarea></div>';
				} else if(type === 'worked_example'){
					html += '<div class="mn-field-group"><label><?php echo esc_js( __( 'Problem Statement', 'practice-problems-el' ) ); ?></label><textarea name="mn_sec[' + index + '][problem]" rows="3"></textarea></div>' +
					'<div class="mn-field-group"><label><?php echo esc_js( __( 'Solution Steps (one per line)', 'practice-problems-el' ) ); ?></label><textarea name="mn_sec[' + index + '][steps]" rows="5"></textarea></div>' +
					'<div style="display:grid;grid-template-columns:120px 1fr;gap:12px;" class="mn-field-group">' +
						'<div><label><?php echo esc_js( __( 'Solution Label', 'practice-problems-el' ) ); ?></label><input type="text" name="mn_sec[' + index + '][solution_label]" value="Solution"></div>' +
						'<div><label><?php echo esc_js( __( 'Final Answer', 'practice-problems-el' ) ); ?></label><input type="text" name="mn_sec[' + index + '][solution]"></div>' +
					'</div>';
				} else if(type === 'common_mistake'){
					html += '<div style="display:grid;grid-template-columns:1fr 140px;gap:12px;" class="mn-field-group">' +
						'<div><label><?php echo esc_js( __( 'Mistake Heading', 'practice-problems-el' ) ); ?></label><input type="text" name="mn_sec[' + index + '][mistake_title]"></div>' +
						'<div><label><?php echo esc_js( __( 'Severity', 'practice-problems-el' ) ); ?></label><select name="mn_sec[' + index + '][severity]"><option value="warning">Warning</option><option value="critical">Critical</option><option value="info">Info / Tip</option></select></div>' +
					'</div>' +
					'<div class="mn-field-group"><label><?php echo esc_js( __( 'Explanation', 'practice-problems-el' ) ); ?></label><textarea name="mn_sec[' + index + '][description]" rows="3"></textarea></div>';
				} else if(type === 'next_steps'){
					html += '<div class="mn-field-group"><label><?php echo esc_js( __( 'Description', 'practice-problems-el' ) ); ?></label><textarea name="mn_sec[' + index + '][description]" rows="2"></textarea></div>' +
					'<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mn-field-group">' +
						'<div><label><?php echo esc_js( __( 'Button Text', 'practice-problems-el' ) ); ?></label><input type="text" name="mn_sec[' + index + '][button_text]" value="Go to practice problems"></div>' +
						'<div><label><?php echo esc_js( __( 'Button Link', 'practice-problems-el' ) ); ?></label><input type="text" name="mn_sec[' + index + '][button_link]" value="#"></div>' +
					'</div>';
				}

				html += '</div></div>';
				$('#mn_sections_container').append(html);
			});
		});
		</script>
		<?php
	}

	/**
	 * Save Meta Box data.
	 */
	public function save_meta_boxes( $post_id ) {
		// Verify autosave / permissions
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Settings
		if ( isset( $_POST['mn_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mn_settings_nonce'] ) ), 'mn_save_settings_nonce' ) ) {
			if ( isset( $_POST['mn_summary'] ) ) {
				update_post_meta( $post_id, '_mn_summary', sanitize_textarea_field( wp_unslash( $_POST['mn_summary'] ) ) );
			}
			if ( isset( $_POST['mn_display_order'] ) ) {
				update_post_meta( $post_id, '_mn_display_order', intval( $_POST['mn_display_order'] ) );
			}
			if ( isset( $_POST['mn_pdf_file'] ) ) {
				update_post_meta( $post_id, '_mn_pdf_file', esc_url_raw( wp_unslash( $_POST['mn_pdf_file'] ) ) );
			}
			if ( isset( $_POST['mn_pdf_label'] ) ) {
				update_post_meta( $post_id, '_mn_pdf_label', sanitize_text_field( wp_unslash( $_POST['mn_pdf_label'] ) ) );
			}
			if ( isset( $_POST['mn_prev_override'] ) ) {
				update_post_meta( $post_id, '_mn_prev_override', sanitize_text_field( wp_unslash( $_POST['mn_prev_override'] ) ) );
			}
			if ( isset( $_POST['mn_next_override'] ) ) {
				update_post_meta( $post_id, '_mn_next_override', sanitize_text_field( wp_unslash( $_POST['mn_next_override'] ) ) );
			}
		}

		// Save Sections
		if ( isset( $_POST['mn_sections_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mn_sections_nonce'] ) ), 'mn_save_sections_nonce' ) ) {
			if ( isset( $_POST['mn_sec'] ) && is_array( $_POST['mn_sec'] ) ) {
				$clean_sections = [];
				foreach ( $_POST['mn_sec'] as $sec_data ) {
					$clean = [
						'type'     => sanitize_key( $sec_data['type'] ?? 'overview' ),
						'title'    => sanitize_text_field( wp_unslash( $sec_data['title'] ?? '' ) ),
						'show_toc' => ( isset( $sec_data['show_toc'] ) && 'yes' === $sec_data['show_toc'] ) ? 'yes' : 'no',
					];

					if ( isset( $sec_data['content'] ) ) {
						$clean['content'] = wp_kses_post( wp_unslash( $sec_data['content'] ) );
					}
					if ( isset( $sec_data['label'] ) ) {
						$clean['label'] = sanitize_text_field( wp_unslash( $sec_data['label'] ) );
					}
					if ( isset( $sec_data['accent_color'] ) ) {
						$clean['accent_color'] = sanitize_hex_color( wp_unslash( $sec_data['accent_color'] ) ) ?: '#3b82f6';
					}
					if ( isset( $sec_data['formula'] ) ) {
						$clean['formula'] = sanitize_text_field( wp_unslash( $sec_data['formula'] ) );
					}
					if ( isset( $sec_data['explanation'] ) ) {
						$clean['explanation'] = wp_kses_post( wp_unslash( $sec_data['explanation'] ) );
					}
					if ( isset( $sec_data['problem'] ) ) {
						$clean['problem'] = wp_kses_post( wp_unslash( $sec_data['problem'] ) );
					}
					if ( isset( $sec_data['steps'] ) ) {
						$clean['steps'] = sanitize_textarea_field( wp_unslash( $sec_data['steps'] ) );
					}
					if ( isset( $sec_data['solution_label'] ) ) {
						$clean['solution_label'] = sanitize_text_field( wp_unslash( $sec_data['solution_label'] ) );
					}
					if ( isset( $sec_data['solution'] ) ) {
						$clean['solution'] = wp_kses_post( wp_unslash( $sec_data['solution'] ) );
					}
					if ( isset( $sec_data['mistake_title'] ) ) {
						$clean['mistake_title'] = sanitize_text_field( wp_unslash( $sec_data['mistake_title'] ) );
					}
					if ( isset( $sec_data['severity'] ) ) {
						$clean['severity'] = sanitize_key( $sec_data['severity'] );
					}
					if ( isset( $sec_data['description'] ) ) {
						$clean['description'] = wp_kses_post( wp_unslash( $sec_data['description'] ) );
					}
					if ( isset( $sec_data['button_text'] ) ) {
						$clean['button_text'] = sanitize_text_field( wp_unslash( $sec_data['button_text'] ) );
					}
					if ( isset( $sec_data['button_link'] ) ) {
						$clean['button_link'] = esc_url_raw( wp_unslash( $sec_data['button_link'] ) );
					}

					$clean_sections[] = $clean;
				}
				update_post_meta( $post_id, '_mn_sections', $clean_sections );
			}
		}
	}
}
