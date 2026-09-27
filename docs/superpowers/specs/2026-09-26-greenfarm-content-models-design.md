# GreenFarm Product and Farm Story Content Models

## Goal

Add durable, native WordPress content models for informational Products and Farm Stories, then expose complete public archive and single experiences without introducing ACF, WooCommerce, or another production dependency. The result must preserve the existing Blog and Homepage behavior and leave a clear migration path to WooCommerce when commerce requirements become real.

## Scope

This phase includes:

- A `greenfarm-core` plugin that owns the Product and Farm Story content models.
- Native Product meta boxes, validation, media-gallery selection, and REST-visible metadata.
- Product Category, Product, and Farm Story archives and single templates in the GreenFarm theme.
- Reusable Product and Farm Story cards shared by archives and the Homepage.
- Breadcrumb, responsive-image, accessibility, SEO, and automated-test coverage.

This phase does not include prices, inventory quantities, checkout, payments, customer accounts, Product schema, ratings, reviews, an email platform, or WooCommerce.

## Ownership Boundary

Content definitions belong in `wp-content/plugins/greenfarm-core` so Product and Farm Story records remain available when the active theme changes. The plugin must contain no public-page presentation beyond WordPress admin fields.

The GreenFarm theme owns templates, cards, layout, typography, responsive images, breadcrumbs, and motion. Theme templates must fail safely when the plugin is inactive: Product and Farm Story routes then cease to exist, while the Homepage already omits their optional sections.

## Content Types

### Product

Register `greenfarm_product` with:

- Public archive at `/products/`.
- Public single URLs at `/products/{product-slug}/`.
- REST API exposure.
- Native title, editor, excerpt, featured image, revisions, and author support.
- Public search inclusion.
- Menu icon and labels appropriate to farm products.

`/products/` is the CPT archive and must not also be assigned to a static WordPress Page.

### Farm Story

Register `farm_story` with:

- Public archive at `/farm-stories/`.
- Public single URLs at `/farm-stories/{story-slug}/`.
- REST API exposure.
- Native title, editor, excerpt, featured image, revisions, and author support.
- Public search inclusion.

Farm Stories receive no custom taxonomy or custom fields in this phase. Editors use the title, excerpt, content, featured image, author, and publication dates already provided by WordPress.

### Product Category

Register hierarchical taxonomy `product_category` for `greenfarm_product` with:

- Public archives at `/products/category/{term-slug}/`.
- REST API and admin-column exposure.
- Native term name, slug, parent, and description fields.

Only Product Category receives a public taxonomy archive. Origin, farming method, harvest season, and availability remain structured Product metadata so the site does not create thin filter archives.

## Product Data Contract

Core WordPress fields provide:

| Content | WordPress source |
| --- | --- |
| Name and slug | Post title and post name |
| Short description | Excerpt |
| Full description | Editor content |
| Featured image | Post thumbnail |
| Category | `product_category` terms |

Register these single-value post-meta keys for `greenfarm_product`:

| Meta key | Type | Admin control | Sanitization |
| --- | --- | --- | --- |
| `greenfarm_origin` | string | Text input | `sanitize_text_field` |
| `greenfarm_farming_method` | string | Text input | `sanitize_text_field` |
| `greenfarm_harvest_season` | string | Text input | `sanitize_text_field` |
| `greenfarm_storage_instructions` | string | Textarea | `sanitize_textarea_field` |
| `greenfarm_availability` | string enum | Select | Allowlist validation |
| `greenfarm_gallery_ids` | integer array | Media Library gallery selector | Positive attachment IDs only |

Availability values and labels are:

- `available` — Available now
- `limited` — Limited availability
- `seasonal` — Seasonal
- `unavailable` — Currently unavailable

An empty availability value is valid and produces no badge. Origin, farming method, harvest season, storage instructions, and gallery may also be empty; templates omit their label and container rather than printing placeholders.

All Product meta is exposed through the REST API with explicit schemas and an authorization callback based on the ability to edit the post. Gallery order is the stored attachment-ID order.

## Native Admin Experience

The Product editor contains one details meta box and one gallery meta box.

The details box groups the five textual/availability fields with explicit labels and short descriptions. It uses a single nonce tied to Product-meta saving. The save routine returns without changing data during autosaves, revisions, invalid nonces, or insufficient permissions. Valid saves sanitize each submitted value; omitted valid fields are deleted so stale metadata does not remain.

The gallery box uses `wp_enqueue_media()` only on Product edit screens. A small admin script opens the native Media Library in multi-select mode, stores ordered attachment IDs in a hidden field, renders thumbnail previews with Media Library alt text, and supports removing selections. JavaScript is an enhancement: an existing stored gallery remains intact if the script does not load and the hidden field is not submitted.

The plugin flushes rewrite rules only on activation and deactivation, never on ordinary requests.

## Public Templates

### Product Archive

`archive-greenfarm_product.php` uses the native main query and pagination. It renders one H1, a short informational introduction, Product cards, and the existing empty-state component when needed. No secondary archive query replaces the WordPress Loop.

### Product Category Archive

