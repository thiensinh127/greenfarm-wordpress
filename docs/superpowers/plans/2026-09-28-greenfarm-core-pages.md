# GreenFarm Core Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Vietnamese-first, editor-managed About and Contact Page Templates at the existing `/about/` and `/contact/` routes.

**Architecture:** The classic GreenFarm theme renders two native WordPress Pages through dedicated selectable templates. Both use the main Page Loop, a small shared hero partial, existing breadcrumbs and motion, and conditionally loaded CSS; editors own all substantive copy and verified contact facts through standard Page fields and blocks.

**Tech Stack:** WordPress 6.6+, PHP 8.2+, classic PHP Page Templates, native block content and responsive image APIs, vanilla CSS/JavaScript, WordPress Playground integration tests.

**Spec:** `docs/superpowers/specs/2026-09-28-greenfarm-core-pages-design.md`

## Global Constraints

- Use native WordPress `page` objects, title, excerpt, featured image, block content, menu administration, permalink handling, and the main Loop.
- Canonical routes remain `/about/` and `/contact/`; do not add rewrite rules, redirects, automatic Page creation, or Vietnamese URL aliases.
- Initial Page content is Vietnamese; every theme-owned interface string uses the `greenfarm` text domain and WordPress translation functions.
- Do not add ACF, custom fields, custom post types, theme options, a form plugin, a page builder, or a third-party motion dependency.
- Never invent or hardcode business email, phone, address, opening hours, map, certification, biography, or social-profile data.
- Render exactly one H1 from the Page title; editor content begins at H2 and may nest H3.
- Omit absent excerpts and featured images without empty wrappers; do not convert body content into an automatic excerpt.
- Keep visible breadcrumbs and `BreadcrumbList` JSON-LD in parity; do not add unverified Organization, LocalBusiness, Person, Review, or rating schema.
- Preserve WordPress core ownership of title tags, canonical URLs, robots directives, Page indexability, and search visibility.
- Support 320px layouts, intrinsic responsive images, visible focus, 44px controls, no-JavaScript use, and reduced motion.
- Preserve all existing Blog, Search, Homepage, Product, Farm Story, archive, single, robots, motion, and lint behavior.

## Review Focus

- A Page with body content but no manual excerpt must omit the hero summary instead of exposing an auto-generated excerpt.
- A missing, deleted, or non-image featured-image reference must omit the media wrapper without leaving spacing or an empty `src`.
- A selected core Page Template must receive core-page CSS and shared motion, while an ordinary Page must receive neither asset.
- Page titles containing ampersands and quotes must remain escaped in visible breadcrumbs and safe inside JSON-LD while preserving visible/schema name parity.
- Editor-owned blocks, including `mailto:`/`tel:` Button blocks, must render through `the_content()` without the theme manufacturing or stripping verified contact facts.

---

### Task 1: Native Page Templates and Shared Hero

**Files:**
- Create: `wp-content/themes/greenfarm/page-templates/about.php`
- Create: `wp-content/themes/greenfarm/page-templates/contact.php`
- Create: `wp-content/themes/greenfarm/template-parts/core-page/hero.php`
- Create: `wp-content/themes/greenfarm/template-parts/core-page/about-cta.php`
- Modify: `wp-content/themes/greenfarm/functions.php`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: the native main Page Loop; Page title, explicit excerpt, featured-image ID, and block content; `home_url()`; `get_template_part(..., $args)`.
- Produces: `greenfarm_enable_page_excerpt(): void`; selectable templates named `GreenFarm About` and `GreenFarm Contact`; shared hero arguments `eyebrow` (`string`) and `page_kind` (`about|contact`); an About CTA linked with `home_url('/products/')` and `home_url('/contact/')`.

- [ ] **Step 1: Add a Page-template query helper and failing template tests**

Add `greenfarm_page_template_query(int $page_id, string $template): WP_Query` to the theme runner. Add tests named `Theme exposes the native Page excerpt field`, `About template renders native Page fields and a bounded continuation CTA`, `Contact template renders editor-owned contact blocks without manufactured facts`, and `Core Page templates omit optional fields safely`. Assert Page excerpt support, exact template headers, one H1, escaped title, explicit excerpt, H2/body block output, responsive attachment markup with intrinsic dimensions/`srcset`/`sizes`, About product/contact URLs, and absence of a form.

The omission test must create body content with an empty `post_excerpt`, set a deleted attachment ID as `_thumbnail_id`, and assert no hero summary, no media wrapper, no empty `src`, and no body-derived excerpt.

- [ ] **Step 2: Run the theme suite and prove RED**

Run: `npm run test:php`

Expected: FAIL because both Page Template files and shared core-page partials are absent.

