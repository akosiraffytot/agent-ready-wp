<?php
/**
 * Agent Ready WP — llms.txt module.
 *
 * Serves a virtual /llms.txt — the community-standard Markdown curated index
 * for AI agents (llmstxt.org). Phase 8: settings form, Core Pages section
 * (menu picker + static-pages fallback), Recent Articles under `## Optional`,
 * opt-in CPT sections, manual Markdown block, eligibility/dedupe/limits,
 * live preview AJAX + default serve.
 *
 * @package Agent_Ready_WP
 */

defined( 'ABSPATH' ) || exit;

const ARWP_LLMS_DOCS_FORMAT  = 'https://llmstxt.org/#format';
const ARWP_LLMS_DOCS_EXAMPLE = 'https://llmstxt.org/#example';

/**
 * Whether the llms.txt module is active.
 *
 * The loader only requires this file when the module is enabled, so the
 * front-end gate is defense in depth.
 *
 * @return bool
 */
function arwp_llms_is_active() {
	$active = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );

	return ! empty( $active['llm_txt'] );
}

/**
 * Public post type slugs eligible for opt-in sections, core types excluded.
 *
 * @return string[]
 */
function arwp_llms_public_cpt_slugs() {
	$types = get_post_types( array( 'public' => true ), 'names' );

	return array_values( array_diff( $types, array( 'post', 'page', 'attachment' ) ) );
}

/**
 * Register the llms.txt settings submenu.
 */
function arwp_llms_register_settings_menu() {
	add_submenu_page(
		'arwp-dashboard',
		__( 'LLMS.TXT Settings', 'arwp' ),
		__( 'LLMS.TXT', 'arwp' ),
		'manage_options',
		'arwp-llms',
		'arwp_llms_render_settings'
	);
}
add_action( 'admin_menu', 'arwp_llms_register_settings_menu' );

/**
 * Register the llms.txt options.
 */
function arwp_llms_register_settings() {
	add_settings_section(
		'arwp_llms_section_identity',
		__( 'Site Identity', 'arwp' ),
		'arwp_llms_section_identity_cb',
		'arwp-llms'
	);

	add_settings_field(
		'arwp_llms_title',
		__( 'Site Title', 'arwp' ),
		'arwp_llms_render_title_field',
		'arwp-llms',
		'arwp_llms_section_identity'
	);

	add_settings_field(
		'arwp_llms_summary',
		__( 'Summary', 'arwp' ),
		'arwp_llms_render_summary_field',
		'arwp-llms',
		'arwp_llms_section_identity'
	);

	add_settings_field(
		'arwp_llms_intro',
		__( 'AI Context', 'arwp' ),
		'arwp_llms_render_intro_field',
		'arwp-llms',
		'arwp_llms_section_identity'
	);

	add_settings_section(
		'arwp_llms_section_sources',
		__( 'Content Sources', 'arwp' ),
		'arwp_llms_section_sources_cb',
		'arwp-llms'
	);

	add_settings_field(
		'arwp_llms_section_core',
		__( 'Core Pages', 'arwp' ),
		'arwp_llms_render_core_field',
		'arwp-llms',
		'arwp_llms_section_sources'
	);

	add_settings_field(
		'arwp_llms_menu_source',
		__( 'Menu Source', 'arwp' ),
		'arwp_llms_render_menu_source_field',
		'arwp-llms',
		'arwp_llms_section_sources'
	);

	add_settings_field(
		'arwp_llms_section_blog',
		__( 'Recent Articles', 'arwp' ),
		'arwp_llms_render_blog_field',
		'arwp-llms',
		'arwp_llms_section_sources'
	);

	add_settings_field(
		'arwp_llms_blog_count',
		__( 'Post Count', 'arwp' ),
		'arwp_llms_render_blog_count_field',
		'arwp-llms',
		'arwp_llms_section_sources'
	);

	foreach ( arwp_llms_public_cpt_slugs() as $cpt ) {
		$cpt_object = get_post_type_object( $cpt );

		add_settings_field(
			'arwp_llms_cpt_' . $cpt,
			$cpt_object->labels->name,
			'arwp_llms_render_cpt_field',
			'arwp-llms',
			'arwp_llms_section_sources',
			array( 'type' => $cpt )
		);

		add_settings_field(
			'arwp_llms_cpt_' . $cpt . '_count',
			/* translators: %s: post type label. */
			sprintf( __( '%s Count', 'arwp' ), $cpt_object->labels->name ),
			'arwp_llms_render_cpt_count_field',
			'arwp-llms',
			'arwp_llms_section_sources',
			array( 'type' => $cpt )
		);
	}

	add_settings_section(
		'arwp_llms_section_manual',
		__( 'Manual Content', 'arwp' ),
		'arwp_llms_section_manual_cb',
		'arwp-llms'
	);

	add_settings_field(
		'arwp_llms_manual',
		__( 'Manual Content', 'arwp' ),
		'arwp_llms_render_manual_field',
		'arwp-llms',
		'arwp_llms_section_manual'
	);

	register_setting( 'arwp_llms_options', 'arwp_llms_title', 'sanitize_text_field' );
	register_setting( 'arwp_llms_options', 'arwp_llms_summary', 'sanitize_textarea_field' );
	register_setting( 'arwp_llms_options', 'arwp_llms_intro', 'sanitize_textarea_field' );
	register_setting( 'arwp_llms_options', 'arwp_llms_section_core', 'absint' );
	register_setting( 'arwp_llms_options', 'arwp_llms_menu_source', 'sanitize_key' );
	register_setting( 'arwp_llms_options', 'arwp_llms_section_blog', 'absint' );
	register_setting( 'arwp_llms_options', 'arwp_llms_blog_count', 'absint' );
	register_setting( 'arwp_llms_options', 'arwp_llms_manual', 'sanitize_textarea_field' );

	foreach ( arwp_llms_public_cpt_slugs() as $cpt ) {
		register_setting( 'arwp_llms_options', 'arwp_llms_cpt_' . $cpt, 'absint' );
		register_setting( 'arwp_llms_options', 'arwp_llms_cpt_' . $cpt . '_count', 'absint' );
	}
}
add_action( 'admin_init', 'arwp_llms_register_settings' );

