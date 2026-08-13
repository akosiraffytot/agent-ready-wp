<?php
/**
 * Agent Ready WP — Admin Dashboard (module card grid).
 *
 * @package Agent_Ready_WP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the top-level menu and Dashboard submenu.
 */
function arwp_register_admin_menu() {
	add_menu_page(
		__( 'Agent Ready WP', 'arwp' ),
		__( 'Agent Ready WP', 'arwp' ),
		'manage_options',
		'arwp-dashboard',
		'arwp_render_dashboard',
		'dashicons-superhero',
		60
	);

	add_submenu_page(
		'arwp-dashboard',
		__( 'Dashboard', 'arwp' ),
		__( 'Dashboard', 'arwp' ),
		'manage_options',
		'arwp-dashboard',
		'arwp_render_dashboard'
	);
}
add_action( 'admin_menu', 'arwp_register_admin_menu' );

/**
 * Enqueue dashboard + settings assets on plugin pages and the post editor
 * (the JSON-LD meta box needs the stylesheet there too).
 *
 * @param string $hook_suffix Current admin page hook.
 */
function arwp_admin_enqueue( $hook_suffix ) {
	$is_llms_page   = 'agent-ready-wp_page_arwp-llms' === $hook_suffix || 'arwp-dashboard_page_arwp-llms' === $hook_suffix;
	$is_plugin_page = 'toplevel_page_arwp-dashboard' === $hook_suffix || 'agent-ready-wp_page_arwp-settings' === $hook_suffix || 'agent-ready-wp_page_arwp-jsonld' === $hook_suffix || $is_llms_page;
	$is_post_editor = 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix;

	if ( ! $is_plugin_page && ! $is_post_editor ) {
		return;
	}

	if ( $is_post_editor ) {
		wp_enqueue_style(
			'arwp-editor',
			ARWP_URL . 'assets/arwp-editor.css',
			array(),
			(string) filemtime( ARWP_PATH . 'assets/arwp-editor.css' )
		);

		wp_enqueue_media();

		wp_enqueue_script(
			'arwp-editor',
			ARWP_URL . 'assets/arwp-editor.js',
			array(),
			(string) filemtime( ARWP_PATH . 'assets/arwp-editor.js' ),
			true
		);

		return;
	}

	wp_enqueue_style(
		'arwp-admin',
		ARWP_URL . 'assets/arwp-admin.css',
		array(),
		(string) filemtime( ARWP_PATH . 'assets/arwp-admin.css' )
	);

	wp_enqueue_script(
		'arwp-admin',
		ARWP_URL . 'assets/arwp-admin.js',
		array( 'jquery' ),
		(string) filemtime( ARWP_PATH . 'assets/arwp-admin.js' ),
		true
	);

	wp_localize_script(
		'arwp-admin',
		'ArwpAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'arwp_toggle_module' ),
		)
	);

	if ( 'agent-ready-wp_page_arwp-jsonld' === $hook_suffix ) {
		wp_enqueue_media();

		wp_enqueue_script(
			'arwp-jsonld-preview',
			ARWP_URL . 'assets/arwp-jsonld-preview.js',
			array(),
			(string) filemtime( ARWP_PATH . 'assets/arwp-jsonld-preview.js' ),
			true
		);

		wp_localize_script(
			'arwp-jsonld-preview',
			'ArwpPreview',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'arwp_preview_jsonld' ),
				'pageUrl' => home_url( '/' ),
			)
		);
	}

	if ( $is_llms_page ) {
		wp_enqueue_script(
			'arwp-llms-preview',
			ARWP_URL . 'assets/arwp-llms-preview.js',
			array(),
			(string) filemtime( ARWP_PATH . 'assets/arwp-llms-preview.js' ),
			true
		);

		wp_localize_script(
			'arwp-llms-preview',
			'ArwpLlmsPreview',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'arwp_preview_llms' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'arwp_admin_enqueue' );

/**
 * Render the Dashboard module card grid.
 */
function arwp_render_dashboard() {
	$registry = arwp_get_modules();
	$active   = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p><?php esc_html_e( 'Enable the modules that make your site ready for AI agents.', 'arwp' ); ?></p>

		<div class="arwp-grid">
			<?php foreach ( $registry as $id => $module ) : ?>
				<div class="arwp-card <?php echo esc_attr( $module['soon'] ? 'arwp-card-soon' : '' ); ?>">
					<?php if ( $module['soon'] ) : ?>
						<span class="arwp-badge"><?php esc_html_e( 'Soon', 'arwp' ); ?></span>
					<?php endif; ?>

					<div class="arwp-card-icon">
						<span class="dashicons <?php echo esc_attr( $module['icon'] ); ?>"></span>
					</div>

					<div class="arwp-card-body">
						<h2 class="arwp-card-title"><?php echo esc_html( $module['title'] ); ?></h2>
						<p class="arwp-card-desc"><?php echo esc_html( $module['description'] ); ?></p>
					</div>

					<div class="arwp-card-actions">
						<?php if ( $module['has_settings'] ) : ?>
							<a
								class="button button-secondary arwp-card-settings"
								href="<?php echo esc_url( admin_url( 'admin.php?page=' . $module['settings_slug'] ) ); ?>"
								<?php echo empty( $active[ $id ] ) ? 'style="display:none;"' : ''; ?>
							>
								<?php esc_html_e( 'Settings', 'arwp' ); ?>
							</a>
						<?php endif; ?>

						<label class="arwp-switch">
							<input
								type="checkbox"
								class="arwp-toggle"
								data-module="<?php echo esc_attr( $id ); ?>"
								data-settings-slug="<?php echo esc_attr( $module['settings_slug'] ); ?>"
								<?php echo empty( $active[ $id ] ) ? '' : 'checked'; ?>
								<?php echo esc_attr( $module['soon'] ? 'disabled' : '' ); ?>
								aria-label="<?php /* translators: %s: module name. */ echo esc_attr( sprintf( __( 'Toggle %s module', 'arwp' ), $module['title'] ) ); ?>"
							>
							<span class="arwp-slider" aria-hidden="true"></span>
						</label>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * AJAX handler: toggle a module on/off.
 */
function arwp_ajax_toggle_module() {
	check_ajax_referer( 'arwp_toggle_module', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'arwp' ) ) );
	}

	$module  = isset( $_POST['module'] ) ? sanitize_key( wp_unslash( $_POST['module'] ) ) : '';
	$enabled = isset( $_POST['enabled'] ) ? ( '1' === sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) ) : false;

	$registry = arwp_get_modules();

	if ( ! isset( $registry[ $module ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Unknown module.', 'arwp' ) ) );
	}

	if ( $enabled && ! empty( $registry[ $module ]['soon'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Module not available yet.', 'arwp' ) ) );
	}

	$active = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );

	if ( ! is_array( $active ) ) {
		$active = arwp_get_default_modules();
	}

	$active[ $module ] = $enabled ? 1 : 0;

	update_option( 'arwp_schema_active_modules', $active );

	wp_send_json_success(
		array(
			'module'  => $module,
			'enabled' => $enabled,
		)
	);
}
add_action( 'wp_ajax_arwp_toggle_module', 'arwp_ajax_toggle_module' );
