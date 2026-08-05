<?php
/**
 * Agent Ready WP — JSON-LD Schema module.
 *
 * Phase 2: per-module settings page. Phase 4 will append the @graph
 * front-end output here.
 *
 * @package Agent_Ready_WP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the JSON-LD settings submenu.
 */
function arwp_jsonld_register_settings_menu() {
	add_submenu_page(
		'arwp-dashboard',
		__( 'JSON-LD Schema Settings', 'arwp' ),
		__( 'JSON-LD Schema', 'arwp' ),
		'manage_options',
		'arwp-jsonld',
		'arwp_jsonld_render_settings'
	);
}
add_action( 'admin_menu', 'arwp_jsonld_register_settings_menu' );

/**
 * Hide the sidebar submenu when the module is off (prevents a flash before
 * JS shows/hides it dynamically). Uses querySelector + closest() so it works
 * in every browser, unlike a CSS :has() selector.
 */
function arwp_jsonld_hide_menu_when_off() {
	$active = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );

	if ( empty( $active['json_ld'] ) ) {
		?>
		<script>
		( function () {
			var link = document.querySelector( '#adminmenu .wp-submenu a[href$="page=arwp-jsonld"]' );
			if ( link && link.closest( 'li' ) ) {
				link.closest( 'li' ).style.display = 'none';
			}
		} )();
		</script>
		<?php
	}
}
add_action( 'admin_head', 'arwp_jsonld_hide_menu_when_off' );

/**
 * Register JSON-LD settings fields.
 */
