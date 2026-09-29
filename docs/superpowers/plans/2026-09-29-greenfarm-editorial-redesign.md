# GreenFarm Editorial Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the GreenFarm theme into a credible editorial farm journal where products support real stories rather than mimic a generic ecommerce catalogue.

**Architecture:** Keep the classic PHP theme and the `greenfarm-core` content plugin intact. Build the shared visual language in existing CSS files, then apply it through current archive, single, Page, and component templates without altering WordPress query ownership or content-model contracts.

**Tech Stack:** WordPress 6.6+, PHP 8.2+, classic PHP templates, native responsive images, vanilla CSS, existing optional reveal JavaScript, WordPress Playground test suites.

**Spec:** `docs/superpowers/specs/2026-09-29-greenfarm-editorial-redesign-design.md`

## Global Constraints

- Preserve the theme/plugin ownership boundary and all existing native WordPress loops.
- Add no page builder, UI framework, ecommerce flow, external font, remote asset, or animation dependency.
- Keep all public content server-rendered and useful with JavaScript disabled.
- Preserve one H1 per page, visible keyboard focus, 44px interactive targets, responsive images, reduced-motion behavior, and existing robots directives.
- Retain editor ownership of Page, Product, Farm Story, and Post content; do not invent business facts.
- Verify 320px, 768px, and 1440px layouts in the full Playground demo.

## Review Focus

- A missing featured image must leave every hero/card readable with no empty visual frame or layout break; Task 2 and Task 3 retain existing optional-image tests.
- An inactive `greenfarm-core` plugin must leave the homepage and normal Pages safe; Task 2 reruns the plugin-disabled homepage fixture.
- A configured Primary menu must continue to override the GreenFarm fallback; Task 1 adds a real WordPress menu-location assertion.
- Empty Product, Farm Story, Journal, and search queries must retain their accessible recovery states; Task 3 and Task 4 retain their real template fixtures.
- Long titles, URLs, and translated strings must wrap at 320px without horizontal overflow; Task 5 records visual QA at all required widths.

---

### Task 1: Establish the editorial shell and navigation

**Files:**
- Modify: `wp-content/themes/greenfarm/header.php`
- Modify: `wp-content/themes/greenfarm/footer.php`
- Modify: `wp-content/themes/greenfarm/functions.php`
- Modify: `wp-content/themes/greenfarm/style.css`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: `greenfarm_primary_menu_fallback(array $args = array()): void`, registered `primary` and `footer` menu locations, existing color tokens.
- Produces: an editorial site shell and a fallback navigation exposing Products, Farm Stories, Journal, About GreenFarm, and Contact when no Primary menu is assigned.

- [ ] **Step 1: Write failing shell and menu assertions in `tests/run.php`**

Render `header.php` with no assigned Primary menu and assert the five GreenFarm routes and semantic Primary navigation exist. Assign a test menu to `primary` and assert its item replaces the fallback list.

- [ ] **Step 2: Run the focused theme suite to verify RED**

Run: `npm run test:php`

Expected: FAIL because the fallback lacks Farm Stories and/or the shell does not expose the new editorial hooks.

- [ ] **Step 3: Implement the smallest shared shell update**

Keep `wp_nav_menu()` as the configured-menu path. Update `greenfarm_primary_menu_fallback()` only for the missing stable public route. Add scoped header/footer classes and use existing tokens for quiet navigation, responsive wrapping, and keyboard-visible focus.

- [ ] **Step 4: Run the focused theme suite to verify GREEN**

Run: `npm run test:php`

Expected: 30+ tests, 0 failures.

- [ ] **Step 5: Commit the shell task**

```bash
git add wp-content/themes/greenfarm/{header.php,footer.php,functions.php,style.css,tests/run.php}
git commit -m "feat: establish GreenFarm editorial shell"
```

### Task 2: Make the homepage the editorial reference

**Files:**
- Modify: `wp-content/themes/greenfarm/front-page.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/hero.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/introduction.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/products.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/stories.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/latest-posts.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/values-process.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/proof.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/cta.php`
- Modify: `wp-content/themes/greenfarm/assets/css/home.css`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: native static front-Page query, existing Product/Farm Story optional queries, `greenfarm_get_blog_url()`, shared card partials, and `data-reveal` markup.
- Produces: content-led hero, bounded seasonal-product strip, featured-story treatment, latest Journal path, and one closing invitation without changing query limits or globals.

