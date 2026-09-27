# GreenFarm Product and Farm Story Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add plugin-owned Product and Farm Story content models plus complete, accessible public archives and single templates that integrate with the existing GreenFarm Homepage.

**Architecture:** A dependency-free `greenfarm-core` plugin owns CPTs, taxonomy, registered Product metadata, secure native meta boxes, and the Media Library gallery interaction. The GreenFarm theme owns shared cards, native-Loop archives, single templates, breadcrumbs, responsive presentation, and conditional assets; separate Playground suites verify the plugin-disabled fallback and the plugin-enabled experience.

**Tech Stack:** WordPress 6.6+, PHP 8.2+, WordPress metadata and Media APIs, classic PHP theme templates, vanilla JavaScript/CSS, WordPress Playground, Node test runner.

**Spec:** `docs/superpowers/specs/2026-09-26-greenfarm-content-models-design.md`

## Global Constraints

- Keep all Product and Farm Story content registration in `wp-content/plugins/greenfarm-core`; the theme must not register content types.
- Use native WordPress fields, post meta, meta boxes, Media Library, main queries, pagination, REST registration, and attachment rendering.
- Do not add ACF, WooCommerce, a page builder, a frontend framework, or an animation/gallery dependency.
- Product URLs are `/products/{product-slug}/`; Product Category URLs are `/products/category/{term-slug}/`; Farm Story URLs are `/farm-stories/{story-slug}/`.
- Products remain informational: do not add price, cart, stock quantity, Product/Offer/Review schema, or placeholder commercial data.
- Keep exactly one H1 per public view; cards use H2 on archives and H3 on the Homepage.
- Preserve the Homepage limits of four Products and two Farm Stories and always restore its static Page context.
- Use capability checks, nonces, allowlists, sanitization, output escaping, and positive attachment IDs at every data boundary.
- Invalid or missing optional metadata/images must be omitted without broken markup.
- Preserve all existing Blog, Search, 404, Homepage, motion, robots, and PHP-lint behavior.

## Review Focus

- A Product save with an invalid nonce, subscriber user, autosave, or revision must preserve every existing Product field.
- A request with valid Product details but no submitted gallery field must preserve the existing gallery rather than erase it.
- Invalid availability values, negative IDs, deleted attachments, and non-image attachments must never render or enter the public data contract.
- Disabling `greenfarm-core` must leave the Blog/Homepage operational and remove optional business sections without fatal calls to plugin functions.
- Native archive queries, pagination globals, and the Homepage Page global must remain intact after shared cards and secondary queries render.

---

### Task 1: Plugin Bootstrap and Content Types

**Files:**
- Create: `wp-content/plugins/greenfarm-core/greenfarm-core.php`
- Create: `wp-content/plugins/greenfarm-core/includes/content-types.php`
- Create: `wp-content/plugins/greenfarm-core/tests/run.php`
- Modify: `package.json`

**Interfaces:**
- Consumes: WordPress plugin, CPT, taxonomy, rewrite, REST, and activation APIs.
- Produces: `greenfarm_core_register_content_types(): void`, activation/deactivation callbacks, public `greenfarm_product`, `farm_story`, and `product_category` models.

- [ ] **Step 1: Write failing plugin registration tests**

Create a Playground runner that loads the plugin and asserts both CPTs and the taxonomy exist; checks `public`, `show_in_rest`, `exclude_from_search`, archive/rewrite slugs, hierarchical Product Categories, admin columns, and exact editor supports; and verifies activation/deactivation callbacks are registered without flushing rewrites during normal `init`.

- [ ] **Step 2: Add the plugin test command and prove RED**

Add `test:plugin` mounting `greenfarm-core`, and include it in `npm test`. Run `npm run test:plugin`.

Expected: FAIL because the plugin bootstrap and registration functions do not exist.

- [ ] **Step 3: Implement the minimal plugin bootstrap and registrations**

Define the plugin headers/version, require `includes/content-types.php`, register models on `init`, and implement activation/deactivation callbacks that register first and then flush rewrite rules. Use `greenfarm_product`, `farm_story`, `product_category`, `products`, `products/category`, and `farm-stories` exactly as specified.

- [ ] **Step 4: Run registration and regression verification**

Run: `npm run test:plugin && npm run test:php`

Expected: Plugin registration assertions pass; the plugin-disabled theme suite still passes.

- [ ] **Step 5: Commit**

```bash
git add package.json wp-content/plugins/greenfarm-core
git commit -m "feat: register GreenFarm content models"
```

### Task 2: Product Metadata and Secure Native Meta Boxes

