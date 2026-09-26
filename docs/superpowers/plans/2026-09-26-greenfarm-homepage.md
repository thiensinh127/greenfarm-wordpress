# GreenFarm Homepage Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the approved mobile-first GreenFarm homepage using native WordPress Page data, optional business-content queries, reusable theme components, and the existing motion system.

**Architecture:** `front-page.php` orchestrates focused homepage template parts. The static Page main query supplies hero and introduction content; small secondary queries supply optional Products, Farm Stories, and latest Posts, then restore global Post state. Existing theme tokens and `data-reveal` behavior are extended rather than introducing a framework or plugin.

**Tech Stack:** WordPress 6.6+, PHP 8.2+, classic PHP theme templates, WordPress Playground integration tests, vanilla CSS, existing vanilla JavaScript motion utility.

**Spec:** `docs/superpowers/specs/2026-09-26-greenfarm-homepage-design.md`

## Global Constraints

- Use `front-page.php` and a configured static WordPress Page.
- The Page title is the only H1; excerpt is hero copy, featured image is hero media, and editor content is the farm introduction.
- Use native WordPress attachment rendering for responsive images and intrinsic dimensions.
- Query `greenfarm_product`, `farm_story`, and `product_category` only when registered; hide their complete sections when content is unavailable.
- Latest Articles uses native Posts in a constrained secondary query and restores global Post state.
- Reuse the existing forest, leaf, cream, paper, and ink design tokens.
- Reuse `data-reveal`; animation uses opacity/transform once and respects `prefers-reduced-motion`.
- Do not add ACF, a page builder, slider, icon library, animation library, remote font, or production plugin.
- Do not output placeholder testimonials, ratings, email capture, Organization schema, or business data.

## Review Focus

- A homepage without excerpt, content, or featured image still renders a coherent hero and never produces broken/empty media markup.
- Registered Product/Farm Story types with no published entries omit their headings and containers entirely.
- Secondary queries never leak their Post into later homepage sections or the global Page context.
- A featured hero attachment receives eager/high-priority attributes while every below-fold attachment keeps native lazy-loading behavior.
- The page remains fully visible when JavaScript or `IntersectionObserver` is unavailable and when reduced motion is requested.

---

### Task 1: Static Homepage Shell and Hero

**Files:**
- Create: `wp-content/themes/greenfarm/front-page.php`
- Create: `wp-content/themes/greenfarm/template-parts/home/hero.php`
- Create: `wp-content/themes/greenfarm/template-parts/home/introduction.php`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: Existing header/footer, WordPress static Page main query, responsive attachment APIs, and `data-reveal` motion contract.
- Produces: A front-page orchestrator with one H1 plus hero/introduction slots consumed by later sections.

- [ ] **Step 1: Write failing homepage-shell integration tests**

Add real WordPress Page fixtures and assert that `front-page.php` renders exactly one H1 from the Page title, excerpt text, editor content, `/products/` and `/about/` CTAs, and no empty `<img>` when the Page has no featured image. Add an attachment fixture and assert its hero image contains `fetchpriority="high"`, eager loading, width, height, `srcset`, and `sizes`.

- [ ] **Step 2: Run the focused PHP suite and verify the homepage tests fail because templates are absent**

Run: `npm run test:php`

Expected: FAIL on the missing homepage H1/hero assertions while the existing Blog tests remain green.

- [ ] **Step 3: Implement the front-page shell, hero, and introduction template parts**

Use the Page Loop once in `front-page.php`. Hero uses `the_title()`, `get_the_excerpt()`, and `wp_get_attachment_image()` with high-priority hero attributes; introduction uses `the_content()` and the approved three proof points. Use the approved CTA URLs through `home_url()`.

- [ ] **Step 4: Run the focused and full tests**

Run: `npm run test:php && npm test && npm run lint:php`

Expected: PASS with all homepage and existing Blog assertions green and no PHP parse errors.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add GreenFarm homepage hero"
```

### Task 2: Values, Process, and Proof Sections

**Files:**
- Create: `wp-content/themes/greenfarm/template-parts/home/values-process.php`
- Create: `wp-content/themes/greenfarm/template-parts/home/proof.php`
- Modify: `wp-content/themes/greenfarm/front-page.php`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: Front-page section order and heading hierarchy from Task 1.
- Produces: Static brand-value, ordered farm-process, and factual proof sections with H2/H3 hierarchy.

- [ ] **Step 1: Write a failing semantic homepage-section test**

Assert rendered output includes one ordered list containing Grow, Harvest, Prepare, and Share in that order; three value cards; the factual proof strip; no testimonial/review schema; and no H1 beyond the Page title.

- [ ] **Step 2: Run the focused PHP suite and verify the section test fails**

Run: `npm run test:php`

Expected: FAIL because the values/process/proof content is absent.

- [ ] **Step 3: Implement values, process, and proof template parts**

Use semantic `<section>`, H2 section headings, H3 value-card headings, and `<ol>` process markup. Mark each major section with `data-reveal`; do not add JavaScript.

- [ ] **Step 4: Run full verification**

Run: `npm test && npm run lint:php`

Expected: PASS with no regression in Blog templates.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add farm values and process"
```

### Task 3: Optional Product and Farm Story Sections

**Files:**
- Create: `wp-content/themes/greenfarm/template-parts/home/products.php`
- Create: `wp-content/themes/greenfarm/template-parts/home/stories.php`
- Modify: `wp-content/themes/greenfarm/front-page.php`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: Future public post types `greenfarm_product`, `farm_story`, taxonomy `product_category`, and native fields/meta.
- Produces: Defensive dynamic sections that emit nothing without registered models/content and restore global Post state after queries.

