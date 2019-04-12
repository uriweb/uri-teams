<?php
/**
 * CRUD synchronizers for teams taxonomies
 */


/**
 * Update an existing team.
 * When an admin updates the user team, update the content team and vice versa.
 * NB: there are a few things that are very important here as they avoid looping
 * @param int $term_id is the term id
 * @param int $tt_id is the taxonomy id
 */
function uri_teams_update_team( $term_id, $tt_id ) {

	// Important.
	// Refresh the term with the latest before testing if it has changed.
	clean_term_cache( array($term_id) );
	$term = get_term( $term_id );
	
	if( ! $term ) {
		return; // bad thing
	}

  $new_taxonomy = ( 'teams_users' == $term->taxonomy ) ? 'teams_content' : 'teams_users';
	$accompanying_term = get_term_by( 'slug', $term->name, $new_taxonomy );

	clean_term_cache( array($term->term_id, $accompanying_term->term_id) );
	
	// Important.
	// to prevent an _infinite loop_, make sure there's an actual change.
	if(
		$term->name != $accompanying_term->name || 
		$term->slug != $accompanying_term->slug || 
		$term->description != $accompanying_term->description
		) {

		$update = wp_update_term( $accompanying_term->term_id, $accompanying_term->taxonomy, array(
			'name' => $term->name,
			'slug' => $term->slug,
			'description' => $term->description
		) );
		
		
// 		if ( ! is_wp_error( $update ) ) {
// 			echo 'success';
// 			echo uri_teams_dump($update);
// 			echo '<hr>';	
// 		} else {
// 			echo 'fail';
// 			echo uri_teams_dump($update);
// 			echo '<hr>';
// 		}

	}


}
add_action( 'edit_teams_users', 'uri_teams_update_team', 20, 2 );
add_action( 'edit_teams_content', 'uri_teams_update_team', 20, 2 );



/**
 * Create a new team.
 * When an admin creates a team in the content taxonomy, create a correlate team for users
 * and vice versa.
 *
 * @param int $term_id is the id of the recently created team
 * @param int $tt_id is the taxonomy id
 */
function uri_teams_create_team( $term_id, $tt_id ) {

	$newly_created_term = get_term( $term_id );
	
	if ( ! is_object( $newly_created_term ) && ! $newly_created_term->taxonomy ) {
		return FALSE;
	}
	
  $new_taxonomy = ( 'teams_users' == $newly_created_term->taxonomy ) ? 'teams_content' : 'teams_users';
	$accompanying_term = get_term_by( 'name', $newly_created_term->name, $new_taxonomy );

	
	// if we already have the accompanying term, no need to proceed.
	if ( $accompanying_term ) {
		return;
	} else {
		// create the accompanying term
		wp_insert_term( $newly_created_term->name, $new_taxonomy, array(
			'description' => $newly_created_term->description,
			'slug' => $newly_created_term->slug,
		));
	}

  
}
add_action( 'created_teams_users', 'uri_teams_create_team', 20, 2 );
add_action( 'created_teams_content', 'uri_teams_create_team', 20, 2 );



/**
 * Delete a team.
 * When an admin creates a team in the content taxonomy, create a correlate team for users
 * and vice versa.
 *
 * @param int $term_id is the id of the recently created team
 * @param int $tt_id is the taxonomy id
 * @param str $taxonomy, for some reason, is the recently deleted term ¯\_(ツ)_/¯
 * @param obj $deleted_term is supposed to be the recently deleted term
 */
function uri_teams_delete_team( $term_id, $tt_id, $taxonomy, $deleted_term ) {

	$newly_deleted_term = $taxonomy;

	if ( ! is_object( $newly_deleted_term ) && ! $newly_deleted_term->taxonomy ) {
		return FALSE;
	}
	
  $new_taxonomy = ( 'teams_users' == $newly_deleted_term->taxonomy ) ? 'teams_content' : 'teams_users';
	$accompanying_term = get_term_by( 'name', $newly_deleted_term->name, $new_taxonomy );


	// if the accompanying term doesn't exist, no need to proceed.
	if ( ! $accompanying_term ) {
		return;
	} else {
		// delete the accompanying term
		wp_delete_term( $accompanying_term->term_id, $accompanying_term->taxonomy );
	}

  
}
add_action( 'delete_teams_users', 'uri_teams_delete_team', 20, 4 );
add_action( 'delete_teams_content', 'uri_teams_delete_team', 20, 4 );

