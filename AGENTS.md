# AGENTS.md

WordPress plugin "Agent Ready WP". Injects `@graph` JSON-LD for AI-agent readiness. Zero runtime deps.

## Build workflow (critical)

- **One phase at a time.** Work is gated by `TODO.MD`. Never build ahead of the approved phase. Finish phase -> give exact test steps -> wait for user confirmation -> tick checkbox -> next phase.
- `TODO.MD` = live status + decisions logged. `DEVELOPMENT_PLAN.md` = draft spec with known inconsistencies: the `modules/` layout wins over the checklist's stale `inc/schema-builder.php` / `inc/integrations-woo.php` paths.
- Scope: only JSON-LD module (`modules/module-json-ld.php`) is built. llm_txt, ai_robots, woocommerce exist as disabled dashboard cards with "Soon" badge. No placeholder module files.
- Data storage: `wp_postmeta` + `wp_options`. No custom DB tables (deliberate).
- Menu: top-level "Agent Ready WP" + Dashboard (card grid) + Settings submenu. Module toggles = instant AJAX, not form reload.

## Current state (don't assume)

- Phases 1–4 complete: `agent-ready-wp.php` (constants, module registry,
  activation seed, loader), `inc/admin-dashboard.php` (menu + card grid +
  AJAX toggle), `inc/admin-settings.php` (Settings submenu + field-render
  helpers), `inc/post-meta-boxes.php` (post/page schema overrides + FAQ
  validation), `inc/user-profile.php` (author jobTitle/sameAs fields),
  `modules/module-json-ld.php` (per-module settings + live preview AJAX +
  `wp_head` @graph output), `assets/arwp-admin.css`,
  `assets/arwp-editor.css`, `assets/arwp-admin.js`,
  `assets/arwp-jsonld-preview.js`.
- Front-end output (Phase 4): single `ld+json` on `wp_head` priority 5 with
  @graph of Organization, WebSite, Person (single posts), and a content node.
  All entity refs are typed via `arwp_jsonld_ref()`. Static front page always
  emits `WebPage` (`is_front_page()` guard overrides meta/default). Pages can
  be overridden to WebPage/AboutPage/ContactPage/FAQPage in the meta box;
  AboutPage with no topic URI gets `about` -> Organization.
- Phases 5–7 complete (user-confirmed): archives/home emit a
  `CollectionPage` + `ItemList` (Phase 5); custom post types get a content
  node, optional Person, and a "Default Other Post Type Schema" mapping
  (`arwp_schema_default_other_type`, default Article) (Phase 6); the post meta
  box registers on all public post types with a generic type union for CPTs
  (Phase 7). `inLanguage` follows `get_locale()` so multilingual sites are
  covered with no extra code. The "no author" Person-skip path is code-guarded
  via `post_author > 0` (not UI-testable).
- FIX (post-Phase-7): the Settings (general) and JSON-LD settings pages shared
  the option group `arwp_schema_options`, and WP `options.php` calls
  `update_option( $option, null )` for every non-POSTed option in the group —
  saving either page wiped the other and triggered an `esc_url(null)` ->
  `ltrim()` deprecation via the org-logo sanitizer. JSON-LD options now use
  their own group `arwp_jsonld_options` (`register_setting` +
  `settings_fields`); general Settings keeps `arwp_schema_options`. Option
  names unchanged, so `get_option()` reads and stored data are unaffected.
- Only the JSON-LD module file exists. llm_txt, ai_robots, woocommerce are
  disabled dashboard cards only. No placeholder module files.
- Phase 8 (PUC auto-update) in progress: re-download into `lib/`, wire
  `PucFactory::buildUpdateChecker` with the hardcoded `ARWP_GITHUB_REPO`
  constant (no token; re-add a token field only for a private repo). Loader
  must `file_exists()` guard module requires.
- Not a git repo. Don't run git/commit commands without asking.

## Stack constraints

- WP 6.9+, PHP 7.4 floor — no PHP 8-only syntax (`match`, union types, `str_contains` without fallback).
- WPCS: nonce on every form/AJAX, `sanitize_*` on input, `esc_*` on output, `current_user_can( 'manage_options' )` for admin/AJAX, `$wpdb->prepare` for SQL, i18n textdomain `arwp`, no `$_POST` without `wp_unslash` + sanitize.
- JSON-LD output: single `<script type="application/ld+json">` on `wp_head` priority 5, via `wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT )`. Never concatenate raw JSON strings.
- No `ACF`/`WooCommerce` calls outside `function_exists()` / `class_exists()` guards.

## Verification

- Lint: `php -l <file>` (PHP 8.3 CLI available on this Laragon box). Run after every phase.
- PHPCS with `WordPress` standard if available; manual nonce/capability audit otherwise.
- No automated test framework. User manually tests in `plugindev` WP install; you must supply concrete test steps per phase.
