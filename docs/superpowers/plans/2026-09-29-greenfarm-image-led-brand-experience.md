# GreenFarm Image-Led Brand Experience Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make GreenFarm an image-led farm-brand site that uses seasonal Products and field stories to build trust without introducing ecommerce.

**Architecture:** Retain the classic PHP theme and `greenfarm-core` content boundary. Compose the experience from native featured images, excerpts, page content, Product metadata, and Product galleries; scoped CSS establishes visual hierarchy and optional existing motion remains progressive enhancement.

**Tech Stack:** WordPress classic PHP theme, `greenfarm-core`, native responsive-image APIs, CSS, existing vanilla JavaScript tests.

**Spec:** `docs/superpowers/specs/2026-09-29-greenfarm-image-led-brand-design.md`

## Global Constraints

- Do not add cart, price, promotion, checkout, account, search-to-buy, payment, delivery-policy, or ecommerce plugin behavior.
- Use only editor-supplied WordPress imagery; never add AI, stock, or generic image placeholders.
- Keep `greenfarm-core` as the Product and Farm Story content-model owner.
- Preserve one H1, keyboard-visible focus, 44px controls, responsive images, reduced motion, and `noindex` protection for utility contexts.
- Add no dependency, custom content model, page builder, external font, or image pipeline.

## Review Focus

- Homepage with no featured image must remain readable and retain its Farm Stories CTA; test in Task 2.
- Optional Product origin/season values must not leave empty metadata UI; test in Task 3.
- Lead-story CSS must degrade to a single-column readable sequence below desktop; test in Tasks 3 and 4.
- Long unbroken headings and fallback navigation labels must not cause horizontal overflow at small widths; test in Task 1.
- About and Contact without editor content or images must omit empty regions; test in Task 5.

---

## File Structure

- `wp-content/themes/greenfarm/style.css` — global navigation, focus, and image-led surface rules.
- `wp-content/themes/greenfarm/functions.php` — fallback navigation ordering only.
- `wp-content/themes/greenfarm/front-page.php`, `template-parts/home/*.php`, `assets/css/home.css` — homepage narrative and image-led composition.
- `wp-content/themes/greenfarm/archive-*.php`, `single-*.php`, `template-parts/content-*-card.php`, `assets/css/content-models.css` — Product and Farm Story hierarchy.
- `wp-content/themes/greenfarm/home.php`, `template-parts/content-post-card.php`, `assets/css/blog.css` — Journal hierarchy.
- `wp-content/themes/greenfarm/page-templates/*.php`, `template-parts/core-page/*.php`, `assets/css/core-pages.css`, `404.php` — image-aware core/recovery experiences.
- `wp-content/themes/greenfarm/tests/run.php`, `tests/content-models.php` — regression coverage for rendered native content and responsive/accessibility hooks.

### Task 1: Establish the image-led global shell

**Files:**
- Modify: `wp-content/themes/greenfarm/style.css:51-115`
- Modify: `wp-content/themes/greenfarm/functions.php:166-176`
- Test: `wp-content/themes/greenfarm/tests/run.php:295-318`

**Interfaces:**
- Consumes: existing `greenfarm_primary_menu_fallback(array $args = array()): void`.
- Produces: accessible fallback order and shared `site-header`/navigation visual rules used by every public route.

- [ ] **Step 1: Write failing fallback/navigation CSS assertions**

Add checks that fallback item order is Products, Farm Stories, Journal, About GreenFarm, Contact; assert global navigation links use `min-height: 2.75rem`, and the small-screen global CSS preserves `overflow-wrap: anywhere` for headings.

- [ ] **Step 2: Run the focused theme test to verify it fails**

Run: `npm run test:php`
Expected: FAIL on the missing order or global responsive/accessibility assertion.

- [ ] **Step 3: Refine the global shell in `style.css` and fallback ordering in `functions.php`**

Keep the header compact and photo-led rather than introducing a utility bar, search, promotional strip, or shop controls. Use native CSS only for 44px targets, focus, text wrapping, and restrained surfaces.

- [ ] **Step 4: Run the focused theme test to verify it passes**

