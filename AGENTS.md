# AGENTS.md

WordPress plugin "Agent Ready WP". Injects `@graph` JSON-LD for AI-agent readiness. Zero runtime deps.

## Skills (load every session)

- **ALWAYS load the `wordpress-pro` skill first** (via the skill tool) at the start of every session before doing any work. It is the authoritative WordPress coding-standards + security reference for this project and must be loaded on every session.
- `caveman` / `ponytail` output-style skills: **load every session too** (user prefers them active) — caveman = terse speech, ponytail = minimal/simplest solutions.

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
  `assets/arwp-jsonld-preview.js`, `assets/arwp-adminbar.css`.
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
- 1.1.0 schema expansion: Phases 1–7 complete (all user-confirmed). Phase 6
  (SEO-compat) behavior: option `arwp_disable_third_party_schema` on global
  Settings (General section, hidden-0 checkbox) defaults ON, and suppression
  only applies while the JSON-LD module is active — `arwp_disable_third_party_schema()`
  (module-json-ld.php, `init`) gates on `arwp_schema_active_modules['json_ld']`
  AND the option, then adds `wpseo_json_ld_output` (`__return_false`),
  `rank_math/json_ld` (`__return_empty_array`; the old `rank_math/json_ld/level_0`
  from the spec was a hallucinated no-op), `aioseo_schema_disable`
  (`__return_true`). When the module is off the checkbox is visually greyed but
  stays ENABLED so options.php (`update_option( $option, null )` for
  non-POSTed group options) can't wipe the stored preference; the JSON-LD
  sidebar submenu is hidden when off via a DOMContentLoaded-deferred head
  script (`arwp_jsonld_hide_menu_when_off()`, module-json-ld.php — the original
  script ran in `admin_head` before `#adminmenu` existed in the DOM and never
  hid anything). Activation seeds `arwp_disable_third_party_schema = '1'`.
  Live suppression vs a real SEO plugin NOT tested (none installed) — code-only
  acceptance.
- 1.1.0 Phase 7 (dev filters + logo picker + validator guard) complete, all
  user-confirmed. Three additions to `modules/module-json-ld.php` +
  `assets/arwp-admin.js` + `assets/arwp-jsonld-preview.js`:
  - **Dev filters:** `apply_filters( 'agent_ready_organization_node', $organization )`
    in `arwp_jsonld_build_global_nodes()` (covers front-end AND preview — preview
    calls this function), and `apply_filters( 'agent_ready_json_ld_graph', $graph )`
    on the return of `arwp_jsonld_build_graph()` (covers front-end, admin-bar
    main item, submenu validators). The settings preview AJAX
    (`arwp_ajax_preview_jsonld()`) builds its schema INLINE and now ALSO wraps it
    in `agent_ready_json_ld_graph` so the preview can't diverge from live output.
  - **Logo picker:** `arwp_field_org_logo()` uses custom markup (text input
    `arwp_schema_org_logo` + "Select from Media Library" button) instead of
    `arwp_text_field()`. `wp_enqueue_media()` added to the jsonld-page branch of
    `arwp_admin_enqueue()`. The media-frame handler in `assets/arwp-admin.js` is
    bound inside `window.addEventListener('load', ...)` because `arwp-admin.js`
    (footer, enqueued before `wp_enqueue_media()`) otherwise runs before
    `wp.media` exists — same footer-ordering fragility class as the old
    Formidable/`arwp-validate-schema.js` bug.
  - **Validator guard:** `arwp_jsonld_validator_href()` returns
    `https://validator.schema.org/#url=` + rawurlencode( current page URL ) when
    the `?code=` payload would exceed 14 KB (admin bar main item + its
    title-anchor inherit automatically; submenu unchanged). Settings-page
    Validate button (`updateValidateLink()` in `assets/arwp-jsonld-preview.js`)
    falls back to `#url=` + `ArwpPreview.pageUrl` (`home_url('/')`, localized in
    `admin-dashboard.php`) at the same threshold. Decision: `#url=` (URL fetch)
    instead of the originally-planned bare validator URL — matches the existing
    "Schema.org (via URL)" submenu behavior. `#url=` needs a publicly reachable
    URL (fetch fails on `plugindev.test`, same as the submenu items).
  - **FIX (found while testing):** neither settings page rendered
    `settings_errors()`, so Save Changes silently reloaded with no "Settings
    saved." notice. Added `settings_errors();` after the opening `<div class="wrap">`
    in both `arwp_render_settings()` (admin-settings.php) and
    `arwp_jsonld_render_settings()` (module-json-ld.php). Folds into 1.1.0.