function arwp_jsonld_register_settings() {
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_description', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_logo', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_same_as', array( 'sanitize_callback' => 'arwp_sanitize_url_list' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_knows_about', array( 'sanitize_callback' => 'arwp_sanitize_url_list' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_website_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_website_alternate_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_default_post_type', array( 'sanitize_callback' => 'arwp_sanitize_post_type' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_default_page_type', array( 'sanitize_callback' => 'arwp_sanitize_page_type' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_default_other_type', array( 'sanitize_callback' => 'arwp_sanitize_other_type' ) );

	add_settings_section(
		'arwp_jsonld_section',
		__( 'Organization Identity', 'arwp' ),
		'arwp_jsonld_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_org_name', __( 'Organization Name', 'arwp' ), 'arwp_field_org_name', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_org_description', __( 'Organization Description', 'arwp' ), 'arwp_field_org_description', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_org_logo', __( 'Organization Logo URL', 'arwp' ), 'arwp_field_org_logo', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_same_as', __( 'sameAs Profiles', 'arwp' ), 'arwp_field_same_as', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_knows_about', __( 'knowsAbout Topics', 'arwp' ), 'arwp_field_knows_about', 'arwp-jsonld', 'arwp_jsonld_section' );

	add_settings_section(
		'arwp_website_section',
		__( 'WebSite Identity', 'arwp' ),
		'arwp_website_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_website_name', __( 'WebSite Name', 'arwp' ), 'arwp_field_website_name', 'arwp-jsonld', 'arwp_website_section' );
	add_settings_field( 'arwp_schema_website_alternate_name', __( 'Alternate Name', 'arwp' ), 'arwp_field_website_alternate_name', 'arwp-jsonld', 'arwp_website_section' );

	add_settings_section(
		'arwp_mappings_section',
		__( 'Default Schema Mappings', 'arwp' ),
		'arwp_mappings_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_default_post_type', __( 'Default Post Schema', 'arwp' ), 'arwp_field_default_post_type', 'arwp-jsonld', 'arwp_mappings_section' );
	add_settings_field( 'arwp_schema_default_page_type', __( 'Default Page Schema', 'arwp' ), 'arwp_field_default_page_type', 'arwp-jsonld', 'arwp_mappings_section' );
	add_settings_field( 'arwp_schema_default_other_type', __( 'Default Other Post Type Schema', 'arwp' ), 'arwp_field_default_other_type', 'arwp-jsonld', 'arwp_mappings_section' );
}
add_action( 'admin_init', 'arwp_jsonld_register_settings' );

/**
 * Allowed post @type values.
 *
 * @return array
 */
function arwp_schema_post_types() {
	return array( 'BlogPosting', 'Article', 'NewsArticle' );
}

/**
 * Allowed page @type values.
 *
 * @return array
 */
function arwp_schema_page_types() {
	return array( 'WebPage', 'AboutPage', 'ContactPage' );
}

/**
 * Allowed @type values for other (custom) post types.
 *
 * @return array
 */
function arwp_schema_other_types() {
	return array( 'Article', 'BlogPosting', 'NewsArticle', 'WebPage' );
}

/**
 * Whitelist a post schema type.
 *
 * @param string $value Raw input.
 * @return string
 */
function arwp_sanitize_post_type( $value ) {
	$value = sanitize_text_field( $value );
	return in_array( $value, arwp_schema_post_types(), true ) ? $value : 'BlogPosting';
}

/**
 * Whitelist a page schema type.
 *
 * @param string $value Raw input.
 * @return string
 */
function arwp_sanitize_page_type( $value ) {
	$value = sanitize_text_field( $value );
	return in_array( $value, arwp_schema_page_types(), true ) ? $value : 'WebPage';
}

/**
 * Whitelist an "other" post type schema type.
 *
 * @param string $value Raw input.
 * @return string
 */
function arwp_sanitize_other_type( $value ) {
	$value = sanitize_text_field( $value );
	return in_array( $value, arwp_schema_other_types(), true ) ? $value : 'Article';
}

/**
 * Default post schema type (mapping or fallback).
 *
 * @return string
 */
function arwp_schema_default_post_type() {
	$value = get_option( 'arwp_schema_default_post_type', '' );
	return in_array( $value, arwp_schema_post_types(), true ) ? $value : 'BlogPosting';
}

/**
 * Default page schema type (mapping or fallback).
 *
 * @return string
 */
function arwp_schema_default_page_type() {
	$value = get_option( 'arwp_schema_default_page_type', '' );
	return in_array( $value, arwp_schema_page_types(), true ) ? $value : 'WebPage';
}

/**
 * Default schema type for other (custom) post types (mapping or fallback).
 *
 * @return string
 */
function arwp_schema_default_other_type() {
	$value = get_option( 'arwp_schema_default_other_type', '' );
	return in_array( $value, arwp_schema_other_types(), true ) ? $value : 'Article';
}

/**
 * Read an option value, or fall back to the stored option when a preview
 * payload does not include it.
 *
 * @param string $name   Option name.
 * @param array  $values Preview values keyed by option name.
 * @return string
 */
function arwp_jsonld_value( $name, $values ) {
	return isset( $values[ $name ] ) ? $values[ $name ] : get_option( $name, '' );
}

/**
 * Split a newline-separated list option into an array.
 *
 * @param string $value Stored or preview value.
 * @return array
 */
function arwp_jsonld_url_list( $value ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $value );
	$urls  = array();

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$urls[] = $line;
		}
	}

	return $urls;
}

/**
 * Build a typed @id reference object for cross-linking graph nodes.
 *
 * @param string $type Schema.org type.
 * @param string $id   Node @id.
 * @return array
 */
function arwp_jsonld_ref( $type, $id ) {
	return array(
		'@type' => $type,
		'@id'   => $id,
	);
}

/**
 * Site language as a BCP 47 tag (e.g. en-US).
 *
 * @return string
 */
function arwp_jsonld_site_language() {
	return str_replace( '_', '-', get_locale() );
}

/**
 * Reduce a title/text to plain text for schema output: strips HTML tags and
 * decodes HTML entities (wptexturize turns quotes/ampersands into entities).
 *
 * @param string $text Raw text.
 * @return string
 */
