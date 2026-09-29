# GreenFarm Core Pages Design

**Status:** Approved conversational design, ready for implementation planning
**Date:** 2026-09-28
**Scope:** Native WordPress About and Contact pages for the GreenFarm theme

## 1. Objective

Add two durable, editor-managed core pages that complete the current public-site navigation:

- About at `/about/`
- Contact at `/contact/`

Both pages will use Vietnamese-first content while preserving the project's existing English URL structure. They must feel consistent with the GreenFarm homepage, product, farm-story, and blog experiences without introducing unnecessary content models, plugins, or configuration.

## 2. Goals

- Give editors full control of each page's title, summary, featured image, and main content through native WordPress Page fields and blocks.
- Provide dedicated About and Contact page layouts with a clear information hierarchy.
- Keep the theme ready for a future site-wide multilingual solution without implementing multilingual routing now.
- Preserve strong SEO, accessibility, responsive behavior, performance, and progressive enhancement.
- Make setup and navigation management understandable to a WordPress administrator.

## 3. Non-goals

- A contact form, form submissions, CRM integration, or email delivery.
- A custom post type, custom taxonomy, ACF field group, theme options page, or custom page builder.
- Automatic creation of the About or Contact pages.
- A theme-owned multilingual system or Vietnamese URL aliases.
- Hardcoded business facts such as address, phone number, email address, opening hours, certifications, or team biographies.
- WooCommerce or transactional functionality.

## 4. Architectural Decisions

### 4.1 Native Page content model

Each destination is a normal WordPress `page` post assigned to a dedicated selectable Page Template:

- GreenFarm About
- GreenFarm Contact

The templates consume only standard WordPress data:

- Page title for the single page-level H1
- Excerpt for an optional introductory summary
- Featured image for an optional hero/supporting image
- Block editor content for all substantive and business-specific copy

This structure avoids duplicating WordPress capabilities and keeps content portable if the theme changes. It also lets editors use native headings, paragraphs, lists, buttons, columns, images, and other core blocks without developer intervention.

### 4.2 Language and URLs

The initial editorial language is Vietnamese. Existing project routes remain English:

- `/about/`
- `/contact/`

Theme-owned interface strings must use the existing GreenFarm text domain and WordPress translation functions. Page content remains editor-owned. This supports a future multilingual plugin or WordPress multilingual strategy without creating a competing language layer now.

### 4.3 No invented business content

The theme provides layout and generic interface labels only. Actual Vietnamese brand narrative, email address, phone number, physical address, opening hours, and similar facts must be entered in the relevant Page content. The implementation must not invent or silently supply these details.

## 5. URL and Navigation Contract

| Destination | WordPress object | Required slug | Canonical route | Template |
| --- | --- | --- | --- | --- |
| About | Page | `about` | `/about/` | GreenFarm About |
| Contact | Page | `contact` | `/contact/` | GreenFarm Contact |

WordPress permalink and canonical behavior remain authoritative. The theme must not add rewrite rules or redirects for these pages.

Header and footer navigation continue to use registered native WordPress menus. Administrators add the two Pages through Appearance → Menus. Homepage links already targeting `/about/` and `/contact/` remain unchanged and form part of the route contract.

If a Page has not been created or published, WordPress returns its normal 404 response. The theme does not create content on activation and does not disguise missing content with a fallback route.

## 6. Shared Page Structure

Both templates use this semantic sequence:

1. Site header
2. Breadcrumb navigation
3. Page hero containing eyebrow, H1, and optional excerpt
4. Optional featured image
5. Main editor-owned block content
6. Template-specific closing content where applicable
7. Site footer

The main landmark contains exactly one page-level H1. The Page title supplies that H1. Editors begin content sections at H2 and nest H3 headings only where required.

Shared presentation should reuse existing theme tokens, container widths, buttons, focus styles, image helpers, breadcrumbs, and motion conventions wherever practical. New code should be limited to the behavior and layout unique to these core pages.

## 7. About Page

### 7.1 Purpose and user intent

