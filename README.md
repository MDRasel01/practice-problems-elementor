# Practice Problems for Elementor

A production-grade, 100% customizable WordPress Elementor Custom Widget for displaying interactive mathematical, scientific, and educational practice problems with step-by-step solution accordions, multi-faceted filtering, and accessible pagination.

---

## ✨ Features

- **100% Zero Hardcoded Text**: Every heading, eyebrow, difficulty label, button text, counter text, and empty state description is exposed in the Elementor controls.
- **Dual Data Source Architecture**:
  - **Elementor Repeater (Inline Data)**: Drag-and-drop management directly within the Elementor panel.
  - **WordPress Custom Post Type (`practice_problem`)**: Scalable database architecture for large problem banks (1000+ problems) with taxonomies (`problem_topic`, `problem_difficulty`).
- **Interactive Multi-Faceted Filters**:
  - Filter by **Topic** (Select Dropdown or Filter Pills).
  - Filter by **Difficulty** (Easy, Medium, Hard, or Custom).
  - Real-time instant **Search Bar** (debounced).
  - **Result Counter** (`Showing {count} of {total} problems`).
  - **Reset Filters** & **Expand All / Collapse All** utility buttons.
- **Accessible Solution System (WAI-ARIA)**:
  - Step-by-step solution accordion with automatic or custom numbered steps.
  - Separate highlighted **Answer Box**.
  - Accessible `aria-expanded`, `aria-controls`, and keyboard navigation (Tab/Enter/Space).
- **Multi-Style Pagination**:
  - Page numbers with smart ellipsis (`...`).
  - Previous / Next buttons.
  - Combined Numbers + Prev / Next.
  - AJAX / Client "Load More" button.
  - Optional smooth auto-scroll to top on page change.
- **Full Responsive Design**:
  - Granular styling controls for Desktop, Tablet, and Mobile across all typography, padding, margins, borders, and layouts.
- **Scoped & Performance-Optimized**:
  - Scoped CSS under `.pp-widget-container` prevents stylesheet leakage into your theme.
  - Zero third-party JavaScript dependencies (clean Vanilla JS).
  - Pre-rendered Server-Side HTML (SSR) for instant SEO indexing.

---

## 📁 Directory Structure

```text
practice-problems-elementor/
├── practice-problems-elementor.php       # Plugin bootstrap, requirements check
├── README.md                             # Documentation
├── includes/
│   ├── class-plugin.php                  # Main Singleton, Elementor category & widget registration
│   ├── class-cpt.php                     # CPT ('practice_problem') & Taxonomies
│   └── widgets/
│       └── class-practice-problems-widget.php # Elementor Widget (All Controls & HTML render)
└── assets/
    ├── css/
    │   └── frontend.css                  # Scoped CSS with modern styles & transitions
    └── js/
        └── frontend.js                   # Interactive client-side filtering, pagination & accordion
```

---

## 🚀 Installation

1. Download or copy the `practice-problems-elementor` folder to your WordPress installation:
   ```text
   /wp-content/plugins/practice-problems-elementor/
   ```
2. Navigate to **WordPress Admin → Plugins** and click **Activate** under **Practice Problems for Elementor**.
3. Ensure **Elementor** (Free or Pro) is active.
4. Open any page in the **Elementor Editor**, search for **Practice Problems**, and drag it onto your page.

---

## 🧮 Math & LaTeX Support

The problem statement and solution steps support HTML, rich text, and LaTeX math syntax. If your theme or site has MathJax or KaTeX installed (e.g., via a KaTeX plugin or CDN), expressions such as `$\lim_{x \to 3} f(x)$` will render beautifully into mathematical notation.

---

## 🔒 Security & Sanitization

- All user inputs are sanitized using `sanitize_text_field` and `sanitize_textarea_field`.
- Rich text and equation statements are rendered safely using WordPress's native `wp_kses_post()`.
- Data attributes and configuration payloads are securely encoded via `wp_json_encode()`.