/**
 * Render the Site Identity section intro.
 */
function arwp_llms_section_identity_cb() {
	echo '<p>' . esc_html__( 'These settings build the top of the file — the title and introduction an AI assistant sees first, before it follows any links.', 'arwp' ) . '</p>';
}

/**
 * Render the Content Sources section intro.
 */
function arwp_llms_section_sources_cb() {
	echo '<p>' . esc_html__( 'Choose which parts of your website are listed in the file. Each enabled source becomes a section of links that an AI assistant can follow.', 'arwp' ) . '</p>';
}

/**
 * Render the Manual Content section intro.
 */
function arwp_llms_section_manual_cb() {
	echo '<p>' . esc_html__( 'Add content by hand that the automatic sections can\'t produce.', 'arwp' ) . '</p>';
}

/**
 * Render the Title field.
 */
function arwp_llms_render_title_field() {
	arwp_text_field(
		'arwp_llms_title',
		get_bloginfo( 'name' ),
		'text',
		__( 'The name of your site, shown as the big heading at the very top of the file. This is the only part of the file that is required. AI assistants use it to know exactly which website or organization they are looking at. Leave this empty to use your site name from Settings → General.', 'arwp' ),
		ARWP_LLMS_DOCS_FORMAT
	);
}

/**
 * Render the Summary field.
 */
function arwp_llms_render_summary_field() {
	arwp_textarea_field(
		'arwp_llms_summary',
		__( 'A one or two sentence introduction shown right below the title, styled as a quote. It should sum up what your site is about — who it is for and what it offers — so an AI assistant understands the big picture before reading any links. Leave empty to use your site tagline from Settings → General.', 'arwp' ),
		ARWP_LLMS_DOCS_FORMAT
	);
}

/**
 * Render the AI Context field.
 *
 * Free-form Markdown needs more height than the shared textarea helper's
 * five rows, so this renderer mirrors arwp_textarea_field() with rows="8".
 */
function arwp_llms_render_intro_field() {
	$value = get_option( 'arwp_llms_intro', '' );
	?>
	<textarea class="large-text" rows="8" name="arwp_llms_intro"><?php echo esc_textarea( $value ); ?></textarea>
	<?php
	arwp_field_description( __( 'Extra background information you want an AI assistant to know before it visits any of the pages in the file. Write a few paragraphs explaining what your site is, who it helps, what topics it covers, and anything else that would help an AI answer questions about you correctly. Plain text with simple formatting only — headings are not allowed in this section. Leave empty if you do not need it.', 'arwp' ), ARWP_LLMS_DOCS_FORMAT );
}

/**
 * Render the Manual Content field.
 *
 * Free-form Markdown needs more height than the shared textarea helper's
 * five rows, so this renderer mirrors arwp_textarea_field() with rows="8".
 */
function arwp_llms_render_manual_field() {
	$value = get_option( 'arwp_llms_manual', '' );
	?>
	<textarea class="large-text" rows="8" name="arwp_llms_manual"><?php echo esc_textarea( $value ); ?></textarea>
	<?php
	arwp_field_description( __( 'Everything you type here is added to the file exactly as written — your way to include anything the automatic sections cannot generate: links to external sites, extra sections with their own headings, notes for AI assistants, or contact details. Use simple formatting: a line starting with a dash and a linked title, like "- [Our Pricing](https://example.com/pricing)". Leave empty to add nothing.', 'arwp' ), ARWP_LLMS_DOCS_EXAMPLE );
}

/**
 * Render the Core Pages checkbox.
 */