`taxonomy-product_category.php` uses the native taxonomy query, term name, optional term description, Product cards, and native pagination. Breadcrumbs include Home → Products → current category.

### Product Detail

`single-greenfarm_product.php` renders:

- Breadcrumbs and the Product title as the sole page-level H1.
- Availability badge only when a valid value exists.
- Short description and featured image.
- A definition-style facts section containing only populated Origin, Farming method, and Harvest season values.
- Full editor content.
- Storage instructions when populated.
- Gallery attachments that are valid images, using WordPress attachment APIs and native responsive sources.
- Product Category links and adjacent Product navigation.

The featured image remains the primary single-page media. Gallery images load lazily and reserve intrinsic space. Invalid, deleted, or non-image gallery attachment IDs are skipped.

### Farm Story Archive

`archive-farm_story.php` uses the native main query, Farm Story cards, and native pagination. The archive title is its sole H1.

### Farm Story Detail

`single-farm_story.php` renders breadcrumbs, one H1, author, published date, a materially different updated date, featured image, editor content, and adjacent Farm Story navigation. It does not reuse Blog categories, tags, related-post logic, or Article schema unless a later SEO phase defines accurate Farm Story schema.

## Shared Components and Homepage Integration

Create focused Product and Farm Story card template parts. Archives and Homepage use the same semantic cards, while template arguments control context-specific heading levels. Archive cards use H2; Homepage cards use H3.

The existing Homepage query limits remain four Products and two Farm Stories. Refactoring must preserve draft exclusion, canonical links, availability output, and restoration of the global Homepage Page context.

## Breadcrumbs and SEO

Extend the existing breadcrumb component to recognize Product archives, Product Category archives, Product singles, Farm Story archives, and Farm Story singles. Visible breadcrumbs and `BreadcrumbList` JSON-LD must contain the same ordered items.

WordPress core continues to provide title-tag support, canonical handling, XML sitemaps, REST routes, and archive pagination. Products and Farm Stories are indexable and searchable when published. Empty archives remain `noindex,follow` through the theme's existing thin-archive rule.

Do not emit Product schema while Products are informational and have no real offer, price, or inventory data. Do not emit placeholder Organization, Review, or rating schema.

## Accessibility and Responsive Behavior

- Every public view has exactly one H1 and a logical H2/H3 outline.
- Cards remain complete without featured images and never output empty image elements.
- Linked card thumbnails hidden from assistive technology have an equivalent visible title link.
- Product facts use semantic terms and descriptions.
- Gallery images use Media Library alt text; empty alt remains valid for decorative images.
- All controls and links retain visible keyboard focus and 44px primary interaction targets.
- Layout begins as one column at 320px and expands without horizontal overflow.
- Motion uses the existing one-shot reveal system and respects reduced-motion settings.

## Error and Empty States

- Missing plugin: Homepage business sections disappear and unrelated theme routes continue working.
- Empty CPT or taxonomy archive: show the existing accessible empty state and emit `noindex,follow`.
- Missing Product metadata: omit the specific field without placeholder copy.
- Invalid availability: treat as empty on save and render.
- Invalid gallery attachment: skip it without breaking the gallery.
- Product without featured image or gallery: preserve a coherent text-first layout.
- Secondary Homepage query: always restore the original Page context.

## WooCommerce Migration Boundary

When commerce is approved, migrate rather than run two competing Product systems indefinitely:

- Title, slug, excerpt, content, status, author, and featured image map to WooCommerce Products.
- Product Categories map to `product_cat`.
- `greenfarm_gallery_ids` maps to WooCommerce gallery attachment IDs while preserving order.
- Origin, farming method, harvest season, and storage instructions map to product attributes or retained custom meta after merchandising requirements are defined.
- Availability is translated only after real stock and backorder rules exist; it must not be mistaken for inventory quantity.

The migration phase will add prices, tax status, inventory, offers, Product schema, checkout, and redirects from any URL that changes.

## Testing and Acceptance Criteria

Automated WordPress Playground tests must prove:

- The plugin registers both CPTs, the taxonomy, expected rewrites, REST support, and native editor supports.
- Product metadata sanitizes valid input and rejects invalid availability/gallery values.
- Autosave, revision, invalid nonce, and insufficient-capability paths do not overwrite saved Product metadata.
- Product, Product Category, and Farm Story archives use published main-query content, native pagination, one H1, and correct card heading levels.
- Product and Farm Story singles render exactly one H1, canonical links, optional fields only when populated, and matching visible/schema breadcrumbs.
- Gallery rendering skips invalid IDs and uses WordPress responsive image markup for valid attachments.
- Homepage shared cards retain their four-Product/two-Story limits and restore the static Page context.
- Empty archives are noindexed and no-image/empty-meta content remains coherent.
- Existing Blog, Search, 404, Homepage, motion, and PHP syntax tests remain green.

Runtime QA must cover 320px, 768px, and 1440px widths, keyboard navigation, reduced motion, populated and empty content, missing images, and horizontal overflow.

The phase is complete when tests pass, runtime QA is documented, the feature branch is pushed, and a Pull Request against `main` is ready for the repository owner to merge.