- [ ] **Step 3: Implement the two native Loops and shared hero contract**

Hook `greenfarm_enable_page_excerpt(): void` to `init` and call `add_post_type_support('page', 'excerpt')` so editors can manage the otherwise-native field. Add valid WordPress `Template Name` headers. Each template calls `get_header()`, runs the native Loop once, renders `template-parts/breadcrumbs`, renders the shared hero with its fixed translated eyebrow, outputs editor content with `the_content()`, and calls `get_footer()`. The hero uses `has_excerpt()` before rendering an excerpt and validates `get_post_thumbnail_id()` with `wp_attachment_is_image()` before calling `wp_get_attachment_image()` with class `core-page-hero__image`, eager loading, high fetch priority, async decoding, and `sizes="(min-width: 64rem) 50vw, 100vw"`.

The About template includes the CTA partial after Page content. The Contact template has no theme-generated contact card or form.

- [ ] **Step 4: Run the focused integration tests**

Run: `npm run test:php`

Expected: All core Page field, omission, one-H1, responsive-image, CTA, and existing theme assertions pass.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm/page-templates wp-content/themes/greenfarm/template-parts/core-page wp-content/themes/greenfarm/functions.php wp-content/themes/greenfarm/tests/run.php
git commit -m "feat: add native GreenFarm core pages"
```

### Task 2: Page Breadcrumbs, SEO Boundaries, and Safe Structured Data

**Files:**
- Modify: `wp-content/themes/greenfarm/template-parts/breadcrumbs.php`
- Modify: `wp-content/themes/greenfarm/tests/run.php`

**Interfaces:**
- Consumes: the current queried Page, `get_the_title()`, `home_url('/')`, the existing `$greenfarm_crumbs` array, and `wp_json_encode()`.
- Produces: Home → current Page visible breadcrumbs and matching `BreadcrumbList` items for non-front Page contexts.

- [ ] **Step 1: Write failing breadcrumb-parity and indexability tests**

Add tests named `Core Page breadcrumb matches its safe BreadcrumbList data` and `Published core Pages keep WordPress index defaults`. Render an About Page whose title contains `&` and quotation marks; assert the visible trail escapes it, the JSON-LD parses, both arrays contain exactly Home then the original plain title, Home has an item URL, and the current Page has no redundant item URL. Capture `wp_robots()` and assert the published Page does not receive `noindex`.

Keep the existing Blog, category, Product, Farm Story, search, and 404 breadcrumb/robots assertions unchanged as regression coverage.

- [ ] **Step 2: Run the theme suite and prove RED**

Run: `npm run test:php`

Expected: FAIL because a native Page context currently produces only the Home crumb.

- [ ] **Step 3: Extend the existing breadcrumb decision tree**

Add one `is_page() && ! is_front_page()` branch that appends the current Page title with an empty URL. Preserve every existing archive and singular branch. Keep schema serialization in the established partial and rely on the current escaped visible output plus WordPress JSON encoding.

- [ ] **Step 4: Run breadcrumb and full regression verification**

Run: `npm run test:php && npm run test:content`

Expected: Core Page parity/indexability and all existing Blog/content-model breadcrumb and robots assertions pass.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/greenfarm/template-parts/breadcrumbs.php wp-content/themes/greenfarm/tests/run.php
git commit -m "feat: add core Page breadcrumbs"
```

### Task 3: Conditional Assets and Mobile-first Presentation

**Files:**
- Create: `wp-content/themes/greenfarm/assets/css/core-pages.css`
- Modify: `wp-content/themes/greenfarm/functions.php`
- Modify: `wp-content/themes/greenfarm/tests/run.php`
- Modify: `wp-content/themes/greenfarm/tests/motion.test.js`

**Interfaces:**
- Consumes: Page Template slugs `page-templates/about.php` and `page-templates/contact.php`, existing `greenfarm-style`, existing `greenfarm-motion`, `[data-reveal]`, and `[data-reveal-child]` hooks.
- Produces: `greenfarm_is_core_page_view(): bool`, conditional `greenfarm-core-pages` style enqueue, and shared motion enqueue for either dedicated template.

- [ ] **Step 1: Write failing conditional-asset tests**

Add a helper that runs `greenfarm_enqueue_assets()` against a Page query after assigning `_wp_page_template`. Add `Core Page assets load only for the two dedicated templates`, asserting About and Contact each enqueue `greenfarm-core-pages` and `greenfarm-motion`, while `default` and an unrelated custom-template slug enqueue neither. Also assert Blog/Home/content-model style handles are not introduced by a core Page request.

- [ ] **Step 2: Pin motion fallback behavior for core-page markup**