- FIX (post-Phase-7): the Settings (general) and JSON-LD settings pages shared
  the option group `arwp_schema_options`, and WP `options.php` calls
  `update_option( $option, null )` for every non-POSTed option in the group —
  saving either page wiped the other and triggered an `esc_url(null)` ->
  `ltrim()` deprecation via the org-logo sanitizer. JSON-LD options now use
  their own group `arwp_jsonld_options` (`register_setting` +
  `settings_fields`); general Settings keeps `arwp_schema_options`. Option
  names unchanged, so `get_option()` reads and stored data are unaffected.
- 1.2.0 (multi-type + clusters + escape hatch): all three phases complete,
  user-confirmed. Changes live in `modules/module-json-ld.php` (+
  `assets/arwp-admin.js`, `assets/arwp-admin.css`,
  `assets/arwp-jsonld-preview.js`):
  - **Multi-type Organization selector:** `arwp_schema_org_type` is now an
    ARRAY of types (legacy string wrapped). `arwp_schema_org_categories()` is
    the source of truth (56 types); `arwp_schema_org_group_map()` maps each
    type to one of 10 groups (local_business/ngo/news_media/corporation/
    commerce/dining/hospitality/medical/education/civic_community); the
    builder emits `@type` as string or array and unions settings sections via
    `$org_groups`. Pill/token multi-select in the JSON-LD settings page
    (data-list of `.arwp-org-token`, `data-type` field, X chips), JS in
    `assets/arwp-admin.js`.
  - **Field clusters:** 14 options — dining (serves_cuisine,
    accepts_reservations, menu_url), hospitality (star_rating, num_rooms,
    checkin_time, checkout_time, pets_allowed), medical (medical_specialty,
    available_service), education (departments, alumni), civic_community
    (affiliation, sport). Sanitizers: `arwp_sanitize_rating` (0–5 float),
    `arwp_sanitize_time` (HH:MM regex). Entity fields use
    `arwp_jsonld_named_entities()` (NAME|URL lines → Service/Organization/
    Person nodes). Toggles emit via `'1' === (string) arwp_jsonld_value(...)`
    (absint stores int, strict string compare failed before — Phase 2 fix).
  - **"Location & Local Information"** section (renamed from "Local Business")
    now also applies to education/civic types, NGOs, news media, and
    corporations via group union.
  - **Custom JSON-LD escape hatch:** options `arwp_schema_custom_org/website/
    content/graph` (4 textareas). `arwp_sanitize_custom_json()` validates
    `name|JSON` lines (`{home}` placeholder, `#` comments, duplicate names →
    arrays; invalid lines dropped + settings notice). `arwp_jsonld_parse_custom()`
    returns name→JSON map (scalars allowed — `null === $decoded` guard, NOT
    `! is_array()`); `arwp_jsonld_apply_custom()` merges (duplicate-name
    crash fixed by normalizing to array before append); `arwp_jsonld_custom_graph()`
    wraps graph-node blocks. Merged into org/website in
    `arwp_jsonld_build_global_nodes()` (BEFORE the `agent_ready_organization_node`
    filter), content in `arwp_jsonld_build_content_node()` +
    `arwp_jsonld_build_page_node()`, graph appended in `arwp_jsonld_build_graph()`
    AND the preview AJAX (`arwp_ajax_preview_jsonld()`, whitelist synced) so
    preview matches live output. Preview buttons re-enable on AJAX failure
    (`refresh()` in `assets/arwp-jsonld-preview.js`).
  - **WPCS pass (2026-08-11):** `phpcs --standard=WordPress` reports 0 errors /
    0 warnings on all 6 plugin PHP files. `phpcbf` fixed CRLF line endings +
    alignment; 75 docblocks added to the settings field/section renderers;
    `translators:` comments on 4 placeholder strings; `@package` added to the
    plugin header. Two real hardening fixes: `isset` guard on `$_POST['enabled']`
    in `arwp_ajax_toggle_module()` (inc/admin-dashboard.php) and
    `check_admin_referer( 'update-user_' . $user_id )` added to
    `arwp_save_user_schema_fields()` (inc/user-profile.php; core already
    verifies this nonce, this is defense in depth). Three documented
    `phpcs:ignore` false positives: the `wp_head` JSON echo (already hex-escaped
    by `wp_json_encode` + JSON_HEX_TAG), the preview AJAX `$_POST` routed to
    per-field `arwp_sanitize_*` callbacks via `call_user_func`, and the profile
    `sameAs` url-list sanitizer.
  - Docs updated (doc/ is gitignored): §4.1 conditional bullets, §4.5 title,
    §4.10–4.14 (Dining/Hospitality/Medical/Education/Civic), §4.15–4.17
    renumbered, §4.18 Custom JSON-LD, §12. Release bookkeeping prepped in
    this repo's committed files (readme.txt, CHANGELOG.TXT, AGENTS.md,
    header + `ARWP_VERSION` = 1.2.0). SHIPPED as commit `3f725aa` (tag
    `v1.2.0` + GitHub Release created by the user on the GitHub UI; PUC
    update delivered to live sites).
