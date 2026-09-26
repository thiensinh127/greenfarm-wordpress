# GreenFarm Blog Setup

The theme does not mutate permanent WordPress content or permalink settings when activated. Configure these site-level settings once in the target environment.

1. Create a published Page named **Blog** with slug `blog`.
2. In **Settings → Reading**, set **Posts page** to **Blog**. Keep the homepage choice aligned with the future GreenFarm homepage implementation.
3. In **Settings → Permalinks**, choose **Custom Structure** and enter `/blog/%postname%/`.
4. Save Permalinks once after migration or domain replacement to flush rewrite rules.
5. Create only the approved durable Blog Categories; keep Tag archives noindex until they become curated topic hubs.
6. Add meaningful featured-image alt text in the Media Library and use H2/H3 headings inside article content—never another H1.

After configuration, verify:

- `/blog/` resolves through `home.php`.
- `/blog/{post-slug}/` resolves through `single.php`.
- `/category/{category-slug}/` resolves through `category.php`.
- Search and missing URLs emit `noindex,follow`.
- Pagination preserves its archive context.
