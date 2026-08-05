<?php
/**
 * Agent Ready WP — Settings submenu (Settings API).
 *
 * @package Agent_Ready_WP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the Settings submenu.
 */
function arwp_register_settings_menu() {
	add_submenu_page(
		'arwp-dashboard',
		__( 'Settings', 'arwp' ),
		__( 'Settings', 'arwp' ),
		'manage_options',
		'arwp-settings',
		'arwp_render_settings'
	);
}
add_action( 'admin_menu', 'arwp_register_settings_menu' );

/**
 * Register global plugin settings.
 */
function arwp_register_global_settings() {
	register_setting( 'arwp_schema_options', 'arwp_adminbar_validate_schema', array( 'sanitize_callback' => 'arwp_sanitize_toggle' ) );

	add_settings_section(
		'arwp_general_section',
		__( 'General', 'arwp' ),
		'arwp_general_section_cb',
		'arwp-settings'
	);

	add_settings_field( 'arwp_adminbar_validate_schema', __( 'Validate Schema (Admin Bar)', 'arwp' ), 'arwp_field_adminbar_validate', 'arwp-settings', 'arwp_general_section' );

	add_settings_section(
		'arwp_author_section',
		__( 'Author Schema', 'arwp' ),
		'__return_false',
		'arwp-settings'
	);

	add_settings_field( 'arwp_author_schema_note', __( 'Author Job Title & Social Links', 'arwp' ), 'arwp_field_author_schema_note', 'arwp-settings', 'arwp_author_section' );
}
add_action( 'admin_init', 'arwp_register_global_settings' );

/**
 * Sanitize an on/off toggle value.
 *
 * @param mixed $value Raw input.
 * @return int
 */
function arwp_sanitize_toggle( $value ) {
	return empty( $value ) ? 0 : 1;
}

/**
 * General settings section description.
 */
function arwp_general_section_cb() {
	echo '<p>' . esc_html__( 'Plugin-wide options.', 'arwp' ) . '</p>';
}

/**
 * Render the "Validate Schema" admin bar toggle.
 */
function arwp_field_adminbar_validate() {
	$checked = get_option( 'arwp_adminbar_validate_schema', 1 );
	?>
	<label for="arwp-adminbar-validate">
		<input type="hidden" name="arwp_adminbar_validate_schema" value="0">
		<input type="checkbox" id="arwp-adminbar-validate" name="arwp_adminbar_validate_schema" value="1" <?php checked( $checked, 1 ); ?>>
		<?php esc_html_e( 'Show the "Validate Schema" button on the front-end admin bar.', 'arwp' ); ?>
	</label>
	<?php
	arwp_field_description( __( 'When enabled, admins get a "Validate Schema" link in the top admin bar on pages, posts, and the homepage that opens schema.org\'s validator with the page\'s JSON-LD prefilled.', 'arwp' ) );
}

/**
 * Render the "Author Schema" note with a link to the current user's profile.
 */
function arwp_field_author_schema_note() {
	$profile_url = get_edit_profile_url();
	echo wp_kses(
		sprintf(
			__( 'Author schema metadata (Job Title &amp; Social Links) is managed on your <a href="%1$s">user profile</a> (Users &gt; Profile).', 'arwp' ),
			esc_url( $profile_url )
		),
		array( 'a' => array( 'href' => array() ) )
	);
}

/**
 * Render the Settings page.
 */
function arwp_render_settings() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'arwp_schema_options' );
			do_settings_sections( 'arwp-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Sanitize a newline-separated list of URLs.
 *
 * @param string $value Raw input.
 * @return string
 */
function arwp_sanitize_url_list( $value ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $value );
	$clean = array();

	foreach ( $lines as $line ) {
		$url = esc_url_raw( trim( $line ) );
		if ( '' !== $url ) {
			$clean[] = $url;
		}
	}

	return implode( "\n", $clean );
}

/**
 * Render a single-line text or password input.
 *
 * @param string $name        Option name.
 * @param string $placeholder Placeholder text.
 * @param string $type        Input type.
 * @param string $description Help text below the input.
 * @param string $learn_more  Optional documentation URL appended to the description.
 */
function arwp_text_field( $name, $placeholder = '', $type = 'text', $description = '', $learn_more = '' ) {
	$value = get_option( $name, '' );
	?>
	<input
		class="regular-text"
		type="<?php echo esc_attr( $type ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
	>
	<?php
	if ( '' !== $description ) {
		arwp_field_description( $description, $learn_more );
	}
}

/**
 * Render a newline-separated textarea.
 *
 * @param string $name        Option name.
 * @param string $description Help text below the textarea.
 * @param string $learn_more  Optional documentation URL appended to the description.
 */
function arwp_textarea_field( $name, $description = '', $learn_more = '' ) {
	$value = get_option( $name, '' );
	?>
	<textarea class="large-text" rows="5" name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
	<?php
	if ( '' !== $description ) {
		arwp_field_description( $description, $learn_more );
	}
}

/**
 * Render a field help description with an optional "Learn more" link.
 *
 * @param string $description Text to show.
 * @param string $learn_more  Documentation URL, or '' for no link.
 */
function arwp_field_description( $description, $learn_more = '' ) {
	echo '<p class="description">' . nl2br( esc_html( $description ) );

	if ( '' !== $learn_more ) {
		echo ' <a href="' . esc_url( $learn_more ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn more', 'arwp' ) . '</a>';
	}

	echo '</p>';
}