**Files:**
- Create: `wp-content/plugins/greenfarm-core/includes/product-meta.php`
- Modify: `wp-content/plugins/greenfarm-core/greenfarm-core.php`
- Modify: `wp-content/plugins/greenfarm-core/tests/run.php`

**Interfaces:**
- Consumes: `greenfarm_product`, WordPress registered-meta, nonce, capability, revision, autosave, and post-meta APIs.
- Produces: `greenfarm_core_register_product_meta(): void`, `greenfarm_core_get_availability_options(): array`, `greenfarm_core_get_availability_label(string): string`, `greenfarm_core_sanitize_availability($value): string`, `greenfarm_core_sanitize_gallery_ids($value): array`, meta-box renderers, and `greenfarm_core_save_product_meta(int): void`.

- [ ] **Step 1: Write failing metadata-contract tests**

Assert all six keys register only for `greenfarm_product`, are single-value, REST-visible with exact string/array schemas, and use the expected sanitizers. Test availability accepts only `available`, `limited`, `seasonal`, `unavailable`, or empty; gallery sanitization keeps ordered, unique, positive integers and drops all other values.

- [ ] **Step 2: Write failing secure-save tests**

Create a real Product with existing meta. Assert valid admin nonce/capability input sanitizes and saves all details; an omitted details field is deleted; an omitted gallery field is preserved; invalid nonce, subscriber capability, autosave revision, and ordinary revision calls leave all existing values unchanged.

- [ ] **Step 3: Run the content suite and prove RED**

Run: `npm run test:plugin`

Expected: FAIL because registered Product meta, sanitizers, meta boxes, and the save handler are absent.

- [ ] **Step 4: Implement registered meta and Product details/gallery boxes**

Register every key on `init` with explicit REST schemas and `auth_callback`. Add the two Product-only meta boxes, one save nonce, allowlisted availability labels, escaped current values, the gallery hidden field, and early returns for invalid save contexts. Update gallery only when its field exists in the submitted request.

- [ ] **Step 5: Run full PHP verification**

Run: `npm run test:plugin && npm run test:php`

Expected: All metadata/security cases and existing theme behavior pass.

- [ ] **Step 6: Commit**

```bash
git add wp-content/plugins/greenfarm-core
git commit -m "feat: add secure Product metadata"
```

### Task 3: Native Product Gallery Admin Interaction

**Files:**
- Create: `wp-content/plugins/greenfarm-core/assets/js/product-gallery.js`
- Create: `wp-content/plugins/greenfarm-core/assets/css/product-admin.css`
- Create: `wp-content/plugins/greenfarm-core/tests/product-gallery.test.js`
- Modify: `wp-content/plugins/greenfarm-core/includes/product-meta.php`
- Modify: `package.json`

**Interfaces:**
- Consumes: Product edit-screen context, meta-box `data-*` hooks, `wp_enqueue_media()`, and `wp.media` selection APIs.
- Produces: Product-only `greenfarm-core-product-gallery` admin asset handles and ordered attachment-ID synchronization.

- [ ] **Step 1: Write failing enqueue and gallery behavior tests**

In PHP, assert media, script, and admin CSS enqueue only for `post.php`/`post-new.php` when the post type is `greenfarm_product`. In Node, execute the real script against small DOM/Media Library mocks and assert selecting images updates ordered IDs/previews, removing one updates both, reopening preselects stored IDs, and missing hooks exit without error.

- [ ] **Step 2: Include plugin JavaScript tests and prove RED**

Extend `test:js` to include `wp-content/plugins/greenfarm-core/tests/*.test.js`. Run `npm run test:js`.

Expected: FAIL because the gallery script does not exist.

- [ ] **Step 3: Implement the minimal progressive enhancement**

Enqueue native media plus plugin assets only on Product editor screens. Implement one Media Library frame, ordered comma-separated IDs, escaped thumbnail previews, Add/Change and Remove controls, and a no-op path when required DOM or `wp.media` is unavailable.

- [ ] **Step 4: Run JavaScript and PHP verification**

Run: `npm run test:js && npm run test:plugin`

Expected: Admin interaction and Product save-preservation tests pass.

- [ ] **Step 5: Commit**

```bash
git add package.json wp-content/plugins/greenfarm-core
git commit -m "feat: add native Product gallery editor"
```

### Task 4: Shared Cards and Homepage Integration

**Files:**
- Create: `wp-content/themes/greenfarm/template-parts/content-product-card.php`
- Create: `wp-content/themes/greenfarm/template-parts/content-story-card.php`
- Create: `wp-content/themes/greenfarm/tests/content-models.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/products.php`
- Modify: `wp-content/themes/greenfarm/template-parts/home/stories.php`
- Modify: `package.json`