function arwp_jsonld_clean_text( $text ) {
	return wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Build the Organization and WebSite @graph nodes shared by the live preview
 * and the Phase 4 front-end output.
 *
 * @param array $values Preview values keyed by option name.
 * @return array
 */
function arwp_jsonld_build_global_nodes( $values = array() ) {
	$home     = home_url( '/' );
	$org_name = arwp_jsonld_value( 'arwp_schema_org_name', $values );
	$site     = arwp_jsonld_value( 'arwp_schema_website_name', $values );

	$organization = array(
		'@type' => 'Organization',
		'@id'   => $home . '#organization',
		'name'  => '' !== $org_name ? $org_name : get_bloginfo( 'name' ),
		'url'   => $home,
	);

	$description = arwp_jsonld_value( 'arwp_schema_org_description', $values );
	if ( '' !== $description ) {
		$organization['description'] = $description;
	}

	$logo = arwp_jsonld_value( 'arwp_schema_org_logo', $values );
	if ( '' !== $logo ) {
		$organization['logo'] = $logo;
	}

	$same_as = arwp_jsonld_url_list( arwp_jsonld_value( 'arwp_schema_same_as', $values ) );
	if ( ! empty( $same_as ) ) {
		$organization['sameAs'] = $same_as;
	}

	$knows_about = arwp_jsonld_url_list( arwp_jsonld_value( 'arwp_schema_knows_about', $values ) );
	if ( ! empty( $knows_about ) ) {
		$organization['knowsAbout'] = $knows_about;
	}

	$website = array(
		'@type'      => 'WebSite',
		'@id'        => $home . '#website',
		'name'       => '' !== $site ? $site : get_bloginfo( 'name' ),
		'url'        => $home,
		'inLanguage' => arwp_jsonld_site_language(),
		'publisher'  => arwp_jsonld_ref( 'Organization', $home . '#organization' ),
	);

	$alternate_name = arwp_jsonld_value( 'arwp_schema_website_alternate_name', $values );
	if ( '' !== $alternate_name ) {
		$website['alternateName'] = $alternate_name;
	}

	return array( $organization, $website );
}

/**
 * Build a mock content node so the settings preview shows how posts will map.
 *
 * @param array $values Preview values keyed by option name.
 * @return array
 */
function arwp_jsonld_build_page_node( $values = array() ) {
	$home = home_url( '/' );
	$type = arwp_jsonld_value( 'arwp_schema_default_post_type', $values );

	if ( ! in_array( $type, arwp_schema_post_types(), true ) ) {
		$type = 'BlogPosting';
	}

	$url = $home . 'example-blog-post/';

	return array(
		'@type'     => $type,
		'@id'       => $url . '#webpage',
		'headline'  => __( 'Example Blog Post', 'arwp' ),
		'url'       => $url,
		'isPartOf'  => arwp_jsonld_ref( 'WebSite', $home . '#website' ),
		'publisher' => arwp_jsonld_ref( 'Organization', $home . '#organization' ),
	);
}

/**
 * Read the per-post about URI, with an ACF fallback when the native meta is
 * empty and ACF is active.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function arwp_jsonld_about_uri( $post ) {
	$uri = get_post_meta( $post->ID, '_arwp_schema_about_uri', true );

	if ( '' === $uri && function_exists( 'get_field' ) ) {
		$uri = (string) get_field( 'arwp_schema_about_uri', $post->ID );
	}

	return $uri;
}

/**
 * Resolve the schema type for a post: meta box override wins, otherwise the
 * module default mapping for the post type.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function arwp_jsonld_resolve_content_type( $post ) {
	if ( is_front_page() ) {
		return 'WebPage';
	}

	$custom = get_post_meta( $post->ID, '_arwp_schema_custom_type', true );
	$allowed = array_diff( arwp_post_meta_types( $post->post_type ), array( 'default' ) );

	if ( in_array( $custom, $allowed, true ) ) {
		return $custom;
	}

	if ( 'page' === $post->post_type ) {
		return arwp_schema_default_page_type();
	}

	if ( 'post' === $post->post_type ) {
		return arwp_schema_default_post_type();
	}

	return arwp_schema_default_other_type();
}

/**
 * Build the author Person node for single posts.
 *
 * @param int $author_id Author user ID.
 * @return array
 */
function arwp_jsonld_build_person_node( $author_id ) {
	$home         = home_url( '/' );
	$author_url   = get_author_posts_url( $author_id );
	$display_name = get_the_author_meta( 'display_name', $author_id );

	$node = array(
		'@type'    => 'Person',
		'@id'      => $author_url . '#person',
		'name'     => '' !== $display_name ? arwp_jsonld_clean_text( $display_name ) : __( 'Anonymous', 'arwp' ),
		'url'      => $author_url,
		'worksFor' => arwp_jsonld_ref( 'Organization', $home . '#organization' ),
	);

	$job_title = get_user_meta( $author_id, 'arwp_author_job_title', true );
	if ( '' !== $job_title ) {
		$node['jobTitle'] = $job_title;
	}

	$same_as = arwp_jsonld_url_list( get_user_meta( $author_id, 'arwp_author_same_as', true ) );
	if ( ! empty( $same_as ) ) {
		$node['sameAs'] = $same_as;
	}

	return $node;
}

/**
 * Build the content node (Article/WebPage/FAQPage) for a single post or page.
 *
 * @param WP_Post $post Post object.
 * @return array
 */
function arwp_jsonld_build_content_node( $post ) {
	$home      = home_url( '/' );
	$permalink = get_permalink( $post );
	$is_page   = 'page' === $post->post_type;
	$type      = arwp_jsonld_resolve_content_type( $post );
	$faq_data  = array();

	if ( 'FAQPage' === $type ) {
		$faq_data = json_decode( get_post_meta( $post->ID, '_arwp_schema_faq_data', true ), true );

		if ( ! is_array( $faq_data ) || empty( $faq_data ) ) {
			$faq_data = array();

			if ( $is_page ) {
				$type = arwp_schema_default_page_type();
			} elseif ( 'post' === $post->post_type ) {
				$type = arwp_schema_default_post_type();
			} else {
				$type = arwp_schema_default_other_type();
			}
		}
	}

	$node = array(
		'@type'         => $type,
		'@id'           => $permalink . '#webpage',
		'url'           => $permalink,
		'inLanguage'    => arwp_jsonld_site_language(),
		'isPartOf'      => arwp_jsonld_ref( 'WebSite', $home . '#website' ),
		'publisher'     => arwp_jsonld_ref( 'Organization', $home . '#organization' ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
	);

	if ( $is_page ) {
		$node['name'] = arwp_jsonld_clean_text( get_the_title( $post ) );
	} else {
		$node['headline'] = arwp_jsonld_clean_text( get_the_title( $post ) );

		if ( (int) $post->post_author > 0 ) {
			$node['author'] = arwp_jsonld_ref( 'Person', get_author_posts_url( $post->post_author ) . '#person' );
		}
	}

	$about_uri = arwp_jsonld_about_uri( $post );
	if ( '' !== $about_uri ) {
		$node['about'] = array(
			'@type'  => 'Thing',
			'sameAs' => $about_uri,
		);
	} elseif ( 'AboutPage' === $type ) {
		$node['about'] = arwp_jsonld_ref( 'Organization', $home . '#organization' );
	}

	if ( ! empty( $faq_data ) ) {
		foreach ( $faq_data as $item ) {
			$node['mainEntity'][] = array(
				'@type'          => 'Question',
				'name'           => isset( $item['q'] ) ? $item['q'] : '',
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => isset( $item['a'] ) ? $item['a'] : '',
				),
			);
		}
	}

	return $node;
}

/**
 * Canonical URL of the current archive, taxonomy or posts page.
 *
 * @return string
 */
function arwp_jsonld_archive_url() {
	$q = get_queried_object();

	if ( is_category() ) {
		return get_category_link( $q );
	}

	if ( is_tag() ) {
		return get_tag_link( $q );
	}

	if ( is_tax() ) {
		return get_term_link( $q );
	}

	if ( is_author() ) {
		return get_author_posts_url( (int) $q->ID );
	}

	if ( is_post_type_archive() ) {
		return get_post_type_archive_link( get_query_var( 'post_type' ) );
	}

	if ( is_day() ) {
		return get_day_link( (int) get_query_var( 'year' ), (int) get_query_var( 'monthnum' ), (int) get_query_var( 'day' ) );
	}

	if ( is_month() ) {
		return get_month_link( (int) get_query_var( 'year' ), (int) get_query_var( 'monthnum' ) );
	}

	if ( is_year() ) {
		return get_year_link( (int) get_query_var( 'year' ) );
	}

	if ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		return $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
	}

	return home_url( '/' );
}

