<?php
/**
 * Plugin Name: URI Teams
 * Description: Permissions management for groups.
 * Version:     0.1.0
 * Author:      URI Web Communications
 * Author URI:  
 *
 * @author John Pennypacker <jpennypacker@uri.edu>
 * @see https://webdevstudios.com/2016/03/22/building-content-teams-wp-user-groups/
 */


// plugin needs to be installed
// https://wordpress.org/plugins/wp-user-groups/

require_once dirname( __FILE__ ) . '/includes/capabilities.php';


// stuff that we need on load
require_once dirname( __FILE__ ) . '/includes/init.php';

// callbacks designed to ensure that taxonomies stay in sync
require_once dirname( __FILE__ ) . '/includes/teams_sync.php';



/**
 * Returns a variable after going through var_dump().
 *
 * Just for ease in debugging.
 *
 * @param mixed whatever you want to dump out.
 *
 */
function uri_teams_dump($var) {

	ob_start();

	echo '<pre class="dump" style="margin-left: 20em;">';
	var_dump($var);
	echo '</pre>';

	$output = ob_get_clean();
	return $output;
}


/**
 * Returns the post types that this plugin affects.
 *
 * And that would be all public post types.
 *
 */
function uri_teams_get_applicable_post_types() {
	// get the content types to which we apply teams.
	$types = get_post_types(array('public'=>TRUE));
	return $types;
}




/**
 * Handles the magic filtering of Program Areas by Content Team.
 *
 * @param  class $query WP_Query that we're modifying.
 */
function wds_filter_content_areas( $query ) {
	if ( ! is_admin() ) {
		return;
	}

	// Get the post type.
	$current_post_type = $query->get( 'post_type' );

	// Make sure we're on the right post type edit page.
	if ( in_array( $current_post_type, uri_teams_get_applicable_post_types() ) ) {

		if ( uri_teams_is_team_editor() ) {

			$teams = ( wp_get_terms_for_user( get_current_user_id(), 'teams_users' ) ) ? wp_get_terms_for_user( get_current_user_id(), 'teams_users' ) : array();
			$areas = array();
			
			foreach ( $teams as $team ) {
				$areas[] = uri_teams_get_content_term_id_by_team( $team, 'content_team' );
			}

			$query->set( 'tax_query', array(
				array(
					'taxonomy' => 'teams_content',
					'field'    => 'term_id',
					'terms'    => $areas,
					'operator' => 'IN',
				),
			) );

		}
	}
}
add_action( 'pre_get_posts', 'wds_filter_content_areas', 10 );

/**
 * Return a term ID for a content term based on that term's content team term.
 * @param  object $term The original term object.
 * @return int          The taxonomy term id.
 */
function uri_teams_get_content_term_id_by_team( $term ) {
	if ( is_array( $term ) && isset( $term['invalid_taxonomy'] ) || empty( $term ) ) {
		return;
	}

	$new_term = get_term_by( 'slug', $term->slug, 'teams_content' );
	return $new_term->term_id;
}

/**
 * Checks a user's capabilities. 
 * If they are only team editors, they can only see team content.
 *
 * @return bool If a user is a member of a specific team, hide things.
 */
function uri_teams_is_team_editor() {

	// If the current user can only edit team content, we need to filter.
	if ( current_user_can( 'edit_global_content' ) ) {
		return false;
	}

	// If the current user can only edit team content, we need to filter.
	if ( current_user_can( 'edit_team_content' ) ) {
		return true;
	}

	return false;
}


/**
 * Adds Teams automagically to content when it's saved.
 *
 * As content is saved, add the saving user's content teams to the post.
 *
 * @param  int $post_id The post ID you're editing.
 */
function uri_teams_add_user_team_terms_to_post( $post_id ) {
	if ( wp_is_post_revision( $post_id ) )
		return;

	// Set Post taxonomy terms to sync with the users taxonomy terms.
	$user_terms = wp_get_terms_for_user( get_current_user_id(), 'teams_users' );

	// Get the normal taxonomy terms that are the same as the user taxonomy terms.
	foreach ( $user_terms as $term ) {
		$post_terms[] = $term->slug; // Add the slug to the array, when we add the normal taxon term below it will use the same slug.
	}

	// Actually associate the matched terms with the post.
	if ( isset( $post_terms ) ) {
		$__terms = wp_set_object_terms( $post_id, $post_terms, 'teams_content' );
	}
}
add_action( 'save_post', 'uri_teams_add_user_team_terms_to_post', 99 );




// activation setup
include_once dirname( __FILE__ ) . '/includes/activate.php';
register_activation_hook( __FILE__, 'uri_teams_add_roles_on_plugin_activation' );

// deactivation cleanup
include_once dirname( __FILE__ ) . '/includes/deactivate.php';
register_deactivation_hook( __FILE__, 'uri_teams_delete_roles_on_plugin_deactivation' );