**Interfaces:**
- Consumes: plugin availability-label API, native post globals, attachment rendering, and `get_template_part(..., $args)`.
- Produces: Product and Farm Story cards accepting `heading_level` (`h2` or `h3`), `card_class`, and `image_size`, defaulting safely for archive use.

The new `test:content` command mounts both plugin and theme, runs `tests/content-models.php`, and is added to the aggregate `npm test` command alongside `test:plugin`, `test:php`, and `test:js`.

- [ ] **Step 1: Write failing shared-card integration tests**

Load plugin and theme, create published/draft Products and Farm Stories, and assert Product cards render only allowlisted availability labels, optional excerpts/images, canonical links, and requested heading levels; Story cards do the same without Product metadata. Assert invalid availability and missing images produce no empty badge/image markup.

- [ ] **Step 2: Write failing Homepage-refactor assertions**

Render the static Homepage with populated models and assert it still limits four Products/two Stories, excludes drafts, uses H3 cards, reads `greenfarm_availability`, and restores the Page global before the next section. Keep the existing plugin-disabled omission test unchanged.

- [ ] **Step 3: Run both PHP suites and prove RED**

Run: `npm run test:content && npm run test:php`

Expected: FAIL because shared cards and the canonical availability meta integration do not exist.

- [ ] **Step 4: Implement cards and refactor Homepage loops**

Move duplicated card markup into the two template parts, allow only H2/H3 arguments, and keep secondary queries in the Homepage partials. Guard the plugin label helper before use so inactive-plugin requests remain safe.

- [ ] **Step 5: Run all automated tests**

Run: `npm test`

Expected: Plugin-enabled shared-card tests and all existing Blog/Homepage tests pass.

- [ ] **Step 6: Commit**

```bash
git add package.json wp-content/themes/greenfarm
git commit -m "refactor: share Product and Story cards"
```

### Task 5: Native Product and Farm Story Archives

**Files:**
- Create: `wp-content/themes/greenfarm/archive-greenfarm_product.php`
- Create: `wp-content/themes/greenfarm/taxonomy-product_category.php`
- Create: `wp-content/themes/greenfarm/archive-farm_story.php`
- Modify: `wp-content/themes/greenfarm/template-parts/breadcrumbs.php`
- Modify: `wp-content/themes/greenfarm/tests/content-models.php`

**Interfaces:**
- Consumes: native main Loop, pagination globals, shared H2 cards, term descriptions, and existing empty-state/robots behavior.
- Produces: semantic archive routes plus Product/Farm Story/Product Category breadcrumb contexts and matching `BreadcrumbList` JSON-LD.

- [ ] **Step 1: Write failing archive and breadcrumb tests**

Render real Product, Product Category, and Farm Story main queries. Assert each archive has exactly one contextual H1, published-only H2 cards, canonical links, native pagination output when multiple pages exist, and an accessible empty state. Assert Product Category description is escaped with `wp_kses_post`.

- [ ] **Step 2: Pin visible/schema breadcrumb parity and robots behavior**

Assert Home → Products → current category, Home → Products for Product archive, and Home → Farm Stories for Story archive appear both visibly and in JSON-LD. Assert empty archives emit `noindex,follow` while populated public archives remain indexable.

- [ ] **Step 3: Run the content suite and prove RED**

Run: `npm run test:content`

Expected: FAIL because specialized archive templates and CPT breadcrumb branches are absent.

- [ ] **Step 4: Implement native archive templates and breadcrumb contexts**

Use the unmodified main Loop, shared cards at H2, `the_posts_pagination()`, existing empty-state partial, and archive-specific heading copy. Extend breadcrumbs without changing Blog category/single behavior.

- [ ] **Step 5: Run PHP regression verification**

Run: `npm run test:content && npm run test:php`