- Validate Schema (admin bar): current **1.0.2** builds the prefilled
  `validator.schema.org/?code=` link SERVER-SIDE with NO JS. The node `href` is
  left `''` so WP never runs the URL through `esc_url()` (its `clean_url` CRLF
  guard strips `%0A`/`%0d`/`%0a`/`%0D` — that was the 1.0.1 one-long-line bug).
  The link lives in the raw `title` as an `<a href="esc_attr( ... )">` where the
  URL comes from `arwp_jsonld_validator_href()` (`'https://validator.schema.org/?code=' .
  rawurlencode( arwp_jsonld_graph_json( $schema ) )`); `esc_attr` does NOT run
  `clean_url`, so pretty-print newlines survive as `%0A`. 1.0.0's
  `arwp-validate-schema.js` (click-time `window.open` interception) was DELETED
  because it silently failed to attach when the footer script ran before the
  admin bar rendered — e.g. with Formidable Forms active
  (`FrmFormsController::footer_js` on `wp_footer` p1, `move_menu_to_footer()`
  removes the `wp_body_open` renderer, `wp_before_admin_bar_render` hook) — and
  the bare href was followed. Server-side anchor is immune to JS/footer
  ordering. Empty-href nodes render `<div class="ab-item ab-empty-item">`;
  `assets/arwp-adminbar.css` (enqueued by `arwp_adminbar_assets()`) styles the
  inner anchor (inherits admin-bar color; core `li:hover > .ab-item` hover).
  The admin Settings-page Validate button is UNCHANGED (JS-built from the live
  preview via `arwp-jsonld-preview.js`, admin-only, not affected). Validator
  choice: `validator.schema.org` (successor to Google SDTT). Its documented
  limitation ("will not fetch or interpret other @context URLs") does NOT apply
  — we always emit `@context: https://schema.org`, its built-in vocabulary.
- Validate Schema (admin bar) submenu (1.0.3): the code-prefilled main item now
  has a hover submenu with two URL-based validators — "Schema.org (via URL)"
  (`https://validator.schema.org/#url=` + `rawurlencode( home_url( add_query_arg( array() ) ) )`)
  and "Google's Rich Results Test"
  (`https://search.google.com/test/rich-results?url=` + same). Both are native
  admin-bar submenu nodes (`parent => 'arwp-validate-schema'`,
  `target="_blank"`, `rel="noopener noreferrer"`); their hrefs are
  rawurlencode'd page URLs (no `%0A`) so they pass `esc_url()` normally — no
  title-anchor hack needed. The main item's server-side `?code=` prefill and
  `assets/arwp-adminbar.css` are UNCHANGED. Both submenu links require a
  publicly reachable URL — Google cannot fetch private/local domains
  (`plugindev.test`), so on local/dev sites validation still goes through the
  code-prefilled main item or the Settings-page Validate button (JS-built from
  the live preview, admin-only).
- Only the JSON-LD module file exists. llm_txt, ai_robots, woocommerce are
  disabled dashboard cards only. No placeholder module files.