The About page establishes trust by explaining who GreenFarm is, how the farm operates, and which values shape its products and stories. Visitors should be able to understand the brand before moving into product discovery or direct contact.

### 7.2 Content and layout contract

- Breadcrumb: Home → current Page title
- Eyebrow: translatable theme label “Về GreenFarm”
- H1: Page title
- Introduction: optional Page excerpt
- Visual: optional responsive featured image, displayed only when assigned
- Body: native block content authored in Vietnamese
- Closing CTA: links to `/products/` and `/contact/`

Recommended editorial sections inside the Page content are:

- GreenFarm's origin and story
- Farming approach and process
- People, growers, and community
- Environmental and quality values

These are editorial recommendations, not hardcoded required modules. Editors can omit, reorder, or expand them using core blocks.

### 7.3 CTA behavior

The template-owned closing CTA helps users continue to product discovery or contact. CTA labels are translatable theme strings. Destinations follow the existing route structure. The CTA must remain useful even when the optional excerpt or featured image is absent.

## 8. Contact Page

### 8.1 Purpose and user intent

The Contact page helps visitors find verified ways to reach GreenFarm. In the initial release, it presents contact information and direct email/phone actions without accepting form submissions.

### 8.2 Content and layout contract

- Breadcrumb: Home → current Page title
- Eyebrow: translatable theme label “Liên hệ”
- H1: Page title
- Introduction: optional Page excerpt
- Visual: optional responsive featured image, displayed only when assigned
- Body: native block content authored in Vietnamese

The Page body owns all contact facts. Recommended blocks include:

- Heading and paragraph blocks for context
- List blocks for address or opening hours
- Button blocks using `mailto:` or `tel:` links for direct actions
- Columns or groups for structured presentation when useful

The theme must not embed a fake email address, phone number, address, map, opening schedule, or social profile. It also must not render an empty contact card when editors have not supplied those details.

## 9. Breadcrumbs and Structured Data

Both templates expose the same two-level breadcrumb trail visually and in `BreadcrumbList` JSON-LD:

1. Home
2. Current Page

Visible and structured breadcrumb names and URLs must match. The current item is identified as the active page and is not treated as a navigation link when that would create a redundant action.

No `LocalBusiness`, `Organization`, person, review, or aggregate-rating schema is added because the theme does not own enough verified business data to publish it safely. WordPress and the site's SEO layer remain responsible for title tags, canonical URLs, robots directives, and any broader site schema.

## 10. SEO and Indexing

Both published Pages are intended to be indexable. Each has:

- One descriptive H1 derived from the Page title
- A stable, short canonical URL
- Editable introductory and body copy
- Contextual links to relevant products, articles, stories, and contact paths
- Breadcrumb navigation and matching structured data
- An optional featured image with WordPress-generated responsive image attributes

The templates do not output a second SEO title, meta description, canonical tag, or robots tag. Those remain under WordPress core and any future SEO plugin to prevent duplicate metadata.

Editors should write a distinct title and excerpt for each Page. About content should focus on brand trust and farming practices; Contact content should focus on verified contact methods. This separation prevents intent overlap and keyword cannibalization.

## 11. Responsive and Accessible Behavior

- Start with a single-column layout that works at 320 CSS pixels without horizontal overflow.
- Expand spacing, image presentation, and content composition at tablet and desktop widths without changing the reading order.
- Preserve semantic landmarks and a logical DOM order independent of the visual layout.
- Ensure interactive links and buttons have at least a 44-by-44 CSS-pixel target where applicable.
- Use visible keyboard focus styles consistent with the rest of the theme.
- Use meaningful link labels rather than relying on color or icons alone.
- Render featured images through WordPress image APIs so intrinsic dimensions, `srcset`, and appropriate `sizes` are available.
- Use the Page featured image alt text from the Media Library; decorative template visuals, if any, must not create redundant announcements.

## 12. Motion and Progressive Enhancement

Existing GreenFarm scroll-reveal behavior may be applied to major sections and content groups. Motion must:

