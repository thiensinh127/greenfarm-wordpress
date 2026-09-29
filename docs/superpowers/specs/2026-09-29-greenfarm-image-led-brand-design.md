# GreenFarm Image-Led Brand Experience

**Status:** Approved conversational design; ready for spec review
**Date:** 2026-09-29

## Goal

Evolve GreenFarm into an image-led farm-brand website that introduces seasonal products, farming practice, and field stories without becoming an ecommerce storefront. The information rhythm may take inspiration from Rau Tươi Đây's clear presentation of farm, product, credibility, and contact information, but GreenFarm must retain its own visual identity, copy, and content.

## Product intent

Visitors should first understand the farm and its seasonal point of view, then discover Products as tangible evidence of that work. Products support enquiry and trust; there is no cart, price, promotion, checkout, account, or purchase flow.

The primary visitor journeys are:

1. Homepage → current field story or seasonal point of view.
2. Story/Journal → relevant seasonal product or related reading.
3. Product → origin, harvest context, growing information, gallery, and Contact.
4. About/Contact → verified brand and connection information.

## Visual direction

Photography is the primary interface material: full-bleed farm scenes, crop detail, hands at work, people, and harvest moments. GreenFarm uses warm paper, forest green, restrained leaf-green emphasis, generous whitespace, and editorial typography. The layout must stay useful when an editor has not supplied an optional image.

Avoid generic image placeholders, AI-generated or stock-like artwork, decorative gradients, shadow-heavy card grids, fake metrics, testimonials without verified editorial content, and ecommerce UI patterns.

## Homepage structure

1. **Image-led hero:** real farm image, concise seasonal statement, supporting copy, and one CTA to Farm Stories.
2. **Farm introduction:** image detail plus two or three editor-verifiable commitments.
3. **Notes from the field:** a prominent lead Farm Story and a smaller supporting row.
4. **This season's harvest:** image-forward Product categories and Product cards with origin and harvest season; no price or buying controls.
5. **From farm to table:** three or four succinct, image-supported process moments using existing editor-owned material.
6. **Trust evidence:** verified method, certification, or attribution only when content exists.
7. **Closing contact invitation:** one low-pressure route to Contact.

## Page rules

### Products

The Product archive begins with seasonal context. Its first card may lead the layout at wider screens; other cards remain scannable and foreground image, title, availability, origin, and harvest season.

Product detail is a readable harvest page: image, context, editor content, facts, gallery, storage notes, and one Contact action. It never presents a buy button or cart substitute.

### Farm Stories and Journal

Archives lead with one image-rich article at desktop width, with subsequent items in a quieter grid. Singles maintain a readable column and use editor-supplied featured/inline imagery. Product links are contextual rather than mandatory.

### About, Contact, and 404

About is a narrative image-and-copy page using native WordPress fields. Contact uses verified, editor-owned connection details with a supporting farm image where supplied. The 404 remains an intentionally quiet recovery state with Home and Journal paths.

## Implementation boundaries

- Keep the classic PHP theme as presentation and `greenfarm-core` as the Product/Farm Story model owner.
- Reuse native Page editor content, excerpts, featured images, Product gallery metadata, and existing responsive-image markup.
- Use CSS composition and existing optional reveal motion only; add no dependency, page builder, custom content model, external font, image pipeline, ecommerce plugin, or fake form.
- A configured WordPress Primary menu wins over the fallback. Fallback order: Products, Farm Stories, Journal, About GreenFarm, Contact.

## Quality requirements

- One H1, semantic heading order, visible keyboard focus, and 44px minimum interactive targets.
- No horizontal overflow at 320px, 768px, or 1440px.
- Responsive images retain editor-supplied meaningful alt text and dimensions.
- Browser layout works without JavaScript and honors reduced-motion preferences.
- Preserve `noindex` protections for 404, search, and thin utility contexts.
- Verify theme, plugin, integration, JavaScript, PHP syntax, and whitespace checks after implementation.

## Out of scope

Direct selling, search-to-buy, cart, discount campaigns, member/login experience, delivery policy modules, payment methods, and invented proof or content are explicitly excluded.
