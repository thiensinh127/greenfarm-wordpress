# GreenFarm Editorial Redesign

**Goal:** Reposition GreenFarm from a generic demo-like farm site into an editorial farm journal that builds trust through real seasonal products, field stories, and useful knowledge.

## Product intent

The current phase prioritizes brand trust over direct ecommerce. Visitors should discover why GreenFarm grows the way it does, meet the people and practices behind the harvest, and encounter products as evidence from those stories. Direct checkout is out of scope.

The primary public paths are:

1. Homepage → a current seasonal story or field point of view.
2. Farm Story or Journal article → supporting context and related seasonal products.
3. Product page → origin, harvest season, growing method, storage guidance, gallery, and a low-pressure contact path.
4. About and Contact → reinforce credibility and make direct connection easy.

## Visual direction

The UI is an editorial farm journal rather than an ecommerce catalogue. It uses forest green for depth, warm paper surfaces for reading, restrained leaf green for emphasis, and authentic photography as the primary visual material. Layouts use generous whitespace, strong display typography, quiet supporting text, and a small number of meaningful calls to action.

Avoid decorative AI-like gradients, repeated generic card grids, fabricated proof points, external motion libraries, fake checkout mechanics, and stock-like illustrative imagery.

## Navigation and hierarchy

The primary navigation remains compact and predictable: Products, Farm Stories, Journal, About GreenFarm, and Contact. A configured WordPress Primary menu always wins. The existing fallback remains useful when no menu has been assigned.

Each page has one primary next action. Products and stories may link to each other contextually, but no page should expose competing conversion paths merely to fill space.

## Page system

### Homepage

Lead with an editorial hero: a seasonal statement, a concise supporting paragraph, one primary route, and an optional real image. Follow with a short farm introduction, a featured story, a limited seasonal-product strip, a values/process section only when it carries specific evidence, latest Journal articles, and one closing invitation.

### Product archive and single

The archive introduces the current harvest rather than behaving like a store shelf. Product cards remain scannable but foreground availability, season, origin, and a meaningful image. The single template is a readable product story: identity first, then facts, gallery, practical guidance, and only one next action.

### Farm Stories and Journal

Archives privilege a lead story and clean editorial rows or cards beneath it. Single articles provide reading width, metadata, image support, contextual related content, and a product connection only when it is genuinely relevant.

### Core pages and recovery states

About explains GreenFarm's people, practice, and values using editor-owned content. Contact gives verified connection paths without invented facts or a pretend form. The 404 remains an intentional recovery page with search and calm routes home.

## Components and implementation boundaries

The existing classic PHP theme remains the presentation layer and the `greenfarm-core` plugin remains the owner of Product and Farm Story content models. The redesign will use only existing WordPress APIs, current responsive-image handling, existing motion enhancement, and a small shared set of theme component classes:

- editorial hero
- section heading
- article, product, and story card variants
- contextual product strip
- CTA and empty/recovery states

Styles will favor content-led composition over a global card abstraction. JavaScript remains optional and only supports the existing one-shot reveal behavior. Every page stays useful without JavaScript and under reduced-motion preferences.

## Accessibility and quality requirements

- One H1 per page and ordered heading hierarchy.
- Keyboard-visible focus and 44px interactive controls.
- No horizontal overflow at 320px, 768px, and 1440px.
- Native responsive images with meaningful alt text supplied by editors.
- Server-rendered page content and navigation.
- `noindex` behavior for 404, search, and thin utility archives is preserved.
- Existing PHP, plugin, integration, JavaScript, lint, and whitespace checks remain green.

## Scope and delivery order

1. Establish shared editorial tokens and navigation refinements.
2. Redesign the homepage as the visual reference.
3. Apply the system to Product and Farm Story archives/singles.
4. Apply the system to Journal, About, Contact, and recovery states.
5. Verify complete paths in the full Playground demo at mobile, tablet, and desktop widths.

No new content model, page builder, dependency, ecommerce flow, external font, or image-generation pipeline is included.