- Enhance hierarchy without delaying access to content
- Avoid sliders, scroll-jacking, parallax, or layout-affecting animation
- Use transform and opacity only when practical
- Respect `prefers-reduced-motion: reduce`
- Leave all content visible and usable when JavaScript is unavailable or fails

The implementation should reuse the existing motion script instead of adding another animation dependency.

## 13. Asset Loading and Performance

Core-page styles load only when one of the two dedicated templates is active. Existing shared theme assets continue to follow their current loading rules. No third-party JavaScript, external font, map embed, or form library is introduced.

The templates should avoid custom queries. The native Page Loop and standard template functions provide the current post object. This prevents unnecessary database work and preserves normal WordPress caching behavior.

Optional images must not reserve incorrect dimensions or cause layout shift. Below-the-fold images may use WordPress-native lazy loading; the most prominent image should follow the theme's established loading-priority convention based on its actual placement.

## 14. Fallback and Error Behavior

- Missing excerpt: omit the summary element and its spacing.
- Missing featured image: omit the image region and rebalance layout spacing.
- Empty Page body: retain a valid hero and template-owned About CTA where applicable; do not fabricate body content.
- Invalid or unsafe editor markup: rely on normal WordPress content filtering and block rendering.
- Missing Page: preserve WordPress's native 404 behavior.
- JavaScript unavailable: all navigation, content, CTAs, and breadcrumbs remain accessible.

## 15. Editorial and Setup Workflow

For each destination, an administrator will:

1. Create a new WordPress Page.
2. Enter the Vietnamese title, excerpt, and body content.
3. Optionally assign a featured image with appropriate alt text.
4. Set the required English slug (`about` or `contact`).
5. Select the matching GreenFarm Page Template.
6. Publish the Page.
7. Add it to the desired registered header and footer menus.
8. Verify homepage and menu links on desktop and mobile.

Project documentation must record these steps and provide a concise suggested Vietnamese block outline. It must clearly distinguish example structure from verified business content.

## 16. Implementation Boundaries

The implementation is expected to touch only the GreenFarm theme, its tests, and its setup documentation. Likely responsibilities include:

- Two Page Template entry files
- Reusable template parts where duplication justifies them
- A small core-page stylesheet loaded conditionally
- Existing breadcrumb and motion integration
- Automated PHP/JS/integration checks appropriate to the current test harness
- Manual setup guidance for editors

No new production plugin or third-party package is required.

## 17. Acceptance Criteria

### Content and templates

- WordPress administrators can select distinct About and Contact templates.
- Each template renders native Page title, optional excerpt, optional featured image, and block content.
- Each rendered page contains exactly one H1.
- Editor-authored H2/H3 content retains a valid hierarchy when written according to the documented guidance.
- Missing optional fields leave no empty wrappers or broken spacing.

### Navigation and SEO

- Canonical routes are `/about/` and `/contact/`.
- Homepage links to both routes remain valid.
- Visible breadcrumbs match `BreadcrumbList` items and URLs.
- Templates do not duplicate title, canonical, meta description, or robots output.
- Published Pages remain searchable and indexable under normal WordPress settings.

### Accessibility and responsive design

- Pages work at 320 CSS pixels without horizontal overflow.
- Keyboard focus is visible and reading order is logical.
- CTA targets meet the 44-pixel minimum where applicable.
- Featured images use intrinsic dimensions and responsive image attributes.
- Reduced-motion users receive no nonessential reveal movement.
- Content remains present without JavaScript.

### Performance and regression safety

- Core-page CSS is conditional to the two templates.
- No third-party runtime dependency or avoidable custom query is introduced.
- Existing blog, product, farm-story, homepage, search, and archive templates retain their current behavior.
- Theme and related test suites pass after implementation.

## 18. Future Multilingual Readiness

When the whole website adopts multilingual support, a dedicated multilingual solution can translate Page content, menus, slugs, and theme strings together. This phase does not preselect that tool. By keeping interface text translatable, business content in native Pages, and routing free of custom language logic, the core pages remain compatible with that later site-wide decision.
