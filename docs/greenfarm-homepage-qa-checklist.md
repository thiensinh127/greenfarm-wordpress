# GreenFarm Homepage QA Checklist

Verified on 2026-09-26 with WordPress Playground, PHP 8.2, current WordPress, and Google Chrome headless. Browser fixtures are runtime-only and are not part of the production theme.

## Content and WordPress behavior

- [x] A configured static Page resolves through `front-page.php`.
- [x] Page title, excerpt, editor content, and featured image populate the intended homepage regions.
- [x] Missing hero media emits no empty image and retains the forest gradient treatment.
- [x] Missing Product, Product Category, or Farm Story models omit their complete sections (integration test).
- [x] Populated models render four categories, four published Products, and two published Farm Stories; drafts stay excluded.
- [x] Latest Articles renders three published Posts and restores the static Page context.
- [x] Blog and Contact CTAs use the configured/canonical WordPress destinations; no fake newsletter form is present.

## Semantics and accessibility

- [x] Rendered homepage has exactly one H1 at 320px, 768px, and 1440px.
- [x] Section headings use H2 and card/process headings use H3 in a logical outline.
- [x] Grow, Harvest, Prepare, and Share use a semantic ordered list.
- [x] Chrome keyboard traversal reaches the skip link, brand, navigation, and primary hero CTA with a visible 3px focus outline.
- [x] Primary CTA measures 51px high, exceeding the 44px target.
- [x] No unverified testimonial, rating, Review schema, or Organization placeholder data is emitted.

## Responsive layout and presentation

- [x] Chrome reports `scrollWidth === viewport width` at 320px, 768px, and 1440px.
- [x] Visual screenshots confirm single-column mobile, two-column tablet cards, and expanded desktop grids.
- [x] Hero remains readable with and without its featured image.
- [x] Product, value, process, story, article, proof, and CTA sections retain consistent spacing and contrast.
- [x] Navigation wraps without horizontal clipping at 320px.

## Images, motion, and performance

- [x] Homepage stylesheet and shared motion script load only in their intended WordPress contexts.
- [x] Hero image renders with intrinsic `1600 × 900` dimensions, eager loading, high fetch priority, and responsive `sizes`.
- [x] Attachment integration test confirms WordPress-generated `srcset`; below-fold templates retain native lazy-loading behavior.
- [x] Reveal motion runs once and unobserves completed sections.
- [x] Repeated child delays rise from 0ms to a maximum of 180ms.
- [x] Reduced-motion emulation creates no observer motion class, no delayed children, and leaves zero invisible sections.
- [x] Browsers without `IntersectionObserver` keep all content visible and do not enable reveal CSS.
- [x] No slider, autoplay media, third-party script, animation library, or remote font is loaded.

## Evidence

- Automated suite: 21 PHP integration tests and 4 JavaScript behavior tests.
- Runtime viewport inspection: 320px, 768px, and 1440px in Chrome through the DevTools Protocol.
- Visual captures and runtime fixtures live in the ignored `.superpowers/sdd/2026-09-26-greenfarm-homepage/` workspace.