/**
 * Build a CollectionPage node for archive, taxonomy and posts-page views,
 * listing the current query's posts in an ItemList.
 *
 * @return array
 */
function arwp_jsonld_build_collection_node() {
	$home  = home_url( '/' );
	$url   = arwp_jsonld_archive_url();
	$title = arwp_jsonld_clean_text( get_the_archive_title() );

	if ( '' === $title || 'Archives' === $title ) {
		$title = get_bloginfo( 'name' );
	}

	$node = array(
		'@type'      => 'CollectionPage',
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => $title,
		'inLanguage' => arwp_jsonld_site_language(),
		'isPartOf'   => arwp_jsonld_ref( 'WebSite', $home . '#website' ),
		'publisher'  => arwp_jsonld_ref( 'Organization', $home . '#organization' ),
	);

	$items  = array();
	$posts  = $GLOBALS['wp_query']->posts;
	$count  = 0;

	if ( is_array( $posts ) ) {
		foreach ( $posts as $post ) {
			$count++;

			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $count,
				'url'      => get_permalink( $post ),
				'name'     => arwp_jsonld_clean_text( get_the_title( $post ) ),
			);

			if ( 10 === $count ) {
				break;
			}
		}
	}

	if ( ! empty( $items ) ) {
		$node['mainEntity'] = array(
			'@type'           => 'ItemList',
			'itemListElement' => $items,
		);
	}

	return $node;
}