- [ ] **Step 1: Write failing homepage semantic assertions**

Extend the existing homepage integration test to assert one editorial primary action, a named featured-story section when stories exist, products after story context, and no duplicated heading levels or leaked Page query context.

- [ ] **Step 2: Run the theme and content integration suites to verify RED**

Run: `npm run test:php && npm run test:content`

Expected: FAIL on the new editorial landmark/order assertion.

- [ ] **Step 3: Recompose existing homepage partials and styles**

Keep each existing query and limit. Change only markup classes, section order, copy wrappers, and CSS so real Page content and a seasonal story lead; products appear as a small contextual strip instead of a store grid. Preserve no-image and plugin-disabled branches.

- [ ] **Step 4: Run homepage regressions to verify GREEN**

Run: `npm run test:php && npm run test:content && node --check wp-content/themes/greenfarm/assets/js/motion.js`

Expected: all theme and content integration tests pass.

- [ ] **Step 5: Commit the homepage task**

```bash
git add wp-content/themes/greenfarm/{front-page.php,template-parts/home,assets/css/home.css,tests/run.php}
git commit -m "feat: redesign GreenFarm homepage editorially"
```

### Task 3: Reframe Product and Farm Story journeys

**Files:**
- Modify: `wp-content/themes/greenfarm/archive-greenfarm_product.php`
- Modify: `wp-content/themes/greenfarm/single-greenfarm_product.php`
- Modify: `wp-content/themes/greenfarm/archive-farm_story.php`
- Modify: `wp-content/themes/greenfarm/single-farm_story.php`
- Modify: `wp-content/themes/greenfarm/template-parts/content-product-card.php`
- Modify: `wp-content/themes/greenfarm/template-parts/content-story-card.php`
- Modify: `wp-content/themes/greenfarm/template-parts/product-gallery.php`
- Modify: `wp-content/themes/greenfarm/assets/css/content-models.css`
- Modify: `wp-content/themes/greenfarm/tests/content-models.php`

**Interfaces:**
- Consumes: plugin-owned Product metadata, Product Category taxonomy, native archive main loops, shared card arguments, and gallery validation.
- Produces: product stories with practical facts, story-first archive hierarchy, and contextual cross-links without altering plugin metadata or pagination.

- [ ] **Step 1: Write failing content-model visual-structure assertions**

Assert Product archive cards expose availability before description, Product singles retain facts/gallery/content in a readable editorial order, and Farm Story archive/single retain independent story-first structures. Keep empty archive and responsive-image assertions.

- [ ] **Step 2: Run the content-model suite to verify RED**

Run: `npm run test:content`

Expected: FAIL on the newly required DOM order or component class.

- [ ] **Step 3: Implement the editorial composition in existing templates**

Use the unchanged main loop and metadata reads. Add only component classes and semantic wrappers needed for the styles; do not add Product fields, write a contact form, or replace adjacent navigation.

- [ ] **Step 4: Apply responsive editorial styling**

Update `content-models.css` for a lead-first archive rhythm, readable product fact panel, image-safe cards, text wrapping, and responsive desktop expansion. Retain 44px controls and reduced-motion compatibility.

- [ ] **Step 5: Run content-model verification to verify GREEN**

Run: `npm run test:content && npm run lint:php:theme`

Expected: 12+ content tests and PHP parse checks pass.

- [ ] **Step 6: Commit the content journey task**

```bash
git add wp-content/themes/greenfarm/{archive-greenfarm_product.php,single-greenfarm_product.php,archive-farm_story.php,single-farm_story.php,template-parts/content-product-card.php,template-parts/content-story-card.php,template-parts/product-gallery.php,assets/css/content-models.css,tests/content-models.php}
git commit -m "feat: refine GreenFarm product and story journeys"
```

### Task 4: Align Journal and core Page reading experiences

