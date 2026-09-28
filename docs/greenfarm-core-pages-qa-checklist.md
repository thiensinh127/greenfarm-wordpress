# GreenFarm Core Pages QA Checklist

QA date: 2026-09-28  
Environment: WordPress Playground CLI 3.1.55, WordPress latest, PHP 8.3 runtime, GreenFarm theme 1.0.0.

## Automated verification

- [x] Theme integration suite: 29 tests, 0 failures.
- [x] GreenFarm Core plugin suite: 13 tests, 0 failures.
- [x] Theme/plugin content integration suite: 12 tests, 0 failures.
- [x] JavaScript suite: 6 tests, 0 failures.
- [x] Theme PHP tree: 40 files parsed without syntax errors.
- [x] Plugin PHP tree: 5 files parsed without syntax errors.
- [x] `motion.js` and `share.js` pass `node --check`.
- [x] `git diff --check` reports no whitespace errors.

Final command sequence:

```text
npm test
npm run lint:php
node --check wp-content/themes/greenfarm/assets/js/motion.js
node --check wp-content/themes/greenfarm/assets/js/share.js
git diff --check
```

## WordPress Playground runtime evidence

A temporary Playground site was created with the current GreenFarm theme mounted. About, Contact, ordinary Page, and image fixtures lived in the ignored plan workspace and were not committed.

- [x] `/about/` and `/contact/` resolve with HTTP 200.
- [x] Each dedicated Page renders exactly one H1 from its Page title.
- [x] About renders its manually entered excerpt, editor block content, and responsive featured image.
- [x] The featured image includes intrinsic width/height, `srcset`, and `sizes`.
- [x] Contact with no manual excerpt omits the summary instead of deriving one from body content.
- [x] Contact preserves the editor-owned `mailto:` Button and does not add a form or contact card.
- [x] Both Pages render Home → current Page `BreadcrumbList` data matching their visible context.
- [x] Both published Pages retain indexable robots defaults.
- [x] About CTA routes target `/products/` and `/contact/`.
- [x] Both templates load `core-pages.css` and the shared `motion.js` enhancement.
- [x] An ordinary Page returns HTTP 200 without core-page CSS or motion.
- [x] An unknown Page keeps the native HTTP 404, one recovery H1, and `noindex`.
- [x] All content and navigation are present in server HTML without JavaScript.
- [x] Runtime checker result: 28 checks, 0 failures.

Runtime commands:

```text
wp-playground-cli server --mount=./wp-content/themes/greenfarm:/wordpress/wp-content/themes/greenfarm --blueprint=<ignored-runtime-blueprint> --port=9401 --workers=1
node <ignored-runtime-checker>
```

## Responsive, accessibility, and motion audit

- [x] The 320px base layout uses a single content column, fluid padding, wrapping breadcrumbs/actions, `overflow-wrap`, and no fixed pixel content widths.
- [x] The 768px path activates the `40rem` breakpoint for wider gutters and the horizontal CTA layout.
- [x] The 1440px path activates the `64rem` breakpoint and responsive hero grid.
- [x] Theme-wide `:focus-visible` remains active for keyboard users.
- [x] Breadcrumb links, core Page buttons, and editor Button links retain a 2.75rem/44px minimum target height.
- [x] Reveal styling changes only opacity and transform.
- [x] `prefers-reduced-motion: reduce` removes reveal transitions and transforms.
- [x] Existing JavaScript tests prove observer reveal, reduced-motion visibility, and readable fallback when `IntersectionObserver` is unavailable.

## Visual-browser limitation

The in-app browser and native Chrome surfaces were unavailable to the computer-use bridge during this run (`Browser is not available: iab` and `Browser is not available: chrome`). Exact screenshots and a manual keyboard walkthrough could not be captured. The 320px, 768px, and 1440px checks above are structural CSS/DOM audits backed by the live Playground HTTP responses. A final human visual sweep at those three widths is recommended before merging.
