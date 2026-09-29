# GreenFarm Editorial Redesign QA

QA date: 2026-09-29
Environment: WordPress Playground CLI 3.1.55, WordPress latest, PHP 8.3, GreenFarm theme, and `greenfarm-core` activated.

## Automated checks

- [x] Theme integration suite: 30 tests, 0 failures.
- [x] GreenFarm Core plugin suite: 13 tests, 0 failures.
- [x] Theme/plugin content integration suite: 13 tests, 0 failures.
- [x] JavaScript suite: 6 tests, 0 failures.
- [x] Theme PHP tree: 40 files parsed without syntax errors.
- [x] Plugin PHP tree: 5 files parsed without syntax errors.
- [x] `motion.js` and `share.js` pass syntax checks.
- [x] `git diff --check` has no whitespace errors.

## Full demo paths

The full ignored Playground fixture creates a static homepage, Product and Farm Story fixtures, Journal posts, and template-assigned About and Contact Pages.

- [x] `/`, `/products/`, `/farm-stories/`, `/about/`, `/contact/`, and `/journal/` resolve in the demo.
- [x] Homepage shows the story section before the bounded seasonal Product strip.
- [x] The Primary fallback lists Products, Farm Stories, About GreenFarm, Contact, and Journal; an assigned menu overrides it.
- [x] Product and Farm Story archives retain native pagination, breadcrumbs, and editorial layout hooks.
- [x] The 404 recovery UI has one H1, native search, Home and Journal paths, and `noindex` protection.

## Manual follow-up before production

- [ ] Replace fixture copy and placeholder imagery with verified GreenFarm photography and content.
- [ ] Review all page types at 320px, 768px, and 1440px in a production-like browser session.
- [ ] Perform a keyboard-only walkthrough after the production menu and content are configured.
