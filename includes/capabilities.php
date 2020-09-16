<?php


/**
 * Get the identifiers of the roles that this plugin creates.
 * returns an array of slugs
 * @return arr
 */
function uri_teams_get_roles() {
	$output = array();
	$roles = uri_teams_roles();

	foreach ( $roles as $r ) {
		array_push( $output, $r['slug'] );
	}

	return $output;
}


/**
 * Get the detailed information about roles that this plugin creates.
 * @return arr
 */
function uri_teams_roles() {
	return array(
// 		array(
// 			'slug' => 'team_contributor',
// 			'name' =>  __( 'Team Contributor', 'uri' ),
// 			'caps' => array(
// 				'read' => true,
// 				'delete_posts' => true,
// 				'edit_posts' => true,
// 				'upload_files' => true, // Let them upload files, they'll need it.
// 				'edit_pages' => true, // Able to edit WordPress pages.
// 			)
// 		),
		array(
			'slug' => 'team_editor',
			'name' =>  __( 'Team Editor', 'uri' ),
			'caps' => array(
				'delete_pages' => true,
				'delete_published_pages' => true,
				'edit_others_pages' => true,
				'edit_pages' => true,
				'edit_published_pages' => true,
				'publish_pages' => true,
				'read_private_pages' => true,
				'delete_posts' => true,
				'delete_published_posts' => true,
				'edit_others_posts' => true,
				'edit_posts' => true,
				'edit_published_posts' => true,
				'publish_posts' => true,
				'read' => true,
				'read_private_posts' => true,
				'unfiltered_html' => true,
				'upload_files' => true,
			)
		)
	);
}



/**
 * Take a role and return the new capabilities that should be added to that role.
 * @param  string $role Any role used by the Content Teams plugin.
 * @return array        Array of new capabilities added to that role.
 */
function uri_teams_role_to_caps_map( $role = '' ) {
	// Bail if no role was passed.
	if ( '' == $role ) {
		return false;
	}

	// Map the new capabilities to user roles.
	$caps_map = array(
		'administrator' => array(
			'edit_team_content',   // Able to view content from teams.
			'edit_global_content', // Able to edit all content, regardless of team.
		),
		'editor' => array(
			'edit_team_content',   
			'edit_global_content', 
		),
		'contributor' => array(
			'edit_team_content',   
			'edit_global_content', 
		),
		'team_editor' => array(
			'edit_team_content',   
		),
// 		'team_contributor' => array(
// 			'edit_team_content',   
// 		),
	);

	// Check if the role passed is one we're using.
	if ( ! array_key_exists( $role, $caps_map ) ) {
		return false;
	}

	// Return the new capabilities for the given role.
	return $caps_map[ $role ];
}





/**
 * Adds or removes the new capabilities required for Content Teams.
 *
 * @param  string $action The desired action. Either 'add' or 'remove'.
 */
function uri_teams_adjust_caps( $action = '' ) {
	if ( ! in_array( $action, array( 'add', 'remove' ) ) ) {
		return;
	}

	$adjust_cap = $action . '_cap';

	$roles = get_editable_roles();
	
	// Loop through all the roles.
	foreach( $roles as $slug => $role ) {
		// get a map of the capabilities we want to associate with the role
		$caps_map = uri_teams_role_to_caps_map( $slug );
		if ( is_array( $caps_map ) && $r = get_role( $slug ) ) {
			// If we have a capabilities map, and a valid role, add the capability to the role
			foreach( $caps_map as $cap ) {
				$r->$adjust_cap( $cap );
			}
		}
	}

}

/**
 * Triggered on activation, adds the new capabilities for Content Teams.
 */
function uri_teams_add_caps() {
	uri_teams_adjust_caps( 'add' );
}

/**
* Triggered on deactivation, removes the new capabilities for Content Teams.
*/
function uri_teams_remove_caps() {
	uri_teams_adjust_caps( 'remove' );
}

