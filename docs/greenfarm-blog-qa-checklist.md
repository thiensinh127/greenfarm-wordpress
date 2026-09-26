# GreenFarm Blog QA Checklist

Homepage-specific checks are tracked separately in [GreenFarm Homepage QA](greenfarm-homepage-qa-checklist.md).

## Content and routing

- [ ] `/blog/` uses the native Posts page query and displays paginated cards.
- [ ] `/category/{slug}/` displays the correct term name, description, Posts, and pagination.
- [ ] `/?s={query}` displays populated and empty states and emits `noindex`.
- [ ] `/blog/{post-slug}/` displays the article, metadata, featured image, taxonomies, sharing, related Posts, and adjacent navigation.
- [ ] Missing URLs return HTTP 404, display recovery links, and emit `noindex`.

## Semantics and accessibility

- [ ] Every view has exactly one page-level H1.
- [ ] Article body headings begin at H2 and descend without skipped levels.
- [ ] Header, navigation, main, article, aside, and footer landmarks have useful labels.
- [ ] All links and controls work by keyboard and show visible focus.
- [ ] Featured images use useful Media Library alt text; decorative linked thumbnails are hidden from assistive technology.
- [ ] Text and controls meet WCAG AA contrast and touch-target expectations.

## Responsive and performance

- [ ] Layout works at 320px, 768px, 1024px, and 1440px without horizontal scrolling.
- [ ] Images include intrinsic dimensions, `srcset`, and `sizes` when generated from WordPress attachments.
- [ ] No-image cards collapse cleanly without empty placeholders.
- [ ] Section reveal animation runs once using opacity/transform only.
- [ ] `prefers-reduced-motion: reduce` leaves all content immediately visible.
- [ ] No slider, autoplay media, third-party share widget, or render-blocking remote font is loaded.

## SEO and internal linking

- [ ] Blog, Category, Search, and Single views use the intended title and heading context.
- [ ] Visible breadcrumbs match `BreadcrumbList` structured data.
- [ ] Single Posts output accurate `Article` structured data.
- [ ] Search and 404 are noindex; canonical indexable views remain crawlable.
- [ ] Related Posts exclude the current Post and require a shared Category.
- [ ] Pillar/supporting links, Category links, and contextual Product/Farm Story links are editorially reviewed.
