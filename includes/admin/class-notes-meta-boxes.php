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
		wp_enqueue_script( 'jquery-ui-sortable' );

		// Configure MathJax 3 for Admin Builder Live Preview
		$mathjax_config = [
			'tex' => [
				'inlineMath'          => [ [ '$', '$' ], [ '\\(', '\\)' ] ],
				'displayMath'         => [ [ '$$', '$$' ], [ '\\[', '\\]' ] ],
				'processEscapes'      => true,
				'processEnvironments' => true,
			],
			'options' => [
				'skipHtmlTags' => [ 'script', 'noscript', 'style', 'textarea', 'pre', 'code' ],
			],
			'svg' => [
				'fontCache' => 'global',
			],
		];

		$config_script = 'window.MathJax = window.MathJax || ' . wp_json_encode( $mathjax_config ) . ';';
		wp_register_script( 'pp-mathjax-config', '', [], PRACTICE_PROBLEMS_VERSION );
		wp_enqueue_script( 'pp-mathjax-config' );
		wp_add_inline_script( 'pp-mathjax-config', $config_script, 'before' );

		wp_enqueue_script(
			'pp-mathjax',
			'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js',
			[ 'pp-mathjax-config' ],
			'3.2.2',
			true
		);

		// Admin Note Section Builder CSS
		wp_enqueue_style(
			'mn-admin-builder',
			PRACTICE_PROBLEMS_URL . 'assets/css/admin-notes-builder.css',
			[],
			PRACTICE_PROBLEMS_VERSION
		);

		// Admin Note Section Builder JS
		wp_enqueue_script(
			'mn-admin-builder',
			PRACTICE_PROBLEMS_URL . 'assets/js/admin-notes-builder.js',
			[ 'jquery', 'jquery-ui-sortable', 'pp-mathjax' ],
			PRACTICE_PROBLEMS_VERSION,
			true
		);
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

		// 2. Structured Section Builder (Chapter / Sub Sections / Live LaTeX)
		add_meta_box(
			'mn_sections_builder',
			esc_html__( 'Note Sections Builder (Overview / Chapters & Sub Sections)', 'practice-problems-el' ),
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
		$sections     = is_array( $raw_sections ) ? $raw_sections : [];
		?>
		<script>
			window.mnInitialSectionsData = <?php echo wp_json_encode( $sections ); ?>;
		</script>

		<div id="mn_builder_root">
			<div style="padding: 24px; text-align: center; color: #64748b;">
				<span class="spinner is-active" style="float:none;margin-right:8px;"></span>
				<?php esc_html_e( 'Loading Note Section Builder...', 'practice-problems-el' ); ?>
			</div>
		</div>

		<textarea style="display:none;" id="mn_sections_data" name="_mn_sections_data"><?php echo esc_textarea( wp_json_encode( $sections ) ); ?></textarea>
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
			if ( isset( $_POST['_mn_sections_data'] ) ) {
				$raw_json = wp_unslash( $_POST['_mn_sections_data'] );
				$decoded  = json_decode( $raw_json, true );

				if ( is_array( $decoded ) ) {
					$clean_chapters = [];

					foreach ( $decoded as $ch ) {
						if ( ! is_array( $ch ) ) {
							continue;
						}

						$clean_ch = [
							'id'             => sanitize_key( $ch['id'] ?? 'ch_' . uniqid() ),
							'title'          => sanitize_text_field( $ch['title'] ?? 'Overview' ),
							'subsections'    => [],
							'definition'     => [
								'enabled' => ! empty( $ch['definition']['enabled'] ),
								'content' => isset( $ch['definition']['content'] ) ? wp_kses_post( $ch['definition']['content'] ) : '',
							],
							'formula'        => [
								'enabled'     => ! empty( $ch['formula']['enabled'] ),
								'formula'     => isset( $ch['formula']['formula'] ) ? sanitize_text_field( $ch['formula']['formula'] ) : '',
								'explanation' => isset( $ch['formula']['explanation'] ) ? wp_kses_post( $ch['formula']['explanation'] ) : '',
							],
							'worked_example' => [
								'enabled'  => ! empty( $ch['worked_example']['enabled'] ),
								'problem'  => isset( $ch['worked_example']['problem'] ) ? wp_kses_post( $ch['worked_example']['problem'] ) : '',
								'solution' => isset( $ch['worked_example']['solution'] ) ? wp_kses_post( $ch['worked_example']['solution'] ) : '',
							],
						];

						if ( isset( $ch['subsections'] ) && is_array( $ch['subsections'] ) ) {
							foreach ( $ch['subsections'] as $sub ) {
								if ( ! is_array( $sub ) ) {
									continue;
								}
								$clean_ch['subsections'][] = [
									'id'      => sanitize_key( $sub['id'] ?? 'sub_' . uniqid() ),
									'title'   => sanitize_text_field( $sub['title'] ?? 'Sub Section' ),
									'content' => isset( $sub['content'] ) ? wp_kses_post( $sub['content'] ) : '',
								];
							}
						}

						$clean_chapters[] = $clean_ch;
					}

					update_post_meta( $post_id, '_mn_sections', $clean_chapters );
				}
			} elseif ( isset( $_POST['mn_sec'] ) && is_array( $_POST['mn_sec'] ) ) {
				// Legacy flat array fallback
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