- Phase 8 (PUC auto-update) complete (user-confirmed end-to-end): PUC v5.7
  vendored into `lib/`, wired via
  `\YahnisElsts\PluginUpdateChecker\v5p7\PucFactory::buildUpdateChecker`
  with the hardcoded `ARWP_GITHUB_REPO` constant (no token; re-add a token
  field only for a private repo) + `setBranch( 'main' )` to match the repo's
  default branch. Loader `file_exists()`-guards module requires. IMPORTANT
  update mechanics: PUC detection priority = latest Release → highest-version
  tag → `main` branch (`getUpdateDetectionStrategies()` GitHubApi.php:351-367).
  A stale Release (the old `v1.0.0`) WINS over the branch and blocks updates —
  that's exactly what happened for 1.0.2/1.0.3 until the `v1.0.3` GitHub
  Release was published (a tag alone was NOT enough). Every functional version
  needs its own GitHub Release. PUC's `fixDirectoryName` filter
  (UpdateChecker.php:1045-1106) renames the version-suffixed zip folder
  (`agent-ready-wp-1.0.3/`) back to `agent-ready-wp/` before install, so GitHub
  zip folder names are update-safe. 1.0.3 update verified end-to-end on this
  box: release published → WP detected → update installed successfully.
  Since 1.0.4 PUC also calls `getVcsApi()->enableReleaseAssets( '/\.zip($|[?#])/i' )`
  (agent-ready-wp.php) so updates download the clean release asset
  `agent-ready-wp.zip` instead of GitHub's auto source archive — the plugin
  folder stays free of repo-only files (index.html, .github, AGENTS.md,
  .gitignore, .nojekyll). PUC only sees API-listed assets (user-uploaded; the
  auto "Source code (zip)" is not among them), and falls back to the source
  archive if a Release lacks a matching asset (GitHubApi.php:100-135).
  GOTCHA (1.0.3→1.0.4, live): the update IMMEDIATELY after enabling release
  assets STILL ships repo files, because the download is performed by the
  PREVIOUSLY-INSTALLED version's PUC code (1.0.3 lacked the flag, so it fetched
  the source archive). Clean updates only start with the NEXT hop — when the
  installed version that performs the download already has the flag. Don't
  diagnose this as a PUC/CI bug; it self-heals (WP's upgrader deletes the whole
  plugin folder before extracting the clean asset).
- Phase 8 Windows caveat: WP's upgrader deletes the whole plugin folder before
  extracting. On Windows that delete can fail with "Could not remove the old
  plugin" if ANY process holds a handle on the folder (opencode's own working
  directory, Explorer, VS Code, or Windows Defender real-time scan). Fix:
  close all windows on the plugin folder and pause Defender real-time
  protection (`Set-MpPreference -DisableRealtimeMonitoring $true`) before
  updating. This is environmental, not a PUC/code bug. Detection, download,
  and install were all proven on this box.
- Git repo: pushed to `github.com/akosiraffytot/agent-ready-wp` (branch `main`).
  History: `21a540b` initial + `6fb8f05` docs + `c8d3746` docs (last sync point,
  1.0.0). The broken 1.0.1 experiment was force-pushed away (`git push --force
  origin main`, `3c08f7a...c8d3746`) and exists only in the local reflog.
  **1.0.2** = commit `361dce0`. **1.0.3** (admin-bar Validate submenu +
  readme.txt + header Description sync) = commit `0284834`. Post-release docs:
  `6ba621d` (record 1.0.3 hash) + `01a3c84` (skills section). Tag `v1.0.3` and
  GitHub Release `v1.0.3` published 2026-08-06; update detected + installed
  end-to-end on this box. 1.0.4 (enableReleaseAssets) = commit `5fc1410`.
  Post-release docs: `deb3436` (1.0.4 hash + release-tag lesson) + `5585e84`
  (CI rsync blacklist). Tag `v1.0.4` + GitHub Release `v1.0.4` published
  2026-08-06; the workflow auto-attached the clean `agent-ready-wp.zip`
  (asset present, ~197 KB). The 1.0.3→1.0.4 hop on two live sites shipped the
  5 repo-only files (see the GOTCHA above) — expected, self-heals at 1.0.5.
  LESSON (1.0.3 release re-create): the original `v1.0.3` tag pointed at
  `01a3c84`, which predates `.github/workflows/` — a `release: published`
  event resolves the workflow from the RELEASE'S COMMIT TREE, not main HEAD,
  so it found no workflow and attached no asset. Always create the release
  tag on a commit that contains the workflow file.
  Committed files: plugin code + `AGENTS.md` + `readme.txt` + `CHANGELOG.TXT`.
  `TODO.MD` / `DEVELOPMENT_PLAN.md` / `recommended-fix-gemini.md` are
  `.gitignore`d local-only. WARNING: the destructive Phase 8 install test
  wiped those three local-only files and they were never committed, so they
  are NOT recoverable from git. Still ask before running git commands.

## Stack constraints

- WP 6.9+, PHP 7.4 floor — no PHP 8-only syntax (`match`, union types, `str_contains` without fallback).
- WPCS: nonce on every form/AJAX, `sanitize_*` on input, `esc_*` on output, `current_user_can( 'manage_options' )` for admin/AJAX, `$wpdb->prepare` for SQL, i18n textdomain `arwp`, no `$_POST` without `wp_unslash` + sanitize.
- JSON-LD output: single `<script type="application/ld+json">` on `wp_head` priority 5, via `wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP )`. Never concatenate raw JSON strings.
- No `ACF`/`WooCommerce` calls outside `function_exists()` / `class_exists()` guards.

