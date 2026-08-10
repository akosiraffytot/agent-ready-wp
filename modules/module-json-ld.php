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
			function arwpHideJsonldMenu() {
				var link = document.querySelector( '#adminmenu .wp-submenu a[href$="page=arwp-jsonld"]' );
				if ( link && link.closest( 'li' ) ) {
					link.closest( 'li' ).style.display = 'none';
				}
			}

			if ( 'loading' === document.readyState ) {
				document.addEventListener( 'DOMContentLoaded', arwpHideJsonldMenu );
			} else {
				arwpHideJsonldMenu();
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
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_type', array( 'sanitize_callback' => 'arwp_sanitize_org_type' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_legal_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_slogan', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_tax_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_vat_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_founding_date', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_founder', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_area_served', array( 'sanitize_callback' => 'arwp_sanitize_text_list' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_contact_telephone', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_contact_email', array( 'sanitize_callback' => 'sanitize_email' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_contact_type', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_contact_languages', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_address_street', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_address_locality', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_address_region', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_address_postal', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_address_country', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_geo_lat', array( 'sanitize_callback' => 'arwp_sanitize_latitude' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_geo_lng', array( 'sanitize_callback' => 'arwp_sanitize_longitude' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_price_range', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_opening_hours', array( 'sanitize_callback' => 'arwp_sanitize_text_list' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_nonprofit_status', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_publishing_principles', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_ethics_policy', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_corrections_policy', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_diversity_policy', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_ticker_symbol', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_payment_accepted', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_currencies_accepted', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_merchant_return_policy', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_description', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_org_logo', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_same_as', array( 'sanitize_callback' => 'arwp_sanitize_url_list' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_knows_about', array( 'sanitize_callback' => 'arwp_sanitize_text_list' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_website_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_website_alternate_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_default_post_type', array( 'sanitize_callback' => 'arwp_sanitize_post_type' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_default_page_type', array( 'sanitize_callback' => 'arwp_sanitize_page_type' ) );
	register_setting( 'arwp_jsonld_options', 'arwp_schema_default_other_type', array( 'sanitize_callback' => 'arwp_sanitize_other_type' ) );

	add_settings_section(
		'arwp_org_type_section',
		__( 'Organization Type', 'arwp' ),
		'arwp_org_type_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_org_type', __( 'Organization Type', 'arwp' ), 'arwp_field_org_type', 'arwp-jsonld', 'arwp_org_type_section' );

	add_settings_section(
		'arwp_jsonld_section',
		__( 'Organization Identity', 'arwp' ),
		'arwp_jsonld_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_org_name', __( 'Organization Name', 'arwp' ), 'arwp_field_org_name', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_org_legal_name', __( 'Legal Name', 'arwp' ), 'arwp_field_org_legal_name', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_org_slogan', __( 'Slogan', 'arwp' ), 'arwp_field_org_slogan', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_org_description', __( 'Organization Description', 'arwp' ), 'arwp_field_org_description', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_org_logo', __( 'Organization Logo URL', 'arwp' ), 'arwp_field_org_logo', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_same_as', __( 'sameAs Profiles', 'arwp' ), 'arwp_field_same_as', 'arwp-jsonld', 'arwp_jsonld_section' );
	add_settings_field( 'arwp_schema_knows_about', __( 'knowsAbout Topics', 'arwp' ), 'arwp_field_knows_about', 'arwp-jsonld', 'arwp_jsonld_section' );

	add_settings_section(
		'arwp_contact_section',
		__( 'Contact Point', 'arwp' ),
		'arwp_contact_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_contact_telephone', __( 'Telephone', 'arwp' ), 'arwp_field_contact_telephone', 'arwp-jsonld', 'arwp_contact_section' );
	add_settings_field( 'arwp_schema_contact_email', __( 'Email', 'arwp' ), 'arwp_field_contact_email', 'arwp-jsonld', 'arwp_contact_section' );
	add_settings_field( 'arwp_schema_contact_type', __( 'Contact Type', 'arwp' ), 'arwp_field_contact_type', 'arwp-jsonld', 'arwp_contact_section' );
	add_settings_field( 'arwp_schema_contact_languages', __( 'Available Languages', 'arwp' ), 'arwp_field_contact_languages', 'arwp-jsonld', 'arwp_contact_section' );

	add_settings_section(
		'arwp_legal_section',
		__( 'Legal & Provenance', 'arwp' ),
		'arwp_legal_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_org_tax_id', __( 'Tax ID', 'arwp' ), 'arwp_field_org_tax_id', 'arwp-jsonld', 'arwp_legal_section' );
	add_settings_field( 'arwp_schema_org_vat_id', __( 'VAT ID', 'arwp' ), 'arwp_field_org_vat_id', 'arwp-jsonld', 'arwp_legal_section' );
	add_settings_field( 'arwp_schema_founding_date', __( 'Founding Date', 'arwp' ), 'arwp_field_founding_date', 'arwp-jsonld', 'arwp_legal_section' );
	add_settings_field( 'arwp_schema_org_founder', __( 'Founder', 'arwp' ), 'arwp_field_org_founder', 'arwp-jsonld', 'arwp_legal_section' );
	add_settings_field( 'arwp_schema_area_served', __( 'Area Served', 'arwp' ), 'arwp_field_area_served', 'arwp-jsonld', 'arwp_legal_section' );

	add_settings_section(
		'arwp_local_section',
		__( 'Local Business', 'arwp' ),
		'arwp_local_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_address_street', __( 'Street Address', 'arwp' ), 'arwp_field_address_street', 'arwp-jsonld', 'arwp_local_section' );
	add_settings_field( 'arwp_schema_address_locality', __( 'Locality (City)', 'arwp' ), 'arwp_field_address_locality', 'arwp-jsonld', 'arwp_local_section' );
	add_settings_field( 'arwp_schema_address_region', __( 'Region (State/Province)', 'arwp' ), 'arwp_field_address_region', 'arwp-jsonld', 'arwp_local_section' );
	add_settings_field( 'arwp_schema_address_postal', __( 'Postal Code', 'arwp' ), 'arwp_field_address_postal', 'arwp-jsonld', 'arwp_local_section' );
	add_settings_field( 'arwp_schema_address_country', __( 'Country Code', 'arwp' ), 'arwp_field_address_country', 'arwp-jsonld', 'arwp_local_section' );
	add_settings_field( 'arwp_schema_geo_lat', __( 'Latitude', 'arwp' ), 'arwp_field_geo_lat', 'arwp-jsonld', 'arwp_local_section' );
	add_settings_field( 'arwp_schema_geo_lng', __( 'Longitude', 'arwp' ), 'arwp_field_geo_lng', 'arwp-jsonld', 'arwp_local_section' );
	add_settings_field( 'arwp_schema_price_range', __( 'Price Range', 'arwp' ), 'arwp_field_price_range', 'arwp-jsonld', 'arwp_local_section' );
	add_settings_field( 'arwp_schema_opening_hours', __( 'Opening Hours', 'arwp' ), 'arwp_field_opening_hours', 'arwp-jsonld', 'arwp_local_section' );

	add_settings_section(
		'arwp_ngo_section',
		__( 'Non-Profit', 'arwp' ),
		'arwp_ngo_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_nonprofit_status', __( 'Non-Profit Status', 'arwp' ), 'arwp_field_nonprofit_status', 'arwp-jsonld', 'arwp_ngo_section' );

	add_settings_section(
		'arwp_news_section',
		__( 'Publishing & Policies', 'arwp' ),
		'arwp_news_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_publishing_principles', __( 'Publishing Principles', 'arwp' ), 'arwp_field_publishing_principles', 'arwp-jsonld', 'arwp_news_section' );
	add_settings_field( 'arwp_schema_ethics_policy', __( 'Ethics Policy', 'arwp' ), 'arwp_field_ethics_policy', 'arwp-jsonld', 'arwp_news_section' );
	add_settings_field( 'arwp_schema_corrections_policy', __( 'Corrections Policy', 'arwp' ), 'arwp_field_corrections_policy', 'arwp-jsonld', 'arwp_news_section' );
	add_settings_field( 'arwp_schema_diversity_policy', __( 'Diversity Policy', 'arwp' ), 'arwp_field_diversity_policy', 'arwp-jsonld', 'arwp_news_section' );

	add_settings_section(
		'arwp_corporate_section',
		__( 'Corporate', 'arwp' ),
		'arwp_corporate_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_ticker_symbol', __( 'Ticker Symbol', 'arwp' ), 'arwp_field_ticker_symbol', 'arwp-jsonld', 'arwp_corporate_section' );

	add_settings_section(
		'arwp_commerce_section',
		__( 'E-Commerce', 'arwp' ),
		'arwp_commerce_section_cb',
		'arwp-jsonld'
	);

	add_settings_field( 'arwp_schema_payment_accepted', __( 'Payment Methods', 'arwp' ), 'arwp_field_payment_accepted', 'arwp-jsonld', 'arwp_commerce_section' );
	add_settings_field( 'arwp_schema_currencies_accepted', __( 'Accepted Currencies', 'arwp' ), 'arwp_field_currencies_accepted', 'arwp-jsonld', 'arwp_commerce_section' );
	add_settings_field( 'arwp_schema_merchant_return_policy', __( 'Return Policy URL', 'arwp' ), 'arwp_field_merchant_return_policy', 'arwp-jsonld', 'arwp_commerce_section' );

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
 * Suppress JSON-LD output from Yoast SEO, Rank Math and All in One SEO when
 * the toggle is on, so ARWP's @graph is the only structured data emitted.
 */
function arwp_disable_third_party_schema() {
	$active = get_option( 'arwp_schema_active_modules', arwp_get_default_modules() );

	if ( ! empty( $active['json_ld'] ) && '1' === get_option( 'arwp_disable_third_party_schema', '1' ) ) {
		add_filter( 'wpseo_json_ld_output', '__return_false' );
		add_filter( 'rank_math/json_ld', '__return_empty_array' );
		add_filter( 'aioseo_schema_disable', '__return_true' );
	}
}
add_action( 'init', 'arwp_disable_third_party_schema' );

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
 * Organization taxonomy: categories of selectable Schema.org subtypes.
 *
 * Category keys are stable slugs; 'label' is the optgroup heading and
 * 'types' the selectable subtype values. Only concrete types are listed
 * (intermediate Schema.org classes like LocalBusiness or MedicalBusiness
 * are implied by their subtypes).
 *
 * @return array
 */
function arwp_schema_org_categories() {
	return array(
		'commercial' => array(
			'label' => __( 'Commercial, Corporate & E-Commerce', 'arwp' ),
			'types' => array( 'Organization', 'Corporation', 'OnlineBusiness', 'Consortium' ),
		),
		'food' => array(
			'label' => __( 'Food & Hospitality', 'arwp' ),
			'types' => array( 'Restaurant', 'Bakery', 'CafeOrCoffeeShop', 'BarOrPub', 'FastFoodRestaurant', 'Hotel', 'Motel', 'BedAndBreakfast' ),
		),
		'healthcare' => array(
			'label' => __( 'Healthcare & Medical', 'arwp' ),
			'types' => array( 'Dentist', 'MedicalClinic', 'Physician', 'Optician', 'Pharmacy', 'VeterinaryCare', 'Hospital' ),
		),
		'home' => array(
			'label' => __( 'Home, Construction & Trades', 'arwp' ),
			'types' => array( 'GeneralContractor', 'Electrician', 'Plumber', 'HVACBusiness', 'HousePainter', 'RoofingContractor', 'Locksmith' ),
		),
		'professional' => array(
			'label' => __( 'Professional & Legal Services', 'arwp' ),
			'types' => array( 'LegalService', 'AccountingService', 'BankOrCreditUnion', 'InsuranceAgency', 'EmploymentAgency', 'RealEstateAgent' ),
		),
		'beauty' => array(
			'label' => __( 'Health, Beauty & Wellness', 'arwp' ),
			'types' => array( 'BeautySalon', 'HairSalon', 'DaySpa', 'NailSalon', 'HealthClub' ),
		),
		'automotive' => array(
			'label' => __( 'Automotive & Retail', 'arwp' ),
			'types' => array( 'AutoRepair', 'AutoDealer', 'GasStation', 'ClothingStore', 'GroceryStore', 'HardwareStore', 'JewelryStore' ),
		),
		'civic' => array(
			'label' => __( 'Non-Profit, Civic & Community', 'arwp' ),
			'types' => array( 'NGO', 'AnimalShelter', 'GovernmentOrganization', 'School', 'CollegeOrUniversity', 'Preschool', 'SportsOrganization', 'PerformingGroup', 'ReligiousOrganization' ),
		),
		'media' => array(
			'label' => __( 'Media & Publishing', 'arwp' ),
			'types' => array( 'NewsMediaOrganization' ),
		),
	);
}

/**
 * Flat list of every selectable organization subtype.
 *
 * @return array
 */
function arwp_schema_org_types() {
	$types = array();

	foreach ( arwp_schema_org_categories() as $category ) {
		$types = array_merge( $types, $category['types'] );
	}

	return $types;
}

/**
 * Type -> conditional field groups. A type may belong to several groups
 * (e.g. ClothingStore is local_business + commerce). Groups drive which
 * settings sections are shown and which properties the node emits.
 *
 * @return array
 */
function arwp_schema_org_group_map() {
	$map = array();

	$local_categories = array( 'food', 'healthcare', 'home', 'professional', 'beauty', 'automotive' );
	$categories       = arwp_schema_org_categories();

	foreach ( $local_categories as $slug ) {
		foreach ( $categories[ $slug ]['types'] as $type ) {
			$map[ $type ][] = 'local_business';
		}
	}

	foreach ( array( 'NGO', 'AnimalShelter' ) as $type ) {
		$map[ $type ][] = 'ngo';
	}

	$map['NewsMediaOrganization'][] = 'news_media';
	$map['Corporation'][]           = 'corporation';

	foreach ( array( 'OnlineBusiness', 'ClothingStore', 'GroceryStore', 'HardwareStore', 'JewelryStore' ) as $type ) {
		$map[ $type ][] = 'commerce';
	}

	return $map;
}

/**
 * Conditional field groups for an organization subtype.
 *
 * @param string $type Organization subtype.
 * @return array
 */
function arwp_schema_org_group( $type ) {
	$map = arwp_schema_org_group_map();
	return isset( $map[ $type ] ) ? $map[ $type ] : array();
}

/**
 * Currently selected organization subtype (mapping or fallback).
 *
 * @return string
 */
function arwp_schema_org_type() {
	$value = get_option( 'arwp_schema_org_type', '' );
	return in_array( $value, arwp_schema_org_types(), true ) ? $value : 'Organization';
}

/**
 * Whitelist an organization subtype.
 *
 * @param string $value Raw input.
 * @return string
 */
function arwp_sanitize_org_type( $value ) {
	$value = sanitize_text_field( $value );
	return in_array( $value, arwp_schema_org_types(), true ) ? $value : 'Organization';
}

/**
 * Sanitize a decimal coordinate, rejecting values outside a range.
 *
 * @param string $value Raw input.
 * @param float  $min   Minimum allowed value.
 * @param float  $max   Maximum allowed value.
 * @return string Empty string when invalid.
 */
function arwp_sanitize_coordinate( $value, $min, $max ) {
	$value = sanitize_text_field( $value );

	if ( ! is_numeric( $value ) ) {
		return '';
	}

	$value = (float) $value;

	return ( $value >= $min && $value <= $max ) ? (string) $value : '';
}

/**
 * Sanitize a latitude (-90 to 90).
 *
 * @param string $value Raw input.
 * @return string
 */
function arwp_sanitize_latitude( $value ) {
	return arwp_sanitize_coordinate( $value, -90, 90 );
}

/**
 * Sanitize a longitude (-180 to 180).
 *
 * @param string $value Raw input.
 * @return string
 */
function arwp_sanitize_longitude( $value ) {
	return arwp_sanitize_coordinate( $value, -180, 180 );
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
 * Split a comma-separated list option into a trimmed array.
 *
 * @param string $value Stored or preview value.
 * @return array
 */
function arwp_jsonld_comma_list( $value ) {
	$items = array();

	foreach ( explode( ',', (string) $value ) as $item ) {
		$item = trim( $item );
		if ( '' !== $item ) {
			$items[] = $item;
		}
	}

	return $items;
}

/**
 * Allowed @type values for areaServed entries. Anything else falls back
 * to the generic Place type.
 *
 * @return array
 */
function arwp_jsonld_place_types() {
	return array(
		'Place',
		'City',
		'AdministrativeArea',
		'Country',
		'State',
		'Region',
		'Continent',
		'Landform',
		'BodyOfWater',
		'Park',
		'TouristAttraction',
		'Airport',
		'Marina',
		'CivicStructure',
	);
}

/**
 * Parse an areaServed line into a typed Place entry.
 *
 * Accepted line formats (pipe-separated, each part trimmed):
 *   - "Cusco"                                  -> Place + name
 *   - "https://.../Q55807"                     -> Place + sameAs
 *   - "Cusco|https://.../Q55807"               -> Place + name + sameAs
 *   - "City|Cusco"                             -> City + name
 *   - "City|Cusco|https://.../Q55807"          -> City + name + sameAs
 * A type token not in arwp_jsonld_place_types() falls back to Place.
 *
 * @param string $line Raw areaServed line.
 * @return array
 */
function arwp_jsonld_parse_area_served( $line ) {
	$parts = array_map( 'trim', explode( '|', $line ) );

	$entry = array( '@type' => 'Place' );

	if ( 3 === count( $parts ) ) {
		$type = $parts[0];
		$name = $parts[1];
		$url  = $parts[2];

		if ( in_array( $type, arwp_jsonld_place_types(), true ) ) {
			$entry['@type'] = $type;
		}

		if ( '' !== $name ) {
			$entry['name'] = $name;
		}

		if ( false !== strpos( $url, '://' ) ) {
			$entry['sameAs'] = $url;
		}

		return $entry;
	}

	if ( 2 === count( $parts ) ) {
		if ( false !== strpos( $parts[1], '://' ) ) {
			$entry['name']   = $parts[0];
			$entry['sameAs'] = $parts[1];
		} elseif ( in_array( $parts[0], arwp_jsonld_place_types(), true ) ) {
			$entry['@type'] = $parts[0];
			$entry['name']  = $parts[1];
		} else {
			$entry['name'] = $parts[0] . '|' . $parts[1];
		}

		return $entry;
	}

	if ( false !== strpos( $parts[0], '://' ) ) {
		$entry['sameAs'] = $parts[0];
	} elseif ( '' !== $parts[0] ) {
		$entry['name'] = $parts[0];
	}

	return $entry;
}

/**
 * Parse a newline-separated areaServed option into Place entries.
 *
 * @param string $value Stored or preview value.
 * @return array
 */
function arwp_jsonld_area_served( $value ) {
	$areas = array();

	foreach ( arwp_jsonld_url_list( $value ) as $line ) {
		$areas[] = arwp_jsonld_parse_area_served( $line );
	}

	return $areas;
}

/**
 * Parse a knowsAbout line into a typed Thing entry.
 *
 * Accepted formats:
 *   - "https://.../Q1544282"                    -> Thing + sameAs
 *   - "Sustainable Tourism"                     -> Thing + name
 *   - "Sustainable Tourism|https://.../Q1544282" -> Thing + name + sameAs
 *
 * @param string $line Raw knowsAbout line.
 * @return array
 */
function arwp_jsonld_parse_thing( $line ) {
	$parts = array_map( 'trim', explode( '|', $line ) );

	$entry = array( '@type' => 'Thing' );

	if ( 2 === count( $parts ) ) {
		$entry['name'] = $parts[0];

		if ( false !== strpos( $parts[1], '://' ) ) {
			$entry['sameAs'] = $parts[1];
		}

		return $entry;
	}

	if ( false !== strpos( $parts[0], '://' ) ) {
		$entry['sameAs'] = $parts[0];
	} elseif ( '' !== $parts[0] ) {
		$entry['name'] = $parts[0];
	}

	return $entry;
}

/**
 * Parse a newline-separated knowsAbout option into Thing entries.
 *
 * @param string $value Stored or preview value.
 * @return array
 */
function arwp_jsonld_thing_list( $value ) {
	$things = array();

	foreach ( arwp_jsonld_url_list( $value ) as $line ) {
		$things[] = arwp_jsonld_parse_thing( $line );
	}

	return $things;
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
 * Map a day token to its schema.org DayOfWeek URL.
 *
 * @param string $token Short (Mo) or lowercase token.
 * @return string Empty string when unknown.
 */
function arwp_jsonld_day_uri( $token ) {
	$map = array(
		'Mo' => 'Monday',
		'Tu' => 'Tuesday',
		'We' => 'Wednesday',
		'Th' => 'Thursday',
		'Fr' => 'Friday',
		'Sa' => 'Saturday',
		'Su' => 'Sunday',
	);

	$key = ucfirst( strtolower( $token ) );

	return isset( $map[ $key ] ) ? 'https://schema.org/' . $map[ $key ] : '';
}

/**
 * Expand a day range token (e.g. "Mo-Fr") into schema.org DayOfWeek URLs,
 * in week order.
 *
 * @param string $from Starting day token.
 * @param string $to   Ending day token.
 * @return array
 */
function arwp_jsonld_day_range( $from, $to ) {
	$order = array( 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su' );

	$start = array_search( ucfirst( strtolower( $from ) ), $order, true );
	$end   = array_search( ucfirst( strtolower( $to ) ), $order, true );

	if ( false === $start || false === $end || $end < $start ) {
		return array();
	}

	$uris = array();

	for ( $i = $start; $i <= $end; $i++ ) {
		$uris[] = arwp_jsonld_day_uri( $order[ $i ] );
	}

	return $uris;
}

/**
 * Parse one opening-hours line ("Mo-Fr 09:00-17:00", "Sa,Su 10:00-14:00")
 * into an openingHoursSpecification array. Returns array() on malformed input.
 *
 * @param string $line Raw line.
 * @return array
 */
function arwp_jsonld_parse_opening_hours( $line ) {
	if ( ! preg_match( '/^([A-Za-z,\-]+)\s+(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})$/', trim( $line ), $match ) ) {
		return array();
	}

	$days = array();

	foreach ( explode( ',', $match[1] ) as $token ) {
		$token = trim( $token );

		if ( false !== strpos( $token, '-' ) ) {
			$parts = array_map( 'trim', explode( '-', $token, 2 ) );
			$days  = array_merge( $days, arwp_jsonld_day_range( $parts[0], $parts[1] ) );
		} else {
			$day = arwp_jsonld_day_uri( $token );

			if ( '' !== $day ) {
				$days[] = $day;
			}
		}
	}

	if ( empty( $days ) ) {
		return array();
	}

	return array(
		'@type'     => 'OpeningHoursSpecification',
		'dayOfWeek' => array_values( array_unique( $days ) ),
		'opens'     => $match[2],
		'closes'    => $match[3],
	);
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

	$org_type = arwp_jsonld_value( 'arwp_schema_org_type', $values );
	$org_type = in_array( $org_type, arwp_schema_org_types(), true ) ? $org_type : 'Organization';

	$organization = array(
		'@type' => $org_type,
		'@id'   => $home . '#organization',
		'name'  => '' !== $org_name ? $org_name : get_bloginfo( 'name' ),
		'url'   => $home,
	);

	$legal_name = arwp_jsonld_value( 'arwp_schema_org_legal_name', $values );
	if ( '' !== $legal_name ) {
		$organization['legalName'] = $legal_name;
	}

	$slogan = arwp_jsonld_value( 'arwp_schema_org_slogan', $values );
	if ( '' !== $slogan ) {
		$organization['slogan'] = $slogan;
	}

	$description = arwp_jsonld_value( 'arwp_schema_org_description', $values );
	if ( '' !== $description ) {
		$organization['description'] = $description;
	}

	$logo = arwp_jsonld_value( 'arwp_schema_org_logo', $values );
	if ( '' !== $logo ) {
		$organization['logo'] = $logo;
	}

	$tax_id = arwp_jsonld_value( 'arwp_schema_org_tax_id', $values );
	if ( '' !== $tax_id ) {
		$organization['taxID'] = $tax_id;
	}

	$vat_id = arwp_jsonld_value( 'arwp_schema_org_vat_id', $values );
	if ( '' !== $vat_id ) {
		$organization['vatID'] = $vat_id;
	}

	$founding_date = arwp_jsonld_value( 'arwp_schema_founding_date', $values );
	if ( '' !== $founding_date ) {
		$organization['foundingDate'] = $founding_date;
	}

	$founder = arwp_jsonld_value( 'arwp_schema_org_founder', $values );
	if ( '' !== $founder ) {
		$organization['founder'] = array(
			'@type' => 'Person',
			'name'  => $founder,
		);
	}

	$contact = array();

	$telephone = arwp_jsonld_value( 'arwp_schema_contact_telephone', $values );
	if ( '' !== $telephone ) {
		$contact['telephone'] = $telephone;
	}

	$email = arwp_jsonld_value( 'arwp_schema_contact_email', $values );
	if ( '' !== $email ) {
		$contact['email'] = $email;
	}

	$contact_type = arwp_jsonld_value( 'arwp_schema_contact_type', $values );
	if ( '' !== $contact_type ) {
		$contact['contactType'] = $contact_type;
	}

	$languages = arwp_jsonld_value( 'arwp_schema_contact_languages', $values );
	if ( '' !== $languages ) {
		$contact['availableLanguage'] = array_values( array_filter( array_map( 'trim', explode( ',', $languages ) ) ) );
	}

	if ( ! empty( $contact ) ) {
		$organization['contactPoint'] = array_merge( array( '@type' => 'ContactPoint' ), $contact );
	}

	$area_served = arwp_jsonld_url_list( arwp_jsonld_value( 'arwp_schema_area_served', $values ) );
	if ( ! empty( $area_served ) ) {
		$areas = arwp_jsonld_area_served( arwp_jsonld_value( 'arwp_schema_area_served', $values ) );

		if ( ! empty( $areas ) ) {
			$organization['areaServed'] = $areas;
		}
	}

	if ( in_array( 'local_business', arwp_schema_org_group( $org_type ), true ) ) {
		$address = array();

		$address_fields = array(
			'streetAddress'   => 'arwp_schema_address_street',
			'addressLocality' => 'arwp_schema_address_locality',
			'addressRegion'   => 'arwp_schema_address_region',
			'postalCode'      => 'arwp_schema_address_postal',
			'addressCountry'  => 'arwp_schema_address_country',
		);

		foreach ( $address_fields as $key => $name ) {
			$value = arwp_jsonld_value( $name, $values );

			if ( '' !== $value ) {
				$address[ $key ] = $value;
			}
		}

		if ( ! empty( $address ) ) {
			$organization['address'] = array_merge( array( '@type' => 'PostalAddress' ), $address );
		}

		$lat = arwp_jsonld_value( 'arwp_schema_geo_lat', $values );
		$lng = arwp_jsonld_value( 'arwp_schema_geo_lng', $values );

		if ( is_numeric( $lat ) && is_numeric( $lng ) ) {
			$organization['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $lat,
				'longitude' => (float) $lng,
			);
		}

		$price_range = arwp_jsonld_value( 'arwp_schema_price_range', $values );
		if ( '' !== $price_range ) {
			$organization['priceRange'] = $price_range;
		}

		$hours = arwp_jsonld_url_list( arwp_jsonld_value( 'arwp_schema_opening_hours', $values ) );
		if ( ! empty( $hours ) ) {
			$specs = array();

			foreach ( $hours as $line ) {
				$spec = arwp_jsonld_parse_opening_hours( $line );

				if ( ! empty( $spec ) ) {
					$specs[] = $spec;
				}
			}

			if ( ! empty( $specs ) ) {
				$organization['openingHoursSpecification'] = $specs;
			}
		}
	}

	if ( in_array( 'ngo', arwp_schema_org_group( $org_type ), true ) ) {
		$nonprofit_status = arwp_jsonld_value( 'arwp_schema_nonprofit_status', $values );

		if ( '' !== $nonprofit_status ) {
			$organization['nonprofitStatus'] = $nonprofit_status;
		}
	}

	if ( in_array( 'news_media', arwp_schema_org_group( $org_type ), true ) ) {
		$policy_fields = array(
			'publishingPrinciples' => 'arwp_schema_publishing_principles',
			'ethicsPolicy'         => 'arwp_schema_ethics_policy',
			'correctionsPolicy'    => 'arwp_schema_corrections_policy',
			'diversityPolicy'      => 'arwp_schema_diversity_policy',
		);

		foreach ( $policy_fields as $key => $name ) {
			$value = arwp_jsonld_value( $name, $values );

			if ( '' !== $value ) {
				$organization[ $key ] = $value;
			}
		}
	}

	if ( in_array( 'corporation', arwp_schema_org_group( $org_type ), true ) ) {
		$ticker = arwp_jsonld_value( 'arwp_schema_ticker_symbol', $values );

		if ( '' !== $ticker ) {
			$organization['tickerSymbol'] = $ticker;
		}
	}

	if ( in_array( 'commerce', arwp_schema_org_group( $org_type ), true ) ) {
		$payments = arwp_jsonld_comma_list( arwp_jsonld_value( 'arwp_schema_payment_accepted', $values ) );
		if ( ! empty( $payments ) ) {
			$organization['paymentAccepted'] = $payments;
		}

		$currencies = arwp_jsonld_comma_list( arwp_jsonld_value( 'arwp_schema_currencies_accepted', $values ) );
		if ( ! empty( $currencies ) ) {
			$organization['currenciesAccepted'] = $currencies;
		}

		$return_policy = arwp_jsonld_value( 'arwp_schema_merchant_return_policy', $values );
		if ( '' !== $return_policy ) {
			$organization['hasMerchantReturnPolicy'] = $return_policy;
		}
	}

	$same_as = arwp_jsonld_url_list( arwp_jsonld_value( 'arwp_schema_same_as', $values ) );
	if ( ! empty( $same_as ) ) {
		$organization['sameAs'] = $same_as;
	}

	$knows_about = arwp_jsonld_thing_list( arwp_jsonld_value( 'arwp_schema_knows_about', $values ) );
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

	return array( apply_filters( 'agent_ready_organization_node', $organization ), $website );
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
 * Build an extra Service node for a post or page with the "Service schema"
 * meta box option enabled.
 *
 * @param WP_Post $post Post object.
 * @return array Empty array when the option is off.
 */
function arwp_jsonld_build_service_node( $post ) {
	if ( '1' !== get_post_meta( $post->ID, '_arwp_schema_service_enabled', true ) ) {
		return array();
	}

	$home      = home_url( '/' );
	$permalink = get_permalink( $post );

	$node = array(
		'@type'    => 'Service',
		'@id'      => $permalink . '#service',
		'name'     => arwp_jsonld_clean_text( get_the_title( $post ) ),
		'provider' => arwp_jsonld_ref( 'Organization', $home . '#organization' ),
	);

	$service_type = get_post_meta( $post->ID, '_arwp_schema_service_type', true );
	if ( '' !== $service_type ) {
		$node['serviceType'] = $service_type;
	}

	$price = get_post_meta( $post->ID, '_arwp_schema_service_price', true );
	if ( '' !== $price ) {
		$node['offers'] = array(
			'@type' => 'Offer',
			'price' => $price,
		);
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
 * Top-most ancestor of the first category assigned to a post.
 *
 * @param WP_Post $post Post object.
 * @return WP_Term|false
 */
function arwp_jsonld_top_category( $post ) {
	$categories = wp_get_post_categories( $post->ID );

	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return false;
	}

	$category_id = (int) $categories[0];
	$ancestors   = get_ancestors( $category_id, 'category' );

	if ( ! empty( $ancestors ) ) {
		$category_id = (int) end( $ancestors );
	}

	$category = get_term( $category_id, 'category' );

	return ( $category && ! is_wp_error( $category ) ) ? $category : false;
}

/**
 * Build a BreadcrumbList node for the current page, post or archive.
 *
 * Trail: Home + ancestors/page or top category/post or archive title.
 * Returns an empty array on the front page or when the trail has fewer
 * than two items (Home + current).
 *
 * @return array
 */
function arwp_jsonld_build_breadcrumb_node() {
	if ( is_front_page() ) {
		return array();
	}

	$items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => __( 'Home', 'arwp' ),
			'item'     => home_url( '/' ),
		),
	);

	$current_url = '';

	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post && isset( $post->ID ) ) {
			if ( 'page' === $post->post_type ) {
				$ancestors = array_reverse( get_post_ancestors( $post ) );

				foreach ( $ancestors as $ancestor_id ) {
					$items[] = array(
						'@type'    => 'ListItem',
						'position' => count( $items ) + 1,
						'name'     => arwp_jsonld_clean_text( get_the_title( $ancestor_id ) ),
						'item'     => get_permalink( $ancestor_id ),
					);
				}
			} elseif ( 'post' === $post->post_type ) {
				$category = arwp_jsonld_top_category( $post );

				if ( $category ) {
					$items[] = array(
						'@type'    => 'ListItem',
						'position' => count( $items ) + 1,
						'name'     => arwp_jsonld_clean_text( $category->name ),
						'item'     => get_term_link( $category ),
					);
				}
			}

			$items[] = array(
				'@type'    => 'ListItem',
				'position' => count( $items ) + 1,
				'name'     => arwp_jsonld_clean_text( get_the_title( $post ) ),
				'item'     => get_permalink( $post ),
			);

			$current_url = get_permalink( $post );
		}
	} elseif ( is_archive() || is_home() ) {
		$title = arwp_jsonld_clean_text( get_the_archive_title() );

		if ( '' === $title || 'Archives' === $title ) {
			$title = get_bloginfo( 'name' );
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => count( $items ) + 1,
			'name'     => $title,
			'item'     => arwp_jsonld_archive_url(),
		);

		$current_url = arwp_jsonld_archive_url();
	} else {
		return array();
	}

	if ( count( $items ) < 2 || '' === $current_url ) {
		return array();
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $current_url . '#breadcrumb',
		'itemListElement' => $items,
	);
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

			$service = arwp_jsonld_build_service_node( $post );
			if ( ! empty( $service ) ) {
				$nodes[] = $service;
			}
		}
	} elseif ( is_archive() || is_home() ) {
		$nodes[] = arwp_jsonld_build_collection_node();
	}

	$breadcrumb = arwp_jsonld_build_breadcrumb_node();
	if ( ! empty( $breadcrumb ) ) {
		$nodes[] = $breadcrumb;
	}

	return apply_filters( 'agent_ready_json_ld_graph', array(
		'@context' => 'https://schema.org',
		'@graph'   => $nodes,
	) );
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
 * Build the validator.schema.org prefill URL for a schema array.
 *
 * The JSON is percent-encoded so pretty-print newlines survive as %0A. The
 * URL is escaped with esc_attr() at output, never esc_url(), whose clean_url
 * guard strips %0A/%0D sequences.
 *
 * @param array $schema Schema array.
 * @return string
 */
function arwp_jsonld_validator_href( $schema ) {
	$url = 'https://validator.schema.org/?code=' . rawurlencode( arwp_jsonld_graph_json( $schema ) );

	if ( strlen( $url ) > 14000 ) {
		return 'https://validator.schema.org/#url=' . rawurlencode( home_url( add_query_arg( array() ) ) );
	}

	return $url;
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
 * Add a "Validate Schema" group to the admin bar. The main item opens the
 * current page's schema in validator.schema.org (code-prefilled) in a new tab.
 * A hover submenu offers two URL-based validators: schema.org's validator with
 * the page URL (#url=) and Google's Rich Results Test (?url=).
 *
 * The main item's href is left empty so WP does not run the URL through
 * esc_url() (whose clean_url guard strips the %0A newlines); the link lives in
 * the raw title instead, so its href is escaped with esc_attr(). The submenu
 * hrefs are rawurlencode()d page URLs (no %0A) and pass esc_url() normally.
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

	$schema = arwp_jsonld_build_graph();

	if ( empty( $schema['@graph'] ) ) {
		return;
	}

	$title = '<a href="' . esc_attr( arwp_jsonld_validator_href( $schema ) ) . '" target="_blank" rel="noopener noreferrer"><span class="ab-icon dashicons-schema"></span><span class="ab-label">' . esc_html__( 'Validate Schema', 'arwp' ) . '</span></a>';

	$wp_admin_bar->add_node(
		array(
			'id'    => 'arwp-validate-schema',
			'title' => $title,
			'href'  => '',
		)
	);

	// add_query_arg() returns a relative URL; home_url() makes it absolute so
	// the validators can fetch the page server-side.
	$page_url = rawurlencode( home_url( add_query_arg( array() ) ) );

	$wp_admin_bar->add_node(
		array(
			'id'     => 'arwp-validate-schema-schemaorg',
			'parent' => 'arwp-validate-schema',
			'title'  => esc_html__( 'Schema.org (via URL)', 'arwp' ),
			'href'   => 'https://validator.schema.org/#url=' . $page_url,
			'meta'   => array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			),
		)
	);

	$wp_admin_bar->add_node(
		array(
			'id'     => 'arwp-validate-schema-richresults',
			'parent' => 'arwp-validate-schema',
			'title'  => esc_html__( "Google's Rich Results Test", 'arwp' ),
			'href'   => 'https://search.google.com/test/rich-results?url=' . $page_url,
			'meta'   => array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			),
		)
	);
}
add_action( 'admin_bar_menu', 'arwp_adminbar_validate_schema', 100 );

/**
 * Enqueue the admin bar validate styles when the button will be shown.
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

	wp_enqueue_style( 'arwp-adminbar', ARWP_URL . 'assets/arwp-adminbar.css', array(), ARWP_VERSION );
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
		'arwp_schema_org_type'               => 'arwp_sanitize_org_type',
		'arwp_schema_org_legal_name'         => 'sanitize_text_field',
		'arwp_schema_org_slogan'             => 'sanitize_text_field',
		'arwp_schema_org_tax_id'             => 'sanitize_text_field',
		'arwp_schema_org_vat_id'             => 'sanitize_text_field',
		'arwp_schema_founding_date'          => 'sanitize_text_field',
		'arwp_schema_org_founder'            => 'sanitize_text_field',
		'arwp_schema_area_served'            => 'arwp_sanitize_text_list',
		'arwp_schema_contact_telephone'      => 'sanitize_text_field',
		'arwp_schema_contact_email'          => 'sanitize_email',
		'arwp_schema_contact_type'           => 'sanitize_text_field',
		'arwp_schema_contact_languages'      => 'sanitize_text_field',
		'arwp_schema_address_street'         => 'sanitize_text_field',
		'arwp_schema_address_locality'       => 'sanitize_text_field',
		'arwp_schema_address_region'         => 'sanitize_text_field',
		'arwp_schema_address_postal'         => 'sanitize_text_field',
		'arwp_schema_address_country'        => 'sanitize_text_field',
		'arwp_schema_geo_lat'                => 'arwp_sanitize_latitude',
		'arwp_schema_geo_lng'                => 'arwp_sanitize_longitude',
		'arwp_schema_price_range'            => 'sanitize_text_field',
		'arwp_schema_opening_hours'          => 'arwp_sanitize_text_list',
		'arwp_schema_nonprofit_status'       => 'sanitize_text_field',
		'arwp_schema_publishing_principles'  => 'esc_url_raw',
		'arwp_schema_ethics_policy'          => 'esc_url_raw',
		'arwp_schema_corrections_policy'     => 'esc_url_raw',
		'arwp_schema_diversity_policy'       => 'esc_url_raw',
		'arwp_schema_ticker_symbol'          => 'sanitize_text_field',
		'arwp_schema_payment_accepted'       => 'sanitize_text_field',
		'arwp_schema_currencies_accepted'    => 'sanitize_text_field',
		'arwp_schema_merchant_return_policy' => 'esc_url_raw',
		'arwp_schema_org_description'        => 'sanitize_textarea_field',
		'arwp_schema_org_logo'               => 'esc_url_raw',
		'arwp_schema_same_as'                => 'arwp_sanitize_url_list',
		'arwp_schema_knows_about'            => 'arwp_sanitize_text_list',
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

	$schema = apply_filters( 'agent_ready_json_ld_graph', $schema );

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
	$value = get_option( 'arwp_schema_org_logo', '' );
	?>
	<input
		id="arwp-schema-org-logo"
		class="regular-text"
		type="text"
		name="arwp_schema_org_logo"
		value="<?php echo esc_attr( $value ); ?>"
		placeholder="https://example.com/logo.png"
	>
	<button type="button" class="button" id="arwp-logo-upload"><?php esc_html_e( 'Select from Media Library', 'arwp' ); ?></button>
	<?php
	arwp_field_description(
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
		__( 'The main subjects your organization is known for. One per line: a name (e.g. "Sustainable Tourism"), a Wikidata URL, or a NAME|URL pair for both (e.g. "Sustainable Tourism|https://www.wikidata.org/wiki/Q1544282"). Shown as knowsAbout.', 'arwp' ),
		'https://schema.org/knowsAbout'
	);
}

function arwp_org_type_section_cb() {
	echo '<p>' . esc_html__( 'Pick the closest Schema.org type for your organization. Local business, non-profit, media and e-commerce types reveal extra fields below.', 'arwp' ) . '</p>';
}

function arwp_field_org_type() {
	$current = arwp_schema_org_type();
	$groups  = arwp_schema_org_group_map();
	?>
	<select name="arwp_schema_org_type" id="arwp-schema-org-type">
		<?php foreach ( arwp_schema_org_categories() as $category ) : ?>
			<optgroup label="<?php echo esc_attr( $category['label'] ); ?>">
				<?php foreach ( $category['types'] as $type ) : ?>
					<option
						value="<?php echo esc_attr( $type ); ?>"
						data-groups="<?php echo esc_attr( implode( ' ', isset( $groups[ $type ] ) ? $groups[ $type ] : array() ) ); ?>"
						<?php selected( $current, $type ); ?>
					>
						<?php echo esc_html( $type ); ?>
					</option>
				<?php endforeach; ?>
			</optgroup>
		<?php endforeach; ?>
	</select>
	<?php
	arwp_field_description( __( 'The Schema.org type emitted as the Organization node @type. Over 50 subtypes across commercial, local business, non-profit and media categories.', 'arwp' ), 'https://schema.org/Organization' );
}

function arwp_field_org_legal_name() {
	arwp_text_field(
		'arwp_schema_org_legal_name',
		'',
		'text',
		__( 'The official legal entity name, distinct from the display brand name. Shown as legalName.', 'arwp' ),
		'https://schema.org/legalName'
	);
}

function arwp_field_org_slogan() {
	arwp_text_field(
		'arwp_schema_org_slogan',
		'',
		'text',
		__( 'A short motto or value statement. Shown as slogan.', 'arwp' ),
		'https://schema.org/slogan'
	);
}

function arwp_contact_section_cb() {
	echo '<p>' . esc_html__( 'A structured public contact channel emitted as a contactPoint on the Organization node.', 'arwp' ) . '</p>';
}

function arwp_field_contact_telephone() {
	arwp_text_field(
		'arwp_schema_contact_telephone',
		'+1-555-0100',
		'text',
		__( 'Public contact phone number in international format. Shown as contactPoint.telephone.', 'arwp' ),
		'https://schema.org/telephone'
	);
}

function arwp_field_contact_email() {
	arwp_text_field(
		'arwp_schema_contact_email',
		'info@example.com',
		'text',
		__( 'Public contact email address. Shown as contactPoint.email.', 'arwp' ),
		'https://schema.org/email'
	);
}

function arwp_field_contact_type() {
	arwp_text_field(
		'arwp_schema_contact_type',
		'customer service',
		'text',
		__( 'Purpose of this contact channel, e.g. "customer service", "sales", "technical support". Shown as contactPoint.contactType.', 'arwp' ),
		'https://schema.org/contactType'
	);
}

function arwp_field_contact_languages() {
	arwp_text_field(
		'arwp_schema_contact_languages',
		'English',
		'text',
		__( 'Comma-separated languages spoken on this channel, e.g. "English, Spanish". Shown as contactPoint.availableLanguage.', 'arwp' ),
		'https://schema.org/availableLanguage'
	);
}

function arwp_legal_section_cb() {
	echo '<p>' . esc_html__( 'Legal identity and provenance signals used by AI agents to assess legitimacy and maturity.', 'arwp' ) . '</p>';
}

function arwp_field_org_tax_id() {
	arwp_text_field(
		'arwp_schema_org_tax_id',
		'',
		'text',
		__( 'Government tax identifier (e.g. EIN, UTR). Shown as taxID.', 'arwp' ),
		'https://schema.org/taxID'
	);
}

function arwp_field_org_vat_id() {
	arwp_text_field(
		'arwp_schema_org_vat_id',
		'',
		'text',
		__( 'Value Added Tax identifier (EU/UK). Shown as vatID.', 'arwp' ),
		'https://schema.org/vatID'
	);
}

function arwp_field_founding_date() {
	arwp_text_field(
		'arwp_schema_founding_date',
		'YYYY-MM-DD',
		'text',
		__( 'Date the organization was founded, e.g. 1999-04-01. Shown as foundingDate.', 'arwp' ),
		'https://schema.org/foundingDate'
	);
}

function arwp_field_org_founder() {
	arwp_text_field(
		'arwp_schema_org_founder',
		'',
		'text',
		__( 'Name of the founder. Shown as founder with a Person type.', 'arwp' ),
		'https://schema.org/founder'
	);
}

function arwp_field_area_served() {
	arwp_textarea_field(
		'arwp_schema_area_served',
		__( 'Cities, regions or countries served, one per line. Plain name (e.g. "Cusco") or Wikidata/Wikipedia URL for grounding. For a typed entry use the format TYPE|NAME|URL, e.g. "City|Cusco|https://www.wikidata.org/wiki/Q55807". TYPE can be City, AdministrativeArea, Country, State or Region (falls back to Place). Shown as areaServed.', 'arwp' ),
		'https://schema.org/areaServed'
	);
}

function arwp_local_section_cb() {
	echo '<p>' . esc_html__( 'Physical location metadata for local business types. Emitted as address, geo, priceRange and openingHoursSpecification on the Organization node.', 'arwp' ) . '</p>';
}

function arwp_field_address_street() {
	arwp_text_field(
		'arwp_schema_address_street',
		'123 Main St',
		'text',
		__( 'Street address, e.g. "123 Main St". Shown as address.streetAddress.', 'arwp' ),
		'https://schema.org/streetAddress'
	);
}

function arwp_field_address_locality() {
	arwp_text_field(
		'arwp_schema_address_locality',
		'City',
		'text',
		__( 'City / locality. Shown as address.addressLocality.', 'arwp' ),
		'https://schema.org/addressLocality'
	);
}

function arwp_field_address_region() {
	arwp_text_field(
		'arwp_schema_address_region',
		'Region',
		'text',
		__( 'State, province or region. Shown as address.addressRegion.', 'arwp' ),
		'https://schema.org/addressRegion'
	);
}

function arwp_field_address_postal() {
	arwp_text_field(
		'arwp_schema_address_postal',
		'00000',
		'text',
		__( 'ZIP or postal code. Shown as address.postalCode.', 'arwp' ),
		'https://schema.org/postalCode'
	);
}

function arwp_field_address_country() {
	arwp_text_field(
		'arwp_schema_address_country',
		'PE',
		'text',
		__( 'Two-letter country code (ISO 3166-1 alpha-2), e.g. "US" or "PE". Shown as address.addressCountry.', 'arwp' ),
		'https://schema.org/addressCountry'
	);
}

function arwp_field_geo_lat() {
	arwp_text_field(
		'arwp_schema_geo_lat',
		'-13.5320',
		'text',
		__( 'Latitude in decimal degrees (e.g. -13.5320). Shown as geo.latitude. Must be between -90 and 90.', 'arwp' ),
		'https://schema.org/latitude'
	);
}

function arwp_field_geo_lng() {
	arwp_text_field(
		'arwp_schema_geo_lng',
		'-71.9675',
		'text',
		__( 'Longitude in decimal degrees (e.g. -71.9675). Shown as geo.longitude. Must be between -180 and 180.', 'arwp' ),
		'https://schema.org/longitude'
	);
}

function arwp_field_price_range() {
	arwp_text_field(
		'arwp_schema_price_range',
		'$$',
		'text',
		__( 'Relative price tier, e.g. $, $$, $$$, $$$$. Shown as priceRange.', 'arwp' ),
		'https://schema.org/priceRange'
	);
}

function arwp_field_opening_hours() {
	arwp_textarea_field(
		'arwp_schema_opening_hours',
		__( 'Weekly opening hours, one entry per line. Format: DAYS HH:MM-HH:MM, e.g. "Mo-Fr 09:00-17:00" or "Sa,Su 10:00-14:00". Day codes: Mo Tu We Th Fr Sa Su.', 'arwp' ),
		'https://schema.org/openingHoursSpecification'
	);
}

function arwp_ngo_section_cb() {
	echo '<p>' . esc_html__( 'Tax-status metadata for non-profit organization types. Emitted as nonprofitStatus on the Organization node.', 'arwp' ) . '</p>';
}

function arwp_field_nonprofit_status() {
	arwp_text_field(
		'arwp_schema_nonprofit_status',
		'https://schema.org/Nonprofit501c3',
		'text',
		__( 'A URL describing the non-profit legal status, e.g. https://schema.org/Nonprofit501c3. Shown as nonprofitStatus.', 'arwp' ),
		'https://schema.org/nonprofitStatus'
	);
}

function arwp_news_section_cb() {
	echo '<p>' . esc_html__( 'Policy URLs for media organizations, used by AI agents and search engines to evaluate publishing standards. Emitted on the Organization node.', 'arwp' ) . '</p>';
}

function arwp_field_publishing_principles() {
	arwp_text_field(
		'arwp_schema_publishing_principles',
		'https://example.com/principles',
		'url',
		__( 'URL to the page describing your publishing principles. Shown as publishingPrinciples.', 'arwp' ),
		'https://schema.org/publishingPrinciples'
	);
}

function arwp_field_ethics_policy() {
	arwp_text_field(
		'arwp_schema_ethics_policy',
		'https://example.com/ethics',
		'url',
		__( 'URL to your ethics policy. Shown as ethicsPolicy.', 'arwp' ),
		'https://schema.org/ethicsPolicy'
	);
}

function arwp_field_corrections_policy() {
	arwp_text_field(
		'arwp_schema_corrections_policy',
		'https://example.com/corrections',
		'url',
		__( 'URL to your corrections policy. Shown as correctionsPolicy.', 'arwp' ),
		'https://schema.org/correctionsPolicy'
	);
}

function arwp_field_diversity_policy() {
	arwp_text_field(
		'arwp_schema_diversity_policy',
		'https://example.com/diversity',
		'url',
		__( 'URL to your diversity policy. Shown as diversityPolicy.', 'arwp' ),
		'https://schema.org/diversityPolicy'
	);
}

function arwp_corporate_section_cb() {
	echo '<p>' . esc_html__( 'Public-company identifier. Emitted as tickerSymbol on the Organization node.', 'arwp' ) . '</p>';
}

function arwp_field_ticker_symbol() {
	arwp_text_field(
		'arwp_schema_ticker_symbol',
		'NASDAQ:ACME',
		'text',
		__( 'Exchange:SYMBOL format, e.g. "NASDAQ:ACME" or "NYSE:F". Shown as tickerSymbol.', 'arwp' ),
		'https://schema.org/tickerSymbol'
	);
}

function arwp_commerce_section_cb() {
	echo '<p>' . esc_html__( 'Online store acceptance metadata. Emitted as paymentAccepted, currenciesAccepted and hasMerchantReturnPolicy on the Organization node.', 'arwp' ) . '</p>';
}

function arwp_field_payment_accepted() {
	arwp_text_field(
		'arwp_schema_payment_accepted',
		'Credit Card, PayPal',
		'text',
		__( 'Comma-separated list of accepted payment methods, e.g. "Credit Card, PayPal, Cash". Shown as paymentAccepted.', 'arwp' ),
		'https://schema.org/paymentAccepted'
	);
}

function arwp_field_currencies_accepted() {
	arwp_text_field(
		'arwp_schema_currencies_accepted',
		'USD',
		'text',
		__( 'Comma-separated list of ISO 4217 currency codes, e.g. "USD, EUR". Shown as currenciesAccepted.', 'arwp' ),
		'https://schema.org/currenciesAccepted'
	);
}

function arwp_field_merchant_return_policy() {
	arwp_text_field(
		'arwp_schema_merchant_return_policy',
		'https://example.com/returns',
		'url',
		__( 'URL to your return policy page. Shown as hasMerchantReturnPolicy.', 'arwp' ),
		'https://schema.org/hasMerchantReturnPolicy'
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
 * Map of settings section id => field group shown conditionally on the
 * selected Organization type.
 *
 * @return array
 */
function arwp_jsonld_conditional_sections() {
	return array(
		'arwp_local_section'    => 'local_business',
		'arwp_ngo_section'      => 'ngo',
		'arwp_news_section'     => 'news_media',
		'arwp_corporate_section' => 'corporation',
		'arwp_commerce_section' => 'commerce',
	);
}

/**
 * Render settings sections like do_settings_sections(), but wrap sections
 * listed in arwp_jsonld_conditional_sections() in a div the admin JS shows
 * or hides based on the selected Organization type's data-groups.
 *
 * @param string $page Settings page slug.
 */
function arwp_jsonld_do_settings_sections( $page ) {
	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections[ $page ] ) ) {
		return;
	}

	$conditional = arwp_jsonld_conditional_sections();

	foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
		$is_conditional = isset( $conditional[ $section['id'] ] );

		if ( $is_conditional ) {
			echo '<div class="arwp-conditional" data-groups="' . esc_attr( $conditional[ $section['id'] ] ) . '">';
		}

		if ( $section['title'] ) {
			echo '<h2>' . esc_html( $section['title'] ) . '</h2>';
		}

		if ( $section['callback'] ) {
			call_user_func( $section['callback'] );
		}

		if ( isset( $wp_settings_fields, $wp_settings_fields[ $page ], $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
			echo '<table class="form-table" role="presentation">';
			do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}

		if ( $is_conditional ) {
			echo '</div>';
		}
	}
}

/**
 * Render the JSON-LD settings page.
 */
function arwp_jsonld_render_settings() {
	?>
	<div class="wrap">
		<?php settings_errors(); ?>
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="arwp-jsonld-layout">
			<div class="arwp-jsonld-fields">
				<form action="options.php" method="post">
					<?php
					settings_fields( 'arwp_jsonld_options' );
					arwp_jsonld_do_settings_sections( 'arwp-jsonld' );
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