Expected: All archive, robots, breadcrumb, Blog, Search, and Homepage assertions pass.

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add Product and Farm Story archives"
```

### Task 6: Product and Farm Story Single Experiences

**Files:**
- Create: `wp-content/themes/greenfarm/single-greenfarm_product.php`
- Create: `wp-content/themes/greenfarm/single-farm_story.php`
- Create: `wp-content/themes/greenfarm/template-parts/product-gallery.php`
- Modify: `wp-content/themes/greenfarm/template-parts/breadcrumbs.php`
- Modify: `wp-content/themes/greenfarm/tests/content-models.php`

**Interfaces:**
- Consumes: registered Product meta, availability labels, valid WordPress attachment IDs, native author/date/content APIs, and post navigation.
- Produces: one-H1 Product/Farm Story pages, optional Product facts/storage/gallery, Story metadata, and single-view breadcrumb JSON-LD.

- [ ] **Step 1: Write failing Product-single tests**

Assert one H1, short/full descriptions, featured image, only populated semantic fact pairs, escaped storage instructions, category links, availability allowlist behavior, and Product-only adjacent navigation. Create valid image, deleted, non-image, negative, and duplicate gallery IDs; assert only ordered valid images render lazily with intrinsic dimensions, `srcset`, and `sizes`.

- [ ] **Step 2: Write failing Farm Story-single tests**

Assert one H1, semantic article, author, published date, updated date only after material modification, featured image/content, and Farm Story-only adjacent navigation. Assert Blog category/tag/related-post UI and Article/Product/Review schema are absent.

- [ ] **Step 3: Pin single breadcrumb parity**

Assert Product single renders Home → Products → primary Product Category when present → Product; Story single renders Home → Farm Stories → Story; visible and JSON-LD orders match.

- [ ] **Step 4: Run the content suite and prove RED**

Run: `npm run test:content`

Expected: FAIL because specialized single templates and Product gallery rendering are absent.

- [ ] **Step 5: Implement both singles and the gallery partial**

Use native Loops once, attachment APIs, strict optional-field guards, semantic `<dl>` Product facts, `<article>` story markup, and post-type-constrained navigation. Filter gallery IDs at render time by positive ID, attachment existence, and image MIME/type.

- [ ] **Step 6: Run all automated tests**

Run: `npm test`

Expected: Product/Story single tests and all previous suites pass.

- [ ] **Step 7: Commit**

```bash
git add wp-content/themes/greenfarm
git commit -m "feat: add Product and Farm Story details"
```

### Task 7: Responsive Styling, Full Linting, and Runtime QA

**Files:**
- Create: `wp-content/themes/greenfarm/assets/css/content-models.css`
- Create: `wp-content/plugins/greenfarm-core/tests/lint.php`
- Create: `docs/greenfarm-content-models-qa-checklist.md`
- Modify: `wp-content/themes/greenfarm/functions.php`
- Modify: `package.json`

**Interfaces:**
- Consumes: all archive/single/card class names, existing design tokens and motion script, theme/plugin PHP trees.
- Produces: conditionally loaded public styles, complete theme/plugin parse checks, and documented release evidence.

- [ ] **Step 1: Write failing conditional-asset tests**

Assert `greenfarm-content-models` CSS and shared motion enqueue on Product/Farm Story/taxonomy views but not unrelated Pages. Confirm Homepage retains its own CSS and shared cards need no duplicate public dependency.

- [ ] **Step 2: Add plugin lint coverage and prove RED**

Split `lint:php` into theme/plugin parse commands and an aggregate command. Run `npm run lint:php`.

Expected: FAIL until the plugin lint runner exists.

- [ ] **Step 3: Implement mobile-first public styling and conditional enqueue**

Style archive grids, category descriptions, Product hero/facts/storage/gallery, Story reading layout, empty states, and desktop expansion using existing tokens. Start at 320px, preserve 44px controls, load only on relevant public routes, use opacity/transform motion hooks, and include reduced-motion overrides through the existing system.

- [ ] **Step 4: Implement complete plugin/theme lint commands**

Parse every PHP file under the theme and plugin with the Playground PHP runtime; retain separate file counts and a nonzero exit on the first parse error.

- [ ] **Step 5: Run final automated verification**

Run: `npm test && npm run lint:php && node --check wp-content/plugins/greenfarm-core/assets/js/product-gallery.js && node --check wp-content/themes/greenfarm/assets/js/motion.js && git diff --check`

Expected: All plugin, content-model, existing theme, JavaScript, syntax, and whitespace checks pass.

- [ ] **Step 6: Run Chrome/Playground runtime QA and document evidence**

Verify plugin activation/rewrite routes, Product admin saves/gallery actions, populated and empty archives, Product/Story singles, plugin-disabled Homepage, keyboard focus, reduced motion, image attributes, and no horizontal overflow at 320px, 768px, and 1440px. Record results in `docs/greenfarm-content-models-qa-checklist.md` without committing QA-only fixtures.

- [ ] **Step 7: Review, push, and create the Pull Request**

Review the complete `origin/main..HEAD` diff against the spec, fix all blocking findings, commit the final CSS/QA changes as `feat: style and verify GreenFarm content models`, push `feature/greenfarm-content-models`, and create a Pull Request targeting `main`. Do not merge it; report the PR URL and ask the repository owner to merge on GitHub.
