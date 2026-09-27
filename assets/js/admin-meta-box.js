/**
 * Admin Meta Box Live LaTeX Preview Engine
 * Practice Problems for Elementor
 */

(function () {
	'use strict';

	/**
	 * Prepare LaTeX string for MathJax rendering.
	 * Detects raw LaTeX formulas (e.g. \frac{a+b}{c} or \textbf{1}. Prove that...)
	 * and wraps them in $$...$$ or \(...\) so MathJax typesets them without showing raw code.
	 */
	function prepareLatex(input, isBlock) {
		if (typeof isBlock === 'undefined') {
			isBlock = true;
		}
		if (!input || typeof input !== 'string') {
			return '';
		}

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
			// Check if it's mixed with a sentence prefix (e.g. "Prove that \sin..." or "Evaluate \lim...")
			const prefixMatch = text.match(/^(.*?\b(?:Prove\s+that|Evaluate|Find|Show\s+that|Calculate|Given\s+that|Where|If|Then)[:\s]+)(.+)$/i);
			if (prefixMatch && prefixMatch[1] && prefixMatch[2]) {
				const prefix = prefixMatch[1];
				const mathPart = prefixMatch[2].trim();
				return prefix + (isBlock ? '$$' + mathPart + '$$' : '\\(' + mathPart + '\\)');
			} else if (!/[a-zA-Z]{4,}\s+[a-zA-Z]{4,}/.test(text)) {
				// Pure formula (no multiple sentence words)
				return isBlock ? '$$' + text + '$$' : '\\(' + text + '\\)';
			} else {
				// Mixed English words with embedded LaTeX formulas - wrap formulas in \( ... \)
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
	 * Main Live Preview Controller
	 */
	function initLivePreview() {
		const idInput = document.getElementById('pp_problem_id');
		const titleInput = document.getElementById('pp_post_title');
		const stepsInput = document.getElementById('pp_steps');
		const answerInput = document.getElementById('pp_answer');

		const badgePreview = document.getElementById('pp-preview-id-badge');
		const badgeNotice = document.getElementById('pp-preview-no-badge-notice');
		const titlePreview = document.getElementById('pp-preview-title');
		const stepsBox = document.getElementById('pp-preview-steps-box');
		const answerBox = document.getElementById('pp-preview-answer-box');
		const statusBadge = document.getElementById('pp-preview-status');
		const previewCard = document.querySelector('.pp-preview-card');

		if (!stepsInput || !stepsBox) {
			return;
		}

		let debounceTimer = null;

		function updatePreview() {
			if (statusBadge) {
				statusBadge.classList.add('is-rendering');
				statusBadge.innerHTML = '<span class="pp-status-dot"></span> Rendering...';
			}

			// 1. Update Problem ID Badge Text
			if (idInput && badgePreview) {
				const idVal = idInput.value.trim();
				if (idVal !== '') {
					badgePreview.textContent = idVal;
					badgePreview.style.display = 'inline-flex';
					if (badgeNotice) badgeNotice.style.display = 'none';
				} else {
					badgePreview.textContent = '';
					badgePreview.style.display = 'none';
					if (badgeNotice) badgeNotice.style.display = 'inline';
				}
			}

			// 2. Update Post Title / Main Problem Question
			if (titleInput && titlePreview) {
				const titleVal = titleInput.value.trim();
				if (titleVal !== '') {
					const preparedTitle = prepareLatex(titleVal, false);
					titlePreview.innerHTML = preparedTitle;
					titlePreview.style.display = 'block';
				} else {
					titlePreview.innerHTML = '<span class="pp-preview-empty-hint"><em>(Enter problem title / question above...)</em></span>';
					titlePreview.style.display = 'block';
				}
			}

			// 3. Update Step-by-Step Solution
			const rawSteps = stepsInput.value || '';
			let stepsArray = [];

			// Handle JSON input or line-by-line input
			const trimmedRaw = rawSteps.trim();
			if (trimmedRaw.startsWith('[') && trimmedRaw.endsWith(']')) {
				try {
					const parsed = JSON.parse(trimmedRaw);
					if (Array.isArray(parsed)) {
						stepsArray = parsed.map(s => String(s).trim()).filter(s => s !== '');
					}
				} catch (e) {
					// Fallback to line split if JSON parsing fails
					stepsArray = trimmedRaw.split(/\r\n|\r|\n/).map(s => s.trim()).filter(s => s !== '');
				}
			} else if (trimmedRaw !== '') {
				stepsArray = trimmedRaw.split(/\r\n|\r|\n/).map(s => s.trim()).filter(s => s !== '');
			}

			if (stepsArray.length > 0) {
				let html = '<ol class="pp-preview-steps-list">';
				stepsArray.forEach((step, index) => {
					const preparedStep = prepareLatex(step, true);
					html += '<li class="pp-preview-step-item">';
					html += '<span class="pp-step-num">' + (index + 1) + '</span>';
					html += '<div class="pp-step-body pp-math-render">' + preparedStep + '</div>';
					html += '</li>';
				});
				html += '</ol>';
				stepsBox.innerHTML = html;
			} else {
				stepsBox.innerHTML = '<div class="pp-preview-empty-text">Enter solution steps on the left to see live LaTeX preview here...</div>';
			}

			// 4. Update Final Answer
			const rawAnswer = answerInput ? answerInput.value.trim() : '';
			if (rawAnswer !== '') {
				const preparedAnswer = prepareLatex(rawAnswer, false);
				answerBox.innerHTML = '<div class="pp-answer-inner"><strong class="pp-answer-label">Answer:</strong> <span class="pp-answer-val pp-math-render">' + preparedAnswer + '</span></div>';
				answerBox.style.display = 'block';
			} else {
				answerBox.innerHTML = '<div class="pp-preview-empty-text">Enter final answer on the left...</div>';
			}

			// 5. Trigger MathJax Typesetting on preview card
			triggerMathJax(previewCard, function () {
				if (statusBadge) {
					statusBadge.classList.remove('is-rendering');
					statusBadge.innerHTML = '<span class="pp-status-dot"></span> MathJax Ready';
				}
			});
		}

		function triggerMathJax(targetEl, callback) {
			const target = targetEl || previewCard;
			if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
				window.MathJax.typesetPromise([target])
					.then(function () {
						if (typeof callback === 'function') callback();
					})
					.catch(function (err) {
						console.warn('[PracticeProblems] MathJax Preview Typeset Error:', err);
						if (typeof callback === 'function') callback();
					});
			} else {
				if (typeof callback === 'function') callback();
			}
		}

		function onInputChange() {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(updatePreview, 80);
		}

		// Attach listeners
		if (idInput) {
			idInput.addEventListener('input', onInputChange);
			idInput.addEventListener('change', onInputChange);
		}
		if (titleInput) {
			titleInput.addEventListener('input', onInputChange);
			titleInput.addEventListener('change', onInputChange);

			// Sync with Classic Editor title field (#title)
			const wpClassicTitle = document.getElementById('title');
			if (wpClassicTitle) {
				if (wpClassicTitle.value && !titleInput.value) {
					titleInput.value = wpClassicTitle.value;
				}
				titleInput.addEventListener('input', function () {
					wpClassicTitle.value = this.value;
				});
				wpClassicTitle.addEventListener('input', function () {
					titleInput.value = this.value;
					onInputChange();
				});
			}

			// Sync with Gutenberg editor if available
			if (window.wp && wp.data && wp.data.select && wp.data.dispatch) {
				try {
					const coreEditor = wp.data.select('core/editor');
					if (coreEditor) {
						const gTitle = coreEditor.getEditedPostAttribute('title');
						if (gTitle && !titleInput.value) {
							titleInput.value = gTitle;
						}
						titleInput.addEventListener('input', function () {
							try {
								wp.data.dispatch('core/editor').editPost({ title: this.value });
							} catch (e) {}
						});
					}
				} catch (e) {}
			}
		}
		if (stepsInput) {
			stepsInput.addEventListener('input', onInputChange);
			stepsInput.addEventListener('change', onInputChange);
		}
		if (answerInput) {
			answerInput.addEventListener('input', onInputChange);
			answerInput.addEventListener('change', onInputChange);
		}

		// Initial render after window or MathJax loads
		if (window.MathJax && window.MathJax.startup && window.MathJax.startup.promise) {
			window.MathJax.startup.promise.then(updatePreview);
		} else {
			setTimeout(updatePreview, 250);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLivePreview);
	} else {
		initLivePreview();
	}
})();
