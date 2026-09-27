/**
 * Math Notes CPT — Note Section Builder Interactive Engine
 * 
 * Hierarchy: Overview / Chapter -> Sub Sections (Accordions) -> Sub Section Content
 * Features:
 *  - Overview acts as a Chapter Box container
 *  - Multiple Overviews can be added (+ Add Overview)
 *  - Inline Rename System: [ Overview ] ✎ -> [ Input ] ✓ -> permanently saved
 *  - Multiple Sub Sections per Chapter as complete Accordion Boxes (+ Add Sub Section)
 *  - Sub Section Title = Accordion Title (synced in real-time)
 *  - Sub Section Accordion Open/Close (+ / − indicator)
 *  - Live LaTeX Preview with MathJax for Title & Content
 *  - Optional Content Blocks per Chapter: Definition [OFF], Key Formula [OFF], Worked Example [OFF]
 *  - Reorder Chapters and Sub Sections (drag & drop + Up/Down buttons)
 *  - Delete Chapters and Sub Sections
 *  - Form serialization to hidden JSON input for WordPress save
 */

(function ($) {
	'use strict';

	// Helper: Escape HTML
	function escapeHtml(str) {
		if (!str) return '';
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	// Helper: Unique ID generator
	function generateId(prefix) {
		return (prefix || 'id') + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
	}

	/**
	 * Prepare LaTeX string for MathJax rendering.
	 * Detects raw LaTeX formulas (e.g. \frac{a+b}{c} or z = a + bi)
	 * and wraps them in $$...$$ or \(...\) so MathJax typesets without showing raw code.
	 */
	function prepareLatex(input, isBlock) {
		if (typeof isBlock === 'undefined') isBlock = true;
		if (!input || typeof input !== 'string') return '';

		let text = input.trim();

		// Check if already has math delimiters: $$, \[, \(, or $...$
		const hasDelimiters = /\$\$|\\\[|\\\(|(?<!\\)\$.+?(?<!\\)\$/.test(text);
		if (hasDelimiters) {
			return text;
		}

		// Convert standard LaTeX text formatting commands to HTML
		text = text.replace(/\\textbf\{([^}]+)\}/g, '<strong>$1</strong>');
		text = text.replace(/\\textit\{([^}]+)\}/g, '<em>$1</em>');
		text = text.replace(/\\underline\{([^}]+)\}/g, '<u>$1</u>');

		// Check for common LaTeX math commands or exponent/subscript syntax
		const hasLatexCmd = /\\(frac|sqrt|sin|cos|tan|cot|sec|csc|cosec|log|ln|lim|sum|int|prod|alpha|beta|gamma|theta|pi|infty|pm|times|cdot|le|ge|neq|approx|vec|hat|begin|end|matrix|cases|over|to|partial)\b/i.test(text);
		const hasMathNotation = /[a-zA-Z0-9]\^[0-9a-zA-Z{]|_[0-9a-zA-Z{]/.test(text);

		if (hasLatexCmd || hasMathNotation) {
			const prefixMatch = text.match(/^(.*?\b(?:Prove\s+that|Evaluate|Find|Show\s+that|Calculate|Given\s+that|Where|If|Then)[:\s]+)(.+)$/i);
			if (prefixMatch && prefixMatch[1] && prefixMatch[2]) {
				const prefix = prefixMatch[1];
				const mathPart = prefixMatch[2].trim();
				return prefix + (isBlock ? '$$' + mathPart + '$$' : '\\(' + mathPart + '\\)');
			} else if (!/[a-zA-Z]{4,}\s+[a-zA-Z]{4,}/.test(text)) {
				return isBlock ? '$$' + text + '$$' : '\\(' + text + '\\)';
			} else {
				return text.replace(/(\\\b(?:frac\{[^{}]*\}\{[^{}]*\}|[a-zA-Z]+(?:\{[^{}]*\})?|[a-zA-Z0-9]+(?:\^[0-9a-zA-Z{}]|_[0-9a-zA-Z{}])+)(?:[^$\n\r<]*[=<>][^$\n\r<]*)?)/g, function (m) {
					if (m.startsWith('<strong>') || m.startsWith('<')) {
						return m;
					}
					return '\\(' + m + '\\)';
				});
			}
		}

		return text;
	}

	/**
	 * Trigger MathJax Typesetting on a specific DOM element.
	 */
	function renderMathJaxIn(element) {
		if (!element) return;
		if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
			window.MathJax.typesetPromise([element]).catch(function (err) {
				console.warn('[NoteBuilder] MathJax typeset error:', err);
			});
		}
	}

	/**
	 * Note Section Builder Main Class
	 */
	class NoteSectionBuilder {
		constructor(containerEl, initialData) {
			this.$container = $(containerEl);
			this.chapters = this.normalizeInitialData(initialData);
			this.renderDebounceTimers = {};

			this.init();
		}

		/**
		 * Normalize initial data into the Chapter -> Subsections hierarchy.
		 * Gracefully migrates old flat sections into Chapter Box.
		 */
		normalizeInitialData(data) {
			if (!data || !Array.isArray(data) || data.length === 0) {
				// Default 1 Chapter with 1 Sub Section, ALL optional blocks OFF
				return [
					{
						id: generateId('ch'),
						title: 'Overview',
						subsections: [
							{
								id: generateId('sub'),
								title: 'Introduction',
								content: ''
							}
						],
						definition: { enabled: false, content: '' },
						formula: { enabled: false, formula: '', explanation: '' },
						worked_example: { enabled: false, problem: '', solution: '' }
					}
				];
			}

			// Check if already in new Chapter format (has 'subsections' or 'title' + optional blocks)
			if (data[0] && (data[0].subsections !== undefined || data[0].definition !== undefined)) {
				return data.map(ch => ({
					id: ch.id || generateId('ch'),
					title: ch.title || 'Overview',
					subsections: Array.isArray(ch.subsections) ? ch.subsections.map(sub => ({
						id: sub.id || generateId('sub'),
						title: sub.title || 'Sub Section',
						content: sub.content || ''
					})) : [],
					definition: {
						enabled: !!(ch.definition && ch.definition.enabled),
						content: (ch.definition && ch.definition.content) || ''
					},
					formula: {
						enabled: !!(ch.formula && ch.formula.enabled),
						formula: (ch.formula && ch.formula.formula) || '',
						explanation: (ch.formula && ch.formula.explanation) || ''
					},
					worked_example: {
						enabled: !!(ch.worked_example && ch.worked_example.enabled),
						problem: (ch.worked_example && ch.worked_example.problem) || '',
						solution: (ch.worked_example && ch.worked_example.solution) || ''
					}
				}));
			}

			// Migrate old flat array format into a single Chapter
			const chapter = {
				id: generateId('ch'),
				title: 'Overview',
				subsections: [],
				definition: { enabled: false, content: '' },
				formula: { enabled: false, formula: '', explanation: '' },
				worked_example: { enabled: false, problem: '', solution: '' }
			};

			data.forEach(item => {
				const type = item.type || 'overview';
				if (type === 'overview' || type === 'custom') {
					chapter.subsections.push({
						id: generateId('sub'),
						title: item.title || 'Overview',
						content: item.content || ''
					});
				} else if (type === 'definition') {
					chapter.definition = {
						enabled: true,
						content: item.content || ''
					};
				} else if (type === 'formula') {
					chapter.formula = {
						enabled: true,
						formula: item.formula || '',
						explanation: item.explanation || ''
					};
				} else if (type === 'worked_example') {
					chapter.worked_example = {
						enabled: true,
						problem: item.problem || '',
						solution: item.solution || ''
					};
				}
			});

			if (chapter.subsections.length === 0) {
				chapter.subsections.push({
					id: generateId('sub'),
					title: 'Introduction',
					content: ''
				});
			}

			return [chapter];
		}

		/**
		 * Initialize Builder DOM and Bindings
		 */
		init() {
			this.render();
			this.bindGlobalEvents();
			this.syncToHiddenInput();

			// Initial MathJax render after window or MathJax is ready
			if (window.MathJax && window.MathJax.startup && window.MathJax.startup.promise) {
				window.MathJax.startup.promise.then(() => {
					this.renderAllPreviews();
				});
			} else {
				setTimeout(() => this.renderAllPreviews(), 400);
			}
		}

		/**
		 * Render entire builder container
		 */
		render() {
			let html = `
				<div class="mn-builder-container">
					<!-- Top Toolbar -->
					<div class="mn-builder-toolbar">
						<div class="mn-builder-toolbar-title">
							<span class="dashicons dashicons-category"></span>
							<span>Note Section Builder — Hierarchical Chapters &amp; Accordions</span>
						</div>
						<button type="button" class="button button-primary mn-btn-add-chapter">
							<span class="dashicons dashicons-plus-alt2"></span>
							+ Add Overview
						</button>
					</div>

					<!-- Chapters Container -->
					<div class="mn-chapters-list">
						${this.chapters.length === 0 ? this.renderEmptyState() : ''}
					</div>

					<!-- Bottom Toolbar -->
					<div class="mn-builder-bottom-toolbar" style="margin-top:20px;text-align:center;">
						<button type="button" class="button button-primary mn-btn-add-chapter mn-btn-add-chapter-bottom" style="padding:8px 20px!important;font-size:14px!important;">
							<span class="dashicons dashicons-plus-alt2"></span>
							+ Add Overview / Chapter
						</button>
					</div>
				</div>
			`;

			this.$container.html(html);

			// Render all chapters
			const $chaptersList = this.$container.find('.mn-chapters-list');
			this.chapters.forEach((chapter, index) => {
				$chaptersList.append(this.buildChapterHtml(chapter, index));
			});

			this.initSortable();
		}

		renderEmptyState() {
			return `
				<div class="mn-chapters-empty">
					<p>No Chapters or Overviews added yet.</p>
					<button type="button" class="button button-primary mn-btn-add-chapter">
						<span class="dashicons dashicons-plus-alt2"></span>
						+ Add Overview
					</button>
				</div>
			`;
		}

		/**
		 * Build HTML for a single Chapter Box (Overview Container)
		 */
		buildChapterHtml(chapter, chIndex) {
			const subCount = chapter.subsections ? chapter.subsections.length : 0;

			return `
				<div class="mn-chapter-card" data-chapter-id="${escapeHtml(chapter.id)}" data-index="${chIndex}">
					<!-- Chapter Header -->
					<div class="mn-chapter-header">
						<div class="mn-chapter-header-left">
							<span class="dashicons dashicons-move mn-drag-handle" title="Drag to reorder chapter"></span>
							<span class="mn-chapter-badge">Chapter</span>

							<!-- Title Display & Inline Rename Area -->
							<div class="mn-chapter-title-wrap">
								<span class="mn-chapter-title-text" title="Click to rename">${escapeHtml(chapter.title || 'Overview')}</span>
								<button type="button" class="mn-btn-rename" title="Rename Chapter">
									<span class="dashicons dashicons-edit"></span>
								</button>
								<!-- Rename Input Box (Initially Hidden) -->
								<div class="mn-chapter-rename-box">
									<input type="text" class="mn-chapter-rename-input" value="${escapeHtml(chapter.title || 'Overview')}" placeholder="e.g. Complex Numbers">
									<button type="button" class="button mn-btn-save-rename" title="Save name">✓</button>
								</div>
							</div>
						</div>

						<!-- Chapter Header Actions -->
						<div class="mn-chapter-header-actions">
							<button type="button" class="mn-btn-action mn-btn-move-chapter-up" title="Move Up" ${chIndex === 0 ? 'disabled style="opacity:0.4;"' : ''}>▲</button>
							<button type="button" class="mn-btn-action mn-btn-move-chapter-down" title="Move Down" ${chIndex === this.chapters.length - 1 ? 'disabled style="opacity:0.4;"' : ''}>▼</button>
							<button type="button" class="mn-btn-action mn-btn-delete-chapter" title="Delete this Chapter Box">
								<span class="dashicons dashicons-trash"></span>
								Delete Chapter
							</button>
						</div>
					</div>

					<!-- Chapter Body -->
					<div class="mn-chapter-body">
						<!-- Sub Sections Accordion Container -->
						<div class="mn-subsections-section">
							<div class="mn-section-label-row">
								<span class="mn-section-label">
									<span class="dashicons dashicons-list-view"></span>
									Sub Sections (Accordions) &mdash; <span class="mn-sub-count">${subCount}</span>
								</span>
								<span style="font-size:12px;color:#64748b;">Sub Section Title = Accordion Title</span>
							</div>

							<!-- List of Accordions -->
							<div class="mn-subsections-list">
								${chapter.subsections.map((sub, sIdx) => this.buildSubSectionHtml(sub, sIdx, chapter.id)).join('')}
							</div>

							<!-- Add Sub Section Button -->
							<button type="button" class="mn-btn-add-sub">
								<span class="dashicons dashicons-plus-alt2"></span>
								+ Add Sub Section
							</button>
						</div>

						<!-- Optional Content Blocks Section -->
						<div class="mn-optional-blocks-wrap">
							<div class="mn-optional-title">Optional Content Blocks (Default: OFF)</div>
							<div class="mn-optional-items">
								<!-- 1. Definition Block -->
								${this.buildDefinitionBlockHtml(chapter)}

								<!-- 2. Key Formula Block -->
								${this.buildFormulaBlockHtml(chapter)}

								<!-- 3. Worked Example Block -->
								${this.buildWorkedExampleBlockHtml(chapter)}
							</div>
						</div>
					</div>
				</div>
			`;
		}

		/**
		 * Build HTML for a Sub Section (Accordion Box)
		 */
		buildSubSectionHtml(sub, sIndex, chapterId) {
			return `
				<div class="mn-subsection-card is-open" data-sub-id="${escapeHtml(sub.id)}" data-index="${sIndex}">
					<!-- Accordion Header -->
					<div class="mn-subsection-header">
						<div class="mn-subsection-header-left">
							<span class="dashicons dashicons-menu mn-drag-handle mn-sub-drag-handle" title="Drag to reorder accordion"></span>
							<span class="mn-accordion-indicator">&minus;</span>
							<span class="mn-subsection-title-display">${escapeHtml(sub.title || 'Sub Section')}</span>
						</div>

						<div class="mn-subsection-header-actions" onclick="event.stopPropagation();">
							<button type="button" class="mn-btn-action mn-btn-move-sub-up" title="Move Up">▲</button>
							<button type="button" class="mn-btn-action mn-btn-move-sub-down" title="Move Down">▼</button>
							<button type="button" class="mn-btn-delete-sub" title="Delete Sub Section">
								<span class="dashicons dashicons-no-alt"></span>
							</button>
						</div>
					</div>

					<!-- Accordion Body -->
					<div class="mn-subsection-body">
						<!-- Sub Section Title Input -->
						<div class="mn-field-row">
							<label>Sub Section Title (Accordion Title)</label>
							<input type="text" class="mn-sub-title-input" value="${escapeHtml(sub.title || '')}" placeholder="e.g. Introduction / Basic Concept">
						</div>

						<!-- Sub Section Content with Live LaTeX Preview -->
						<div class="mn-field-row">
							<label>Sub Section Content &amp; Mathematical LaTeX</label>
							<div class="mn-latex-split-grid">
								<!-- Left: LaTeX / Content Input -->
								<div class="mn-latex-input-col">
									<textarea class="mn-sub-content-input" rows="6" placeholder="e.g. \\textbf{Definition:}&#10;A complex number is represented as&#10;z = a + bi&#10;or \\frac{a+b}{c}">${escapeHtml(sub.content || '')}</textarea>
								</div>
								<!-- Right: Live LaTeX Preview -->
								<div class="mn-latex-preview-col">
									<div class="mn-preview-box mn-sub-content-preview">
										<div class="mn-preview-label">Live LaTeX Preview</div>
										<div class="mn-preview-rendered pp-math-render">${this.getRenderedHtml(sub.content)}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			`;
		}

		/**
		 * Build HTML for Optional Definition Block
		 */
		buildDefinitionBlockHtml(chapter) {
			const isEnabled = !!(chapter.definition && chapter.definition.enabled);
			const content = (chapter.definition && chapter.definition.content) || '';

			return `
				<div class="mn-optional-card ${isEnabled ? 'is-enabled' : ''}" data-block-type="definition">
					<div class="mn-optional-header">
						<div class="mn-optional-name">
							<span class="dashicons dashicons-book-alt"></span>
							<span>Definition</span>
						</div>
						<label class="mn-toggle-switch">
							<input type="checkbox" class="mn-toggle-input" ${isEnabled ? 'checked' : ''}>
							<span class="mn-toggle-slider"></span>
						</label>
					</div>

					<div class="mn-optional-body">
						<div class="mn-field-row">
							<label>Definition Content (Supports LaTeX)</label>
							<div class="mn-latex-split-grid">
								<div class="mn-latex-input-col">
									<textarea class="mn-def-content-input" rows="4" placeholder="Enter clear mathematical definition... e.g. An imaginary number is defined as $i = \\sqrt{-1}$">${escapeHtml(content)}</textarea>
								</div>
								<div class="mn-latex-preview-col">
									<div class="mn-preview-box mn-def-preview">
										<div class="mn-preview-label">Live LaTeX Preview</div>
										<div class="mn-preview-rendered pp-math-render">${this.getRenderedHtml(content)}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			`;
		}

		/**
		 * Build HTML for Optional Key Formula Block
		 */
		buildFormulaBlockHtml(chapter) {
			const isEnabled = !!(chapter.formula && chapter.formula.enabled);
			const formula = (chapter.formula && chapter.formula.formula) || '';
			const explanation = (chapter.formula && chapter.formula.explanation) || '';

			return `
				<div class="mn-optional-card ${isEnabled ? 'is-enabled' : ''}" data-block-type="formula">
					<div class="mn-optional-header">
						<div class="mn-optional-name">
							<span class="dashicons dashicons-editor-insertmore"></span>
							<span>Key Formula</span>
						</div>
						<label class="mn-toggle-switch">
							<input type="checkbox" class="mn-toggle-input" ${isEnabled ? 'checked' : ''}>
							<span class="mn-toggle-slider"></span>
						</label>
					</div>

					<div class="mn-optional-body">
						<div class="mn-field-row">
							<label>Formula (LaTeX)</label>
							<div class="mn-latex-split-grid">
								<div class="mn-latex-input-col">
									<input type="text" class="mn-formula-input" value="${escapeHtml(formula)}" placeholder="e.g. x = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}">
								</div>
								<div class="mn-latex-preview-col">
									<div class="mn-preview-box mn-formula-preview" style="min-height:50px;">
										<div class="mn-preview-label">Live LaTeX Preview</div>
										<div class="mn-preview-rendered pp-math-render">${this.getRenderedHtml(formula, true)}</div>
									</div>
								</div>
							</div>
						</div>

						<div class="mn-field-row">
							<label>Formula Explanation / Notes (Optional)</label>
							<textarea class="mn-formula-explanation-input" rows="2" placeholder="e.g. Where a, b, and c are real coefficients and a ≠ 0">${escapeHtml(explanation)}</textarea>
						</div>
					</div>
				</div>
			`;
		}

		/**
		 * Build HTML for Optional Worked Example Block
		 */
		buildWorkedExampleBlockHtml(chapter) {
			const isEnabled = !!(chapter.worked_example && chapter.worked_example.enabled);
			const problem = (chapter.worked_example && chapter.worked_example.problem) || '';
			const solution = (chapter.worked_example && chapter.worked_example.solution) || '';

			return `
				<div class="mn-optional-card ${isEnabled ? 'is-enabled' : ''}" data-block-type="worked_example">
					<div class="mn-optional-header">
						<div class="mn-optional-name">
							<span class="dashicons dashicons-welcome-learn-more"></span>
							<span>Worked Example</span>
						</div>
						<label class="mn-toggle-switch">
							<input type="checkbox" class="mn-toggle-input" ${isEnabled ? 'checked' : ''}>
							<span class="mn-toggle-slider"></span>
						</label>
					</div>

					<div class="mn-optional-body">
						<div class="mn-field-row">
							<label>Example Problem / Question</label>
							<div class="mn-latex-split-grid">
								<div class="mn-latex-input-col">
									<textarea class="mn-example-problem-input" rows="3" placeholder="e.g. Solve the quadratic equation: 2x^2 + 5x - 3 = 0">${escapeHtml(problem)}</textarea>
								</div>
								<div class="mn-latex-preview-col">
									<div class="mn-preview-box mn-example-problem-preview">
										<div class="mn-preview-label">Live LaTeX Preview</div>
										<div class="mn-preview-rendered pp-math-render">${this.getRenderedHtml(problem)}</div>
									</div>
								</div>
							</div>
						</div>

						<div class="mn-field-row">
							<label>Solution Steps &amp; Answer (Supports LaTeX)</label>
							<div class="mn-latex-split-grid">
								<div class="mn-latex-input-col">
									<textarea class="mn-example-solution-input" rows="5" placeholder="e.g. Step 1: Identify coefficients: a=2, b=5, c=-3&#10;Step 2: Apply formula: x = \\frac{-5 \\pm \\sqrt{25 - 4(2)(-3)}}{4}&#10;Final answer: x = \\frac{1}{2} \\text{ or } -3">${escapeHtml(solution)}</textarea>
								</div>
								<div class="mn-latex-preview-col">
									<div class="mn-preview-box mn-example-solution-preview">
										<div class="mn-preview-label">Live LaTeX Preview</div>
										<div class="mn-preview-rendered pp-math-render">${this.getRenderedHtml(solution)}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			`;
		}

		/**
		 * Helper: Generate initial rendered HTML string for preview box
		 */
		getRenderedHtml(rawContent, isPureFormula) {
			if (!rawContent || !rawContent.trim()) {
				return '<span class="mn-preview-placeholder">Live preview will appear here as you type...</span>';
			}
			const prepared = prepareLatex(rawContent, !!isPureFormula);
			// Replace newlines with <br> for plain text portions if not pure formula
			return prepared.replace(/\r?\n/g, '<br>');
		}

		/**
		 * Update Live Preview with Debounced MathJax
		 */
		updatePreview(previewEl, rawText, isPureFormula) {
			if (!previewEl) return;
			const $preview = $(previewEl);
			const $rendered = $preview.find('.mn-preview-rendered');

			if (!rawText || !rawText.trim()) {
				$rendered.html('<span class="mn-preview-placeholder">Live preview will appear here as you type...</span>');
				return;
			}

			const prepared = prepareLatex(rawText, !!isPureFormula);
			$rendered.html(prepared.replace(/\r?\n/g, '<br>'));

			const previewKey = $preview.attr('id') || Math.random().toString();
			if (this.renderDebounceTimers[previewKey]) {
				clearTimeout(this.renderDebounceTimers[previewKey]);
			}

			this.renderDebounceTimers[previewKey] = setTimeout(() => {
				renderMathJaxIn($rendered[0]);
			}, 150);
		}

		/**
		 * Render all preview boxes in current DOM
		 */
		renderAllPreviews() {
			this.$container.find('.mn-preview-rendered').each(function () {
				renderMathJaxIn(this);
			});
		}

		/**
		 * Initialize jQuery UI Sortable for drag-and-drop reordering
		 */
		initSortable() {
			const self = this;
			if ($.fn.sortable) {
				// Chapters Sortable
				this.$container.find('.mn-chapters-list').sortable({
					handle: '.mn-drag-handle',
					items: '> .mn-chapter-card',
					axis: 'y',
					opacity: 0.8,
					placeholder: 'mn-chapter-sortable-placeholder',
					stop: function () {
						self.rebuildStateFromDom();
					}
				});

				// Sub Sections Sortable inside each Chapter
				this.$container.find('.mn-subsections-list').sortable({
					handle: '.mn-sub-drag-handle',
					items: '> .mn-subsection-card',
					axis: 'y',
					opacity: 0.8,
					placeholder: 'mn-sub-sortable-placeholder',
					stop: function () {
						self.rebuildStateFromDom();
					}
				});
			}
		}

		/**
		 * Sync state to hidden textarea/input for WordPress post saving
		 */
		syncToHiddenInput() {
			let $hidden = $('#mn_sections_data');
			if ($hidden.length === 0) {
				$hidden = $('<textarea style="display:none;" id="mn_sections_data" name="_mn_sections_data"></textarea>');
				this.$container.append($hidden);
			}
			$hidden.val(JSON.stringify(this.chapters));
		}

		/**
		 * Rebuild state array from current DOM hierarchy
		 */
		rebuildStateFromDom() {
			const newChapters = [];
			const self = this;

			this.$container.find('.mn-chapter-card').each(function (chIdx) {
				const $ch = $(this);
				const chId = $ch.attr('data-chapter-id') || generateId('ch');
				const chTitle = $ch.find('.mn-chapter-title-text').text().trim() || 'Overview';

				const subSections = [];
				$ch.find('.mn-subsection-card').each(function (sIdx) {
					const $sub = $(this);
					const subId = $sub.attr('data-sub-id') || generateId('sub');
					const subTitle = $sub.find('.mn-sub-title-input').val() || '';
					const subContent = $sub.find('.mn-sub-content-input').val() || '';

					subSections.push({
						id: subId,
						title: subTitle,
						content: subContent
					});
				});

				// Definition
				const $defCard = $ch.find('.mn-optional-card[data-block-type="definition"]');
				const defEnabled = $defCard.find('.mn-toggle-input').is(':checked');
				const defContent = $defCard.find('.mn-def-content-input').val() || '';

				// Formula
				const $formCard = $ch.find('.mn-optional-card[data-block-type="formula"]');
				const formEnabled = $formCard.find('.mn-toggle-input').is(':checked');
				const formula = $formCard.find('.mn-formula-input').val() || '';
				const explanation = $formCard.find('.mn-formula-explanation-input').val() || '';

				// Worked Example
				const $exCard = $ch.find('.mn-optional-card[data-block-type="worked_example"]');
				const exEnabled = $exCard.find('.mn-toggle-input').is(':checked');
				const exProblem = $exCard.find('.mn-example-problem-input').val() || '';
				const exSolution = $exCard.find('.mn-example-solution-input').val() || '';

				newChapters.push({
					id: chId,
					title: chTitle,
					subsections: subSections,
					definition: { enabled: defEnabled, content: defContent },
					formula: { enabled: formEnabled, formula: formula, explanation: explanation },
					worked_example: { enabled: exEnabled, problem: exProblem, solution: exSolution }
				});
			});

			this.chapters = newChapters;
			this.syncToHiddenInput();
		}

		/**
		 * Bind all user interactions (clicks, inputs, toggles, rename)
		 */
		bindGlobalEvents() {
			const self = this;

			// ==========================================
			// 1. ADD OVERVIEW / CHAPTER
			// ==========================================
			this.$container.on('click', '.mn-btn-add-chapter', function (e) {
				e.preventDefault();
				const newChapter = {
					id: generateId('ch'),
					title: 'Overview',
					subsections: [
						{
							id: generateId('sub'),
							title: 'Introduction',
							content: ''
						}
					],
					// CRITICAL RULE: Optional sections ALWAYS default to OFF for new chapters!
					definition: { enabled: false, content: '' },
					formula: { enabled: false, formula: '', explanation: '' },
					worked_example: { enabled: false, problem: '', solution: '' }
				};

				self.chapters.push(newChapter);
				self.render();
				self.syncToHiddenInput();

				// Smooth scroll to newly added chapter
				const $newCard = self.$container.find(`.mn-chapter-card[data-chapter-id="${newChapter.id}"]`);
				if ($newCard.length) {
					$('html, body').animate({ scrollTop: $newCard.offset().top - 80 }, 300);
					self.renderMathJaxInChapter($newCard);
				}
			});

			// ==========================================
			// 2. CHAPTER INLINE RENAME SYSTEM
			// ==========================================
			// Click Title or Edit Icon -> Switch to Rename Mode
			this.$container.on('click', '.mn-chapter-title-text, .mn-btn-rename', function (e) {
				e.stopPropagation();
				const $wrap = $(this).closest('.mn-chapter-title-wrap');
				const $text = $wrap.find('.mn-chapter-title-text');
				const $btnRename = $wrap.find('.mn-btn-rename');
				const $renameBox = $wrap.find('.mn-chapter-rename-box');
				const $input = $renameBox.find('.mn-chapter-rename-input');

				$text.hide();
				$btnRename.hide();
				$renameBox.css('display', 'inline-flex');
				$input.focus().select();
			});

			// Click Save (✓) in Rename Box
			this.$container.on('click', '.mn-btn-save-rename', function (e) {
				e.stopPropagation();
				const $wrap = $(this).closest('.mn-chapter-title-wrap');
				self.saveChapterRename($wrap);
			});

			// Press Enter key in Rename input -> Save
			this.$container.on('keydown', '.mn-chapter-rename-input', function (e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					const $wrap = $(this).closest('.mn-chapter-title-wrap');
					self.saveChapterRename($wrap);
				} else if (e.key === 'Escape') {
					e.preventDefault();
					const $wrap = $(this).closest('.mn-chapter-title-wrap');
					$wrap.find('.mn-chapter-rename-box').hide();
					$wrap.find('.mn-chapter-title-text').show();
					$wrap.find('.mn-btn-rename').show();
				}
			});

			// ==========================================
			// 3. DELETE CHAPTER
			// ==========================================
			this.$container.on('click', '.mn-btn-delete-chapter', function (e) {
				e.preventDefault();
				const $card = $(this).closest('.mn-chapter-card');
				const chTitle = $card.find('.mn-chapter-title-text').text() || 'Overview';

				if (confirm(`Are you sure you want to delete Chapter "${chTitle}"? All sub sections within it will be removed.`)) {
					$card.slideUp(200, function () {
						$(this).remove();
						self.rebuildStateFromDom();
						if (self.chapters.length === 0) {
							self.render();
						}
					});
				}
			});

			// ==========================================
			// 4. MOVE CHAPTER UP / DOWN
			// ==========================================
			this.$container.on('click', '.mn-btn-move-chapter-up', function (e) {
				e.preventDefault();
				const $card = $(this).closest('.mn-chapter-card');
				const $prev = $card.prev('.mn-chapter-card');
				if ($prev.length) {
					$card.insertBefore($prev);
					self.rebuildStateFromDom();
					self.render();
				}
			});

			this.$container.on('click', '.mn-btn-move-chapter-down', function (e) {
				e.preventDefault();
				const $card = $(this).closest('.mn-chapter-card');
				const $next = $card.next('.mn-chapter-card');
				if ($next.length) {
					$card.insertAfter($next);
					self.rebuildStateFromDom();
					self.render();
				}
			});

			// ==========================================
			// 5. ADD SUB SECTION
			// ==========================================
			this.$container.on('click', '.mn-btn-add-sub', function (e) {
				e.preventDefault();
				const $chapterCard = $(this).closest('.mn-chapter-card');
				const chapterId = $chapterCard.attr('data-chapter-id');
				const $subList = $chapterCard.find('.mn-subsections-list');

				const newSub = {
					id: generateId('sub'),
					title: 'New Sub Section',
					content: ''
				};

				const subHtml = self.buildSubSectionHtml(newSub, $subList.children().length, chapterId);
				const $subEl = $(subHtml).hide();
				$subList.append($subEl);
				$subEl.slideDown(200);

				// Update count
				$chapterCard.find('.mn-sub-count').text($subList.children().length);

				self.rebuildStateFromDom();
				self.initSortable();

				// Focus newly created title input
				$subEl.find('.mn-sub-title-input').focus().select();
			});

			// ==========================================
			// 6. ACCORDION TOGGLE OPEN / CLOSE
			// ==========================================
			this.$container.on('click', '.mn-subsection-header', function (e) {
				// Don't toggle if clicked on action buttons or drag handle
				if ($(e.target).closest('.mn-subsection-header-actions, .mn-drag-handle').length) {
					return;
				}

				const $card = $(this).closest('.mn-subsection-card');
				const isOpen = $card.hasClass('is-open');
				const $indicator = $card.find('.mn-accordion-indicator');
				const $body = $card.find('.mn-subsection-body');

				if (isOpen) {
					$card.removeClass('is-open');
					$indicator.html('&plus;');
					$body.slideUp(180);
				} else {
					$card.addClass('is-open');
					$indicator.html('&minus;');
					$body.slideDown(180, function () {
						renderMathJaxIn($card.find('.mn-preview-rendered')[0]);
					});
				}
			});

			// ==========================================
			// 7. SUB SECTION TITLE INPUT (SYNC WITH ACCORDION HEADER)
			// ==========================================
			this.$container.on('input', '.mn-sub-title-input', function () {
				const val = $(this).val();
				const $card = $(this).closest('.mn-subsection-card');
				$card.find('.mn-subsection-title-display').text(val || 'Sub Section');
				self.rebuildStateFromDom();
			});

			// ==========================================
			// 8. SUB SECTION CONTENT & LIVE LATEX PREVIEW
			// ==========================================
			this.$container.on('input', '.mn-sub-content-input', function () {
				const val = $(this).val();
				const $card = $(this).closest('.mn-subsection-card');
				const $previewBox = $card.find('.mn-sub-content-preview');
				self.updatePreview($previewBox, val, false);
				self.rebuildStateFromDom();
			});

			// ==========================================
			// 9. DELETE SUB SECTION
			// ==========================================
			this.$container.on('click', '.mn-btn-delete-sub', function (e) {
				e.preventDefault();
				e.stopPropagation();
				const $subCard = $(this).closest('.mn-subsection-card');
				const $chapterCard = $subCard.closest('.mn-chapter-card');
				const subTitle = $subCard.find('.mn-sub-title-input').val() || 'Sub Section';

				if (confirm(`Delete sub section "${subTitle}"?`)) {
					$subCard.slideUp(180, function () {
						$(this).remove();
						$chapterCard.find('.mn-sub-count').text($chapterCard.find('.mn-subsection-card').length);
						self.rebuildStateFromDom();
					});
				}
			});

			// ==========================================
			// 10. MOVE SUB SECTION UP / DOWN
			// ==========================================
			this.$container.on('click', '.mn-btn-move-sub-up', function (e) {
				e.preventDefault();
				e.stopPropagation();
				const $card = $(this).closest('.mn-subsection-card');
				const $prev = $card.prev('.mn-subsection-card');
				if ($prev.length) {
					$card.insertBefore($prev);
					self.rebuildStateFromDom();
				}
			});

			this.$container.on('click', '.mn-btn-move-sub-down', function (e) {
				e.preventDefault();
				e.stopPropagation();
				const $card = $(this).closest('.mn-subsection-card');
				const $next = $card.next('.mn-subsection-card');
				if ($next.length) {
					$card.insertAfter($next);
					self.rebuildStateFromDom();
				}
			});

			// ==========================================
			// 11. OPTIONAL BLOCKS TOGGLE SWITCHES
			// ==========================================
			this.$container.on('change', '.mn-toggle-input', function () {
				const $card = $(this).closest('.mn-optional-card');
				const isChecked = $(this).is(':checked');
				if (isChecked) {
					$card.addClass('is-enabled');
					$card.find('.mn-optional-body').slideDown(180, function () {
						$card.find('.mn-preview-rendered').each(function () {
							renderMathJaxIn(this);
						});
					});
				} else {
					$card.removeClass('is-enabled');
					$card.find('.mn-optional-body').slideUp(180);
				}
				self.rebuildStateFromDom();
			});

			// ==========================================
			// 12. DEFINITION INPUT & PREVIEW
			// ==========================================
			this.$container.on('input', '.mn-def-content-input', function () {
				const val = $(this).val();
				const $card = $(this).closest('.mn-optional-card');
				const $preview = $card.find('.mn-def-preview');
				self.updatePreview($preview, val, false);
				self.rebuildStateFromDom();
			});

			// ==========================================
			// 13. KEY FORMULA INPUT & PREVIEW
			// ==========================================
			this.$container.on('input', '.mn-formula-input', function () {
				const val = $(this).val();
				const $card = $(this).closest('.mn-optional-card');
				const $preview = $card.find('.mn-formula-preview');
				self.updatePreview($preview, val, true);
				self.rebuildStateFromDom();
			});

			this.$container.on('input', '.mn-formula-explanation-input', function () {
				self.rebuildStateFromDom();
			});

			// ==========================================
			// 14. WORKED EXAMPLE INPUT & PREVIEW
			// ==========================================
			this.$container.on('input', '.mn-example-problem-input', function () {
				const val = $(this).val();
				const $card = $(this).closest('.mn-optional-card');
				const $preview = $card.find('.mn-example-problem-preview');
				self.updatePreview($preview, val, false);
				self.rebuildStateFromDom();
			});

			this.$container.on('input', '.mn-example-solution-input', function () {
				const val = $(this).val();
				const $card = $(this).closest('.mn-optional-card');
				const $preview = $card.find('.mn-example-solution-preview');
				self.updatePreview($preview, val, false);
				self.rebuildStateFromDom();
			});

			// ==========================================
			// 15. SYNC ON FORM SUBMIT
			// ==========================================
			$('form#post').on('submit', function () {
				self.rebuildStateFromDom();
			});
		}

		/**
		 * Save Chapter Rename Action
		 */
		saveChapterRename($wrap) {
			const $text = $wrap.find('.mn-chapter-title-text');
			const $btnRename = $wrap.find('.mn-btn-rename');
			const $renameBox = $wrap.find('.mn-chapter-rename-box');
			const $input = $renameBox.find('.mn-chapter-rename-input');

			let newTitle = $input.val().trim();
			if (!newTitle) {
				newTitle = 'Overview';
				$input.val(newTitle);
			}

			$text.text(newTitle);
			$renameBox.hide();
			$text.show();
			$btnRename.show();

			this.rebuildStateFromDom();
		}

		renderMathJaxInChapter($chapterCard) {
			$chapterCard.find('.mn-preview-rendered').each(function () {
				renderMathJaxIn(this);
			});
		}
	}

	// Auto-initialize when DOM is ready
	$(document).ready(function () {
		const $root = $('#mn_builder_root');
		if ($root.length) {
			const initialData = window.mnInitialSectionsData || [];
			window.mnSectionBuilderInstance = new NoteSectionBuilder($root[0], initialData);
		}
	});

})(jQuery);
