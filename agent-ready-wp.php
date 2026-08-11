<?php
/**
 * Plugin Name: Agent Ready WP
 * Plugin URI:  https://github.com/akosiraffytot/agent-ready-wp
 * Description: Zero-bloat JSON-LD plugin that automatically emits a full Schema.org @graph on every page for search engines and AI agents.
 * Version:     1.2.1
 * Author:      Rafael Mendoza
 * Author URI:  https://akosiraffytot.dev/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: arwp
 *
 * @package Agent_Ready_WP
 */

defined( 'ABSPATH' ) || exit;

define( 'ARWP_VERSION', '1.2.1' );
define( 'ARWP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ARWP_URL', plugin_dir_url( __FILE__ ) );
define( 'ARWP_GITHUB_REPO', 'akosiraffytot/agent-ready-wp' );

/**
 * Plugin Update Checker (PUC) v5 — GitHub-hosted auto-update.
 *
 * Repo is the hardcoded ARWP_GITHUB_REPO constant. No token (public repo);
 * re-add a token field only if the repo is ever made private.
 */
if ( file_exists( ARWP_PATH . 'lib/plugin-update-checker/plugin-update-checker.php' ) ) {
	require_once ARWP_PATH . 'lib/plugin-update-checker/plugin-update-checker.php';

	// PUC v5 removed the global PucFactory class; the factory is namespaced.
	if ( class_exists( '\YahnisElsts\PluginUpdateChecker\v5p7\PucFactory' ) ) {
		$arwp_update_checker = \YahnisElsts\PluginUpdateChecker\v5p7\PucFactory::buildUpdateChecker(
			'https://github.com/' . ARWP_GITHUB_REPO,
			__FILE__,
			'agent-ready-wp'
		);
		$arwp_update_checker->setBranch( 'main' );
		$arwp_update_checker->getVcsApi()->enableReleaseAssets( '/\.zip($|[?#])/i' );
	}
}

/**
 * Module registry. Single source of truth for the dashboard cards and loader.
 *
 * @return array
 */
function arwp_get_modules() {
	return array(
		'json_ld'     => array(
			'title'          => 'JSON-LD Schema',
			'description'    => 'Adds JSON-LD structured data so AI agents and search engines understand your content.',
			'icon'           => 'dashicons-schedule',
			'active_default' => 1,
			'has_settings'   => true,
			'settings_slug'  => 'arwp-jsonld',
			'soon'           => false,
		),
		'llm_txt'     => array(
			'title'          => 'llm.txt',
			'description'    => 'Serve a machine-readable /llm.txt markdown summary.',
			'icon'           => 'dashicons-media-text',
			'active_default' => 0,
			'has_settings'   => false,
			'settings_slug'  => '',
			'soon'           => true,
		),
		'ai_robots'   => array(
			'title'          => 'AI Robots',
			'description'    => 'Directives for AI crawlers in robots.txt.',
			'icon'           => 'dashicons-shield-alt',
			'active_default' => 0,
			'has_settings'   => false,
			'settings_slug'  => '',
			'soon'           => true,
		),
		'woocommerce' => array(
			'title'          => 'WooCommerce',
			'description'    => 'Link WooCommerce product schema to the Organization graph.',
			'icon'           => 'dashicons-cart',
			'active_default' => 0,
			'has_settings'   => false,
			'settings_slug'  => '',
			'soon'           => true,
		),
	);
}

/**
 * Default module state keyed by module id.
 *
 * @return array
 */
function arwp_get_default_modules() {
	$defaults = array();

	foreach ( arwp_get_modules() as $id => $module ) {
		$defaults[ $id ] = $module['active_default'];
	}

	return $defaults;
}

/**
 * Seed module defaults on activation.
 */
function arwp_activate() {
	if ( false === get_option( 'arwp_schema_active_modules' ) ) {
		add_option( 'arwp_schema_active_modules', arwp_get_default_modules() );
	}

	if ( false === get_option( 'arwp_disable_third_party_schema' ) ) {
		add_option( 'arwp_disable_third_party_schema', '1' );
	}
}
register_activation_hook( __FILE__, 'arwp_activate' );

/**
 * Load active modules. Each module file self-registers its hooks.
 */
function arwp_load_modules() {
	$registry = arwp_get_modules();
	$active   = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );

	// json_ld always loads so its settings page stays reachable even when the
	// module is toggled off. Front-end output gates on the active option.
	foreach ( $registry as $id => $module ) {
		if ( empty( $active[ $id ] ) && 'json_ld' !== $id ) {
			continue;
		}

		$file = ARWP_PATH . 'modules/module-' . str_replace( '_', '-', $id ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
add_action( 'plugins_loaded', 'arwp_load_modules' );

require_once ARWP_PATH . 'inc/admin-dashboard.php';
require_once ARWP_PATH . 'inc/admin-settings.php';
require_once ARWP_PATH . 'inc/post-meta-boxes.php';
require_once ARWP_PATH . 'inc/user-profile.php';