/**
 * Build the full @graph for the current request.
 *
 * @return array
 */
function arwp_jsonld_build_graph() {
	$nodes = arwp_jsonld_build_global_nodes();

	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post && isset( $post->ID, $post->post_type, $post->post_author ) ) {
			if ( 'page' !== $post->post_type && (int) $post->post_author > 0 ) {
				$nodes[] = arwp_jsonld_build_person_node( (int) $post->post_author );
			}

			$nodes[] = arwp_jsonld_build_content_node( $post );
		}
	} elseif ( is_archive() || is_home() ) {
		$nodes[] = arwp_jsonld_build_collection_node();
	}

	return array(
		'@context' => 'https://schema.org',
		'@graph'   => $nodes,
	);
}

/**
 * Encode a schema array exactly as it is emitted on the front end.
 *
 * @param array $schema Schema array.
 * @return string
 */
function arwp_jsonld_graph_json( $schema ) {
	return wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
}

/**
 * Output the @graph as a single ld+json script on wp_head (priority 5).
 */
function arwp_jsonld_render_output() {
	$active = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );

	if ( empty( $active['json_ld'] ) ) {
		return;
	}

	$schema = arwp_jsonld_build_graph();

	if ( empty( $schema['@graph'] ) ) {
		return;
	}

	echo '<script type="application/ld+json">' . arwp_jsonld_graph_json( $schema ) . '</script>';
}
add_action( 'wp_head', 'arwp_jsonld_render_output', 5 );

/**
 * Add a "Validate Schema" link to the admin bar that opens the current page's
 * schema in validator.schema.org (prefilled) in a new tab. The prefilled URL is
 * built client-side in arwp-validate-schema.js, mirroring the settings page.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar object.
 */
function arwp_adminbar_validate_schema( $wp_admin_bar ) {
	if ( ! is_admin_bar_showing() || is_admin() ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( get_option( 'arwp_adminbar_validate_schema', 1 ) ) ) {
		return;
	}

	$active = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );

	if ( empty( $active['json_ld'] ) ) {
		return;
	}

	if ( ! ( is_front_page() || is_singular() || is_archive() || is_home() ) ) {
		return;
	}

	$wp_admin_bar->add_node(
		array(
			'id'    => 'arwp-validate-schema',
			'title' => '<span class="ab-icon dashicons-schema"></span><span class="ab-label">' . esc_html__( 'Validate Schema', 'arwp' ) . '</span>',
			'href'  => 'https://validator.schema.org/',
			'meta'  => array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			),
		)
	);
}
add_action( 'admin_bar_menu', 'arwp_adminbar_validate_schema', 100 );

/**
 * Enqueue the admin bar validate script when the button will be shown.
 */
