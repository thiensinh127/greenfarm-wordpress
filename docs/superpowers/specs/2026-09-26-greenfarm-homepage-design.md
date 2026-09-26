# GreenFarm Homepage Design

## Goal

Build a mobile-first GreenFarm homepage that moves visitors from brand understanding to trust, product discovery, educational content, and an enquiry/subscription action. The visual language uses immersive farm photography, forest green foundations, warm off-white content surfaces, restrained lime accents, and premium editorial typography.

## WordPress Architecture

- Use `front-page.php` in the existing custom classic theme.
- Configure a static Page as the WordPress homepage.
- Use the Page title as the hero H1, excerpt as hero supporting copy, featured image as hero media, and editor content as the farm introduction body.
- Query native Posts for educational articles using a small secondary homepage query because the front page main query represents the static Page.
- Query `greenfarm_product` and `farm_story` only when those post types exist. Sections disappear cleanly when their data model has not yet been implemented; the homepage does not invent duplicate fallback business data.
- Query `product_category` only when that taxonomy exists and contains public terms.
- Keep values, process labels, and section interface copy in the theme because they define the GreenFarm presentation. Move them into structured site options only when editors demonstrate a real need to change them.
- Do not add ACF, a page builder, slider, icon library, animation library, or remote webfont.

## Page Structure

### 1. Header

Reuse the semantic site header. On the front page it sits over the hero at the top and becomes a solid forest-green sticky bar after the page begins scrolling. The primary navigation exposes About, Products, Learn, Farm Stories, and Contact without listing generated tags or low-value archives.

### 2. Hero

- Full-width dark photographic composition with readable gradient overlay.
- Page title is the only H1.
- Page excerpt supplies the short supporting statement.
- Primary CTA links to `/products/`; secondary CTA links to `/about/`.
- Featured image is the LCP candidate, rendered through WordPress attachment APIs with explicit dimensions and responsive sources. It is eager/high-priority; no other image receives this treatment.
- If no featured image exists, retain a premium forest-green gradient rather than displaying a broken or placeholder image.

### 3. Farm Introduction

- H2: “A farm rooted in care.”
- Render the static homepage Page content as the editorial introduction.
- Display three factual proof points: seasonal growing, responsible methods, and transparent farm stories.
- CTA links to `/about/`.

### 4. Product Categories

- Render up to four non-empty `product_category` terms.
- Each card links to its canonical taxonomy archive and uses an optional term image when that model later supplies one.
- Hide the entire section when the taxonomy or usable terms do not exist.

### 5. Featured Products

- Render up to four published `greenfarm_product` posts marked featured when the future product model exposes that flag; until then, use the newest published products.
- Cards use native title, excerpt, featured image, availability metadata, and permalink.
- Hide the section when the post type has no published content.

### 6. Farm Values

Three evidence-oriented value cards: thoughtfully grown, harvested in season, and openly shared. Avoid unverifiable absolute claims such as “100% sustainable.”

### 7. Farm Process

Use an ordered list for Grow → Harvest → Prepare → Share. Desktop presents a horizontal path; mobile presents a vertical timeline. No animation is required to understand sequence.

### 8. Farm Stories

- Render the two newest published `farm_story` posts.
- Feature the first story more prominently on desktop.
- Hide the section when the post type has no published content.

### 9. Latest Educational Articles

- Render three published native Posts, excluding sticky behavior from changing the curated layout.
- Reuse accessible post-card semantics while allowing homepage-specific heading levels.
- Link the section to the configured Posts page.

### 10. Social Proof

Do not publish testimonials until verified testimonials and consent exist. Version one uses a factual proof strip instead: seasonal harvests, clear growing information, and practical farm education. No rating or Review schema is emitted.

### 11. Newsletter / Final CTA

Version one is a compact “Get seasonal updates” call to action linking to Contact. Do not create a fake email capture pipeline. A form is introduced only after a real mailing provider, consent copy, storage policy, and error/success flow are selected.

### 12. Footer

Reuse the existing semantic footer and its WordPress menus. The visual treatment remains dark, simple, and free of taxonomy-link dumps.

## Visual System

- Forest: `#052e20`
- Deep forest: `#031f16`
- Leaf accent: `#a8d644`
- Warm cream: `#f5f3e8`
- Paper: `#fffdf6`
- Ink: `#13251d`
- Large editorial headings use the existing system-font stack with tight tracking; no external font request.
- Cards use moderate rounding, subtle borders, and restrained shadow.
- Sections alternate dark immersive surfaces and warm light surfaces to create rhythm without decorative clutter.
- Photography remains natural and realistic; illustrations and playful farm mascots are excluded.

## Motion

- Reuse the existing `data-reveal`/`IntersectionObserver` system.
- Sections fade upward once when entering the viewport.
- Repeated cards may use a small stagger capped at 180ms total.
- Use only opacity and transform; never animate layout properties.
- Content remains visible without JavaScript.
- `prefers-reduced-motion: reduce` removes transitions and transforms.
- No slider, autoplay, scroll hijacking, cursor effect, or continuous parallax.

## Responsive Behavior

- Start from a single-column layout at 320px.
- Category/product/article cards move to two columns when space permits and three/four columns only at desktop widths.
- Hero text precedes supporting imagery in source order.
- Minimum interactive target size is 44px.
- Navigation may wrap temporarily; a dedicated menu toggle belongs to a later global-navigation phase if real menu length requires it.
- Prevent horizontal overflow at every supported breakpoint.

## SEO, Accessibility, and Performance

- Exactly one H1 from the static homepage title.
- Use H2 for section headings and H3 for cards.
- Home emits Organization/WebSite schema only when matching visible, accurate site data is available; do not create placeholder schema.
- Dynamic cards link to canonical public destinations using descriptive visible text.
- Hero is the only eager/high-priority image. Below-fold images use WordPress lazy loading.
- All images reserve intrinsic space and use WordPress `srcset`/`sizes` output.
- Maintain logical landmarks, keyboard focus, color contrast, and screen-reader-friendly decorative treatment.
- Avoid remote fonts, third-party scripts, autoplay video, and JavaScript-driven layout.

## Error and Empty States

- Missing hero image falls back to CSS gradient.
- Empty Product Category, Product, or Farm Story datasets remove their entire section without leaving an orphaned heading.
- An empty Blog query omits Latest Articles while preserving the CTA flow.
- Invalid configured links fall back to known WordPress URLs or the home URL.

## Acceptance Criteria

- WordPress selects `front-page.php` for the configured static homepage.
- The rendered page contains exactly one H1 and a logical H2/H3 outline.
- The hero works with and without a featured image.
- Optional CPT/taxonomy sections appear only when their models and content exist.
- Latest Articles uses a constrained secondary query and restores global Post state.
- Desktop and mobile layouts contain no horizontal overflow.
- Scroll reveals run once and reduced-motion users see content immediately.
- Native responsive image attributes and intrinsic dimensions are present for WordPress attachments.
- No extra production plugin or frontend dependency is introduced.
