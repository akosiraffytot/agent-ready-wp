<?php
/**
 * Agent Ready WP — Author entity schema fields on the user profile.
 *
 * Phase 4: Job Title + social profiles feed the author Person node in the
 * JSON-LD @graph.
 *
 * @package Agent_Ready_WP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the author schema fields on the user profile screen.
 *
 * @param WP_User $user Current user object.
 */
function arwp_render_user_schema_fields( $user ) {
	$job_title = get_user_meta( $user->ID, 'arwp_author_job_title', true );
	$same_as   = get_user_meta( $user->ID, 'arwp_author_same_as', true );
	?>
	<h3><?php esc_html_e( 'Agent Ready WP — Author Entity Schema', 'arwp' ); ?></h3>
	<table class="form-table" role="presentation">
		<tr>
			<th>
				<label for="arwp_author_job_title"><?php esc_html_e( 'Job Title', 'arwp' ); ?></label>
			</th>
			<td>
				<input
					type="text"
					class="regular-text"
					id="arwp_author_job_title"
					name="arwp_author_job_title"
					value="<?php echo esc_attr( $job_title ); ?>"
				>
				<p class="description"><?php esc_html_e( 'Role shown in the author Person node of the JSON-LD graph, e.g. "Editor at Acme".', 'arwp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="arwp_author_same_as"><?php esc_html_e( 'Social Profiles (sameAs)', 'arwp' ); ?></label>
			</th>
			<td>
				<textarea
					class="large-text"
					rows="4"
					id="arwp_author_same_as"
					name="arwp_author_same_as"
				><?php echo esc_textarea( $same_as ); ?></textarea>
				<p class="description"><?php esc_html_e( "Links to this author's official profiles so agents can confirm the same person across platforms. One URL per line.", 'arwp' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'arwp_render_user_schema_fields' );
add_action( 'edit_user_profile', 'arwp_render_user_schema_fields' );

/**
 * Save the author schema fields. Core verifies the profile form nonce before
 * these actions fire; we add the capability check.
 *
 * @param int $user_id User ID being saved.
 * @return bool
 */
function arwp_save_user_schema_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( isset( $_POST['arwp_author_job_title'] ) ) {
		update_user_meta( $user_id, 'arwp_author_job_title', sanitize_text_field( wp_unslash( $_POST['arwp_author_job_title'] ) ) );
	}

	if ( isset( $_POST['arwp_author_same_as'] ) ) {
		update_user_meta( $user_id, 'arwp_author_same_as', arwp_sanitize_url_list( wp_unslash( $_POST['arwp_author_same_as'] ) ) );
	}

	return true;
}
add_action( 'personal_options_update', 'arwp_save_user_schema_fields' );
add_action( 'edit_user_profile_update', 'arwp_save_user_schema_fields' );