Extend the existing Node test fixtures with a representative core Page reveal section. Assert reduced-motion immediately adds `is-visible`, missing `IntersectionObserver` leaves content readable, and the observer path adds visibility once intersecting. Reuse the production script; do not add a second motion implementation.

- [ ] **Step 3: Run focused suites and prove RED**

Run: `npm run test:php && npm run test:js`

Expected: FAIL because the core-page conditional, stylesheet, and matching fixture coverage do not exist.

- [ ] **Step 4: Implement the conditional and mobile-first CSS**

Define `greenfarm_is_core_page_view(): bool` with `is_page_template(array('page-templates/about.php', 'page-templates/contact.php'))`. Enqueue `core-pages.css` after `greenfarm-style` and include core-page views in the existing shared-motion condition.

Style the breadcrumb/hero/content/media/CTA classes with existing color, radius, shadow, container, and reading-width tokens. Use a single column at 320px; add tablet/desktop composition at the existing `40rem` and `64rem` conventions; constrain editor content to the reading width; make long text/URLs wrap; keep links/buttons at least 44px tall; animate only opacity/transform under `.has-reveal-motion`; and disable reveal transitions/transforms under `prefers-reduced-motion: reduce`.

- [ ] **Step 5: Run asset, motion, syntax, and regression checks**

Run: `npm test && npm run lint:php && node --check wp-content/themes/greenfarm/assets/js/motion.js && git diff --check`

Expected: All theme/plugin/content integration and JavaScript suites pass; all PHP parses; production motion syntax and whitespace checks pass.

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/greenfarm/assets/css/core-pages.css wp-content/themes/greenfarm/functions.php wp-content/themes/greenfarm/tests
git commit -m "feat: style GreenFarm core pages"
```

### Task 4: Editor Setup, Runtime QA, and Pull Request

**Files:**
- Create: `docs/greenfarm-core-pages-setup.md`
- Create: `docs/greenfarm-core-pages-qa-checklist.md`

**Interfaces:**
- Consumes: the completed templates, English route contract, native menu workflow, current Playground commands, and the approved spec.
- Produces: reproducible Vietnamese-first editor setup instructions, release evidence, and a public Pull Request targeting `main`.

- [ ] **Step 1: Write the editor setup guide**

Document Page creation, exact `about`/`contact` slugs, matching template selection, title/manual excerpt/featured-image fields, H2/H3 block hierarchy, menu assignment through Appearance → Menus, and homepage-link verification. Include concise Vietnamese block outlines but label every brand/contact fact as editor-supplied; include no sample email, phone, address, hours, or fabricated claim.

- [ ] **Step 2: Run final automated verification from a clean command sequence**

Run:

```bash
npm test
npm run lint:php
node --check wp-content/themes/greenfarm/assets/js/motion.js
node --check wp-content/themes/greenfarm/assets/js/share.js
git diff --check
```

Expected: Theme, plugin, content integration, and JavaScript tests report zero failures; theme/plugin PHP trees parse; both production scripts pass syntax checking; no whitespace errors are reported.

- [ ] **Step 3: Run WordPress Playground runtime QA**

Create temporary published About and Contact Pages with their exact slugs/templates and Vietnamese test content. Verify both routes return HTTP 200, one H1, correct explicit excerpt behavior, body blocks, featured-image attributes, Home → Page breadcrumb parity, indexable robots defaults, conditional CSS/motion, About CTA routes, no Contact form or invented facts, and valid no-JavaScript markup. Verify an ordinary Page does not load core-page assets and a missing route returns the native 404.

Audit keyboard focus, reduced motion, and horizontal overflow at 320px, 768px, and 1440px. Keep runtime fixtures outside tracked source and record the environment, commands, results, and any browser-surface limitation in `docs/greenfarm-core-pages-qa-checklist.md`.

- [ ] **Step 4: Review the complete branch against the spec**

Review `origin/main..HEAD` for scope, data ownership, escaping, exactly-one-H1 behavior, breadcrumb/schema parity, conditional assets, responsive images, no-JavaScript behavior, and regressions. Fix every blocking finding and rerun the relevant verification command before proceeding.

- [ ] **Step 5: Commit documentation and verified corrections**

```bash
git add docs/greenfarm-core-pages-setup.md docs/greenfarm-core-pages-qa-checklist.md wp-content/themes/greenfarm
git commit -m "docs: verify GreenFarm core pages"
```

- [ ] **Step 6: Push and create the Pull Request**

Push `feature/greenfarm-core-pages`, create a public GitHub Pull Request targeting `main`, and include the implemented scope plus exact automated/runtime QA results. Do not merge the Pull Request. Report its URL and ask the repository owner to review and merge it on GitHub.