Run: `npm run test:php`
Expected: 0 failures.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm/style.css wp-content/themes/greenfarm/functions.php wp-content/themes/greenfarm/tests/run.php
git commit -m "feat: establish GreenFarm image-led shell"
```

### Task 2: Recompose the homepage around real imagery and field stories

**Files:**
- Modify: `wp-content/themes/greenfarm/front-page.php:12-27`
- Modify: `wp-content/themes/greenfarm/template-parts/home/hero.php:9-43`
- Modify: `wp-content/themes/greenfarm/template-parts/home/introduction.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/stories.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/products.php`
- Modify: `wp-content/themes/greenfarm/assets/css/home.css`
- Test: `wp-content/themes/greenfarm/tests/run.php:620-825`

**Interfaces:**
- Consumes: native Page title, excerpt, featured image, existing bounded Product and Farm Story queries.
- Produces: homepage section classes `home-hero`, `home-stories--featured`, and `home-products` in editorial order for route tests.

- [ ] **Step 1: Write failing homepage narrative tests**

Assert the hero CTA targets `/farm-stories/`; the page retains one H1 when its featured image is absent; the featured Farm Story section precedes seasonal Products; and bounded Product/Story queries restore Page context.

- [ ] **Step 2: Run the focused theme test to verify it fails**

Run: `npm run test:php`
Expected: FAIL on the new image-led narrative assertion.

- [ ] **Step 3: Update homepage templates and `home.css`**

Make the Page featured image optional and dominant when supplied, turn introduction/process/proof blocks into image-and-evidence composition without manufactured claims, preserve the lead-story-first order, and retain Products as a no-price seasonal discovery strip.

- [ ] **Step 4: Run the focused theme test to verify it passes**

Run: `npm run test:php`
Expected: 0 failures.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm/front-page.php wp-content/themes/greenfarm/template-parts/home wp-content/themes/greenfarm/assets/css/home.css wp-content/themes/greenfarm/tests/run.php
git commit -m "feat: build GreenFarm image-led homepage"
```

### Task 3: Make Product and Farm Story paths photo-led and contextual

**Files:**
- Modify: `wp-content/themes/greenfarm/archive-greenfarm_product.php`
- Modify: `wp-content/themes/greenfarm/archive-farm_story.php`
- Modify: `wp-content/themes/greenfarm/single-greenfarm_product.php`
- Modify: `wp-content/themes/greenfarm/single-farm_story.php`
- Modify: `wp-content/themes/greenfarm/template-parts/content-product-card.php`
- Modify: `wp-content/themes/greenfarm/template-parts/content-story-card.php`
- Modify: `wp-content/themes/greenfarm/assets/css/content-models.css`
- Test: `wp-content/themes/greenfarm/tests/content-models.php:240-800`

**Interfaces:**
- Consumes: `greenfarm_origin`, `greenfarm_harvest_season`, availability, gallery IDs, native featured images, and the existing card arguments (`heading_level`, `card_class`, `image_size`, `image_context`).
- Produces: Product provenance presentation, a Contact route from Product singles, and a desktop lead-story archive layout that collapses naturally below `64rem`.

- [ ] **Step 1: Write failing content-model tests**

Assert Product cards show populated origin/harvest-season metadata, omit the metadata line if both are blank, Product singles include a Contact link but no buy/cart controls, and the first Farm Story card spans the desktop grid only inside the `min-width: 64rem` rule.

- [ ] **Step 2: Run the focused content test to verify it fails**

Run: `npm run test:content`
Expected: FAIL on the missing provenance, contact, or lead-layout behavior.

- [ ] **Step 3: Update Product/Story templates and scoped CSS**

Use existing images and fields only. Keep Product facts and gallery native, add one low-pressure Contact action, and compose the first Story as a visual lead on desktop while all archive cards remain semantic H2 articles.

- [ ] **Step 4: Run the focused content test to verify it passes**

Run: `npm run test:content`
Expected: 0 failures.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm/archive-greenfarm_product.php wp-content/themes/greenfarm/archive-farm_story.php wp-content/themes/greenfarm/single-greenfarm_product.php wp-content/themes/greenfarm/single-farm_story.php wp-content/themes/greenfarm/template-parts/content-product-card.php wp-content/themes/greenfarm/template-parts/content-story-card.php wp-content/themes/greenfarm/assets/css/content-models.css wp-content/themes/greenfarm/tests/content-models.php
git commit -m "feat: refine GreenFarm product and story imagery"
```

### Task 4: Apply lead-article hierarchy to the Journal

**Files:**
- Modify: `wp-content/themes/greenfarm/home.php`
- Modify: `wp-content/themes/greenfarm/template-parts/content-post-card.php`
- Modify: `wp-content/themes/greenfarm/assets/css/blog.css`
- Test: `wp-content/themes/greenfarm/tests/run.php:336-362`

**Interfaces:**
- Consumes: native main Loop, existing `post-card` template, and WordPress category/permalink data.
- Produces: `journal-archive--editorial` lead-article composition while preserving H2 card headings and native pagination.

- [ ] **Step 1: Write failing Journal layout tests**

Assert the Journal archive has one H1, first card spans the grid only at `min-width: 64rem`, post title links remain H2, and native pagination remains present.

- [ ] **Step 2: Run the focused theme test to verify it fails**

Run: `npm run test:php`
Expected: FAIL on the missing lead-article composition assertion.

- [ ] **Step 3: Implement the scoped Journal visual hierarchy**

Promote only the first real post using CSS and existing card markup; do not add carousel behavior, artificial featured content, or a separate query.

- [ ] **Step 4: Run the focused theme test to verify it passes**

Run: `npm run test:php`
Expected: 0 failures.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm/home.php wp-content/themes/greenfarm/template-parts/content-post-card.php wp-content/themes/greenfarm/assets/css/blog.css wp-content/themes/greenfarm/tests/run.php
git commit -m "feat: give GreenFarm Journal a lead article"
```

