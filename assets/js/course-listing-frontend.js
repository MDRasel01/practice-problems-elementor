/**
 * Course Listing & Filter Frontend Controller
 *
 * Handles AJAX search debouncing, level filter switching,
 * URL query synchronization, skeleton state transitions, and accessible navigation.
 *
 * @package PracticeProblems
 */

(function($) {
	'use strict';

	class CourseListingWidget {
		constructor($container) {
			this.$container = $container;
			this.config = this.parseConfig();

			this.$searchInput     = this.$container.find('.cl-search-input');
			this.$searchClear     = this.$container.find('.cl-search-clear');
			this.$filterBtns      = this.$container.find('.cl-filter-btn');
			this.$coursesGrid     = this.$container.find('.cl-main-grid').length ? this.$container.find('.cl-main-grid') : this.$container.children('.cl-courses-grid');
			this.$skeleton        = this.$container.find('.cl-loading-skeleton');
			this.$emptyState      = this.$container.find('.cl-empty-state');
			this.$resetBtn        = this.$container.find('.cl-empty-reset-btn');
			this.$paginationWrap  = this.$container.find('.cl-pagination-wrapper');
			this.$srStatus        = this.$container.find('.cl-sr-status');

			this.state = {
				search: '',
				level: 'all',
				page: 1,
				maxPages: 1,
				isLoading: false,
			};

			this.debounceTimer = null;
			this.observer = null;

			this.init();
		}

		parseConfig() {
			try {
				const raw = this.$container.attr('data-config');
				return raw ? JSON.parse(raw) : {};
			} catch (e) {
				return {};
			}
		}

		init() {
			this.bindEvents();
			this.readUrlParams();
			this.initInfiniteScroll();
		}

		bindEvents() {
			const self = this;

			// Search input events
			if (this.$searchInput.length) {
				const behavior = this.config.searchMethod || 'live_debounce';
				const delay    = this.config.debounceDelay || 300;

				this.$searchInput.on('input', function() {
					const val = $(this).val().trim();
					self.$searchClear.toggle(val.length > 0);

					if (behavior === 'live_debounce') {
						clearTimeout(self.debounceTimer);
						self.debounceTimer = setTimeout(() => {
							self.state.search = val;
							self.state.page = 1;
							self.fetchCourses();
						}, delay);
					} else if (behavior === 'live') {
						self.state.search = val;
						self.state.page = 1;
						self.fetchCourses();
					}
				});

				this.$searchInput.on('keydown', function(e) {
					if (e.key === 'Enter') {
						e.preventDefault();
						clearTimeout(self.debounceTimer);
						self.state.search = $(this).val().trim();
						self.state.page = 1;
						self.fetchCourses();
					}
				});

				this.$searchClear.on('click', function() {
					self.$searchInput.val('').focus();
					$(this).hide();
					self.state.search = '';
					self.state.page = 1;
					self.fetchCourses();
				});
			}

			// Level filter buttons
			this.$container.on('click', '.cl-filter-btn', function(e) {
				e.preventDefault();
				const $btn = $(this);
				const level = $btn.attr('data-level') || 'all';

				if (self.state.level === level) {
					return;
				}

				self.$filterBtns.removeClass('is-active').attr('aria-pressed', 'false');
				$btn.addClass('is-active').attr('aria-pressed', 'true');

				self.state.level = level;
				self.state.page = 1;
				self.fetchCourses();
			});

			// Filter keyboard navigation (ArrowLeft, ArrowRight, Home, End)
			this.$container.on('keydown', '.cl-filter-btn', function(e) {
				const btns = self.$filterBtns.toArray();
				const idx  = btns.indexOf(this);

				if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
					e.preventDefault();
					const next = btns[(idx + 1) % btns.length];
					$(next).focus();
				} else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
					e.preventDefault();
					const prev = btns[(idx - 1 + btns.length) % btns.length];
					$(prev).focus();
				} else if (e.key === 'Home') {
					e.preventDefault();
					$(btns[0]).focus();
				} else if (e.key === 'End') {
					e.preventDefault();
					$(btns[btns.length - 1]).focus();
				}
			});

			// Reset filters button
			this.$resetBtn.on('click', function(e) {
				e.preventDefault();
				self.$searchInput.val('');
				self.$searchClear.hide();
				self.state.search = '';
				self.state.level = 'all';
				self.state.page = 1;

				self.$filterBtns.removeClass('is-active').attr('aria-pressed', 'false');
				self.$filterBtns.filter('[data-level="all"]').addClass('is-active').attr('aria-pressed', 'true');

				self.fetchCourses();
			});

			// Pagination: Page Numbers & Prev/Next
			this.$container.on('click', '.cl-page-number', function(e) {
				e.preventDefault();
				const p = parseInt($(this).attr('data-page'), 10);
				if (p && p !== self.state.page) {
					self.state.page = p;
					self.fetchCourses(true);
				}
			});

			this.$container.on('click', '.cl-page-prev', function(e) {
				e.preventDefault();
				if (self.state.page > 1) {
					self.state.page -= 1;
					self.fetchCourses(true);
				}
			});

			this.$container.on('click', '.cl-page-next', function(e) {
				e.preventDefault();
				const max = self.getMaxPages();
				if (self.state.page < max) {
					self.state.page += 1;
					self.fetchCourses(true);
				}
			});

			// Load More Button
			this.$container.on('click', '.cl-load-more-btn', function(e) {
				e.preventDefault();
				const $btn = $(this);
				const max = parseInt($btn.attr('data-max-pages'), 10) || 1;
				if (self.state.page < max) {
					self.state.page += 1;
					$btn.addClass('is-loading');
					self.fetchCourses(false, true);
				}
			});
		}

		getMaxPages() {
			const $nav = this.$container.find('.cl-pagination-nav');
			return parseInt($nav.attr('data-max-pages'), 10) || 1;
		}

		readUrlParams() {
			if (!this.config.syncUrl) {
				return;
			}
			const urlParams = new URLSearchParams(window.location.search);
			let hasParam = false;

			if (urlParams.has('search')) {
				const searchVal = urlParams.get('search');
				this.state.search = searchVal;
				this.$searchInput.val(searchVal);
				this.$searchClear.toggle(searchVal.length > 0);
				hasParam = true;
			}

			if (urlParams.has('level')) {
				const levelVal = urlParams.get('level');
				this.state.level = levelVal;
				this.$filterBtns.removeClass('is-active').attr('aria-pressed', 'false');
				const $target = this.$filterBtns.filter(`[data-level="${levelVal}"]`);
				if ($target.length) {
					$target.addClass('is-active').attr('aria-pressed', 'true');
				}
				hasParam = true;
			}

			// If URL had filters preset on page load, refresh view
			if (hasParam) {
				this.fetchCourses();
			}
		}

		syncUrl() {
			if (!this.config.syncUrl || !window.history.pushState) {
				return;
			}
			const url = new URL(window.location.href);

			if (this.state.search) {
				url.searchParams.set('search', this.state.search);
			} else {
				url.searchParams.delete('search');
			}

			if (this.state.level && this.state.level !== 'all') {
				url.searchParams.set('level', this.state.level);
			} else {
				url.searchParams.delete('level');
			}

			window.history.replaceState({}, '', url.toString());
		}

		announceSr(message) {
			if (this.$srStatus.length) {
				this.$srStatus.text(message);
			}
		}

		fetchCourses(scrollToTop = false, isAppend = false) {
			const self = this;
			if (this.state.isLoading) {
				return;
			}

			this.state.isLoading = true;
			this.syncUrl();
			this.announceSr('Loading courses...');

			if (!isAppend) {
				if (this.config.loadingType === 'skeleton') {
					this.$coursesGrid.hide();
					this.$skeleton.show();
				} else {
					this.$coursesGrid.css('opacity', '0.4');
				}
				this.$emptyState.hide();
			}

			const payload = {
				action: 'cl_query_courses',
				nonce: (window.CourseListingConfig && window.CourseListingConfig.nonce) ? window.CourseListingConfig.nonce : '',
				search: this.state.search,
				level: this.state.level,
				page: this.state.page,
				per_page: this.config.postsPerPage || 6,
				orderby: this.config.orderby || 'course_order',
				order: this.config.order || 'ASC',
				categories: this.config.categories || [],
				levels: this.config.levels || [],
				exclude_ids: this.config.excludeIds || '',
				card_settings: this.config.cardSettings || {},
				pagination_type: this.config.paginationType || 'numbers',
			};

			const ajaxUrl = (window.CourseListingConfig && window.CourseListingConfig.ajaxUrl) ? window.CourseListingConfig.ajaxUrl : '/wp-admin/admin-ajax.php';

			$.ajax({
				url: ajaxUrl,
				type: 'POST',
				data: payload,
				dataType: 'json',
			}).done(function(res) {
				self.state.isLoading = false;
				self.$skeleton.hide();
				self.$coursesGrid.show().css('opacity', '1');

				if (res && res.success && res.data) {
					const data = res.data;
					self.state.maxPages = data.total_pages || 1;

					if (isAppend) {
						self.$coursesGrid.append(data.html);
					} else {
						self.$coursesGrid.html(data.html);
					}

					// Empty state check
					if (data.total === 0) {
						self.$emptyState.show();
						self.$paginationWrap.hide();
						self.announceSr('No courses found.');
					} else {
						self.$emptyState.hide();
						self.$paginationWrap.show();
						self.announceSr(`Found ${data.total} courses.`);
					}

					// Update Pagination UI
					self.updatePagination(data.total_pages, data.page);

					// Smooth scroll to top of widget container if user navigated page
					if (scrollToTop && self.$container.length) {
						$('html, body').animate({
							scrollTop: self.$container.offset().top - 80,
						}, 300);
					}
				}
			}).fail(function() {
				self.state.isLoading = false;
				self.$skeleton.hide();
				self.$coursesGrid.show().css('opacity', '1');
				self.announceSr('Error loading courses. Please try again.');
			});
		}

		updatePagination(maxPages, currentPage) {
			const $loadMoreBtn = this.$container.find('.cl-load-more-btn');
			if ($loadMoreBtn.length) {
				$loadMoreBtn.removeClass('is-loading');
				$loadMoreBtn.attr('data-page', currentPage).attr('data-max-pages', maxPages);
				if (currentPage >= maxPages) {
					$loadMoreBtn.hide();
				} else {
					$loadMoreBtn.show();
				}
			}

			const $pageNav = this.$container.find('.cl-pagination-nav');
			if ($pageNav.length) {
				$pageNav.attr('data-max-pages', maxPages);

				// Prev & Next buttons
				$pageNav.find('.cl-page-prev').prop('disabled', currentPage <= 1);
				$pageNav.find('.cl-page-next').prop('disabled', currentPage >= maxPages);

				// Rebuild page numbers
				const $numWrap = $pageNav.find('.cl-page-numbers');
				$numWrap.empty();
				for (let i = 1; i <= maxPages; i++) {
					const isActive = (i === currentPage);
					$numWrap.append(`
						<button type="button" class="cl-page-number ${isActive ? 'is-active' : ''}" data-page="${i}" aria-current="${isActive ? 'page' : 'false'}">
							${i}
						</button>
					`);
				}

				if (maxPages <= 1) {
					$pageNav.hide();
				} else {
					$pageNav.show();
				}
			}
		}

		initInfiniteScroll() {
			if (this.config.paginationType !== 'infinite_scroll') {
				return;
			}
			const sentinel = this.$container.find('.cl-infinite-sentinel')[0];
			if (!sentinel || !window.IntersectionObserver) {
				return;
			}

			const self = this;
			this.observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting && !self.state.isLoading && self.state.page < self.state.maxPages) {
						self.state.page += 1;
						self.fetchCourses(false, true);
					}
				});
			}, { rootMargin: '200px' });

			this.observer.observe(sentinel);
		}
	}

	// Elementor hook & DOM ready
	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/course_listing.default', function($scope) {
			const $widget = $scope.find('.cl-widget-container');
			if ($widget.length) {
				new CourseListingWidget($widget);
			}
		});
	});

	$(document).ready(function() {
		$('.cl-widget-container').each(function() {
			if (!$(this).data('cl-widget-initialized')) {
				$(this).data('cl-widget-initialized', true);
				new CourseListingWidget($(this));
			}
		});
	});

})(jQuery);