function arwp_llms_render_core_field() {
	arwp_llms_checkbox_field(
		'arwp_llms_section_core',
		__( 'Include the Core Pages section in the file.', 'arwp' ),
		__( 'Lists your most important pages as the main section of the file — the pages an AI assistant should look at first, such as Home, About, Services, and Contact. The list is built automatically from the menu you choose below.', 'arwp' ),
		ARWP_LLMS_DOCS_FORMAT
	);
}

/**
 * Render the Menu source select.
 */
function arwp_llms_render_menu_source_field() {
	$current = get_option( 'arwp_llms_menu_source', 'auto' );
	$current = sanitize_key( $current );
	$menus   = arwp_llms_nav_menu_options();
	?>
	<select id="arwp_llms_menu_source" name="arwp_llms_menu_source">
		<option value="auto" <?php selected( $current, 'auto' ); ?>><?php esc_html_e( 'Automatic (first assigned location)', 'arwp' ); ?></option>
		<?php foreach ( $menus as $menu_id => $menu_name ) : ?>
			<option value="<?php echo esc_attr( $menu_id ); ?>" <?php selected( $current, $menu_id ); ?>><?php echo esc_html( $menu_name ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
	arwp_field_description( __( 'Which navigation menu is used to build the Core Pages list. Only the top-level items (the main menu entries, not the dropdowns) are included. Choose "Automatic" to let the plugin use the first menu assigned to a menu location; if your theme has no menus set up, it falls back to listing your top-level pages instead.', 'arwp' ), ARWP_LLMS_DOCS_FORMAT );
}

/**
 * Render the Recent Articles checkbox.
 */
function arwp_llms_render_blog_field() {
	arwp_llms_checkbox_field(
		'arwp_llms_section_blog',
		__( 'Include the Recent Articles section in the file.', 'arwp' ),
		__( 'Adds your latest blog posts to an "Optional" section at the end of the file. The "Optional" label tells AI assistants these links are secondary — they can skip them when they only need the essentials. A good way to show that your site has fresh, ongoing content.', 'arwp' ),
		ARWP_LLMS_DOCS_FORMAT
	);
}

/**
 * Render the Post Count field.
 */
function arwp_llms_render_blog_count_field() {
	$value = absint( get_option( 'arwp_llms_blog_count', 10 ) );
	?>
	<input class="small-text" type="number" min="0" max="100" name="arwp_llms_blog_count" value="<?php echo esc_attr( $value ); ?>">
	<?php
	arwp_field_description( __( 'How many of your most recent posts to include. Set to 0 to turn the Recent Articles section off entirely. The maximum is 100.', 'arwp' ), ARWP_LLMS_DOCS_FORMAT );
}

/**
 * Render a custom post type checkbox.
 *
 * @param array $args Field args, with the CPT slug in 'type'.
 */
function arwp_llms_render_cpt_field( $args ) {
	$type       = sanitize_key( $args['type'] );
	$cpt_object = get_post_type_object( $type );
	$label      = $cpt_object ? $cpt_object->labels->name : $type;

	arwp_llms_checkbox_field(
		'arwp_llms_cpt_' . $type,
		/* translators: %s: post type label. */
		sprintf( __( 'List %s in the file.', 'arwp' ), $label ),
		sprintf(
			/* translators: %s: post type label. */
			__( 'Adds a section listing your %s content — for example your products, case studies, or team members. Only turn this on if that content is important for AI assistants to find.', 'arwp' ),
			$label
		),
		ARWP_LLMS_DOCS_FORMAT,
		0
	);
}

/**
 * Render a custom post type item count field.
 *
 * @param array $args Field args, with the CPT slug in 'type'.
 */
function arwp_llms_render_cpt_count_field( $args ) {
	$type       = sanitize_key( $args['type'] );
	$cpt_object = get_post_type_object( $type );
	$label      = $cpt_object ? $cpt_object->labels->name : $type;
	$value      = absint( get_option( 'arwp_llms_cpt_' . $type . '_count', 10 ) );
	?>
	<input class="small-text" type="number" min="0" max="100" name="arwp_llms_cpt_<?php echo esc_attr( $type ); ?>_count" value="<?php echo esc_attr( $value ); ?>">
	<?php
	arwp_field_description(
		sprintf(
		/* translators: %s: post type label. */
			__( 'How many %s items to include. Set to 0 to hide this section. The maximum is 100.', 'arwp' ),
			$label
		),
		ARWP_LLMS_DOCS_FORMAT
	);
}

/**
 * Render an on/off checkbox option.
 *
 * @param string $name        Option name.
 * @param string $label       Label text next to the checkbox.
 * @param string $description Help text below the toggle.
 * @param string $learn_more  Optional documentation URL appended to the description.
 * @param int    $default_value Default value for an unset option.
 */
function arwp_llms_checkbox_field( $name, $label, $description = '', $learn_more = '', $default_value = 1 ) {
	$value = get_option( $name, $default_value );
	?>
	<label for="<?php echo esc_attr( $name ); ?>">
		<input type="checkbox" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $value, 1 ); ?>>
		<?php echo esc_html( $label ); ?>
	</label>
	<?php
	if ( '' !== $description ) {
		arwp_field_description( $description, $learn_more );
	}
}

