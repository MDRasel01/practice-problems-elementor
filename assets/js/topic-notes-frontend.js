/**
 * Topic Notes (Single Topic Notes) Frontend Interactive Engine
 * Vanilla JavaScript: Scroll Progress Bar, Dynamic TOC IntersectionObserver & Smooth Scrolling.
 */

(function () {
    'use strict';

    class TopicNotesWidget {
        constructor(container) {
            this.container = container;
            this.progressBar = container.querySelector('.tn-progress-bar');
            this.tocLinks = Array.from(container.querySelectorAll('.tn-toc-link'));
            this.sections = Array.from(container.querySelectorAll('.tn-section[id], .tn-chapter-box[id], .tn-accordion-item[id]'));

            this.init();
        }

        init() {
            this.initProgressBar();
            this.initTOCSpy();
            this.initSmoothScroll();
            this.initAccordions();
            this.initMathJax();
        }

        /**
         * Initialize MathJax typesetting on load
         */
        initMathJax() {
            if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                const mathEls = Array.from(this.container.querySelectorAll('.pp-math-render'));
                if (mathEls.length > 0) {
                    window.MathJax.typesetPromise(mathEls).catch(function (err) {
                        console.warn('[TopicNotes] MathJax typeset error:', err);
                    });
                }
            }
        }

        /**
         * Reading Progress Bar
         */
        initProgressBar() {
            if (!this.progressBar) return;

            const updateProgress = () => {
                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrollPercent = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                this.progressBar.style.width = Math.min(100, Math.max(0, scrollPercent)) + '%';
            };

            window.addEventListener('scroll', () => {
                window.requestAnimationFrame(updateProgress);
            }, { passive: true });

            updateProgress();
        }

        /**
         * Dynamic Table of Contents (TOC) Active State Spy
         */
        initTOCSpy() {
            if (!this.tocLinks.length || !this.sections.length) return;

            const self = this;
            const observerOptions = {
                root: null,
                rootMargin: '-20% 0px -60% 0px',
                threshold: 0
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        self.setActiveTOCLink(id);
                    }
                });
            }, observerOptions);

            this.sections.forEach(section => observer.observe(section));
        }

        setActiveTOCLink(id) {
            this.tocLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href === '#' + id) {
                    link.classList.add('active');
                    link.setAttribute('aria-current', 'true');
                } else {
                    link.classList.remove('active');
                    link.removeAttribute('aria-current');
                }
            });
        }

        /**
         * Smooth Scroll to Section on TOC click
         */
        initSmoothScroll() {
            this.tocLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    if (href && href.startsWith('#')) {
                        const target = this.container.querySelector(href);
                        if (target) {
                            e.preventDefault();

                            // If target is an accordion item or inside one, ensure it is open
                            const accordionItem = target.classList.contains('tn-accordion-item') ? target : target.closest('.tn-accordion-item');
                            if (accordionItem && !accordionItem.classList.contains('is-open')) {
                                this.openAccordion(accordionItem);
                            }

                            const offset = 80; // Offset for navbar
                            const elementPosition = target.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - offset;

                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });

                            // Update active link immediately on click
                            this.setActiveTOCLink(href.substring(1));
                        }
                    }
                });
            });
        }

        /**
         * Sub Section Accordions Handler
         */
        initAccordions() {
            const accordionItems = this.container.querySelectorAll('.tn-accordion-item');
            accordionItems.forEach(item => {
                const header = item.querySelector('.tn-accordion-header');
                if (!header) return;

                header.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleAccordion(item);
                });
            });
        }

        toggleAccordion(item) {
            if (item.classList.contains('is-open')) {
                this.closeAccordion(item);
            } else {
                this.openAccordion(item);
            }
        }

        openAccordion(item) {
            item.classList.add('is-open');
            const header = item.querySelector('.tn-accordion-header');
            const icon = item.querySelector('.tn-accordion-icon');
            const body = item.querySelector('.tn-accordion-body');

            if (header) header.setAttribute('aria-expanded', 'true');
            if (icon) icon.innerHTML = '&minus;';
            if (body) {
                body.style.display = 'block';
                // Trigger MathJax on opened content
                if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                    window.MathJax.typesetPromise([body]).catch(function () {});
                }
            }
        }

        closeAccordion(item) {
            item.classList.remove('is-open');
            const header = item.querySelector('.tn-accordion-header');
            const icon = item.querySelector('.tn-accordion-icon');
            const body = item.querySelector('.tn-accordion-body');

            if (header) header.setAttribute('aria-expanded', 'false');
            if (icon) icon.innerHTML = '&plus;';
            if (body) body.style.display = 'none';
        }
    }

    /**
     * Elementor Frontend Initialization Hook
     */
    function initTopicNotes(scope) {
        const root = scope ? (scope instanceof HTMLElement ? scope : scope[0]) : document;
        const widgets = root.querySelectorAll('.tn-widget-container');
        widgets.forEach(container => {
            if (!container._tnWidgetInitialized) {
                container._tnWidgetInitialized = new TopicNotesWidget(container);
            }
        });
    }

    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        window.elementorFrontend.hooks.addAction('frontend/element_ready/topic_notes.default', function ($scope) {
            initTopicNotes($scope);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initTopicNotes());
    } else {
        initTopicNotes();
    }

    window.addEventListener('elementor/frontend/init', () => {
        if (window.elementorFrontend && window.elementorFrontend.hooks) {
            window.elementorFrontend.hooks.addAction('frontend/element_ready/topic_notes.default', function ($scope) {
                initTopicNotes($scope);
            });
        }
    });

})();
