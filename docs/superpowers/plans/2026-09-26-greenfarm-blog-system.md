# GreenFarm Blog System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the responsive, accessible, SEO-ready native WordPress GreenFarm blog system defined in the approved design.

**Architecture:** A custom classic theme owns blog presentation and light progressive enhancement. Native WordPress main queries render Blog, Category, and Search contexts; `WP_Query` is restricted to a reusable related-posts template part. Core template hierarchy selects views, while template parts hold shared semantic markup.

**Tech Stack:** WordPress 6.6+, PHP 8.2+, classic PHP theme templates, WordPress PHPUnit test suite, vanilla CSS, progressive-enhancement vanilla JavaScript.

**Spec:** `docs/superpowers/specs/2026-09-26-greenfarm-blog-design.md`

## Global Constraints

- Use native WordPress features before adding any plugin or dependency.
- Use a custom classic theme named `greenfarm`; do not use a page builder or child theme.
- Posts use `/blog/%postname%/`; the Blog archive is `/blog/`; Categories use `/category/{slug}/`.
- Use the global main query for Blog, Category, and Search; only related Posts use a secondary `WP_Query`.
- Output one page-level H1 per template; article editor content begins at H2.
- Search, empty archives, attachment pages, generated filter URLs, and Tag archives initially emit `noindex,follow`.
- Use native WordPress responsive image functions with attachment metadata; no manually composed image URLs.
- Use real photography, forest green foundations, restrained lime accents, warm off-white surfaces, and generous editorial spacing.
- Scroll reveals use `opacity` and `transform` once per section and respect `prefers-reduced-motion: reduce`.
- No sliders, autoplay media, third-party share widgets, tracking share counts, or render-blocking third-party scripts.

## Review Focus

- A published Post with no Category must omit the related-articles section rather than issue a broad query.
- A Post assigned multiple Categories must choose a deterministic breadcrumb category without creating alternate article URLs.
- Search queries with no matching Posts must retain one H1 and render an accessible empty state with noindex metadata.
- A missing featured image must not leave empty image markup, broken cards, or layout-dependent CSS gaps.
- A user with reduced-motion enabled must see all content immediately without hidden/transformed sections.

---

### Task 1: Establish WordPress Theme and Test Baseline

**Files:**
- Create: `wp-content/themes/greenfarm/style.css`
- Create: `wp-content/themes/greenfarm/functions.php`
- Create: `wp-content/themes/greenfarm/index.php`
- Create: `wp-content/themes/greenfarm/tests/bootstrap.php`
- Create: `wp-content/themes/greenfarm/tests/test-theme-support.php`

**Interfaces:**
- Consumes: A local WordPress 6.6+ install and WordPress PHPUnit test framework.
- Produces: Activatable `greenfarm` theme with required theme supports and a repeatable PHPUnit command.

- [ ] **Step 1: Initialize the project repository and local WordPress test configuration**

Create the Git repository if absent. Configure a local WordPress/PHPUnit test bootstrap that loads `wp-content/themes/greenfarm`; document the exact local database and test database variables in an untracked environment file.

- [ ] **Step 2: Write the failing theme-support test**

```php
public function test_theme_registers_post_thumbnails_and_html5_support(): void {
    $this->assertTrue( current_theme_supports( 'post-thumbnails' ) );
    $this->assertTrue( current_theme_supports( 'html5', 'search-form' ) );
}
```

- [ ] **Step 3: Run the focused PHPUnit test to verify it fails because the theme setup is absent**

Run: `phpunit --filter test_theme_registers_post_thumbnails_and_html5_support`

Expected: FAIL because `greenfarm` has not registered the required supports.

- [ ] **Step 4: Create the minimal theme shell and register theme supports in `functions.php`**

Register `post-thumbnails`, `title-tag`, responsive embeds, and HTML5 support for search form, comment form, comment list, gallery, caption, style, and script. Keep `index.php` as the hierarchy fallback and `style.css` as the WordPress theme header plus global tokens.

- [ ] **Step 5: Run the focused test and full theme PHPUnit suite**

Run: `phpunit --filter test_theme_registers_post_thumbnails_and_html5_support && phpunit`

Expected: PASS with no failures.