/**
 * Registered nav menus as an id => name map.
 *
 * @return array
 */
function arwp_llms_nav_menu_options() {
	$menus   = get_terms(
		array(
			'taxonomy'   => 'nav_menu',
			'hide_empty' => false,
		)
	);
	$options = array();

	if ( is_array( $menus ) ) {
		foreach ( $menus as $menu ) {
			$options[ $menu->term_id ] = $menu->name;
		}
	}

	return $options;
}

/**
 * Structural self-checks surfaced on the settings page.
 *
 * Reports problems with the current live build: empty item titles, invalid
 * item URLs, duplicate normalized URLs within a section, and invalid or
 * untitled links in the manual Markdown block.
 *
 * @return string[] Human-readable problems (empty when all checks pass).
 */
function arwp_llms_self_checks() {
	$problems = array();

	foreach ( arwp_llms_build_sections() as $section ) {
		$seen = array();

		foreach ( $section['items'] as $item ) {
			if ( '' === trim( $item['text'] ) ) {
				$problems[] = sprintf(
					/* translators: %s: section title. */
					__( 'Section "%s" has an item with an empty title.', 'arwp' ),
					$section['title']
				);
			}

			if ( ! arwp_llms_valid_url( $item['url'] ) ) {
				$problems[] = sprintf(
					/* translators: 1: section title, 2: invalid URL. */
					__( 'Section "%1$s" has an invalid URL: %2$s', 'arwp' ),
					$section['title'],
					$item['url']
				);
			}

			$key = arwp_llms_normalize_url( $item['url'] );

			if ( isset( $seen[ $key ] ) ) {
				$problems[] = sprintf(
					/* translators: 1: section title, 2: duplicate URL. */
					__( 'Section "%1$s" lists the same URL twice: %2$s', 'arwp' ),
					$section['title'],
					$item['url']
				);
			}

			$seen[ $key ] = true;
		}
	}

	$manual = sanitize_textarea_field( get_option( 'arwp_llms_manual', '' ) );
	$lines  = preg_split( '/\r\n|\r|\n/', $manual );

	foreach ( $lines as $line ) {
		if ( ! preg_match_all( '/\[([^\]]*)\]\(([^)]+)\)/', $line, $matches, PREG_SET_ORDER ) ) {
			continue;
		}

		foreach ( $matches as $match ) {
			$title = trim( $match[1] );
			$url   = trim( $match[2] );

			if ( '' === $title ) {
				$problems[] = __( 'The Manual Markdown block has a link with an empty title.', 'arwp' );
			}

			if ( ! arwp_llms_valid_url( $url ) ) {
				$problems[] = sprintf(
					/* translators: %s: invalid URL from the manual block. */
					__( 'The Manual Markdown block has an invalid link URL: %s', 'arwp' ),
					$url
				);
			}
		}
	}

	return $problems;
}

/**
 * Whether a URL is an absolute http/https URL with a host.
 *
 * Uses wp_parse_url instead of filter_var() so `.test` and other local
 * hostnames (which filter_var rejects) validate.
 *
 * @param string $url URL to check.
 * @return bool
 */
function arwp_llms_valid_url( $url ) {
	$parts = wp_parse_url( $url );

	if ( ! is_array( $parts ) ) {
		return false;
	}

	return ! empty( $parts['host'] ) && isset( $parts['scheme'] ) && in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true );
}

/**
 * Render the llms.txt settings page.
 */