**Files:**
- Modify: `wp-content/themes/greenfarm/home.php`
- Modify: `wp-content/themes/greenfarm/single.php`
- Modify: `wp-content/themes/greenfarm/template-parts/article-header.php`
- Modify: `wp-content/themes/greenfarm/template-parts/content-post-card.php`
- Modify: `wp-content/themes/greenfarm/page-templates/about.php`
- Modify: `wp-content/themes/greenfarm/page-templates/contact.php`
- Modify: `wp-content/themes/greenfarm/template-parts/core-page/about-cta.php`
- Modify: `wp-content/themes/greenfarm/assets/css/blog.css`
- Modify: `wp-content/themes/greenfarm/assets/css/core-pages.css`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: native Posts archive/single loop, Article JSON-LD, core Page hero partial, editor Page body, and explicit excerpts.
- Produces: a calm Journal reading flow, context-first cards, a trust-building About path, and a factual Contact path without changing editor-owned content or SEO schema.

- [ ] **Step 1: Write failing Journal and core Page assertions**

Add assertions that the Journal archive retains one H1 and its recovery path, an Article has one primary next action area, About renders its continuation CTA after editor content, and Contact has no manufactured conversion UI.

- [ ] **Step 2: Run the theme suite to verify RED**

Run: `npm run test:php`

Expected: FAIL on a new editorial class/ordering assertion.

- [ ] **Step 3: Update templates and CSS with the shared editorial rhythm**

Keep article schema, sharing, taxonomy, related posts, breadcrumbs, Page loops, and contact facts unchanged. Use CSS to set reading widths, image rhythm, quiet metadata, and one clear next action per context.

- [ ] **Step 4: Run Journal/core Page regressions to verify GREEN**

Run: `npm run test:php && node --check wp-content/themes/greenfarm/assets/js/share.js`

Expected: theme suite passes with valid production JavaScript syntax.

- [ ] **Step 5: Commit the reading-experience task**

```bash
git add wp-content/themes/greenfarm/{home.php,single.php,page-templates,template-parts/article-header.php,template-parts/content-post-card.php,template-parts/core-page/about-cta.php,assets/css/blog.css,assets/css/core-pages.css,tests/run.php}
git commit -m "feat: align GreenFarm editorial reading experiences"
```

### Task 5: Finish recovery states and full visual QA

**Files:**
- Modify: `wp-content/themes/greenfarm/404.php`
- Modify: `wp-content/themes/greenfarm/style.css`
- Modify: `wp-content/themes/greenfarm/tests/run.php`
- Modify: `docs/greenfarm-editorial-redesign-qa-checklist.md`

**Interfaces:**
- Consumes: `greenfarm_get_blog_url()`, WordPress search form, `greenfarm_archive_robots()`, global design tokens, and the full ignored Playground demo fixtures.
- Produces: deliberate 404 recovery, final responsive accessibility evidence, and a recorded visual QA checklist.

- [ ] **Step 1: Write failing 404 recovery assertions**

Assert a dedicated `error-hero`, one H1, native search form, home and Journal recovery links, and `noindex` robots for a real 404 query.

- [ ] **Step 2: Run the theme suite to verify RED**

Run: `npm run test:php`

Expected: FAIL because the 404 does not yet provide the final recovery structure.

- [ ] **Step 3: Implement the focused recovery state**

Use only `404.php` and global CSS. Keep the native form, a clear home path, Journal path, and no added JavaScript.

- [ ] **Step 4: Run all automated verification**

Run: `npm test && npm run lint:php && node --check wp-content/themes/greenfarm/assets/js/motion.js && node --check wp-content/themes/greenfarm/assets/js/share.js && git diff --check`

Expected: all theme, plugin, content, JS, lint, syntax, and whitespace checks pass.

- [ ] **Step 5: Run full-demo visual QA and document it**

Use the ignored full Playground demo to inspect Homepage, Product archive/single, Farm Story archive/single, Journal archive/single, About, Contact, and 404 at 320px, 768px, and 1440px. Confirm no overflow, meaningful empty states, visible keyboard focus, reduced motion, and working contextual routes. Record command/environment/results in `docs/greenfarm-editorial-redesign-qa-checklist.md`.

- [ ] **Step 6: Commit verification and final recovery state**

```bash
git add wp-content/themes/greenfarm/{404.php,style.css,tests/run.php} docs/greenfarm-editorial-redesign-qa-checklist.md
git commit -m "feat: verify GreenFarm editorial redesign"
```