- [ ] **Step 1: Write failing optional-content tests**

First render with no business post types and assert Product Category, Featured Product, and Farm Story headings are absent. Register temporary public test post types/taxonomy, create published fixtures plus one draft, render again, and assert only published fixtures appear, limits are four categories/products and two stories, canonical links are present, and the global homepage Page remains current after rendering.

- [ ] **Step 2: Run the focused PHP suite and verify populated optional sections fail**

Run: `npm run test:php`

Expected: FAIL because populated business content is not rendered; absence assertions pass.

- [ ] **Step 3: Implement Product Category, Product, and Farm Story template parts**

Guard with `taxonomy_exists()`/`post_type_exists()`. Use `get_terms()` for up to four non-empty categories and separate constrained `WP_Query` instances for four published Products and two published Farm Stories. Render native title/excerpt/featured image/permalink and availability meta; call `wp_reset_postdata()` after each query.

- [ ] **Step 4: Run full verification**

Run: `npm test && npm run lint:php`

Expected: PASS including missing-model, draft-exclusion, result-limit, and global-state assertions.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add homepage product and story sections"
```

### Task 4: Latest Articles and Final CTA

**Files:**
- Create: `wp-content/themes/greenfarm/template-parts/home/latest-posts.php`
- Create: `wp-content/themes/greenfarm/template-parts/home/cta.php`
- Modify: `wp-content/themes/greenfarm/template-parts/content-post-card.php`
- Modify: `wp-content/themes/greenfarm/front-page.php`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: `greenfarm_get_blog_url()`, native Posts, shared post-card component, and homepage Page context.
- Produces: Three-article homepage query, configurable H3 card headings, Blog link, and Contact-based seasonal CTA.

- [ ] **Step 1: Write failing latest-content tests**

Create four published Posts and one draft. Assert the homepage displays exactly three published latest-article cards with H3 headings, omits the draft/fourth result, links to the configured Blog URL, keeps the homepage Page as global Post afterward, and links the final CTA to `/contact/` without rendering an email form.

- [ ] **Step 2: Run the focused PHP suite and verify it fails**

Run: `npm run test:php`

Expected: FAIL because Latest Articles and final CTA are absent.

- [ ] **Step 3: Allow `content-post-card.php` to consume `heading_level`**

Accept only `h2` or `h3` from the `$args` array, defaulting to `h2`, so archive behavior remains unchanged and homepage cards use H3.

- [ ] **Step 4: Implement Latest Articles and final CTA**

Query three published Posts with sticky behavior ignored and no pagination count, render the shared card at H3, reset Post data, link to `greenfarm_get_blog_url()`, and render the approved Contact CTA without a form.

- [ ] **Step 5: Run full verification**

Run: `npm test && npm run lint:php`

Expected: PASS including existing archive H2 assertions and homepage H3/global-state assertions.

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add homepage articles and CTA"
```

### Task 5: Responsive Homepage Styling and Runtime QA

**Files:**
- Create: `wp-content/themes/greenfarm/assets/css/home.css`
- Modify: `wp-content/themes/greenfarm/functions.php`
- Modify: `wp-content/themes/greenfarm/assets/js/motion.js`
- Modify: `wp-content/themes/greenfarm/tests/motion.test.js`
- Modify: `docs/greenfarm-blog-qa-checklist.md`
- Create: `docs/greenfarm-homepage-qa-checklist.md`

**Interfaces:**
- Consumes: Homepage class names from Tasks 1–4, existing design tokens, and existing reveal behavior.
- Produces: Conditionally loaded homepage CSS, optional capped card staggering, and release-verification evidence.

- [ ] **Step 1: Write failing motion behavior tests**

Extend the real-script Node test to assert repeated reveal children receive increasing delay values capped at 180ms, intersecting homepage sections reveal once, and reduced-motion sections receive no delayed transition behavior.

- [ ] **Step 2: Run JavaScript tests and verify the stagger assertions fail**

Run: `npm run test:js`

Expected: FAIL because existing `motion.js` does not assign capped stagger values.

- [ ] **Step 3: Implement homepage styles and conditional enqueue**

Add mobile-first hero, alternating section surfaces, grids, process timeline, featured-story layout, proof strip, and CTA styling in `home.css`. Enqueue it only for `is_front_page()` and preserve the existing Blog stylesheet conditions.

- [ ] **Step 4: Extend motion behavior minimally**

For elements explicitly marked with a homepage stagger attribute, set per-child CSS custom-property delays up to 180ms. Do not change default content visibility or reduced-motion behavior.

- [ ] **Step 5: Run automated verification**

Run: `npm test && npm run lint:php && node --check wp-content/themes/greenfarm/assets/js/motion.js && git diff --check`

Expected: PASS with all PHP/JavaScript tests and syntax checks green.

- [ ] **Step 6: Run WordPress Playground browser QA and document results**

Verify static front-page selection, one-H1 outline, hero with/without image, empty optional sections, populated fixtures, 320px/768px/1440px layout, keyboard focus, reduced motion, no horizontal overflow, and native image attributes against `docs/greenfarm-homepage-qa-checklist.md`.

- [ ] **Step 7: Commit**

```bash
git add wp-content/themes/greenfarm docs
git commit -m "feat: style and verify GreenFarm homepage"
```