- [ ] **Step 6: Commit the theme baseline**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add GreenFarm theme baseline"
```

### Task 2: Build Native Blog, Category, and Search Archives

**Files:**
- Create: `wp-content/themes/greenfarm/home.php`
- Create: `wp-content/themes/greenfarm/category.php`
- Create: `wp-content/themes/greenfarm/search.php`
- Create: `wp-content/themes/greenfarm/template-parts/content-post-card.php`
- Create: `wp-content/themes/greenfarm/template-parts/content-none.php`
- Create: `wp-content/themes/greenfarm/template-parts/breadcrumbs.php`
- Create: `wp-content/themes/greenfarm/tests/test-archive-templates.php`

**Interfaces:**
- Consumes: Theme setup from Task 1 and the global WordPress Loop.
- Produces: Semantic, paginated Blog, Category, and Search rendering with one H1 per view.

- [ ] **Step 1: Write failing archive-template tests**

```php
public function test_blog_card_renders_post_title_and_permalink(): void { /* create post; assert linked title */ }
public function test_empty_search_has_one_h1_and_noindex_robots(): void { /* request unmatched search; assert markup */ }
```

- [ ] **Step 2: Run archive tests to verify they fail because the templates do not exist**

Run: `phpunit --filter ArchiveTemplates`

Expected: FAIL with missing template or expected markup assertions.

- [ ] **Step 3: Implement the main-Loop archive templates and post card**

Use only `have_posts()` and `the_post()` for Blog, Category, and Search. Render `main`, archive-specific H1, optional Category description, `<article>` cards with H2 headings, native featured-image functions, author/date metadata, excerpts, `the_posts_pagination()`, and an accessible no-results template. Add noindex output only for Search.

- [ ] **Step 4: Implement the Breadcrumb template part for archive contexts**

Render the visible Blog/Category breadcrumb path and matching `BreadcrumbList` data from the current archive context; do not use a plugin or duplicate hard-coded routes.

- [ ] **Step 5: Run focused archive tests and full suite**

Run: `phpunit --filter ArchiveTemplates && phpunit`

Expected: PASS with no failures.

- [ ] **Step 6: Commit native archive templates**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add blog archive templates"
```

### Task 3: Build the Single-Article Reading Experience

**Files:**
- Create: `wp-content/themes/greenfarm/single.php`
- Create: `wp-content/themes/greenfarm/template-parts/article-header.php`
- Create: `wp-content/themes/greenfarm/template-parts/article-meta.php`
- Create: `wp-content/themes/greenfarm/template-parts/social-share.php`
- Create: `wp-content/themes/greenfarm/tests/test-single-article.php`

**Interfaces:**
- Consumes: Theme supports from Task 1 and breadcrumb renderer from Task 2.
- Produces: Semantic single-Post template with one H1, accurate metadata, share links, and native adjacent-post navigation.

- [ ] **Step 1: Write failing single-article tests**

```php
public function test_single_post_has_one_h1_and_article_landmark(): void { /* create post; assert exact markup */ }
public function test_modified_date_is_hidden_when_equal_to_publish_date(): void { /* assert no updated time */ }
```

- [ ] **Step 2: Run focused tests to verify the missing single template fails**

Run: `phpunit --filter SingleArticle`

Expected: FAIL because the article template and metadata rules are absent.

- [ ] **Step 3: Implement `single.php` and article template parts**

Output `<main><article>` with title H1, semantic author/published time, conditional updated time, featured image through `the_post_thumbnail()`, native post content, category/tag links, share controls, and native adjacent-post navigation. Use only progressive static share links; do not load external widgets.

- [ ] **Step 4: Extend breadcrumbs for Posts and multiple Categories**

Use the first deterministic assigned Category unless later editorial primary-category metadata is intentionally introduced. Keep the article canonical URL category-free.

- [ ] **Step 5: Run focused tests and full suite**

Run: `phpunit --filter SingleArticle && phpunit`

Expected: PASS with no failures.