## Verification

- Lint: `php -l <file>` (PHP 8.3 CLI available on this Laragon box). Run after every phase.
- PHPCS with `WordPress` standard: `phpcs --standard=WordPress <file>` must return 0 errors (0 warnings) on every plugin PHP file (all 6 files verified clean as of 1.2.0). Run `phpcbf --standard=WordPress` to auto-fix formatting (CRLF, alignment) before manual fixes. Known documented false positives are suppressed with `// phpcs:ignore` (JSON output already hex-escaped by `wp_json_encode`; `$_POST` passed to per-field `arwp_sanitize_*` callbacks; `sameAs` url-list sanitizer). Manual nonce/capability audit otherwise.
- No automated test framework. User manually tests in `plugindev` WP install; you must supply concrete test steps per phase.

## Versioning & releases

- SemVer: PATCH = bug fixes, MINOR = backwards-compatible features, MAJOR = breaking changes. Docs-only changes (readme.txt, plugin description, AGENTS.md, CHANGELOG.TXT) do NOT bump the version.
- PUC offers an update only when the remote `Version:` header > installed version. A docs-only commit at the current version is correct — installed sites receive it with the next functional release.
- Every functional release = ONE commit containing all of: `Version:` header bump + `ARWP_VERSION` bump (agent-ready-wp.php), new `readme.txt` changelog section + `Stable tag:` update, and `CHANGELOG.TXT` update. PUC reads the header from `main`, so keep version + notes in the same commit.
- Every functional release ALSO needs a GitHub Release (`vN.N.N` tag + published Release). PUC priority = latest Release → highest-version tag → `main` branch, so an old Release on the repo blocks all newer tags/branch versions until a new Release is published.
- Publishing a Release auto-runs `.github/workflows/build-release-zip.yml`, which `rsync`s the repo root into an `agent-ready-wp/` staging folder EXCLUDING a blacklist (`.git`, `.github`, `.gitignore`, `AGENTS.md`, `index.html`, `.nojekyll`, dev-only `TODO.MD`/`DEVELOPMENT_PLAN.md`/`recommended-fix-gemini.md`/`session-ses_*.md`, `staging`), zips it, and uploads `agent-ready-wp.zip` to that Release as an asset (since commit `5585e84`). New plugin files/dirs at the repo root are auto-included; a NEW repo-only root file requires adding it to the exclude list. The site at `akosiraffytot.github.io/agent-ready-wp` (GitHub Pages, source = `main` root, `index.html` + `.nojekyll`) has a Download button pointing at `releases/latest/download/agent-ready-wp.zip` (permanent latest-asset URL). If a Release has no `agent-ready-wp.zip` asset, that URL 404s — keep the asset attached on every functional release. Re-publishing a Release re-triggers the workflow (`--clobber` overwrites the asset).
- `readme.txt` `Stable tag:` must always match the released code version.
- Keep the plugin header `Description:` in sync with the readme short description (≤150 chars, no markup).
- Push after any release commit; ask before running git commands.
- Current released version: 1.2.0 (SHIPPED: commit `3f725aa`; tag `v1.2.0` +
  GitHub Release created by the user on the GitHub UI; PUC auto-update
  delivered to live sites — confirmed "we have the v1.2.0 on our websites").
  1.2.0 = multi-type Organization selector (pill/token multi-select, `@type`
  array + unioned conditional sections) + per-niche field clusters (dining,
  hospitality, medical, education, civic_community — 14 options) +
  "Location & Local Information" rename/coverage + Custom JSON-LD escape
  hatch (4 textareas, `name|JSON`, `{home}`, `#` comments, duplicate names →
  arrays, invalid lines dropped with a notice) + WPCS-clean pass.
- 1.2.1 (in progress, PREPPED — commit NOT yet made): geo fix — the
  Organization node's `latitude`/`longitude` were cast to `(float)`, so
  servers with a high php.ini `serialize_precision` dumped the full ~54-digit
  IEEE754 expansion (e.g. `34.85698000000000007503331289626657962799072265625`)
  in the JSON output. FIX: emit the sanitized string as-is
  (module-json-ld.php, `arwp_jsonld_build_global_nodes()`, geo block) —
  schema.org's `latitude`/`longitude` accept Text, so the output is clean on
  any server. Release bookkeeping prepped: header + `ARWP_VERSION` 1.2.1,
  readme `Stable tag` + changelog + upgrade notice, `CHANGELOG.TXT`, AGENTS.md,
  TODO.MD. Commit + push + tag `v1.2.1` + GitHub Release + PUC update test
  still pending user go-ahead.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