function arwp_llms_render_settings() {
	$problems = arwp_llms_self_checks();
	?>
	<div class="wrap">
		<style>
			/*
			 * Dependent-field pattern: hide a field row while its parent
			 * toggle is unchecked. The dependent row must immediately follow
			 * the toggle's row in the settings table.
			 */
			tr:has(#arwp_llms_section_core:not(:checked)) + tr {
				display: none;
			}

			tr:has(#arwp_llms_section_blog:not(:checked)) + tr {
				display: none;
			}

			<?php foreach ( arwp_llms_public_cpt_slugs() as $cpt ) : ?>
			tr:has(#arwp_llms_cpt_<?php echo esc_html( $cpt ); ?>:not(:checked)) + tr {
				display: none;
			}
			<?php endforeach; ?>
		</style>
		<?php settings_errors(); ?>
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p><?php esc_html_e( 'An llms.txt file is a plain-text overview of your website that AI assistants (like ChatGPT and Claude) read to understand what your site is about and find its key pages. This page controls what goes into your site\'s /llms.txt.', 'arwp' ); ?></p>
		<?php foreach ( $problems as $problem ) : ?>
			<div class="notice notice-warning">
				<p><?php echo esc_html( $problem ); ?></p>
			</div>
		<?php endforeach; ?>
		<?php if ( file_exists( ABSPATH . 'llms.txt' ) ) : ?>
			<div class="notice notice-warning">
				<p>
					<?php
					esc_html_e( 'A physical llms.txt file exists in your site root. The server serves it directly, so this plugin\'s virtual /llms.txt is shadowed. Delete the file to use the plugin output.', 'arwp' );
					?>
				</p>
			</div>
		<?php endif; ?>

		<div class="arwp-llms-layout">
			<div class="arwp-llms-fields">
				<form action="options.php" method="post">
					<?php
					settings_fields( 'arwp_llms_options' );
					do_settings_sections( 'arwp-llms' );
					submit_button();
					?>
				</form>
			</div>

			<aside class="arwp-llms-preview">
				<h2 class="arwp-llms-preview-title"><?php esc_html_e( 'Live Preview', 'arwp' ); ?></h2>
				<p class="arwp-llms-preview-note"><?php esc_html_e( 'What /llms.txt serves. Updates as you change the settings.', 'arwp' ); ?></p>
				<pre id="arwp-llms-output" aria-live="polite"><?php echo esc_html( arwp_llms_build() ); ?></pre>
				<div class="arwp-llms-preview-actions">
					<a
						class="button button-secondary"
						href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>"
						target="_blank"
						rel="noopener noreferrer"
					>
						<?php esc_html_e( 'View /llms.txt', 'arwp' ); ?>
					</a>
					<button
						type="button"
						class="button"
						id="arwp-copy-llms"
						disabled
						data-copy="<?php esc_attr_e( 'Copy', 'arwp' ); ?>"
						data-copied="<?php esc_attr_e( 'Copied', 'arwp' ); ?>"
					>
						<?php esc_html_e( 'Copy', 'arwp' ); ?>
					</button>
				</div>
			</aside>
		</div>
	</div>
	<?php
}

/**
 * Build the /llms.txt Markdown content.
 *
 * Single source of truth: the live endpoint and the settings preview both call
 * this function so they can never diverge.
 *
 * Phase 6: H1 (title), blockquote summary (tagline), a free-form AI Context
 * block, generated sections (Core Pages from the picked menu, Recent
 * Articles under `## Optional`, opt-in CPT sections), then any manual
 * Markdown appended verbatim.
 *
 * @param array $values Optional option overrides from the preview AJAX.
 * @return string
 */
function arwp_llms_build( $values = array() ) {
	$lines = array();

	$title = arwp_llms_value( $values, 'arwp_llms_title', '' );
	$title = sanitize_text_field( $title );

	if ( '' === $title ) {
		$title = sanitize_text_field( get_bloginfo( 'name' ) );
	}

	if ( '' !== $title ) {
		$lines[] = '# ' . $title;
	}

	$summary = arwp_llms_value( $values, 'arwp_llms_summary', '' );
	$summary = sanitize_textarea_field( $summary );

	if ( '' === $summary ) {
		$summary = sanitize_textarea_field( get_bloginfo( 'description' ) );
	}

	$summary_lines = preg_split( '/\r\n|\r|\n/', $summary );

	foreach ( $summary_lines as $summary_line ) {
		$summary_line = trim( $summary_line );

		if ( '' !== $summary_line ) {
			$lines[] = '> ' . $summary_line;
		}
	}

	$intro = arwp_llms_value( $values, 'arwp_llms_intro', '' );
	$intro = sanitize_textarea_field( $intro );

	if ( '' !== trim( $intro ) ) {
		$lines[] = '';

		$intro_lines = preg_split( '/\r\n|\r|\n/', $intro );

		foreach ( $intro_lines as $intro_line ) {
			$lines[] = $intro_line;
		}
	}

	$sections = apply_filters( 'agent_ready_llms_sections', arwp_llms_build_sections( $values ), $values );

	if ( ! is_array( $sections ) ) {
		$sections = array();
	}

	foreach ( $sections as $section ) {
		if ( empty( $section['items'] ) ) {
			continue;
		}

		$lines[] = '';
		$lines[] = '## ' . ( $section['optional'] ? __( 'Optional', 'arwp' ) : $section['title'] );
		$lines[] = '';

		foreach ( $section['items'] as $item ) {
			$line = '- [' . $item['text'] . '](' . $item['url'] . ')';

			if ( '' !== $item['desc'] ) {
				$line .= ': ' . $item['desc'];
			}

			$lines[] = $line;
		}
	}

	$manual = arwp_llms_value( $values, 'arwp_llms_manual', '' );
	$manual = sanitize_textarea_field( $manual );

	if ( '' !== trim( $manual ) ) {
		$lines[] = '';

		foreach ( preg_split( '/\r\n|\r|\n/', $manual ) as $manual_line ) {
			$lines[] = $manual_line;
		}
	}

	if ( empty( $lines ) ) {
		return '';
	}

	$content = implode( "\n", $lines ) . "\n";

	// ponytail: output safety net for pathological configs; per-section caps already bound size.
	if ( strlen( $content ) > 512000 ) {
		return '';
	}

	return apply_filters( 'agent_ready_llms_content', $content, $sections, $values );
}

