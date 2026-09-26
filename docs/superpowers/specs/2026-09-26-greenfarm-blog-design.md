# GreenFarm Blog System Design

## Goal

Build a fast, accessible, search-ready native WordPress blog system for GreenFarm that uses the main WordPress query for public archives and a constrained related-post query only where the main query cannot serve the requirement.

## Scope

The system covers the Posts archive at `/blog/`, single posts at `/blog/{post-slug}/`, Category archives at `/category/{category-slug}/`, search results, pagination, article metadata, featured images, breadcrumbs, related articles, and share-link architecture.

The work belongs in a custom classic theme named `greenfarm`. It does not install a blog, SEO, pagination, breadcrumb, related-post, image, or social-sharing plugin.

## URL and Routing Decisions

- Set WordPress's Posts page to the Blog page, yielding `/blog/` and selecting `home.php`.
- Use a date-free Post permalink structure: `/blog/%postname%/`.
- Keep Category archives at `/category/{category-slug}/`.
- Use WordPress's native `/?s={query}` search URL. Search result pages emit `noindex,follow` and are excluded from XML sitemap entries.
- Use canonical, self-referencing paginated archive URLs such as `/blog/page/2/`; pagination never points every page to page one.
- Keep Tag archives noindex until an editorial decision makes an individual archive a substantive content hub.

## Template Architecture

```text
wp-content/themes/greenfarm/
├── functions.php
├── style.css
├── home.php
├── single.php
├── category.php
├── search.php
├── index.php
├── template-parts/
│   ├── article-header.php
│   ├── article-meta.php
│   ├── breadcrumbs.php
│   ├── content-none.php
│   ├── content-post-card.php
│   ├── related-posts.php
│   └── social-share.php
└── assets/
    ├── css/blog.css
    └── js/share.js
```

- `home.php` renders the native Posts-page main Loop, section heading, post cards, and native pagination.
- `single.php` renders one semantic `<article>` with the title as the sole page-level H1.
- `category.php` renders the category title as the sole page-level H1, optional category description, main Loop, and pagination.
- `search.php` renders the query-specific result title as the sole page-level H1, main search Loop, and no-results state.
- `index.php` is the safe hierarchy fallback.
- Shared visual and semantic units belong in template parts, not duplicated across templates.

## Content Rendering Rules

### Archive cards

- Each card uses an `<article>` with a linked heading, category label, excerpt, author, published date, and featured image when present.
- The archive page outputs exactly one H1; card headings are H2.
- Use `the_post_thumbnail()` or `wp_get_attachment_image()` so WordPress delivers attachment alt text, width/height, `srcset`, `sizes`, and native lazy loading.
- The first visible archive image may use eager/high-priority loading only when measured as the LCP candidate; all other archive images use native lazy loading.

### Single posts

- The article title is the single H1.
- Metadata exposes author name, machine-readable published time, and an updated time only when it materially differs from publication.
- The featured image appears after the article header with a meaningful media-library alt attribute.
- The content is output within the native post-content flow. Editorial guidelines require H2 for primary sections and H3 for subsections; no H1 is entered in article body content.
- Category and controlled Tag links appear after content. Tags remain optional and do not control the article URL.
- Next/previous post links are included only when a neighbor exists.

### Related articles

- Render at most three related Posts.
- Select shared Categories first, exclude the current Post, and respect post visibility.
- Do not query when the current post has no Categories, and do not render an empty section.
- This is the only secondary `WP_Query` in the blog system; archive, Category, and search templates use the global main query.

### Breadcrumbs

- Render `Home > Blog` on the Posts archive.
- Render `Home > Blog > {primary category} > {article title}` on single Posts.
- Render `Home > Blog > {category}` on Category archives.
- If a Post has multiple Categories, choose one deterministic primary category for its breadcrumb; the URL never changes by category.
- Breadcrumb visual markup and `BreadcrumbList` JSON-LD convey the same path.

### Sharing

- Provide progressive-enhancement share controls: static links to supported sharing endpoints and a copy-link control.
- Use Web Share API only when available; otherwise static links work without JavaScript.
- Do not load third-party share widgets, tracking scripts, or share-count services.

## Accessibility and Performance Requirements

- Visual direction uses real farm photography, forest green foundations, restrained lime accents, warm off-white content surfaces, and premium editorial spacing; it must not resemble a generic WordPress theme.
- Semantic landmarks: header, navigation, main, article, aside where applicable, and footer.
- All controls have visible focus styles, accessible names, keyboard operation, and minimum usable touch targets.
- Reading column: roughly 65–75 characters per line at desktop with comfortable line height and contrast.
- Do not use sliders, autoplay video, layout-shifting embeds, or render-blocking third-party widgets.
- Section reveals use only `opacity` and `transform`, run once when entering the viewport, and are disabled or materially reduced for `prefers-reduced-motion: reduce`. Hero parallax is excluded until measured performance demonstrates headroom.
- Define image dimensions and rely on WordPress responsive-image output to reduce CLS and payload.
- Load only page-specific CSS; defer non-critical share enhancement JavaScript.

## SEO Requirements

- Each template has exactly one appropriate page-level H1.
- Generate unique document titles and descriptions through a small theme integration or later dedicated SEO layer; preserve canonical URLs.
- Published posts, useful Category archives, Blog archive, and published Farm/brand pages are indexable.
- Search, 404, Tag archives initially, empty archives, attachment pages, and duplicate filter URLs are noindex.
- Include only canonical indexable URLs in the WordPress XML sitemap.
- Use `Article` and `BreadcrumbList` schema only when markup matches visible content.
- Internal links must connect support articles to their pillar, sibling articles where helpful, category hubs, relevant Products, and Farm Stories.

## Testing and Acceptance Criteria

- Blog, Category, Search, and Single templates resolve through the expected template hierarchy.
- Archive/category/search pages paginate correctly and preserve their query context.
- Single Posts render title as one H1, machine-readable dates, author, featured image, and article semantics.
- Related articles exclude the current post, share a Category, and do not render for uncategorized Posts.
- Search and other non-indexable utility views emit the intended robots directive.
- Images emit width, height, alt, `srcset`, and `sizes` attributes when a WordPress attachment is present.
- Keyboard, screen-reader landmark, heading-outline, responsive layout, and performance checks pass in a real WordPress environment.

## Non-goals

- No WooCommerce integration.
- No custom page builder, custom REST API, headless frontend, external search service, social-share analytics, or generic related-post plugin.
- No manual query for the main archive, Category archive, or search result listing.