### Task 5: Align About, Contact, and recovery pages with the image-led system

**Files:**
- Modify: `wp-content/themes/greenfarm/page-templates/about.php`
- Modify: `wp-content/themes/greenfarm/page-templates/contact.php`
- Modify: `wp-content/themes/greenfarm/template-parts/core-page/hero.php`
- Modify: `wp-content/themes/greenfarm/template-parts/core-page/about-cta.php`
- Modify: `wp-content/themes/greenfarm/assets/css/core-pages.css`
- Modify: `wp-content/themes/greenfarm/404.php`
- Modify: `wp-content/themes/greenfarm/style.css`
- Test: `wp-content/themes/greenfarm/tests/run.php:828-980`

**Interfaces:**
- Consumes: native Page title, excerpt, content, featured image, and existing core-page hero arguments (`eyebrow`, `page_kind`).
- Produces: optional image-aware Page headers and quiet noindex recovery behavior without invented contact facts.

- [ ] **Step 1: Write failing core-page/recovery tests**

Assert About and Contact preserve one H1 and omit empty editor/image regions, supplied featured images use intrinsic responsive markup, Contact exposes only editor-owned connection actions, and 404 retains native search, Home/Journal links, and `noindex`.

- [ ] **Step 2: Run the focused theme test to verify it fails**

Run: `npm run test:php`
Expected: FAIL on the new image-aware or empty-state assertion.

- [ ] **Step 3: Implement image-aware core-page composition**

Use current hero/CTA templates and native attachment APIs; keep About narrative, Contact factual, and 404 calm. Do not manufacture testimonials, certifications, addresses, forms, or imagery.

- [ ] **Step 4: Run the focused theme test to verify it passes**

Run: `npm run test:php`
Expected: 0 failures.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm/page-templates wp-content/themes/greenfarm/template-parts/core-page wp-content/themes/greenfarm/assets/css/core-pages.css wp-content/themes/greenfarm/404.php wp-content/themes/greenfarm/style.css wp-content/themes/greenfarm/tests/run.php
git commit -m "feat: align GreenFarm core pages with imagery"
```

### Task 6: Verify the image-led demo and document editor handoff

**Files:**
- Modify: `docs/greenfarm-image-led-brand-qa-checklist.md`
- Test: `wp-content/themes/greenfarm/tests/run.php`
- Test: `wp-content/themes/greenfarm/tests/content-models.php`
- Test: `wp-content/themes/greenfarm/tests/*.test.js`

**Interfaces:**
- Consumes: all prior task templates, CSS classes, and existing ignored Playground demo fixtures.
- Produces: a QA handoff separating automated evidence from editor-owned photography/copy and production browser review.

- [ ] **Step 1: Write the QA checklist before the final run**

List required demo routes (`/`, `/products/`, `/farm-stories/`, `/journal/`, `/about/`, `/contact/`, and an unknown path), 320px/768px/1440px image/layout checks, keyboard walkthrough, real-media replacement, and all expected automated commands.

- [ ] **Step 2: Run the full verification suite**

Run: `npm test && npm run lint:php && git diff --check`
Expected: theme, plugin, content, and JavaScript suites have 0 failures; PHP parses cleanly; no whitespace errors.

- [ ] **Step 3: Inspect full Playground demo routes at 320px, 768px, and 1440px**

Verify desktop lead cards become normal readable single-column cards below desktop, the no-image state does not collapse sections, and keyboard focus remains visible through header, CTAs, and native search.

- [ ] **Step 4: Record observed results and the remaining editor-owned tasks**

Only mark a manual result complete after it is observed. Keep real photography/copy replacement unchecked until supplied by the editor.

- [ ] **Step 5: Commit**

```bash
git add docs/greenfarm-image-led-brand-qa-checklist.md
git commit -m "docs: add GreenFarm image-led QA handoff"
```

## Self-review

- Spec coverage: Tasks 1–5 cover navigation, homepage, Product/Story, Journal, and core/recovery pages; Task 6 covers the route, responsive, keyboard, and quality requirements.
- Step scan: every code task starts with an explicit failing assertion and ends with a command and focused commit.
- Type consistency: all template arguments and metadata names match existing theme interfaces; no new PHP API is introduced.
- Review focus: all five likely failure modes are assigned to a task test or manual route check.
- Proportion: the plan records interfaces, exact files, and test evidence without prescribing CSS declarations or duplicating implementation code.