/**
 * Read an option value, preferring an override from the preview $values array.
 *
 * @param array  $values   Option overrides (may be empty).
 * @param string $name     Option name.
 * @param mixed  $fallback Fallback when the option is unset.
 * @return mixed
 */
function arwp_llms_value( $values, $name, $fallback = '' ) {
	if ( isset( $values[ $name ] ) ) {
		return $values[ $name ];
	}

	return get_option( $name, $fallback );
}

/**
 * Build the section model for the file.
 *
 * @param array $values Option overrides from the preview AJAX.
 * @return array
 */
function arwp_llms_build_sections( $values = array() ) {
	$sections = array();

	if ( '1' === (string) arwp_llms_value( $values, 'arwp_llms_section_core', 1 ) ) {
		$items = arwp_llms_core_items( $values );

		if ( ! empty( $items ) ) {
			$sections[] = array(
				'title'    => __( 'Core Pages', 'arwp' ),
				'optional' => false,
				'items'    => $items,
			);
		}
	}

	if ( '1' === (string) arwp_llms_value( $values, 'arwp_llms_section_blog', 1 ) ) {
		$items = arwp_llms_recent_items( $values );

		if ( ! empty( $items ) ) {
			$sections[] = array(
				'title'    => __( 'Recent Articles', 'arwp' ),
				'optional' => true,
				'items'    => $items,
			);
		}
	}

	$sections = array_merge( $sections, arwp_llms_cpt_sections( $values ) );

	return $sections;
}

/**
 * Build the opt-in sections for enabled public custom post types.
 *
 * One section per enabled CPT, titled from the type label, listing the latest
 * items (recent first, capped per the CPT's count option).
 *
 * @param array $values Option overrides from the preview AJAX.
 * @return array
 */
function arwp_llms_cpt_sections( $values = array() ) {
	$sections = array();

	foreach ( arwp_llms_public_cpt_slugs() as $cpt ) {
		if ( '1' !== (string) arwp_llms_value( $values, 'arwp_llms_cpt_' . $cpt, 0 ) ) {
			continue;
		}

		$items = arwp_llms_cpt_items( $values, $cpt );

		if ( empty( $items ) ) {
			continue;
		}

		$cpt_object = get_post_type_object( $cpt );

		$sections[] = array(
			'title'    => $cpt_object ? $cpt_object->labels->name : $cpt,
			'optional' => false,
			'items'    => $items,
		);
	}

	return $sections;
}

/**
 * Build the items for one custom post type's section.
 *
 * @param array  $values Option overrides from the preview AJAX.
 * @param string $cpt    Post type slug.
 * @return array
 */
function arwp_llms_cpt_items( $values = array(), $cpt = '' ) {
	$count = absint( arwp_llms_value( $values, 'arwp_llms_cpt_' . $cpt . '_count', 10 ) );
	$count = min( $count, 100 );

	if ( 0 === $count ) {
		return array();
	}

	$posts = get_posts(
		array(
			'numberposts' => $count,
			'post_status' => 'publish',
			'post_type'   => sanitize_key( $cpt ),
		)
	);

	return arwp_llms_items_from_posts( $posts );
}

/**
 * Build the Recent Articles items (latest published posts).
 *
 * @param array $values Option overrides from the preview AJAX.
 * @return array
 */
function arwp_llms_recent_items( $values = array() ) {
	$count = absint( arwp_llms_value( $values, 'arwp_llms_blog_count', 10 ) );
	$count = min( $count, 100 );

	if ( 0 === $count ) {
		return array();
	}

	$posts = get_posts(
		array(
			'numberposts' => $count,
			'post_status' => 'publish',
			'post_type'   => 'post',
		)
	);

	return arwp_llms_items_from_posts( $posts );
}

/**
 * Build the Core Pages items from the resolved menu or the pages fallback.
 *
 * @param array $values Option overrides from the preview AJAX.
 * @return array
 */
function arwp_llms_core_items( $values = array() ) {
	$menu_id = arwp_llms_resolve_menu( $values );

	if ( $menu_id ) {
		$menu_items = wp_get_nav_menu_items( $menu_id );

		if ( is_array( $menu_items ) ) {
			return arwp_llms_items_from_menu( $menu_items );
		}
	}

	return arwp_llms_items_from_pages();
}

/**
 * Resolve the menu source option to a nav menu term ID (or 0 for none).
 *
 * An explicit menu ID wins; "auto" resolves through the theme's registered
 * nav-menu locations, deduped by menu ID, first non-empty. Zero means the
 * caller should fall back to static pages.
 *
 * @param array $values Option overrides from the preview AJAX.
 * @return int
 */
