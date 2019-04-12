<?php



/**
 * Create a user group for teams.
 */
function uri_teams_register_user_taxonomy() {
	// Make sure that WP_User_Taxonomy class exists
	if ( ! class_exists( 'WP_User_Taxonomy' ) ) {
		return;
	}

	// Create the new user taxonomy.
	new WP_User_Taxonomy( 'teams_users', array('post', 'users/content-team'), array(
		'hierarchical' => false,
		'singular' => __( 'Team',  'uri' ),
		'plural'   => __( 'Teams', 'uri' ),
	) );
	
}
add_action( 'init', 'uri_teams_register_user_taxonomy' );


/**
 * Register a new taxonomy for teams to be used on content.
 */
function uri_teams_register_taxonomies() {

	// get the content types to which we apply teams.
	$types = uri_teams_get_applicable_post_types();

	register_taxonomy('teams_content', $types, array(
			'hierarchical' => false,
			'label' => __('Teams', 'uri'),
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'show_in_rest' => true,
			'singular_label' => __('Team', 'uri'),
			'description' => __( 'Teams are used for managing privileges', 'uri' )
		)
	);
}
add_action( 'init', 'uri_teams_register_taxonomies', 90 );


/**
 * Disable the default proof-of-concept user groups ("type" and "group").
 */
function uri_teams_remove_default_user_groups () {
	remove_action( 'init', 'wp_register_default_user_group_taxonomy' );
	remove_action( 'init', 'wp_register_default_user_type_taxonomy' );
}
add_action( 'init', 'uri_teams_remove_default_user_groups', 9 );