function arwp_adminbar_assets() {
	if ( ! is_admin_bar_showing() || is_admin() ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( get_option( 'arwp_adminbar_validate_schema', 1 ) ) ) {
		return;
	}

	$active = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );

	if ( empty( $active['json_ld'] ) ) {
		return;
	}

	if ( ! ( is_front_page() || is_singular() || is_archive() || is_home() ) ) {
		return;
	}

	wp_enqueue_script( 'arwp-validate-schema', ARWP_URL . 'assets/arwp-validate-schema.js', array(), ARWP_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'arwp_adminbar_assets' );

/**
 * AJAX handler: build the @graph preview from unsaved form values.
 */
function arwp_ajax_preview_jsonld() {
	check_ajax_referer( 'arwp_preview_jsonld', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'arwp' ) ) );
	}

	$fields = array(
		'arwp_schema_org_name'               => 'sanitize_text_field',
		'arwp_schema_org_description'        => 'sanitize_textarea_field',
		'arwp_schema_org_logo'               => 'esc_url_raw',
		'arwp_schema_same_as'                => 'arwp_sanitize_url_list',
		'arwp_schema_knows_about'            => 'arwp_sanitize_url_list',
		'arwp_schema_website_name'           => 'sanitize_text_field',
		'arwp_schema_website_alternate_name' => 'sanitize_text_field',
		'arwp_schema_default_post_type'      => 'arwp_sanitize_post_type',
		'arwp_schema_default_page_type'      => 'arwp_sanitize_page_type',
		'arwp_schema_default_other_type'     => 'arwp_sanitize_other_type',
	);

	$values = array();

	foreach ( $fields as $name => $callback ) {
		if ( isset( $_POST[ $name ] ) ) {
			$values[ $name ] = call_user_func( $callback, wp_unslash( $_POST[ $name ] ) );
		}
	}

	$schema = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_merge(
			arwp_jsonld_build_global_nodes( $values ),
			array( arwp_jsonld_build_page_node( $values ) )
		),
	);

	wp_send_json_success( array( 'schema' => $schema ) );
}
add_action( 'wp_ajax_arwp_preview_jsonld', 'arwp_ajax_preview_jsonld' );

function arwp_jsonld_section_cb() {
	echo '<p>' . esc_html__( 'Organization identity used in the @graph structured data.', 'arwp' ) . '</p>';
}

function arwp_field_org_name() {
	arwp_text_field(
		'arwp_schema_org_name',
		get_bloginfo( 'name' ),
		'text',
		__( 'The legal or brand name of your organization or business. Shown to search engines and AI agents in the Organization node. Leave empty to use the WordPress site name.', 'arwp' ),
		'https://schema.org/Organization'
	);
}

function arwp_field_org_logo() {
	arwp_text_field(
		'arwp_schema_org_logo',
		'https://example.com/logo.png',
		'text',
		__( 'Full URL to your logo image, e.g. https://example.com/logo.png. Recommended minimum height of 112px.', 'arwp' ),
		'https://schema.org/logo'
	);
}

function arwp_field_org_description() {
	arwp_textarea_field(
		'arwp_schema_org_description',
		__( 'One or two sentences describing what your organization does. Used as the summary in the Organization node.', 'arwp' ),
		'https://schema.org/description'
	);
}

function arwp_field_same_as() {
	arwp_textarea_field(
		'arwp_schema_same_as',
		__( 'Links to your official profiles on other platforms, so search engines and AI agents can confirm they all belong to the same organization. One URL per line. Examples:', 'arwp' )
		. "\nhttps://www.linkedin.com/company/acme\nhttps://x.com/acme\nhttps://en.wikipedia.org/wiki/Acme",
		'https://schema.org/sameAs'
	);
}

function arwp_field_knows_about() {
	arwp_textarea_field(
		'arwp_schema_knows_about',
		__( 'The main subjects your organization is known for, using Wikidata URLs. One URL per line. Example:', 'arwp' )
		. "\nhttps://www.wikidata.org/wiki/Q18352212",
		'https://schema.org/knowsAbout'
	);
}

function arwp_website_section_cb() {
	echo '<p>' . esc_html__( 'WebSite node identity in the @graph. Empty fields fall back to the WordPress site name.', 'arwp' ) . '</p>';
}

