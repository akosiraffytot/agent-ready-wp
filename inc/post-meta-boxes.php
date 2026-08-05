<?php
/**
 * Agent Ready WP — Post meta box "AI Entity Settings".
 *
 * Phase 3: per-post schema overrides stored in wp_postmeta.
 *
 * @package Agent_Ready_WP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the meta box on all public post types.
 */
function arwp_register_post_meta_box() {
	add_meta_box(
		'arwp-entity-settings',
		__( 'JSON-LD Schema Settings - Agent Ready WP', 'arwp' ),
		'arwp_render_post_meta_box',
		array_values( get_post_types( array( 'public' => true ), 'names' ) ),
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'arwp_register_post_meta_box' );

/**
 * Allowed per-post schema type values.
 *
 * Page types are offered only on pages; custom post types get a generic
 * union; article types on other post types.
 *
 * @param string $post_type Post type slug.
 * @return array
 */
function arwp_post_meta_types( $post_type = '' ) {
	if ( 'page' === $post_type ) {
		return array( 'default', 'WebPage', 'AboutPage', 'ContactPage', 'FAQPage' );
	}

	if ( 'post' === $post_type ) {
		return array( 'default', 'Article', 'BlogPosting', 'FAQPage' );
	}

	return array( 'default', 'Article', 'BlogPosting', 'WebPage', 'FAQPage' );
}

/**
 * Render the meta box fields.
 *
 * @param WP_Post $post Current post object.
 */
function arwp_render_post_meta_box( $post ) {
	wp_nonce_field( 'arwp_post_meta', 'arwp_post_meta_nonce' );

	$about_uri   = get_post_meta( $post->ID, '_arwp_schema_about_uri', true );
	$custom_type = get_post_meta( $post->ID, '_arwp_schema_custom_type', true );
	$faq_data    = get_post_meta( $post->ID, '_arwp_schema_faq_data', true );
	$author_name = get_the_author_meta( 'display_name', $post->post_author );
	$author_link = get_edit_user_link( $post->post_author );

	if ( ! in_array( $custom_type, arwp_post_meta_types( $post->post_type ), true ) ) {
		$custom_type = 'default';
	}
	?>
	<div class="arwp-post-meta">
		<?php if ( 'FAQPage' === $custom_type && '' === $faq_data ) : ?>
			<p class="description arwp-faq-warning"><?php esc_html_e( 'Notice: FAQPage schema is selected, but no FAQ questions/answers were provided. The site is temporarily using default page schema until FAQ data is added.', 'arwp' ); ?></p>
		<?php endif; ?>

		<p>
			<label for="arwp-author-note"><strong><?php esc_html_e( 'Author details', 'arwp' ); ?></strong></label>
		</p>
		<div class="arwp-author-note" id="arwp-author-note">
			<?php
			printf(
				wp_kses(
					__( 'Author details (Job Title &amp; Social Links) are pulled from %1$s&#8217;s <a href="%2$s" target="_blank" rel="noopener noreferrer">User Profile ↗</a>.', 'arwp' ),
					array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
				),
				esc_html( $author_name ),
				esc_url( $author_link )
			);
			?>
		</div>

		<p>
			<label for="arwp-about-uri"><strong><?php esc_html_e( 'Primary topic (about)', 'arwp' ); ?></strong></label>
		</p>
		<input
			type="text"
			class="widefat"
			id="arwp-about-uri"
			name="_arwp_schema_about_uri"
			value="<?php echo esc_attr( $about_uri ); ?>"
			placeholder="https://www.wikidata.org/wiki/Q28343"
		>
		<?php arwp_field_description( __( '(Optional) Add a Wikidata or Wikipedia URL for the primary topic of this page. This tells AI agents (like ChatGPT, Perplexity, and Claude) precisely what real-world concept this content covers. Leave blank if not applicable.', 'arwp' ), 'https://en.wikipedia.org/wiki/Wikipedia:Finding_a_Wikidata_ID' ); ?>

		<p>
			<label for="arwp-custom-type"><strong><?php esc_html_e( 'Schema type', 'arwp' ); ?></strong></label>
		</p>
		<select class="widefat" id="arwp-custom-type" name="_arwp_schema_custom_type">
			<?php foreach ( arwp_post_meta_types( $post->post_type ) as $type ) : ?>
				<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $custom_type, $type ); ?>>
					<?php echo esc_html( 'default' === $type ? __( 'Default', 'arwp' ) : $type ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php arwp_field_description( __( 'Overrides the module default for this item. "Default" uses the mapping set on the JSON-LD settings page.', 'arwp' ) ); ?>

		<p>
			<label for="arwp-faq-data"><strong><?php esc_html_e( 'FAQ data (optional)', 'arwp' ); ?></strong></label>
		</p>
		<textarea
			class="widefat"
			id="arwp-faq-data"
			name="_arwp_schema_faq_data"
			rows="6"
			placeholder='[{"q":"Question one?","a":"Answer one."}]'
		><?php echo esc_textarea( $faq_data ); ?></textarea>
		<?php arwp_field_description( __( 'JSON array of question/answer pairs for a FAQPage node, e.g. [{"q":"Question?","a":"Answer."}]. Saved data is normalized.', 'arwp' ), 'https://schema.org/FAQPage' ); ?>
	</div>
	<?php
}

/**
 * Validate a decoded FAQ array (list of objects with "q" and "a" keys).
 *
 * @param array $decoded json_decode() output.
 * @return bool
 */
function arwp_valid_faq_data( $decoded ) {
	if ( empty( $decoded ) || ! is_array( $decoded ) ) {
		return false;
	}

	foreach ( $decoded as $item ) {
		if ( ! is_array( $item ) || ! isset( $item['q'] ) || ! isset( $item['a'] ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Save the meta box fields.
 *
 * @param int $post_id Post ID.
 */
function arwp_save_post_meta_box( $post_id ) {
	if ( ! isset( $_POST['arwp_post_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['arwp_post_meta_nonce'] ) ), 'arwp_post_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['_arwp_schema_about_uri'] ) ) {
		$about_uri = sanitize_url( wp_unslash( $_POST['_arwp_schema_about_uri'] ) );

		if ( '' !== $about_uri ) {
			update_post_meta( $post_id, '_arwp_schema_about_uri', $about_uri );
		} else {
			delete_post_meta( $post_id, '_arwp_schema_about_uri' );
		}
	}

	if ( isset( $_POST['_arwp_schema_custom_type'] ) ) {
		$custom_type = sanitize_text_field( wp_unslash( $_POST['_arwp_schema_custom_type'] ) );

		if ( in_array( $custom_type, arwp_post_meta_types( get_post_type( $post_id ) ), true ) ) {
			if ( 'default' !== $custom_type ) {
				update_post_meta( $post_id, '_arwp_schema_custom_type', $custom_type );
			} else {
				delete_post_meta( $post_id, '_arwp_schema_custom_type' );
			}
		}
	}

	if ( isset( $_POST['_arwp_schema_faq_data'] ) ) {
		$faq_raw = trim( sanitize_textarea_field( wp_unslash( $_POST['_arwp_schema_faq_data'] ) ) );

		if ( '' !== $faq_raw ) {
			$decoded = json_decode( $faq_raw, true );

			if ( is_array( $decoded ) && arwp_valid_faq_data( $decoded ) ) {
				update_post_meta( $post_id, '_arwp_schema_faq_data', wp_json_encode( $decoded, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
			} else {
				set_transient( 'arwp_faq_error_' . $post_id, true, 60 );
			}
		} else {
			delete_post_meta( $post_id, '_arwp_schema_faq_data' );
		}
	}
}
add_action( 'save_post', 'arwp_save_post_meta_box' );

/**
 * Show an admin notice when FAQ data was rejected on save.
 */
function arwp_notice_invalid_faq() {
	if ( ! isset( $GLOBALS['post'] ) || empty( $GLOBALS['post']->ID ) ) {
		return;
	}

	$key = 'arwp_faq_error_' . $GLOBALS['post']->ID;

	if ( get_transient( $key ) ) {
		delete_transient( $key );
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'FAQ data was not saved: invalid JSON. Use an array of {"q": "...", "a": "..."} objects.', 'arwp' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'arwp_notice_invalid_faq' );
