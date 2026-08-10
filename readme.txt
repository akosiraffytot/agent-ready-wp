=== Agent Ready WP ===
Contributors: akosiraffytot
Tags: json-ld, schema, structured data, schema.org, seo, knowledge graph
Requires at least: 6.9
Tested up to: 7.0.2
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Zero-bloat JSON-LD plugin that automatically emits a full Schema.org @graph on every page for search engines and AI agents.

== Description ==

Agent Ready WP injects a single [Schema.org](https://schema.org) JSON-LD `@graph` into every page, so search engines and AI agents instantly understand your organization, your content, and how your site is structured — with zero configuration and zero runtime dependencies.

= Plug-and-play =
Activate the plugin and a complete `@graph` is generated automatically on every page. No settings to save, no shortcodes, no template edits. Sensible defaults (site name, locale, schema types) kick in immediately; the settings page is there when you want to fine-tune.

= Schema output =
* **Organization** — name, logo, sameAs, and knowsAbout on every page
* **WebSite** — name, alternate name, and language
* **Person** — authors on posts (jobTitle and sameAs from the author profile)
* **WebPage / AboutPage / ContactPage / FAQPage** — per-page override from the editor
* **Article** — posts and other content types
* **CollectionPage + ItemList** — archives and the blog page
* **Custom post types** — full support with a configurable default schema

= Built-in tools =
* Live JSON-LD preview on the settings page — updates as you type
* Copy and Validate buttons that open validator.schema.org prefilled
* Admin bar "Validate Schema" menu for the current page, with a submenu for Schema.org (via URL) and Google's Rich Results Test
* FAQ validation while editing

= AI-ready roadmap =
* **JSON-LD** — active by default
* **llm_txt, AI robots, WooCommerce** — coming soon (module cards on the dashboard)

No dependencies, theme-agnostic, translation-ready.

== Installation ==

= Upload the plugin =
1. Download the plugin .zip from [GitHub](https://github.com/akosiraffytot/agent-ready-wp).
2. In WordPress admin, go to Plugins > Add New > Upload Plugin, select the .zip, and click Install Now.
3. Activate "Agent Ready WP". JSON-LD output starts immediately — no setup required.

= Automatic updates =
Agent Ready WP checks GitHub for updates, so new releases appear in Plugins > Installed Plugins alongside WordPress updates.

== Frequently Asked Questions ==

= Do I need to configure anything after activating? =
No. A complete `@graph` is generated on every page using sensible defaults. The settings page is optional customization.

= Does it require another plugin or theme? =
No. It has zero runtime dependencies and works with any theme.

= Which schema types are generated? =
Organization, WebSite, Person (authors), WebPage/AboutPage/ContactPage/FAQPage (pages), Article (posts), CollectionPage + ItemList (archives), and custom post types.

= How do I check that my structured data is valid? =
Use the admin bar "Validate Schema" menu — its main item opens validator.schema.org prefilled with the current page's schema, and its submenu adds Schema.org (via URL) and Google's Rich Results Test, which validate the live page URL — or use the Validate button on the JSON-LD settings page.

= Will it slow down my site? =
No. Output is a single small JSON-LD script in the head, built with standard WordPress functions and no external requests.

== Changelog ==

= 1.1.0 =
* New: organization type selector with 54 Schema.org subtypes (LocalBusiness, non-profit, news media, corporation, e-commerce, and store types).
* New: Organization identity fields — legal name, slogan, tax/VAT IDs, founding date, founder, area served, and contact point (phone, email, type, languages).
* New: LocalBusiness data — postal address, geo coordinates, price range, and opening hours (parsed to openingHoursSpecification).
* New: niche fields — non-profit status, publishing/ethics/corrections/diversity policies, ticker symbol, accepted payments/currencies, merchant return policy.
* New: per-post Service schema (type + price) from the editor.
* New: automatic BreadcrumbList on pages, posts, and archives.
* New: option to suppress third-party SEO JSON-LD (Yoast, Rank Math, AIOSEO) to avoid duplicates.
* New: developer filters agent_ready_organization_node and agent_ready_json_ld_graph.
* New: organization logo pickable from the WordPress Media Library.
* Improved: oversized validator code-prefill now falls back to URL-based validation instead of an empty validator.
* Fixed: settings pages now show the "Settings saved." confirmation notice after saving.

= 1.0.4 =
* Updated: plugin updates now download the install-ready zip attached to the GitHub release, so the plugin folder stays free of repository-only files (index.html, .github, AGENTS.md).

= 1.0.3 =
* New: the admin bar "Validate Schema" menu now includes a submenu with two URL-based validators — Schema.org (via URL) and Google's Rich Results Test — alongside the existing code-prefilled schema.org validator.

= 1.0.2 =
* Fixed: the admin bar "Validate Schema" button now opens validator.schema.org with the prefilled JSON-LD even on sites with Formidable Forms active (the link is built server-side, with no JavaScript dependency).
* Fixed: the JSON-LD prefilled into the validator keeps its per-line formatting (newlines are no longer stripped by WordPress URL escaping).

= 1.0.0 =
* Initial release.
* Emits a single JSON-LD @graph: Organization, WebSite, Person, content nodes, archives CollectionPage + ItemList, and custom post type support.
* Dashboard with one-click module toggles (JSON-LD active; llm_txt, ai_robots, woocommerce coming soon).
* Post/page schema overrides (WebPage, AboutPage, ContactPage, FAQPage) with FAQ validation.
* Author profile fields (jobTitle, sameAs) for Person schema.
* Live JSON-LD preview with Copy and Validate actions.
* Automatic updates from GitHub (Plugin Update Checker).

== Upgrade Notice ==

= 1.1.0 =
Adds the schema expansion: organization type selector, LocalBusiness/non-profit/news/corporate/e-commerce fields, Service schema, BreadcrumbList, third-party SEO suppression, developer filters, and Media Library logo picker.

= 1.0.4 =
Updates now use the clean install-ready zip from the release; the plugin folder no longer gains repository files during an update.

= 1.0.3 =
Adds URL-based validation links (Schema.org via URL and Google's Rich Results Test) to the admin bar Validate Schema menu.

= 1.0.2 =
Fixes the admin bar Validate button, which could open validator.schema.org without your JSON-LD on sites running Formidable Forms. If you use Validate, update.