function arwp_field_website_name() {
	arwp_text_field(
		'arwp_schema_website_name',
		get_bloginfo( 'name' ),
		'text',
		__( 'The name of the website shown in the WebSite node. Leave empty to use the WordPress site name.', 'arwp' ),
		'https://schema.org/WebSite'
	);
}

function arwp_field_website_alternate_name() {
	arwp_text_field(
		'arwp_schema_website_alternate_name',
		'',
		'text',
		__( 'A short name, acronym or brand abbreviation for the website, e.g. "ARWP" or "Acme Inc.". Optional.', 'arwp' ),
		'https://schema.org/alternateName'
	);
}

function arwp_mappings_section_cb() {
	echo '<p>' . esc_html__( 'Default schema type per content type. Override on individual items via the post editor meta box.', 'arwp' ) . '</p>';
}

function arwp_field_default_post_type() {
	arwp_select_field(
		'arwp_schema_default_post_type',
		arwp_schema_post_types(),
		arwp_schema_default_post_type(),
		__( 'The schema type applied to blog posts by default. BlogPosting suits a standard blog; NewsArticle suits time-sensitive news items.', 'arwp' ),
		'https://schema.org/BlogPosting'
	);
}

function arwp_field_default_page_type() {
	arwp_select_field(
		'arwp_schema_default_page_type',
		arwp_schema_page_types(),
		arwp_schema_default_page_type(),
		__( 'The schema type applied to pages by default. WebPage is the generic type; AboutPage and ContactPage give more specific meaning where relevant.', 'arwp' ),
		'https://schema.org/WebPage'
	);
}

function arwp_field_default_other_type() {
	arwp_select_field(
		'arwp_schema_default_other_type',
		arwp_schema_other_types(),
		arwp_schema_default_other_type(),
		__( 'The schema type applied to custom post types (any public post type other than posts and pages) by default.', 'arwp' ),
		'https://schema.org/Article'
	);
}

/**
 * Render a select field.
 *
 * @param string $name        Option name.
 * @param array  $options     Allowed values.
 * @param string $current     Currently selected value.
 * @param string $description Help text below the select.
 * @param string $learn_more  Optional documentation URL appended to the description.
 */
function arwp_select_field( $name, $options, $current, $description = '', $learn_more = '' ) {
	?>
	<select name="<?php echo esc_attr( $name ); ?>">
		<?php foreach ( $options as $option ) : ?>
			<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $current, $option ); ?>>
				<?php echo esc_html( $option ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
	if ( '' !== $description ) {
		arwp_field_description( $description, $learn_more );
	}
}

/**
 * Render the JSON-LD settings page.
 */
function arwp_jsonld_render_settings() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="arwp-jsonld-layout">
			<div class="arwp-jsonld-fields">
				<form action="options.php" method="post">
					<?php
					settings_fields( 'arwp_jsonld_options' );
					do_settings_sections( 'arwp-jsonld' );
					submit_button();
					?>
				</form>
			</div>

			<aside class="arwp-jsonld-preview">
				<h2 class="arwp-jsonld-preview-title"><?php esc_html_e( 'Live Preview', 'arwp' ); ?></h2>
				<p class="arwp-jsonld-preview-note"><?php esc_html_e( 'Updates as you type. The mock page node uses the default post schema.', 'arwp' ); ?></p>
				<pre id="arwp-jsonld-output" aria-live="polite"></pre>
				<div class="arwp-jsonld-preview-actions">
					<button
						type="button"
						class="button"
						id="arwp-copy-jsonld"
						disabled
						data-copy="<?php esc_attr_e( 'Copy', 'arwp' ); ?>"
						data-copied="<?php esc_attr_e( 'Copied', 'arwp' ); ?>"
					>
						<?php esc_html_e( 'Copy', 'arwp' ); ?>
					</button>
					<a
						class="button button-secondary button-disabled"
						id="arwp-validate-jsonld"
						href="https://validator.schema.org/"
						target="_blank"
						rel="noopener noreferrer"
						aria-disabled="true"
					>
						<?php esc_html_e( 'Validate', 'arwp' ); ?>
					</a>
				</div>
			</aside>
		</div>
	</div>
	<?php
}