function arwp_llms_resolve_menu( $values = array() ) {
	$source = arwp_llms_value( $values, 'arwp_llms_menu_source', 'auto' );
	$source = sanitize_key( $source );

	if ( '' !== $source && 'auto' !== $source ) {
		return absint( $source );
	}

	$locations = get_nav_menu_locations();

	if ( empty( $locations ) ) {
		return 0;
	}

	$assigned = array();

	foreach ( array_keys( get_registered_nav_menus() ) as $location ) {
		if ( ! empty( $locations[ $location ] ) ) {
			$assigned[ $locations[ $location ] ] = true;
		}
	}

	if ( empty( $assigned ) ) {
		return 0;
	}

	reset( $assigned );

	return key( $assigned );
}

/**
 * Whether a post may appear in /llms.txt.
 *
 * @param WP_Post $post Post object.
 * @return bool
 */
function arwp_llms_is_eligible_post( $post ) {
	if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
		return false;
	}

	if ( 'publish' !== $post->post_status ) {
		return false;
	}

	if ( ! is_post_type_viewable( $post->post_type ) ) {
		return false;
	}

	if ( false === get_permalink( $post->ID ) ) {
		return false;
	}

	if ( post_password_required( $post ) ) {
		return false;
	}

	return true;
}

/**
 * Normalize a URL for duplicate comparison within a section.
 *
 * Strips the fragment and drops any trailing slash so `/about/` and `/about`
 * compare equal. Used only as a dedupe key, never as the emitted URL.
 *
 * @param string $url URL to normalize.
 * @return string
 */
function arwp_llms_normalize_url( $url ) {
	$url = (string) strtok( $url, '#' );

	return untrailingslashit( $url );
}

/**
 * Remove items whose normalized URL repeats within the section.
 *
 * First occurrence wins.
 *
 * @param array $items Section items.
 * @return array
 */
function arwp_llms_dedupe_items( $items ) {
	$seen  = array();
	$clean = array();

	foreach ( $items as $item ) {
		$key = arwp_llms_normalize_url( $item['url'] );

		if ( isset( $seen[ $key ] ) ) {
			continue;
		}

		$seen[ $key ] = true;
		$clean[]      = $item;
	}

	return $clean;
}

/**
 * Convert top-level nav menu items into section items.
 *
 * @param array $menu_items Items from wp_get_nav_menu_items().
 * @return array
 */
function arwp_llms_items_from_menu( $menu_items ) {
	$items  = array();
	$depth  = max( 1, absint( apply_filters( 'agent_ready_llms_menu_depth', 1 ) ) );
	$depths = array();

	foreach ( $menu_items as $menu_item ) {
		$parent                         = (int) $menu_item->menu_item_parent;
		$depths[ (int) $menu_item->ID ] = ( 0 === $parent ) ? 1 : ( isset( $depths[ $parent ] ) ? $depths[ $parent ] + 1 : 1 );
	}

	foreach ( $menu_items as $menu_item ) {
		if ( ! isset( $depths[ (int) $menu_item->ID ] ) || $depths[ (int) $menu_item->ID ] > $depth ) {
			continue;
		}

		$menu_post = null;

		if ( ! empty( $menu_item->object_id ) ) {
			$menu_post = get_post( $menu_item->object_id );

			if ( $menu_post && ! arwp_llms_is_eligible_post( $menu_post ) ) {
				continue;
			}
		}

		$url = trim( (string) $menu_item->url );

		if ( '' === $url ) {
			continue;
		}

		$text = arwp_llms_clean_title( $menu_item->title );

		if ( '' === $text ) {
			continue;
		}

		$description = trim( (string) $menu_item->description );

		if ( '' === $description && $menu_post ) {
			$description = arwp_llms_post_description( $menu_post->ID );
		}

		$item = array(
			'text' => $text,
			'url'  => $url,
			'desc' => arwp_llms_limit_description( $description ),
		);

		if ( ! apply_filters( 'agent_ready_llms_include_item', true, $item, $menu_post ) ) {
			continue;
		}

		$item['desc'] = apply_filters( 'agent_ready_llms_item_description', $item['desc'], $menu_post );

		$items[] = $item;
	}

	return arwp_llms_dedupe_items( $items );
}

/**
 * Convert top-level static pages into section items (menu fallback).
 *
 * @return array
 */
function arwp_llms_items_from_pages() {
	$pages = get_pages( array( 'parent' => 0 ) );

	return arwp_llms_items_from_posts( $pages );
}

/**
 * Convert a list of posts into section items.
 *
 * @param WP_Post[] $posts Post objects.
 * @return array
 */
