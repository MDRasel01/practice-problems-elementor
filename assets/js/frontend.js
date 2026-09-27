/**
 * Practice Problems for Elementor - Frontend Interactive Engine
 * Zero-dependency, accessible vanilla JavaScript with Elementor live-preview hooks.
 */

(function () {
    'use strict';

    /**
     * PracticeProblemsWidget Class
     */
    class PracticeProblemsWidget {
        constructor(container) {
            this.container = container;
            const rawConfig = container.getAttribute('data-pp-config');
            this.config = rawConfig ? JSON.parse(rawConfig) : {};

            // State
            this.selectedTopic = 'all';
            this.selectedDifficulty = 'all';
            this.searchQuery = '';
            this.currentPage = this.config.initialPage || 1;
            this.perPage = this.config.perPage || 5;
            this.isAllExpanded = false;

            // Elements
            this.listEl = container.querySelector('.pp-problems-list');
            this.cardElements = Array.from(container.querySelectorAll('.pp-problem-card'));
            this.emptyStateEl = container.querySelector('.pp-empty-state');
            this.paginationEl = container.querySelector('.pp-pagination-container');
            this.counterEl = container.querySelector('.pp-counter-text');
            this.searchInput = container.querySelector('.pp-search-input');
            this.resetBtn = container.querySelector('.pp-reset-btn');
            this.emptyResetBtn = container.querySelector('.pp-empty-reset-btn');
            this.expandAllBtn = container.querySelector('.pp-expand-all-btn');

            this.topicSelect = container.querySelector('.pp-topic-select');
            this.topicPills = Array.from(container.querySelectorAll('.pp-topic-pills .pp-pill-btn'));

            this.diffSelect = container.querySelector('.pp-difficulty-filter-select');
            this.diffPills = Array.from(container.querySelectorAll('.pp-difficulty-pills .pp-pill-btn'));

            this.init();
        }

        init() {
            this.bindEvents();
            this.applyFiltersAndPagination();
            this.typesetMath(this.container);
        }

        typesetMath(target) {
            const scope = target || this.container;
            if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                const mathEls = scope.querySelectorAll ? Array.from(scope.querySelectorAll('.pp-math-render')) : [scope];
                if (mathEls.length > 0) {
                    window.MathJax.typesetPromise(mathEls).catch(function () {
                        // Suppress harmless promise rejections during active DOM edits
                    });
                }
            }
        }

        bindEvents() {
            const self = this;

            // 1. Topic Filter (Select)
            if (this.topicSelect) {
                this.topicSelect.addEventListener('change', function () {
                    self.selectedTopic = this.value;
                    self.currentPage = 1;
                    self.applyFiltersAndPagination();
                });
            }

            // 2. Topic Filter (Pills)
            this.topicPills.forEach(pill => {
                pill.addEventListener('click', function () {
                    self.topicPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    self.selectedTopic = this.getAttribute('data-topic') || 'all';
                    self.currentPage = 1;
                    self.applyFiltersAndPagination();
                });
            });

            // 3. Difficulty Filter (Select)
            if (this.diffSelect) {
                this.diffSelect.addEventListener('change', function () {
                    self.selectedDifficulty = this.value;
                    self.currentPage = 1;
                    self.applyFiltersAndPagination();
                });
            }

            // 4. Difficulty Filter (Pills)
            this.diffPills.forEach(pill => {
                pill.addEventListener('click', function () {
                    self.diffPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    self.selectedDifficulty = this.getAttribute('data-difficulty') || 'all';
                    self.currentPage = 1;
                    self.applyFiltersAndPagination();
                });
            });

            // 5. Instant Live Search (Near-instant 25ms response for seamless typing)
            if (this.searchInput) {
                let debounceTimer;
                this.searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        self.searchQuery = this.value.trim().toLowerCase();
                        self.currentPage = 1;
                        self.applyFiltersAndPagination();
                    }, 25);
                });
            }

            // 6. Reset Filters
            const handleReset = () => {
                self.selectedTopic = 'all';
                self.selectedDifficulty = 'all';
                self.searchQuery = '';
                self.currentPage = 1;

                if (self.topicSelect) self.topicSelect.value = 'all';
                self.topicPills.forEach(p => p.classList.toggle('active', p.getAttribute('data-topic') === 'all'));

                if (self.diffSelect) self.diffSelect.value = 'all';
                self.diffPills.forEach(p => p.classList.toggle('active', p.getAttribute('data-difficulty') === 'all'));

                if (self.searchInput) self.searchInput.value = '';

                self.applyFiltersAndPagination();
            };

            if (this.resetBtn) this.resetBtn.addEventListener('click', handleReset);
            if (this.emptyResetBtn) this.emptyResetBtn.addEventListener('click', handleReset);

            // 7. Expand / Collapse All
            if (this.expandAllBtn) {
                this.expandAllBtn.addEventListener('click', function () {
                    self.isAllExpanded = !self.isAllExpanded;
                    const expandText = this.getAttribute('data-expand-text') || 'Expand All';
                    const collapseText = this.getAttribute('data-collapse-text') || 'Collapse All';
                    this.textContent = self.isAllExpanded ? collapseText : expandText;

                    // Toggle each visible card's solution
                    self.cardElements.forEach(card => {
                        if (card.style.display !== 'none') {
                            const btn = card.querySelector('.pp-solution-toggle-btn');
                            const content = card.querySelector('.pp-solution-content');
                            if (btn && content) {
                                self.setAccordionState(btn, content, self.isAllExpanded);
                            }
                        }
                    });
                });
            }

            // 8. Individual Solution Accordion Toggles
            this.container.addEventListener('click', function (e) {
                const btn = e.target.closest('.pp-solution-toggle-btn');
                if (!btn) return;

                const card = btn.closest('.pp-problem-card');
                const content = card ? card.querySelector('.pp-solution-content') : null;
                if (!content) return;

                const isCurrentlyExpanded = btn.classList.contains('expanded');
                self.setAccordionState(btn, content, !isCurrentlyExpanded);
            });
        }

        setAccordionState(btn, content, expand) {
            const showText = btn.getAttribute('data-show-text') || 'Show solution';
            const hideText = btn.getAttribute('data-hide-text') || 'Hide solution';
            const labelEl = btn.querySelector('.pp-btn-text');

            if (expand) {
                btn.classList.add('expanded');
                btn.setAttribute('aria-expanded', 'true');
                if (labelEl) labelEl.textContent = hideText;
                content.style.display = 'block';
                content.classList.add('open');
                this.typesetMath(content);
            } else {
                btn.classList.remove('expanded');
                btn.setAttribute('aria-expanded', 'false');
                if (labelEl) labelEl.textContent = showText;
                content.style.display = 'none';
                content.classList.remove('open');
            }
        }

        applyFiltersAndPagination() {
            const self = this;

            // 1. Filter cards
            const matchedCards = this.cardElements.filter(card => {
                const topic = (card.getAttribute('data-topic') || '').toLowerCase();
                const diff = (card.getAttribute('data-difficulty') || '').toLowerCase();
                const title = (card.getAttribute('data-title') || '').toLowerCase();
                const id = (card.getAttribute('data-id') || '').toLowerCase();
                const cardText = card.textContent.toLowerCase();

                // Topic match
                if (self.selectedTopic !== 'all') {
                    const cardTopics = topic.split(',').map(t => t.trim().toLowerCase());
                    if (!cardTopics.includes(self.selectedTopic.toLowerCase())) {
                        return false;
                    }
                }

                // Difficulty match
                if (self.selectedDifficulty !== 'all' && diff !== self.selectedDifficulty.toLowerCase()) {
                    return false;
                }

                // Instant Live Search match (Title, Topic, Difficulty, ID, Statement, Steps, Answer)
                if (self.searchQuery) {
                    const allContent = (title + ' ' + topic + ' ' + diff + ' ' + id + ' ' + cardText).toLowerCase();
                    const tokens = self.searchQuery.split(/\s+/).filter(t => t.length > 0);
                    const allTokensMatch = tokens.every(token => allContent.includes(token));
                    if (!allTokensMatch) {
                        return false;
                    }
                }

                return true;
            });

            const totalMatches = matchedCards.length;
            const totalPages = Math.max(1, Math.ceil(totalMatches / this.perPage));

            // Keep currentPage within bounds
            if (this.currentPage > totalPages) {
                this.currentPage = totalPages;
            }

            // 2. Empty State
            if (totalMatches === 0) {
                this.cardElements.forEach(card => card.style.display = 'none');
                if (this.emptyStateEl) {
                    this.emptyStateEl.style.display = 'block';
                    const descEl = this.emptyStateEl.querySelector('.pp-empty-desc') || this.emptyStateEl.querySelector('p');
                    if (descEl) {
                        if (self.searchQuery) {
                            descEl.textContent = `No problems found matching "${self.searchQuery}". Try a different keyword or reset filters.`;
                        } else {
                            descEl.textContent = 'No problems match those filters yet.';
                        }
                    }
                }
                if (this.paginationEl) this.paginationEl.style.display = 'none';
                this.updateCounter(0, this.cardElements.length);
                return;
            }

            if (this.emptyStateEl) this.emptyStateEl.style.display = 'none';
            if (this.paginationEl) this.paginationEl.style.display = 'flex';

            // 3. Slice cards according to pagination
            const isLoadMore = this.config.paginationType === 'load_more';
            const startIndex = isLoadMore ? 0 : (this.currentPage - 1) * this.perPage;
            const endIndex = this.currentPage * this.perPage;

            this.cardElements.forEach(card => {
                const matchIndex = matchedCards.indexOf(card);
                if (matchIndex >= startIndex && matchIndex < endIndex) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            // 4. Update Counter
            this.updateCounter(totalMatches, this.cardElements.length);

            // 5. Render Pagination
            if (this.config.enablePagination) {
                this.renderPagination(totalPages);
            }

            // 6. Typeset MathJax on visible cards
            this.typesetMath(this.container);
        }

        updateCounter(matchingCount, totalCount) {
            if (!this.counterEl) return;
            const tpl = this.config.resultCounterTpl || 'Showing {count} of {total} problems';
            this.counterEl.textContent = tpl
                .replace('{count}', matchingCount)
                .replace('{total}', totalCount);
        }

        renderPagination(totalPages) {
            if (!this.paginationEl) return;
            this.paginationEl.innerHTML = '';

            if (totalPages <= 1 && this.config.paginationType !== 'load_more') {
                this.paginationEl.style.display = 'none';
                return;
            }
            this.paginationEl.style.display = 'flex';

            const self = this;
            const type = this.config.paginationType || 'numbers_and_prev_next';

            // Case: Load More
            if (type === 'load_more') {
                const loadMoreBtn = document.createElement('button');
                loadMoreBtn.type = 'button';
                loadMoreBtn.className = 'pp-page-btn pp-load-more-btn';
                loadMoreBtn.textContent = this.config.loadMoreText || 'Load More';
                if (this.currentPage >= totalPages) {
                    loadMoreBtn.disabled = true;
                }
                loadMoreBtn.addEventListener('click', () => {
                    self.currentPage++;
                    self.applyFiltersAndPagination();
                });
                this.paginationEl.appendChild(loadMoreBtn);
                return;
            }

            // Case: Numbers / Prev Next
            const hasPrevNext = (type === 'prev_next' || type === 'numbers_and_prev_next');
            const hasNumbers = (type === 'numbers' || type === 'numbers_and_prev_next');

            // Previous Button
            if (hasPrevNext) {
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'pp-page-btn pp-prev-btn';
                prevBtn.textContent = this.config.prevText || '← Previous';
                prevBtn.disabled = (this.currentPage <= 1);
                prevBtn.addEventListener('click', () => {
                    if (self.currentPage > 1) {
                        self.changePage(self.currentPage - 1);
                    }
                });
                this.paginationEl.appendChild(prevBtn);
            }

            // Page Number Buttons with optional ellipsis
            if (hasNumbers) {
                const maxVisible = this.config.maxVisiblePages || 5;
                const pages = this.generatePageNumbers(this.currentPage, totalPages, maxVisible);

                pages.forEach(p => {
                    if (p === '...') {
                        const ellipsis = document.createElement('span');
                        ellipsis.className = 'pp-page-ellipsis';
                        ellipsis.textContent = '…';
                        self.paginationEl.appendChild(ellipsis);
                    } else {
                        const pageBtn = document.createElement('button');
                        pageBtn.type = 'button';
                        pageBtn.className = 'pp-page-btn pp-number-btn' + (p === self.currentPage ? ' active' : '');
                        pageBtn.textContent = p;
                        pageBtn.setAttribute('aria-label', 'Go to page ' + p);
                        pageBtn.addEventListener('click', () => {
                            self.changePage(p);
                        });
                        self.paginationEl.appendChild(pageBtn);
                    }
                });
            }

            // Next Button
            if (hasPrevNext) {
                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'pp-page-btn pp-next-btn';
                nextBtn.textContent = this.config.nextText || 'Next →';
                nextBtn.disabled = (this.currentPage >= totalPages);
                nextBtn.addEventListener('click', () => {
                    if (self.currentPage < totalPages) {
                        self.changePage(self.currentPage + 1);
                    }
                });
                this.paginationEl.appendChild(nextBtn);
            }
        }

        changePage(newPage) {
            this.currentPage = newPage;
            this.applyFiltersAndPagination();
            if (this.config.scrollToTop) {
                this.container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        generatePageNumbers(current, total, maxVisible) {
            if (total <= maxVisible) {
                return Array.from({ length: total }, (_, i) => i + 1);
            }

            const pages = [];
            const half = Math.floor(maxVisible / 2);
            let start = Math.max(1, current - half);
            let end = Math.min(total, start + maxVisible - 1);

            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }

            if (start > 1) {
                pages.push(1);
                if (start > 2) pages.push('...');
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            if (end < total) {
                if (end < total - 1) pages.push('...');
                pages.push(total);
            }

            return pages;
        }
    }

    /**
     * Elementor Frontend Initialization Handler
     */
    function initWidgets(scope) {
        const root = scope ? (scope instanceof HTMLElement ? scope : scope[0]) : document;
        const widgets = root.querySelectorAll('.pp-widget-container');
        widgets.forEach(container => {
            if (!container._ppWidgetInitialized) {
                container._ppWidgetInitialized = new PracticeProblemsWidget(container);
            } else {
                container._ppWidgetInitialized.typesetMath(container);
            }
        });
    }

    // Attach to Elementor frontend hook if present
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        window.elementorFrontend.hooks.addAction('frontend/element_ready/practice_problems.default', function ($scope) {
            initWidgets($scope);
        });
    }

    // Also initialize on standard window load / DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initWidgets());
    } else {
        initWidgets();
    }

    // Listen to Elementor preview updates
    window.addEventListener('elementor/frontend/init', () => {
        if (window.elementorFrontend && window.elementorFrontend.hooks) {
            window.elementorFrontend.hooks.addAction('frontend/element_ready/practice_problems.default', function ($scope) {
                initWidgets($scope);
            });
        }
    });

})();
