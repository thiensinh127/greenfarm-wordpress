# GreenFarm Content Models QA Checklist

QA date: 2026-09-27  
Environment: WordPress Playground CLI 3.1.55, WordPress latest, PHP 8.3 runtime, GreenFarm theme 1.0.0, GreenFarm Core 1.0.0.

## Automated verification

- [x] Theme integration suite: Blog, Search, Homepage, SEO, accessibility, and query-context regressions.
- [x] Plugin suite: registrations, rewrite priority, REST metadata, sanitization, secure saves, meta boxes, and conditional admin assets.
- [x] Theme/plugin integration suite: shared cards, conditional public assets, archives, empty states, breadcrumbs, Product single/gallery, and Farm Story single.
- [x] JavaScript suite: Product gallery selection/removal/order and scroll reveal/reduced-motion behavior.
- [x] Theme and plugin PHP trees parse without syntax errors.
- [x] Both JavaScript production files pass `node --check`.
- [x] `git diff --check` reports no whitespace errors.

Final commands:

```text
npm test
npm run lint:php
node --check wp-content/plugins/greenfarm-core/assets/js/product-gallery.js
node --check wp-content/themes/greenfarm/assets/js/motion.js
git diff --check
```

## WordPress Playground runtime evidence

A temporary Playground site was created with the plugin and theme mounted from this branch. QA fixtures were generated outside the tracked source tree and were not committed.

- [x] GreenFarm Core activates and the theme activates without a PHP error.
- [x] Pretty permalinks resolve `/products/`, `/products/page/2/`, `/products/{slug}/`, `/products/category/{term}/`, `/farm-stories/`, and `/farm-stories/{slug}/` with HTTP 200 responses.
- [x] Product Category rewrite rules precede Product attachment rules; populated and empty category routes resolve correctly.
- [x] Product archive uses the native 10-post page size and renders the remaining two QA Products on page 2.
- [x] Empty Product Category renders the accessible recovery state and `noindex,follow`.
- [x] Every tested public business-content route has exactly one H1 and conditionally loads `content-models.css` plus shared motion.
- [x] Homepage loads its dedicated stylesheet without the content-model stylesheet.
- [x] Product single renders saved facts, controlled availability, storage instructions, two ordered gallery images, intrinsic dimensions, and lazy loading.
- [x] Farm Story single renders semantic article markup, author/date metadata, and no unsupported Article/Product schema.
- [x] Runtime HTTP/DOM checker result: 49 checks, 0 failures.

## Product admin and REST

- [x] Product edit screen renders the GreenFarm details and gallery meta boxes.
- [x] Gallery UI renders ordered previews, per-image Remove controls, Choose images, and Clear gallery.
- [x] `product-gallery.js` loads only on the Product editor.
- [x] The raw WordPress Custom Fields meta box is hidden.
- [x] Authenticated REST update changed Origin to `North Field QA` and gallery order from `6,7` to `7,6`; the REST response returned both registered meta values and the public Product page reflected them.
- [x] Plugin deactivation leaves the Homepage functional with one H1 and omits optional Product/Story sections; the plugin was reactivated after the check.

## Responsive, accessibility, and motion

- [x] Mobile-first base rules contain no fixed page/card widths; media and embedded content use `max-width: 100%`, reading columns use `min-width: 0`, and long headings/links can wrap.
- [x] 320px structural audit: single-column grids, wrapping breadcrumbs/forms/navigation, fluid headings, and bounded media prevent intentional horizontal overflow.
- [x] 768px structural audit: the 40rem breakpoint expands archive/gallery layouts to two columns with fluid gutters.
- [x] 1440px structural audit: the 64rem breakpoint expands Product archives/galleries and constrains content to the shared 75rem container.
- [x] Pagination, search controls, and adjacent-post links retain a minimum 44px target height.
- [x] Focus uses the theme-wide visible `:focus-visible` outline.
- [x] Scroll reveal uses opacity/transform only, reveals each section once, and staggers child cards with a capped delay.
- [x] Reduced-motion behavior is covered in JavaScript tests and CSS removes reveal/image transitions under `prefers-reduced-motion: reduce`.

## Visual-browser limitation

The native Chrome and in-app browser surfaces were unavailable to the computer-use bridge during this run (`Browser is not available` / `cgWindowNotFound`). Exact viewport screenshots and a manual keyboard walkthrough could not be captured. The three target widths above were verified by responsive-rule and DOM audits, while behavior was exercised in the live Playground server. A final human visual sweep at 320px, 768px, and 1440px is recommended before merging.