function arwp_llms_items_from_posts( $posts ) {
	$items = array();

	foreach ( $posts as $post ) {
		if ( ! arwp_llms_is_eligible_post( $post ) ) {
			continue;
		}

		$url = get_permalink( $post->ID );

		if ( false === $url ) {
			continue;
		}

		$text = arwp_llms_clean_title( get_the_title( $post->ID ) );

		if ( '' === $text ) {
			continue;
		}

		$description = apply_filters( 'agent_ready_llms_item_description', arwp_llms_post_description( $post->ID ), $post );

		$item = array(
			'text' => $text,
			'url'  => $url,
			'desc' => $description,
		);

		if ( ! apply_filters( 'agent_ready_llms_include_item', true, $item, $post ) ) {
			continue;
		}

		$items[] = $item;
	}

	return arwp_llms_dedupe_items( $items );
}

/**
 * Description for a post: excerpt, then sanitized trimmed content.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function arwp_llms_post_description( $post_id ) {
	$excerpt = trim( (string) get_the_excerpt( $post_id ) );

	if ( '' !== $excerpt ) {
		return arwp_llms_limit_description( $excerpt );
	}

	$post = get_post( $post_id );

	if ( ! $post ) {
		return '';
	}

	$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$content = trim( (string) preg_replace( '/\s+/', ' ', $content ) );

	return arwp_llms_limit_description( wp_trim_words( $content, 40 ) );
}

/**
 * Cap a description at 300 characters.
 *
 * @param string $desc Description.
 * @return string
 */
function arwp_llms_limit_description( $desc ) {
	$desc = trim( $desc );

	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $desc, 0, 300 );
	}

	return substr( $desc, 0, 300 );
}

/**
 * Normalize a title for safe use inside Markdown link text.
 *
 * @param string $title Raw title.
 * @return string
 */
function arwp_llms_clean_title( $title ) {
	$title = preg_replace( '/\r\n|\r|\n/', ' ', (string) $title );
	$title = str_replace( array( '[', ']' ), array( '\\[', '\\]' ), $title );

	return trim( $title );
}

/**
 * Serve the virtual /llms.txt file on template_redirect.
 *
 * No physical file and no rewrite rules: when the request path is exactly
 * `llms.txt` and the module is active, emit the built Markdown as text/plain.
 * An empty build leaves the request untouched so WordPress 404s normally.
 */
function arwp_llms_render() {
	global $wp;

	if ( ! arwp_llms_is_active() ) {
		return;
	}

	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_feed() ) {
		return;
	}

	if ( ! isset( $wp->request ) || 'llms.txt' !== $wp->request ) {
		return;
	}

	$content = arwp_llms_build();

	if ( '' === trim( $content ) ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset', 'UTF-8' ) );

	// phpcs:ignore WordPress.Security.EscapeOutput -- Served as text/plain, not HTML; content is built from sanitized options.
	echo $content;
	exit;
}
add_action( 'template_redirect', 'arwp_llms_render' );

/**
 * AJAX handler: build the /llms.txt preview from unsaved form values.
 *
 * Mirrors the JSON-LD preview handler: whitelisted option names, each routed
 * to the same sanitizer its register_setting() call uses, then the shared
 * builder so the preview can never diverge from the live endpoint.
 */
function arwp_ajax_preview_llms() {
	check_ajax_referer( 'arwp_preview_llms', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'arwp' ) ) );
	}

	$fields = array(
		'arwp_llms_title'        => 'sanitize_text_field',
		'arwp_llms_summary'      => 'sanitize_textarea_field',
		'arwp_llms_intro'        => 'sanitize_textarea_field',
		'arwp_llms_section_core' => 'absint',
		'arwp_llms_menu_source'  => 'sanitize_key',
		'arwp_llms_section_blog' => 'absint',
		'arwp_llms_blog_count'   => 'absint',
		'arwp_llms_manual'       => 'sanitize_textarea_field',
	);

	foreach ( arwp_llms_public_cpt_slugs() as $cpt ) {
		$fields[ 'arwp_llms_cpt_' . $cpt ]            = 'absint';
		$fields[ 'arwp_llms_cpt_' . $cpt . '_count' ] = 'absint';
	}

	$values = array();

	foreach ( $fields as $name => $callback ) {
		if ( isset( $_POST[ $name ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized by each field's sanitizer callback.
			$values[ $name ] = call_user_func( $callback, wp_unslash( $_POST[ $name ] ) );
		}
	}

	// Unchecked checkboxes are absent from FormData; default them to off so
	// the preview reflects toggling a section off before saving.
	$checkboxes = array( 'arwp_llms_section_core', 'arwp_llms_section_blog' );

	foreach ( arwp_llms_public_cpt_slugs() as $cpt ) {
		$checkboxes[] = 'arwp_llms_cpt_' . $cpt;
	}

	foreach ( $checkboxes as $name ) {
		if ( ! isset( $values[ $name ] ) ) {
			$values[ $name ] = '0';
		}
	}

	wp_send_json_success( array( 'content' => arwp_llms_build( $values ) ) );
}
add_action( 'wp_ajax_arwp_preview_llms', 'arwp_ajax_preview_llms' );