- [ ] **Step 6: Commit the reading template**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add GreenFarm article template"
```

### Task 4: Add Constrained Related Articles and Native Share Enhancement

**Files:**
- Create: `wp-content/themes/greenfarm/template-parts/related-posts.php`
- Create: `wp-content/themes/greenfarm/assets/js/share.js`
- Modify: `wp-content/themes/greenfarm/functions.php`
- Modify: `wp-content/themes/greenfarm/single.php`
- Create: `wp-content/themes/greenfarm/tests/test-related-posts.php`

**Interfaces:**
- Consumes: Single-article template from Task 3.
- Produces: A maximum-three related-article section based on shared Categories and optional native share enhancement.

- [ ] **Step 1: Write failing related-post tests**

```php
public function test_related_posts_exclude_current_post_and_share_category(): void { /* assert IDs */ }
public function test_uncategorized_post_has_no_related_posts_section(): void { /* assert absent section */ }
```

- [ ] **Step 2: Run focused tests to verify they fail before the related query exists**

Run: `phpunit --filter RelatedPosts`

Expected: FAIL because related-post selection is absent.

- [ ] **Step 3: Implement the sole secondary `WP_Query` in `related-posts.php`**

Query at most three published Posts sharing current Post Category IDs, exclude current ID, ignore sticky posts, and reset post data. Return without markup when the current Post has no Categories or no results.

- [ ] **Step 4: Add optional Web Share/copy-link enhancement without breaking static links**

Enqueue `share.js` only on singular Posts. The script enhances an existing control when `navigator.share` or clipboard APIs exist; static share URLs remain functional with JavaScript disabled.

- [ ] **Step 5: Run focused tests and full suite**

Run: `phpunit --filter RelatedPosts && phpunit`

Expected: PASS with no failures.

- [ ] **Step 6: Commit related content and sharing**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add related articles and sharing"
```

### Task 5: Apply Responsive Editorial Styling and Motion Safety

**Files:**
- Create: `wp-content/themes/greenfarm/assets/css/blog.css`
- Modify: `wp-content/themes/greenfarm/style.css`
- Modify: `wp-content/themes/greenfarm/functions.php`
- Modify: `wp-content/themes/greenfarm/home.php`
- Modify: `wp-content/themes/greenfarm/category.php`
- Modify: `wp-content/themes/greenfarm/search.php`
- Modify: `wp-content/themes/greenfarm/single.php`
- Create: `wp-content/themes/greenfarm/tests/test-motion-contract.php`

**Interfaces:**
- Consumes: Semantic markup from Tasks 2–4.
- Produces: Mobile-first readable layouts, responsive cards/images, visible keyboard focus, and once-only low-cost section reveals.

- [ ] **Step 1: Write failing style-contract tests**

```php
public function test_blog_styles_include_reduced_motion_override(): void { /* assert CSS contract */ }
public function test_blog_templates_mark_reveal_sections_without_hiding_content_by_default(): void { /* assert data attribute */ }
```

- [ ] **Step 2: Run focused tests to verify the motion contract fails**

Run: `phpunit --filter MotionContract`

Expected: FAIL because blog styles and reveal markers are absent.

- [ ] **Step 3: Implement mobile-first blog CSS and enqueue it conditionally**

Set forest-green, lime-accent, warm-off-white tokens; readable article width, line-height, contrast, responsive card grid, visible focus treatment, and dimensions-safe image wrappers. Load CSS only for blog-related views.

- [ ] **Step 4: Add once-only reveal enhancement with reduced-motion fallback**

Use native `IntersectionObserver` to add a reveal class to marked sections. Default content is visible without JavaScript; only enhanced views transition opacity/transform. Add a `prefers-reduced-motion: reduce` override that removes transitions and transforms.

- [ ] **Step 5: Run focused tests, full suite, and static PHP validation**

Run: `phpunit --filter MotionContract && phpunit && find wp-content/themes/greenfarm -name '*.php' -print0 | xargs -0 -n1 php -l`

Expected: PASS with no PHPUnit failures or PHP syntax errors.

- [ ] **Step 6: Commit responsive visual system**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: style GreenFarm blog experience"
```

### Task 6: Verify Template Behavior in WordPress

**Files:**
- Create: `docs/greenfarm-blog-qa-checklist.md`

**Interfaces:**
- Consumes: Completed theme from Tasks 1–5.
- Produces: Repeatable manual and automated release verification evidence.

- [ ] **Step 1: Create the release QA checklist**

Cover desktop/mobile layouts, keyboard navigation, screen-reader landmarks, exact H1 count, heading sequence, noindex Search behavior, Category pagination, no-result Search, related Posts, images with/without media, reduced motion, and 404 behavior.

- [ ] **Step 2: Run the complete automated suite**

Run: `phpunit && find wp-content/themes/greenfarm -name '*.php' -print0 | xargs -0 -n1 php -l`

Expected: PASS with zero failures and zero PHP syntax errors.

- [ ] **Step 3: Perform browser checks in a local WordPress instance**

Verify `/blog/`, a Category archive, a paginated archive, a populated/empty search, and a single Post against the checklist at desktop and mobile widths.

- [ ] **Step 4: Commit QA documentation**

```bash
git add docs/greenfarm-blog-qa-checklist.md
git commit -m "docs: add GreenFarm blog QA checklist"
```
