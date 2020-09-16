<?php


/**
 * Admin page for the teams taxonomy
 */
function uri_teams_users_teams_page() {

  $taxonomy = get_taxonomy( 'uri_teams' );

  add_users_page(
    esc_attr( $taxonomy->labels->menu_name ),
    esc_attr( $taxonomy->labels->menu_name ),
    $taxonomy->cap->manage_terms,
    'edit-tags.php?taxonomy=' . $taxonomy->name
  );

}
add_action( 'admin_menu', 'uri_teams_users_teams_page' );


/**
 * Adds a 'Members' column on the manage teams admin page.
 */
function uri_teams_add_user_column( $columns ) {
//  unset( $columns['posts'] );
  $columns['users'] = __( 'Members' );
  return $columns;
}
add_filter( 'manage_edit-uri_teams_columns', 'uri_teams_add_user_column' );

/**
 * @param string $display WP just passes an empty string here.
 * @param string $column The name of the custom column.
 * @param int $term_id The ID of the term being displayed in the table.
 */
function uri_teams_add_user_column_count( $display, $column, $term_id ) {
  if ( 'users' === $column ) {
    $term = get_term( $term_id, 'uri_teams' );
    echo $term->count;
  }
}
add_filter( 'manage_uri_teams_custom_column', 'uri_teams_add_user_column_count', 10, 3 );


/**
 * Update parent file name to fix the selected menu issue
 */
function uri_teams_change_parent_file($parent_file) {
    global $submenu_file;
    if ( isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'uri_teams' && 'edit-tags.php?taxonomy=uri_teams' == $submenu_file ) {
	    $parent_file = 'users.php';
    }
    return $parent_file;
}
add_filter('parent_file', 'uri_teams_change_parent_file');


/**
 * Register a new taxonomy for teams to be used on content.
 */
function uri_teams_register_taxonomies() {
	// get the content types to which we apply teams.
	$types = uri_teams_get_applicable_post_types();
	register_taxonomy('uri_teams', $types, _uri_teams_define_taxonomy() );
}
add_action( 'init', 'uri_teams_register_taxonomies', 90 );



function _uri_teams_get_teams_for_user( $user_id ) {
	return wp_get_object_terms( $user_id, 'uri_teams', array() );
}

/**
 * Define the teams taxonomy arguments in one place.
 */
function _uri_teams_define_taxonomy () {
	return array(
		'hierarchical' => false,
		'label' => __('Teams', 'uri'),
		'labels' => array(
			'name' => __('Teams', 'uri'),
			'all_items' => __('Teams', 'uri'),
			'singular_name' => __('Team', 'uri'),
			'search_items' => __('Search Teams', 'uri'),
			'edit_item' => __('Edit Team', 'uri'),
			'view_item' => __('View Team', 'uri'),
			'update_item' => __('Update Team', 'uri'),
			'add_new_item' => __('Add New Team', 'uri'),
			'add_or_remove_items' => __('Add or Remove Teams', 'uri'),
			'not_found' => __('No Teams Found', 'uri'),
			'back_to_items' => __('Back to Teams', 'uri'),
		),
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'show_in_rest' => true,
		'description' => __( 'Teams are used for managing privileges', 'uri' )
	);
}

